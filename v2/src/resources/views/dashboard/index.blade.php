@extends('layouts.dashboard')

@section('content')
<div class="content-header">
  <div class="container">
    <div class="row mb-2 align-items-center">
      <div class="col-sm-6">
        <h1 class="m-0 text-dark">Dashboard</h1>
      </div>
      <div class="col-sm-6 text-right">
        <a href="{{ route('transactions.create') }}" class="btn btn-primary btn-sm">
          <i class="fas fa-plus mr-1"></i> Novo Lançamento
        </a>
      </div>
    </div>
  </div>
</div>

<div class="content">
  <div class="container">

    {{-- ================================================================== --}}
    {{--  6-MONTH SUMMARY                                                    --}}
    {{-- ================================================================== --}}
    @php
      $mesesNomes = [1=>'Janeiro',2=>'Fevereiro',3=>'Março',4=>'Abril',5=>'Maio',6=>'Junho',
                     7=>'Julho',8=>'Agosto',9=>'Setembro',10=>'Outubro',11=>'Novembro',12=>'Dezembro'];
      $tipoLabels = ['despesa'=>'Despesa','receita'=>'Receita','transferencia'=>'Transferência','emprestimo'=>'Empréstimo'];
      $tipoBadge  = ['despesa'=>'danger','receita'=>'success','transferencia'=>'secondary','emprestimo'=>'warning'];
    @endphp

    <div class="row">
      <div class="col-12">
        <h5 class="text-muted mb-2"><i class="fas fa-calendar-alt mr-1"></i> Resumo Mensal</h5>
      </div>
    </div>

    {{-- Month tabs --}}
    <ul class="nav nav-tabs" id="monthTabs" role="tablist">
      @foreach ($monthlySummaries as $i => $summary)
      @php $tabId = 'month-' . $summary['date']->format('Y-m'); @endphp
      <li class="nav-item">
        <a class="nav-link {{ $i === 0 ? 'active' : '' }}"
           id="{{ $tabId }}-tab"
           data-toggle="tab"
           href="#{{ $tabId }}"
           role="tab">
          {{ $summary['date']->format('M/Y') }}
          @if ($summary['transactions']->count() > 0)
            <span class="badge badge-secondary ml-1">{{ $summary['transactions']->count() }}</span>
          @endif
        </a>
      </li>
      @endforeach
    </ul>

    <div class="tab-content border border-top-0 rounded-bottom bg-white p-3 mb-4" id="monthTabContent">
      @foreach ($monthlySummaries as $i => $summary)
      @php
        $tabId    = 'month-' . $summary['date']->format('Y-m');
        $y        = $summary['date']->year;
        $m        = $summary['date']->month;
        $txAll    = $summary['transactions'];
        $byCat    = $summary['by_category'];
        $byCard   = $summary['by_card'];
        $lending  = $summary['lendings'];
        $lendPend = $lending->filter(fn($t) => !$t->data_recebimento)->sum('valor');
        $lendTot  = $lending->sum('valor');
      @endphp
      <div class="tab-pane fade {{ $i === 0 ? 'show active' : '' }}"
           id="{{ $tabId }}" role="tabpanel">

        @if ($txAll->count() === 0)
          <p class="text-muted mt-2 mb-0">Nenhum lançamento para {{ $mesesNomes[$m] }}/{{ $y }}.</p>
          <a href="{{ route('transactions.month', [$y, $m]) }}" class="btn btn-sm btn-outline-primary mt-2">
            <i class="fa fa-search mr-1"></i> Ver mês
          </a>
        @else

        {{-- Quick totals row --}}
        @php
          $aPagar   = $txAll->where('tipo','despesa')->whereNull('data_pagamento')->sum('valor');
          $pago     = $txAll->where('tipo','despesa')->whereNotNull('data_pagamento')->sum('valor');
          $aReceber = $txAll->where('tipo','receita')->whereNull('data_pagamento')->sum('valor');
          $recebido = $txAll->where('tipo','receita')->whereNotNull('data_pagamento')->sum('valor');
        @endphp
        <div class="row mb-3">
          <div class="col-6 col-md-3">
            <div class="info-box mb-2">
              <span class="info-box-icon bg-danger"><i class="fas fa-arrow-down"></i></span>
              <div class="info-box-content">
                <span class="info-box-text">A pagar</span>
                <span class="info-box-number" style="font-size:1em">R$ {{ number_format($aPagar, 2, ',', '.') }}</span>
              </div>
            </div>
          </div>
          <div class="col-6 col-md-3">
            <div class="info-box mb-2">
              <span class="info-box-icon bg-secondary"><i class="fas fa-check"></i></span>
              <div class="info-box-content">
                <span class="info-box-text">Pago</span>
                <span class="info-box-number" style="font-size:1em">R$ {{ number_format($pago, 2, ',', '.') }}</span>
              </div>
            </div>
          </div>
          <div class="col-6 col-md-3">
            <div class="info-box mb-2">
              <span class="info-box-icon bg-success"><i class="fas fa-arrow-up"></i></span>
              <div class="info-box-content">
                <span class="info-box-text">A receber</span>
                <span class="info-box-number" style="font-size:1em">R$ {{ number_format($aReceber, 2, ',', '.') }}</span>
              </div>
            </div>
          </div>
          <div class="col-6 col-md-3">
            <div class="info-box mb-2">
              <span class="info-box-icon bg-info"><i class="fas fa-wallet"></i></span>
              <div class="info-box-content">
                <span class="info-box-text">Recebido</span>
                <span class="info-box-number" style="font-size:1em">R$ {{ number_format($recebido, 2, ',', '.') }}</span>
              </div>
            </div>
          </div>
        </div>

        <div class="row">

          {{-- By Category --}}
          <div class="col-md-4">
            <div class="card card-primary card-outline mb-2">
              <div class="card-header py-2">
                <h6 class="card-title mb-0"><i class="fas fa-tags mr-1"></i> Por Categoria</h6>
              </div>
              <div class="card-body p-0">
                <table class="table table-sm mb-0">
                  <tbody>
                    @forelse ($byCat as $idCat => $group)
                    @php
                      $catNome  = optional($group->first()->category)->nome ?? 'Sem categoria';
                      $catIcon  = optional($group->first()->category)->icon_class;
                      $catTotal = $group->sum('valor');
                    @endphp
                    <tr>
                      <td style="font-size:.85em">
                        @if ($catIcon)<i class="{{ $catIcon }} text-muted mr-1"></i>@endif
                        {{ $catNome }}
                      </td>
                      <td class="text-right text-nowrap" style="font-size:.85em">
                        <strong>R$ {{ number_format($catTotal, 2, ',', '.') }}</strong>
                      </td>
                      <td style="width:32px" class="text-right">
                        @if ($idCat)
                        <a href="{{ route('transactions.month', [$y, $m, 'categoria' => $idCat]) }}"
                           class="btn btn-xs btn-outline-secondary" title="Ver">
                          <i class="fa fa-search fa-xs"></i>
                        </a>
                        @endif
                      </td>
                    </tr>
                    @empty
                    <tr><td class="text-muted" colspan="3">—</td></tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          {{-- By Credit Card --}}
          <div class="col-md-4">
            <div class="card card-dark card-outline mb-2">
              <div class="card-header py-2">
                <h6 class="card-title mb-0"><i class="fas fa-credit-card mr-1"></i> Por Cartão</h6>
              </div>
              <div class="card-body p-0">
                <table class="table table-sm mb-0">
                  <tbody>
                    @forelse ($byCard as $idCard => $group)
                    @php
                      $cardDesc  = optional($group->first()->credit_card)->descricao ?? '—';
                      $cardTotal = $group->sum('valor');
                    @endphp
                    <tr>
                      <td style="font-size:.85em">
                        <i class="fa fa-credit-card fa-xs text-muted mr-1"></i> {{ $cardDesc }}
                      </td>
                      <td class="text-right text-nowrap" style="font-size:.85em">
                        <strong>R$ {{ number_format($cardTotal, 2, ',', '.') }}</strong>
                      </td>
                      <td style="width:32px" class="text-right">
                        <a href="{{ route('transactions.month', [$y, $m, 'cartao' => $idCard]) }}"
                           class="btn btn-xs btn-outline-secondary" title="Ver">
                          <i class="fa fa-search fa-xs"></i>
                        </a>
                      </td>
                    </tr>
                    @empty
                    <tr><td class="text-muted" colspan="3">Nenhum lançamento em cartão</td></tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          {{-- Lending summary --}}
          <div class="col-md-4">
            <div class="card card-warning card-outline mb-2">
              <div class="card-header py-2">
                <h6 class="card-title mb-0"><i class="fas fa-hand-holding-usd mr-1"></i> Empréstimos</h6>
              </div>
              <div class="card-body p-0">
                @if ($lending->count() === 0)
                  <p class="text-muted p-2 mb-0" style="font-size:.85em">Nenhum empréstimo no mês.</p>
                @else
                @php $lendByPessoa = $lending->groupBy('id_cliente'); @endphp
                <table class="table table-sm mb-0">
                  <tbody>
                    @foreach ($lendByPessoa as $idCli => $group)
                    @php
                      $nome = optional($group->first()->contact)->nome ?? 'Sem pessoa';
                      $pend = $group->filter(fn($t) => !$t->data_recebimento)->sum('valor');
                      $tot  = $group->sum('valor');
                    @endphp
                    <tr>
                      <td style="font-size:.85em">
                        <i class="fa fa-user fa-xs text-muted mr-1"></i> {{ $nome }}
                        @if ($pend > 0)
                          <br><span class="text-warning" style="font-size:.9em"><i class="fa fa-clock fa-xs"></i> Pend: R$ {{ number_format($pend, 2, ',', '.') }}</span>
                        @endif
                      </td>
                      <td class="text-right text-nowrap" style="font-size:.85em">
                        <strong>R$ {{ number_format($tot, 2, ',', '.') }}</strong>
                      </td>
                      <td style="width:32px" class="text-right">
                        <a href="{{ route('transactions.month', [$y, $m, 't' => 'emprestimo', 'pessoa' => $idCli]) }}"
                           class="btn btn-xs btn-outline-secondary" title="Ver">
                          <i class="fa fa-search fa-xs"></i>
                        </a>
                      </td>
                    </tr>
                    @endforeach
                  </tbody>
                  <tfoot>
                    <tr class="bg-light font-weight-bold">
                      <td style="font-size:.85em">Total</td>
                      <td class="text-right text-nowrap {{ $lendPend > 0 ? 'text-warning' : '' }}" style="font-size:.85em">
                        R$ {{ number_format($lendTot, 2, ',', '.') }}
                      </td>
                      <td></td>
                    </tr>
                  </tfoot>
                </table>
                @endif
              </div>
            </div>
          </div>

        </div>{{-- /.row summaries --}}

        <div class="text-right mt-1 mb-1">
          <a href="{{ route('transactions.month', [$y, $m]) }}" class="btn btn-sm btn-outline-primary">
            <i class="fa fa-list mr-1"></i> Ver todos os lançamentos de {{ $mesesNomes[$m] }}/{{ $y }}
          </a>
        </div>

        @endif
      </div>{{-- /.tab-pane --}}
      @endforeach
    </div>{{-- /.tab-content --}}

    {{-- ================================================================== --}}
    {{--  RECENT TRANSACTIONS                                                --}}
    {{-- ================================================================== --}}
    <div class="row mt-2">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title"><i class="fas fa-history mr-1"></i> Últimos 10 Lançamentos Adicionados</h3>
            <div class="card-tools">
              <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
              </button>
            </div>
          </div>
          <div class="card-body p-0">
            @if ($recentTransactions->count() > 0)
            <table class="table table-sm table-hover mb-0">
              <thead>
                <tr>
                  <th class="d-none d-sm-table-cell" style="width:90px">Data</th>
                  <th>Descrição</th>
                  <th class="d-none d-md-table-cell" style="width:120px">Categoria</th>
                  <th class="d-none d-sm-table-cell" style="width:90px">Tipo</th>
                  <th class="text-right" style="width:110px">Valor</th>
                  <th class="d-none d-sm-table-cell text-right" style="width:110px">Pagamento</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($recentTransactions as $tx)
                <tr style="cursor:pointer" onclick="window.location='{{ route('transactions.view', $tx->id) }}'">
                  <td class="text-nowrap d-none d-sm-table-cell">{{ $tx->data->format('d/m/Y') }}</td>
                  <td>
                    @if ($tx->category)
                      <i class="{{ $tx->category->icon_class }} text-muted mr-1" style="font-size:.8em"></i>
                    @endif
                    {{ $tx->descricao ?: $tx->descricao_banco }}
                    <div class="d-sm-none" style="font-size:.78em">
                      {{ $tx->data->format('d/m/Y') }}
                      @if ($tx->tipo)
                        &bull; <span class="badge badge-{{ $tipoBadge[$tx->tipo] ?? 'light' }}">{{ $tipoLabels[$tx->tipo] ?? ucfirst($tx->tipo) }}</span>
                      @endif
                    </div>
                  </td>
                  <td class="d-none d-md-table-cell">{{ $tx->category->nome ?? '—' }}</td>
                  <td class="d-none d-sm-table-cell">
                    @if ($tx->tipo)
                      <span class="badge badge-{{ $tipoBadge[$tx->tipo] ?? 'light' }}">
                        {{ $tipoLabels[$tx->tipo] ?? ucfirst($tx->tipo) }}
                      </span>
                    @endif
                  </td>
                  <td class="text-right font-weight-bold text-nowrap">
                    R$ {{ number_format($tx->valor, 2, ',', '.') }}
                  </td>
                  <td class="d-none d-sm-table-cell text-right text-nowrap">
                    @if ($tx->data_pagamento)
                      <span class="text-success"><i class="fa fa-check"></i> {{ \Carbon\Carbon::parse($tx->data_pagamento)->format('d/m/Y') }}</span>
                    @else
                      <span class="text-danger"><i class="fa fa-clock"></i> Pendente</span>
                    @endif
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
            @else
              <p class="text-muted p-3 mb-0">Nenhum lançamento encontrado.</p>
            @endif
          </div>
          <div class="card-footer text-right">
            <a href="{{ route('transactions.month') }}" class="btn btn-sm btn-outline-secondary">
              <i class="fa fa-list mr-1"></i> Ver todos os lançamentos
            </a>
            <a href="{{ route('transactions.create') }}" class="btn btn-sm btn-primary ml-1">
              <i class="fa fa-plus mr-1"></i> Novo Lançamento
            </a>
          </div>
        </div>
      </div>
    </div>

  </div>{{-- /.container --}}
</div>{{-- /.content --}}
@endsection
