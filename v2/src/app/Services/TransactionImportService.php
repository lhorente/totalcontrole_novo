<?php

namespace App\Services;

use App\Models\Scopes\CurrentUserScope;
use App\Models\Transaction;
use Illuminate\Database\QueryException;

class TransactionImportService
{
  /**
   * Deduplica e grava uma transação vinda de uma fonte externa (import por IA,
   * futuramente Pluggy). Não lê Auth::/session() -- todo o contexto de quem/onde
   * gravar vem explícito nos parâmetros, para funcionar também fora de um
   * request HTTP (ex.: job agendado).
   *
   * $dados espera as mesmas chaves que hoje alimentam Transaction::create() no
   * import por IA (id_categoria, descricao_banco, descricao, valor, data,
   * id_cartao, tipo, id_cliente, data_banco, chave_banco, data_fatura), mais
   * origem/id_externo quando a fonte tiver um identificador estável (Pluggy).
   *
   * $dryRun faz tudo (inclusive as duas checagens de duplicidade) menos o
   * INSERT final -- necessário porque `transacoes` é MyISAM (herdada do v1) e
   * MyISAM não suporta transação: um DB::rollBack() em volta desta chamada
   * NÃO desfaz o Transaction::create(), o insert fica gravado de verdade.
   *
   * @return array{status: 'criada'|'duplicada', transacao: ?Transaction}
   */
  public function importarTransacaoExterna(array $dados, int $idUsuario, int $idWorkspace, ?int $idCaixa, bool $dryRun = false): array
  {
    $origem     = $dados['origem'] ?? null;
    $idExterno  = $dados['id_externo'] ?? null;

    $chaveBanco = $dados['chave_banco'] ?? null;
    if (!$chaveBanco) {
      $chaveBanco = Transaction::generateChaveBanco(
        $dados['data_banco'] ?? '',
        $dados['descricao_banco'] ?? '',
        $dados['valor'] ?? 0,
        $dados['data_fatura'] ?? $dados['data'] ?? ''
      );
    }

    // Duplicidade por chave_banco: mesma regra do import por IA hoje, escopada
    // por id_usuario (não id_workspace) -- quirk legado preservado de propósito.
    $duplicadaPorChaveBanco = Transaction::withoutGlobalScope(CurrentUserScope::class)
      ->where('chave_banco', $chaveBanco)
      ->where('id_usuario', $idUsuario)
      ->exists();

    if ($duplicadaPorChaveBanco) {
      return ['status' => 'duplicada', 'transacao' => null];
    }

    // Duplicidade por origem+id_externo (Pluggy, Smartpos-like): apoiada no
    // índice único (id_workspace, origem, id_externo) já existente em `transacoes`.
    if ($origem && $idExterno) {
      $duplicadaPorOrigemExterno = Transaction::withoutGlobalScope(CurrentUserScope::class)
        ->where('id_workspace', $idWorkspace)
        ->where('origem', $origem)
        ->where('id_externo', $idExterno)
        ->exists();

      if ($duplicadaPorOrigemExterno) {
        return ['status' => 'duplicada', 'transacao' => null];
      }
    }

    $linha = [
      'id_categoria'    => $dados['id_categoria'] ?? null,
      'descricao_banco' => $dados['descricao_banco'] ?? '',
      'descricao'       => $dados['descricao'] ?? '',
      'valor'           => $dados['valor'] ?? 0,
      'data'            => $dados['data'] ?? now(),
      'id_cartao'       => $dados['id_cartao'] ?? null,
      'id_caixa'        => $idCaixa,
      'tipo'            => $dados['tipo'] ?? 'despesa',
      'id_cliente'      => $dados['id_cliente'] ?? null,
      'id_usuario'      => $idUsuario,
      'id_workspace'    => $idWorkspace,
      'chave_banco'     => $chaveBanco,
    ];

    if ($origem && $idExterno) {
      $linha['origem']     = $origem;
      $linha['id_externo'] = $idExterno;
      $linha['status']     = $dados['status'] ?? 'disponivel';
    }

    if ($dryRun) {
      return ['status' => 'criada', 'transacao' => null];
    }

    try {
      $transacao = Transaction::create($linha);
    } catch (QueryException $e) {
      // Corrida rara entre duas execuções do job: o índice único
      // (id_workspace, origem, id_externo) barrou no lugar do exists() acima.
      if ($e->getCode() === '23000' && $origem && $idExterno) {
        return ['status' => 'duplicada', 'transacao' => null];
      }
      throw $e;
    }

    return ['status' => 'criada', 'transacao' => $transacao];
  }
}
