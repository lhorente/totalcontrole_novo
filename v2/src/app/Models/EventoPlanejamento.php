<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventoPlanejamento extends Model
{
  public $table = 'eventos_planejamento';

  protected $primaryKey = 'id_evento';

  public $incrementing = false;

  public $timestamps = false;

  protected $fillable = [
    'id_evento',
    'id_bem',
    'categoria',
    'prioridade',
    'recorrente',
    'recorrencia_intervalo',
    'recorrencia_unidade',
    'data_conclusao',
    'valor_pago',
    'id_transacao',
    'observacoes',
  ];

  protected $attributes = [
    'prioridade' => 'necessidade',
    'recorrente' => false,
  ];

  protected $casts = [
    'recorrente' => 'boolean',
  ];

  protected $dates = [
    'data_conclusao',
  ];

  public function evento()
  {
      return $this->belongsTo(Evento::class, 'id_evento');
  }

  public function bem()
  {
      return $this->belongsTo(Bem::class, 'id_bem');
  }

  public function transacao()
  {
      return $this->belongsTo(Transaction::class, 'id_transacao');
  }
}
