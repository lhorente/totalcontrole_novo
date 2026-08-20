<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Scopes\CurrentUserScope;

class Bem extends Model
{
  use SoftDeletes;
  use HasFactory;

  public $table = 'bens';

  protected $fillable = [
    'id_workspace',
    'tipo',
    'nome',
    'detalhe',
    'ativo',
  ];

  protected $attributes = [
    'ativo' => true,
  ];

  protected $casts = [
    'ativo' => 'boolean',
  ];

  protected static function booted()
  {
      static::addGlobalScope(new CurrentUserScope);
  }

  public function workspace()
  {
      return $this->belongsTo(Workspace::class, 'id_workspace');
  }
}
