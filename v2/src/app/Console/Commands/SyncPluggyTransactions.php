<?php

namespace App\Console\Commands;

use App\Exceptions\PluggyLoginErrorException;
use App\Exceptions\PluggyMfaPendingException;
use App\Models\IntegracaoBancaria;
use App\Services\PluggyTransactionSyncService;
use Illuminate\Console\Command;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Log;

class SyncPluggyTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pluggy:sync-transactions
                             {--integracao= : Restringe a sincronização a um id de integracoes_bancarias específico}
                             {--dry-run : Roda a sincronização (chamadas reais à Pluggy) sem gravar nada no banco}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincroniza transações de cartão de crédito das integrações bancárias ativas via Pluggy';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(PluggyTransactionSyncService $syncService)
    {
        $integracaoId = $this->option('integracao');
        $dryRun       = (bool) $this->option('dry-run');

        $query = IntegracaoBancaria::where('status', 'ativo');

        if ($integracaoId) {
            $query->where('id', $integracaoId);
        }

        $integracoes = $query->get();

        if ($integracoes->isEmpty()) {
            $this->info('Nenhuma integração ativa encontrada.');

            return self::SUCCESS;
        }

        $this->info("{$integracoes->count()} integração(ões) ativa(s) encontrada(s).");

        foreach ($integracoes as $integracao) {
            $this->line("Sincronizando integração #{$integracao->id} ({$integracao->provider}, item_id={$integracao->pluggy_item_id})...");

            try {
                $resultado = $syncService->sincronizar($integracao, $dryRun);

                $resumo = "{$resultado['criadas']} criada(s), {$resultado['duplicadas']} duplicada(s), {$resultado['ignoradas_pagamento_fatura']} pagamento(s) de fatura ignorado(s) em {$resultado['paginas']} página(s).";

                if ($dryRun) {
                    $this->warn("[dry-run] {$resumo} Nada foi gravado.");
                } else {
                    // Sucesso limpa o último erro -- sem isso, um `ultimo_erro` de uma
                    // falha antiga fica exibido pra sempre mesmo depois do sync voltar
                    // a funcionar normalmente.
                    if ($integracao->ultimo_erro !== null) {
                        $integracao->update(['ultimo_erro' => null]);
                    }

                    $this->info($resumo);
                }
            } catch (PluggyLoginErrorException $e) {
                Log::warning("Integração Pluggy #{$integracao->id} ({$integracao->provider}) com LOGIN_ERROR: {$e->getMessage()}");

                // LOGIN_ERROR não se resolve sozinho -- fica fora de 'ativo' até o
                // usuário reconectar a conta na Pluggy e alguém voltar o status manualmente.
                if (!$dryRun) {
                    $integracao->update(['status' => 'login_error', 'ultimo_erro' => $e->getMessage()]);
                }

                $this->error("Integração #{$integracao->id}: credenciais inválidas na Pluggy (LOGIN_ERROR). Não será tentada de novo até o status voltar para 'ativo'.");
            } catch (PluggyMfaPendingException $e) {
                Log::warning("Integração Pluggy #{$integracao->id} ({$integracao->provider}) aguardando MFA: {$e->getMessage()}");

                if (!$dryRun) {
                    $integracao->update(['status' => 'mfa_pendente', 'ultimo_erro' => $e->getMessage()]);
                }

                $this->error("Integração #{$integracao->id}: aguardando autenticação em duas etapas (MFA) do usuário na Pluggy. Não será tentada de novo até o status voltar para 'ativo'.");
            } catch (RequestException $e) {
                if ($e->response?->status() === 429) {
                    // Rate limit é um problema da execução inteira, não desta integração
                    // isolada -- continuar batendo nas próximas só gastaria mais do limite.
                    // Aborta o lote; as integrações não processadas são tentadas amanhã.
                    Log::error("Rate limit da Pluggy atingido na integração #{$integracao->id} ({$integracao->provider}). Abortando o restante do lote.");
                    $this->error('Rate limit da Pluggy atingido -- abortando o restante da execução. As integrações não processadas serão tentadas na próxima execução agendada.');

                    break;
                }

                Log::error("Falha HTTP ao sincronizar integração Pluggy #{$integracao->id} (provider={$integracao->provider}, item_id={$integracao->pluggy_item_id}): {$e->getMessage()}", [
                    'exception' => $e,
                ]);

                if (!$dryRun) {
                    $integracao->update(['ultimo_erro' => $e->getMessage()]);
                }

                $this->error("Falha na integração #{$integracao->id}: {$e->getMessage()}");
            } catch (\Throwable $e) {
                Log::error("Falha ao sincronizar integração Pluggy #{$integracao->id} (provider={$integracao->provider}, item_id={$integracao->pluggy_item_id}): {$e->getMessage()}", [
                    'exception' => $e,
                ]);

                // Não muda `status` aqui: erro genérico/transiente (rede, item ainda
                // sem conta de cartão, etc.) -- mantém a integração elegível para nova
                // tentativa amanhã, só registra o último erro para visibilidade.
                if (!$dryRun) {
                    $integracao->update(['ultimo_erro' => $e->getMessage()]);
                }

                $this->error("Falha na integração #{$integracao->id}: {$e->getMessage()}");
            }
        }

        return self::SUCCESS;
    }
}
