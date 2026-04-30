@extends('layouts.dashboard')

@section('content')

<div class="content-header">
  <div class="container">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 text-dark">Cartões de crédito</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
          <li class="breadcrumb-item active">Cartões de crédito</li>
        </ol>
      </div>
    </div>
  </div>
</div>

<div class="content">
  <div class="container">

    {{-- Navegação de mês --}}
    <div class="d-flex align-items-center justify-content-between mb-3">
      <a href="{{ route('transactions.creditCards', ['year' => $prevDate->year, 'month' => $prevDate->month]) }}"
         class="btn btn-sm btn-outline-secondary">
        <i class="fa fa-chevron-left"></i>
      </a>
      <h5 class="mb-0">{{ ucfirst($currentDate->translatedFormat('F / Y')) }}</h5>
      <a href="{{ route('transactions.creditCards', ['year' => $nextDate->year, 'month' => $nextDate->month]) }}"
         class="btn btn-sm btn-outline-secondary">
        <i class="fa fa-chevron-right"></i>
      </a>
    </div>

    {{-- Cards de totais --}}
    <div class="row mb-3">
      <div class="col-6 col-md-3">
        <div class="small-box bg-white shadow-sm border">
          <div class="inner">
            <p class="text-muted mb-0" style="font-size:.8rem">Total em faturas</p>
            <h5 class="text-danger mb-0">R$ {{ number_format($totals['geral'], 0, ',', '.') }}</h5>
          </div>
        </div>
      </div>
      @foreach ($workspaces as $ws)
      <div class="col-6 col-md-3">
        <div class="small-box bg-white shadow-sm border">
          <div class="inner">
            <p class="text-muted mb-0" style="font-size:.8rem">{{ $ws->nome }}</p>
            <h5 class="mb-0">R$ {{ number_format($totals[$ws->id], 0, ',', '.') }}</h5>
          </div>
        </div>
      </div>
      @endforeach
      @if ($totals['em_aberto_empresa'] > 0)
      <div class="col-6 col-md-3">
        <div class="small-box bg-white shadow-sm border-warning border">
          <div class="inner">
            <p class="text-muted mb-0" style="font-size:.8rem">Empresa em aberto</p>
            <h5 class="text-warning mb-0">R$ {{ number_format($totals['em_aberto_empresa'], 0, ',', '.') }}</h5>
          </div>
        </div>
      </div>
      @endif
    </div>

    <p class="text-muted small mb-2">FATURAS DO MÊS</p>

    {{-- Cards dos cartões --}}
    <div class="row">
      @forelse ($cardData as $item)
      @php
        $card = $item['card'];
        $initials = strtoupper(substr($card->descricao, 0, 2));
        $colors = ['bg-primary','bg-success','bg-danger','bg-warning','bg-info','bg-secondary'];
        $color = $colors[$loop->index % count($colors)];
      @endphp
      <div class="col-md-6 mb-3">
        <div class="card shadow-sm h-100">
          <div class="card-header d-flex align-items-center justify-content-between py-2">
            <div class="d-flex align-items-center">
              <span class="badge {{ $color }} mr-2 p-2" style="font-size:.85rem;min-width:36px">
                {{ $initials }}
              </span>
              <div>
                <strong>{{ $card->descricao }}</strong><br>
                <small class="text-muted">{{ $card->nome_titular ?? Auth::user()->name }}</small>
              </div>
            </div>
            @if ($item['pago'])
              <span class="badge badge-success">Pago</span>
            @else
              <span class="badge badge-warning text-dark">Em aberto</span>
            @endif
          </div>

          <div class="card-body py-2">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="text-muted">Fatura atual</span>
              <strong>R$ {{ number_format($item['fatura'], 0, ',', '.') }}</strong>
            </div>

            @if ($item['fatura'] > 0)
            {{-- Barra de breakdown --}}
            @php
              $wsColors = ['#4e73df','#1cc88a','#e74a3b','#f6c23e','#36b9cc'];
              $wsList = collect($item['breakdown'])->values();
              $segments = [];
              foreach ($wsList as $idx => $b) {
                $pct = $item['fatura'] > 0 ? round($b['total'] / $item['fatura'] * 100) : 0;
                $segments[] = ['pct' => $pct, 'color' => $wsColors[$idx % count($wsColors)]];
              }
            @endphp
            <div class="progress mb-2" style="height:6px;border-radius:3px">
              @foreach ($segments as $seg)
                <div class="progress-bar" style="width:{{ $seg['pct'] }}%;background:{{ $seg['color'] }}"></div>
              @endforeach
            </div>
            @endif

            {{-- Breakdown por workspace --}}
            @foreach ($item['breakdown'] as $wsId => $b)
            @php $wsColor = $wsColors[$loop->index % count($wsColors)]; @endphp
            <div class="d-flex justify-content-between small">
              <span>
                <span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:{{ $wsColor }};margin-right:4px"></span>
                {{ $b['nome'] }}
              </span>
              <span>R$ {{ number_format($b['total'], 0, ',', '.') }}</span>
            </div>
            @endforeach

            {{-- Indicador empresa em aberto --}}
            @if ($item['em_aberto_empresa'] > 0)
            <div class="mt-2 p-2 rounded" style="background:#fff8e1;border-left:3px solid #f6c23e">
              <i class="fas fa-clock text-warning mr-1"></i>
              <small>
                @foreach ($workspaces->where('tipo', 'empresa') as $ws)
                  {{ $ws->nome }}
                @endforeach
                deve R$ {{ number_format($item['em_aberto_empresa'], 0, ',', '.') }}
              </small>
            </div>
            @endif
          </div>

          <div class="card-footer py-2 text-right">
            <a href="{{ route('transactions.month', ['year' => $year, 'month' => $month]) }}?cartao={{ $card->id }}"
               class="btn btn-sm btn-outline-secondary">
              <i class="fa fa-chevron-down mr-1"></i> Ver lançamentos
            </a>
          </div>
        </div>
      </div>
      @empty
      <div class="col-12">
        <div class="alert alert-info">Nenhum cartão de crédito cadastrado.</div>
      </div>
      @endforelse
    </div>

  </div>
</div>

@endsection
