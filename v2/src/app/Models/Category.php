<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Category extends Model
{
    use SoftDeletes;
    use HasFactory;

    public $table = 'categorias';

    static function getCategories(){
      return self::where('id_workspace', session('active_workspace_id'))->where('status','a')->orderBy('nome')->get();
    }

    static function getCategory($id){
      return self::where('id_workspace', session('active_workspace_id'))->where('id', $id)->first();
    }

}
