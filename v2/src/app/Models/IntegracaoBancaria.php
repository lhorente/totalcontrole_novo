<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class IntegracaoBancaria extends Model
{
  use SoftDeletes;
  use HasFactory;

  public $table = 'integracoes_bancarias';

  // Sem CurrentUserScope de propósito: o job de sincronização roda via
  // artisan (sem sessão HTTP/workspace ativo), então o filtro por
  // id_workspace é feito explicitamente por quem consulta este model
  // (ver PluggyTransactionSyncService), não por um global scope.

  protected $fillable = [
    'id_workspace',
    'id_cartao',
    'provider',
    'pluggy_item_id',
    'status',
    'last_sync_at',
    'ultimo_erro',
  ];

  protected $dates = [
    'created_at',
    'updated_at',
    'deleted_at',
    'last_sync_at',
  ];

  public function workspace()
  {
    return $this->belongsTo(Workspace::class, 'id_workspace');
  }

  public function creditCard()
  {
    return $this->belongsTo(CreditCard::class, 'id_cartao');
  }
}
