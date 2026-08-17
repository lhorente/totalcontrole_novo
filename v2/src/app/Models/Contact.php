<?php

namespace App\Models;

use App\Models\Scopes\CurrentUserScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
{
    use SoftDeletes;
    use HasFactory;

    public $table = 'contatos';

    protected $fillable = [
        'id_workspace',
        'id_usuario',
        'nome',
        'tipo',
        'status',
        'documento',
        'email',
        'telefone',
        'observacoes',
    ];

    protected static function booted()
    {
        static::addGlobalScope(new CurrentUserScope);
    }

    public function fornecedor()
    {
        return $this->hasOne(ContatoFornecedor::class, 'id_contato');
    }

    public function clienteComercial()
    {
        return $this->hasOne(ContatoCliente::class, 'id_contato');
    }

    static function getCurrentUserContacts(){
      return self::orderBy('nome')->get();
    }

    static function getContact($id){
      return self::where('id',$id)->first();
    }
}
