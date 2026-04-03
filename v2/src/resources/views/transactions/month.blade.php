@extends('layouts.dashboard')

@section('content')
<div class="content-header">
  <div class="container">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 text-dark">Resumo mensal</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
          <li class="breadcrumb-item"><a href="{{ route('transactions.month') }}">Resumo mensal</a></li>
          <li class="breadcrumb-item active">Lançamentos</li>
        </ol>
      </div>
    </div>
  </div>
</div>

<div class="content">
  <div class="container">
    <div class="row justify-content-center">

    {{-- Navigation (only when filtering by month) --}}
      @if ($month)
      <div class="col-md-12 d-flex justify-content-between mb-2">
        <a href="{{ route('transactions.month', [$beforeMonthObj->format('Y'), (int)$beforeMonthObj->format('m')]) }}" class="btn btn-sm btn-outline-secondary">
          <i class="fa fa-chevron-left"></i> {{ $beforeMonthObj->format('M/Y') }}
        </a>
        <strong class="align-self-center">
          @php $meses = [1=>'Janeiro',2=>'Fevereiro',3=>'Março',4=>'Abril',5=>'Maio',6=>'Junho',7=>'Julho',8=>'Agosto',9=>'Setembro',10=>'Outubro',11=>'Novembro',12=>'Dezembro']; @endphp
          {{ $meses[$month] ?? $month }} / {{ $year }}
        </strong>
        <a href="{{ route('transactions.month', [$nextMonthObj->format('Y'), (int)$nextMonthObj->format('m')]) }}" class="btn btn-sm btn-outline-secondary">
          {{ $nextMonthObj->format('M/Y') }} <i class="fa fa-chevron-right"></i>
        </a>
      </div>
      @endif

      {{-- Filter card --}}
      <div class="col-md-12">
        <div class="card collapsed-card">
          <div class="card-header">
            <h3 class="card-title"><i class="fa fa-filter"></i> Filtros</h3>
            <div class="card-tools">
              <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-plus"></i>
              </button>
            </div>
          </div>

          <div class="card-body">
            <form method="GET" action="{{ route('transactions.month', [$year, $month]) }}">

              <div class="row">

                {{-- Predefined filters --}}
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Filtros pré-definidos</label>
                    <select class="form-control" name="ps">
                      <option value=""></option>
                      <option value="lendings_not_paid">Empréstimos não pagos</option>
                    </select>
                  </div>
                </div>

                {{-- Type --}}
                <div class="col-md-2">
                  <div class="form-group">
                    <label>Tipo</label>
                    <select class="form-control" name="t">
                      <option value="">Todos</option>
                      <option value="despesa"      @selected($type === 'despesa')>Despesa</option>
                      <option value="lucro"      @selected($type === 'lucro')>Receita</option>
                      <option value="transferencia" @selected($type === 'transferencia')>Transferência</option>
                      <option value="emprestimo"   @selected($type === 'emprestimo')>Empréstimo</option>
                      <option value="pagamento_emprestimo"   @selected($type === 'pagamento_emprestimo')>Pgto. Empréstimo</option>
                    </select>
                  </div>
                </div>

              </div>{{-- /.row --}}

              <div class="row">

                {{-- Category --}}
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Categoria</label>
                    <select class="form-control" name="categoria">
                      <option value="">Todas</option>
                      @foreach ($categorias as $cat)
                        <option value="{{ $cat->id }}" @if($categoria && $categoria->id == $cat->id) selected @endif>{{ $cat->nome }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>

                {{-- Credit card --}}
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Cartão</label>
                    <select class="form-control" name="cartao">
                      <option value="">Todos</option>
                      @foreach ($cartoes as $c)
                        <option value="{{ $c->id }}" @if($cartao && $cartao->id == $c->id) selected @endif>{{ $c->descricao }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>

                {{-- Person/contact --}}
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Pessoa</label>
                    <select class="form-control" name="pessoa">
                      <option value="">Todas</option>
                      @foreach ($pessoas as $p)
                        <option value="{{ $p->id }}" @if($pessoa && $pessoa->id == $p->id) selected @endif>{{ $p->nome }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>

                {{-- Wallet --}}
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Carteira</label>
                    <select class="form-control" name="caixa">
                      <option value="">Todas</option>
                      @foreach ($caixas as $cx)
                        <option value="{{ $cx->id }}" @if($caixa && $caixa->id == $cx->id) selected @endif>{{ $cx->titulo }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>

              </div>{{-- /.row --}}

              <div class="row">
                <div class="col-md-12">
                  <button type="submit" class="btn btn-primary">
                    <i class="fa fa-search"></i> Buscar
                  </button>
                </div>
              </div>

            </form>
          </div>
        </div>
      </div>{{-- /.col filter card --}}

      {{-- Active filter badges --}}
      @if ($categoria || $cartao || $pessoa || $caixa || $type)
      @php
        $meses = [1=>'Jan',2=>'Fev',3=>'Mar',4=>'Abr',5=>'Mai',6=>'Jun',7=>'Jul',8=>'Ago',9=>'Set',10=>'Out',11=>'Nov',12=>'Dez'];
        $baseParams = ['year' => $year, 'month' => $month];
        $activeQuery = array_filter(request()->only(['t', 'categoria', 'cartao', 'pessoa', 'caixa', 'ps']));
      @endphp
      <div class="col-md-12 mb-2">
        <div class="d-flex align-items-center flex-wrap gap-1">
          <strong class="mr-1">Filtros ativos:</strong>

          <span class="badge badge-secondary">{{ $meses[$month] ?? $month }}/{{ $year }}</span>

          @if ($type)
            <a href="{{ route('transactions.month', array_merge($baseParams, array_diff_key($activeQuery, ['t' => '']))) }}"
               class="badge badge-info" title="Remover filtro de tipo">
              {{ ucfirst($type) }} &times;
            </a>
          @endif
          @if ($categoria)
            <a href="{{ route('transactions.month', array_merge($baseParams, array_diff_key($activeQuery, ['categoria' => '']))) }}"
               class="badge badge-warning" title="Remover filtro de categoria">
              Cat: {{ $categoria->nome }} &times;
            </a>
          @endif
          @if ($cartao)
            <a href="{{ route('transactions.month', array_merge($baseParams, array_diff_key($activeQuery, ['cartao' => '']))) }}"
               class="badge badge-dark" title="Remover filtro de cartão">
              Cartão: {{ $cartao->descricao }} &times;
            </a>
          @endif
          @if ($pessoa)
            <a href="{{ route('transactions.month', array_merge($baseParams, array_diff_key($activeQuery, ['pessoa' => '']))) }}"
               class="badge badge-primary" title="Remover filtro de pessoa">
              Pessoa: {{ $pessoa->nome }} &times;
            </a>
          @endif
          @if ($caixa)
            <a href="{{ route('transactions.month', array_merge($baseParams, array_diff_key($activeQuery, ['caixa' => '']))) }}"
               class="badge badge-success" title="Remover filtro de carteira">
              Carteira: {{ $caixa->titulo }} &times;
            </a>
          @endif
        </div>
      </div>
      @endif

      {{-- Totals --}}
      @if ($transactions->count() > 0)
      <div class="col-md-12">
        <div class="row">
          <div class="col-6 col-md-3">
            <div class="small-box bg-danger">
              <div class="inner">
                <h5>R$ {{ number_format($total_a_pagar, 2, ',', '.') }}</h5>
                <p>A pagar</p>
              </div>
              <div class="icon"><i class="fas fa-arrow-down"></i></div>
            </div>
          </div>
          <div class="col-6 col-md-3">
            <div class="small-box bg-secondary">
              <div class="inner">
                <h5>R$ {{ number_format($total_pago, 2, ',', '.') }}</h5>
                <p>Pago</p>
              </div>
              <div class="icon"><i class="fas fa-check"></i></div>
            </div>
          </div>
          <div class="col-6 col-md-3">
            <div class="small-box bg-success">
              <div class="inner">
                <h5>R$ {{ number_format($total_a_receber, 2, ',', '.') }}</h5>
                <p>A receber</p>
              </div>
              <div class="icon"><i class="fas fa-arrow-up"></i></div>
            </div>
          </div>
          <div class="col-6 col-md-3">
            <div class="small-box bg-info">
              <div class="inner">
                <h5>R$ {{ number_format($total_recebido, 2, ',', '.') }}</h5>
                <p>Recebido</p>
              </div>
              <div class="icon"><i class="fas fa-wallet"></i></div>
            </div>
          </div>
        </div>
      </div>
      @endif

      {{-- Lending summary --}}
      @php
        $emprestimos = $transactions->where('tipo', 'emprestimo');
        $emprestimos_recebidos = $transactions->where('tipo', 'pagamento_emprestimo');
        $emprestimosTotal   = $emprestimos->sum('valor');
        $emprestimosRecebidosTotal = $emprestimos_recebidos->sum('valor');
        $emprestimosPendentes = $emprestimosTotal - $emprestimosRecebidosTotal;
        $emprestimosRecebidos = $emprestimosRecebidosTotal;
        $emprestimosPorPessoa = $emprestimos->groupBy('id_cliente');
        $pagamentosPorPessoa  = $emprestimos_recebidos->groupBy('id_cliente');
      @endphp
      @if ($emprestimos->count() > 0)
      <div class="col-md-12">
        <div class="card collapsed-card card-warning">
          <div class="card-header" data-card-widget="collapse" style="cursor:pointer">
            <h3 class="card-title">
              <i class="fas fa-hand-holding-usd mr-1"></i>
              Resumo de Empréstimos
              <span class="badge badge-light ml-2">{{ $emprestimos->count() }}</span>
            </h3>
            <div class="card-tools">
              <span class="mr-3 text-sm">
                <span class="font-weight-bold">
                  R$ {{ number_format($emprestimosRecebidosTotal, 2, ',', '.') }}
                </span>
                <span class="text-muted"> recebido</span>
                &nbsp;/&nbsp;
                <span class="font-weight-bold">R$ {{ number_format($emprestimosTotal, 2, ',', '.') }}</span>
                <span class="text-muted"> total</span>
              </span>
              <button type="button" class="btn btn-tool">
                <i class="fas fa-plus"></i>
              </button>
            </div>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
              <thead>
                <tr>
                  <th>Pessoa</th>
                  <th class="text-right" style="width:140px">Total</th>
                  <th class="text-right d-none d-sm-table-cell" style="width:140px">Pendente</th>
                  <th class="text-right d-none d-sm-table-cell" style="width:140px">Recebido</th>
                  <th style="width:90px"></th>
                </tr>
              </thead>
              <tbody>
                @foreach ($emprestimosPorPessoa as $idCliente => $group)
                @php
                  $pessoaNome   = optional($group->first()->contact)->nome ?? 'Sem pessoa';
                  $pessoaTotal  = $group->sum('valor');
                  $pessoaReceb  = isset($pagamentosPorPessoa[$idCliente]) ? $pagamentosPorPessoa[$idCliente]->sum('valor') : 0;
                  $pessoaPend   = $pessoaTotal - $pessoaReceb;
                @endphp
                <tr>
                  <td>
                    <i class="fa fa-user fa-xs text-muted mr-1"></i>
                    {{ $pessoaNome }}
                    <div class="d-sm-none" style="font-size:.8em">
                      @if ($pessoaPend > 0)
                        <span class="text-warning"><i class="fa fa-clock fa-xs"></i> Pend: R$ {{ number_format($pessoaPend, 2, ',', '.') }}</span>
                      @endif
                    </div>
                  </td>
                  <td class="text-right text-nowrap">R$ {{ number_format($pessoaTotal, 2, ',', '.') }}</td>
                  <td class="text-right d-none d-sm-table-cell text-nowrap {{ $pessoaPend > 0 ? 'text-warning font-weight-bold' : 'text-muted' }}">
                    R$ {{ number_format($pessoaPend, 2, ',', '.') }}
                  </td>
                  <td class="text-right d-none d-sm-table-cell text-nowrap {{ $pessoaReceb > 0 ? 'text-success' : 'text-muted' }}">
                    @if ($pessoaReceb > 0)
                      R$ {{ number_format($pessoaReceb, 2, ',', '.') }}
                    @else
                      —
                    @endif
                  </td>
                  <td class="text-right">
                    <a href="{{ route('transactions.month', array_merge([$year, $month], array_filter(request()->only(['categoria','cartao','caixa'])), ['t' => 'emprestimo', 'pessoa' => $idCliente])) }}"
                       class="btn btn-xs btn-outline-secondary" title="Ver empréstimos desta pessoa">
                      <i class="fa fa-search fa-xs"></i>
                    </a>
                    <a href="{{ route('transactions.month', array_merge([$year, $month], array_filter(request()->only(['categoria','cartao','caixa'])), ['t' => 'pagamento_emprestimo', 'pessoa' => $idCliente])) }}"
                       class="btn btn-xs btn-outline-success ml-1" title="Ver pagamentos desta pessoa">
                      <i class="fa fa-money-bill-wave fa-xs"></i>
                    </a>
                  </td>
                </tr>
                @endforeach
              </tbody>
              <tfoot class="font-weight-bold">
                <tr class="bg-light">
                  <td>Total</td>
                  <td class="text-right text-nowrap">R$ {{ number_format($emprestimosTotal, 2, ',', '.') }}</td>
                  <td class="text-right d-none d-sm-table-cell text-nowrap {{ $emprestimosPendentes > 0 ? 'text-warning' : '' }}">
                    R$ {{ number_format($emprestimosPendentes, 2, ',', '.') }}
                  </td>
                  <td class="text-right d-none d-sm-table-cell text-nowrap text-success">
                    R$ {{ number_format($emprestimosRecebidos, 2, ',', '.') }}
                  </td>
                  <td></td>
                </tr>
              </tfoot>
            </table>
            </div>
          </div>
        </div>
      </div>
      @endif

      {{-- Summary by category --}}
      @php
        $transactionsByCategory = $transactions->groupBy('id_categoria');
      @endphp
      @if ($transactionsByCategory->count() > 0)
      <div class="col-md-12">
        <div class="card collapsed-card card-primary">
          <div class="card-header" data-card-widget="collapse" style="cursor:pointer">
            <h3 class="card-title">
              <i class="fas fa-tags mr-1"></i>
              Resumo por Categoria
              <span class="badge badge-light ml-2">{{ $transactionsByCategory->count() }}</span>
            </h3>
            <div class="card-tools">
              <button type="button" class="btn btn-tool">
                <i class="fas fa-plus"></i>
              </button>
            </div>
          </div>
          <div class="card-body p-0">
            <table class="table table-sm table-hover mb-0">
              <thead>
                <tr>
                  <th>Categoria</th>
                  <th class="text-center" style="width:80px">Qtd</th>
                  <th class="text-right" style="width:140px">Total</th>
                  <th style="width:60px"></th>
                </tr>
              </thead>
              <tbody>
                @foreach ($transactionsByCategory as $idCat => $group)
                @php
                  $catNome  = optional($group->first()->category)->nome ?? 'Sem categoria';
                  $catIcon  = optional($group->first()->category)->icon_class;
                  $catTotal = $group->sum('valor');
                @endphp
                <tr>
                  <td>
                    @if ($catIcon)
                      <i class="{{ $catIcon }} text-muted mr-1" style="font-size:.85em"></i>
                    @endif
                    {{ $catNome }}
                  </td>
                  <td class="text-center text-muted">{{ $group->count() }}</td>
                  <td class="text-right font-weight-bold">R$ {{ number_format($catTotal, 2, ',', '.') }}</td>
                  <td class="text-right">
                    @if ($idCat)
                    <a href="{{ route('transactions.month', array_merge([$year, $month], array_filter(request()->only(['t','cartao','pessoa','caixa'])), ['categoria' => $idCat])) }}"
                       class="btn btn-xs btn-outline-secondary" title="Filtrar por categoria">
                      <i class="fa fa-search fa-xs"></i>
                    </a>
                    @endif
                  </td>
                </tr>
                @endforeach
              </tbody>
              <tfoot class="font-weight-bold">
                <tr class="bg-light">
                  <td>Total</td>
                  <td class="text-center text-muted">{{ $transactions->count() }}</td>
                  <td class="text-right">R$ {{ number_format($transactions->sum('valor'), 2, ',', '.') }}</td>
                  <td></td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      </div>
      @endif

      {{-- Summary by credit card --}}
      @php
        $transactionsByCard = $transactions->filter(fn($t) => !is_null($t->id_cartao))->groupBy('id_cartao');
      @endphp
      @if ($transactionsByCard->count() > 0)
      <div class="col-md-12">
        <div class="card collapsed-card card-dark">
          <div class="card-header" data-card-widget="collapse" style="cursor:pointer">
            <h3 class="card-title">
              <i class="fas fa-credit-card mr-1"></i>
              Resumo por Cartão
              <span class="badge badge-light ml-2">{{ $transactionsByCard->count() }}</span>
            </h3>
            <div class="card-tools">
              <button type="button" class="btn btn-tool">
                <i class="fas fa-plus"></i>
              </button>
            </div>
          </div>
          <div class="card-body p-0">
            <table class="table table-sm table-hover mb-0">
              <thead>
                <tr>
                  <th>Cartão</th>
                  <th class="text-center" style="width:80px">Qtd</th>
                  <th class="text-right" style="width:140px">Total</th>
                  <th style="width:60px"></th>
                </tr>
              </thead>
              <tbody>
                @foreach ($transactionsByCard as $idCard => $group)
                @php
                  $cardDesc  = optional($group->first()->credit_card)->descricao ?? '—';
                  $cardTotal = $group->sum('valor');
                @endphp
                <tr>
                  <td><i class="fa fa-credit-card fa-xs text-muted mr-1"></i> {{ $cardDesc }}</td>
                  <td class="text-center text-muted">{{ $group->count() }}</td>
                  <td class="text-right font-weight-bold">R$ {{ number_format($cardTotal, 2, ',', '.') }}</td>
                  <td class="text-right">
                    <a href="{{ route('transactions.month', array_merge([$year, $month], array_filter(request()->only(['t','categoria','pessoa','caixa'])), ['cartao' => $idCard])) }}"
                       class="btn btn-xs btn-outline-secondary" title="Filtrar por cartão">
                      <i class="fa fa-search fa-xs"></i>
                    </a>
                  </td>
                </tr>
                @endforeach
              </tbody>
              <tfoot class="font-weight-bold">
                <tr class="bg-light">
                  <td>Total</td>
                  <td class="text-center text-muted">{{ $transactionsByCard->flatten()->count() }}</td>
                  <td class="text-right">R$ {{ number_format($transactionsByCard->flatten()->sum('valor'), 2, ',', '.') }}</td>
                  <td></td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      </div>
      @endif

      {{-- View toggle + transaction list --}}
      <div class="col-md-12">

        <div class="d-flex justify-content-between align-items-center mb-2">
          <small class="text-muted">{{ $transactions->count() }} lançamento(s)</small>
          <div class="btn-group btn-group-sm" role="group" id="view-toggle">
            <button type="button" class="btn btn-outline-secondary" id="btn-view-table" title="Visualização em tabela">
              <i class="fas fa-table"></i>
            </button>
            <button type="button" class="btn btn-outline-secondary" id="btn-view-cards" title="Visualização em cartões">
              <i class="fas fa-th-list"></i>
            </button>
          </div>
        </div>

        {{-- TABLE VIEW --}}
        <div id="view-table">
          @if ($transactions->count() > 0)
          <div class="card">
            <div class="card-body p-0">
              <table class="table table-sm table-hover mb-0">
                <thead>
                  <tr>
                    <th class="d-none d-sm-table-cell th-sortable" style="width:90px; cursor:pointer; user-select:none" data-sort-key="data" data-sort-type="text">
                      Data <i class="fas fa-sort sort-icon text-muted ml-1" style="font-size:.75em"></i>
                    </th>
                    <th class="th-sortable" style="cursor:pointer; user-select:none" data-sort-key="descricao" data-sort-type="text">
                      Descrição <i class="fas fa-sort sort-icon text-muted ml-1" style="font-size:.75em"></i>
                    </th>
                    <th class="d-none d-md-table-cell th-sortable" style="width:120px; cursor:pointer; user-select:none" data-sort-key="categoria" data-sort-type="text">
                      Categoria <i class="fas fa-sort sort-icon text-muted ml-1" style="font-size:.75em"></i>
                    </th>
                    <th class="d-none d-md-table-cell th-sortable" style="width:90px; cursor:pointer; user-select:none" data-sort-key="tipo" data-sort-type="text">
                      Tipo <i class="fas fa-sort sort-icon text-muted ml-1" style="font-size:.75em"></i>
                    </th>
                    <th class="d-none d-md-table-cell th-sortable" style="width:120px; cursor:pointer; user-select:none" data-sort-key="cartao" data-sort-type="text">
                      Cartão <i class="fas fa-sort sort-icon text-muted ml-1" style="font-size:.75em"></i>
                    </th>
                    <th class="d-none d-md-table-cell th-sortable" style="width:120px; cursor:pointer; user-select:none" data-sort-key="pessoa" data-sort-type="text">
                      Pessoa <i class="fas fa-sort sort-icon text-muted ml-1" style="font-size:.75em"></i>
                    </th>
                    <th class="text-right th-sortable" style="width:100px; cursor:pointer; user-select:none" data-sort-key="valor" data-sort-type="number">
                      Valor <i class="fas fa-sort sort-icon text-muted ml-1" style="font-size:.75em"></i>
                    </th>
                    <th class="text-right d-none d-sm-table-cell" style="width:110px">Pagamento</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($transactions as $transaction)
                  @php
                    $tipoLabels = ['despesa'=>'Despesa','lucro'=>'Receita','transferencia'=>'Transferência','emprestimo'=>'Empréstimo','pagamento_emprestimo'=>'Pgto. Empréstimo'];
                    $tipoBadge  = ['despesa'=>'danger','lucro'=>'success','transferencia'=>'secondary','emprestimo'=>'warning','pagamento_emprestimo'=>'success'];
                  @endphp
                  <tr style="cursor:pointer"
                      onclick="window.location='/transactions/view/{{ $transaction->id }}'"
                      data-sort-data="{{ $transaction->data->format('Y-m-d') }}"
                      data-sort-descricao="{{ $transaction->descricao ?: $transaction->descricao_banco }}"
                      data-sort-categoria="{{ optional($transaction->category)->nome ?? '' }}"
                      data-sort-tipo="{{ $tipoLabels[$transaction->tipo] ?? $transaction->tipo ?? '' }}"
                      data-sort-cartao="{{ optional($transaction->credit_card)->descricao ?? '' }}"
                      data-sort-pessoa="{{ optional($transaction->contact)->nome ?? '' }}"
                      data-sort-valor="{{ $transaction->valor }}">
                    <td class="text-nowrap d-none d-sm-table-cell">{{ $transaction->data->format('d/m/Y') }}</td>
                    <td>
                      @if ($transaction->category)
                        <i class="{{ $transaction->category->icon_class }} text-muted mr-1" style="font-size:.8em"></i>
                      @endif
                      {{ $transaction->descricao ?: $transaction->descricao_banco }}
                      {{-- Mobile-only secondary info --}}
                      <div class="d-sm-none mt-1" style="font-size:.78em; line-height:1.6">
                        @if ($transaction->tipo)
                          <span class="badge badge-{{ $tipoBadge[$transaction->tipo] ?? 'light' }} mr-1">
                            {{ $tipoLabels[$transaction->tipo] ?? ucfirst($transaction->tipo) }}
                          </span>
                        @endif
                        @if ($transaction->category)
                          <span class="text-muted"><i class="fa fa-tag fa-xs"></i> {{ $transaction->category->nome }}</span>
                        @endif
                        @if ($transaction->credit_card)
                          <span class="text-muted ml-1"><i class="fa fa-credit-card fa-xs"></i> {{ $transaction->credit_card->descricao }}</span>
                        @endif
                        @if ($transaction->contact)
                          <span class="text-muted ml-1"><i class="fa fa-user fa-xs"></i> {{ $transaction->contact->nome }}</span>
                        @endif
                        <br>
                        @if ($transaction->data_pagamento)
                          <span class="text-success"><i class="fa fa-check"></i> Pago {{ \Carbon\Carbon::parse($transaction->data_pagamento)->format('d/m') }}</span>
                        @else
                          <span class="text-danger"><i class="fa fa-clock"></i> Pendente</span>
                        @endif
                      </div>
                    </td>
                    <td class="d-none d-md-table-cell">{{ $transaction->category->nome ?? '—' }}</td>
                    <td class="d-none d-md-table-cell">
                      @if ($transaction->tipo)
                        <span class="badge badge-{{ $tipoBadge[$transaction->tipo] ?? 'light' }}">
                          {{ $tipoLabels[$transaction->tipo] ?? ucfirst($transaction->tipo) }}
                        </span>
                      @endif
                    </td>
                    <td class="d-none d-md-table-cell">
                      @if ($transaction->credit_card)
                        <span><i class="fa fa-credit-card fa-xs text-muted"></i> {{ $transaction->credit_card->descricao }}</span>
                      @else
                        —
                      @endif
                    </td>
                    <td class="d-none d-md-table-cell">
                      @if ($transaction->contact)
                        <i class="fa fa-user fa-xs text-muted"></i> {{ $transaction->contact->nome }}
                      @else
                        —
                      @endif
                    </td>
                    <td class="text-right font-weight-bold text-nowrap">
                      R$ {{ number_format($transaction->valor, 2, ',', '.') }}
                    </td>
                    <td class="text-right d-none d-sm-table-cell text-nowrap">
                      @if ($transaction->data_pagamento)
                        <span class="text-success"><i class="fa fa-check"></i> {{ \Carbon\Carbon::parse($transaction->data_pagamento)->format('d/m/Y') }}</span>
                      @else
                        <span class="text-danger"><i class="fa fa-clock"></i> Pendente</span>
                      @endif
                    </td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
          @else
          <div class="alert alert-info">Nenhum lançamento encontrado para os filtros selecionados.</div>
          @endif
        </div>{{-- /#view-table --}}

        {{-- CARD VIEW --}}
        <div id="view-cards" style="display:none">
          @forelse ($transactions as $transaction)
          <a href="/transactions/view/{{ $transaction->id }}" class="info-box info-box-transaction">
            <span class="info-box-icon {{ $transaction->data_pagamento ? 'bg-secondary' : ($transaction->tipo === 'receita' ? 'bg-success' : 'bg-danger') }}">
              @if ($transaction->category)
              <i class="{{ $transaction->category->icon_class }}"></i>
              @else
              <i class="fas fa-exchange-alt"></i>
              @endif
            </span>
            <div class="info-box-content">
              <span class="info-box-text">{{ $transaction->descricao ?: $transaction->descricao_banco }}</span>
              <span class="info-box-number">
                <span>{{ $transaction->data->format('d/m/Y') }}</span>
                <span>R$ {{ number_format($transaction->valor, 2, ',', '.') }}</span>
              </span>
              <div class="categories">
                @if ($transaction->wallet)
                <span><i class="fa fa-wallet"></i> {{ $transaction->wallet->titulo }}</span>
                @endif

                @if ($transaction->category)
                <span><i class="fa fa-tag"></i> {{ $transaction->category->nome }}</span>
                @endif

                @if ($transaction->tipo)
                <span>{{ ucfirst($transaction->tipo) }}</span>
                @endif

                @if ($transaction->credit_card)
                <span><i class="fa fa-credit-card"></i> {{ $transaction->credit_card->descricao }}</span>
                @endif

                @if ($transaction->contact)
                  @if ($transaction->data_recebimento)
                    <span><i class="fa fa-user-check"></i> {{ $transaction->contact->nome }}</span>
                  @else
                    <span><i class="fa fa-user"></i> {{ $transaction->contact->nome }}</span>
                  @endif
                @endif

                @if ($transaction->data_pagamento)
                <span class="text-success"><i class="fa fa-check"></i> Pago em {{ \Carbon\Carbon::parse($transaction->data_pagamento)->format('d/m/Y') }}</span>
                @else
                <span class="text-danger"><i class="fa fa-clock"></i> Pendente</span>
                @endif
              </div>
            </div>
          </a>
          @empty
          <div class="alert alert-info">Nenhum lançamento encontrado para os filtros selecionados.</div>
          @endforelse
        </div>{{-- /#view-cards --}}

      </div>{{-- /.col --}}

    </div>{{-- /.row --}}
  </div>{{-- /.container --}}
</div>{{-- /.content --}}

<script>
(function () {
  // ── Table sorting ────────────────────────────────────────────────────────
  var sortState = { key: null, dir: 1 };

  function sortTable(key, type) {
    var table = document.querySelector('#view-table table');
    if (!table) return;
    var tbody = table.querySelector('tbody');
    var rows  = Array.from(tbody.querySelectorAll('tr'));

    // Toggle direction when clicking the same column, otherwise default asc
    if (sortState.key === key) {
      sortState.dir = sortState.dir === 1 ? -1 : 1;
    } else {
      sortState.key = key;
      sortState.dir = 1;
    }

    rows.sort(function (a, b) {
      var aVal = (a.dataset['sort' + key.charAt(0).toUpperCase() + key.slice(1)] || '').trim();
      var bVal = (b.dataset['sort' + key.charAt(0).toUpperCase() + key.slice(1)] || '').trim();

      var cmp;
      if (type === 'number') {
        cmp = parseFloat(aVal) - parseFloat(bVal);
      } else {
        cmp = aVal.localeCompare(bVal, 'pt-BR', { sensitivity: 'base' });
      }
      return cmp * sortState.dir;
    });

    rows.forEach(function (r) { tbody.appendChild(r); });

    // Update icons
    document.querySelectorAll('.th-sortable .sort-icon').forEach(function (icon) {
      icon.className = 'fas fa-sort sort-icon text-muted ml-1';
      icon.style.fontSize = '.75em';
    });
    var activeIcon = document.querySelector('.th-sortable[data-sort-key="' + key + '"] .sort-icon');
    if (activeIcon) {
      activeIcon.className = 'fas ' + (sortState.dir === 1 ? 'fa-sort-up' : 'fa-sort-down') + ' sort-icon text-primary ml-1';
    }
  }

  document.querySelectorAll('.th-sortable').forEach(function (th) {
    th.addEventListener('click', function () {
      sortTable(th.dataset.sortKey, th.dataset.sortType);
    });
  });
  // ─────────────────────────────────────────────────────────────────────────

  // ── View mode toggle ─────────────────────────────────────────────────────
  var STORAGE_KEY = 'transactions_view_mode';
  var mode = localStorage.getItem(STORAGE_KEY) || 'table';

  function applyMode(m) {
    if (m === 'cards') {
      document.getElementById('view-table').style.display = 'none';
      document.getElementById('view-cards').style.display = '';
      document.getElementById('btn-view-table').classList.remove('active');
      document.getElementById('btn-view-cards').classList.add('active');
    } else {
      document.getElementById('view-table').style.display = '';
      document.getElementById('view-cards').style.display = 'none';
      document.getElementById('btn-view-table').classList.add('active');
      document.getElementById('btn-view-cards').classList.remove('active');
    }
    localStorage.setItem(STORAGE_KEY, m);
  }

  document.getElementById('btn-view-table').addEventListener('click', function () { applyMode('table'); });
  document.getElementById('btn-view-cards').addEventListener('click', function () { applyMode('cards'); });

  applyMode(mode);
})();
</script>
@endsection
