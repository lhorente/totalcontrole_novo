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

  public function month(Request $request, $year = null, $month = null){
    $year  = $year  ?? date('Y');
    $month = $month ?? date('n');
    $type = $request->input('t');
    $id_cliente = $request->input('ct');
    $id_categoria = $request->input('categoria');
    $id_cartao = $request->input('cartao');
    $id_pessoa = $request->input('pessoa');
    $id_caixa = $request->input('caixa');

    // Load selected filter objects
    $categoria = $id_categoria ? Category::find($id_categoria) : null;
    $cartao    = $id_cartao   ? CreditCard::find($id_cartao)  : null;
    $pessoa    = $id_pessoa   ? Contact::find($id_pessoa)     : null;
    $caixa     = $id_caixa   ? Wallet::where('id', $id_caixa)->where('id_usuario', Auth::id())->first() : null;

    // Load filter option lists
    $categorias = Category::where('id_usuario', Auth::id())
                           ->where('status', 'a')
                           ->orderBy('nome')
                           ->get();

    $cartoes = CreditCard::where('id_usuario', Auth::id())
                          ->orderBy('descricao')
                          ->get();

    $pessoas = Contact::where('id_usuario', Auth::id())
                       ->orderBy('nome')
                       ->get();

    $caixas = Wallet::where('id_usuario', Auth::id())
                     ->orderBy('titulo')
                     ->get();

    $ps = $request->input('ps');
    if ($ps == 'lendings_not_paid'){
      $transactions = Transaction::getLendingsNotPaid($id_cliente);
    } else {
      $filters = [
        'year'  => $year,
        'month' => $month,
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

    // Totals
    $total_a_pagar    = $transactions->where('tipo', 'despesa')->whereNull('data_pagamento')->sum('valor');
    $total_pago       = $transactions->where('tipo', 'despesa')->whereNotNull('data_pagamento')->sum('valor');
    $total_a_receber  = $transactions->where('tipo', 'lucro')->whereNull('data_pagamento')->sum('valor');
    $total_recebido   = $transactions->where('tipo', 'lucro')->whereNotNull('data_pagamento')->sum('valor');

    $currentDateObj = new \DateTime;
    $currentDateObj->setDate($year, $month ?: date('m'), 1);
    $currentDateObj->setTime(0, 0);

    $nextMonthObj = clone $currentDateObj;
    $nextMonthObj->add(new \DateInterval('P1M'));

    $beforeMonthObj = clone $currentDateObj;
    $beforeMonthObj->sub(new \DateInterval('P1M'));

    $nav_route = 'transactions.month';

    return view('transactions/month', compact(
      'transactions',
      'nextMonthObj',
      'beforeMonthObj',
      'year',
      'month',
      'type',
      'categoria',
      'categorias',
      'cartao',
      'cartoes',
      'pessoa',
      'pessoas',
      'caixa',
      'caixas',
      'total_a_pagar',
      'total_pago',
      'total_a_receber',
      'total_recebido',
      'nav_route'
    ));
  }

  public function search(Request $request){
    $month = $request->input('m');
    $year = $request->input('y', date('Y'));
    $type = $request->input('t');
    $id_cliente = $request->input('ct');
    $id_categoria = $request->input('categoria');
    $id_cartao = $request->input('cartao');
    $id_pessoa = $request->input('pessoa');
    $id_caixa = $request->input('caixa');

    // Load selected filter objects
    $categoria = $id_categoria ? Category::find($id_categoria) : null;
    $cartao    = $id_cartao   ? CreditCard::find($id_cartao)  : null;
    $pessoa    = $id_pessoa   ? Contact::find($id_pessoa)     : null;
    $caixa     = $id_caixa   ? Wallet::where('id', $id_caixa)->where('id_usuario', Auth::id())->first() : null;

    // Load filter option lists
    $categorias = Category::where('id_usuario', Auth::id())
                           ->where('status', 'a')
                           ->orderBy('nome')
                           ->get();

    $cartoes = CreditCard::where('id_usuario', Auth::id())
                          ->orderBy('descricao')
                          ->get();

    $pessoas = Contact::where('id_usuario', Auth::id())
                       ->orderBy('nome')
                       ->get();

    $caixas = Wallet::where('id_usuario', Auth::id())
                     ->orderBy('titulo')
                     ->get();

    $ps = $request->input('ps');
    if ($ps == 'lendings_not_paid'){
      $transactions = Transaction::getLendingsNotPaid($id_cliente);
    } else {
      $filters = [
        'year'  => $year,
        'month' => $month,
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

    // Totals
    $total_a_pagar    = $transactions->where('tipo', 'despesa')->whereNull('data_pagamento')->sum('valor');
    $total_pago       = $transactions->where('tipo', 'despesa')->whereNotNull('data_pagamento')->sum('valor');
    $total_a_receber  = $transactions->where('tipo', 'lucro')->whereNull('data_pagamento')->sum('valor');
    $total_recebido   = $transactions->where('tipo', 'lucro')->whereNotNull('data_pagamento')->sum('valor');

    $currentDateObj = new \DateTime;
    $currentDateObj->setDate($year, $month ?: date('m'), 1);
    $currentDateObj->setTime(0, 0);

    $nextMonthObj = clone $currentDateObj;
    $nextMonthObj->add(new \DateInterval('P1M'));

    $beforeMonthObj = clone $currentDateObj;
    $beforeMonthObj->sub(new \DateInterval('P1M'));

    $nav_route = 'transactions.search';

    return view('transactions/search', compact(
      'transactions',
      'nextMonthObj',
      'beforeMonthObj',
      'year',
      'month',
      'type',
      'categoria',
      'categorias',
      'cartao',
      'cartoes',
      'pessoa',
      'pessoas',
      'caixa',
      'caixas',
      'total_a_pagar',
      'total_pago',
      'total_a_receber',
      'total_recebido',
      'nav_route'
    ));
  }

  public function view($id){
    // CurrentUserScope garante que apenas transações do usuário logado são retornadas
    $transaction = Transaction::with(['category', 'contact', 'wallet', 'credit_card'])
                               ->findOrFail($id);

    return view('transactions/view', compact('transaction'));
  }

  public function create(Request $request){
    $categorias = Category::where('id_usuario', Auth::id())
                           ->where('status', 'a')
                           ->orderBy('nome')
                           ->get();

    $cartoes = CreditCard::where('id_usuario', Auth::id())
                          ->orderBy('descricao')
                          ->get();

    $pessoas = Contact::where('id_usuario', Auth::id())
                       ->orderBy('nome')
                       ->get();

    $caixas = Wallet::where('id_usuario', Auth::id())
                     ->orderBy('titulo')
                     ->get();

    // Pré-selecionar tipo/data via query string: /transactions/new?tipo=despesa&data=2026-03-08
    $defaults = [
      'tipo' => $request->input('tipo', 'despesa'),
      'data' => $request->input('data', date('Y-m-d')),
    ];

    return view('transactions/new', compact('categorias', 'cartoes', 'pessoas', 'caixas', 'defaults'));
  }

  public function store(Request $request){
    $request->validate([
      'descricao'      => 'nullable|string|max:255',
      'valor'          => 'required|numeric|min:0',
      'data'           => 'required|date',
      'tipo'           => 'required|in:despesa,lucro,transferencia,investimento,emprestimo,pagamento_emprestimo',
      'id_categoria'   => 'nullable|integer',
      'id_caixa'       => 'nullable|integer',
      'id_cartao'      => 'nullable|integer',
      'id_cliente'     => 'nullable|integer',
      'data_pagamento' => 'nullable|date',
    ]);

    $transaction = Transaction::create([
      'id_usuario'     => Auth::id(),
      'descricao'      => $request->input('descricao'),
      'valor'          => $request->input('valor'),
      'data'           => $request->input('data'),
      'tipo'           => $request->input('tipo'),
      'id_categoria'   => $request->input('id_categoria') ?: null,
      'id_caixa'       => $request->input('id_caixa') ?: null,
      'id_cartao'      => $request->input('id_cartao') ?: null,
      'id_cliente'     => $request->input('id_cliente') ?: null,
      'data_pagamento' => $request->input('data_pagamento') ?: null,
      'status'         => 'disponivel',
    ]);

    return redirect()
      ->route('transactions.view', $transaction->id)
      ->with('success', 'Lançamento criado com sucesso.');
  }

  public function edit($id){
    $transaction = Transaction::with(['category', 'contact', 'wallet', 'credit_card'])
                               ->findOrFail($id);

    $categorias = Category::where('id_usuario', Auth::id())
                           ->where('status', 'a')
                           ->orderBy('nome')
                           ->get();

    $cartoes = CreditCard::where('id_usuario', Auth::id())
                          ->orderBy('descricao')
                          ->get();

    $pessoas = Contact::where('id_usuario', Auth::id())
                       ->orderBy('nome')
                       ->get();

    $caixas = Wallet::where('id_usuario', Auth::id())
                     ->orderBy('titulo')
                     ->get();

    return view('transactions/edit', compact('transaction', 'categorias', 'cartoes', 'pessoas', 'caixas'));
  }

  public function update(Request $request, $id){
    $transaction = Transaction::findOrFail($id);

    $request->validate([
      'descricao'      => 'nullable|string|max:255',
      'valor'          => 'required|numeric|min:0',
      'data'           => 'required|date',
      'tipo'           => 'required|in:despesa,lucro,transferencia,investimento,emprestimo,pagamento_emprestimo',
      'id_categoria'   => 'nullable|integer',
      'id_caixa'       => 'nullable|integer',
      'id_cartao'      => 'nullable|integer',
      'id_cliente'     => 'nullable|integer',
      'data_pagamento'    => 'nullable|date',
      'data_recebimento' => 'nullable|date',
    ]);

    $transaction->descricao        = $request->input('descricao');
    $transaction->valor            = $request->input('valor');
    $transaction->data             = $request->input('data');
    $transaction->tipo             = $request->input('tipo');
    $transaction->id_categoria     = $request->input('id_categoria') ?: null;
    $transaction->id_caixa         = $request->input('id_caixa') ?: null;
    $transaction->id_cartao        = $request->input('id_cartao') ?: null;
    $transaction->id_cliente       = $request->input('id_cliente') ?: null;
    $transaction->data_pagamento   = $request->input('data_pagamento') ?: null;
    $transaction->data_recebimento = in_array($request->input('tipo'), ['emprestimo', 'pagamento_emprestimo'])
      ? ($request->input('data_recebimento') ?: null)
      : null;

    $transaction->save();

    return redirect()
      ->route('transactions.view', $id)
      ->with('success', 'Lançamento atualizado com sucesso.');
  }

  public function quickUpdate(Request $request, $id){
    $transaction = Transaction::findOrFail($id);

    $field = $request->input('field');
    $allowed = ['data_pagamento', 'data_recebimento'];

    if (!in_array($field, $allowed)) {
      abort(422, 'Campo não permitido.');
    }

    $request->validate([
      'value' => 'nullable|date',
    ]);

    $transaction->$field = $request->input('value') ?: null;
    $transaction->save();

    return redirect()
      ->route('transactions.view', $id)
      ->with('success', 'Lançamento atualizado com sucesso.');
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

      // Verifica se existe transação com valor próximo (desconsiderando centavos), mesmo cartão, no mesmo mês
      $valorInteiro = (int) floor(abs($valor));
      $transacaoSimilarAproximada = (!$isDuplicada && !$isDuplicadaPorValor) ? Transaction::where('id_usuario', Auth::id())
                                  ->where('id_cartao', $idCartao)
                                  ->whereRaw('FLOOR(ABS(valor)) = ?', [$valorInteiro])
                                  ->whereYear('data', $dataFaturaCarbon->year)
                                  ->whereMonth('data', $dataFaturaCarbon->month)
                                  ->first() : null;
      $isDuplicadaPorValorAproximado = $transacaoSimilarAproximada !== null;
      
      $transaction['is_duplicada'] = $isDuplicada;
      $transaction['is_duplicada_por_valor'] = $isDuplicadaPorValor;
      $transaction['duplicada_por_valor_descricao'] = $isDuplicadaPorValor
          ? ($transacaoSimilar->descricao ?: $transacaoSimilar->descricao_banco)
          : null;
      $transaction['is_duplicada_por_valor_aproximado'] = $isDuplicadaPorValorAproximado;
      $transaction['duplicada_por_valor_aproximado_descricao'] = $isDuplicadaPorValorAproximado
          ? ($transacaoSimilarAproximada->descricao ?: $transacaoSimilarAproximada->descricao_banco)
          : null;
      $transaction['chave_banco'] = $chaveBanco;
      
      return $transaction;
    });

    $categorias = Category::where('id_usuario', Auth::id())
                          ->whereNull('parent_id')
                          ->where('status', 'a')
                          ->orderBy('nome')
                          ->get();
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
          'id_cliente' => $item['id_cliente'] ?? null,
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
