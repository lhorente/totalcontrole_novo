@extends('layouts.dashboard')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/transaction-modal.css') }}?v={{ filemtime(public_path('css/transaction-modal.css')) }}">
<style>
  /* ── Buscar lançamentos (bl-*) ──────────────────────────────────────────── */
  .bl-card { background: #fff; border-radius: 10px; box-shadow: 0 1px 3px rgba(0,0,0,.12); }
  .bl-top { display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-end; padding: 20px; border-bottom: 1px solid #e9ecef; }
  .bl-field { display: flex; flex-direction: column; gap: 6px; min-width: 0; }
  .bl-field > label, .bl-field > .bl-label { font-size: 13px; font-weight: 600; color: #495057; margin: 0; }
  .bl-q { flex: 999 1 380px; }
  .bl-q-wrap { position: relative; }
  .bl-q-wrap i { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); color: #6c757d; }
  .bl-q-wrap input { padding-left: 38px; }
  .bl-ou { padding-bottom: 11px; color: #6c757d; font-size: 14px; }
  .bl-id { flex: 0 1 150px; }
  .bl-top .form-control { height: 42px; font-size: 15px; }
  .bl-btn-buscar { height: 42px; padding: 0 26px; font-weight: 600; }
  .bl-btn-filtros { display: none; }

  .bl-groups { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); }
  .bl-group { margin: 0; padding: 16px 20px 20px; border: 0; border-right: 1px solid #e9ecef; display: flex; flex-direction: column; gap: 12px; min-width: 0; }
  .bl-group:last-child { border-right: 0; }
  .bl-group legend { float: left; width: auto; margin: 0; padding: 0; font-size: 12px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: #1B5E5C; }
  .bl-group legend + * { clear: left; }
  .bl-group .form-control { height: 38px; font-size: 15px; }
  .bl-range { display: grid; grid-template-columns: minmax(0, 1fr) auto minmax(0, 1fr); gap: 6px; align-items: center; }
  .bl-range > span { color: #6c757d; font-size: 14px; }
  .bl-range input.bl-num { text-align: right; }
  .bl-mais { display: none; flex-direction: column; gap: 12px; }
  .bl-mais-aberto .bl-mais { display: flex; }

  .bl-chip-btn { height: 30px; padding: 0 10px; border-radius: 15px; border: 1px solid #ced4da; background: #fff; color: #212529; font-size: 13px; font-weight: 600; cursor: pointer; }
  .bl-chip-btn.active { background: #1B5E5C; border-color: #1B5E5C; color: #fff; }
  .bl-chips { display: flex; flex-wrap: wrap; gap: 6px; }
  .bl-check { position: relative; margin: 0; }
  .bl-check input { position: absolute; opacity: 0; pointer-events: none; }
  .bl-check span { display: inline-flex; align-items: center; height: 30px; padding: 0 10px; border-radius: 6px; border: 1px solid #ced4da; background: #fff; color: #212529; font-size: 13px; cursor: pointer; user-select: none; }
  .bl-check input:checked + span { background: #1B5E5C; border-color: #1B5E5C; color: #fff; }
  .bl-check input:focus-visible + span { box-shadow: 0 0 0 3px rgba(27,94,92,.25); }
  .bl-seg { display: flex; border: 1px solid #ced4da; border-radius: 6px; overflow: hidden; }
  .bl-seg .bl-check { flex: 1; }
  .bl-seg .bl-check span { width: 100%; justify-content: center; border: 0; border-radius: 0; height: 34px; }
  .bl-seg .bl-check + .bl-check span { border-left: 1px solid #ced4da; }
  .bl-cancelados { display: flex; align-items: center; gap: 8px; font-size: 14px; color: #495057; font-weight: 400; margin: 0; }

  .bl-bar { display: flex; justify-content: space-between; align-items: center; padding: 8px 20px; background: #f8f9fa; border-top: 1px solid #e9ecef; border-radius: 0 0 10px 10px; }
  .bl-bar button, .bl-bar a { border: 0; background: transparent; padding: 6px 0; font-size: 15px; }
  .bl-bar button { color: #1B5E5C; font-weight: 600; cursor: pointer; }
  .bl-bar a { color: #495057; }
  .bl-sheet-head, .bl-sheet-foot { display: none; }

  .bl-ativos { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; margin: 16px 0; }
  .bl-ativo { display: inline-flex; align-items: center; gap: 2px; background: #e3efee; color: #123f3e; border-radius: 14px; padding: 2px 4px 2px 12px; font-size: 14px; }
  .bl-ativo a { display: inline-flex; align-items: center; justify-content: center; width: 26px; height: 26px; color: #123f3e; font-size: 16px; text-decoration: none; }

  .bl-kpis { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; margin-bottom: 16px; }
  .bl-kpi { padding: 14px 16px; }
  .bl-kpi-label { font-size: 13px; color: #6c757d; }
  .bl-kpi-valor { font-size: 24px; font-weight: 700; font-variant-numeric: tabular-nums; }
  .bl-kpi-sub { font-size: 13px; color: #6c757d; }

  .bl-res-head { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px; padding: 10px 16px; border-bottom: 1px solid #e9ecef; }
  .bl-table { margin: 0; font-size: 15px; }
  .bl-table thead th { font-size: 13px; font-weight: 600; color: #495057; background: #f8f9fa; border-top: 0; }
  .bl-table td { vertical-align: middle; }
  .bl-table tbody tr { cursor: pointer; }
  .bl-banco { font-size: 13px; color: #6c757d; }
  .bl-num { font-variant-numeric: tabular-nums; }
  .bl-cancelado { opacity: .55; text-decoration: line-through; }
  .bl-results mark { background: #fde9a8; padding: 0 1px; border-radius: 2px; }
  .bl-lista { display: none; }

  @media (max-width: 991px) {
    .bl-groups { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .bl-group:nth-child(2) { border-right: 0; }
    .bl-group:nth-child(-n+2) { border-bottom: 1px solid #e9ecef; }
    .bl-kpis { grid-template-columns: repeat(2, minmax(0, 1fr)); }
  }

  /* Celular: busca + botão de filtros no topo; os grupos viram um painel de tela cheia */
  @media (max-width: 576px) {
    .bl-top { padding: 12px; gap: 8px; flex-wrap: nowrap; border-bottom: 0; }
    .bl-q { flex: 1 1 auto; }
    .bl-q > label, .bl-ou, .bl-id, .bl-btn-buscar, .bl-bar { display: none; }
    .bl-btn-filtros { display: flex; position: relative; width: 44px; height: 42px; flex: 0 0 44px; align-items: center; justify-content: center; border: 1px solid #1B5E5C; border-radius: 6px; background: #e3efee; color: #1B5E5C; }
    .bl-btn-filtros .badge { position: absolute; top: -7px; right: -7px; background: #1B5E5C; color: #fff; border-radius: 10px; min-width: 20px; }

    .bl-panel { display: none; }
    .bl-open .bl-panel { display: flex; flex-direction: column; position: fixed; inset: 0; z-index: 1040; background: #fff; }
    .bl-open .bl-sheet-head { display: flex; justify-content: space-between; align-items: center; background: #1B5E5C; color: #fff; padding: 10px 16px; }
    .bl-sheet-head h2 { margin: 0; font-size: 20px; font-weight: 600; }
    .bl-sheet-head button { width: 44px; height: 44px; margin-right: -10px; border: 0; background: transparent; color: #fff; font-size: 20px; }
    .bl-open .bl-groups { display: block; flex: 1 1 auto; overflow-y: auto; overscroll-behavior: contain; }
    .bl-group { border-right: 0; border-bottom: 8px solid #f4f6f9 !important; padding: 16px; }
    .bl-group:last-child { border-bottom: 0 !important; }
    .bl-group .form-control { height: 44px; font-size: 16px; }
    .bl-mais { display: flex; }  /* no painel tudo fica visível */
    .bl-open .bl-sheet-foot { display: flex; gap: 10px; padding: 12px 16px; border-top: 1px solid #e9ecef; }
    .bl-sheet-foot a, .bl-sheet-foot button { height: 48px; font-size: 16px; display: flex; align-items: center; justify-content: center; }
    .bl-sheet-foot button { flex: 1 1 auto; font-weight: 600; }

    .bl-ativos { flex-wrap: nowrap; overflow-x: auto; margin: 0 0 12px; padding-bottom: 2px; }
    .bl-ativos > span:first-child { display: none; }
    .bl-ativo { flex: 0 0 auto; }
    .bl-kpis { display: none; }
    .bl-resumo-mobile { display: flex !important; justify-content: space-between; align-items: baseline; margin-bottom: 8px; }
    .bl-res-head, .bl-tabela { display: none; }
    .bl-results.bl-card { background: transparent; box-shadow: none; }
    .bl-lista { display: flex; flex-direction: column; gap: 8px; }
    .bl-item { display: flex; gap: 12px; align-items: center; width: 100%; text-align: left; border: 0; background: #fff; border-radius: 10px; box-shadow: 0 1px 2px rgba(0,0,0,.1); padding: 12px 14px; color: #212529; }
    .bl-item-main { flex: 1 1 auto; min-width: 0; }
    .bl-item-desc { font-size: 16px; font-weight: 600; }
    .bl-item-banco { font-size: 13px; color: #6c757d; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .bl-item-meta { font-size: 13px; color: #6c757d; }
    .bl-item-valor { text-align: right; white-space: nowrap; }
    .bl-item-valor strong { display: block; font-size: 16px; }
  }
</style>
@endpush

@section('content')
@php
  $hoje  = now();
  $atalhos = [
    'Este mês'    => [$hoje->copy()->startOfMonth(), $hoje->copy()->endOfMonth()],
    'Mês passado' => [$hoje->copy()->subMonthNoOverflow()->startOfMonth(), $hoje->copy()->subMonthNoOverflow()->endOfMonth()],
    '3 meses'     => [$hoje->copy()->subMonthsNoOverflow(2)->startOfMonth(), $hoje->copy()->endOfMonth()],
    'Este ano'    => [$hoje->copy()->startOfYear(), $hoje->copy()->endOfYear()],
  ];
  $moeda = fn ($v) => 'R$ ' . number_format($v, 2, ',', '.');

  // Destaca o texto buscado (sem diferenciar maiúsculas); o resto vai escapado
  $destaca = function ($texto) use ($f) {
    $texto = (string) $texto;
    if ($f['q'] === '') return e($texto);
    $partes = preg_split('/(' . preg_quote($f['q'], '/') . ')/iu', $texto, -1, PREG_SPLIT_DELIM_CAPTURE);
    $html = '';
    foreach ($partes as $i => $p) $html .= $i % 2 ? '<mark>' . e($p) . '</mark>' : e($p);
    return $html;
  };

  $qtdFiltros = count(array_filter($chips, fn ($c) => strpos($c['label'], 'Texto: ') !== 0));
  $tipoBadge  = ['despesa' => 'danger', 'lucro' => 'success', 'transferencia' => 'secondary', 'investimento' => 'info', 'emprestimo' => 'warning', 'pagamento_emprestimo' => 'success', 'venda' => 'primary'];
@endphp

<div class="content-header">
  <div class="container">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 text-dark">Buscar lançamentos</h1>
      </div>
      <div class="col-sm-6 d-none d-sm-block">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Início</a></li>
          <li class="breadcrumb-item active">Buscar lançamentos</li>
        </ol>
      </div>
    </div>
  </div>
</div>

<div class="content">
  <div class="container">

    <form method="GET" action="{{ route('transactions.search') }}" id="bl-form"
          class="bl-card {{ $maisAberto ? 'bl-mais-aberto' : '' }}">

      <div class="bl-top">
        <div class="bl-field bl-q">
          <label for="bl-q">Buscar por texto</label>
          <div class="bl-q-wrap">
            <i class="fas fa-search" aria-hidden="true"></i>
            <input type="search" id="bl-q" name="q" class="form-control" value="{{ $f['q'] }}"
                   placeholder="Descrição ou descrição do banco" aria-label="Buscar por texto">
          </div>
        </div>
        <span class="bl-ou">ou</span>
        <div class="bl-field bl-id">
          <label for="bl-id">ID do lançamento</label>
          <input type="text" id="bl-id" name="id" class="form-control" inputmode="numeric" value="{{ $f['id'] }}" placeholder="ex.: 18421">
        </div>
        <button type="submit" class="btn btn-primary bl-btn-buscar">Buscar</button>
        <button type="button" class="bl-btn-filtros" id="bl-abrir-filtros" aria-label="Filtros ({{ $qtdFiltros }} ativos)">
          <i class="fas fa-sliders-h"></i>
          @if ($qtdFiltros)<span class="badge">{{ $qtdFiltros }}</span>@endif
        </button>
      </div>

      <div class="bl-panel">
        <div class="bl-sheet-head">
          <h2>Filtros</h2>
          <button type="button" id="bl-fechar-filtros" aria-label="Fechar"><i class="fas fa-times"></i></button>
        </div>

        <div class="bl-groups">

          {{-- Quando --}}
          <fieldset class="bl-group">
            <legend>Quando</legend>
            <div class="bl-field">
              <span class="bl-label">Período</span>
              <div class="bl-range">
                <input type="date" name="de" id="bl-de" class="form-control" value="{{ $f['de'] }}" aria-label="Início">
                <span>a</span>
                <input type="date" name="ate" id="bl-ate" class="form-control" value="{{ $f['ate'] }}" aria-label="Fim">
              </div>
              <div class="bl-chips">
                @foreach ($atalhos as $label => [$ini, $fim])
                  <button type="button" class="bl-chip-btn bl-atalho {{ $f['de'] === $ini->toDateString() && $f['ate'] === $fim->toDateString() ? 'active' : '' }}"
                          data-de="{{ $ini->toDateString() }}" data-ate="{{ $fim->toDateString() }}">{{ $label }}</button>
                @endforeach
              </div>
            </div>
            <div class="bl-mais">
              <div class="bl-field">
                <label for="bl-base">Considerar a data de</label>
                <select name="base" id="bl-base" class="form-control">
                  <option value="data"      @if($f['base'] === 'data') selected @endif>Lançamento</option>
                  <option value="compra"    @if($f['base'] === 'compra') selected @endif>Compra</option>
                  <option value="pagamento" @if($f['base'] === 'pagamento') selected @endif>Pagamento</option>
                </select>
              </div>
            </div>
          </fieldset>

          {{-- Valor --}}
          <fieldset class="bl-group">
            <legend>Valor</legend>
            <div class="bl-field">
              <span class="bl-label">Faixa (R$)</span>
              <div class="bl-range">
                <input type="text" name="vmin" class="form-control bl-num" inputmode="decimal" placeholder="mín." aria-label="Valor mínimo"
                       value="{{ $params['vmin'] ?? '' }}">
                <span>a</span>
                <input type="text" name="vmax" class="form-control bl-num" inputmode="decimal" placeholder="máx." aria-label="Valor máximo"
                       value="{{ $params['vmax'] ?? '' }}">
              </div>
            </div>
            <div class="bl-field">
              <span class="bl-label">Tipo</span>
              <div class="bl-chips">
                @foreach ($tiposBusca as $valor => $label)
                  <label class="bl-check">
                    <input type="checkbox" name="tipos[]" value="{{ $valor }}" @if(in_array($valor, $f['tipos'], true)) checked @endif>
                    <span>{{ $label }}</span>
                  </label>
                @endforeach
              </div>
            </div>
          </fieldset>

          {{-- Conta --}}
          <fieldset class="bl-group">
            <legend>Conta</legend>
            <div class="bl-field">
              <label for="bl-cartao">Cartão</label>
              <select name="cartao" id="bl-cartao" class="form-control">
                <option value="">Todos</option>
                @foreach ($cartoes as $c)
                  <option value="{{ $c->id }}" @if($f['cartao'] == $c->id) selected @endif>{{ $c->descricao }}</option>
                @endforeach
              </select>
            </div>
            <div class="bl-mais">
              <div class="bl-field">
                <label for="bl-digitos">Final do cartão</label>
                <input type="text" name="digitos" id="bl-digitos" class="form-control" inputmode="numeric" maxlength="4" placeholder="ex.: 4821" value="{{ $f['digitos'] }}">
              </div>
              <div class="bl-field">
                <label for="bl-caixa">Carteira</label>
                <select name="caixa" id="bl-caixa" class="form-control">
                  <option value="">Todas</option>
                  @foreach ($caixas as $cx)
                    <option value="{{ $cx->id }}" @if($f['caixa'] == $cx->id) selected @endif>{{ $cx->titulo }}</option>
                  @endforeach
                </select>
              </div>
              <div class="bl-field">
                <label for="bl-origem">Origem</label>
                <select name="origem" id="bl-origem" class="form-control">
                  <option value="">Todas</option>
                  @foreach ($origensBusca as $valor => $label)
                    <option value="{{ $valor }}" @if($f['origem'] === $valor) selected @endif>{{ $label }}</option>
                  @endforeach
                </select>
              </div>
            </div>
          </fieldset>

          {{-- Classificação --}}
          <fieldset class="bl-group">
            <legend>Classificação</legend>
            <div class="bl-field">
              <label for="bl-categoria">Categoria</label>
              <select name="categoria" id="bl-categoria" class="form-control">
                <option value="">Todas</option>
                <option value="{{ \App\Models\Transaction::FILTER_SEM_CATEGORIA }}" @if($f['categoria'] === \App\Models\Transaction::FILTER_SEM_CATEGORIA) selected @endif>Sem categoria</option>
                @foreach ($categorias as $cat)
                  <option value="{{ $cat->id }}" @if($f['categoria'] == $cat->id && $f['categoria'] !== \App\Models\Transaction::FILTER_SEM_CATEGORIA) selected @endif>{{ $cat->nome }}</option>
                @endforeach
              </select>
            </div>
            <div class="bl-mais">
              <div class="bl-field">
                <label for="bl-pessoa">Pessoa</label>
                <select name="pessoa" id="bl-pessoa" class="form-control">
                  <option value="">Todas</option>
                  @foreach ($pessoas as $p)
                    <option value="{{ $p->id }}" @if($f['pessoa'] == $p->id) selected @endif>{{ $p->nome }}</option>
                  @endforeach
                </select>
              </div>
              <div class="bl-field">
                <span class="bl-label">Pagamento</span>
                <div class="bl-seg">
                  <label class="bl-check"><input type="radio" name="pagamento" value="" @if(!$f['pagamento']) checked @endif><span>Todos</span></label>
                  <label class="bl-check"><input type="radio" name="pagamento" value="pago" @if($f['pagamento'] === 'pago') checked @endif><span>Pago</span></label>
                  <label class="bl-check"><input type="radio" name="pagamento" value="pendente" @if($f['pagamento'] === 'pendente') checked @endif><span>Pendente</span></label>
                </div>
              </div>
              <label class="bl-cancelados">
                <input type="checkbox" name="cancelados" value="1" @if($f['cancelados']) checked @endif> Incluir cancelados
              </label>
            </div>
          </fieldset>
        </div>

        <div class="bl-bar">
          <button type="button" id="bl-toggle-mais">
            <i class="fas {{ $maisAberto ? 'fa-chevron-up' : 'fa-chevron-down' }} fa-xs mr-1"></i>
            <span>{{ $maisAberto ? 'Menos filtros' : 'Mais filtros' }}</span>
          </button>
          <a href="{{ route('transactions.search') }}">Limpar filtros</a>
        </div>

        <div class="bl-sheet-foot">
          <a href="{{ route('transactions.search') }}" class="btn btn-outline-secondary">Limpar</a>
          <button type="submit" class="btn btn-primary">Ver lançamentos</button>
        </div>
      </div>
    </form>

    @if ($chips)
      <div class="bl-ativos">
        <span class="text-muted" style="font-size:14px">Filtros:</span>
        @foreach ($chips as $chip)
          <span class="bl-ativo">{{ $chip['label'] }}<a href="{{ $chip['url'] }}" aria-label="Remover filtro {{ $chip['label'] }}">&times;</a></span>
        @endforeach
      </div>
    @else
      <div class="mb-3"></div>
    @endif

    <div class="bl-kpis">
      <div class="bl-card bl-kpi">
        <div class="bl-kpi-label">Lançamentos</div>
        <div class="bl-kpi-valor">{{ number_format($resumo['qtd'], 0, ',', '.') }}</div>
      </div>
      <div class="bl-card bl-kpi">
        <div class="bl-kpi-label">Total</div>
        <div class="bl-kpi-valor">{{ $moeda($resumo['total']) }}</div>
        @if ($resumo['tipos'] > 1)
          <div class="bl-kpi-sub">Despesas {{ $moeda($resumo['despesas']) }} · Receitas {{ $moeda($resumo['receitas']) }}</div>
        @endif
      </div>
      <div class="bl-card bl-kpi">
        <div class="bl-kpi-label">Média</div>
        <div class="bl-kpi-valor">{{ $moeda($resumo['media']) }}</div>
      </div>
      <div class="bl-card bl-kpi">
        <div class="bl-kpi-label">Maior</div>
        <div class="bl-kpi-valor">{{ $moeda($resumo['maior']) }}</div>
      </div>
    </div>

    <div class="bl-resumo-mobile" style="display:none">
      <span class="text-muted" style="font-size:14px">{{ number_format($resumo['qtd'], 0, ',', '.') }} lançamento(s)</span>
      <strong style="font-size:17px">Total {{ $moeda($resumo['total']) }}</strong>
    </div>

    @if ($resumo['qtd'] > $limiteBusca)
      <div class="alert alert-warning py-2">
        Mostrando os {{ number_format($limiteBusca, 0, ',', '.') }} mais recentes de {{ number_format($resumo['qtd'], 0, ',', '.') }}.
        Refine os filtros para ver o resto — o CSV exportado traz todos. Os totais acima já consideram todos.
      </div>
    @endif

    <div class="bl-card bl-results mb-4">
      <div class="bl-res-head">
        <span class="text-muted" style="font-size:14px">Clique numa linha para editar</span>
        @if ($resumo['qtd'])
          <a href="{{ route('transactions.search', array_merge($params, ['export' => 'csv'])) }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-download mr-1"></i> Exportar CSV
          </a>
        @endif
      </div>

      @if ($transactions->isEmpty())
        <div class="p-4 text-center text-muted">Nenhum lançamento encontrado para os filtros selecionados.</div>
      @else
        @php
          // Atributos que o modal de edição (metOpenModal) lê da linha/cartão
          $dadosModal = function ($t) {
            $attrs = [
              'id'               => $t->id,
              'descricao'        => $t->descricao ?: '',
              'valor'            => $t->valor,
              'data'             => $t->data->format('Y-m-d'),
              'tipo'             => $t->tipo,
              'id-categoria'     => $t->id_categoria ?? '',
              'id-cartao'        => $t->id_cartao ?? '',
              'id-cliente'       => $t->id_cliente ?? '',
              'data-pagamento'   => $t->data_pagamento ? \Carbon\Carbon::parse($t->data_pagamento)->format('Y-m-d') : '',
              'data-recebimento' => $t->data_recebimento ? \Carbon\Carbon::parse($t->data_recebimento)->format('Y-m-d') : '',
              'id-workspace'     => $t->id_workspace,
              'origem'           => $t->origem ?: '',
            ];
            return collect($attrs)->map(fn ($v, $k) => 'data-' . $k . '="' . e($v) . '"')->implode(' ');
          };
        @endphp

        <div class="bl-tabela table-responsive">
          <table class="table table-hover bl-table">
            <thead>
              <tr>
                <th class="pl-3">ID</th>
                <th>Data</th>
                <th>Descrição</th>
                <th>Categoria</th>
                <th>Cartão</th>
                <th class="text-right">Valor</th>
                <th class="pr-3">Pagamento</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($transactions as $t)
                <tr onclick="metOpenModal(this)" {!! $dadosModal($t) !!} class="{{ $t->status === 'cancelado' ? 'bl-cancelado' : '' }}">
                  <td class="pl-3 text-muted bl-num">#{{ $t->id }}</td>
                  <td class="bl-num">
                    {{ $t->data->format('d/m/Y') }}
                    @if ($f['base'] === 'compra' && $t->data_compra && !$t->data_compra->isSameDay($t->data))
                      <div class="bl-banco">compra {{ $t->data_compra->format('d/m/Y') }}</div>
                    @endif
                  </td>
                  <td>
                    <div>{!! $destaca($t->descricao ?: $t->descricao_banco) !!}</div>
                    @if ($t->descricao && $t->descricao_banco && $t->descricao_banco !== $t->descricao)
                      <div class="bl-banco">{!! $destaca($t->descricao_banco) !!}</div>
                    @endif
                  </td>
                  <td>{{ optional($t->category)->nome ?? '—' }}</td>
                  <td>
                    {{ optional($t->credit_card)->descricao ?? '—' }}
                    @if ($t->ultimos_digitos_cartao)<div class="bl-banco">final {{ $t->ultimos_digitos_cartao }}</div>@endif
                  </td>
                  <td class="text-right font-weight-bold bl-num text-nowrap">
                    {{ $moeda($t->valor) }}
                    @if ($resumo['tipos'] > 1)
                      <div><span class="badge badge-{{ $tipoBadge[$t->tipo] ?? 'light' }}">{{ $tiposBusca[$t->tipo] ?? $t->tipo }}</span></div>
                    @endif
                  </td>
                  <td class="pr-3 text-nowrap">
                    @if ($t->data_pagamento)
                      <span class="text-success"><i class="fa fa-check"></i> {{ \Carbon\Carbon::parse($t->data_pagamento)->format('d/m/Y') }}</span>
                    @else
                      <span class="text-danger"><i class="fa fa-clock"></i> Pendente</span>
                    @endif
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>

        <div class="bl-lista">
          @foreach ($transactions as $t)
            <button type="button" class="bl-item {{ $t->status === 'cancelado' ? 'bl-cancelado' : '' }}" onclick="metOpenModal(this)" {!! $dadosModal($t) !!}>
              <div class="bl-item-main">
                <div class="bl-item-desc">{!! $destaca($t->descricao ?: $t->descricao_banco) !!}</div>
                @if ($t->descricao && $t->descricao_banco && $t->descricao_banco !== $t->descricao)
                  <div class="bl-item-banco">{!! $destaca($t->descricao_banco) !!}</div>
                @endif
                <div class="bl-item-meta">
                  #{{ $t->id }} · {{ $t->data->format('d/m/Y') }}@if ($t->credit_card) · {{ $t->credit_card->descricao }}@endif
                </div>
              </div>
              <div class="bl-item-valor">
                <strong class="bl-num">{{ $moeda($t->valor) }}</strong>
                @if ($t->data_pagamento)
                  <small class="text-success">Pago</small>
                @else
                  <small class="text-danger">Pendente</small>
                @endif
              </div>
            </button>
          @endforeach
        </div>
      @endif
    </div>

  </div>
</div>

@include('transactions.partials.modal_edit')
@include('transactions.partials.modal_scripts')

<script>
(function () {
  var form = document.getElementById('bl-form');

  // Atalhos de período: preenchem as datas; no desktop já buscam
  form.querySelectorAll('.bl-atalho').forEach(function (btn) {
    btn.addEventListener('click', function () {
      document.getElementById('bl-de').value  = this.dataset.de;
      document.getElementById('bl-ate').value = this.dataset.ate;
      form.querySelectorAll('.bl-atalho').forEach(function (b) { b.classList.toggle('active', b === btn); });
      if (!form.classList.contains('bl-open')) form.submit();
    });
  });

  // Datas digitadas à mão desmarcam o atalho
  ['bl-de', 'bl-ate'].forEach(function (id) {
    document.getElementById(id).addEventListener('change', function () {
      form.querySelectorAll('.bl-atalho').forEach(function (b) { b.classList.remove('active'); });
    });
  });

  // Mais / menos filtros (desktop)
  document.getElementById('bl-toggle-mais').addEventListener('click', function () {
    var aberto = form.classList.toggle('bl-mais-aberto');
    this.querySelector('i').className = 'fas ' + (aberto ? 'fa-chevron-up' : 'fa-chevron-down') + ' fa-xs mr-1';
    this.querySelector('span').textContent = aberto ? 'Menos filtros' : 'Mais filtros';
  });

  // Painel de filtros em tela cheia (celular)
  document.getElementById('bl-abrir-filtros').addEventListener('click', function () {
    form.classList.add('bl-open');
    document.body.style.overflow = 'hidden';
  });
  document.getElementById('bl-fechar-filtros').addEventListener('click', function () {
    form.classList.remove('bl-open');
    document.body.style.overflow = '';
  });

  // Não manda campos vazios: a URL fica curta e dá para salvar/compartilhar.
  // Período vazio vai como "de=" para não cair no padrão (mês atual).
  form.addEventListener('submit', function () {
    var de = document.getElementById('bl-de'), ate = document.getElementById('bl-ate');
    form.querySelectorAll('input, select').forEach(function (el) {
      if (el === de && !de.value && !ate.value) return;
      if (el.value === '' || ((el.type === 'radio' || el.type === 'checkbox') && !el.checked)) el.disabled = true;
    });
  });
  // Voltando pelo histórico a página pode vir do cache com os campos desabilitados
  window.addEventListener('pageshow', function () {
    form.querySelectorAll(':disabled').forEach(function (el) { el.disabled = false; });
  });
})();
</script>
@endsection
