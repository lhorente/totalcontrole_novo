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
        $wsColors = ['#4e73df','#1cc88a','#e74a3b','#f6c23e','#36b9cc'];
        $hasChildren = count($item['children']) > 0;
        $sync = $item['sync'];
      @endphp
      <div class="col-md-6 mb-3">
        <div class="card shadow-sm h-100">
          <div class="card-header d-flex align-items-center justify-content-between py-2">
            <div class="d-flex align-items-center">
              <span class="badge {{ $color }} mr-2 p-2" style="font-size:.85rem;min-width:36px">
                {{ $initials }}
              </span>
              <div>
                <strong>{{ $card->descricao }}</strong>
                @if ($hasChildren)
                  <span class="badge badge-light border ml-1" style="font-size:.7rem">
                    {{ count($item['children']) }} vinculado{{ count($item['children']) > 1 ? 's' : '' }}
                  </span>
                @endif
                <br>
                <small class="text-muted">
                  {{ $card->nome_titular ?? Auth::user()->name }}
                  @if ($card->ultimos_digitos) · final {{ $card->ultimos_digitos }} @endif
                </small>
              </div>
            </div>
            @if ($item['pago'])
              <span class="badge badge-success">Pago</span>
            @else
              <span class="badge badge-warning text-dark">Em aberto</span>
            @endif
          </div>

          @if ($sync)
          @php
            $providerLabels = ['nubank' => 'Nubank', 'bradesco' => 'Bradesco'];
            $providerLabel = $providerLabels[$sync->provider] ?? ucfirst($sync->provider);
            $syncDate = $sync->last_sync_at ?? $sync->updated_at;
            $syncDateFmt = $syncDate ? $syncDate->format('d/m') . ' às ' . $syncDate->format('H:i') : null;
            $syncDot = $sync->status === 'ativo' ? '#1cc88a' : ($sync->status === 'mfa_pendente' ? '#f6c23e' : '#e74a3b');
          @endphp
          <div class="px-3 py-2 small d-flex align-items-center {{ $sync->status === 'ativo' ? 'text-muted' : ($sync->status === 'mfa_pendente' ? 'text-warning' : 'text-danger') }}"
               style="border-bottom:1px solid #eee; {{ $sync->status !== 'ativo' ? 'background:#fff8f8' : '' }}">
            <span style="display:inline-block;width:6px;height:6px;border-radius:50%;margin-right:6px;flex:none;background:{{ $syncDot }}"></span>
            @if ($sync->status === 'ativo')
              Sincronizado com {{ $providerLabel }} via Pluggy {{ $syncDateFmt ? 'em ' . $syncDateFmt : '' }}
            @elseif ($sync->status === 'mfa_pendente')
              Aguardando confirmação no app do {{ $providerLabel }} · Pluggy
            @else
              Falha ao sincronizar com {{ $providerLabel }} via Pluggy{{ $syncDateFmt ? ' · última tentativa em ' . $syncDateFmt : '' }}
            @endif
          </div>
          @endif

          <div class="card-body py-2">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="text-muted">{{ $hasChildren ? 'Fatura consolidada' : 'Fatura atual' }}</span>
              <strong>R$ {{ number_format($item['fatura'], 0, ',', '.') }}</strong>
            </div>

            @if ($item['fatura'] > 0)
            {{-- Barra de breakdown --}}
            @php
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
                @if ($hasChildren) <span class="text-muted">(somado dos vinculados)</span> @endif
              </small>
            </div>
            @endif
          </div>

          <div class="card-footer py-2">
            @if ($hasChildren)
            <button type="button" class="btn btn-sm btn-link p-0 mb-2 text-decoration-none"
                    data-toggle="collapse" data-target="#children-{{ $card->id }}"
                    aria-expanded="false" aria-controls="children-{{ $card->id }}">
              <i class="fa fa-chevron-right mr-1"></i> Ver cartões vinculados ({{ count($item['children']) }})
            </button>
            <div class="collapse" id="children-{{ $card->id }}">
              <div class="list-group list-group-flush mb-2" style="font-size:.85rem">
                @foreach ($item['children'] as $child)
                @php $childCard = $child['card']; @endphp
                <div class="list-group-item d-flex align-items-center justify-content-between px-0 py-2 {{ $child['fatura'] == 0 ? 'text-muted' : '' }}">
                  <div class="d-flex align-items-center" style="min-width:0">
                    <span class="badge bg-secondary mr-2" style="font-size:.65rem;min-width:26px">
                      {{ strtoupper(substr($childCard->descricao, 0, 2)) }}
                    </span>
                    <span class="text-truncate" style="max-width:150px">{{ $childCard->descricao }}</span>
                  </div>
                  <div class="d-flex align-items-center">
                    <strong class="mr-2">R$ {{ number_format($child['fatura'], 0, ',', '.') }}</strong>
                    <a href="{{ route('transactions.cardTransactions', ['cardId' => $childCard->id, 'year' => $year, 'month' => $month]) }}" class="small">
                      Ver →
                    </a>
                  </div>
                </div>
                @endforeach
              </div>
            </div>
            @endif

            <div class="text-right">
              <a href="{{ route('transactions.cardTransactions', ['cardId' => $card->id, 'year' => $year, 'month' => $month]) }}"
                 class="btn btn-sm btn-outline-secondary">
                <i class="fa fa-chevron-down mr-1"></i> {{ $hasChildren ? 'Ver todos os lançamentos' : 'Ver lançamentos' }}
              </a>
            </div>
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

@push('scripts')
<script>
  $(function () {
    $('[id^="children-"].collapse').on('shown.bs.collapse hidden.bs.collapse', function () {
      var expanded = $(this).hasClass('show');
      $('[data-target="#' + this.id + '"] i')
        .toggleClass('fa-chevron-down', expanded)
        .toggleClass('fa-chevron-right', !expanded);
    });
  });
</script>
@endpush

@endsection
