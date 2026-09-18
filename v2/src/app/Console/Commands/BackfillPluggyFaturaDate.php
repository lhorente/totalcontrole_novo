<?php

namespace App\Console\Commands;

use App\Models\CreditCard;
use App\Models\IntegracaoBancaria;
use App\Models\Scopes\CurrentUserScope;
use App\Models\Transaction;
use App\Services\PluggyClient;
use Carbon\Carbon;
use Illuminate\Console\Command;

class BackfillPluggyFaturaDate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pluggy:backfill-fatura-date
                             {integracao : id de integracoes_bancarias cujas transações serão recalculadas}
                             {--dry-run : Mostra o que seria alterado sem gravar nada no banco}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalcula a data das transações já importadas de uma integração Pluggy usando o dia de fechamento/vencimento do cartão, em vez do billForecastDate da Pluggy (não confiável pra todo banco -- ver PluggyTransactionSyncService::mapearTransacao)';

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

        // Janela de busca cobre desde 2 meses antes da transação mais antiga já
        // importada dessa origem -- margem extra porque a data armazenada pode
        // já estar deslocada pelo bug que este comando corrige.
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

        $corrigidas            = 0;
        $inalteradas           = 0;
        $naoEncontradas        = 0;
        $semCartaoConfigurado  = 0;
        $cartaoCache           = [];

        foreach ($transacoes as $transacao) {
            $transacaoPluggy = $pluggyById[$transacao->id_externo] ?? null;

            if (!$transacaoPluggy) {
                $naoEncontradas++;
                continue;
            }

            $idCartao = $transacao->id_cartao;

            if (!array_key_exists($idCartao, $cartaoCache)) {
                $cartaoCache[$idCartao] = CreditCard::find($idCartao);
            }

            $cartao = $cartaoCache[$idCartao];

            if (!$cartao || !$cartao->dia_fechamento) {
                $semCartaoConfigurado++;
                continue;
            }

            $dataOriginalCompra = Carbon::parse($transacaoPluggy['date']);
            $novaData           = $cartao->calcularDataFatura($dataOriginalCompra);

            if ($novaData->format('Y-m-d') !== $transacao->data->format('Y-m-d')) {
                $this->line("#{$transacao->id} {$transacao->descricao_banco}: {$transacao->data->format('Y-m-d')} -> {$novaData->format('Y-m-d')}");

                if (!$dryRun) {
                    $transacao->data = $novaData->format('Y-m-d');
                    $transacao->save();
                }

                $corrigidas++;
            } else {
                $inalteradas++;
            }
        }

        $this->newLine();
        $prefixo = $dryRun ? '[dry-run] ' : '';
        $this->info("{$prefixo}Corrigidas: {$corrigidas} | Já corretas: {$inalteradas} | Não encontradas na Pluggy: {$naoEncontradas} | Sem dia_fechamento cadastrado: {$semCartaoConfigurado}");

        if ($dryRun) {
            $this->warn('Nada foi gravado -- rode sem --dry-run pra aplicar as correções.');
        }

        return self::SUCCESS;
    }
}
