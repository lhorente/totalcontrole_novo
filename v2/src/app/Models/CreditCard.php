<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class CreditCard extends Model
{
  use SoftDeletes;
  use HasFactory;

  public $table = 'cartoes';

  static function getCreditCards(){
    return self::where('id_usuario',Auth::id())->orderBy('descricao')->get();
  }

  static function getCreditCard($id){
    return self::where('id_usuario',Auth::id())->where('id',$id)->first();
  }

  public function parent()
  {
    return $this->belongsTo(CreditCard::class, 'id_cartao_pai');
  }

  public function children()
  {
    return $this->hasMany(CreditCard::class, 'id_cartao_pai');
  }

  public function isVirtual()
  {
    return !is_null($this->id_cartao_pai);
  }

  public function defaultCategory()
  {
    return $this->belongsTo(Category::class, 'id_categoria_padrao');
  }

  /**
   * Calcula em qual fatura uma compra feita em $dataCompra vai cair, a partir
   * do dia de fechamento e do dia de vencimento cadastrados no cartão -- não
   * dá pra assumir que a fatura sempre leva o nome do mês do fechamento:
   * quando o vencimento cai no mês seguinte ao fechamento (ex.: fecha dia 28,
   * vence dia 5 do mês seguinte), a fatura também leva o nome do mês
   * seguinte. Só o dia de fechamento sozinho reproduz certo o caso da Nubank
   * (fecha e vence dia 4, mesmo mês) mas erra o do Bradesco (fecha dia 28,
   * vence só no mês seguinte).
   *
   * A data retornada usa sempre o dia de vencimento (não o dia da compra) --
   * é a data que a fatura de fato vence, independente de em que dia dentro
   * do ciclo a compra caiu.
   */
  public function calcularDataFatura(\Carbon\Carbon $dataCompra): \Carbon\Carbon
  {
    $diaFechamento = (int) $this->dia_fechamento;
    $diaVencimento = (int) $this->dia_vencimento;

    $fechamento = $dataCompra->copy()->startOfMonth();
    if ($dataCompra->day >= $diaFechamento) {
      $fechamento->addMonthNoOverflow();
    }

    $fatura = $fechamento->copy();
    if ($diaVencimento < $diaFechamento) {
      $fatura->addMonthNoOverflow();
    }

    return $fatura->day(min($diaVencimento, $fatura->daysInMonth));
  }
}
