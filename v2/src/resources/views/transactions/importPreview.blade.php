@extends('layouts.dashboard')

@section('content')
<div class="content-header">
  <div class="container">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 text-dark">Revisar Importação</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
          <li class="breadcrumb-item"><a href="{{ url('/transactions') }}">Lançamentos</a></li>
          <li class="breadcrumb-item"><a href="{{ route('transactions.import') }}">Importar CSV</a></li>
          <li class="breadcrumb-item active">Revisar</li>
        </ol>
      </div>
    </div>
  </div>
</div>

  @php
    $duplicadas = $transactions->filter(function($t) { return $t['is_duplicada'] ?? false; })->count();
    $valorDuplicadas = $transactions->filter(function($t) { return $t['is_duplicada_por_valor'] ?? false; })->count();
    $valorAproximadoDuplicadas = $transactions->filter(function($t) { return $t['is_duplicada_por_valor_aproximado'] ?? false; })->count();
  @endphp

<div class="content">
  <div class="container">
    <div class="row">
      <div class="col-12">

        <div class="t-topbar">
            <div>
              <h2>Importar fatura — {{ $id_cartao }}</h2>
              <div class="t-topbar-meta">Fatura: {{ $data_fatura }}· {{ $transactions->count() }} transações</div>
              </div>
              <div class="t-topbar-pills">
              <div class="t-topbar-pill" id="pill-new">{{ $transactions->count() }} lançamentos</div>
              <div class="t-topbar-pill" id="pill-inst"> meses futuros</div>
              <div class="t-topbar-pill" id="pill-pend"> sem categoria</div>
              <div class="t-topbar-pill" id="pill-dup"> {{ $duplicadas }} duplicatas</div>
            </div>
        </div>

        <div class="t-quickbar">
          <span class="t-quickbar-label">Aplicar ao selecionado:</span>
          <button class="t-qbtn" onclick="quickCat(6)">Alimentação</button>
          <button class="t-qbtn" onclick="quickCat(55)">Aportes</button>
          <button class="t-qbtn" onclick="quickCat(28)">Cachorros</button>
          <button class="t-qbtn" onclick="quickCat(13)">Casa</button>
          <button class="t-qbtn" onclick="quickCat(54)">Classificar</button>
          <button class="t-qbtn" onclick="quickCat(24)">Combustível</button>
          <button class="t-qbtn" onclick="quickCat(5)">Contas mensais</button>
          <button class="t-qbtn" onclick="quickCat(9)">Economia</button>
          <button class="t-qbtn" onclick="quickCat(34)">Empréstimos</button>
          <button class="t-qbtn" onclick="quickCat(4)">Estudos</button>
          <button class="t-qbtn" onclick="quickCat(32)">Furo no saldo</button>
          <button class="t-qbtn" onclick="quickCat(8)">Lazer</button>
          <button class="t-qbtn" onclick="quickCat(29)">Mercado</button>
          <button class="t-qbtn" onclick="quickCat(11)">Outros</button>
          <button class="t-qbtn" onclick="quickCat(31)">Presentes</button>
          <button class="t-qbtn" onclick="quickCat(10)">Roupas</button>
          <button class="t-qbtn" onclick="quickCat(1)">Salário</button>
          <button class="t-qbtn" onclick="quickCat(7)">Saúde/Higiene</button>
          <button class="t-qbtn" onclick="quickCat(3)">Veículos</button>
          <button class="t-qbtn" onclick="quickCat(2)">Vendas</button>
          <button class="t-qbtn" onclick="quickCat(35)">Viagens</button>
          <button class="t-qbtn" onclick="quickCat(27)">Vídeo Game</button>
        </div>

        <form action="{{ route('transactions.importStore') }}" method="POST">
          @csrf
          
          <input type="hidden" name="id_cartao" value="{{ $id_cartao }}">
          <input type="hidden" name="data_fatura" value="{{ $data_fatura }}">

          <div class="card card-primary">
            <div class="card-body p-0">

              <div class="t-section" id="sec-fatura-202607-wrap" style="margin-bottom:0;">
                <div class="t-sec-head" style="background:#065F46" onclick="toggleSec('fatura-202607')">
                  <span>📄</span>
                  <span>Julho 2026 — Fatura atual</span>
                  <span class="t-sec-count">100 lançamentos</span>
                  <span style="margin-left:8px;font-size:12px;opacity:.8;">R$ 20.375,84</span>
                  <span class="t-sec-caret up" id="car-fatura-202607">▼</span>
                </div>

                @php
                $sortedTransactions = $transactions->sortBy(function($t) {
                  if ($t['is_duplicada'] ?? false) return 0;
                  if ($t['is_duplicada_por_valor'] ?? false) return 1;
                  if ($t['is_duplicada_por_valor_aproximado'] ?? false) return 2;
                  return 3;
                })->values();

                $groups = [
                  0 => ['label' => 'Chave duplicada (já existem no sistema)', 'class' => 'table-warning', 'icon' => 'fas fa-clone'],
                  1 => ['label' => 'Mesmo valor já lançado neste mês', 'class' => 'tr-valor-similar', 'icon' => 'fas fa-search'],
                  2 => ['label' => 'Valor aproximado já lançado neste mês', 'class' => 'tr-valor-aproximado', 'icon' => 'fas fa-balance-scale'],
                  3 => ['label' => 'Novas transações', 'class' => '', 'icon' => 'fas fa-plus-circle'],
                ];

                $currentGroup = -1;
                @endphp

                @foreach ($sortedTransactions as $index => $transaction)
                  @php
                    $group = ($transaction['is_duplicada'] ?? false) ? 0
                    : (($transaction['is_duplicada_por_valor'] ?? false) ? 1
                    : (($transaction['is_duplicada_por_valor_aproximado'] ?? false) ? 2 : 3));
                  @endphp

                  <div class="t-row" id="row-{{ $loop->index }}" onclick="handleRowClick(event, this)" data-categoria="{{ $transaction['id_categoria'] }}" data-descricao="{{ $transaction['descricao_banco'] }}">
                    <input type="hidden" name="transacoes[{{ $loop->index }}][chave_banco]" value="{{ $transaction['chave_banco'] }}" readonly>
                    <input type="hidden" name="transacoes[{{ $loop->index }}][data_banco]" value="{{ $transaction['data_banco'] }}" readonly>
                    <input type="hidden" name="transacoes[{{ $loop->index }}][id_cartao]" value="{{ $id_cartao }}" readonly>
                    <input type="hidden" name="transacoes[{{ $loop->index }}][descricao_banco]" value="{{ $transaction['descricao_banco'] }}" readonly>
                    <input type="hidden" name="transacoes[{{ $loop->index }}][valor]" value="{{ $transaction['valor'] }}" {{ $transaction['is_duplicada'] ? '' : 'required' }}>


                    <input type="checkbox" class="t-row-cb cb" id="cb-0" name="transacoes[{{ $loop->index }}][importar]" value="1" {{ $transaction['is_duplicada'] || $transaction['is_duplicada_por_valor'] ? '' : 'checked' }}>
                    <span class="t-row-desc ">
                      <input type="text" 
                      class="form-control form-control-sm" 
                      name="transacoes[{{ $loop->index }}][descricao]" 
                      value="{{ $transaction['descricao_banco'] }}">

                      @if($transaction['is_duplicada'])
                        <div class="t-expand-alert" style="background:#FEF2F2;border-color:#FCA5A5;color:#7F1D1D;">
                          Já existe no sistema (chave duplicada)
                        </div>
                      @elseif($transaction['is_duplicada_por_valor'])
                        <div class="t-expand-alert" style="background:#FEF2F2;border-color:#FCA5A5;color:#7F1D1D;">
                          Possível duplicata com <strong>{{ $transaction['duplicada_por_valor_descricao'] }}</strong> · R$ {{ $transaction['duplicada_por_valor_valor'] }}<br>
                        </div>
                      @elseif($transaction['is_duplicada_por_valor_aproximado'])
                        <div class="t-expand-alert" style="background:#FEF2F2;border-color:#FCA5A5;color:#7F1D1D;">
                          Possível duplicata com <strong>{{ $transaction['duplicada_por_valor_aproximado_descricao'] }}</strong> · R$ {{ $transaction['duplicada_por_valor_aproximado_valor'] }}<br>
                        </div>
                      @endif
                    </span>
                    <span class="t-row-val">R$ {{ $transaction['valor'] }}</span>

                    <select class="tipo-select t-row-sel tipo-se" name="transacoes[{{ $loop->index }}][tipo]" data-index="{{ $loop->index }}" data-key="{{ $transaction['_key'] ?? $loop->index }}" data-pessoa-target="pessoa-{{ $loop->index }}" {{ $transaction['is_duplicada'] ? '' : 'required' }}>
                      <option value="despesa" {{ $transaction['tipo_lancamento'] == 'despesa' ? 'selected' : '' }}>Despesa</option>
                      <option value="receita" {{ $transaction['tipo_lancamento'] == 'receita' ? 'selected' : '' }}>Receita</option>
                      <option value="emprestimo" {{ $transaction['tipo_lancamento'] == 'emprestimo' ? 'selected' : '' }}>Empréstimo</option>
                    </select>

                    <select class="t-row-sel cat-sel" id="cat-0" name="transacoes[{{ $loop->index }}][id_categoria]" data-index="{{ $loop->index }}" data-key="{{ $transaction['_key'] ?? $loop->index }}" {{ $transaction['is_duplicada'] ? '' : 'required' }}>
                      <option value="">Selecione</option>
                      @foreach ($categorias as $categoria)
                        <option value="{{ $categoria->id }}" {{ ($transaction['id_categoria'] ?? '') == $categoria->id ? 'selected' : '' }}>{{ $categoria->nome }}</option>
                      @endforeach
                    </select>

                    <select class="t-row-sel tipo-se pessoa-src" name="transacoes[{{ $loop->index }}][id_cliente]" id="pessoa-{{ $loop->index }}" data-key="{{ $transaction['_key'] ?? $loop->index }}" style="display: none;">
                      <option value="">Selecione</option>
                      @foreach ($pessoas as $pessoa)
                        <option value="{{ $pessoa->id }}">{{ $pessoa->nome }}</option>
                      @endforeach
                    </select>
                    <button type="button" class="t-row-dismiss" onclick="removeRow(this); event.stopPropagation();" title="Remover da listagem">&times;</button>
                  </div>
                @endforeach
              </div>
            </div>

            <div class="card-footer">
              <button type="submit" class="btn btn-success">
                <i class="fas fa-save"></i> Salvar Transações
              </button>
              <a href="{{ route('transactions.import') }}" class="btn btn-default">
                <i class="fas fa-arrow-left"></i> Voltar
              </a>
            </div>
          </div>

        </div>
        </div>

        {{-- ============================================================ --}}
        {{-- Seção de Parcelas Futuras                                     --}}
        {{-- ============================================================ --}}
        @if(!empty($installmentGroups))
        @php
          $pf_total_dup      = collect($installmentGroups)->flatMap(fn($g) => $g['installments'])->filter(fn($f) => $f['is_duplicada'] ?? false)->count();
          $pf_total_dupVal   = collect($installmentGroups)->flatMap(fn($g) => $g['installments'])->filter(fn($f) => $f['is_duplicada_por_valor'] ?? false)->count();
          $pf_total_dupAprox = collect($installmentGroups)->flatMap(fn($g) => $g['installments'])->filter(fn($f) => $f['is_duplicada_por_valor_aproximado'] ?? false)->count();
        @endphp

        @if($pf_total_dup > 0)
        <div class="alert alert-warning alert-dismissible mt-3 mb-1">
          <button type="button" class="close" data-dismiss="alert">&times;</button>
          <i class="icon fas fa-clone"></i>
          <strong>{{ $pf_total_dup }}</strong> parcela(s) futura(s) já existem no sistema (chave duplicada) e foram desmarcadas.
        </div>
        @endif
        @if($pf_total_dupVal > 0)
        <div class="alert alert-dismissible mt-1 mb-1" style="background-color:#fde8d0;border-color:#f59f55;color:#7a4a0a;">
          <button type="button" class="close" data-dismiss="alert">&times;</button>
          <i class="icon fas fa-search"></i>
          <strong>{{ $pf_total_dupVal }}</strong> parcela(s) futura(s) com mesmo valor já lançado no mês e foram desmarcadas.
        </div>
        @endif
        @if($pf_total_dupAprox > 0)
        <div class="alert alert-dismissible mt-1 mb-1" style="background-color:#e8f4fd;border-color:#7ab8e8;color:#1a4a7a;">
          <button type="button" class="close" data-dismiss="alert">&times;</button>
          <i class="icon fas fa-balance-scale"></i>
          <strong>{{ $pf_total_dupAprox }}</strong> parcela(s) futura(s) com valor aproximado já lançado. Verifique antes de criar.
        </div>
        @endif

        @php $pfIndex = 0; @endphp
        @foreach($installmentGroups as $mesAno => $group)
        @php
          $mesLabel     = ucfirst(\Carbon\Carbon::parse($group['data'])->locale('pt_BR')->isoFormat('MMMM [de] YYYY'));
          $mesTotal     = collect($group['installments'])->sum('valor');
          $mesInstCount = count($group['installments']);
          $secId        = 'inst-' . $mesAno;
        @endphp
        <div class="t-section mt-2" id="sec-{{ $secId }}-wrap">
          <div class="t-sec-head" style="background:#1E3A5F" onclick="toggleSec('{{ $secId }}')">
            <span>📅</span>
            <span>{{ $mesLabel }} — Parcelas futuras</span>
            <span class="t-sec-count">{{ $mesInstCount }} parcela(s)</span>
            <span style="margin-left:8px;font-size:12px;opacity:.8;">R$ {{ number_format($mesTotal, 2, ',', '.') }}</span>
            <span class="t-sec-caret up" id="car-{{ $secId }}">▼</span>
          </div>
          <div class="t-sec-body open" id="sec-{{ $secId }}">
            @foreach($group['installments'] as $future)
            @php
              $pf_dup       = $future['is_duplicada'] ?? false;
              $pf_dupVal    = $future['is_duplicada_por_valor'] ?? false;
              $pf_dupValApr = $future['is_duplicada_por_valor_aproximado'] ?? false;
              $pf_rowStyle  = $pf_dup       ? 'background:#fff3cd;'
                            : ($pf_dupVal    ? 'background:#fde8d0;'
                            : ($pf_dupValApr ? 'background:#e8f4fd;' : ''));
            @endphp
            <div class="t-row" style="{{ $pf_rowStyle }}" onclick="handleRowClick(event, this)">
              <input type="hidden" name="parcelas_futuras[{{ $pfIndex }}][chave_banco]" value="{{ $future['chave_banco'] }}">
              <input type="hidden" name="parcelas_futuras[{{ $pfIndex }}][data_banco]" value="{{ $future['data_banco'] }}">
              <input type="hidden" name="parcelas_futuras[{{ $pfIndex }}][descricao_banco]" value="{{ $future['descricao_banco'] }}">
              <input type="hidden" name="parcelas_futuras[{{ $pfIndex }}][data]" value="{{ $future['data'] }}">
              <input type="hidden" name="parcelas_futuras[{{ $pfIndex }}][id_cartao]" value="{{ $future['id_cartao'] }}">

              <input type="checkbox"
                     class="t-row-cb pf-checkbox pf-group-{{ $mesAno }}"
                     name="parcelas_futuras[{{ $pfIndex }}][criar]"
                     value="1"
                     {{ ($pf_dup || $pf_dupVal) ? '' : 'checked' }}>

              <span class="t-row-desc">
                <span style="display:flex;align-items:center;gap:6px;">
                  <span class="badge badge-info" style="font-size:10px;flex-shrink:0;white-space:nowrap;">{{ $future['parcel'] }}/{{ $future['total'] }}</span>
                  <input type="text"
                         class="form-control form-control-sm pf-descricao"
                         name="parcelas_futuras[{{ $pfIndex }}][descricao]"
                         value="{{ $future['descricao'] }}">
                </span>
                @if($pf_dup)
                  <div class="t-expand-alert" style="background:#FEF2F2;border-color:#FCA5A5;color:#7F1D1D;">
                    Já existe no sistema (chave duplicada)
                  </div>
                @elseif($pf_dupVal)
                  <div class="t-expand-alert" style="background:#FEF2F2;border-color:#FCA5A5;color:#7F1D1D;">
                    Possível duplicata com <strong>{{ $future['duplicada_por_valor_descricao'] }}</strong>
                  </div>
                @elseif($pf_dupValApr)
                  <div class="t-expand-alert" style="background:#EFF6FF;border-color:#7ab8e8;color:#1a4a7a;">
                    Valor aproximado já lançado: <strong>{{ $future['duplicada_por_valor_aproximado_descricao'] }}</strong>
                  </div>
                @endif
              </span>

              <span class="t-row-val">R$ {{ number_format($future['valor'], 2, ',', '.') }}</span>

              <input type="hidden"
                     class="t-row-sel"
                     style="width:90px;"
                     name="parcelas_futuras[{{ $pfIndex }}][valor]"
                     value="{{ $future['valor'] }}"
                     step="0.01">

              <select class="pf-tipo t-row-sel"
                      name="parcelas_futuras[{{ $pfIndex }}][tipo]"
                      data-source-index="{{ $future['source_index'] }}"
                      data-pessoa-target="pf-pessoa-{{ $pfIndex }}">
                <option value="despesa">Despesa</option>
                <option value="lucro">Receita</option>
                <option value="emprestimo">Empréstimo</option>
              </select>

              <select class="pf-categoria t-row-sel"
                      name="parcelas_futuras[{{ $pfIndex }}][id_categoria]"
                      data-source-index="{{ $future['source_index'] }}">
                <option value="">Categoria</option>
                @foreach($categorias as $cat)
                  <option value="{{ $cat->id }}" {{ ($future['id_categoria'] ?? '') == $cat->id ? 'selected' : '' }}>{{ $cat->nome }}</option>
                @endforeach
              </select>

              <select class="t-row-sel tipo-se pf-cliente-sel" name="parcelas_futuras[{{ $pfIndex }}][id_cliente]" id="pf-pessoa-{{ $pfIndex }}" data-source-index="{{ $future['source_index'] }}" style="display: none;">
                <option value="">Selecione</option>
                @foreach ($pessoas as $pessoa)
                  <option value="{{ $pessoa->id }}">{{ $pessoa->nome }}</option>
                @endforeach
              </select>

              <button type="button" class="t-row-dismiss" onclick="removeRow(this); event.stopPropagation();" title="Remover da listagem">&times;</button>
            </div>
            @php $pfIndex++; @endphp
            @endforeach
          </div>
        </div>
        @endforeach
        @endif

        </form>
      </div>
    </div>
  </div>
</div>

<style>
  input[readonly] {
    background-color: #e9ecef;
  }
  .table-warning {
    background-color: #fff3cd !important;
  }
  .tr-warning span {
    font-size: 12px;
  }
  .table-warning:hover {
    background-color: #ffe8a1 !important;
  }
  .tr-valor-similar {
    background-color: #fde8d0 !important;
  }
  .tr-valor-similar span {
    font-size: 12px;
  }
  .tr-valor-similar:hover {
    background-color: #fbd4b0 !important;
  }
  .tr-valor-aproximado {
    background-color: #e8f4fd !important;
  }
  .tr-valor-aproximado span {
    font-size: 12px;
  }
  .tr-valor-aproximado:hover {
    background-color: #cce6f8 !important;
  }

    .t-expand-alert { border-left: 3px solid; padding: 8px 10px; border-radius: 0 6px 6px 0; font-size: 12px; margin: 5px 0 10px 0; }

    /* TOPBAR INFO */
    .t-topbar { background: linear-gradient(135deg,#1B5E5C,#2D8B86); padding: 12px 20px; border-radius: 10px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; }
    .t-topbar h2 { color: white; font-size: 15px; font-weight: 600; margin: 0; }
    .t-topbar-meta { color: rgba(255,255,255,.65); font-size: 12px; margin-top: 2px; }
    .t-topbar-pills { display: flex; gap: 8px; flex-wrap: wrap; }
    .t-topbar-pill { background: rgba(255,255,255,.15); color: white; font-size: 11px; font-weight: 500; padding: 4px 10px; border-radius: 20px; white-space: nowrap; }

   /* SECTION */
    .t-section { background: white; border-radius: 10px; overflow: hidden; }
    .t-sec-head { padding: 11px 16px; display: flex; align-items: center; gap: 8px; cursor: pointer; user-select: none; color: white; font-size: 13px; font-weight: 600; }
    .t-sec-head:hover { filter: brightness(1.06); }
    .t-sec-count { background: rgba(255,255,255,.2); font-size: 11px; padding: 1px 8px; border-radius: 20px; }
    .t-sec-caret { margin-left: auto; font-size: 16px; transition: transform .2s; }
    .t-sec-caret.up { transform: rotate(180deg); }
    .t-sec-body { display: none; }
    .t-sec-body.open { display: block; }

    /* QUICK CATS */
    .t-quickbar { background: white; border-radius: 10px; padding: 10px 14px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
    .t-quickbar-label { font-size: 11px; font-weight: 600; color: #888; white-space: nowrap; }
    .t-qbtn { font-size: 11px; font-weight: 500; padding: 4px 12px; border-radius: 20px; cursor: pointer; border: 0.5px solid; transition: opacity .15s; background: transparent; }
    .t-qbtn:hover { opacity: .75; }
    .t-qbtn-autofill { font-size: 11px; font-weight: 600; padding: 5px 14px; background: linear-gradient(135deg,#1B5E5C,#2D8B86); color: white; border: none; border-radius: 20px; cursor: pointer; margin-left: auto; white-space: nowrap; }

   /* ROW */
    .t-row-wrap { border-bottom: 0.5px solid #F0F0F0; }
    .t-row-wrap:last-child { border-bottom: none; }
    .t-row { display: flex; align-items: center; gap: 10px; padding: 9px 14px; cursor: pointer; transition: background .1s; user-select: none; }
    .t-row:hover { background: #FAFAFA; }
    .t-row.selected { background: #D1FAE5 !important; outline: 1.5px solid #2D8B86; outline-offset: -1px; }

    .t-row-cb { width: 16px; height: 16px; flex-shrink: 0; cursor: pointer; }
    .t-row-date { font-size: 11px; color: #888; width: 54px; flex-shrink: 0; white-space: nowrap; }
    .t-row-desc { flex: 1; font-size: 13px; font-weight: 500; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; min-width: 0; }
    .t-row-val { font-size: 13px; font-weight: 600; white-space: nowrap; color: #1A1A1A; min-width: 80px; text-align: right; }
    .t-row-badge { font-size: 10px; font-weight: 600; padding: 3px 9px; border-radius: 20px; border: 0.5px solid; white-space: nowrap; flex-shrink: 0; }
    .t-row-sel { font-size: 11px; padding: 4px 7px; border: 0.5px solid #DDD; border-radius: 6px; background: white; cursor: pointer; color: #333; }
    .t-row-sel:focus { outline: none; border-color: #2D8B86; }
    .t-row-dismiss { flex-shrink: 0; background: none; border: 0.5px solid #CCC; border-radius: 4px; color: #AAA; font-size: 15px; line-height: 1; padding: 2px 8px; cursor: pointer; transition: all .15s; }
    .t-row-dismiss:hover { background: #FEF2F2; border-color: #FCA5A5; color: #DC2626; }
</style>

<script>
  // Mostra/esconde o select de pessoa baseado no tipo (transações e parcelas futuras)
  function togglePessoaSelect(tipoSelect) {
    const target = tipoSelect.getAttribute('data-pessoa-target');
    const pessoaSelect = target ? document.getElementById(target) : null;
    if (!pessoaSelect) return;

    if (tipoSelect.value === 'emprestimo') {
      pessoaSelect.style.display = 'block';
    } else {
      pessoaSelect.style.display = 'none';
      pessoaSelect.value = '';
    }
  }

  // Inicializa e adiciona listener para todos os selects de tipo (transações e parcelas futuras)
  document.querySelectorAll('.tipo-select, .pf-tipo').forEach(function(tipoSelect) {
    togglePessoaSelect(tipoSelect);
    tipoSelect.addEventListener('change', function() {
      togglePessoaSelect(this);
    });
  });

  // Checkbox "Selecionar todos"
  // document.getElementById('select-all').addEventListener('change', function() {
  //   const checkboxes = document.querySelectorAll('.import-checkbox');
  //   checkboxes.forEach(function(checkbox) {
  //     checkbox.checked = this.checked;
  //     toggleCategoriaRequired(checkbox);
  //   }.bind(this));
  // });

  // Atualiza o checkbox "select-all" se algum item for desmarcado
  document.querySelectorAll('.import-checkbox').forEach(function(checkbox) {
    checkbox.addEventListener('change', function() {
      const selectAll = document.getElementById('select-all');
      const allChecked = Array.from(document.querySelectorAll('.import-checkbox')).every(cb => cb.checked);
      selectAll.checked = allChecked;
      
      // Gerencia o atributo required da categoria baseado no checkbox
      toggleCategoriaRequired(this);
    });
  });

  // Função para gerenciar o required da categoria
  function toggleCategoriaRequired(checkbox) {
    const row = checkbox.closest('tr');
    const categoriaSelect = row.querySelector('.categoria-select');
    const valorInput = row.querySelector('input[name*="[valor]"]');
    const tipoSelect = row.querySelector('.tipo-select');
    
    if (checkbox.checked) {
      categoriaSelect.setAttribute('required', 'required');
      valorInput.setAttribute('required', 'required');
      tipoSelect.setAttribute('required', 'required');
    } else {
      categoriaSelect.removeAttribute('required');
      valorInput.removeAttribute('required');
      tipoSelect.removeAttribute('required');
    }
  }

  // Inicializa o estado correto ao carregar a página
  document.querySelectorAll('.import-checkbox').forEach(function(checkbox) {
    toggleCategoriaRequired(checkbox);
  });

  // -----------------------------------------------------------------------
  // Seleção de linhas estilo Excel (Ctrl = toggle, Shift = range)
  // -----------------------------------------------------------------------
  var lastSelectedRow = null;

  function handleRowClick(event, row) {
    const tag = event.target.tagName.toLowerCase();
    if (['input', 'select', 'button', 'label', 'option', 'textarea'].includes(tag)) return;

    const allRows = Array.from(document.querySelectorAll('.t-row'));

    if (event.ctrlKey || event.metaKey) {
      // Ctrl/Cmd: toggle individual
      row.classList.toggle('selected');
      lastSelectedRow = row;
    } else if (event.shiftKey && lastSelectedRow) {
      // Shift: range between last and current
      event.preventDefault();
      window.getSelection && window.getSelection().removeAllRanges();
      const from = allRows.indexOf(lastSelectedRow);
      const to   = allRows.indexOf(row);
      const lo   = Math.min(from, to);
      const hi   = Math.max(from, to);
      allRows.forEach((r, i) => { if (i >= lo && i <= hi) r.classList.add('selected'); });
      lastSelectedRow = row;
    } else {
      // Plain click: deselect all, select this
      allRows.forEach(r => r.classList.remove('selected'));
      row.classList.add('selected');
      lastSelectedRow = row;
    }

    updateQuickbar();
  }

  function updateQuickbar() {
    const count = document.querySelectorAll('.t-row.selected').length;
    const label = document.querySelector('.t-quickbar-label');
    if (!label) return;
    label.textContent = count > 0
      ? 'Aplicar a ' + count + ' selecionado(s):'
      : 'Aplicar ao selecionado:';
  }

  function quickCat(catId) {
    const selected = document.querySelectorAll('.t-row.selected');
    if (selected.length === 0) return;
    selected.forEach(function(row) {
      const catSel = row.querySelector('.cat-sel, .pf-categoria');
      if (catSel) catSel.value = catId;
    });
  }

  // Escape limpa seleção
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      document.querySelectorAll('.t-row.selected').forEach(r => r.classList.remove('selected'));
      lastSelectedRow = null;
      updateQuickbar();
    }
  });

  // -----------------------------------------------------------------------
  // Remove linha da listagem
  // -----------------------------------------------------------------------
  function removeRow(btn) {
    btn.closest('.t-row').remove();
  }

  // -----------------------------------------------------------------------
  // Parcelas Futuras: toggle de seção por mês
  // -----------------------------------------------------------------------
  function toggleSec(id) {
    var body  = document.getElementById('sec-' + id);
    var caret = document.getElementById('car-' + id);
    if (!body) return;
    body.classList.toggle('open');
    if (caret) caret.classList.toggle('up');
  }

  @if(!empty($installmentGroups))
  // -----------------------------------------------------------------------
  // Parcelas Futuras: sincroniza categoria e tipo com a transação de origem
  // -----------------------------------------------------------------------
  function syncPfByKey(sourceKey) {
    var catSrc    = document.querySelector('.cat-sel[data-key="' + sourceKey + '"]');
    var tipoSrc   = document.querySelector('.tipo-select[data-key="' + sourceKey + '"]');
    var pessoaSrc = document.querySelector('.pessoa-src[data-key="' + sourceKey + '"]');

    document.querySelectorAll('.pf-categoria[data-source-index="' + sourceKey + '"]')
      .forEach(function(sel) {
        if (catSrc) sel.value = catSrc.value;
      });

    document.querySelectorAll('.pf-tipo[data-source-index="' + sourceKey + '"]')
      .forEach(function(sel) {
        if (!tipoSrc) return;
        // Mapeia valores do select de origem para os valores do select de parcelas futuras
        // (origem usa "receita", parcelas futuras usam "lucro")
        var tipoVal = tipoSrc.value === 'receita' ? 'lucro' : tipoSrc.value;
        sel.value = tipoVal;
        // Dispara change para que togglePessoaSelect atualize o campo de pessoa
        sel.dispatchEvent(new Event('change'));
      });

    document.querySelectorAll('.pf-cliente-sel[data-source-index="' + sourceKey + '"]')
      .forEach(function(sel) {
        if (pessoaSrc) sel.value = pessoaSrc.value;
      });
  }

  // Quando o usuário muda categoria/tipo/pessoa de uma transação de origem, sincroniza as parcelas futuras
  document.querySelectorAll('.cat-sel, .tipo-select, .pessoa-src').forEach(function(sel) {
    sel.addEventListener('change', function() {
      var key = this.getAttribute('data-key');
      if (key !== null) syncPfByKey(key);
    });
  });

  // Sincroniza valores iniciais ao carregar a página
  var pfSourceKeys = new Set();
  document.querySelectorAll('.pf-categoria[data-source-index], .pf-tipo[data-source-index]').forEach(function(el) {
    pfSourceKeys.add(el.getAttribute('data-source-index'));
  });
  pfSourceKeys.forEach(function(key) { syncPfByKey(key); });
  @endif
</script>

@endsection
