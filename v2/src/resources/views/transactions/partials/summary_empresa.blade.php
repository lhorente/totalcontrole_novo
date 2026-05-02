{{-- DRE Summary (Empresa) --}}
@php
  $dreReceitas = $transactions->where('tipo', 'venda');
  $dreCustos   = $transactions->where('tipo', 'despesa');

  $totalReceita = $dreReceitas->sum('valor');
  $totalCustos  = $dreCustos->sum('valor');
  $margemBruta  = $totalReceita - $totalCustos;
  $margemPct    = $totalReceita > 0 ? round(($margemBruta / $totalReceita) * 100, 1) : 0;
  $qtdVendas    = $dreReceitas->count();
  $ticketMedio  = $qtdVendas > 0 ? $totalReceita / $qtdVendas : 0;

  $receitasPorCategoria = $dreReceitas->groupBy('id_categoria')->sortByDesc(fn($g) => $g->sum('valor'));
  $custosPorCategoria   = $dreCustos->groupBy('id_categoria')->sortByDesc(fn($g) => $g->sum('valor'));
@endphp

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

{{-- DRE Panel --}}
<div class="col-md-12 mb-3">
  <div class="card card-outline card-primary">
    <div class="card-header">
      <h3 class="card-title"><i class="fas fa-chart-bar mr-1"></i> DRE — Resultado do Período</h3>
    </div>
    <div class="card-body p-0">
      <div class="row no-gutters">

        {{-- RECEITAS --}}
        <div class="col-12 col-md-4 border-right d-flex flex-column">
          <div class="px-3 pt-3 pb-1 flex-grow-1">
            <p class="text-uppercase text-muted font-weight-bold mb-2" style="font-size:.75rem; letter-spacing:.05em">
              <i class="fas fa-arrow-up text-success mr-1"></i> Receitas
            </p>
            @forelse ($receitasPorCategoria as $idCat => $group)
            @php
              $catNome  = optional($group->first()->category)->nome ?? 'Sem categoria';
              $catTotal = $group->sum('valor');
            @endphp
            <div class="d-flex justify-content-between align-items-center mb-1" style="font-size:.9rem">
              <a href="{{ route('transactions.month', array_merge([$year, $month], array_filter(request()->only(['cartao','pessoa','caixa'])), ['t' => 'lucro', 'categoria' => $idCat])) }}"
                 class="text-dark text-decoration-none">
                {{ $catNome }}
              </a>
              <span class="text-success font-weight-bold text-nowrap ml-2">
                R$ {{ number_format($catTotal, 2, ',', '.') }}
              </span>
            </div>
            @empty
            <p class="text-muted small">Sem receitas no período.</p>
            @endforelse
          </div>
          <div class="d-flex justify-content-between align-items-center px-3 py-2 bg-light border-top font-weight-bold">
            <span>Total receita</span>
            <span class="text-success text-nowrap">R$ {{ number_format($totalReceita, 2, ',', '.') }}</span>
          </div>
        </div>

        {{-- CUSTOS --}}
        <div class="col-12 col-md-4 border-right d-flex flex-column">
          <div class="px-3 pt-3 pb-1 flex-grow-1">
            <p class="text-uppercase text-muted font-weight-bold mb-2" style="font-size:.75rem; letter-spacing:.05em">
              <i class="fas fa-arrow-down text-danger mr-1"></i> Custos
            </p>
            @forelse ($custosPorCategoria as $idCat => $group)
            @php
              $catNome  = optional($group->first()->category)->nome ?? 'Sem categoria';
              $catTotal = $group->sum('valor');
            @endphp
            <div class="d-flex justify-content-between align-items-center mb-1" style="font-size:.9rem">
              <a href="{{ route('transactions.month', array_merge([$year, $month], array_filter(request()->only(['cartao','pessoa','caixa'])), ['t' => 'despesa', 'categoria' => $idCat])) }}"
                 class="text-dark text-decoration-none">
                {{ $catNome }}
              </a>
              <span class="text-danger font-weight-bold text-nowrap ml-2">
                R$ {{ number_format($catTotal, 2, ',', '.') }}
              </span>
            </div>
            @empty
            <p class="text-muted small">Sem custos no período.</p>
            @endforelse
          </div>
          <div class="d-flex justify-content-between align-items-center px-3 py-2 bg-light border-top font-weight-bold">
            <span>Total custos</span>
            <span class="text-danger text-nowrap">R$ {{ number_format($totalCustos, 2, ',', '.') }}</span>
          </div>
        </div>

        {{-- RESULTADO --}}
        <div class="col-12 col-md-4 d-flex flex-column">
          <div class="px-3 pt-3 pb-1 flex-grow-1">
            <p class="text-uppercase text-muted font-weight-bold mb-2" style="font-size:.75rem; letter-spacing:.05em">
              <i class="fas fa-balance-scale mr-1"></i> Resultado
            </p>
            <div class="d-flex justify-content-between align-items-center mb-1" style="font-size:.9rem">
              <span class="text-muted">Margem bruta</span>
              <span class="font-weight-bold text-nowrap ml-2 {{ $margemBruta >= 0 ? 'text-success' : 'text-danger' }}">
                R$ {{ number_format($margemBruta, 2, ',', '.') }}
              </span>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-1" style="font-size:.9rem">
              <span class="text-muted">Margem %</span>
              <span class="font-weight-bold text-nowrap ml-2 {{ $margemPct >= 0 ? 'text-success' : 'text-danger' }}">
                {{ number_format($margemPct, 1, ',', '.') }}%
              </span>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-1" style="font-size:.9rem">
              <span class="text-muted">Ticket médio</span>
              <span class="font-weight-bold text-nowrap ml-2">
                R$ {{ number_format($ticketMedio, 2, ',', '.') }}
              </span>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-1" style="font-size:.9rem">
              <span class="text-muted">Qtd. receitas</span>
              <span class="font-weight-bold text-nowrap ml-2">{{ $qtdVendas }}</span>
            </div>
          </div>
          <div class="d-flex justify-content-between align-items-center px-3 py-2 bg-light border-top font-weight-bold">
            <span>Resultado</span>
            <span class="text-nowrap {{ $margemPct >= 0 ? 'text-success' : 'text-danger' }}">
              {{ $margemPct >= 0 ? '+' : '' }}{{ number_format($margemPct, 1, ',', '.') }}%
            </span>
          </div>
        </div>

      </div>{{-- /.row --}}
    </div>
  </div>
</div>

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
