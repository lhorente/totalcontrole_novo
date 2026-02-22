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

  static function search($filters,$orders){
    $query = new Transaction();

    $year = isset($filters['year']) ? $filters['year'] : null;
    $month = isset($filters['month']) ? $filters['month'] : null;
    $id_categoria = isset($filters['id_categoria']) ? $filters['id_categoria'] : null;
    $id_cartao = isset($filters['id_cartao']) ? $filters['id_cartao'] : null;
    $id_pessoa = isset($filters['id_pessoa']) ? $filters['id_pessoa'] : null;
    $id_caixa = isset($filters['id_caixa']) ? $filters['id_caixa'] : null;
    $tipo = isset($filters['tipo']) ? $filters['tipo'] : null;

    // Apply ordering
    if ($orders){
      foreach($orders as $field => $direction){
        $query = $query->orderBy($field, $direction);
      }
    } else {
      $query = $query->orderBy('data_pagamento')->orderBy('data');
    }

    return $query->get();
  }
}
