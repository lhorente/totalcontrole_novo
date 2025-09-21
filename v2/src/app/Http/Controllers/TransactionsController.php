<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Http\Requests\StoreContact;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TransactionsController extends Controller
{
  public function index(Request $request){
    $month = $request->input('m',date('m'));
    $year = $request->input('y',date('Y'));
    $type = $request->input('t');

    $conditions = [];
    $conditions['id_usuario'] = Auth::id();

    if ($type){
      $conditions['tipo'] = $type;
    }

    $transactions = Transaction::whereYear('data','=',$year)->whereMonth('data','=',$month)->where($conditions)->orderBy('data_pagamento')->orderBy('data')->get();
    // $transactions = Transaction::where('id_usuario',Auth::id())->limit(10)->orderBy('data')->get();
    //
    // echo "<pre>";var_dump($transactions);
    //
    // exit;

    return view('transactions/index',compact('transactions'));
  }

  public function search(Request $request){
    $month = $request->input('m',date('m'));
    $year = $request->input('y',date('Y'));
    $type = $request->input('t');
    $id_cliente = $request->input('ct');

    $ps = $request->input('ps');
    if ($ps == 'lendings_not_paid'){
      $transactions = Transaction::getLendingsNotPaid($id_cliente);
    } else {
      $conditions = [];
      $conditions['id_usuario'] = Auth::id();

      if ($type){
        $conditions['tipo'] = $type;
      }

      $transactions = Transaction::search(['year'=>$year,'month'=>$month],['data_pagamento'=>'asc','data'=>'asc']);
    }

    $currentDateObj = new \DateTime;
    $currentDateObj->setDate($year,$month,1);
    $currentDateObj->setTime(0,0);

    $nextMonthObj = clone $currentDateObj;
    $nextMonthObj->add(new \DateInterval('P1M'));

    $beforeMonthObj = clone $currentDateObj;
    $beforeMonthObj->sub(new \DateInterval('P1M'));

    return view('transactions/search',compact('transactions','nextMonthObj','beforeMonthObj','year'));
  }

  public function saveModal(Request $request){
    return view('transactions/modal_save');
  }
}
