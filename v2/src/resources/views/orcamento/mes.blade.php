@extends('layouts.dashboard')

@php
  $mesesNomes = [1=>'Janeiro',2=>'Fevereiro',3=>'Março',4=>'Abril',5=>'Maio',6=>'Junho',7=>'Julho',8=>'Agosto',9=>'Setembro',10=>'Outubro',11=>'Novembro',12=>'Dezembro'];
  $brl = fn($v) => 'R$ '.number_format($v, 2, ',', '.');
  $situacoes = [
    'estourou' => ['Estourou', 'mr-badge-planejado', '#1D4A7C'],
    'aberto'   => [$mesPassado ? 'Ficou abaixo' : 'Em aberto', $mesPassado ? 'mr-badge-tranquilo' : 'mr-badge-atencao', $mesPassado ? '#2D8B86' : '#E8913A'],
    'ok'       => ['Concluído', 'mr-badge-tranquilo', '#2D8B86'],
  ];
  $anterior = \Carbon\Carbon::create($ano, $mes, 1)->subMonth();
  $proximo  = \Carbon\Carbon::create($ano, $mes, 1)->addMonth();
@endphp

@section('content')
@include('orcamento.partials.styles')

<div class="content-header">
  <div class="container">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 text-dark">Acompanhamento do mês</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
          <li class="breadcrumb-item"><a href="{{ route('orcamento.index', $ano) }}">Orçamento {{ $ano }}</a></li>
          <li class="breadcrumb-item active">{{ $mesesNomes[$mes] }}</li>
        </ol>
      </div>
    </div>
  </div>
</div>

<div class="content">
  <div class="container">

    <div class="d-flex align-items-center justify-content-between flex-wrap mb-3" style="gap:12px;">
      <div class="d-flex align-items-center flex-wrap" style="gap:8px;">
        @if ($anterior->year == $ano)
          <a href="{{ route('orcamento.mes', [$ano, $anterior->month]) }}" class="btn btn-sm btn-outline-secondary rounded-circle" title="Mês anterior"><i class="fas fa-chevron-left"></i></a>
        @endif
        <h2 class="m-0" style="font-weight:700;">{{ $mesesNomes[$mes] }} {{ $ano }}</h2>
        @if ($proximo->year == $ano)
          <a href="{{ route('orcamento.mes', [$ano, $proximo->month]) }}" class="btn btn-sm btn-outline-secondary rounded-circle" title="Próximo mês"><i class="fas fa-chevron-right"></i></a>
        @endif
        @unless ($mesPassado)
          <span class="mr-badge mr-badge-planejado">mês em andamento</span>
        @endunless
      </div>
      <a href="{{ route('orcamento.index', [$ano, 'modo' => 'real']) }}">Ver o ano inteiro <i class="fas fa-chevron-right ml-1" style="font-size:.75rem;"></i></a>
    </div>

    @if ($provisoesManuais->isNotEmpty())
      <div class="mb-3" style="border:1px solid #93C5FD;background:#EFF6FF;border-radius:.25rem;padding:12px 16px;color:#1D4A7C;font-size:.9rem;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
        <span><strong>{{ $provisoesManuais->count() }} lançamentos “Prov …” em {{ mb_strtolower($mesesNomes[$mes]) }}</strong> não contam como gasto real aqui. Converta-os em itens para que entrem no planejado.</span>
        <a href="{{ route('orcamento.provisoes', $ano) }}" class="btn btn-sm btn-outline-primary">Converter provisões</a>
      </div>
    @endif

    <div class="row mb-3">
      <div class="col-6 col-lg-3 mb-2">
        <div class="oc-kpi"><div class="oc-kpi-label">Planejado no mês</div><div class="oc-kpi-valor">{{ $brl($planejado) }}</div></div>
      </div>
      <div class="col-6 col-lg-3 mb-2">
        <div class="oc-kpi"><div class="oc-kpi-label">Já saiu (dos itens)</div><div class="oc-kpi-valor">{{ $brl($jaSaiu) }}</div></div>
      </div>
      <div class="col-6 col-lg-3 mb-2">
        @if ($mesPassado)
          <div class="oc-kpi"><div class="oc-kpi-label">Sobrou do planejado</div><div class="oc-kpi-valor" style="color:#065F46;">{{ $brl($emAberto) }}</div><div class="oc-kpi-sub">itens que gastaram menos que o previsto</div></div>
        @else
          <div class="oc-kpi" style="background:#FDF4E7;border:1px solid #F0C77E;box-shadow:none;">
            <div class="oc-kpi-label" style="color:#8A5A1C;">Provisões em aberto</div>
            <div class="oc-kpi-valor" style="color:#8A5A1C;">{{ $brl($emAberto) }}</div>
            <div class="oc-kpi-sub" style="color:#8A5A1C;">ainda vai sair · entra na previsão do Nosso Mês</div>
          </div>
        @endif
      </div>
      <div class="col-6 col-lg-3 mb-2">
        <div class="oc-kpi"><div class="oc-kpi-label">Fora do orçamento</div><div class="oc-kpi-valor" style="color:#1D4A7C;">{{ $brl($fora->sum('total')) }}</div><div class="oc-kpi-sub">gastos sem item correspondente</div></div>
      </div>
    </div>

    <div class="mr-card mb-3" style="border-top:3px solid #2D8B86;">
      <div class="mr-card-header">
        <div>
          <h6 class="mr-card-title"><i class="fas fa-tasks"></i> Item a item</h6>
          <span class="mr-card-sub">planejado × realizado · estourados primeiro</span>
        </div>
      </div>
      @if ($linhas->isEmpty())
        <p class="text-muted small mb-0" style="padding:16px 20px;">Nenhum item com valor planejado ou realizado neste mês.</p>
      @else
      <div style="overflow-x:auto;">
        <table class="table table-sm mb-0" style="font-size:.87rem;min-width:760px;">
          <thead>
            <tr class="text-muted">
              <th style="padding-left:20px;">Item</th>
              <th class="text-right">Planejado</th>
              <th class="text-right">Realizado</th>
              <th style="width:22%;">Andamento</th>
              <th>Situação</th>
              <th style="padding-right:20px;"></th>
            </tr>
          </thead>
          <tbody>
            @foreach ($linhas as $l)
              @php
                [$rotulo, $badge, $cor] = $situacoes[$l['situacao']];
                $pct = $l['plan'] > 0 ? min(100, $l['real'] / $l['plan'] * 100) : 100;
                $item = $l['item'];
                $link = $item->padrao_descricao
                  ? route('transactions.month', [$ano, $mes, 't' => 'despesa', 'estabelecimento' => $item->padrao_descricao])
                  : ($item->id_categoria ? route('transactions.month', [$ano, $mes, 't' => 'despesa', 'categoria' => $item->id_categoria]) : null);
              @endphp
              <tr>
                <td style="padding-left:20px;">
                  <a href="{{ route('orcamento.item.edit', $item->id) }}" style="color:#212529;font-weight:600;">{{ $item->nome }}</a>
                  <span class="text-muted" style="font-size:.76rem;">· {{ \App\Models\Orcamento::GRUPOS[$item->grupo] ?? $item->grupo }}</span>
                </td>
                <td class="text-right">{{ $brl($l['plan']) }}</td>
                <td class="text-right" style="font-weight:600;">{{ $l['real'] > 0 ? $brl($l['real']) : '—' }}</td>
                <td>
                  <div class="oc-meta-barra" style="height:10px;"><span style="left:0;border-radius:5px;width:{{ $pct }}%;background:{{ $cor }};"></span></div>
                </td>
                <td>
                  <span class="mr-badge {{ $badge }}">
                    {{ $rotulo }}
                    @if ($l['situacao'] === 'estourou') +{{ $brl($l['real'] - $l['plan']) }}
                    @elseif ($l['situacao'] === 'aberto') · {{ $mesPassado ? '' : 'falta ' }}{{ $brl($l['plan'] - $l['real']) }}
                    @endif
                  </span>
                </td>
                <td class="text-right" style="padding-right:20px;font-size:.8rem;">
                  @if ($l['qtd'] > 0 && $link)
                    <a href="{{ $link }}">{{ $l['qtd'] }} lançamento{{ $l['qtd'] > 1 ? 's' : '' }}</a>
                  @elseif ($l['qtd'] > 0)
                    {{ $l['qtd'] }} lançamento{{ $l['qtd'] > 1 ? 's' : '' }}
                  @elseif (!$item->id_categoria && !$item->padrao_descricao)
                    <a href="{{ route('orcamento.item.edit', $item->id) }}" class="text-muted">sem categoria/descrição</a>
                  @endif
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      @endif
    </div>

    @if ($fora->isNotEmpty())
    <div class="mr-card mb-3" style="border-top:3px solid #1D4A7C;">
      <div class="mr-card-header">
        <div>
          <h6 class="mr-card-title"><i class="fas fa-question-circle"></i> Fora do orçamento</h6>
          <span class="mr-card-sub">gastos de {{ mb_strtolower($mesesNomes[$mes]) }} que não bateram com nenhum item, por categoria</span>
        </div>
      </div>
      <div style="padding:6px 20px 12px;">
        @foreach ($fora as $f)
          <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap:10px;padding:8px 0;border-top:{{ $loop->first ? 'none' : '1px solid #f1f3f5' }};font-size:.9rem;">
            <span><strong style="font-weight:600;">{{ $f['nome'] }}</strong> <span class="text-muted" style="font-size:.78rem;">· {{ $f['qtd'] }} lançamento{{ $f['qtd'] > 1 ? 's' : '' }}</span></span>
            <span class="d-flex align-items-center" style="gap:14px;">
              <strong>{{ $brl($f['total']) }}</strong>
              <a href="{{ route('transactions.month', [$ano, $mes, 't' => 'despesa', 'categoria' => $f['id_categoria'] ?: \App\Models\Transaction::FILTER_SEM_CATEGORIA]) }}" style="font-size:.78rem;">ver</a>
              @if ($f['id_categoria'])
                <a href="{{ route('orcamento.item.create', [$ano, 'categoria' => $f['id_categoria']]) }}" style="font-size:.78rem;">criar item</a>
              @endif
            </span>
          </div>
        @endforeach
      </div>
    </div>
    @endif

  </div>
</div>
@endsection
