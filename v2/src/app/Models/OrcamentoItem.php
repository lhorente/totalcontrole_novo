<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Scopes\CurrentUserScope;

class OrcamentoItem extends Model
{
  use SoftDeletes;

  public $table = 'orcamento_anual_itens';

  const TIPOS = [
    'fixo'      => 'Todo mês, mesmo valor',
    'mensal'    => 'Mês a mês',
    'detalhado' => 'Detalhado em sub-itens',
  ];

  const DISTRIBUICOES = [
    'no_mes'  => 'No mês de cada sub-item',
    'reserva' => 'Reservar igual todo mês',
  ];

  protected $fillable = [
    'id_orcamento',
    'id_workspace',
    'grupo',
    'nome',
    'id_categoria',
    'padrao_descricao',
    'padrao_normalizado',
    'tipo',
    'valor_fixo',
    'distribuicao',
    'valores',
    'ordem',
  ];

  protected $casts = [
    'valores' => 'array',
  ];

  protected static function booted()
  {
      static::addGlobalScope(new CurrentUserScope);

      static::saving(function (self $item) {
          $item->padrao_descricao   = trim((string) $item->padrao_descricao) ?: null;
          $item->padrao_normalizado = $item->padrao_descricao ? TransactionMapping::normalize($item->padrao_descricao) : null;
      });
  }

  public function orcamento()
  {
      return $this->belongsTo(Orcamento::class, 'id_orcamento');
  }

  public function category()
  {
      return $this->belongsTo(Category::class, 'id_categoria');
  }

  public function subitens()
  {
      return $this->hasMany(OrcamentoSubitem::class, 'id_item')->orderBy('mes')->orderBy('id');
  }

  /** Planejado (provisão) do mês, 1–12. */
  public function valorMes(int $mes): float
  {
      return (float) ($this->valores[$mes - 1] ?? 0);
  }

  public function totalAno(): float
  {
      return array_sum(array_map('floatval', $this->valores ?? []));
  }

  /**
   * Recalcula `valores` (12 meses) a partir do tipo do item: valor fixo, valores
   * digitados mês a mês ou soma dos sub-itens (no mês de cada um, ou dividida por 12).
   */
  public function recalcularValores(array $valoresMensais = []): void
  {
      if ($this->tipo === 'fixo') {
          $this->valores = array_fill(0, 12, round((float) $this->valor_fixo, 2));
      } elseif ($this->tipo === 'detalhado') {
          $porMes = array_fill(0, 12, 0.0);
          foreach ($this->subitens as $sub) {
              $porMes[$sub->mes - 1] += (float) $sub->valor;
          }
          $this->valores = $this->distribuicao === 'reserva'
              ? array_fill(0, 12, round(array_sum($porMes) / 12, 2))
              : array_map(fn($v) => round($v, 2), $porMes);
      } else {
          $this->valores = array_map(fn($i) => round((float) ($valoresMensais[$i] ?? 0), 2), range(0, 11));
      }
  }
}
