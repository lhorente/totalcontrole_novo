<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Scopes\CurrentUserScope;
use Illuminate\Support\Str;

class TransactionMapping extends Model
{
  use SoftDeletes;
  use HasFactory;

  public $table = 'de_para_transacoes';

  protected $fillable = [
    'id_workspace',
    'id_usuario',
    'id_categoria',
    'id_cliente',
    'padrao',
    'padrao_normalizado',
    'descricao_local',
    'origem',
    'ocorrencias',
    'ultima_utilizacao',
    'ativo',
  ];

  protected $dates = [
    'created_at',
    'updated_at',
    'deleted_at',
    'ultima_utilizacao',
  ];

  protected static function booted()
  {
      static::addGlobalScope(new CurrentUserScope);

      static::saving(function (TransactionMapping $mapeamento) {
        if ($mapeamento->isDirty('padrao')) {
          $mapeamento->padrao_normalizado = self::normalize($mapeamento->padrao);
        }
      });
  }

  public function category()
  {
      return $this->belongsTo('App\Models\Category','id_categoria');
  }

  public function contact()
  {
      return $this->belongsTo('App\Models\Contact','id_cliente');
  }

  public function workspace()
  {
      return $this->belongsTo(Workspace::class, 'id_workspace');
  }

  static function getMappings(){
    return self::with('category')->orderByDesc('ocorrencias')->orderBy('descricao_local')->get();
  }

  static function getMapping($id){
    return self::where('id',$id)->first();
  }

  /**
   * Normaliza uma descrição para comparação: maiúsculas, sem acento, espaços colapsados.
   */
  public static function normalize(string $descricao): string
  {
    $texto = Str::ascii($descricao);
    $texto = mb_strtoupper(trim($texto));
    $texto = preg_replace('/\s+/', ' ', $texto);

    return $texto ?? '';
  }

  /**
   * Encontra o mapeamento mais específico (padrão mais longo) cujo padrão
   * esteja contido na descrição do banco informada.
   */
  public static function matchFor(string $descricaoBanco, ?int $workspaceId = null): ?self
  {
    $normalizada = self::normalize($descricaoBanco);

    if ($normalizada === '') {
      return null;
    }

    $query = $workspaceId
      ? self::withoutGlobalScope(CurrentUserScope::class)->where('id_workspace', $workspaceId)
      : self::query();

    return $query->where('ativo', true)
      ->get()
      ->filter(fn (self $mapeamento) => str_contains($normalizada, $mapeamento->padrao_normalizado))
      ->sortByDesc(fn (self $mapeamento) => strlen($mapeamento->padrao_normalizado))
      ->first();
  }

  /**
   * Registra que o mapeamento foi aplicado (importação ou autoalimentação).
   */
  public function registrarUso(): void
  {
    $this->increment('ocorrencias', 1, ['ultima_utilizacao' => now()]);
  }
}
