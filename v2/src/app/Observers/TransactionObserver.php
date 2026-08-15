<?php

namespace App\Observers;

use App\Models\Transaction;
use App\Models\TransactionMapping;

class TransactionObserver
{
  /**
   * Autoalimenta o De <> Para sempre que uma transação é salva com um
   * apelido (descricao) diferente da descrição bruta do banco.
   */
  public function saved(Transaction $t): void
  {
    if (!$t->wasRecentlyCreated && !$t->wasChanged('descricao')) {
      return;
    }

    TransactionMapping::learnFrom($t);
  }
}
