<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CreditCard;
use App\Http\Requests\StoreCreditCard;
use Illuminate\Support\Facades\Auth;

class CreditCardsController extends Controller
{
  public function index(){
    $credit_cards = CreditCard::getCreditCards();

    return view('credit_cards/index',compact('credit_cards'));
  }

  public function new(){
    $parent_cards = $this->getPossibleParentCards();
    return view('credit_cards/new', compact('parent_cards'));
  }

  public function edit($id){
    $credit_card = CreditCard::getCreditCard($id);
    $parent_cards = $this->getPossibleParentCards($id);
    return view('credit_cards/edit',compact('credit_card', 'parent_cards'));
  }

  private function getPossibleParentCards($excludeId = null){
    return CreditCard::where('id_usuario', Auth::id())
                      ->where('status', 'ativo')
                      ->whereNull('id_cartao_pai')
                      ->when($excludeId, fn($query) => $query->where('id', '!=', $excludeId))
                      ->orderBy('descricao')
                      ->get();
  }

  // public function store(){
  public function store(StoreCreditCard $request){
    $credit_card = new CreditCard;
    $credit_card->id_usuario = Auth::id();

    if ($request->input('id')){
      $credit_card = CreditCard::getCreditCard($request->input('id'));
      if (!$credit_card){
        // EXIBIR ERRO
      }
    }

    $credit_card->descricao = $request->input('descricao');
    $credit_card->dia_vencimento = $request->input('dia_vencimento');
    $credit_card->id_cartao_pai = $request->input('id_cartao_pai') ?: null;
    $credit_card->ultimos_digitos = $request->input('ultimos_digitos') ?: null;

    if ($credit_card->save()){
      return redirect('/credit_cards')->with('success', 'Cartão de crédito salvo com sucesso');
      // redirect()->back()->withSuccess('Cartão de crédito salvo com sucesso');
    }

    // die("OK");
    // $validated = $request->validated();
    //
    // var_dump($credit_card);
  }

  public function remove($id){
    $credit_card = CreditCard::getCreditCard($id);
    if (!$credit_card){
      // Exibir erro
    }

    if ($credit_card->delete()){
      return redirect('/credit_cards')->with('success', 'Cartão de crédito excluído com sucesso');
    } else {
      return redirect('/credit_cards')->with('error', 'Não foi possível excluir o contato');
    }
  }
}
