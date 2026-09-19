<?php

namespace App\Services;

use App\Exceptions\PluggyLoginErrorException;
use App\Exceptions\PluggyMfaPendingException;
use App\Models\CreditCard;
use App\Models\IntegracaoBancaria;
use App\Models\TransactionMapping;
use App\Models\Wallet;
use Carbon\Carbon;

class PluggyTransactionSyncService
{
  public function __construct(
    private PluggyClient $pluggyClient,
    private TransactionImportService $transactionImportService,
  ) {
  }

  /**
   * Sincroniza uma integração: checa o status do item (LOGIN_ERROR/MFA
   * pendente -- ver checarStatusItem()), resolve a conta de cartão de
   * crédito, busca transações novas desde last_sync_at (páginando por
   * "after") e grava cada uma via
   * TransactionImportService::importarTransacaoExterna(). Rate limit (HTTP
   * 429) não é tratado aqui -- é um problema global da execução, não desta
   * integração isolada, então sobe como exceção normal (RequestException)
   * para quem chama (comando agendado) decidir se aborta o lote inteiro.
   *
   * $dryRun repassa para importarTransacaoExterna() (que pula o INSERT) e
   * também não atualiza last_sync_at -- ver TransactionImportService para o
   * porquê de não dar pra confiar em DB::rollBack() aqui (`transacoes` é MyISAM).
   *
   * @throws PluggyLoginErrorException
   * @throws PluggyMfaPendingException
   * @return array{criadas: int, duplicadas: int, ignoradas_pagamento_fatura: int, paginas: int}
   */
  public function sincronizar(IntegracaoBancaria $integracao, bool $dryRun = false): array
  {
    if (!$integracao->id_cartao) {
      throw new \RuntimeException("Integração {$integracao->id} não tem id_cartao vinculado.");
    }

    $cartao = CreditCard::find($integracao->id_cartao);
    if (!$cartao) {
      throw new \RuntimeException("Integração {$integracao->id}: cartão {$integracao->id_cartao} não encontrado.");
    }

    $idUsuario = $cartao->id_usuario;
    $idCaixa   = Wallet::where('id_usuario', $idUsuario)->where('exibir_no_saldo', 1)->first()?->id;

    $this->checarStatusItem($integracao->pluggy_item_id);

    $conta = $this->pluggyClient->resolveCreditCardAccount($integracao->pluggy_item_id);

    // Primeira sincronização: sem last_sync_at, busca uma janela inicial razoável
    // em vez do histórico completo.
    $from = $integracao->last_sync_at?->format('Y-m-d') ?? now()->subDays(90)->format('Y-m-d');

    $resultado = ['criadas' => 0, 'duplicadas' => 0, 'ignoradas_pagamento_fatura' => 0, 'paginas' => 0];
    $ultimaDataProcessada = null;
    $after = null;

    do {
      $resposta = $this->pluggyClient->getTransactions($conta['id'], $from, $after);
      $resultado['paginas']++;

      foreach ($resposta['results'] ?? [] as $transacaoPluggy) {
        // Pagamento de fatura (débito na conta corrente quitando o cartão) não é
        // uma despesa -- gravá-lo como despesa infla o gasto do mês incorretamente.
        if (($transacaoPluggy['operationType'] ?? null) === 'PAGAMENTO_FATURA') {
          $resultado['ignoradas_pagamento_fatura']++;
          continue;
        }

        $dados = $this->mapearTransacao($transacaoPluggy, $integracao);

        $resultadoImportacao = $this->transactionImportService->importarTransacaoExterna(
          $dados,
          $idUsuario,
          $integracao->id_workspace,
          $idCaixa,
          $dryRun
        );

        $resultado[$resultadoImportacao['status'] === 'criada' ? 'criadas' : 'duplicadas']++;

        // Parcelas futuras vêm da Pluggy com `date` já projetado para o mês da
        // parcela (ex.: compra em 12/09 parcelada em 2x pode trazer a 2ª
        // parcela com date=12/10) -- bem diferente de creditCardMetadata.purchaseDate,
        // que é a data real da compra. Se essa data futura virasse o novo
        // last_sync_at, o próximo `dateFrom` pularia semanas de transações
        // reais ainda não sincronizadas. Por isso ela nunca conta para o watermark.
        $dataTransacao = Carbon::parse($transacaoPluggy['date']);
        if ($dataTransacao->isFuture()) {
          continue;
        }
        if (!$ultimaDataProcessada || $dataTransacao->gt($ultimaDataProcessada)) {
          $ultimaDataProcessada = $dataTransacao;
        }
      }

      $after = $this->extrairAfter($resposta['next'] ?? null);
    } while ($after !== null);

    // Marca a última transação processada, não "agora" -- se o job falhar no
    // meio, a próxima execução reprocessa a partir daí. Idempotente porque o
    // dedup por origem+id_externo tolera reprocessar a mesma janela.
    if ($ultimaDataProcessada && !$dryRun) {
      $integracao->update(['last_sync_at' => $ultimaDataProcessada]);
    }

    return $resultado;
  }

  private function mapearTransacao(array $transacaoPluggy, IntegracaoBancaria $integracao): array
  {
    $descricaoBanco = $transacaoPluggy['descriptionRaw'] ?? $transacaoPluggy['description'] ?? '';

    // Mesma sugestão de local/categoria usada no import por IA (De <> Para),
    // escopada explicitamente por id_workspace -- matchFor() aceita esse
    // parâmetro justamente para funcionar fora de um request HTTP.
    $mapeamento = TransactionMapping::matchFor($descricaoBanco, $integracao->id_workspace);

    $idCartao = $this->resolverCartao($transacaoPluggy, $integracao);
    $cartao   = CreditCard::find($idCartao);

    $dataTransacao    = Carbon::parse($transacaoPluggy['date']);
    $billForecastDate = $transacaoPluggy['creditCardMetadata']['billForecastDate'] ?? null;

    // O `billForecastDate` da Pluggy não é confiável pra todo banco -- no
    // Bradesco ele não reflete o fechamento real (compra ainda dentro do
    // fechamento sendo reportada no mês errado). Com o dia de fechamento
    // (e vencimento, pra saber se a fatura leva o nome do mês do fechamento
    // ou do mês seguinte) cadastrados no cartão, calculamos isso localmente
    // em vez de confiar na Pluggy -- ver CreditCard::calcularDataFatura().
    // Sem o cadastro (cartão ainda não configurado), cai no billForecastDate
    // como antes, e na falta dele, na data crua da compra.
    if ($cartao && $cartao->dia_fechamento) {
      $dataCalculada = $cartao->calcularDataFatura($dataTransacao);
    } elseif ($billForecastDate) {
      [$anoFatura, $mesFatura] = explode('-', $billForecastDate);
      $ultimoDiaFatura = Carbon::create((int) $anoFatura, (int) $mesFatura, 1)->daysInMonth;
      $dataCalculada = Carbon::create((int) $anoFatura, (int) $mesFatura, min($dataTransacao->day, $ultimoDiaFatura));
    } else {
      $dataCalculada = $dataTransacao->copy();
    }

    return [
      'id_categoria'    => $mapeamento?->id_categoria,
      'descricao_banco' => $descricaoBanco,
      'descricao'       => $mapeamento?->descricao_local ?? $descricaoBanco,
      'valor'           => (float) ($transacaoPluggy['amount'] ?? 0),
      'data'            => $dataCalculada->format('Y-m-d'),
      // `data` é a data da fatura (calculada acima); `data_compra` preserva a
      // data real em que a compra aconteceu, reportada crua pela Pluggy --
      // sem isso não dá pra saber depois quando o dinheiro de fato saiu.
      'data_compra'     => $dataTransacao->format('Y-m-d'),
      'data_banco'      => $transacaoPluggy['date'] ?? '',
      'data_fatura'     => $dataCalculada->copy()->startOfMonth()->format('Y-m-d'),
      'id_cartao'       => $idCartao,
      // Guardado por linha (não só resolvido pra achar o id_cartao) porque é
      // a evidência crua que permite reprocessar/corrigir a associação depois
      // -- ver BackfillPluggyCardInfo, criado exatamente pra isso.
      'ultimos_digitos_cartao' => $transacaoPluggy['creditCardMetadata']['cardNumber'] ?? null,
      'tipo'            => 'despesa',
      'origem'          => 'pluggy_' . $integracao->provider,
      'id_externo'      => $transacaoPluggy['id'],
    ];
  }

  /**
   * Casa a transação com o subcartão certo pelos últimos 4 dígitos
   * (creditCardMetadata.cardNumber), mesmo padrão já usado no import CSV
   * (CsvParserService::toPreviewArray -- coluna "ultimos_digitos"). Sem
   * subcartão cadastrado com esses dígitos, cai no cartão físico da integração.
   * Público pra ser reaproveitado pelo BackfillPluggyCardInfo, que precisa
   * reavaliar a associação de transações já importadas antes de o subcartão
   * certo existir no sistema.
   */
  public function resolverCartao(array $transacaoPluggy, IntegracaoBancaria $integracao): ?int
  {
    $ultimosDigitos = $transacaoPluggy['creditCardMetadata']['cardNumber'] ?? null;

    if ($ultimosDigitos) {
      $subcartao = CreditCard::where('id_cartao_pai', $integracao->id_cartao)
        ->where('ultimos_digitos', $ultimosDigitos)
        ->first();

      if ($subcartao) {
        return $subcartao->id;
      }
    }

    return $integracao->id_cartao;
  }

  /**
   * Status de Item vêm da doc oficial (https://docs.pluggy.ai/docs/item-lifecycle):
   * LOGIN_ERROR = credenciais inválidas, não adianta tentar de novo sem o
   * usuário reconectar; WAITING_USER_INPUT = está esperando MFA do usuário.
   * Qualquer outro status (UPDATED, UPDATING, OUTDATED) segue normalmente --
   * OUTDATED em particular pode ter dados parciais de uma sincronização
   * anterior que ainda vale a pena buscar.
   */
  private function checarStatusItem(string $itemId): void
  {
    $item = $this->pluggyClient->getItem($itemId);

    if (($item['status'] ?? null) === 'LOGIN_ERROR') {
      throw new PluggyLoginErrorException($itemId, $item['error']['message'] ?? null);
    }

    if (($item['status'] ?? null) === 'WAITING_USER_INPUT') {
      throw new PluggyMfaPendingException($itemId);
    }
  }

  private function extrairAfter(?string $next): ?string
  {
    if (!$next) {
      return null;
    }

    parse_str(ltrim($next, '?'), $parametros);

    return $parametros['after'] ?? null;
  }
}
