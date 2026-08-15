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

  /**
   * Cria ou reforça o mapeamento a partir de uma transação já salva com um
   * apelido (descricao) diferente da descrição bruta do banco. Mapeamentos
   * manuais (criados pela tela De <> Para) não são sobrescritos — apenas
   * reforçados (contagem de uso). Usado tanto pelo TransactionObserver
   * (tempo real) quanto pelo backfill retroativo.
   */
  public static function learnFrom(Transaction $t): ?self
  {
    if (empty($t->descricao_banco) || empty($t->descricao) || $t->descricao === $t->descricao_banco) {
      return null;
    }

    $padraoNormalizado = self::normalize($t->descricao_banco);

    $mapeamento = self::withoutGlobalScope(CurrentUserScope::class)
      ->where('id_workspace', $t->id_workspace)
      ->where('padrao_normalizado', $padraoNormalizado)
      ->first();

    if ($mapeamento) {
      if ($mapeamento->origem === 'automatico') {
        $mapeamento->descricao_local = $t->descricao;
        $mapeamento->id_categoria    = $t->id_categoria;
        $mapeamento->save();
      }
    } else {
      $mapeamento = self::create([
        'id_workspace'    => $t->id_workspace,
        'id_usuario'      => $t->id_usuario,
        'padrao'          => $t->descricao_banco,
        'descricao_local' => $t->descricao,
        'id_categoria'    => $t->id_categoria,
        'origem'          => 'automatico',
        'ativo'           => true,
      ]);
    }

    $mapeamento->registrarUso();

    return $mapeamento;
  }
}
