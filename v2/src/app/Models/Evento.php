<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Scopes\CurrentUserScope;

class Evento extends Model
{
  use SoftDeletes;
  use HasFactory;

  public $table = 'eventos';

  protected $fillable = [
    'id_workspace',
    'id_usuario',
    'id_cliente',
    'id_evento_pai',
    'modulo',
    'tipo',
    'titulo',
    'data_evento',
    'data_vencimento',
    'valor',
    'status',
    'metadata',
  ];

  protected $attributes = [
    'status'   => 'ativo',
    'metadata' => '{}',
  ];

  protected $casts = [
    'metadata' => 'array',
  ];

  protected $dates = [
    'created_at',
    'updated_at',
    'deleted_at',
    'data_evento',
    'data_vencimento',
  ];

  protected static function booted()
  {
      static::addGlobalScope(new CurrentUserScope);
  }

  public function documento()
  {
      return $this->hasOne(EventoDocumento::class, 'id_evento');
  }

  public function contact()
  {
      return $this->belongsTo(Contact::class, 'id_cliente');
  }

  public function pai()
  {
      return $this->belongsTo(self::class, 'id_evento_pai');
  }

  public function filhos()
  {
      return $this->hasMany(self::class, 'id_evento_pai');
  }

  public function workspace()
  {
      return $this->belongsTo(Workspace::class, 'id_workspace');
  }

  public function scopeModulo($query, $modulo)
  {
      return $query->where('modulo', $modulo);
  }

  public function scopeVencendoEm($query, $dias)
  {
      return $query->whereNotNull('data_vencimento')
                   ->where('data_vencimento', '<=', now()->addDays($dias));
  }
}
