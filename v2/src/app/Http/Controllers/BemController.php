<?php

namespace App\Http\Controllers;

use App\Models\Bem;
use App\Http\Requests\StoreBem;

class BemController extends Controller
{
  public function index(){
    $bens = Bem::orderBy('tipo')->orderBy('nome')->get();

    return view('bens/index', compact('bens'));
  }

  public function create(){
    return view('bens/create');
  }

  public function store(StoreBem $request){
    $bem = new Bem;
    $bem->id_workspace = session('active_workspace_id');

    $this->fillBem($bem, $request);
    $bem->save();

    return redirect('/bens')->with('success', 'Bem cadastrado com sucesso');
  }

  public function edit($id){
    $bem = Bem::findOrFail($id);

    return view('bens/edit', compact('bem'));
  }

  public function update(StoreBem $request, $id){
    $bem = Bem::findOrFail($id);

    $this->fillBem($bem, $request);
    $bem->save();

    return redirect('/bens')->with('success', 'Bem salvo com sucesso');
  }

  public function destroy($id){
    $bem = Bem::findOrFail($id);

    if ($bem->delete()){
      return redirect('/bens')->with('success', 'Bem excluído com sucesso');
    } else {
      return redirect('/bens')->with('error', 'Não foi possível excluir o bem');
    }
  }

  private function fillBem(Bem $bem, StoreBem $request){
    $bem->tipo    = $request->input('tipo');
    $bem->nome    = $request->input('nome');
    $bem->detalhe = $request->input('detalhe') ?: null;
    $bem->ativo   = $request->boolean('ativo', true);
  }
}
