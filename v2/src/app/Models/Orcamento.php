<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\CurrentUserScope;

class Orcamento extends Model
{
  public $table = 'orcamentos_anuais';

  const GRUPOS = [
    'necessidades' => 'Necessidades',
    'objetivos'    => 'Objetivos',
    'liberdade'    => 'Liberdade Individual',
  ];

  const METAS_PADRAO = [
    'necessidades' => 55,
    'objetivos'    => 20,
    'liberdade'    => 25,
  ];

  protected $fillable = [
    'id_workspace',
    'id_usuario',
    'ano',
    'renda',
    'metas',
  ];

  protected $casts = [
    'renda' => 'array',
    'metas' => 'array',
  ];

  protected static function booted()
  {
      static::addGlobalScope(new CurrentUserScope);
  }

  public function itens()
  {
      return $this->hasMany(OrcamentoItem::class, 'id_orcamento')->orderBy('ordem')->orderBy('id');
  }

  /** Renda prevista do mês (1–12). */
  public function rendaMes(int $mes): float
  {
      return (float) ($this->renda[$mes - 1] ?? 0);
  }

  public function rendaAno(): float
  {
      return array_sum(array_map('floatval', $this->renda ?? []));
  }
}
