<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TransactionMapping;
use App\Models\Category;
use App\Models\Contact;
use App\Http\Requests\StoreTransactionMapping;
use Illuminate\Support\Facades\Auth;

class TransactionMappingsController extends Controller
{
  public function index(){
    $mappings = TransactionMapping::getMappings();

    return view('transaction_mappings/index', compact('mappings'));
  }

  public function new(){
    $categories = Category::getCategories();
    $contacts   = Contact::getCurrentUserContacts();

    return view('transaction_mappings/new', compact('categories', 'contacts'));
  }

  public function edit($id){
    $mapping = TransactionMapping::getMapping($id);
    $categories = Category::getCategories();
    $contacts   = Contact::getCurrentUserContacts();

    return view('transaction_mappings/edit', compact('mapping', 'categories', 'contacts'));
  }

  public function store(StoreTransactionMapping $request){
    $mapping = new TransactionMapping;
    $mapping->id_usuario   = Auth::id();
    $mapping->id_workspace = session('active_workspace_id');
    $mapping->origem       = 'manual';

    if ($request->input('id')){
      $mapping = TransactionMapping::getMapping($request->input('id'));
      if (!$mapping){
        // EXIBIR ERRO
      }
    }

    $mapping->padrao          = $request->input('padrao');
    $mapping->descricao_local = $request->input('descricao_local');
    $mapping->id_categoria    = $request->input('id_categoria') ?: null;
    $mapping->id_cliente      = $request->input('id_cliente') ?: null;
    $mapping->ativo           = $request->boolean('ativo', true);

    if ($mapping->save()){
      return redirect('/transaction-mappings')->with('success', 'Mapeamento salvo com sucesso');
    }
  }

  public function remove($id){
    $mapping = TransactionMapping::getMapping($id);
    if (!$mapping){
      // Exibir erro
    }

    if ($mapping->delete()){
      return redirect('/transaction-mappings')->with('success', 'Mapeamento excluído com sucesso');
    } else {
      return redirect('/transaction-mappings')->with('error', 'Não foi possível excluir o mapeamento');
    }
  }

  public function quickToggle(Request $request, $id){
    $mapping = TransactionMapping::getMapping($id);
    if (!$mapping){
      abort(404);
    }

    $mapping->ativo = !$mapping->ativo;
    $mapping->save();

    return redirect('/transaction-mappings')->with('success', 'Mapeamento atualizado com sucesso');
  }
}
