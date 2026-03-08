@extends('layouts.dashboard')

@section('content')
<div class="content-header">
  <div class="container">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 text-dark">Busca de Lançamentos</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
          <li class="breadcrumb-item"><a href="{{ route('transactions.search') }}">Busca</a></li>
          <li class="breadcrumb-item active">Lançamentos</li>
        </ol>
      </div>
    </div>
  </div>
</div>

<div class="content">
  <div class="container">
    <div class="row justify-content-center">

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
            <form method="GET" action="{{ route('transactions.search') }}">

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

                {{-- Period (year) --}}
                <div class="col-md-2">
                  <div class="form-group">
                    <label>Ano</label>
                    <input type="number" class="form-control" name="y" value="{{ $year }}" placeholder="Ano" />
                  </div>
                </div>

                {{-- Period (month) --}}
                <div class="col-md-2">
                  <div class="form-group">
                    <label>Mês</label>
                    <select class="form-control" name="m">
                      <option value="">Ano inteiro</option>
                      @foreach ([1=>'Janeiro',2=>'Fevereiro',3=>'Março',4=>'Abril',5=>'Maio',6=>'Junho',7=>'Julho',8=>'Agosto',9=>'Setembro',10=>'Outubro',11=>'Novembro',12=>'Dezembro'] as $n => $nome)
                        <option value="{{ $n }}" @if((string)$month === (string)$n) selected @endif>{{ $nome }}</option>
                      @endforeach
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
                      <option value="receita"      @selected($type === 'receita')>Receita</option>
                      <option value="transferencia" @selected($type === 'transferencia')>Transferência</option>
                      <option value="emprestimo"   @selected($type === 'emprestimo')>Empréstimo</option>
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
      @if ($categoria || $cartao || $pessoa || $caixa || $type || $month)
      <div class="col-md-12 mb-2">
        <div>
          <strong>Filtros ativos:</strong>
          @if ($month)
            @php $meses = [1=>'Jan',2=>'Fev',3=>'Mar',4=>'Abr',5=>'Mai',6=>'Jun',7=>'Jul',8=>'Ago',9=>'Set',10=>'Out',11=>'Nov',12=>'Dez']; @endphp
            <span class="badge badge-secondary">{{ $meses[$month] ?? $month }}/{{ $year }}</span>
          @else
            <span class="badge badge-secondary">Ano: {{ $year }}</span>
          @endif
          @if ($type)<span class="badge badge-info">{{ ucfirst($type) }}</span>@endif
          @if ($categoria)<span class="badge badge-warning">Cat: {{ $categoria->nome }}</span>@endif
          @if ($cartao)<span class="badge badge-dark">Cartão: {{ $cartao->descricao }}</span>@endif
          @if ($pessoa)<span class="badge badge-primary">Pessoa: {{ $pessoa->nome }}</span>@endif
          @if ($caixa)<span class="badge badge-success">Carteira: {{ $caixa->titulo }}</span>@endif
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

      {{-- Navigation (only when filtering by month) --}}
      @if ($month)
      <div class="col-md-12 d-flex justify-content-between mb-2">
        <a href="{{ route('transactions.search', array_filter(['y' => $beforeMonthObj->format('Y'), 'm' => (int)$beforeMonthObj->format('m')])) }}" class="btn btn-sm btn-outline-secondary">
          <i class="fa fa-chevron-left"></i> {{ $beforeMonthObj->format('M/Y') }}
        </a>
        <strong class="align-self-center">
          @php $meses = [1=>'Janeiro',2=>'Fevereiro',3=>'Março',4=>'Abril',5=>'Maio',6=>'Junho',7=>'Julho',8=>'Agosto',9=>'Setembro',10=>'Outubro',11=>'Novembro',12=>'Dezembro']; @endphp
          {{ $meses[$month] ?? $month }} / {{ $year }}
        </strong>
        <a href="{{ route('transactions.search', array_filter(['y' => $nextMonthObj->format('Y'), 'm' => (int)$nextMonthObj->format('m')])) }}" class="btn btn-sm btn-outline-secondary">
          {{ $nextMonthObj->format('M/Y') }} <i class="fa fa-chevron-right"></i>
        </a>
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
                    <th style="width:100px">Data</th>
                    <th>Descrição</th>
                    <th style="width:120px">Categoria</th>
                    <th style="width:90px">Tipo</th>
                    <th style="width:120px">Cartão</th>
                    <th style="width:120px">Pessoa</th>
                    <th class="text-right" style="width:120px">Valor</th>
                    <th style="width:110px">Pagamento</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($transactions as $transaction)
                  <tr class=""
                      style="cursor:pointer"
                      onclick="window.location='/transactions/edit/{{ $transaction->id }}'">
                    <td>{{ $transaction->data->format('d/m/Y') }}</td>
                    <td>
                      @if ($transaction->category)
                        <i class="{{ $transaction->category->icon_class }} text-muted mr-1" style="font-size:.8em"></i>
                      @endif
                      {{ $transaction->descricao ?: $transaction->descricao_banco }}
                    </td>
                    <td>{{ $transaction->category->nome ?? '—' }}</td>
                    <td>
                      @php
                        $tipoLabels = ['despesa'=>'Despesa','receita'=>'Receita','transferencia'=>'Transferência','emprestimo'=>'Empréstimo'];
                        $tipoBadge  = ['despesa'=>'danger','receita'=>'success','transferencia'=>'secondary','emprestimo'=>'warning'];
                      @endphp
                      @if ($transaction->tipo)
                        <span class="badge badge-{{ $tipoBadge[$transaction->tipo] ?? 'light' }}">
                          {{ $tipoLabels[$transaction->tipo] ?? ucfirst($transaction->tipo) }}
                        </span>
                      @endif
                    </td>
                    <td>
                      @if ($transaction->credit_card)
                        <span><i class="fa fa-credit-card fa-xs text-muted"></i> {{ $transaction->credit_card->descricao }}</span>
                      @else
                        —
                      @endif
                    </td>
                    <td>
                      @if ($transaction->contact)
                        <i class="fa fa-user fa-xs text-muted"></i> {{ $transaction->contact->nome }}
                      @else
                        —
                      @endif
                    </td>
                    <td class="text-right font-weight-bold">
                      R$ {{ number_format($transaction->valor, 2, ',', '.') }}
                    </td>
                    <td>
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
          <a href="/transactions/edit/{{ $transaction->id }}" class="info-box info-box-transaction">
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
