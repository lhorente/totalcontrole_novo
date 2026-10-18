<?php

namespace App\Console\Commands;

use App\Models\IntegracaoBancaria;
use App\Models\Scopes\CurrentUserScope;
use App\Models\Transaction;
use App\Services\PluggyClient;
use App\Services\PluggyTransactionSyncService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class BackfillPluggyMoeda extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pluggy:backfill-moeda
                             {integracao : id de integracoes_bancarias cujas transações serão recalculadas}
                             {--dry-run : Mostra o que seria alterado sem gravar nada no banco}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Corrige o valor das transações em moeda estrangeira já importadas de uma integração Pluggy, que foram gravadas com o valor na moeda original (amount) em vez do convertido para a moeda da conta (amountInAccountCurrency)';

    public function __construct(private PluggyClient $pluggyClient)
    {
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

        // Busca a partir da data de compra mais antiga já importada dessa origem
        // (com margem) -- `data` é a data da fatura, pode estar à frente da compra.
        $dataMaisAntiga = Transaction::withoutGlobalScope(CurrentUserScope::class)
            ->where('origem', $origem)
            ->min('data');

        $dateFrom = $dataMaisAntiga
            ? Carbon::parse($dataMaisAntiga)->subMonths(2)->format('Y-m-d')
            : now()->subYear()->format('Y-m-d');

        $this->info("Buscando transações na Pluggy (integração #{$integracao->id}, {$integracao->provider}) desde {$dateFrom}...");

        $pluggyById = [];
        $after = null;

        do {
            $resposta = $this->pluggyClient->getTransactions($conta['id'], $dateFrom, $after);

            foreach ($resposta['results'] ?? [] as $transacaoPluggy) {
                // Só interessam as que vieram em moeda diferente da conta.
                if (isset($transacaoPluggy['amountInAccountCurrency'])
                    && ($transacaoPluggy['currencyCode'] ?? 'BRL') !== 'BRL') {
                    $pluggyById[$transacaoPluggy['id']] = $transacaoPluggy;
                }
            }

            $next  = $resposta['next'] ?? null;
            $after = null;

            if ($next) {
                parse_str(ltrim($next, '?'), $parametros);
                $after = $parametros['after'] ?? null;
            }
        } while ($after !== null);

        $this->info('Transações em moeda estrangeira na Pluggy: ' . count($pluggyById));

        if (!$pluggyById) {
            return self::SUCCESS;
        }

        $transacoes = Transaction::withoutGlobalScope(CurrentUserScope::class)
            ->where('origem', $origem)
            ->whereIn('id_externo', array_keys($pluggyById))
            ->get();

        $this->newLine();

        $corrigidas  = 0;
        $inalteradas = 0;

        foreach ($transacoes as $transacao) {
            $transacaoPluggy = $pluggyById[$transacao->id_externo];
            $novoValor       = PluggyTransactionSyncService::valorNaMoedaDaConta($transacaoPluggy);

            if (abs((float) $transacao->valor - $novoValor) < 0.005) {
                $inalteradas++;
                continue;
            }

            $this->line("#{$transacao->id} {$transacao->descricao_banco} ({$transacaoPluggy['currencyCode']} {$transacaoPluggy['amount']}): {$transacao->valor} -> {$novoValor}");

            if (!$dryRun) {
                $transacao->valor = $novoValor;
                $transacao->save();
            }

            $corrigidas++;
        }

        $this->newLine();
        $prefixo = $dryRun ? '[dry-run] ' : '';
        $this->info("{$prefixo}Corrigidas: {$corrigidas} | Já corretas: {$inalteradas}");

        if ($dryRun) {
            $this->warn('Nada foi gravado -- rode sem --dry-run pra aplicar as correções.');
        }

        return self::SUCCESS;
    }
}
