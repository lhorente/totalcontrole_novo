<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrcamentoSubitem extends Model
{
  public $table = 'orcamento_anual_subitens';

  public $timestamps = false;

  protected $fillable = [
    'id_item',
    'mes',
    'descricao',
    'valor',
  ];

  public function item()
  {
      return $this->belongsTo(OrcamentoItem::class, 'id_item');
  }
}
