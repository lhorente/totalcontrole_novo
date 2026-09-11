<?php

namespace App\Exceptions;

class PluggyAccountNotFoundException extends \RuntimeException
{
  public function __construct(public readonly string $itemId)
  {
    parent::__construct("Nenhuma conta do tipo CREDIT/CREDIT_CARD encontrada para o item_id '{$itemId}' na Pluggy.");
  }
}
