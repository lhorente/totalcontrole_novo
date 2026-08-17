<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContatoFornecedor extends Model
{
  public $table = 'contatos_fornecedores';

  protected $primaryKey = 'id_contato';

  public $incrementing = false;

  public $timestamps = false;

  protected $fillable = [
    'id_contato',
    'tipo_servico',
    'razao_social',
    'cnpj',
    'contato_responsavel',
    'forma_pagamento_preferida',
    'observacoes',
  ];

  public function contato()
  {
      return $this->belongsTo(Contact::class, 'id_contato');
  }
}
