<?php

namespace App\Services;

use App\Exceptions\PluggyAccountNotFoundException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class PluggyClient
{
  private string $clientId;
  private string $clientSecret;
  private string $baseUrl;

  public function __construct()
  {
    $this->clientId     = config('services.pluggy.client_id');
    $this->clientSecret = config('services.pluggy.client_secret');
    $this->baseUrl      = config('services.pluggy.base_url');
  }

  /**
   * apiKey da Pluggy: válido por 2h e compartilhado entre todos os itens
   * (bancos) da mesma aplicação (client_id/client_secret) — não é por item_id.
   * Cacheado com margem de segurança (100min) para nunca usar um token expirado.
   */
  public function getApiKey(): string
  {
    return Cache::remember('pluggy_api_key', now()->addMinutes(100), function () {
      return $this->authenticate();
    });
  }

  private function authenticate(): string
  {
    $response = Http::post("{$this->baseUrl}/auth", [
      'clientId'     => $this->clientId,
      'clientSecret' => $this->clientSecret,
    ])->throw();

    return $response->json('apiKey');
  }

  /**
   * GET /items/{id} -- status da conexão em si (não confundir com status de
   * conta/transação). Usado para detectar LOGIN_ERROR e WAITING_USER_INPUT
   * (MFA) antes de tentar buscar contas/transações de um item quebrado.
   * https://docs.pluggy.ai/reference/items-retrieve
   *
   * @return array{id: string, status: string, executionStatus: string, error: ?array}
   */
  public function getItem(string $itemId): array
  {
    $response = Http::withHeaders(['X-API-KEY' => $this->getApiKey()])
      ->get("{$this->baseUrl}/items/{$itemId}")
      ->throw();

    return $response->json();
  }

  /**
   * @return array Lista de contas retornadas pela Pluggy para o item_id.
   */
  public function getAccounts(string $itemId): array
  {
    $response = Http::withHeaders(['X-API-KEY' => $this->getApiKey()])
      ->get("{$this->baseUrl}/accounts", ['itemId' => $itemId])
      ->throw();

    return $response->json('results') ?? [];
  }

  /**
   * Filtro genérico (não hardcoded por banco): entre as contas do item_id,
   * retorna a que representa o cartão de crédito (type=CREDIT, subtype=CREDIT_CARD).
   * Vale igualmente para Nubank e Bradesco, e para qualquer outro banco futuro.
   *
   * @throws PluggyAccountNotFoundException se nenhuma conta bater o filtro
   */
  public function resolveCreditCardAccount(string $itemId): array
  {
    $contas = $this->getAccounts($itemId);

    foreach ($contas as $conta) {
      if (($conta['type'] ?? null) === 'CREDIT' && ($conta['subtype'] ?? null) === 'CREDIT_CARD') {
        return $conta;
      }
    }

    throw new PluggyAccountNotFoundException($itemId);
  }

  /**
   * GET /v2/transactions (cursor-based). O cursor de paginação real da Pluggy
   * chama-se "after" (confirmado em resposta real: {"results":[...],"next":"?accountId=...&after=..."}),
   * não "cursor" -- quem pagina extrai o valor de "after" do "next" da resposta anterior.
   * O filtro de data chama-se "dateFrom" (não "from" -- a API real rejeita "from"
   * com 400 "property from should not exist", confirmado contra a API de verdade;
   * ver https://docs.pluggy.ai/reference/transactions-list-by-cursor).
   * Retorna a resposta crua da Pluggy; a paginação em si é responsabilidade de quem chama.
   */
  public function getTransactions(string $accountId, ?string $dateFrom = null, ?string $after = null): array
  {
    $query = array_filter([
      'accountId' => $accountId,
      'dateFrom'  => $dateFrom,
      'after'     => $after,
    ]);

    $response = Http::withHeaders(['X-API-KEY' => $this->getApiKey()])
      ->get("{$this->baseUrl}/v2/transactions", $query)
      ->throw();

    return $response->json();
  }
}
