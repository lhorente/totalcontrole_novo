<?php

namespace App\Console\Commands;

use App\Models\Scopes\CurrentUserScope;
use App\Models\Transaction;
use App\Models\TransactionMapping;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillTransactionMappings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transaction-mappings:backfill
                             {--workspace= : Restringe o backfill a um id_workspace específico}
                             {--dry-run : Mostra o que seria feito sem gravar nada no banco}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Alimenta o De <> Para com transações já existentes cuja descrição foi renomeada manualmente (descricao != descricao_banco)';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $workspaceId = $this->option('workspace');
        $dryRun      = (bool) $this->option('dry-run');

        $query = Transaction::withoutGlobalScope(CurrentUserScope::class)
            ->whereNotNull('descricao_banco')
            ->where('descricao_banco', '!=', '')
            ->whereNotNull('descricao')
            ->where('descricao', '!=', '')
            ->whereColumn('descricao', '!=', 'descricao_banco')
            ->orderBy('updated_at'); // a edição mais recente é quem "vence" o local/categoria do mapeamento

        if ($workspaceId) {
            $query->where('id_workspace', $workspaceId);
        }

        $transacoes = $query->get(['id', 'id_workspace', 'id_usuario', 'id_categoria', 'descricao_banco', 'descricao', 'updated_at']);

        if ($transacoes->isEmpty()) {
            $this->info('Nenhuma transação com descrição renomeada manualmente foi encontrada.');

            return self::SUCCESS;
        }

        $this->info("{$transacoes->count()} transações com descrição renomeada encontradas.");

        $bar = $this->output->createProgressBar($transacoes->count());
        $bar->start();

        $criados    = 0;
        $reforcados = 0;

        DB::beginTransaction();

        foreach ($transacoes as $t) {
            $mapeamento = TransactionMapping::learnFrom($t);

            if ($mapeamento) {
                $mapeamento->wasRecentlyCreated ? $criados++ : $reforcados++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        if ($dryRun) {
            DB::rollBack();
            $this->warn("[dry-run] Nada foi gravado. Seriam criados {$criados} mapeamentos e reforçados {$reforcados}.");
        } else {
            DB::commit();
            $this->info("Concluído: {$criados} mapeamentos criados, {$reforcados} reforçados (contador de uso incrementado).");
        }

        return self::SUCCESS;
    }
}
