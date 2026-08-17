<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContatoCliente extends Model
{
  public $table = 'contatos_clientes';

  protected $primaryKey = 'id_contato';

  public $incrementing = false;

  public $timestamps = false;

  protected $fillable = [
    'id_contato',
    'valor_hora',
    'forma_cobranca',
    'contrato_url',
    'observacoes',
  ];

  public function contato()
  {
      return $this->belongsTo(Contact::class, 'id_contato');
  }
}
