<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventoDocumento extends Model
{
  public $table = 'eventos_documentos';

  protected $primaryKey = 'id_evento';

  public $incrementing = false;

  public $timestamps = false;

  protected $fillable = [
    'id_evento',
    'numero_documento',
    'emissor',
    'arquivo_url',
    'observacoes',
  ];

  public function evento()
  {
      return $this->belongsTo(Evento::class, 'id_evento');
  }
}
