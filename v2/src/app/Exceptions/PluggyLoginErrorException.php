<?php

namespace App\Exceptions;

class PluggyLoginErrorException extends \RuntimeException
{
  public function __construct(public readonly string $itemId, ?string $detalhe = null)
  {
    $mensagem = "Item '{$itemId}' está com LOGIN_ERROR na Pluggy: credenciais inválidas, é preciso reconectar a conta.";
    if ($detalhe) {
      $mensagem .= " Detalhe: {$detalhe}";
    }

    parent::__construct($mensagem);
  }
}
