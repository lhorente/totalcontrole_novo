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
}
