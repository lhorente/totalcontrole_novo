<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Scopes\CurrentUserScope;

class Transaction extends Model
{
  use SoftDeletes;
  use HasFactory;

  public $table = 'transacoes';

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
    'data_pagamento',
    'data_recebimento',
    'tipo',
    'status',
    'chave_banco',
    'id_workspace',
  ];

  protected $dates = [
    'created_at',
    'updated_at',
    'deleted_at',
    'data'
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
          $lendings[$id_cliente]->contact_name = $_lending->contact->nome;
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

  static function search($filters, $orders = []){
    $query = (new Transaction())->newQuery();

    $year        = $filters['year']         ?? null;
    $month       = $filters['month']        ?? null;
    $id_categoria = $filters['id_categoria'] ?? null;
    $id_cartao   = $filters['id_cartao']    ?? null;
    $id_pessoa   = $filters['id_pessoa']    ?? null;
    $id_caixa    = $filters['id_caixa']     ?? null;
    $tipo        = $filters['tipo']         ?? null;

    // Always exclude cancelled transactions
    $query->where('status', '!=', 'cancelado');

    if ($year) {
      $query->whereYear('data', $year);
    }

    if ($month) {
      $query->whereMonth('data', $month);
    }

    // Category filter: include subcategories
    if ($id_categoria) {
      $subcategoryIds = Category::where('parent_id', $id_categoria)->pluck('id')->toArray();
      $categoryIds = array_merge([$id_categoria], $subcategoryIds);
      $query->whereIn('id_categoria', $categoryIds);
    }

    if ($id_cartao) {
      $query->where('id_cartao', $id_cartao);
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

    return $query->get();
  }
}
