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
            <td class="text-right text-nowrap">
              <a href="{{ route('transactions.month', array_merge([$year, $month], array_filter(request()->only(['t','categoria','pessoa','caixa'])), ['cartao' => $idCard])) }}"
                 class="btn btn-xs btn-outline-secondary" title="Filtrar por cartão">
                <i class="fa fa-search fa-xs"></i>
              </a>
              @php $pendingCount = $group->whereNull('data_pagamento')->count(); @endphp
              @if ($pendingCount > 0)
              <form method="POST"
                    action="{{ route('transactions.payCardBill', [$idCard, $year, $month]) }}"
                    class="d-inline"
                    onsubmit="return confirm('Confirmar pagamento da fatura do cartão {{ addslashes($cardDesc) }}? {{ $pendingCount }} lançamento(s) pendente(s) serão marcados como pagos hoje.')">
                @csrf
                <button type="submit" class="btn btn-xs btn-success ml-1" title="Confirmar pagamento da fatura">
                  <i class="fa fa-check fa-xs"></i> Pagar fatura
                </button>
              </form>
              @else
              <span class="btn btn-xs btn-outline-success ml-1 disabled" title="Fatura já paga">
                <i class="fa fa-check-double fa-xs"></i>
              </span>
              @endif
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
