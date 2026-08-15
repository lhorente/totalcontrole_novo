<?php

namespace App\Observers;

use App\Models\Transaction;
use App\Models\TransactionMapping;
use App\Models\Scopes\CurrentUserScope;

class TransactionObserver
{
  /**
   * Autoalimenta o De <> Para sempre que uma transação é salva com um
   * apelido (descricao) diferente da descrição bruta do banco.
   * Mapeamentos manuais (criados pela tela De <> Para) não são
   * sobrescritos — apenas reforçados (contagem de uso).
   */
  public function saved(Transaction $t): void
  {
    if (empty($t->descricao_banco)) {
      return;
    }

    if (empty($t->descricao) || $t->descricao === $t->descricao_banco) {
      return;
    }

    if (!$t->wasRecentlyCreated && !$t->wasChanged('descricao')) {
      return;
    }

    $padraoNormalizado = TransactionMapping::normalize($t->descricao_banco);

    $mapeamento = TransactionMapping::withoutGlobalScope(CurrentUserScope::class)
      ->where('id_workspace', $t->id_workspace)
      ->where('padrao_normalizado', $padraoNormalizado)
      ->first();

    if ($mapeamento) {
      if ($mapeamento->origem === 'automatico') {
        $mapeamento->descricao_local = $t->descricao;
        $mapeamento->id_categoria    = $t->id_categoria;
        $mapeamento->save();
      }
      $mapeamento->registrarUso();
      return;
    }

    $mapeamento = TransactionMapping::create([
      'id_workspace'    => $t->id_workspace,
      'id_usuario'      => $t->id_usuario,
      'padrao'          => $t->descricao_banco,
      'descricao_local' => $t->descricao,
      'id_categoria'    => $t->id_categoria,
      'origem'          => 'automatico',
      'ativo'           => true,
    ]);
    $mapeamento->registrarUso();
  }
}
