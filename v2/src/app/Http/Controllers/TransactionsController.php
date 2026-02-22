<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Category;
use App\Models\Contact;
use App\Models\CreditCard;
use App\Models\Wallet;
use App\Http\Requests\StoreContact;
use App\Http\Requests\ImportCsvRequest;
use App\Services\CsvParserService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TransactionsController extends Controller
{
  public function index(Request $request){
    $month = $request->input('m',date('m'));
    $year = $request->input('y',date('Y'));
    $type = $request->input('t');
    $id_categoria = $request->input('categoria');
    $id_cartao = $request->input('cartao');
    $id_pessoa = $request->input('pessoa');
    $id_caixa = $request->input('caixa');

    $filters = [
      'year' => $year,
      'month' => $month
    ];

    if ($type){
      $filters['tipo'] = $type;
    }
    if ($id_categoria){
      $filters['id_categoria'] = $id_categoria;
    }
    if ($id_cartao){
      $filters['id_cartao'] = $id_cartao;
    }
    if ($id_pessoa){
      $filters['id_pessoa'] = $id_pessoa;
    }
    if ($id_caixa){
      $filters['id_caixa'] = $id_caixa;
    }

    $transactions = Transaction::search($filters, ['data_pagamento'=>'asc','data'=>'asc']);

    return view('transactions/index',compact('transactions'));
  }

  public function search(Request $request){
    $month = $request->input('m',date('m'));
    $year = $request->input('y',date('Y'));
    $type = $request->input('t');
    $id_cliente = $request->input('ct');
    $id_categoria = $request->input('categoria');
    $id_cartao = $request->input('cartao');
    $id_pessoa = $request->input('pessoa');
    $id_caixa = $request->input('caixa');

    $ps = $request->input('ps');
    if ($ps == 'lendings_not_paid'){
      $transactions = Transaction::getLendingsNotPaid($id_cliente);
    } else {
      $filters = [
        'year' => $year,
        'month' => $month
      ];

      if ($type){
        $filters['tipo'] = $type;
      }
      if ($id_categoria){
        $filters['id_categoria'] = $id_categoria;
      }
      if ($id_cartao){
        $filters['id_cartao'] = $id_cartao;
      }
      if ($id_pessoa){
        $filters['id_pessoa'] = $id_pessoa;
      }
      if ($id_caixa){
        $filters['id_caixa'] = $id_caixa;
      }

      $transactions = Transaction::search($filters, ['data_pagamento'=>'asc','data'=>'asc']);
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

  public function import()
  {
    $cartoes = CreditCard::where('id_usuario', Auth::id())->get();

    return view('transactions/import', compact('cartoes'));
  }

  public function importPreview(ImportCsvRequest $request)
  {
    $file = $request->file('file');
    $filePath = $file->getRealPath();

    $csvParser = new CsvParserService();
    $transactions = $csvParser->toPreviewArray($filePath);
    $dataFatura = $request->input('data_fatura');
    $idCartao = $request->input('id_cartao');
    $dataFaturaCarbon = Carbon::parse($dataFatura);

    // Verifica duplicatas para cada transação
    $transactions = $transactions->map(function($transaction) use ($dataFatura, $idCartao, $dataFaturaCarbon) {
      $dataBanco = $transaction['data_banco'] ?? '';
      $descricao = $transaction['descricao_banco'] ?? '';
      $valor = $transaction['valor'] ?? 0;
      $chaveBanco = md5($dataBanco . '|' . $descricao . '|' . $valor . '|' . $dataFatura);
      
      // Verifica se já existe pela chave_banco
      $isDuplicada = Transaction::where('chave_banco', $chaveBanco)
                                  ->where('id_usuario', Auth::id())
                                  ->exists();

      // Verifica se existe transação com mesmo valor, mesmo cartão, no mesmo mês
      $valorArredondado = round($valor, 2);
      $transacaoSimilar = !$isDuplicada ? Transaction::where('id_usuario', Auth::id())
                                  ->where('id_cartao', $idCartao)
                                  ->whereRaw('ROUND(valor, 2) = ?', [$valorArredondado])
                                  ->whereYear('data', $dataFaturaCarbon->year)
                                  ->whereMonth('data', $dataFaturaCarbon->month)
                                  ->first() : null;
      $isDuplicadaPorValor = $transacaoSimilar !== null;
      
      $transaction['is_duplicada'] = $isDuplicada;
      $transaction['is_duplicada_por_valor'] = $isDuplicadaPorValor;
      $transaction['duplicada_por_valor_descricao'] = $isDuplicadaPorValor
          ? ($transacaoSimilar->descricao ?: $transacaoSimilar->descricao_banco)
          : null;
      $transaction['chave_banco'] = $chaveBanco;
      
      return $transaction;
    });

    $categorias = Category::where('id_usuario', Auth::id())->get();
    $pessoas = Contact::where('id_usuario', Auth::id())->get();

    return view('transactions/importPreview', [
      'transactions' => $transactions,
      'id_cartao' => $request->input('id_cartao'),
      'data_fatura' => $request->input('data_fatura'),
      'categorias' => $categorias,
      'pessoas' => $pessoas,
    ]);
  }

  public function importStore(Request $request)
  {
    $transacoes = $request->input('transacoes', []);
    $dataFatura = $request->input('data_fatura');
    $count = 0;
    $duplicadas = 0;

    // Busca a caixa padrão do usuário (exibir_no_saldo = 1)
    $caixaPadrao = Wallet::where('id_usuario', Auth::id())
                         ->where('exibir_no_saldo', 1)
                         ->first();
    
    $idCaixa = $caixaPadrao ? $caixaPadrao->id : null;

    DB::transaction(function () use ($transacoes, $dataFatura, $idCaixa, &$count, &$duplicadas) {
      foreach ($transacoes as $item) {
        // Importa apenas se o checkbox estiver marcado
        if (!isset($item['importar']) || $item['importar'] != '1') {
          continue;
        }

        // Usa a chave_banco que já foi calculada na preview
        $chaveBanco = $item['chave_banco'] ?? null;
        
        // Se não tiver chave, gera uma nova (fallback)
        if (!$chaveBanco) {
          $dataBanco = $item['data_banco'] ?? '';
          $descricao = $item['descricao_banco'] ?? '';
          $valor = $item['valor'] ?? 0;
          $chaveBanco = md5($dataBanco . '|' . $descricao . '|' . $valor . '|' . $dataFatura);
        }

        // Verifica se já existe uma transação com essa chave
        $existe = Transaction::where('chave_banco', $chaveBanco)
                             ->where('id_usuario', Auth::id())
                             ->exists();

        if ($existe) {
          $duplicadas++;
          continue;
        }

        Transaction::create([
          'id_categoria' => $item['id_categoria'] ?? null,
          'descricao_banco' => $item['descricao_banco'] ?? '',
          'descricao' => $item['descricao'] ?? '',
          'valor' => $item['valor'] ?? 0,
          'data' => $dataFatura ?? now(),
          'id_cartao' => $item['id_cartao'] ?? null,
          'id_caixa' => $idCaixa,
          'tipo' => $item['tipo'] ?? 'despesa',
          'id_pessoa' => $item['id_pessoa'] ?? null,
          'id_usuario' => Auth::id(),
          'chave_banco' => $chaveBanco,
        ]);
        $count++;
      }
    });

    $mensagem = "{$count} transações importadas com sucesso.";
    if ($duplicadas > 0) {
      $mensagem .= " {$duplicadas} transações duplicadas foram ignoradas.";
    }

    return redirect()->route('transactions.index')
      ->with('success', $mensagem);
  }

}
