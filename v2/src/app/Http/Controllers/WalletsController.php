<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Wallet;
use App\Http\Requests\StoreWallet;
use Illuminate\Support\Facades\Auth;

class WalletsController extends Controller
{
  public function index(){
    $wallets = Wallet::getParentsWallets();

    return view('wallets/index',compact('wallets'));
  }

  public function new(){
    $wallets = Wallet::getParentsWallets();

    return view('wallets/new',compact('wallets'));
  }

  public function edit($id){
    $wallet = Wallet::getWallet($id);

    $wallets = Wallet::getParentsWallets();

    return view('wallets/edit',compact('wallet','wallets'));
  }

  // public function store(){
  public function store(StoreWallet $request){
    $wallet = new Wallet;
    $wallet->id_usuario = Auth::id();

    if ($request->input('id')){
      $wallet = Wallet::getWallet($request->input('id'));
      if (!$wallet){
        // EXIBIR ERRO
      }
    }

    $wallet->titulo = $request->input('titulo');
    $wallet->parent_id = $request->input('parent_id');
    $wallet->exibir_no_saldo = $request->input('exibir_no_saldo',0);

    if ($wallet->save()){
      return redirect('/wallets')->with('success', 'Carteira salva com sucesso');
    }
  }

  public function remove($id){
    $wallet = Wallet::getWallet($id);
    if (!$wallet){
      // Exibir erro
    }

    if ($wallet->delete()){
      return redirect('/wallets')->with('success', 'Carteira excluída com sucesso');
    } else {
      return redirect('/wallets')->with('error', 'Não foi possível excluir a cateira');
    }
  }

  public function dashboard(){
    $wallets = Wallet::getParentsWallets();

    return view('wallets/dashboard',compact('wallets'));
  }
}
