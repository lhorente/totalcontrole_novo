<?php

namespace App\Exceptions;

class PluggyMfaPendingException extends \RuntimeException
{
  public function __construct(public readonly string $itemId)
  {
    parent::__construct("Item '{$itemId}' está aguardando autenticação em duas etapas (MFA) na Pluggy -- não dá pra sincronizar até o usuário completar esse passo no app/site do banco.");
  }
}
