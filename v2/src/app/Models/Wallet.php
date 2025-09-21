<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Wallet extends Model
{
  use SoftDeletes;
  use HasFactory;

  public $table = 'caixas';

  static function getWallets(){
    return self::where('id_usuario',Auth::id())->orderBy('titulo')->get();
  }

  static function getParentsWallets(){
    $wallets = self::where('id_usuario',Auth::id())->whereNull('parent_id')->orderBy('titulo')->get();

    if ($wallets){
      foreach ($wallets as &$wallet){
        if($wallet->childs){
          foreach ($wallet->childs as $walletChild){
            $wallet->saldo += $walletChild->saldo;
          }
        }
      }
    }

    return $wallets;
  }

  static function getWallet($id){
    return self::where('id_usuario',Auth::id())->where('id',$id)->first();
  }

  public function parent()
  {
      return $this->belongsTo('App\Models\Wallet', 'parent_id');
  }

  public function childs()
  {
      return $this->hasMany('App\Models\Wallet', 'parent_id');
  }
}
