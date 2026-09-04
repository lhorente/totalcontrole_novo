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
}
