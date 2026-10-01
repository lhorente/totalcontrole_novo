{{-- Linha do ranking "Onde mais gastamos" (Nosso Mês). Espera $rank, $estab, $maxEstabValor, $year, $month. --}}
@php
  $estabPct = $maxEstabValor > 0 ? max(6, round($estab['total'] / $maxEstabValor * 100)) : 0;
  $variacao = $estab['variacao'];
  if ($variacao === null) {
    [$varLabel, $varColor] = ['novo', '#1D4A7C'];
  } elseif (abs($variacao) < 0.5) {
    [$varLabel, $varColor] = ['=', '#8a94a3'];
  } elseif ($variacao > 0) {
    [$varLabel, $varColor] = ['▲ '.round($variacao).'%', '#9B1C1C'];
  } else {
    [$varLabel, $varColor] = ['▼ '.round(abs($variacao)).'%', '#065F46'];
  }
@endphp
<div class="mr-bar-row">
  <div class="mr-bar-fill" style="width:{{ $estabPct }}%;background:#E8F3F2;"></div>
  <div class="mr-bar-content">
    <span class="mr-estab-rank">{{ $rank }}</span>
    <span class="flex-grow-1" style="min-width:0;">
      <div style="font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $estab['nome'] }}</div>
      <div class="text-muted" style="font-size:.76rem;">
        @if ($estab['categoria']){{ $estab['categoria'] }} · @endif{{ $estab['compras'] }} compra{{ $estab['compras'] > 1 ? 's' : '' }}@if ($estab['compras'] > 1) · média R$ {{ number_format($estab['media'], 2, ',', '.') }}@endif
      </div>
    </span>
    <span class="text-right" style="width:80px;min-width:52px;font-size:.76rem;font-weight:600;color:{{ $varColor }};">{{ $varLabel }}</span>
    <span class="text-right" style="width:120px;min-width:84px;">
      <strong>R$ {{ number_format($estab['total'], 2, ',', '.') }}</strong>
      <div class="text-muted" style="font-size:.76rem;">{{ round($estab['share']) }}% das despesas</div>
    </span>
    <a href="{{ route('transactions.month', [$year, $month, 't' => 'despesa', 'estabelecimento' => $estab['nome']]) }}"
       class="mr-row-btn" title="Ver lançamentos de {{ $estab['nome'] }}">
      <i class="fa fa-search fa-xs"></i>
    </a>
  </div>
</div>
