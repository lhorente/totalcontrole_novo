<?php

namespace App\Console\Commands;

use App\Models\IntegracaoBancaria;
use App\Models\Scopes\CurrentUserScope;
use App\Models\Transaction;
use App\Services\PluggyClient;
use App\Services\PluggyTransactionSyncService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class BackfillPluggyCardInfo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pluggy:backfill-card-info
                             {integracao : id de integracoes_bancarias cujas transações serão reprocessadas}
                             {--dry-run : Mostra o que seria alterado sem gravar nada no banco}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Preenche últimos_digitos_cartao/data_compra nas transações já importadas de uma integração Pluggy e reavalia o id_cartao -- na primeira importação, alguns subcartões ainda não existiam no sistema, então a transação ficou associada ao cartão físico em vez do subcartão certo.';

    public function __construct(
        private PluggyClient $pluggyClient,
        private PluggyTransactionSyncService $syncService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $integracao = IntegracaoBancaria::find($this->argument('integracao'));

        if (!$integracao) {
            $this->error("Integração #{$this->argument('integracao')} não encontrada.");

            return self::FAILURE;
        }

        $origem = 'pluggy_' . $integracao->provider;

        $conta = $this->pluggyClient->resolveCreditCardAccount($integracao->pluggy_item_id);

        // Janela de busca cobre desde 2 meses antes da transação mais antiga já
        // importada dessa origem -- margem extra por segurança.
        $dataMaisAntiga = Transaction::withoutGlobalScope(CurrentUserScope::class)
            ->where('origem', $origem)
            ->min('data_compra');

        $dateFrom = $dataMaisAntiga
            ? Carbon::parse($dataMaisAntiga)->subMonths(2)->format('Y-m-d')
            : now()->subYear()->format('Y-m-d');

        $this->info("Buscando transações na Pluggy (integração #{$integracao->id}, {$integracao->provider}) desde {$dateFrom}...");

        $pluggyById = [];
        $after = null;

        do {
            $resposta = $this->pluggyClient->getTransactions($conta['id'], $dateFrom, $after);

            foreach ($resposta['results'] ?? [] as $transacaoPluggy) {
                $pluggyById[$transacaoPluggy['id']] = $transacaoPluggy;
            }

            $next  = $resposta['next'] ?? null;
            $after = null;

            if ($next) {
                parse_str(ltrim($next, '?'), $parametros);
                $after = $parametros['after'] ?? null;
            }
        } while ($after !== null);

        $this->info('Total de transações buscadas na Pluggy: ' . count($pluggyById));

        $transacoes = Transaction::withoutGlobalScope(CurrentUserScope::class)
            ->where('origem', $origem)
            ->whereNotNull('id_externo')
            ->get();

        $this->info("Total de transações no banco ({$origem}): " . $transacoes->count());
        $this->newLine();

        $atualizadas     = 0;
        $cartaoTrocado    = 0;
        $inalteradas     = 0;
        $naoEncontradas  = 0;

        foreach ($transacoes as $transacao) {
            $transacaoPluggy = $pluggyById[$transacao->id_externo] ?? null;

            if (!$transacaoPluggy) {
                $naoEncontradas++;
                continue;
            }

            $novosUltimosDigitos = $transacaoPluggy['creditCardMetadata']['cardNumber'] ?? null;
            $novaDataCompra      = Carbon::parse($transacaoPluggy['date'])->format('Y-m-d');
            $novoIdCartao        = $this->syncService->resolverCartao($transacaoPluggy, $integracao);

            $mudancas = [];

            if ($transacao->ultimos_digitos_cartao !== $novosUltimosDigitos) {
                $mudancas[] = "ultimos_digitos_cartao: " . ($transacao->ultimos_digitos_cartao ?? 'null') . ' -> ' . ($novosUltimosDigitos ?? 'null');
            }

            if (!$transacao->data_compra || $transacao->data_compra->format('Y-m-d') !== $novaDataCompra) {
                $mudancas[] = 'data_compra: ' . ($transacao->data_compra ? $transacao->data_compra->format('Y-m-d') : 'null') . " -> {$novaDataCompra}";
            }

            if ($transacao->id_cartao != $novoIdCartao) {
                $mudancas[] = "id_cartao: {$transacao->id_cartao} -> {$novoIdCartao}";
                $cartaoTrocado++;
            }

            if (empty($mudancas)) {
                $inalteradas++;
                continue;
            }

            $this->line("#{$transacao->id} {$transacao->descricao_banco}: " . implode(' | ', $mudancas));

            if (!$dryRun) {
                $transacao->ultimos_digitos_cartao = $novosUltimosDigitos;
                $transacao->data_compra            = $novaDataCompra;
                $transacao->id_cartao              = $novoIdCartao;
                $transacao->save();
            }

            $atualizadas++;
        }

        $this->newLine();
        $prefixo = $dryRun ? '[dry-run] ' : '';
        $this->info("{$prefixo}Atualizadas: {$atualizadas} (das quais com troca de cartão: {$cartaoTrocado}) | Já corretas: {$inalteradas} | Não encontradas na Pluggy: {$naoEncontradas}");

        if ($dryRun) {
            $this->warn('Nada foi gravado -- rode sem --dry-run pra aplicar as correções.');
        }

        return self::SUCCESS;
    }
}
