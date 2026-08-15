<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Category;
use App\Models\Contact;
use App\Models\CreditCard;
use App\Models\Wallet;
use App\Models\Workspace;
use App\Models\TransactionMapping;
use App\Http\Requests\StoreContact;
use App\Http\Requests\ImportCsvRequest;
use App\Services\CsvParserService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TransactionsController extends Controller
{
  public function creditCards(Request $request)
  {
    $year  = $request->input('year',  date('Y'));
    $month = $request->input('month', date('n'));

    // Workspaces do usuário para o breakdown
    $workspaces = Auth::user()->workspaces()->where('workspaces.ativo', true)->orderBy('workspaces.nome')->get();

    // Cartões do usuário
    $cards = CreditCard::where('id_usuario', Auth::id())->where('status', 'ativo')->orderBy('descricao')->get();

    $cardData = [];
    $totals = ['geral' => 0];
    foreach ($workspaces as $ws) {
      $totals[$ws->id] = 0;
    }
    $totals['em_aberto_empresa'] = 0;

    foreach ($cards as $card) {
      // Todas as transações do mês desse cartão (ignora workspace scope — contexto do usuário)
      $transactions = Transaction::withoutGlobalScope(\App\Models\Scopes\CurrentUserScope::class)
        ->where('id_usuario', Auth::id())
        ->where('id_cartao', $card->id)
        ->whereYear('data', $year)
        ->whereMonth('data', $month)
        ->where('status', '!=', 'cancelado')
        ->get();

      $fatura = $transactions->sum('valor');

      // Breakdown por workspace
      $breakdown = [];
      foreach ($workspaces as $ws) {
        $breakdown[$ws->id] = [
          'nome'  => $ws->nome,
          'tipo'  => $ws->tipo,
          'total' => $transactions->where('id_workspace', $ws->id)->sum('valor'),
        ];
      }

      // Valor em aberto de workspaces do tipo empresa (sem data_pagamento)
      $emAbertoEmpresa = 0;
      foreach ($workspaces as $ws) {
        if ($ws->tipo === 'empresa') {
          $emAbertoEmpresa += $transactions
            ->where('id_workspace', $ws->id)
            ->whereNull('data_pagamento')
            ->sum('valor');
        }
      }

      $pago = $transactions->whereNotNull('data_pagamento')->count() > 0
        && $transactions->whereNull('data_pagamento')->count() === 0;

      $cardData[] = [
        'card'            => $card,
        'fatura'          => $fatura,
        'breakdown'       => $breakdown,
        'em_aberto_empresa' => $emAbertoEmpresa,
        'pago'            => $pago,
      ];

      $totals['geral'] += $fatura;
      foreach ($workspaces as $ws) {
        $totals[$ws->id] += $breakdown[$ws->id]['total'];
      }
      $totals['em_aberto_empresa'] += $emAbertoEmpresa;
    }

    $currentDate = Carbon::create($year, $month, 1);
    $prevDate    = $currentDate->copy()->subMonth();
    $nextDate    = $currentDate->copy()->addMonth();

    return view('transactions/credit_cards', compact(
      'cardData', 'workspaces', 'totals',
      'year', 'month', 'currentDate', 'prevDate', 'nextDate'
    ));
  }

  public function cardTransactions(Request $request, $cardId, $year = null, $month = null)
  {
    $year  = $year  ?? date('Y');
    $month = $month ?? date('n');

    $card = CreditCard::where('id', $cardId)->where('id_usuario', Auth::id())->firstOrFail();

    $transactions = Transaction::withoutGlobalScope(\App\Models\Scopes\CurrentUserScope::class)
      ->where('id_usuario', Auth::id())
      ->where('id_cartao', $cardId)
      ->where('status', '!=', 'cancelado')
      ->whereYear('data', $year)
      ->whereMonth('data', $month)
      ->with(['category', 'contact', 'wallet', 'credit_card', 'workspace'])
      ->orderBy('data_pagamento', 'asc')
      ->orderBy('data', 'asc')
      ->get();

    $total_a_pagar   = $transactions->where('tipo', 'despesa')->whereNull('data_pagamento')->sum('valor');
    $total_pago      = $transactions->where('tipo', 'despesa')->whereNotNull('data_pagamento')->sum('valor');
    $total_a_receber = $transactions->where('tipo', 'lucro')->whereNull('data_pagamento')->sum('valor');
    $total_recebido  = $transactions->where('tipo', 'lucro')->whereNotNull('data_pagamento')->sum('valor');

    $currentDateObj = new \DateTime;
    $currentDateObj->setDate($year, $month, 1);
    $currentDateObj->setTime(0, 0);

    $nextMonthObj = clone $currentDateObj;
    $nextMonthObj->add(new \DateInterval('P1M'));

    $beforeMonthObj = clone $currentDateObj;
    $beforeMonthObj->sub(new \DateInterval('P1M'));

    $prevUrl = route('transactions.cardTransactions', [
      'cardId' => $cardId,
      'year'   => $beforeMonthObj->format('Y'),
      'month'  => (int) $beforeMonthObj->format('m'),
    ]);
    $nextUrl = route('transactions.cardTransactions', [
      'cardId' => $cardId,
      'year'   => $nextMonthObj->format('Y'),
      'month'  => (int) $nextMonthObj->format('m'),
    ]);

    $categorias = Category::where('id_workspace', session('active_workspace_id'))
                           ->where('status', 'a')
                           ->orderBy('nome')
                           ->get();

    $cartoes = CreditCard::where('id_usuario', Auth::id())
                          ->where('status', 'ativo')
                          ->orderBy('descricao')
                          ->get();

    $pessoas = Contact::where('id_usuario', Auth::id())
                       ->where('status', 'ativo')
                       ->orderBy('nome')
                       ->get();

    $caixas = Wallet::where('id_usuario', Auth::id())
                     ->orderBy('titulo')
                     ->get();

    $type      = null;
    $categoria = null;
    $cartao    = $card;
    $pessoa    = null;
    $caixa     = null;
    $nav_route    = 'transactions.month';
    $activeWorkspace = Workspace::find(session('active_workspace_id'));
    $showWorkspaceColumn = true;
    $workspaces = Auth::user()->workspaces()->where('workspaces.ativo', true)->orderBy('workspaces.nome')->get();

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
      'nav_route',
      'activeWorkspace',
      'prevUrl',
      'nextUrl',
      'showWorkspaceColumn',
      'workspaces'
    ));
  }

  public function payCardBill(Request $request, $cardId, $year, $month)
  {
    $updated = Transaction::where('id_usuario', Auth::id())
      ->where('id_cartao', $cardId)
      ->whereYear('data', $year)
      ->whereMonth('data', $month)
      ->whereNull('data_pagamento')
      ->update(['data_pagamento' => Carbon::today()]);

    return redirect()->back()->with('success', "Fatura confirmada: {$updated} lançamento(s) marcado(s) como pago(s).");
  }

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
    $categorias = Category::where('id_workspace', session('active_workspace_id'))
                           ->where('status', 'a')
                           ->orderBy('nome')
                           ->get();

    $cartoes = CreditCard::where('id_usuario', Auth::id())
                          ->where('status', 'ativo')
                          ->orderBy('descricao')
                          ->get();

    $pessoas = Contact::where('id_usuario', Auth::id())
                       ->where('status', 'ativo')
                       ->orderBy('nome')
                       ->get();

    $caixas = Wallet::where('id_usuario', Auth::id())
                     ->orderBy('titulo')
                     ->get();

    $workspaces = Auth::user()->workspaces()->where('workspaces.ativo', true)->orderBy('workspaces.nome')->get();

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
    $activeWorkspace = Workspace::find(session('active_workspace_id'));

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
      'nav_route',
      'activeWorkspace',
      'workspaces'
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
    $categorias = Category::where('id_workspace', session('active_workspace_id'))
                           ->where('status', 'a')
                           ->orderBy('nome')
                           ->get();

    $cartoes = CreditCard::where('id_usuario', Auth::id())
                          ->where('status', 'ativo')
                          ->orderBy('descricao')
                          ->get();

    $pessoas = Contact::where('id_usuario', Auth::id())
                       ->where('status', 'ativo')
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
    $transaction = Transaction::withoutGlobalScope(\App\Models\Scopes\CurrentUserScope::class)
                               ->where('id_usuario', Auth::id())
                               ->with(['category', 'contact', 'wallet', 'credit_card', 'workspace'])
                               ->findOrFail($id);

    return view('transactions/view', compact('transaction'));
  }

  public function create(Request $request){
    $categorias = Category::where('id_workspace', session('active_workspace_id'))
                           ->where('status', 'a')
                           ->orderBy('nome')
                           ->get();

    $cartoes = CreditCard::where('id_usuario', Auth::id())
                          ->where('status', 'ativo')
                          ->orderBy('descricao')
                          ->get();

    $pessoas = Contact::where('id_usuario', Auth::id())
                       ->where('status', 'ativo')
                       ->orderBy('nome')
                       ->get();

    // Pré-selecionar tipo/data via query string: /transactions/new?tipo=despesa&data=2026-03-08
    $defaults = [
      'tipo' => $request->input('tipo', 'despesa'),
      'data' => $request->input('data', date('Y-m-d')),
    ];

    return view('transactions/new', compact('categorias', 'cartoes', 'pessoas', 'defaults'));
  }

  public function store(Request $request){
    $request->validate([
      'descricao'      => 'nullable|string|max:255',
      'valor'          => 'required|numeric|min:0',
      'data'           => 'required|date',
      'tipo'           => 'required|in:despesa,lucro,transferencia,investimento,emprestimo,pagamento_emprestimo',
      'id_categoria'   => 'nullable|integer',
      'id_cartao'      => 'nullable|integer',
      'id_cliente'     => 'nullable|integer',
      'data_pagamento' => 'nullable|date',
    ]);

    $idCaixa = Wallet::where('id_usuario', Auth::id())->where('exibir_no_saldo', 1)->value('id');

    $workspaceId = session('active_workspace_id');
    abort_unless(
      Auth::user()->workspaces()->where('workspaces.id', $workspaceId)->where('workspaces.ativo', true)->exists(),
      403,
      'Workspace inv\u00e1lido ou sem permiss\u00e3o.'
    );

    $transaction = Transaction::create([
      'id_usuario'     => Auth::id(),
      'id_workspace'   => $workspaceId,
      'descricao'      => $request->input('descricao'),
      'valor'          => $request->input('valor'),
      'data'           => $request->input('data'),
      'tipo'           => $request->input('tipo'),
      'id_categoria'   => $request->input('id_categoria') ?: null,
      'id_caixa'       => $idCaixa,
      'id_cartao'      => $request->input('id_cartao') ?: null,
      'id_cliente'     => $request->input('id_cliente') ?: null,
      'data_pagamento' => $request->input('data_pagamento') ?: null,
      'status'         => 'disponivel',
    ]);

    $backUrl = $request->input('_back');
    $viewUrl = route('transactions.view', $transaction->id);
    if ($backUrl) {
      $viewUrl .= '?_back=' . urlencode($backUrl);
    }
    return redirect($viewUrl)
      ->with('success', 'Lançamento criado com sucesso.');
  }

  public function edit($id){
    $transaction = Transaction::withoutGlobalScope(\App\Models\Scopes\CurrentUserScope::class)
                               ->where('id_usuario', Auth::id())
                               ->with(['category', 'contact', 'wallet', 'credit_card'])
                               ->findOrFail($id);

    $categorias = Category::where('id_workspace', session('active_workspace_id'))
                           ->where('status', 'a')
                           ->orderBy('nome')
                           ->get();

    $cartoes = CreditCard::where('id_usuario', Auth::id())
                          ->where('status', 'ativo')
                          ->orderBy('descricao')
                          ->get();

    $pessoas = Contact::where('id_usuario', Auth::id())
                       ->where('status', 'ativo')
                       ->orderBy('nome')
                       ->get();

    $workspaces = Auth::user()->workspaces()->where('workspaces.ativo', true)->orderBy('workspaces.nome')->get();

    return view('transactions/edit', compact('transaction', 'categorias', 'cartoes', 'pessoas', 'workspaces'));
  }

  public function update(Request $request, $id){
    $transaction = Transaction::withoutGlobalScope(\App\Models\Scopes\CurrentUserScope::class)->where('id_usuario', Auth::id())->findOrFail($id);

    $request->validate([
      'descricao'      => 'nullable|string|max:255',
      'valor'          => 'required|numeric|min:0',
      'data'           => 'required|date',
      'tipo'           => 'required|in:despesa,lucro,transferencia,investimento,emprestimo,pagamento_emprestimo',
      'id_categoria'   => 'nullable|integer',
      'id_cartao'      => 'nullable|integer',
      'id_cliente'     => 'nullable|integer',
      'id_workspace'   => 'required|integer',
      'data_pagamento'    => 'nullable|date',
      'data_recebimento' => 'nullable|date',
    ]);

    $newWorkspaceId = $request->input('id_workspace');
    abort_unless(
      Auth::user()->workspaces()->where('workspaces.id', $newWorkspaceId)->where('workspaces.ativo', true)->exists(),
      403,
      'Workspace inválido ou sem permissão.'
    );

    $idCaixa = Wallet::where('id_usuario', Auth::id())->where('exibir_no_saldo', 1)->value('id');

    $transaction->descricao        = $request->input('descricao');
    $transaction->valor            = $request->input('valor');
    $transaction->data             = $request->input('data');
    $transaction->tipo             = $request->input('tipo');
    $transaction->id_categoria     = $request->input('id_categoria') ?: null;
    $transaction->id_caixa         = $idCaixa;
    $transaction->id_cartao        = $request->input('id_cartao') ?: null;
    $transaction->id_cliente       = $request->input('id_cliente') ?: null;
    $transaction->data_pagamento   = $request->input('data_pagamento') ?: null;
    $transaction->data_recebimento = in_array($request->input('tipo'), ['emprestimo', 'pagamento_emprestimo'])
      ? ($request->input('data_recebimento') ?: null)
      : null;
    $transaction->id_workspace = $newWorkspaceId;

    $transaction->save();

    $backUrl = $request->input('_back');
    $viewUrl = route('transactions.view', $id);
    if ($backUrl) {
      $viewUrl .= '?_back=' . urlencode($backUrl);
    }
    return redirect($viewUrl)
      ->with('success', 'Lançamento atualizado com sucesso.');
  }

  public function destroy(Request $request, $id){
    $transaction = Transaction::withoutGlobalScope(\App\Models\Scopes\CurrentUserScope::class)->where('id_usuario', Auth::id())->findOrFail($id);
    $transaction->delete();

    $backUrl = $request->input('_back');
    return redirect($backUrl ?: route('transactions.month'))
      ->with('success', 'Lançamento #'.$id.' excluído com sucesso.');
  }

  public function quickUpdate(Request $request, $id){
    $transaction = Transaction::withoutGlobalScope(\App\Models\Scopes\CurrentUserScope::class)->where('id_usuario', Auth::id())->findOrFail($id);

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

    $backUrl = $request->input('_back');
    $viewUrl = route('transactions.view', $id);
    if ($backUrl) {
      $viewUrl .= '?_back=' . urlencode($backUrl);
    }
    return redirect($viewUrl)
      ->with('success', 'Lançamento atualizado com sucesso.');
  }

  public function bulkUpdate(Request $request){
    $request->validate([
      'ids'   => 'required|array|min:1',
      'ids.*' => 'integer',
      'field' => 'required|in:id_categoria,id_workspace',
      'value' => 'nullable|integer',
    ]);

    $ids   = $request->input('ids');
    $field = $request->input('field');
    $value = $request->input('value') ?: null;

    if ($field === 'id_workspace') {
      abort_unless(
        $value && Auth::user()->workspaces()->where('workspaces.id', $value)->where('workspaces.ativo', true)->exists(),
        403,
        'Workspace inválido ou sem permissão.'
      );
    }

    $count = Transaction::whereIn('id', $ids)
      ->where('id_usuario', Auth::id())
      ->update([$field => $value]);

    return redirect()->back()->with('success', $count . ' lançamento(s) atualizados com sucesso.');
  }

  public function storeModal(Request $request)
  {
    $request->validate([
      'descricao'      => 'nullable|string|max:255',
      'valor'          => 'required|numeric|min:0',
      'data'           => 'required|date',
      'tipo'           => 'required|in:despesa,lucro,transferencia,investimento,emprestimo,pagamento_emprestimo',
      'id_categoria'   => 'nullable|integer',
      'id_cartao'      => 'nullable|integer',
      'id_cliente'     => 'nullable|integer',
      'data_pagamento' => 'nullable|date',
    ]);

    $workspaceId = session('active_workspace_id');
    abort_unless(
      Auth::user()->workspaces()->where('workspaces.id', $workspaceId)->where('workspaces.ativo', true)->exists(),
      403,
      'Workspace inválido ou sem permissão.'
    );

    $idCaixa = Wallet::where('id_usuario', Auth::id())->where('exibir_no_saldo', 1)->value('id');

    Transaction::create([
      'id_usuario'     => Auth::id(),
      'id_workspace'   => $workspaceId,
      'descricao'      => $request->input('descricao'),
      'valor'          => $request->input('valor'),
      'data'           => $request->input('data'),
      'tipo'           => $request->input('tipo'),
      'id_categoria'   => $request->input('id_categoria') ?: null,
      'id_caixa'       => $idCaixa,
      'id_cartao'      => $request->input('id_cartao') ?: null,
      'id_cliente'     => $request->input('id_cliente') ?: null,
      'data_pagamento' => $request->input('data_pagamento') ?: null,
      'status'         => 'disponivel',
    ]);

    $backUrl = $request->input('_back', route('transactions.month'));
    return redirect($backUrl)->with('success', 'Lançamento criado com sucesso.');
  }

  public function modalUpdate(Request $request, $id)
  {
    $transaction = Transaction::withoutGlobalScope(\App\Models\Scopes\CurrentUserScope::class)
                               ->where('id_usuario', Auth::id())
                               ->findOrFail($id);

    $request->validate([
      'descricao'        => 'nullable|string|max:255',
      'valor'            => 'required|numeric|min:0',
      'data'             => 'required|date',
      'tipo'             => 'required|in:despesa,lucro,transferencia,investimento,emprestimo,pagamento_emprestimo',
      'id_categoria'     => 'nullable|integer',
      'id_cartao'        => 'nullable|integer',
      'id_cliente'       => 'nullable|integer',
      'id_workspace'     => 'required|integer',
      'data_pagamento'   => 'nullable|date',
      'data_recebimento' => 'nullable|date',
    ]);

    $newWorkspaceId = $request->input('id_workspace');
    abort_unless(
      Auth::user()->workspaces()->where('workspaces.id', $newWorkspaceId)->where('workspaces.ativo', true)->exists(),
      403,
      'Workspace inválido ou sem permissão.'
    );

    $idCaixa = Wallet::where('id_usuario', Auth::id())->where('exibir_no_saldo', 1)->value('id');

    $transaction->descricao        = $request->input('descricao');
    $transaction->valor            = $request->input('valor');
    $transaction->data             = $request->input('data');
    $transaction->tipo             = $request->input('tipo');
    $transaction->id_categoria     = $request->input('id_categoria') ?: null;
    $transaction->id_caixa         = $idCaixa;
    $transaction->id_cartao        = $request->input('id_cartao') ?: null;
    $transaction->id_cliente       = $request->input('id_cliente') ?: null;
    $transaction->data_pagamento   = $request->input('data_pagamento') ?: null;
    $transaction->data_recebimento = in_array($request->input('tipo'), ['emprestimo', 'pagamento_emprestimo'])
      ? ($request->input('data_recebimento') ?: null)
      : null;
    $transaction->id_workspace     = $newWorkspaceId;

    $transaction->save();

    $backUrl = $request->input('_back', route('transactions.month'));
    return redirect($backUrl)->with('success', 'Lançamento #' . $id . ' atualizado com sucesso.');
  }

  public function saveModal(Request $request){
    return view('transactions/modal_save');
  }

  public function import()
  {
    $cartoes = CreditCard::where('id_usuario', Auth::id())->where('status', 'ativo')->get();

    $categorias = Category::where('id_workspace', session('active_workspace_id'))
                          ->where('status', 'a')
                          ->orderBy('nome')
                          ->get(['id', 'nome']);

    return view('transactions/import', compact('cartoes', 'categorias'));
  }

  public function importPreview(ImportCsvRequest $request)
  {
    $tmpFile = null;

    if ($request->hasFile('file')) {
      $filePath = $request->file('file')->getRealPath();
    } else {
      $csvContent = $request->input('csv_content');
      $tmpFile    = tempnam(sys_get_temp_dir(), 'csv_import_');
      file_put_contents($tmpFile, $csvContent);
      $filePath   = $tmpFile;
    }

    $csvParser = new CsvParserService();
    $transactions = $csvParser->toPreviewArray($filePath);

    if ($tmpFile) {
      @unlink($tmpFile);
    }

    $dataFatura = $request->input('data_fatura');
    $idCartao = $request->input('id_cartao');
    $dataFaturaCarbon = Carbon::parse($dataFatura);

    // Verifica duplicatas para cada transação
    $transactions = $transactions->map(function($transaction, $idx) use ($dataFatura, $idCartao, $dataFaturaCarbon) {
      $dataBanco = $transaction['data_banco'] ?? '';
      $descricao = $transaction['descricao_banco'] ?? '';
      $valor = $transaction['valor'] ?? 0;
      $chaveBanco = Transaction::generateChaveBanco($dataBanco, $descricao, $valor, $dataFatura);
      
      // Verifica se já existe pela chave_banco
      $isDuplicada = Transaction::withoutGlobalScope(\App\Models\Scopes\CurrentUserScope::class)
                                  ->where('chave_banco', $chaveBanco)
                                  ->where('id_usuario', Auth::id())
                                  ->exists();

      // Verifica se existe transação com mesmo valor, mesmo cartão, no mesmo mês
      $valorArredondado = round($valor, 2);
      $transacaoSimilar = !$isDuplicada ? Transaction::withoutGlobalScope(\App\Models\Scopes\CurrentUserScope::class)
                                  ->where('id_usuario', Auth::id())
                                  ->where('id_cartao', $idCartao)
                                  ->whereRaw('ROUND(valor, 2) = ?', [$valorArredondado])
                                  ->whereYear('data', $dataFaturaCarbon->year)
                                  ->whereMonth('data', $dataFaturaCarbon->month)
                                  ->first() : null;
      $isDuplicadaPorValor = $transacaoSimilar !== null;

      // Verifica se existe transação com valor próximo (desconsiderando centavos), mesmo cartão, no mesmo mês
      $valorInteiro = (int) floor(abs($valor));
      $transacaoSimilarAproximada = (!$isDuplicada && !$isDuplicadaPorValor) ? Transaction::withoutGlobalScope(\App\Models\Scopes\CurrentUserScope::class)
                                  ->where('id_usuario', Auth::id())
                                  ->where('id_cartao', $idCartao)
                                  ->whereRaw('FLOOR(ABS(valor)) = ?', [$valorInteiro])
                                  ->whereYear('data', $dataFaturaCarbon->year)
                                  ->whereMonth('data', $dataFaturaCarbon->month)
                                  ->first() : null;
      $isDuplicadaPorValorAproximado = $transacaoSimilarAproximada !== null;
      
      $transaction['is_duplicada'] = $isDuplicada;
      $transaction['is_duplicada_por_valor'] = $isDuplicadaPorValor;
      $transaction['duplicada_por_valor_descricao'] = $isDuplicadaPorValor ? ($transacaoSimilar->descricao ?: $transacaoSimilar->descricao_banco) : null;
      $transaction['duplicada_por_valor_valor'] = $isDuplicadaPorValor ? $transacaoSimilar->valor  : null;
      $transaction['is_duplicada_por_valor_aproximado'] = $isDuplicadaPorValorAproximado;
      $transaction['duplicada_por_valor_aproximado_descricao'] = $isDuplicadaPorValorAproximado ? ($transacaoSimilarAproximada->descricao ?: $transacaoSimilarAproximada->descricao_banco) : null;
      $transaction['duplicada_por_valor_aproximado_valor'] = $isDuplicadaPorValorAproximado ? $transacaoSimilarAproximada->valor : null;
      $transaction['chave_banco'] = $chaveBanco;
      $transaction['data_banco'] = $dataBanco;
      $transaction['_key'] = $idx;

      // Sugestão de local/categoria a partir do De <> Para cadastrado.
      // O mapeamento tem prioridade sobre a categoria vinda do CSV: é uma
      // regra específica e deliberada, enquanto a coluna do CSV costuma
      // trazer só uma categoria padrão/genérica.
      $mapeamento = TransactionMapping::matchFor($descricao);
      if ($mapeamento) {
        $transaction['descricao_sugerida'] = $mapeamento->descricao_local;
        if ($mapeamento->id_categoria) {
          $transaction['id_categoria'] = $mapeamento->id_categoria;
        }
      }

      return $transaction;
    });

    $categorias = Category::where('id_workspace', session('active_workspace_id'))
                          ->whereNull('parent_id')
                          ->where('status', 'a')
                          ->orderBy('nome')
                          ->get();
    $pessoas = Contact::where('id_usuario', Auth::id())->where('status', 'ativo')->get();

    // --- Installment suggestions ---
    // Group future installments by future billing month.
    $installmentGroups = [];
    foreach ($transactions as $index => $transaction) {
      $inst = $transaction['installment'] ?? null;
      if (!$inst || $inst['current'] >= $inst['total']) {
        continue;
      }

      $remaining = $inst['total'] - $inst['current'];
      for ($i = 1; $i <= $remaining; $i++) {
        $futureParcel = $inst['current'] + $i;
        $futureDesc   = preg_replace(
          '/\b' . preg_quote($inst['current'], '/') . '\/' . preg_quote($inst['total'], '/') . '\b/',
          $futureParcel . '/' . $inst['total'],
          $transaction['descricao_banco']
        );
        $futureDate   = Carbon::parse($dataFatura)->addMonths($i);
        $futureVal    = $transaction['valor'];
        $mesAno       = $futureDate->format('Y-m');

        // Chave futura (sem data_banco pois é gerada, não vem do CSV)
        $futureChave = Transaction::generateChaveBanco($mesAno, $futureDesc, $futureVal, $futureDate->format('Y-m-d'));

        $futureDupChave = Transaction::withoutGlobalScope(\App\Models\Scopes\CurrentUserScope::class)
                                     ->where('chave_banco', $futureChave)
                                     ->where('id_usuario', Auth::id())
                                     ->exists();

        $futureValArredondado = round($futureVal, 2);
        $futureSimilar = !$futureDupChave
          ? Transaction::withoutGlobalScope(\App\Models\Scopes\CurrentUserScope::class)
              ->where('id_usuario', Auth::id())
              ->where('id_cartao', $idCartao)
              ->whereRaw('ROUND(valor, 2) = ?', [$futureValArredondado])
              ->whereYear('data', $futureDate->year)
              ->whereMonth('data', $futureDate->month)
              ->first()
          : null;
        $futureDupValor = $futureSimilar !== null;

        $futureValInteiro = (int) floor(abs($futureVal));
        $futureSimilarAprox = (!$futureDupChave && !$futureDupValor)
          ? Transaction::withoutGlobalScope(\App\Models\Scopes\CurrentUserScope::class)
              ->where('id_usuario', Auth::id())
              ->where('id_cartao', $idCartao)
              ->whereRaw('FLOOR(ABS(valor)) = ?', [$futureValInteiro])
              ->whereYear('data', $futureDate->year)
              ->whereMonth('data', $futureDate->month)
              ->first()
          : null;
        $futureDupValorAprox = $futureSimilarAprox !== null;

        if (!isset($installmentGroups[$mesAno])) {
          $installmentGroups[$mesAno] = [
            'mes_ano'      => $mesAno,
            'data'         => $futureDate->format('Y-m-d'),
            'installments' => [],
          ];
        }

        $futureMapeamento = TransactionMapping::matchFor($futureDesc);

        $installmentGroups[$mesAno]['installments'][] = [
          'source_index'    => $index,
          'parcel'          => $futureParcel,
          'total'           => $inst['total'],
          'descricao_banco' => $futureDesc,
          'descricao'       => $futureMapeamento->descricao_local ?? $futureDesc,
          'valor'           => $futureVal,
          'data'            => $futureDate->format('Y-m-d'),
          'id_cartao'       => $idCartao,
          'data_banco'       => $transaction['data_banco'],
          'chave_banco'     => $futureChave,
          'id_categoria'    => $transaction['id_categoria'] ?? null,
          // Duplicate flags
          'is_duplicada'                           => $futureDupChave,
          'is_duplicada_por_valor'                 => $futureDupValor,
          'duplicada_por_valor_descricao'          => $futureDupValor
              ? ($futureSimilar->descricao ?: $futureSimilar->descricao_banco)
              : null,
          'is_duplicada_por_valor_aproximado'      => $futureDupValorAprox,
          'duplicada_por_valor_aproximado_descricao' => $futureDupValorAprox
              ? ($futureSimilarAprox->descricao ?: $futureSimilarAprox->descricao_banco)
              : null,
        ];
      }
    }

    // Sort groups chronologically
    ksort($installmentGroups);

    return view('transactions/importPreview', [
      'transactions'      => $transactions,
      'id_cartao'         => $request->input('id_cartao'),
      'data_fatura'       => $request->input('data_fatura'),
      'categorias'        => $categorias,
      'pessoas'           => $pessoas,
      'installmentGroups' => $installmentGroups,
    ]);
  }

  public function importPreviewJson(Request $request)
  {
    $request->validate([
      'id_cartao'   => 'required|exists:cartoes,id',
      'data_fatura' => 'required|date',
    ]);

    $idCartao         = $request->input('id_cartao');
    $dataFatura       = $request->input('data_fatura');
    $dataFaturaCarbon = Carbon::parse($dataFatura);

    $jsonContent = null;

    if ($request->hasFile('file') && $request->file('file')->isValid()) {
      $ext = strtolower($request->file('file')->getClientOriginalExtension());
      if ($ext !== 'json') {
        return back()->withErrors(['file' => 'O arquivo deve ser do tipo JSON (.json).'])->withInput();
      }
      $jsonContent = file_get_contents($request->file('file')->getRealPath());
    } elseif ($request->filled('json_content')) {
      $jsonContent = $request->input('json_content');
    } else {
      return back()->withErrors(['file' => 'Envie um arquivo JSON ou cole o conteúdo JSON na caixa de texto.'])->withInput();
    }

    $data = json_decode($jsonContent, true);

    if (json_last_error() !== JSON_ERROR_NONE || !isset($data['transacoes']) || !is_array($data['transacoes'])) {
      return back()->withErrors(['file' => 'JSON inválido ou estrutura inesperada. Verifique se o arquivo foi gerado corretamente.'])->withInput();
    }

    $categorias = Category::getCategories();
    $contatos   = Contact::getCurrentUserContacts();
    $catMap     = $categorias->keyBy(fn($c) => mb_strtolower(trim($c->nome)));
    $contatoMap = $contatos->keyBy(fn($c) => mb_strtolower(trim($c->nome)));

    $cartao = CreditCard::find($idCartao);

    // Process each transaction: map IDs, generate chave_banco and check duplicates
    $transacoesProcessadas = [];
    foreach ($data['transacoes'] as $t) {
      $t['id_categoria'] = $catMap->get(mb_strtolower(trim($t['categoria'] ?? '')))?->id ?? null;
      $t['id_cliente']   = $contatoMap->get(mb_strtolower(trim($t['pessoa'] ?? '')))?->id ?? null;

      // Sugestão de local/categoria a partir do De <> Para cadastrado.
      // A categoria do mapeamento tem prioridade sobre a do JSON: é uma
      // regra específica e deliberada. Já a descrição só é usada quando o
      // JSON não trouxer nenhuma (o classificador externo tende a ser mais rico).
      $mapeamento = TransactionMapping::matchFor($t['descricao_banco'] ?? '');
      if ($mapeamento) {
        $t['descricao'] = $t['descricao'] ?: $mapeamento->descricao_local;
        if ($mapeamento->id_categoria) {
          $t['id_categoria'] = $mapeamento->id_categoria;
        }
      }
      $t['descricao'] = $t['descricao'] ?: ($t['descricao_banco'] ?? '');

      $dataBanco  = $t['data_banco'] ?? '';
      $descricao  = $t['descricao_banco'] ?? '';
      $valor      = floatval($t['valor'] ?? 0);

      // Each transaction can carry its own data_fatura (multi-bill JSON); fall back to request value
      $dataFaturaTransacao       = $t['data_fatura'] ?? $dataFatura;
      $dataFaturaTransacaoCarbon = Carbon::parse($dataFaturaTransacao);

      // Generate chave_banco
      $chaveBanco = Transaction::generateChaveBanco($dataBanco, $descricao, $valor, $dataFaturaTransacao);

      // Check exact duplicate by chave_banco
      $transacaoDuplicada = Transaction::withoutGlobalScope(\App\Models\Scopes\CurrentUserScope::class)
        ->where('chave_banco', $chaveBanco)
        ->where('id_usuario', Auth::id())
        ->select('id', 'descricao', 'descricao_banco')
        ->first();
      $isDuplicada = $transacaoDuplicada !== null;

      // Check similar by exact rounded value (same card, same month)
      $valorArredondado = round($valor, 2);
      $transacaoSimilar = !$isDuplicada
        ? Transaction::withoutGlobalScope(\App\Models\Scopes\CurrentUserScope::class)
            ->where('id_usuario', Auth::id())
            ->where('id_cartao', $idCartao)
            ->whereNull('chave_banco')
            ->whereRaw('ROUND(valor, 2) = ?', [$valorArredondado])
            ->whereYear('data', $dataFaturaTransacaoCarbon->year)
            ->whereMonth('data', $dataFaturaTransacaoCarbon->month)
            ->select('id', 'descricao', 'descricao_banco')
            ->first()
        : null;
      $isDuplicadaPorValor = $transacaoSimilar !== null;

      // Check similar by approximate value (floor, ignoring cents)
      $transacaoSimilarAproximada    = null;
      $isDuplicadaPorValorAproximado = false;
      if (!$isDuplicadaPorValor) {
        $valorInteiro = (int) floor(abs($valor));
        $transacaoSimilarAproximada = (!$isDuplicada)
          ? Transaction::withoutGlobalScope(\App\Models\Scopes\CurrentUserScope::class)
              ->where('id_usuario', Auth::id())
              ->where('id_cartao', $idCartao)
              ->whereRaw('FLOOR(ABS(valor)) = ?', [$valorInteiro])
              ->whereYear('data', $dataFaturaTransacaoCarbon->year)
              ->whereMonth('data', $dataFaturaTransacaoCarbon->month)
              ->select('id', 'descricao', 'descricao_banco')
              ->first()
          : null;
        $isDuplicadaPorValorAproximado = $transacaoSimilarAproximada !== null;
      }

      $t['chave_banco']           = $chaveBanco;
      $t['data_fatura_transacao'] = $dataFaturaTransacao;

      // Duplicate flags + found objects
      $t['is_duplicada'] = $isDuplicada;
      // $t['is_similar'] = $isDuplicadaPorValor;
      // $t['is_similar_aproximado'] = $isDuplicadaPorValorAproximado;

      if ($isDuplicada){
        $t['duplicada']    = $transacaoDuplicada
          ? ['id' => $transacaoDuplicada->id, 'descricao' => $transacaoDuplicada->descricao, 'descricao_banco' => $transacaoDuplicada->descricao_banco]
          : null;
      } else if ($isDuplicadaPorValor) {
        // $t['duplicada']    = $transacaoSimilar
        //   ? ['id' => $transacaoSimilar->id, 'descricao' => $transacaoSimilar->descricao, 'descricao_banco' => $transacaoSimilar->descricao_banco]
        //   : null;
      } else if ($isDuplicadaPorValorAproximado){
        // $t['duplicada']    = $transacaoSimilarAproximada
        //   ? ['id' => $transacaoSimilarAproximada->id, 'descricao' => $transacaoSimilarAproximada->descricao, 'descricao_banco' => $transacaoSimilarAproximada->descricao_banco]
        //   : null;
      }

      $transacoesProcessadas[] = $t;
    }

    // Separate transactions by billing month: current vs future
    $transacoesFaturaAtual = [];
    $proximasFaturas       = [];

    foreach ($transacoesProcessadas as $t) {
      $dtCarbon = Carbon::parse($t['data_fatura_transacao']);
      $mesAno   = $dtCarbon->format('Y-m');

      if ($dtCarbon->year === $dataFaturaCarbon->year && $dtCarbon->month === $dataFaturaCarbon->month) {
        $transacoesFaturaAtual[] = $t;
      } else {
        // Fetch existing DB total for that future month (once per month)
        if (!isset($proximasFaturas[$mesAno])) {
          $totalExistente = Transaction::withoutGlobalScope(\App\Models\Scopes\CurrentUserScope::class)
            ->where('id_usuario', Auth::id())
            ->where('id_cartao', $idCartao)
            ->where('status', '!=', 'cancelado')
            ->whereYear('data', $dtCarbon->year)
            ->whereMonth('data', $dtCarbon->month)
            ->sum('valor');

          $proximasFaturas[$mesAno] = [
            'mes_ano'         => $mesAno,
            'data_fatura'     => $dtCarbon->format('Y-m-d'),
            'total_existente' => $totalExistente,
            'transacoes'      => [],
          ];
        }
        $proximasFaturas[$mesAno]['transacoes'][] = $t;
      }
    }

    // Keep future bills sorted chronologically
    ksort($proximasFaturas);

    // Total of the next calendar month already saved in the DB
    $proximaFaturaCarbon  = $dataFaturaCarbon->copy()->addMonth();
    $totalProximaFatura   = Transaction::withoutGlobalScope(\App\Models\Scopes\CurrentUserScope::class)
      ->where('id_usuario', Auth::id())
      ->where('id_cartao', $idCartao)
      ->where('status', '!=', 'cancelado')
      ->whereYear('data', $proximaFaturaCarbon->year)
      ->whereMonth('data', $proximaFaturaCarbon->month)
      ->sum('valor');

    // Total of current-bill transactions (excluding payments / previous balance)
    $totalFatura = collect($transacoesFaturaAtual)->filter(function ($t) {
      $desc  = strtoupper($t['descricao_banco'] ?? '');
      $valor = floatval($t['valor'] ?? 0);
      return $valor > 0
        && !str_contains($desc, 'SALDO ANTERIOR')
        && !str_contains($desc, 'PAGTO');
    })->sum('valor');

// dd($transacoesFaturaAtual);

    return view('transactions/importPreviewJson', [
      'transacoes'          => $transacoesFaturaAtual,
      'proximas_faturas'    => array_values($proximasFaturas),
      'total_fatura'        => $totalFatura,
      'total_proxima_fatura' => $totalProximaFatura,
      'id_cartao'           => $idCartao,
      'data_fatura'         => $dataFatura,
      'cartao'              => $cartao,
      'categorias'          => $categorias,
      'contatos'            => $contatos,
    ]);
  }

  public function importStore(Request $request)
  {
    $transacoes = $request->input('transacoes', []);
    $dataFatura = $request->input('data_fatura');
    $parcelasFuturas = $request->input('parcelas_futuras', []);
    $count = 0;
    $duplicadas = 0;
    $parcelasCount = 0;

    $workspaceId = session('active_workspace_id');
    abort_unless(
      Auth::user()->workspaces()->where('workspaces.id', $workspaceId)->where('workspaces.ativo', true)->exists(),
      403,
      'Workspace inválido ou sem permissão.'
    );

    // Busca a caixa padrão do usuário (exibir_no_saldo = 1)
    $caixaPadrao = Wallet::where('id_usuario', Auth::id())
                         ->where('exibir_no_saldo', 1)
                         ->first();
    
    $idCaixa = $caixaPadrao ? $caixaPadrao->id : null;

    DB::transaction(function () use ($transacoes, $dataFatura, $idCaixa, $workspaceId, $parcelasFuturas, &$count, &$duplicadas, &$parcelasCount) {
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
          $chaveBanco = Transaction::generateChaveBanco($dataBanco, $descricao, $valor, $dataFatura);
        }

        // Verifica se já existe uma transação com essa chave
        $existe = Transaction::withoutGlobalScope(\App\Models\Scopes\CurrentUserScope::class)
                             ->where('chave_banco', $chaveBanco)
                             ->where('id_usuario', Auth::id())
                             ->exists();

        if ($existe) {
          $duplicadas++;
          continue;
        }

        Transaction::create([
          'id_categoria'    => $item['id_categoria'] ?? null,
          'descricao_banco' => $item['descricao_banco'] ?? '',
          'descricao'       => $item['descricao'] ?? '',
          'valor'           => $item['valor'] ?? 0,
          'data'            => $dataFatura ?? now(),
          'id_cartao'       => $item['id_cartao'] ?? null,
          'id_caixa'        => $idCaixa,
          'tipo'            => $item['tipo'] ?? 'despesa',
          'id_cliente'      => $item['id_cliente'] ?? null,
          'id_usuario'      => Auth::id(),
          'id_workspace'    => $workspaceId,
          'chave_banco'     => $chaveBanco,
        ]);
        $count++;
      }

      // Cria parcelas futuras marcadas pelo usuário
      foreach ($parcelasFuturas as $pf) {
        if (!isset($pf['criar']) || $pf['criar'] != '1') {
          continue;
        }

        // Usa a chave_banco que já foi calculada na preview
        $chaveBancoFut = $pf['chave_banco'] ?? null;
        
        // Se não tiver chave, gera uma nova (fallback)
        // Usa a data da parcela futura como mês de referência da fatura
        if (!$chaveBancoFut) {
          $dataBancoFut  = $pf['data_banco']     ?? '';
          $descricaoFut  = $pf['descricao_banco'] ?? '';
          $valorFut      = $pf['valor']           ?? 0;
          $dataFaturaFut = $pf['data']            ?? $dataFatura;
          $chaveBancoFut = Transaction::generateChaveBanco($dataBancoFut, $descricaoFut, $valorFut, $dataFaturaFut);
        }

        Transaction::create([

          'id_categoria'    => $pf['id_categoria'] ?: null,
          'descricao_banco' => $pf['descricao_banco'] ?? '',
          'descricao'       => $pf['descricao'] ?? '',
          'valor'           => $pf['valor'] ?? 0,
          'data'            => $pf['data'] ?? now(),
          'id_cartao'       => $pf['id_cartao'] ?? null,
          'id_caixa'        => $idCaixa,
          'tipo'            => $pf['tipo'] ?? 'despesa',
          'id_cliente'      => $pf['id_cliente'] ?? null,
          'id_usuario'      => Auth::id(),
          'id_workspace'    => $workspaceId,
          'chave_banco'     => $chaveBancoFut,
        ]);
        $parcelasCount++;
      }
    });

    $mensagem = "{$count} transações importadas com sucesso.";
    if ($parcelasCount > 0) {
      $mensagem .= " {$parcelasCount} parcela(s) futura(s) criada(s).";
    }
    if ($duplicadas > 0) {
      $mensagem .= " {$duplicadas} transações duplicadas foram ignoradas.";
    }

    return redirect()->route('transactions.index')
      ->with('success', $mensagem);
  }

}
