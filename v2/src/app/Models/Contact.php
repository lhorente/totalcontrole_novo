<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Contact extends Model
{
    use SoftDeletes;
    use HasFactory;

    public $table = 'clientes';

    static function getCurrentUserContacts(){
      return self::where('id_usuario',Auth::id())->orderBy('nome')->get();
    }

    static function getContact($id){
      return self::where('id_usuario',Auth::id())->where('id',$id)->first();
    }
}
