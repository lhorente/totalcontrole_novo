@extends('layouts.dashboard')

@php
  $mesesNomes = [1=>'Janeiro',2=>'Fevereiro',3=>'Março',4=>'Abril',5=>'Maio',6=>'Junho',
                 7=>'Julho',8=>'Agosto',9=>'Setembro',10=>'Outubro',11=>'Novembro',12=>'Dezembro'];
@endphp

<style>
  .mr-card { background: #fff; border-radius: .25rem; box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.15); }
  .mr-card-header { padding: 14px 20px; border-bottom: 1px solid #eef0f2; display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; }
  .mr-card-title { font-size: 1rem; font-weight: 700; color: #343a40; margin: 0; display: flex; align-items: center; gap: 8px; }
  .mr-card-sub { font-size: .82rem; color: #8a94a3; margin-top: 2px; }
  .mr-bar-row { position: relative; padding: 7px 4px; border-radius: 4px; overflow: hidden; }
  .mr-bar-fill { position: absolute; left: 0; top: 0; bottom: 0; border-radius: 4px; }
  .mr-bar-content { position: relative; display: flex; align-items: center; justify-content: space-between; gap: 10px; font-size: .87rem; }
  .mr-row-btn { width: 24px; height: 24px; min-width: 24px; border-radius: 5px; border: 1px solid #dee2e6; background: #fff; display: flex; align-items: center; justify-content: center; color: #8a94a3; flex: none; }
  .mr-row-btn:hover { border-color: #adb5bd; color: #495057; text-decoration: none; }
  .mr-two-col { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; align-items: start; }
  .mr-badge { display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 999px; font-size: .76rem; font-weight: 600; white-space: nowrap; }
  .mr-badge-tranquilo { background: #ECFDF5; color: #065F46; border: 1px solid #A7F3D0; }
  .mr-badge-atencao   { background: #FDF4E7; color: #8A5A1C; border: 1px solid #F0C77E; }
  .mr-badge-vermelho  { background: #FEF2F2; color: #9B1C1C; border: 1px solid #FCA5A5; }
  .mr-badge-sem_dados { background: #F1F3F5; color: #6c757d; border: 1px solid #dee2e6; }
  .mr-badge-planejado { background: #EFF6FF; color: #1D4A7C; border: 1px solid #93C5FD; }
  .mr-badge-agendado  { background: #FDF4E7; color: #8A5A1C; border: 1px solid #F0C77E; }
  .mr-badge-atrasado  { background: #FEF2F2; color: #9B1C1C; border: 1px solid #FCA5A5; }
  .mr-dot-necessidade { width: 8px; height: 8px; border-radius: 50%; background: #DC2626; display: inline-block; flex: none; }
  .mr-dot-desejo { width: 8px; height: 8px; border-radius: 50%; border: 1.5px solid #ADB5BD; display: inline-block; flex: none; }
  .mr-wish-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; padding: 18px 20px; }
  .mr-months-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; padding: 18px 20px; }
  @media (max-width: 900px) {
    .mr-wish-grid, .mr-months-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
  }
  @media (max-width: 767px) {
    .mr-two-col { grid-template-columns: minmax(0, 1fr); }
  }
  @media (max-width: 600px) {
    .mr-wish-grid, .mr-months-grid { grid-template-columns: minmax(0, 1fr); }
  }
</style>

@section('content')

<div class="content-header">
  <div class="container">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 text-dark">Nosso Mês</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
          <li class="breadcrumb-item active">Nosso Mês</li>
        </ol>
      </div>
    </div>
  </div>
</div>

<div class="content">
  <div class="container">

    <div class="d-flex align-items-end justify-content-between flex-wrap mb-3">
      <div>
        <div class="d-flex align-items-center">
          <a href="{{ route('transactions.monthReview', [$beforeMonthObj->format('Y'), (int) $beforeMonthObj->format('m')]) }}"
             class="btn btn-sm btn-outline-secondary rounded-circle mr-2" title="Mês anterior">
            <i class="fas fa-chevron-left"></i>
          </a>
          <h2 class="m-0" style="font-weight:700;">Nosso {{ $mesesNomes[(int) $month] ?? $month }}</h2>
          <a href="{{ route('transactions.monthReview', [$nextMonthObj->format('Y'), (int) $nextMonthObj->format('m')]) }}"
             class="btn btn-sm btn-outline-secondary rounded-circle ml-2" title="Próximo mês">
            <i class="fas fa-chevron-right"></i>
          </a>
        </div>
        <div class="text-muted" style="margin-left:44px;">Um resumo simples de como estamos e pra que estamos guardando</div>
      </div>
      <a href="{{ route('transactions.month', [$year, $month]) }}" class="d-flex align-items-center">
        Ver lançamentos detalhados <i class="fas fa-chevron-right ml-1" style="font-size:.75rem;"></i>
      </a>
    </div>

    {{-- Banner de saudação --}}
    <div class="mr-card mb-3" style="background:linear-gradient(135deg,#1B5E5C,#2D8B86);color:#fff;padding:26px 30px;">
      <div style="font-size:.88rem;opacity:.85;margin-bottom:6px;">
        @if ($sobraPrevista >= 0)
          Depois de pagar tudo esse mês, sobram
        @else
          Esse mês, além do que entrou, ainda faltam
        @endif
      </div>
      <div style="font-size:2.4rem;font-weight:700;line-height:1;">R$ {{ number_format(abs($sobraPrevista), 2, ',', '.') }}</div>
      <div style="font-size:.88rem;opacity:.9;margin-top:10px;">
        Entrou R$ {{ number_format($totalEntrou, 2, ',', '.') }}
        &nbsp;·&nbsp;
        Já foi R$ {{ number_format($totalDespesaMesAtual, 2, ',', '.') }}
      </div>
    </div>

    {{-- KPIs --}}
    <div class="row mb-3">
      <div class="col-6 col-md-3">
        <div class="info-box mb-2">
          <span class="info-box-icon bg-info"><i class="fas fa-wallet"></i></span>
          <div class="info-box-content">
            <span class="info-box-text">Entrou este mês</span>
            <span class="info-box-number" style="font-size:1em">R$ {{ number_format($totalEntrou, 2, ',', '.') }}</span>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="info-box mb-2">
          <span class="info-box-icon bg-secondary"><i class="fas fa-check"></i></span>
          <div class="info-box-content">
            <span class="info-box-text">Já pago</span>
            <span class="info-box-number" style="font-size:1em">R$ {{ number_format($totalPago, 2, ',', '.') }}</span>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="info-box mb-2">
          <span class="info-box-icon bg-danger"><i class="fas fa-arrow-down"></i></span>
          <div class="info-box-content">
            <span class="info-box-text">Ainda vamos pagar</span>
            <span class="info-box-number" style="font-size:1em">R$ {{ number_format($totalAPagar, 2, ',', '.') }}</span>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="info-box mb-2">
          <span class="info-box-icon bg-success"><i class="fas fa-piggy-bank"></i></span>
          <div class="info-box-content">
            <span class="info-box-text">Sobra prevista</span>
            <span class="info-box-number" style="font-size:1em">R$ {{ number_format($sobraPrevista, 2, ',', '.') }}</span>
          </div>
        </div>
      </div>
    </div>

    <div class="mr-two-col mb-3">

      {{-- Onde foi o dinheiro (por categoria) --}}
      <div class="mr-card" style="border-top:3px solid #007bff;">
        <div class="mr-card-header">
          <div>
            <h6 class="mr-card-title"><i class="fas fa-tags"></i> Onde foi o dinheiro</h6>
            <span class="mr-card-sub">por categoria</span>
          </div>
        </div>
        <div style="padding:12px 14px;display:flex;flex-direction:column;gap:6px;">
          @forelse ($byCategory as $idCategoria => $group)
            @php
              $catNome  = optional($group->first()->category)->nome ?? 'Sem categoria';
              $catIcon  = optional($group->first()->category)->icon_class ?: 'fas fa-tag';
              $catTotal = $group->sum('valor');
              $catPct   = $maxCategoriaValor > 0 ? max(6, round($catTotal / $maxCategoriaValor * 100)) : 0;
            @endphp
            <div class="mr-bar-row">
              <div class="mr-bar-fill" style="width:{{ $catPct }}%;background:#EFF6FF;"></div>
              <div class="mr-bar-content">
                <span class="d-flex align-items-center" style="gap:8px;">
                  <i class="{{ $catIcon }} text-muted"></i> {{ $catNome }}
                </span>
                <span class="d-flex align-items-center" style="gap:8px;">
                  <strong>R$ {{ number_format($catTotal, 2, ',', '.') }}</strong>
                  @if ($idCategoria)
                    <a href="{{ route('transactions.month', [$year, $month, 'categoria' => $idCategoria]) }}"
                       class="mr-row-btn" title="Ver lançamentos de {{ $catNome }}">
                      <i class="fa fa-search fa-xs"></i>
                    </a>
                  @endif
                </span>
              </div>
            </div>
          @empty
            <p class="text-muted mb-0 small">Nenhuma despesa lançada neste mês.</p>
          @endforelse
        </div>
      </div>

      {{-- Nos cartões --}}
      <div class="mr-card" style="border-top:3px solid #343a40;">
        <div class="mr-card-header">
          <div>
            <h6 class="mr-card-title"><i class="fas fa-credit-card"></i> Nos cartões</h6>
            <span class="mr-card-sub">deste mês</span>
          </div>
        </div>
        <div style="padding:12px 14px;display:flex;flex-direction:column;gap:10px;">
          @forelse ($cartoes as $card)
            @php
              $cardTotal = $byCard->get($card->id)?->sum('valor') ?? 0;
              $cardPct   = $maxCardValor > 0 ? max(6, round($cardTotal / $maxCardValor * 100)) : 0;
            @endphp
            @if ($cardTotal > 0)
              <div class="mr-bar-row" style="padding:10px 4px;">
                <div class="mr-bar-fill" style="width:{{ $cardPct }}%;background:#f4f6f9;"></div>
                <div class="mr-bar-content align-items-start">
                  <span>
                    <div style="font-weight:600;">{{ $card->descricao }}</div>
                    <div class="text-muted" style="font-size:.76rem;margin-top:2px;">vence dia {{ $card->dia_vencimento }}</div>
                  </span>
                  <span class="d-flex align-items-center" style="gap:8px;">
                    <strong>R$ {{ number_format($cardTotal, 2, ',', '.') }}</strong>
                    <a href="{{ route('transactions.month', [$year, $month, 'cartao' => $card->id]) }}"
                       class="mr-row-btn" title="Ver lançamentos do {{ $card->descricao }}">
                      <i class="fa fa-search fa-xs"></i>
                    </a>
                  </span>
                </div>
              </div>
            @endif
          @empty
            <p class="text-muted mb-0 small">Nenhum cartão cadastrado.</p>
          @endforelse
          @if ($totalForaCartao > 0)
            <div class="text-muted" style="font-size:.78rem;padding-top:2px;">
              + R$ {{ number_format($totalForaCartao, 2, ',', '.') }} pagos direto da conta, fora do cartão
            </div>
          @endif
        </div>
      </div>

    </div>

    {{-- Resumo de Empréstimos --}}
    @if ($emprestimosCount > 0)
    <div class="mr-card mb-3" style="overflow:hidden;">
      <div style="background:#F2A93C;padding:14px 20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
        <div class="d-flex align-items-center" style="gap:10px;">
          <span style="width:30px;height:30px;border-radius:50%;background:rgba(255,255,255,.4);display:flex;align-items:center;justify-content:center;">
            <i class="fas fa-hand-holding-usd" style="color:#2b2109;"></i>
          </span>
          <h6 class="mr-card-title" style="color:#2b2109;">Resumo de Empréstimos</h6>
          <span style="background:#fff;color:#2b2109;border-radius:999px;padding:1px 9px;font-size:.78rem;font-weight:700;">{{ $emprestimosCount }}</span>
        </div>
        <span style="font-size:.85rem;color:#2b2109;">
          <strong>R$ {{ number_format($pagamentosTotal, 2, ',', '.') }}</strong> recebido /
          <strong>R$ {{ number_format($emprestimosTotal, 2, ',', '.') }}</strong> total
        </span>
      </div>
      <div style="overflow-x:auto;">
        <table class="table table-sm mb-0" style="font-size:.87rem;">
          <thead>
            <tr class="text-muted">
              <th style="padding-left:20px;">Pessoa</th>
              <th class="text-right">Total</th>
              <th class="text-right">Pendente</th>
              <th class="text-right">Recebido</th>
              <th style="width:36px;"></th>
            </tr>
          </thead>
          <tbody>
            @foreach ($emprestimosPorPessoa as $idCliente => $group)
              @php
                $pessoaNome = optional($group->first()->contact)->nome ?? 'Sem pessoa';
                $pessoaTotal = $group->sum('valor');
                $pessoaRecebido = $pagamentosPorPessoa->get($idCliente)?->sum('valor') ?? 0;
                $pessoaPendente = $pessoaTotal - $pessoaRecebido;
              @endphp
              <tr>
                <td style="padding-left:20px;"><i class="fas fa-user text-muted mr-1" style="font-size:.8em;"></i> {{ $pessoaNome }}</td>
                <td class="text-right">R$ {{ number_format($pessoaTotal, 2, ',', '.') }}</td>
                <td class="text-right" style="color:{{ $pessoaPendente > 0 ? '#8A5A1C' : '#adb5bd' }};font-weight:600;">
                  {{ $pessoaPendente > 0 ? 'R$ '.number_format($pessoaPendente, 2, ',', '.') : '—' }}
                </td>
                <td class="text-right" style="color:{{ $pessoaRecebido > 0 ? '#065F46' : '#adb5bd' }};font-weight:600;">
                  {{ $pessoaRecebido > 0 ? 'R$ '.number_format($pessoaRecebido, 2, ',', '.') : '—' }}
                </td>
                <td class="text-right">
                  <a href="{{ route('transactions.month', [$year, $month, 't' => 'emprestimo', 'pessoa' => $idCliente]) }}"
                     class="mr-row-btn ml-auto" title="Ver lançamentos de {{ $pessoaNome }}">
                    <i class="fa fa-search fa-xs"></i>
                  </a>
                </td>
              </tr>
            @endforeach
          </tbody>
          <tfoot>
            <tr style="border-top:2px solid #eef0f2;font-weight:700;">
              <td style="padding-left:20px;">Total</td>
              <td class="text-right">R$ {{ number_format($emprestimosTotal, 2, ',', '.') }}</td>
              <td class="text-right" style="color:#8A5A1C;">R$ {{ number_format($emprestimosTotal - $pagamentosTotal, 2, ',', '.') }}</td>
              <td class="text-right" style="color:#065F46;">R$ {{ number_format($pagamentosTotal, 2, ',', '.') }}</td>
              <td></td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
    @endif

    {{-- Os próximos meses --}}
    <div class="mr-card mb-3">
      <div class="mr-card-header">
        <div>
          <h6 class="mr-card-title"><i class="fas fa-calendar-alt"></i> Os próximos meses</h6>
          <span class="mr-card-sub">previsão com base no que já está lançado</span>
        </div>
      </div>
      <div class="mr-months-grid">
        @php
          $badgeLabels = ['tranquilo' => 'tranquilo', 'atencao' => 'atenção', 'vermelho' => 'no vermelho', 'sem_dados' => 'sem dados ainda'];
        @endphp
        @foreach ($proximosMeses as $pm)
          <div style="border:1px solid {{ $pm['badge'] === 'vermelho' ? '#FCA5A5' : '#eef0f2' }};background:{{ $pm['badge'] === 'vermelho' ? '#FEF2F2' : '#fff' }};border-radius:.25rem;padding:14px 16px;position:relative;">
            <a href="{{ route('transactions.month', [$pm['year'], $pm['month']]) }}" class="mr-row-btn" style="position:absolute;top:10px;right:10px;" title="Ver lançamentos do mês">
              <i class="fa fa-search fa-xs"></i>
            </a>
            <div class="d-flex justify-content-between align-items-center" style="padding-right:28px;">
              <span style="font-weight:700;">{{ $mesesNomes[$pm['month']] ?? $pm['month'] }}</span>
              <span class="mr-badge mr-badge-{{ $pm['badge'] }}">{{ $badgeLabels[$pm['badge']] }}</span>
            </div>
            <div style="font-size:1.3rem;font-weight:700;margin-top:8px;">R$ {{ number_format($pm['total'], 2, ',', '.') }}</div>
          </div>
        @endforeach
      </div>
    </div>

    {{-- Nossos sonhos e planos --}}
    @if ($sonhosDesejo->isNotEmpty() || $necessidadesAtrasadas->isNotEmpty())
    <div class="mr-card mb-3">
      <div class="mr-card-header" style="background:linear-gradient(135deg,#1B5E5C,#2D8B86);border-radius:.25rem .25rem 0 0;border-bottom:none;">
        <div>
          <h6 class="mr-card-title" style="color:#fff;"><i class="fas fa-heart"></i> Nossos sonhos e planos</h6>
          <span style="color:rgba(255,255,255,.85);font-size:.82rem;">o que a gente quer fazer juntos, puxado das Manutenções</span>
        </div>
        <a href="{{ route('planejamento.index') }}" style="color:#fff;font-size:.82rem;text-decoration:underline;">ver tudo</a>
      </div>

      @if ($sonhosDesejo->isNotEmpty())
      @php
        $tipoIcon = ['casa' => 'fa-home', 'carro' => 'fa-car', 'outro' => 'fa-star'];
        $statusLabels = ['planejado' => 'Planejado', 'agendado' => 'Agendado'];
      @endphp
      <div class="mr-wish-grid">
        @foreach ($sonhosDesejo as $item)
          @php
            $bem = optional($item->planejamento)->bem;
            $icon = $tipoIcon[optional($bem)->tipo] ?? 'fa-star';
            $statusLabel = $statusLabels[$item->status] ?? ucfirst($item->status);
            $badgeClass = $item->status === 'agendado' ? 'mr-badge-agendado' : 'mr-badge-planejado';
          @endphp
          <div style="border:1px solid #eef0f2;border-radius:.25rem;padding:16px;display:flex;flex-direction:column;gap:10px;">
            <div class="d-flex align-items-center" style="gap:8px;">
              <span class="mr-dot-desejo"></span>
              <span style="font-size:.76rem;color:#8a94a3;">Desejo</span>
            </div>
            <div class="d-flex align-items-center" style="gap:8px;">
              <i class="fas {{ $icon }}" style="color:#2D8B86;"></i>
              <span style="font-weight:700;">{{ $item->titulo }}</span>
            </div>
            <div style="font-size:1.15rem;font-weight:700;">R$ {{ number_format($item->valor, 2, ',', '.') }}</div>
            <span class="mr-badge {{ $badgeClass }}">
              {{ $statusLabel }}@if ($item->status === 'agendado' && $item->data_vencimento) · {{ $mesesNomes[$item->data_vencimento->month] }}@endif
            </span>
            @if ($bem)
              <div class="text-muted" style="font-size:.8rem;">{{ $bem->nome }}</div>
            @endif
          </div>
        @endforeach
      </div>
      @endif

      @if ($necessidadesAtrasadas->isNotEmpty())
      <div style="padding:0 20px 18px;display:flex;flex-direction:column;gap:8px;">
        @foreach ($necessidadesAtrasadas as $item)
          <div style="border:1px solid #FCA5A5;background:#FEF2F2;border-radius:.25rem;padding:12px 16px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
            <div class="d-flex align-items-center" style="gap:10px;">
              <span class="mr-dot-necessidade"></span>
              <span style="font-size:.76rem;color:#9B1C1C;">Necessidade</span>
              <span style="font-weight:700;">{{ $item->titulo }}</span>
            </div>
            <div class="d-flex align-items-center" style="gap:14px;">
              <strong>R$ {{ number_format($item->valor, 2, ',', '.') }}</strong>
              <span class="mr-badge mr-badge-atrasado">Atrasado</span>
            </div>
          </div>
        @endforeach
      </div>
      @endif
    </div>
    @endif

  </div>
</div>

@endsection
