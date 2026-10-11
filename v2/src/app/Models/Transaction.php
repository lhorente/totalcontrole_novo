<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Scopes\CurrentUserScope;
use Carbon\Carbon;

class Transaction extends Model
{
  use SoftDeletes;
  use HasFactory;

  public $table = 'transacoes';

  const FILTER_SEM_CATEGORIA = 'sem_categoria';

  protected $fillable = [
    'id_categoria',
    'id_cliente',
    'id_caixa',
    'id_cartao',
    'id_cliente',
    'id_usuario',
    'descricao',
    'descricao_banco',
    'valor',
    'data',
    'data_compra',
    'data_pagamento',
    'data_recebimento',
    'tipo',
    'status',
    'chave_banco',
    'origem',
    'id_externo',
    'id_workspace',
    'ultimos_digitos_cartao',
  ];

  protected $dates = [
    'created_at',
    'updated_at',
    'deleted_at',
    'data',
    'data_compra',
  ];

  protected static function booted()
  {
      static::addGlobalScope(new CurrentUserScope);
  }

  public function category()
  {
      return $this->belongsTo('App\Models\Category','id_categoria');
  }

  public function contact()
  {
      return $this->belongsTo('App\Models\Contact','id_cliente');
  }

  public function wallet()
  {
      return $this->belongsTo('App\Models\Wallet','id_caixa');
  }

  public function credit_card()
  {
      return $this->belongsTo('App\Models\CreditCard','id_cartao');
  }

  public function workspace()
  {
      return $this->belongsTo(Workspace::class, 'id_workspace');
  }

  static function getTransactions(){
    return self::orderBy('data')->get();
  }

  static function getTransaction($id){
    return self::where('id',$id)->first();
  }

  static function getLendingsTotalsByPeriod(\DateTime $dateStart, \DateTime $dateEnd){
    // \DB::enableQueryLog();
    $lendings = [];

    $query = new Transaction;
    $query = $query->select('id_cliente','valor','data_recebimento');
    $query = $query->where('tipo','emprestimo');
    $query = $query->whereBetween('data',[$dateStart,$dateEnd]);
    // $query = $query->groupBy('id_cliente');
    $_lendings = $query->get();
    // dd(\DB::getQueryLog());

    if ($_lendings){
      foreach ($_lendings as $_lending){
        $id_cliente = $_lending->id_cliente;
        if (!isset($lendings[$id_cliente])){
          $lendings[$id_cliente] = new \StdClass;
          $lendings[$id_cliente]->total = 0;
          $lendings[$id_cliente]->total_pending = 0;
          if (isset($_lending->contact) && $_lending->contact){
            $lendings[$id_cliente]->contact_name = $_lending->contact->nome;
          } else {
            $lendings[$id_cliente]->contact_name = "";
          }
        }

        $lendings[$id_cliente]->total += $_lending->valor;
        if (!$_lending->data_recebimento){
          $lendings[$id_cliente]->total_pending += $_lending->valor;
        }
      }
    }



    return $lendings;
  }

  static function getLendingsNotPaid($id_cliente=null){
    // \DB::enableQueryLog();
    $query = new Transaction;

    if ($id_cliente){
      $query = $query->where('id_cliente',$id_cliente);
    }

    // $query = $query->select('id_cliente','valor','data_recebimento');
    $query = $query->where('tipo','emprestimo');
    $query = $query->whereDate('data', '<=', new \DateTime);
    $query = $query->where('data_recebimento',null);
    $query = $query->where('status','!=','cancelado');    
    $query = $query->orderBy('data','asc');

    $results = $query->get();
    return $results;
  }

  static function getLendingsNotPaidTotals(){
    $lendings = [];

    $_lendings = self::getLendingsNotPaid();

    if ($_lendings){
      foreach ($_lendings as $_lending){
        $id_cliente = $_lending->id_cliente;
        if (!isset($lendings[$id_cliente])){
          $lendings[$id_cliente] = new \StdClass;
          $lendings[$id_cliente]->total = 0;
          $lendings[$id_cliente]->total_pending = 0;
          if ($_lending->contact){
            $lendings[$id_cliente]->contact_name = $_lending->contact->nome;
          } else {
            $lendings[$id_cliente]->contact_name = "N/A";
          }
        }

        $lendings[$id_cliente]->total += $_lending->valor;
        if (!$_lending->data_recebimento){
          $lendings[$id_cliente]->total_pending += $_lending->valor;
        }
      }
    }

    return $lendings;
  }

  /**
   * Nome de exibição do estabelecimento de uma transação: o apelido local
   * (descricao) ou, na falta dele, a descrição do banco, sem o marcador de
   * parcela ("03/10") para que todas as parcelas caiam no mesmo lugar.
   */
  public static function estabelecimentoNome(?string $descricao, ?string $descricaoBanco = null): string
  {
    $nome = trim((string) ($descricao ?: $descricaoBanco));
    $nome = preg_replace('/\s*\b(PARC(ELA)?\.?\s*)?\d{1,3}\/\d{1,3}\b\s*/i', ' ', $nome);
    $nome = trim(preg_replace('/\s+/', ' ', $nome ?? ''));

    return $nome !== '' ? $nome : 'Sem descrição';
  }

  /**
   * Chave de agrupamento por estabelecimento (mesma normalização do De <> Para:
   * maiúsculas, sem acentos, espaços colapsados).
   */
  public static function estabelecimentoKey(?string $descricao, ?string $descricaoBanco = null): string
  {
    return TransactionMapping::normalize(self::estabelecimentoNome($descricao, $descricaoBanco));
  }

  static function search($filters, $orders = []){
    $query = (new Transaction())->newQuery();

    $year        = $filters['year']         ?? null;
    $month       = $filters['month']        ?? null;
    $id_categoria = $filters['id_categoria'] ?? null;
    $id_cartao   = $filters['id_cartao']    ?? null;
    $id_pessoa   = $filters['id_pessoa']    ?? null;
    $id_caixa    = $filters['id_caixa']     ?? null;
    $tipo        = $filters['tipo']         ?? null;
    $estabelecimento = $filters['estabelecimento'] ?? null;

    // Always exclude cancelled transactions
    $query->where('status', '!=', 'cancelado');

    if ($year) {
      $query->whereYear('data', $year);
    }

    if ($month) {
      $query->whereMonth('data', $month);
    }

    // Category filter: include subcategories, or "sem categoria" (no category assigned)
    if ($id_categoria === self::FILTER_SEM_CATEGORIA) {
      $query->whereNull('id_categoria');
    } elseif ($id_categoria) {
      $subcategoryIds = Category::where('parent_id', $id_categoria)->pluck('id')->toArray();
      $categoryIds = array_merge([$id_categoria], $subcategoryIds);
      $query->whereIn('id_categoria', $categoryIds);
    }

    // Card filter: include child virtual cards (rollup to the physical card's invoice)
    if ($id_cartao) {
      $childCardIds = CreditCard::where('id_cartao_pai', $id_cartao)->pluck('id')->toArray();
      $cardIds = array_merge([$id_cartao], $childCardIds);
      $query->whereIn('id_cartao', $cardIds);
    }

    // id_pessoa maps to id_cliente column
    if ($id_pessoa) {
      $query->where('id_cliente', $id_pessoa);
    }

    if ($id_caixa) {
      $query->where('id_caixa', $id_caixa);
    }

    if ($tipo) {
      $query->where('tipo', $tipo);
    }

    // Eager load relationships to avoid N+1
    $query->with(['category', 'contact', 'wallet', 'credit_card']);

    // Apply ordering
    if ($orders) {
      foreach ($orders as $field => $direction) {
        $query->orderBy($field, $direction);
      }
    } else {
      $query->orderBy('data_pagamento')->orderBy('data');
    }

    $result = $query->get();

    // Estabelecimento: comparado pela chave normalizada (sem acento/parcela), por isso filtrado em memória
    if ($estabelecimento) {
      $busca = TransactionMapping::normalize($estabelecimento);
      $result = $result->filter(fn ($t) => str_contains(self::estabelecimentoKey($t->descricao, $t->descricao_banco), $busca))->values();
    }

    return $result;
  }

  /**
   * Busca avançada da tela "Buscar lançamentos". Devolve o builder ainda sem
   * ordenação nem execução, para o controller agregar, listar e exportar a
   * partir da mesma consulta. Chaves de $f (todas opcionais):
   * id, q, de, ate, base (data|compra|pagamento), vmin, vmax, tipos[], cartao,
   * digitos, caixa, origem (manual|importacao|pluggy|smartpos), categoria,
   * pessoa, pagamento (pago|pendente), cancelados.
   */
  static function busca(array $f)
  {
    $query = (new Transaction())->newQuery()
      ->with(['category', 'contact', 'wallet', 'credit_card']);

    // ID identifica o lançamento sozinho (inclusive cancelado)
    if (!empty($f['id'])) {
      return $query->where('id', $f['id']);
    }

    if (empty($f['cancelados'])) {
      $query->where('status', '!=', 'cancelado');
    }

    $colData = self::colunaDataBusca($f['base'] ?? null);
    if (!empty($f['de'])) {
      $query->whereRaw("$colData >= ?", [$f['de']]);
    }
    if (!empty($f['ate'])) {
      $query->whereRaw("$colData <= ?", [$f['ate']]);
    }

    if (($f['q'] ?? '') !== '') {
      $like = '%' . addcslashes($f['q'], '%_\\') . '%';
      $query->where(function ($w) use ($like) {
        $w->where('descricao', 'like', $like)->orWhere('descricao_banco', 'like', $like);
      });
    }

    // valor é FLOAT: sem o ROUND, 87.40 (float) é maior que 87.40 e sairia de "até 87,40"
    if (isset($f['vmin'])) {
      $query->whereRaw('ROUND(valor, 2) >= ?', [$f['vmin']]);
    }
    if (isset($f['vmax'])) {
      $query->whereRaw('ROUND(valor, 2) <= ?', [$f['vmax']]);
    }

    if (!empty($f['tipos'])) {
      $query->whereIn('tipo', $f['tipos']);
    }

    // Cartão: inclui os cartões virtuais filhos (mesma regra do search())
    if (!empty($f['cartao'])) {
      $childCardIds = CreditCard::where('id_cartao_pai', $f['cartao'])->pluck('id')->toArray();
      $query->whereIn('id_cartao', array_merge([$f['cartao']], $childCardIds));
    }

    if (!empty($f['digitos'])) {
      $query->where('ultimos_digitos_cartao', $f['digitos']);
    }

    if (!empty($f['caixa'])) {
      $query->where('id_caixa', $f['caixa']);
    }

    // Importação CSV não grava origem, só a chave_banco
    switch ($f['origem'] ?? null) {
      case 'manual':     $query->whereNull('origem')->whereNull('chave_banco'); break;
      case 'importacao': $query->whereNull('origem')->whereNotNull('chave_banco'); break;
      case 'pluggy':     $query->where('origem', 'like', 'pluggy\_%'); break;
      case 'smartpos':   $query->where('origem', 'smartpos'); break;
    }

    // Categoria: inclui subcategorias, ou "sem categoria" (mesma regra do search())
    if (($f['categoria'] ?? null) === self::FILTER_SEM_CATEGORIA) {
      $query->whereNull('id_categoria');
    } elseif (!empty($f['categoria'])) {
      $subcategoryIds = Category::where('parent_id', $f['categoria'])->pluck('id')->toArray();
      $query->whereIn('id_categoria', array_merge([$f['categoria']], $subcategoryIds));
    }

    if (!empty($f['pessoa'])) {
      $query->where('id_cliente', $f['pessoa']);
    }

    if (($f['pagamento'] ?? null) === 'pago') {
      $query->whereNotNull('data_pagamento');
    } elseif (($f['pagamento'] ?? null) === 'pendente') {
      $query->whereNull('data_pagamento');
    }

    return $query;
  }

  /**
   * Expressão SQL da data usada no período da busca. "Compra" cai para a data do
   * lançamento quando data_compra não foi preenchida (só a Pluggy preenche).
   */
  static function colunaDataBusca($base)
  {
    return [
      'compra'    => 'COALESCE(data_compra, data)',
      'pagamento' => 'data_pagamento',
    ][$base] ?? 'data';
  }

  /**
   * Gera a chave_banco usada para identificar/deduplicar transações importadas.
   * Formato: dataBanco(Y-m-d)|descricao|valor|dataFatura(Y-m)
   * dataFatura usa apenas ano-mês para que chaves geradas em dias diferentes
   * do mesmo mês sejam consideradas idênticas.
   */
  public static function generateChaveBanco(string $dataBanco, string $descricao, float|string $valor, string $dataFatura): string
  {
    try {
      $dataBancoNorm = $dataBanco ? Carbon::parse($dataBanco)->format('Y-m-d') : '';
    } catch (\Throwable) {
      $dataBancoNorm = $dataBanco;
    }

    try {
      $dataFaturaNorm = $dataFatura ? Carbon::parse($dataFatura)->format('Y-m') : '';
    } catch (\Throwable) {
      $dataFaturaNorm = $dataFatura;
    }

    return md5($dataBancoNorm . '|' . $descricao . '|' . $valor . '|' . $dataFaturaNorm);
  }
}
