@extends('layouts.dashboard')

@php
  $mesesNomes  = [1=>'Janeiro',2=>'Fevereiro',3=>'Março',4=>'Abril',5=>'Maio',6=>'Junho',7=>'Julho',8=>'Agosto',9=>'Setembro',10=>'Outubro',11=>'Novembro',12=>'Dezembro'];
  $mesesCurtos = [1=>'jan',2=>'fev',3=>'mar',4=>'abr',5=>'mai',6=>'jun',7=>'jul',8=>'ago',9=>'set',10=>'out',11=>'nov',12=>'dez'];
  $brl = fn($v) => 'R$ '.number_format($v, 2, ',', '.');
  $num = fn($v) => number_format($v, 0, ',', '.');
@endphp

@section('content')
@include('orcamento.partials.styles')

<div class="content-header">
  <div class="container-fluid" style="max-width:1400px;">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 text-dark">Orçamento</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
          <li class="breadcrumb-item active">Orçamento</li>
        </ol>
      </div>
    </div>
  </div>
</div>

<div class="content">
  <div class="container-fluid" style="max-width:1400px;">

    <div class="d-flex align-items-center justify-content-between flex-wrap mb-3" style="gap:12px;">
      <div class="d-flex align-items-center flex-wrap" style="gap:8px;">
        <a href="{{ route('orcamento.index', $ano - 1) }}" class="btn btn-sm btn-outline-secondary rounded-circle" title="Ano anterior"><i class="fas fa-chevron-left"></i></a>
        <h2 class="m-0" style="font-weight:700;">Orçamento {{ $ano }}</h2>
        <a href="{{ route('orcamento.index', $ano + 1) }}" class="btn btn-sm btn-outline-secondary rounded-circle" title="Próximo ano"><i class="fas fa-chevron-right"></i></a>
        @if ($orcamento && $mesCorrente > 0 && $mesCorrente < 12)
          <span class="mr-badge mr-badge-tranquilo">em uso · {{ $mesesCurtos[$mesCorrente] ?? '' }}</span>
        @endif
      </div>
      @if ($orcamento)
        <div class="d-flex flex-wrap" style="gap:8px;">
          @if ($mesCorrente >= 1)
            <a href="{{ route('orcamento.mes', [$ano, min($mesCorrente, 12)]) }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-tasks"></i> Acompanhar {{ $mesesCurtos[min($mesCorrente, 12)] }}</a>
          @endif
          <a href="{{ route('orcamento.config', $ano) }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-sliders-h"></i> Renda e metas</a>
          <a href="{{ route('orcamento.item.create', $ano) }}" class="btn btn-sm btn-primary"><i class="fas fa-plus"></i> Adicionar item</a>
        </div>
      @endif
    </div>

    @if (!$orcamento)
      <div class="mr-card" style="padding:28px;text-align:center;">
        <h4 style="font-weight:700;">Ainda não existe orçamento para {{ $ano }}</h4>
        <p class="text-muted">O orçamento é a sua planilha de planejamento dentro do sistema: renda prevista, metas por grupo e cada conta mês a mês. Cada célula vira uma provisão que é abatida quando o gasto real aparece.</p>
        <form method="POST" action="{{ route('orcamento.store', $ano) }}" class="d-inline">
          @csrf
          <button type="submit" class="btn btn-primary">Criar orçamento {{ $ano }}</button>
        </form>
        <a href="{{ route('orcamento.provisoes', $ano) }}" class="btn btn-outline-secondary ml-1">Começar pelos lançamentos “Prov …” de {{ $ano }}</a>
      </div>
    @else

    @if ($provisoesManuais->isNotEmpty())
      <div class="mb-3" style="border:1px solid #93C5FD;background:#EFF6FF;border-radius:.25rem;padding:12px 16px;color:#1D4A7C;font-size:.9rem;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
        <span><strong>{{ $provisoesManuais->sum(fn($p) => count($p['ids'])) }} lançamentos “Prov …” em {{ $ano }}</strong> ({{ $provisoesManuais->count() }} provisões diferentes). Eles podem virar itens do orçamento — os lançamentos ficam no histórico como cancelados.</span>
        <a href="{{ route('orcamento.provisoes', $ano) }}" class="btn btn-sm btn-outline-primary">Converter provisões</a>
      </div>
    @endif

    {{-- KPIs --}}
    <div class="row mb-3">
      <div class="col-6 col-lg-3 mb-2">
        <div class="oc-kpi">
          <div class="oc-kpi-label">Renda prevista no ano</div>
          <div class="oc-kpi-valor">{{ $brl($rendaAno) }}</div>
          <div class="oc-kpi-sub">média {{ $brl($rendaAno / 12) }}/mês · <a href="{{ route('orcamento.config', $ano) }}">editar</a></div>
        </div>
      </div>
      <div class="col-6 col-lg-3 mb-2">
        <div class="oc-kpi">
          <div class="oc-kpi-label">Planejado no ano</div>
          <div class="oc-kpi-valor">{{ $brl($planejadoAno) }}</div>
          <div class="oc-kpi-sub">{{ $orcamento->itens->count() }} itens · média {{ $brl($planejadoAno / 12) }}/mês</div>
        </div>
      </div>
      <div class="col-6 col-lg-3 mb-2">
        <div class="oc-kpi">
          <div class="oc-kpi-label">Sobra prevista no ano</div>
          <div class="oc-kpi-valor" style="color:{{ $rendaAno - $planejadoAno >= 0 ? '#065F46' : '#9B1C1C' }};">{{ $brl($rendaAno - $planejadoAno) }}</div>
          <div class="oc-kpi-sub">{{ $rendaAno > 0 ? number_format(($rendaAno - $planejadoAno) / $rendaAno * 100, 1, ',', '.').'% da renda' : 'cadastre a renda prevista' }}</div>
        </div>
      </div>
      <div class="col-6 col-lg-3 mb-2">
        @if ($mesMaisApertado && $sobraMes[$mesMaisApertado] < 0)
          <div class="oc-kpi" style="background:#FEF2F2;border:1px solid #FCA5A5;box-shadow:none;">
            <div class="oc-kpi-label" style="color:#9B1C1C;">Mês mais apertado</div>
            <div class="oc-kpi-valor" style="color:#9B1C1C;">{{ $mesesNomes[$mesMaisApertado] }}</div>
            <div class="oc-kpi-sub" style="color:#9B1C1C;">planejado {{ $brl($planejadoMes[$mesMaisApertado]) }} para {{ $brl($orcamento->rendaMes($mesMaisApertado)) }} de renda</div>
          </div>
        @else
          <div class="oc-kpi">
            <div class="oc-kpi-label">Mês mais apertado</div>
            <div class="oc-kpi-valor">{{ $mesMaisApertado ? $mesesNomes[$mesMaisApertado] : '—' }}</div>
            <div class="oc-kpi-sub">{{ $mesMaisApertado ? 'sobra '.$brl($sobraMes[$mesMaisApertado]) : 'nenhum item ainda' }}</div>
          </div>
        @endif
      </div>
    </div>

    {{-- Metas por grupo --}}
    <div class="mr-card mb-3">
      <div class="mr-card-header">
        <div>
          <h6 class="mr-card-title"><i class="fas fa-bullseye"></i> Metas por grupo</h6>
          <span class="mr-card-sub">quanto da renda mensal cada grupo deveria levar, e quanto o planejado leva</span>
        </div>
        <a href="{{ route('orcamento.config', $ano) }}" style="font-size:.82rem;">ajustar metas</a>
      </div>
      <div class="row" style="padding:16px 20px 6px;">
        @foreach ($metas as $meta)
          @php
            $escala = max(100, $meta['plan_pct'], $meta['meta_pct']) * 1.1;
            $acima  = $meta['plan'] > $meta['meta'] + 0.005;
          @endphp
          <div class="col-md-4 mb-3">
            <div class="d-flex justify-content-between align-items-baseline">
              <strong>{{ $meta['nome'] }}</strong>
              <span class="text-muted" style="font-size:.8rem;">meta {{ rtrim(rtrim(number_format($meta['meta_pct'], 1, ',', ''), '0'), ',') }}% · {{ $brl($meta['meta']) }}/mês</span>
            </div>
            <div class="oc-meta-barra my-1">
              <span style="left:0;border-radius:6px;width:{{ min(100, $meta['plan_pct'] / $escala * 100) }}%;background:{{ $acima ? '#E8913A' : '#2D8B86' }};"></span>
              <span style="width:2px;background:#343a40;left:{{ $meta['meta_pct'] / $escala * 100 }}%;"></span>
            </div>
            <div style="font-size:.8rem;color:{{ $acima ? '#8A5A1C' : '#065F46' }};{{ $acima ? 'font-weight:600;' : '' }}">
              planejado {{ $brl($meta['plan']) }}/mês · {{ round($meta['plan_pct']) }}% da renda ·
              {{ $acima ? $brl($meta['plan'] - $meta['meta']).' acima' : 'dentro da meta' }}
            </div>
          </div>
        @endforeach
      </div>
    </div>

    {{-- Grade --}}
    <div class="mr-card mb-3" style="border-top:3px solid #2D8B86;">
      <div class="mr-card-header">
        <div>
          <h6 class="mr-card-title"><i class="fas fa-th"></i> Itens do orçamento</h6>
          <span class="mr-card-sub">
            @if ($modo === 'plan') valores planejados por mês · cada célula é uma provisão
            @elseif ($modo === 'real') o que de fato saiu em cada item (meses futuros mostram o planejado, em cinza)
            @else realizado − planejado (meses futuros mostram o planejado, em cinza)
            @endif
          </span>
        </div>
        <div class="btn-group btn-group-sm" role="group" aria-label="O que mostrar nas células">
          @foreach (['plan' => 'Planejado', 'real' => 'Realizado', 'dif' => 'Diferença'] as $k => $rotulo)
            <a href="{{ route('orcamento.index', [$ano, 'modo' => $k]) }}" class="btn {{ $modo === $k ? 'btn-secondary' : 'btn-outline-secondary' }}">{{ $rotulo }}</a>
          @endforeach
        </div>
      </div>

      @if ($orcamento->itens->isEmpty())
        <p class="text-muted mb-0" style="padding:16px 20px;">Nenhum item ainda. <a href="{{ route('orcamento.item.create', $ano) }}">Adicione o primeiro</a>@if ($provisoesManuais->isNotEmpty()) ou <a href="{{ route('orcamento.provisoes', $ano) }}">converta os “Prov …”</a>@endif.</p>
      @else
      <div style="overflow-x:auto;">
        <table class="table table-sm oc-grid">
          <thead>
            <tr class="text-muted" style="background:#fbfcfd;">
              <th class="oc-sticky" style="background:#fbfcfd;padding-left:20px;">Item</th>
              <th>Categoria</th>
              @foreach ($mesesCurtos as $m => $curto)
                <th class="text-right">
                  @if ($m <= $mesCorrente)
                    <a href="{{ route('orcamento.mes', [$ano, $m]) }}" class="text-muted" title="Acompanhar {{ mb_strtolower($mesesNomes[$m]) }}">{{ $curto }}</a>
                  @else
                    {{ $curto }}
                  @endif
                </th>
              @endforeach
              <th class="text-right" style="padding-right:20px;color:#343a40;">Total</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($grade as $grupo)
              @continue($grupo['linhas']->isEmpty())
              <tr class="oc-grupo">
                <td class="oc-sticky" style="padding-left:20px;">{{ $grupo['nome'] }} <span class="text-muted" style="font-weight:400;">· {{ $grupo['linhas']->count() }} {{ $grupo['linhas']->count() > 1 ? 'itens' : 'item' }}</span></td>
                <td></td>
                @foreach ($grupo['totais'] as $t)<td class="text-right">{{ $num($t) }}</td>@endforeach
                <td class="text-right" style="padding-right:20px;">{{ $num($grupo['total']) }}</td>
              </tr>
              @foreach ($grupo['linhas'] as $linha)
                @php $item = $linha['item']; @endphp
                <tr>
                  <td class="oc-sticky" style="padding-left:20px;">
                    <a href="{{ route('orcamento.item.edit', $item->id) }}" style="color:#212529;font-weight:600;">{{ $item->nome }}</a>
                    @if ($item->tipo === 'detalhado')<span class="mr-badge mr-badge-sem_dados" style="padding:0 7px;font-size:.68rem;">detalhado</span>@endif
                    @if ($item->padrao_descricao)<span class="text-muted" style="font-size:.7rem;" title="Reconhece lançamentos cuja descrição contém “{{ $item->padrao_descricao }}”"><i class="fas fa-link"></i></span>@endif
                  </td>
                  <td class="text-muted">{{ optional($item->category)->nome ?? '—' }}</td>
                  @foreach ($linha['celulas'] as $m => $c)
                    @php
                      if ($c['real'] === null) {
                        $classe = $modo === 'plan' ? ($c['plan'] ? '' : 'oc-vazio') : 'oc-futuro';
                        $texto  = $c['plan'] ? $num($c['plan']) : '·';
                      } elseif ($m == $mesCorrente && $ano == now()->year && $c['real'] < $c['plan'] - 0.005) {
                        $classe = 'oc-aberto';
                        $texto  = $modo === 'real' ? $num($c['real']) : $num($c['valor']);
                      } else {
                        $classe = $c['real'] > $c['plan'] + 0.005 ? 'oc-acima' : ($c['real'] < $c['plan'] - 0.005 ? 'oc-abaixo' : '');
                        $texto  = ($modo === 'dif' && $c['valor'] > 0 ? '+' : '').(abs($c['valor']) < 0.5 && !$c['plan'] && !$c['real'] ? '·' : $num($c['valor']));
                        if ($texto === '·') { $classe = 'oc-vazio'; }
                      }
                    @endphp
                    <td class="text-right {{ $classe }}">{{ $texto }}</td>
                  @endforeach
                  <td class="text-right" style="padding-right:20px;font-weight:600;">{{ $num($linha['total']) }}</td>
                </tr>
              @endforeach
            @endforeach
          </tbody>
          <tfoot>
            <tr style="border-top:2px solid #dee2e6;font-weight:700;">
              <td class="oc-sticky" style="padding-left:20px;">Total planejado</td><td></td>
              @foreach ($planejadoMes as $v)<td class="text-right">{{ $num($v) }}</td>@endforeach
              <td class="text-right" style="padding-right:20px;">{{ $num($planejadoAno) }}</td>
            </tr>
            <tr style="color:#065F46;">
              <td class="oc-sticky" style="padding-left:20px;">Renda prevista</td><td></td>
              @for ($m = 1; $m <= 12; $m++)<td class="text-right">{{ $num($orcamento->rendaMes($m)) }}</td>@endfor
              <td class="text-right" style="padding-right:20px;">{{ $num($rendaAno) }}</td>
            </tr>
            <tr style="font-weight:700;">
              <td class="oc-sticky" style="padding-left:20px;">Sobra do mês</td><td></td>
              @foreach ($sobraMes as $v)<td class="text-right" style="{{ $v < 0 ? 'color:#9B1C1C;background:#FEF2F2;' : 'color:#065F46;' }}">{{ $num($v) }}</td>@endforeach
              <td class="text-right" style="padding-right:20px;color:{{ $rendaAno - $planejadoAno >= 0 ? '#065F46' : '#9B1C1C' }};">{{ $num($rendaAno - $planejadoAno) }}</td>
            </tr>
          </tfoot>
        </table>
      </div>
      <div class="d-flex flex-wrap text-muted" style="gap:18px;padding:10px 20px;border-top:1px solid #eef0f2;font-size:.76rem;">
        <span>Clique no item para editar · clique no mês (cabeçalho) para acompanhar planejado × realizado</span>
        @if ($modo !== 'plan')
          <span><strong style="color:#1D4A7C;">azul</strong> = acima do planejado · <strong style="color:#065F46;">verde</strong> = abaixo · <strong style="color:#8A5A1C;">laranja</strong> = provisão do mês corrente ainda em aberto</span>
        @endif
      </div>
      @endif
    </div>

    @endif

  </div>
</div>
@endsection
