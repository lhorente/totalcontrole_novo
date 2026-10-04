@extends('layouts.dashboard')

@php
  $mesesNomes = [1=>'Janeiro',2=>'Fevereiro',3=>'Março',4=>'Abril',5=>'Maio',6=>'Junho',
                 7=>'Julho',8=>'Agosto',9=>'Setembro',10=>'Outubro',11=>'Novembro',12=>'Dezembro'];
  $mesesCurtos = [1=>'jan',2=>'fev',3=>'mar',4=>'abr',5=>'mai',6=>'jun',7=>'jul',8=>'ago',9=>'set',10=>'out',11=>'nov',12=>'dez'];
  $brl = fn($v) => 'R$ '.number_format($v, 2, ',', '.');
  $mil = fn($v) => 'R$ '.number_format($v / 1000, 1, ',', '.').' mil';
  $k   = fn($v) => number_format($v / 1000, 1, ',', '.').'k';
  $perfis = [
    'todo'    => ['Todo mês', 'mr-badge-tranquilo'],
    'varia'   => ['Varia', 'mr-badge-planejado'],
    'sazonal' => ['Sazonal', 'mr-badge-atencao'],
    'sem'     => ['Sem categoria', 'mr-badge-sem_dados'],
  ];
  $chartAltura = 160;
@endphp

<style>
  .mr-card { background: #fff; border-radius: .25rem; box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.15); }
  .mr-card-header { padding: 14px 20px; border-bottom: 1px solid #eef0f2; display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; }
  .mr-card-title { font-size: 1rem; font-weight: 700; color: #343a40; margin: 0; display: flex; align-items: center; gap: 8px; }
  .mr-card-sub { font-size: .82rem; color: #8a94a3; margin-top: 2px; }
  .mr-row-btn { width: 24px; height: 24px; min-width: 24px; border-radius: 5px; border: 1px solid #dee2e6; background: #fff; display: flex; align-items: center; justify-content: center; color: #8a94a3; flex: none; }
  .mr-row-btn:hover { border-color: #adb5bd; color: #495057; text-decoration: none; }
  .mr-badge { display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 999px; font-size: .76rem; font-weight: 600; white-space: nowrap; }
  .mr-badge-tranquilo { background: #ECFDF5; color: #065F46; border: 1px solid #A7F3D0; }
  .mr-badge-atencao   { background: #FDF4E7; color: #8A5A1C; border: 1px solid #F0C77E; }
  .mr-badge-planejado { background: #EFF6FF; color: #1D4A7C; border: 1px solid #93C5FD; }
  .mr-badge-sem_dados { background: #F1F3F5; color: #6c757d; border: 1px solid #dee2e6; }
  .mr-wish-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; padding: 18px 20px; }
  .na-chart { position: relative; height: {{ $chartAltura + 30 }}px; display: grid; grid-template-columns: repeat(12, minmax(0, 1fr)); gap: 10px; align-items: end; border-bottom: 1px solid #dee2e6; }
  .na-chart-col { display: flex; flex-direction: column; align-items: center; justify-content: flex-end; height: 100%; gap: 4px; color: #212529; }
  .na-chart-col:hover { text-decoration: none; background: #f8f9fa; }
  .na-bar-cur { display: block; width: 42%; max-width: 26px; border-radius: 3px 3px 0 0; background: #2D8B86; }
  .na-bar-futuro { background: repeating-linear-gradient(45deg, #9fd0cc 0 3px, #e8f3f2 3px 6px); }
  .na-bar-prev { display: block; width: 30%; max-width: 16px; border-radius: 3px 3px 0 0; background: #ced4da; }
  .na-row12 { display: grid; grid-template-columns: repeat(12, minmax(0, 1fr)); gap: 10px; text-align: center; }
  .na-dot { display: block; width: 8px; height: 14px; border-radius: 2px; background: #e9ecef; }
  .na-dot-on { background: #2D8B86; }
  .na-budget-col { background: #F7FAFF; color: #1D4A7C; }
  .na-toggle .btn { font-size: .82rem; }
  @media (max-width: 900px) {
    .mr-wish-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
  }
  @media (max-width: 600px) {
    .mr-wish-grid { grid-template-columns: minmax(0, 1fr); }
  }
</style>

@section('content')

<div class="content-header">
  <div class="container">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 text-dark">Nosso Ano</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
          <li class="breadcrumb-item active">Nosso Ano</li>
        </ol>
      </div>
    </div>
  </div>
</div>

<div class="content">
  <div class="container">

    <div class="d-flex align-items-end justify-content-between flex-wrap mb-3" style="gap:12px;">
      <div>
        <div class="d-flex align-items-center flex-wrap" style="gap:8px;">
          <a href="{{ route('transactions.yearReview', [$year - 1, 'base' => $base]) }}"
             class="btn btn-sm btn-outline-secondary rounded-circle" title="Ano anterior">
            <i class="fas fa-chevron-left"></i>
          </a>
          <h2 class="m-0" style="font-weight:700;">Nosso {{ $year }}</h2>
          <a href="{{ route('transactions.yearReview', [$year + 1, 'base' => $base]) }}"
             class="btn btn-sm btn-outline-secondary rounded-circle" title="Próximo ano">
            <i class="fas fa-chevron-right"></i>
          </a>
          @if ($closedMonths > 0 && $closedMonths < 12)
            <span class="mr-badge mr-badge-planejado">ano em andamento · jan–{{ $mesesCurtos[$closedMonths] }} fechados</span>
          @elseif ($closedMonths === 0)
            <span class="mr-badge mr-badge-sem_dados">ano ainda não começou</span>
          @endif
        </div>
        <div class="text-muted" style="margin-left:44px;">Como foi o nosso ano e o que ele diz sobre o orçamento de {{ $year + 1 }}</div>
      </div>
      <a href="{{ $ultimoMesFechado ? route('transactions.monthReview', [$ultimoMesFechado->year, $ultimoMesFechado->month]) : route('transactions.monthReview') }}" class="d-flex align-items-center">
        Ver o Nosso Mês <i class="fas fa-chevron-right ml-1" style="font-size:.75rem;"></i>
      </a>
    </div>

    @if ($closedMonths > 0)

    {{-- Banner --}}
    <div class="mr-card mb-3" style="background:linear-gradient(135deg,#1B5E5C,#2D8B86);color:#fff;padding:26px 30px;">
      <div style="font-size:.88rem;opacity:.85;margin-bottom:6px;">
        @if ($closedMonths < 12)
          De janeiro a {{ mb_strtolower($mesesNomes[$closedMonths]) }}, gastamos em média por mês
        @else
          Em {{ $year }}, gastamos em média por mês
        @endif
      </div>
      <div style="font-size:2.4rem;font-weight:700;line-height:1;">{{ $brl($mediaMensal) }}</div>
      <div style="font-size:.88rem;opacity:.9;margin-top:10px;">
        Saiu {{ $mil($totalSaiu) }} no total
        &nbsp;·&nbsp;
        Entrou {{ $mil($totalEntrou) }}@if ($mesesSemReceita->isNotEmpty()) (receita lançada em {{ $closedMonths - $mesesSemReceita->count() }} de {{ $closedMonths }} meses)@endif
        &nbsp;·&nbsp;
        Mês mais pesado: {{ mb_strtolower($mesesNomes[$mesMaisPesado]) }}
      </div>
    </div>

    {{-- KPIs --}}
    <div class="row mb-3">
      <div class="col-6 col-md-3">
        <div class="info-box mb-2">
          <span class="info-box-icon bg-danger"><i class="fas fa-arrow-down"></i></span>
          <div class="info-box-content">
            <span class="info-box-text">Saiu no ano</span>
            <span class="info-box-number" style="font-size:1em">{{ $brl($totalSaiu) }}</span>
            <span class="text-muted" style="font-size:.76rem;">{{ $closedMonths < 12 ? 'jan–'.$mesesCurtos[$closedMonths].' fechados' : 'ano completo' }}</span>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="info-box mb-2">
          <span class="info-box-icon bg-info"><i class="fas fa-equals"></i></span>
          <div class="info-box-content">
            <span class="info-box-text">Média por mês</span>
            <span class="info-box-number" style="font-size:1em">{{ $brl($mediaMensal) }}</span>
            <span class="text-muted" style="font-size:.76rem;">mediana {{ $brl($medianaMensal) }}</span>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="info-box mb-2">
          <span class="info-box-icon bg-secondary"><i class="fas fa-chart-bar"></i></span>
          <div class="info-box-content">
            <span class="info-box-text">Mês mais pesado</span>
            <span class="info-box-number" style="font-size:1em">{{ $mesesNomes[$mesMaisPesado] }}</span>
            <span class="text-muted" style="font-size:.76rem;">{{ $brl($meses[$mesMaisPesado]['despesa']) }}</span>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="info-box mb-2">
          <span class="info-box-icon bg-success"><i class="fas fa-arrow-up"></i></span>
          <div class="info-box-content">
            <span class="info-box-text">Entrou no ano</span>
            <span class="info-box-number" style="font-size:1em">{{ $brl($totalEntrou) }}</span>
            <span class="text-muted" style="font-size:.76rem;">receita lançada em {{ $closedMonths - $mesesSemReceita->count() }} de {{ $closedMonths }} meses</span>
          </div>
        </div>
      </div>
    </div>

    {{-- Qualidade dos dados --}}
    @if ($semCategoriaAno->isNotEmpty() || $mesesSemReceita->isNotEmpty())
    <div class="mb-3" style="border:1px solid #F0C77E;background:#FDF4E7;border-radius:.25rem;padding:12px 16px;color:#5c3b10;font-size:.9rem;">
      <i class="fas fa-exclamation-triangle mr-1" style="color:#8A5A1C;"></i>
      <strong>Antes de usar como orçamento:</strong>
      @if ($semCategoriaAno->isNotEmpty())
        <a href="{{ route('transactions.search', ['y' => $year, 't' => 'despesa', 'categoria' => \App\Models\Transaction::FILTER_SEM_CATEGORIA]) }}" style="color:#8A5A1C;text-decoration:underline;">{{ $semCategoriaAno->count() }} lançamento{{ $semCategoriaAno->count() > 1 ? 's' : '' }} sem categoria</a>
        ({{ $brl($semCategoriaAno->sum('valor')) }})@if ($mesesSemReceita->isNotEmpty()) e @endif
      @endif
      @if ($mesesSemReceita->isNotEmpty())
        {{ $mesesSemReceita->count() }} {{ $mesesSemReceita->count() > 1 ? 'meses' : 'mês' }} sem receita lançada
        (@foreach ($mesesSemReceita as $m)<a href="{{ route('transactions.monthReview', [$year, $m]) }}" style="color:#8A5A1C;text-decoration:underline;">{{ $mesesCurtos[$m] }}</a>{{ $loop->last ? '' : ', ' }}@endforeach)
      @endif
    </div>
    @endif

    {{-- Mês a mês --}}
    @php $mediaAltura = $maxMesValor > 0 ? round($mediaMensal / $maxMesValor * $chartAltura) : 0; @endphp
    <div class="mr-card mb-3" style="border-top:3px solid #2D8B86;">
      <div class="mr-card-header">
        <div>
          <h6 class="mr-card-title"><i class="fas fa-chart-bar"></i> Mês a mês</h6>
          <span class="mr-card-sub">despesas de {{ $year }} comparadas com {{ $year - 1 }} · clique num mês para abrir o Nosso Mês</span>
        </div>
        <div class="d-flex flex-wrap" style="gap:14px;font-size:.76rem;color:#495057;">
          <span class="d-flex align-items-center" style="gap:6px;"><span style="width:12px;height:12px;border-radius:2px;background:#2D8B86;"></span>{{ $year }}</span>
          @if ($closedMonths < 12)
            <span class="d-flex align-items-center" style="gap:6px;"><span class="na-bar-futuro" style="width:12px;height:12px;border-radius:2px;"></span>já lançado, ainda por vir</span>
          @endif
          <span class="d-flex align-items-center" style="gap:6px;"><span style="width:12px;height:12px;border-radius:2px;background:#ced4da;"></span>{{ $year - 1 }}</span>
          <span class="d-flex align-items-center" style="gap:6px;"><span style="width:14px;border-top:2px dashed #8A5A1C;"></span>média {{ $year }}</span>
        </div>
      </div>
      <div style="padding:18px 20px 12px;overflow-x:auto;">
        <div style="min-width:640px;">
          <div class="na-chart">
            <div style="position:absolute;left:0;right:0;bottom:{{ $mediaAltura }}px;border-top:2px dashed #8A5A1C;opacity:.7;pointer-events:none;"></div>
            <div style="position:absolute;right:0;bottom:{{ $mediaAltura + 4 }}px;font-size:.7rem;color:#8A5A1C;background:#fff;padding:0 4px;pointer-events:none;">média {{ $mil($mediaMensal) }}</div>
            @foreach ($meses as $m => $dados)
              @php
                $hCur  = $maxMesValor > 0 ? round($dados['despesa'] / $maxMesValor * $chartAltura) : 0;
                $hPrev = $maxMesValor > 0 ? round($dados['anterior'] / $maxMesValor * $chartAltura) : 0;
              @endphp
              <a href="{{ route('transactions.monthReview', [$year, $m]) }}" class="na-chart-col" title="Abrir o Nosso Mês de {{ mb_strtolower($mesesNomes[$m]) }}: {{ $brl($dados['despesa']) }} ({{ $year - 1 }}: {{ $brl($dados['anterior']) }})">
                <span style="font-size:.7rem;font-weight:600;color:#495057;">{{ $dados['despesa'] > 0 ? $k($dados['despesa']) : '' }}</span>
                <span class="d-flex align-items-end justify-content-center w-100" style="gap:3px;">
                  <span class="na-bar-prev" style="height:{{ $hPrev }}px;"></span>
                  <span class="na-bar-cur {{ $dados['fechado'] ? '' : 'na-bar-futuro' }}" style="height:{{ $hCur }}px;"></span>
                </span>
              </a>
            @endforeach
          </div>
          <div class="na-row12" style="padding-top:6px;font-size:.82rem;font-weight:600;color:#343a40;">
            @foreach ($mesesCurtos as $curto)<span>{{ $curto }}</span>@endforeach
          </div>
          <div class="na-row12" style="padding-top:8px;margin-top:8px;border-top:1px dashed #eef0f2;font-size:.7rem;">
            @foreach ($meses as $m => $dados)
              @if ($dados['receita'] > 0)
                <span style="color:#065F46;font-weight:600;">{{ $k($dados['receita']) }}</span>
              @else
                <span style="color:{{ $dados['fechado'] ? '#9B1C1C' : '#adb5bd' }};font-weight:700;">—</span>
              @endif
            @endforeach
          </div>
          <div class="text-muted" style="font-size:.7rem;padding-top:4px;">Linha de baixo: receita lançada no mês (— = nada lançado)</div>
        </div>
      </div>
    </div>

    @else
      <div class="mr-card mb-3" style="padding:20px;">
        <p class="text-muted mb-0">{{ $year }} ainda não tem nenhum mês fechado. Veja o <a href="{{ route('transactions.yearReview', [$year - 1]) }}">Nosso {{ $year - 1 }}</a>.</p>
      </div>
    @endif

    {{-- Base para o orçamento do próximo ano --}}
    <div class="mr-card mb-3" style="border-top:3px solid #007bff;" id="orcamento">
      <div class="mr-card-header">
        <div>
          <h6 class="mr-card-title"><i class="fas fa-clipboard-list"></i> Base para o orçamento de {{ $year + 1 }}</h6>
          <span class="mr-card-sub">
            por categoria ·
            @if ($baseMeses === 0)
              sem meses fechados no período
            @elseif ($base === 'cal')
              ano calendário {{ $year }}{{ $closedMonths < 12 ? ' · jan–'.$mesesCurtos[$closedMonths].' fechados' : '' }}
            @else
              últimos 12 meses fechados ({{ $mesesCurtos[$baseInicio->month] }}/{{ $baseInicio->format('y') }} – {{ $mesesCurtos[$closedMonths] }}/{{ substr($year, -2) }})
            @endif
          </span>
        </div>
        <div class="d-flex align-items-center flex-wrap" style="gap:12px;">
          <div class="btn-group btn-group-sm na-toggle" role="group" aria-label="Período da base">
            <a href="{{ route('transactions.yearReview', [$year, 'base' => '12m']) }}#orcamento" class="btn {{ $base === '12m' ? 'btn-secondary' : 'btn-outline-secondary' }}">Últimos 12 meses</a>
            <a href="{{ route('transactions.yearReview', [$year, 'base' => 'cal']) }}#orcamento" class="btn {{ $base === 'cal' ? 'btn-secondary' : 'btn-outline-secondary' }}">Ano calendário</a>
          </div>
          @if ($orcamento->isNotEmpty())
            <div class="d-flex align-items-center" style="gap:6px;font-size:.82rem;color:#495057;">
              <span id="na-reajuste-label">Reajuste para {{ $year + 1 }}</span>
              <button type="button" class="btn btn-sm btn-outline-secondary" id="na-reajuste-menos" aria-label="Diminuir reajuste">−</button>
              <strong id="na-reajuste-valor" aria-labelledby="na-reajuste-label" style="min-width:40px;text-align:center;">+5%</strong>
              <button type="button" class="btn btn-sm btn-outline-secondary" id="na-reajuste-mais" aria-label="Aumentar reajuste">+</button>
            </div>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="na-exportar" title="Baixar a tabela em CSV">
              <i class="fas fa-download"></i> Exportar
            </button>
          @endif
        </div>
      </div>

      @if ($orcamento->isEmpty())
        <p class="text-muted small mb-0" style="padding:16px 20px;">Nenhuma despesa no período.</p>
      @else
        <div style="overflow-x:auto;">
          <table class="table table-sm mb-0" style="font-size:.87rem;min-width:900px;" id="na-tabela-orcamento">
            <thead>
              <tr class="text-muted">
                <th style="padding-left:20px;">Categoria</th>
                <th>Meses com gasto</th>
                <th>Perfil</th>
                <th class="text-right">{{ $base === 'cal' && $closedMonths < 12 ? 'Total jan–'.$mesesCurtos[$closedMonths] : 'Total '.$baseMeses.' meses' }}</th>
                <th class="text-right">vs ano anterior</th>
                <th class="text-right">Mês mais alto</th>
                <th class="text-right na-budget-col">Reservar por mês em {{ $year + 1 }}</th>
                <th style="width:36px;"></th>
              </tr>
            </thead>
            <tbody>
              @foreach ($orcamento as $cat)
                @php
                  $variacao = $cat['variacao'];
                  if ($variacao === null) {
                    [$varLabel, $varColor] = ['—', '#8a94a3'];
                  } elseif (abs($variacao) < 0.5) {
                    [$varLabel, $varColor] = ['=', '#8a94a3'];
                  } elseif ($variacao > 0) {
                    [$varLabel, $varColor] = ['▲ '.round($variacao).'%', '#9B1C1C'];
                  } else {
                    [$varLabel, $varColor] = ['▼ '.round(abs($variacao)).'%', '#065F46'];
                  }
                @endphp
                <tr class="na-orc-row" data-perfil="{{ $cat['perfil'] }}" data-nome="{{ $cat['nome'] }}" data-total="{{ round($cat['total'], 2) }}" data-base="{{ round($cat['base_mensal'], 2) }}" style="{{ $cat['perfil'] === 'sem' ? 'background:#FFFBF3;' : '' }}">
                  <td style="padding-left:20px;font-weight:600;">{{ $cat['nome'] }}</td>
                  <td>
                    <span class="d-flex align-items-center" style="gap:2px;">
                      @foreach ($cat['mensal'] as $i => $valorMes)
                        <span class="na-dot {{ $valorMes > 0 ? 'na-dot-on' : '' }}" title="{{ $mesesCurtos[(int) substr($baseJanela[$i], 5)] }}/{{ substr($baseJanela[$i], 2, 2) }}: {{ $brl($valorMes) }}"></span>
                      @endforeach
                      <span class="text-muted" style="font-size:.76rem;margin-left:6px;">{{ $cat['meses'] }}/{{ $baseMeses }}</span>
                    </span>
                  </td>
                  <td><span class="mr-badge {{ $perfis[$cat['perfil']][1] }}">{{ $perfis[$cat['perfil']][0] }}</span></td>
                  <td class="text-right">{{ $brl($cat['total']) }}</td>
                  <td class="text-right" style="font-size:.8rem;font-weight:600;color:{{ $varColor }};">{{ $varLabel }}</td>
                  <td class="text-right text-muted" style="font-size:.8rem;">{{ $mesesCurtos[$cat['pico_mes']->month] }}/{{ $cat['pico_mes']->format('y') }} · {{ $brl($cat['pico_valor']) }}</td>
                  <td class="text-right na-budget-col" style="font-weight:700;"><span class="na-sugestao">{{ $brl($cat['base_mensal'] * 1.05) }}</span></td>
                  <td class="text-right">
                    <a href="{{ route('transactions.search', ['y' => $year, 't' => 'despesa', 'categoria' => $cat['id_categoria'] ?: \App\Models\Transaction::FILTER_SEM_CATEGORIA]) }}"
                       class="mr-row-btn ml-auto" title="Ver lançamentos de {{ $cat['nome'] }} em {{ $year }}">
                      <i class="fa fa-search fa-xs"></i>
                    </a>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap:14px;padding:14px 20px;border-top:2px solid #eef0f2;">
          <div class="d-flex flex-wrap" style="gap:22px;font-size:.82rem;color:#495057;">
            @foreach (['todo' => 'Todo mês', 'varia' => 'Varia', 'sazonal' => 'Sazonal (reserva)', 'sem' => 'Sem categoria'] as $perfil => $rotulo)
              @if ($orcamento->where('perfil', $perfil)->isNotEmpty())
                <span>{{ $rotulo }}<strong class="d-block na-total-perfil" data-perfil="{{ $perfil }}" style="font-size:1rem;color:{{ $perfil === 'sem' ? '#8A5A1C' : '#212529' }};">{{ $brl($orcamento->where('perfil', $perfil)->sum('base_mensal') * 1.05) }}</strong></span>
              @endif
            @endforeach
          </div>
          <div class="text-right">
            <div style="font-size:.82rem;color:#1D4A7C;">Orçamento mensal sugerido para {{ $year + 1 }}</div>
            <div style="font-size:1.6rem;font-weight:700;color:#1D4A7C;" id="na-total-geral">{{ $brl($orcamento->sum('base_mensal') * 1.05) }}</div>
            <div class="text-muted" style="font-size:.76rem;">
              @if ($base === 'cal' && $baseMeses < 12)
                total jan–{{ $mesesCurtos[$closedMonths] }} ÷ {{ $baseMeses }} meses fechados + reajuste
              @else
                total de {{ $baseMeses }} meses ÷ {{ $baseMeses }} + reajuste · sazonais viram reserva mensal
              @endif
            </div>
          </div>
        </div>
      @endif
    </div>

    {{-- Sonhos e planos para o próximo ano --}}
    @if ($desejosSemData->isNotEmpty())
    @php $tipoIcon = ['casa' => 'fa-home', 'carro' => 'fa-car', 'outro' => 'fa-star']; @endphp
    <div class="mr-card mb-3">
      <div class="mr-card-header" style="background:linear-gradient(135deg,#1B5E5C,#2D8B86);border-radius:.25rem .25rem 0 0;border-bottom:none;">
        <div>
          <h6 class="mr-card-title" style="color:#fff;"><i class="fas fa-heart"></i> Sonhos e planos para {{ $year + 1 }}</h6>
          <span style="color:rgba(255,255,255,.9);font-size:.82rem;">
            {{ $desejosSemData->count() }} desejo{{ $desejosSemData->count() > 1 ? 's' : '' }} sem data · {{ $brl($desejosSemData->sum('valor')) }} no total — quais entram no ano que vem?
          </span>
        </div>
        <a href="{{ route('planejamento.index') }}" style="color:#fff;font-size:.82rem;text-decoration:underline;">ver tudo</a>
      </div>
      <div class="mr-wish-grid">
        @foreach ($desejosSemData->take(3) as $item)
          @php
            $bem = optional($item->planejamento)->bem;
            $icon = $tipoIcon[optional($bem)->tipo] ?? 'fa-star';
          @endphp
          <div style="border:1px solid #eef0f2;border-radius:.25rem;padding:16px;display:flex;flex-direction:column;gap:8px;">
            <span style="font-size:.76rem;color:#8a94a3;">Desejo · sem data</span>
            <div class="d-flex align-items-center" style="gap:8px;">
              <i class="fas {{ $icon }}" style="color:#2D8B86;"></i>
              <span style="font-weight:700;">{{ $item->titulo }}</span>
            </div>
            <div style="font-size:1.15rem;font-weight:700;">{{ $brl($item->valor) }}</div>
            <span style="font-size:.8rem;color:#1D4A7C;">= {{ $brl($item->valor / 12) }}/mês se entrar em {{ $year + 1 }}</span>
            @if ($bem)
              <div class="text-muted" style="font-size:.8rem;">{{ $bem->nome }}</div>
            @endif
          </div>
        @endforeach
      </div>
    </div>
    @endif

  </div>
</div>

@if ($orcamento->isNotEmpty())
<script>
(function () {
  var reajuste = 5;
  var fmt = function (v) { return 'R$ ' + v.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); };
  var rows = Array.prototype.slice.call(document.querySelectorAll('.na-orc-row'));

  function recalcular() {
    var f = 1 + reajuste / 100;
    var porPerfil = {};
    var geral = 0;
    rows.forEach(function (row) {
      var v = parseFloat(row.dataset.base) * f;
      row.querySelector('.na-sugestao').textContent = fmt(v);
      porPerfil[row.dataset.perfil] = (porPerfil[row.dataset.perfil] || 0) + v;
      geral += v;
    });
    document.querySelectorAll('.na-total-perfil').forEach(function (el) {
      el.textContent = fmt(porPerfil[el.dataset.perfil] || 0);
    });
    document.getElementById('na-total-geral').textContent = fmt(geral);
    document.getElementById('na-reajuste-valor').textContent = (reajuste > 0 ? '+' : '') + reajuste + '%';
  }

  document.getElementById('na-reajuste-menos').addEventListener('click', function () { reajuste = Math.max(0, reajuste - 1); recalcular(); });
  document.getElementById('na-reajuste-mais').addEventListener('click', function () { reajuste = Math.min(30, reajuste + 1); recalcular(); });

  document.getElementById('na-exportar').addEventListener('click', function () {
    var f = 1 + reajuste / 100;
    var num = function (v) { return v.toFixed(2).replace('.', ','); };
    var lines = ['"Categoria";"Perfil";"Total no período";"Reservar por mês (+' + reajuste + '%)"'];
    rows.forEach(function (row) {
      var perfil = row.querySelector('.mr-badge').textContent.trim();
      lines.push(['"' + row.dataset.nome.replace(/"/g, '""') + '"', '"' + perfil + '"', num(parseFloat(row.dataset.total)), num(parseFloat(row.dataset.base) * f)].join(';'));
    });
    var blob = new Blob(['﻿' + lines.join('\r\n')], { type: 'text/csv;charset=utf-8' });
    var a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = 'orcamento-{{ $year + 1 }}-base-{{ $base }}.csv';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(a.href);
  });
})();
</script>
@endif

@endsection
