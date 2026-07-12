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
          <li class="breadcrumb-item"><a href="{{ route('transactions.import') }}">Importar</a></li>
          <li class="breadcrumb-item active">Revisar JSON</li>
        </ol>
      </div>
    </div>
  </div>
</div>

<div class="content">
  <div class="container">

    <style>
    .t-page { display: flex; flex-direction: column; gap: 12px; margin-bottom: 40px; }

    /* TOPBAR INFO */
    .t-topbar { background: linear-gradient(135deg,#1B5E5C,#2D8B86); padding: 12px 20px; border-radius: 10px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; }
    .t-topbar h2 { color: white; font-size: 15px; font-weight: 600; margin: 0; }
    .t-topbar-meta { color: rgba(255,255,255,.65); font-size: 12px; margin-top: 2px; }
    .t-topbar-pills { display: flex; gap: 8px; flex-wrap: wrap; }
    .t-topbar-pill { background: rgba(255,255,255,.15); color: white; font-size: 11px; font-weight: 500; padding: 4px 10px; border-radius: 20px; white-space: nowrap; }

    /* BATCH BAR */
    .t-batch-bar { background: #1D4A7C; border-radius: 10px; padding: 10px 16px; display: none; align-items: center; gap: 10px; flex-wrap: wrap; }
    .t-batch-bar.on { display: flex; }
    .t-batch-bar span { color: white; font-size: 12px; font-weight: 600; white-space: nowrap; }
    .t-batch-bar select { font-size: 12px; padding: 5px 8px; border: none; border-radius: 6px; cursor: pointer; flex: 1; min-width: 120px; max-width: 180px; }
    .t-btn-apply { padding: 6px 16px; background: #2D8B86; color: white; border: none; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; white-space: nowrap; }
    .t-btn-clear { padding: 6px 12px; background: rgba(255,255,255,.15); color: white; border: none; border-radius: 6px; font-size: 12px; cursor: pointer; }

    /* QUICK CATS */
    .t-quickbar { background: white; border-radius: 10px; padding: 10px 14px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
    .t-quickbar-label { font-size: 11px; font-weight: 600; color: #888; white-space: nowrap; }
    .t-qbtn { font-size: 11px; font-weight: 500; padding: 4px 12px; border-radius: 20px; cursor: pointer; border: 0.5px solid; transition: opacity .15s; background: transparent; }
    .t-qbtn:hover { opacity: .75; }
    .t-qbtn-autofill { font-size: 11px; font-weight: 600; padding: 5px 14px; background: linear-gradient(135deg,#1B5E5C,#2D8B86); color: white; border: none; border-radius: 20px; cursor: pointer; margin-left: auto; white-space: nowrap; }

    /* SECTION */
    .t-section { background: white; border-radius: 10px; overflow: hidden; }
    .t-sec-head { padding: 11px 16px; display: flex; align-items: center; gap: 8px; cursor: pointer; user-select: none; color: white; font-size: 13px; font-weight: 600; }
    .t-sec-head:hover { filter: brightness(1.06); }
    .t-sec-count { background: rgba(255,255,255,.2); font-size: 11px; padding: 1px 8px; border-radius: 20px; }
    .t-sec-caret { margin-left: auto; font-size: 16px; transition: transform .2s; }
    .t-sec-caret.up { transform: rotate(180deg); }
    .t-sec-body { display: none; }
    .t-sec-body.open { display: block; }

    /* ROW */
    .t-row-wrap { border-bottom: 0.5px solid #F0F0F0; }
    .t-row-wrap:last-child { border-bottom: none; }
    .t-row { display: flex; align-items: center; gap: 10px; padding: 9px 14px; cursor: pointer; transition: background .1s; }
    .t-row:hover { background: #FAFAFA; }
    .t-row.selected { background: #E8F5F3 !important; }

    .t-row-cb { width: 16px; height: 16px; flex-shrink: 0; cursor: pointer; }
    .t-row-date { font-size: 11px; color: #888; width: 54px; flex-shrink: 0; white-space: nowrap; }
    .t-row-desc { flex: 1; font-size: 13px; font-weight: 500; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; min-width: 0; }
    .t-row-val { font-size: 13px; font-weight: 600; white-space: nowrap; color: #1A1A1A; min-width: 80px; text-align: right; }
    .t-row-badge { font-size: 10px; font-weight: 600; padding: 3px 9px; border-radius: 20px; border: 0.5px solid; white-space: nowrap; flex-shrink: 0; }
    .t-row-sel { font-size: 11px; padding: 4px 7px; border: 0.5px solid #DDD; border-radius: 6px; background: white; cursor: pointer; color: #333; }
    .t-row-sel:focus { outline: none; border-color: #2D8B86; }

    /* badge variants */
    .b-new    { background: #ECFDF5; color: #059669; border-color: #6EE7B7; }
    .b-dup    { background: #FFFBEB; color: #B45309; border-color: #FCD34D; }
    .b-inst   { background: #EDE9FE; color: #4338CA; border-color: #A5B4FC; }
    .b-plan   { background: #EFF6FF; color: #1D4A7C; border-color: #93C5FD; }
    .b-emp    { background: #FEF2F2; color: #9B1C1C; border-color: #FCA5A5; }
    .b-conf-alta  { background: #ECFDF5; color: #059669; border-color: #6EE7B7; }
    .b-conf-media { background: #FFFBEB; color: #B45309; border-color: #FCD34D; }
    .b-conf-baixa { background: #FEF2F2; color: #9B1C1C; border-color: #FCA5A5; }

    .desc-emp  { color: #B45309; }
    .desc-pend { color: #6B7280; }

    /* EXPAND PANEL */
    .t-expand { display: none; padding: 12px 16px 14px 44px; background: #F9FAFB; border-top: 0.5px solid #F0F0F0; }
    .t-expand.open { display: block; }
    .t-expand-alert { border-left: 3px solid; padding: 8px 10px; border-radius: 0 6px 6px 0; font-size: 12px; margin-bottom: 10px; }
    .t-expand-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; }
    .t-expand-grid label { display: block; font-size: 11px; color: #888; font-weight: 500; margin-bottom: 4px; }
    .t-expand-grid select, .t-expand-grid input { width: 100%; font-size: 12px; padding: 6px 8px; border: 0.5px solid #DDD; border-radius: 6px; background: white; box-sizing: border-box; }
    .t-expand-grid select:focus, .t-expand-grid input:focus { outline: none; border-color: #2D8B86; }
    .t-expand-inst { background: #EDE9FE; border-left: 3px solid #A78BFA; color: #3730A3; }
    .t-expand-dup  { background: #FEF3C7; border-left: 3px solid #F59E0B; color: #78350F; }
    .t-expand-emp  { background: #FEF2F2; border-left: 3px solid #FCA5A5; color: #7F1D1D; }
    .t-expand-actions { display: flex; gap: 8px; margin-top: 10px; }
    .t-expand-actions button { flex: 1; padding: 7px; border: none; border-radius: 6px; font-size: 12px; font-weight: 500; cursor: pointer; }
    .t-btn-ok { background: #10B981; color: white; }
    .t-btn-rm { background: #EF4444; color: white; }
    .t-expand-parcelas { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px; }
    .t-expand-parcela-pill { font-size: 11px; background: #EDE9FE; color: #4338CA; border: 0.5px solid #A5B4FC; padding: 3px 8px; border-radius: 10px; }

    /* FOOTER */
    .t-footer { background: white; border-radius: 10px; padding: 16px 20px; display: flex; align-items: center; gap: 20px; }
    .t-footer-totals { flex: 1; }
    .t-total-val { font-size: 24px; font-weight: 700; color: #1B5E5C; }
    .t-total-sub { font-size: 12px; color: #888; margin-top: 2px; }
    .t-footer-actions { display: flex; flex-direction: column; gap: 8px; min-width: 220px; }
    .t-btn-import-all { padding: 11px 20px; background: linear-gradient(135deg,#1B5E5C,#2D8B86); color: white; border: none; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; width: 100%; }
    .t-btn-import-new { padding: 8px 20px; background: transparent; color: #1B5E5C; border: 1.5px solid #2D8B86; border-radius: 8px; font-size: 12px; font-weight: 500; cursor: pointer; width: 100%; }
    .t-btn-import-all:hover { filter: brightness(1.08); }
    .t-btn-import-new:hover { background: #E8F5F3; }

    /* PROGRESS */
    .t-progress-bar { height: 4px; background: #E5E7EB; border-radius: 2px; margin-top: 4px; }
    .t-progress-fill { height: 100%; background: linear-gradient(90deg,#10B981,#2D8B86); border-radius: 2px; transition: width .4s; }
    </style>

    <div class="t-page">

    <!-- TOPBAR INFO -->
    <div class="t-topbar">
        <div>
        <h2>Importar fatura — {{ $cartao ? $cartao->descricao : 'Cartão #'.$id_cartao }}</h2>
        <div class="t-topbar-meta">Fatura: {{ \Carbon\Carbon::parse($data_fatura)->format('m/Y') }} &middot; {{ count($transacoes) }} transações no JSON</div>
        </div>
        <div class="t-topbar-pills">
        <div class="t-topbar-pill" id="pill-new">– novos</div>
        <div class="t-topbar-pill" id="pill-inst">– parcelas futuras</div>
        <div class="t-topbar-pill" id="pill-pend">– a classificar</div>
        <div class="t-topbar-pill" id="pill-dup">– duplicatas</div>
        </div>
    </div>

    <!-- PAINEL DE CONCILIAÇÃO -->
    <div class="t-section" id="recon-panel">

      <!-- 4 colunas de totais -->
      <div style="display:grid;grid-template-columns:repeat(4,1fr);border-bottom:0.5px solid #F0F0F0;">

        <div style="padding:16px 20px;border-right:0.5px solid #F0F0F0;">
          <div style="font-size:11px;font-weight:600;color:#888;text-transform:uppercase;letter-spacing:.4px;margin-bottom:6px;">Total da fatura (banco)</div>
          <div style="font-size:22px;font-weight:700;color:#1A1A1A;" id="recon-banco">{{ 'R$ ' . number_format($total_fatura, 2, ',', '.') }}</div>
          <div style="font-size:11px;color:#888;margin-top:4px;">Fonte: JSON importado</div>
        </div>

        <div style="padding:16px 20px;border-right:0.5px solid #F0F0F0;">
          <div style="font-size:11px;font-weight:600;color:#888;text-transform:uppercase;letter-spacing:.4px;margin-bottom:6px;">Já no sistema</div>
          <div style="font-size:22px;font-weight:700;color:#10B981;" id="recon-sistema">R$ 0,00</div>
          <div style="font-size:11px;color:#888;margin-top:4px;" id="recon-sistema-sub">0 lançamentos</div>
        </div>

        <div style="padding:16px 20px;border-right:0.5px solid #F0F0F0;">
          <div style="font-size:11px;font-weight:600;color:#888;text-transform:uppercase;letter-spacing:.4px;margin-bottom:6px;">Serão importados</div>
          <div style="font-size:22px;font-weight:700;color:#2563EB;" id="recon-novos">R$ 0,00</div>
          <div style="font-size:11px;color:#888;margin-top:4px;" id="recon-novos-sub">0 lançamentos</div>
        </div>

        <div style="padding:16px 20px;">
          <div style="font-size:11px;font-weight:600;color:#888;text-transform:uppercase;letter-spacing:.4px;margin-bottom:6px;">Total pós-importação</div>
          <div style="font-size:22px;font-weight:700;" id="recon-total">R$ 0,00</div>
          <div style="font-size:11px;color:#888;margin-top:4px;" id="recon-diff-label">calculando...</div>
        </div>

      </div>

      <!-- Banner de status -->
      <div id="recon-banner" style="margin:12px 16px;padding:11px 14px;border-radius:8px;font-size:13px;font-weight:500;display:flex;align-items:center;gap:8px;border:1px solid #E5E7EB;background:#F9FAFB;">
        <span id="recon-banner-icon">⏳</span>
        <span id="recon-banner-text">Calculando reconciliação...</span>
      </div>

      <!-- Barra de composição -->
      <div style="padding:0 16px 14px;">
        <div style="display:flex;justify-content:space-between;font-size:11px;color:#888;margin-bottom:5px;">
          <span>Composição da fatura</span>
          <span style="display:flex;gap:10px;">
            <span><span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#10B981;margin-right:3px;"></span>Já no sistema</span>
            <span><span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#3B82F6;margin-right:3px;"></span>Novos</span>
            <span><span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#F59E0B;margin-right:3px;"></span>Duplicatas</span>
            <span><span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#8B5CF6;margin-right:3px;"></span>Empréstimos</span>
          </span>
        </div>
        <div style="height:6px;background:#E5E7EB;border-radius:3px;overflow:hidden;display:flex;">
          <div id="seg-ja"   style="height:100%;background:#10B981;width:0%;transition:width .4s;"></div>
          <div id="seg-novo" style="height:100%;background:#3B82F6;width:0%;transition:width .4s;"></div>
          <div id="seg-dup"  style="height:100%;background:#F59E0B;width:0%;transition:width .4s;"></div>
          <div id="seg-emp"  style="height:100%;background:#8B5CF6;width:0%;transition:width .4s;"></div>
        </div>
      </div>

    </div>
    <!-- /PAINEL DE CONCILIAÇÃO -->

    <!-- BATCH BAR -->
    <div class="t-batch-bar" id="batchBar">
        <span><span id="batchCount">0</span> selecionados</span>
        <select id="bCat">
        <option value="">Categoria...</option>
        @foreach($categorias as $cat)
            <option value="{{ $cat->id }}">{{ $cat->nome }}</option>
        @endforeach
        </select>
        <select id="bTipo">
        <option value="">Tipo...</option>
        <option>Pessoal</option><option>Emprestado</option><option>Brunity</option>
        </select>
        <button class="t-btn-apply" onclick="applyBatch()">✓ Aplicar</button>
        <button class="t-btn-clear" onclick="clearSel()">Limpar</button>
    </div>

    <!-- QUICK CATS -->
    <div class="t-quickbar">
        <span class="t-quickbar-label">Aplicar ao selecionado:</span>
        @foreach($categorias as $cat)
        <button class="t-qbtn" onclick="quickCat({{ $cat->id }})">{{ $cat->nome }}</button>
        @endforeach
        <button class="t-qbtn-autofill" onclick="autoFill()">✦ Auto-preencher</button>
    </div>

    <!-- FILTROS -->
    <div class="t-quickbar" id="filterBar">
        <span class="t-quickbar-label">Filtrar:</span>
        <select id="filter-confianca" onchange="applyFilter()" style="font-size:11px;padding:4px 8px;border:0.5px solid #DDD;border-radius:6px;background:white;cursor:pointer;color:#333;">
            <option value="">Confiança: todas</option>
            <option value="alta">Confiança alta</option>
            <option value="média">Confiança média</option>
            <option value="baixa">Confiança baixa</option>
        </select>
        <label style="display:flex;align-items:center;gap:5px;font-size:11px;cursor:pointer;color:#333;font-weight:500;">
            <input type="checkbox" id="filter-dup" onchange="applyFilter()"> Apenas duplicadas
        </label>
        <button onclick="clearFilter()" style="font-size:11px;padding:4px 10px;border:0.5px solid #DDD;border-radius:6px;background:white;cursor:pointer;color:#888;">✕ Limpar filtros</button>
    </div>

    <!-- SEÇÕES POR FATURA -->
    <div id="faturas-container">
      <!-- preenchido dinamicamente por renderAll() -->
    </div>

    <!-- FOOTER -->
    <div class="t-footer">
        <div class="t-footer-totals">
        <div style="font-size:12px;color:#888;margin-bottom:2px;">Total desta fatura</div>
        <div class="t-total-val" id="total-val">R$ 0,00</div>
        <div class="t-total-sub" id="total-sub"></div>
        <div class="t-progress-bar" style="margin-top:10px;width:200px">
            <div class="t-progress-fill" id="progress" style="width:0%"></div>
        </div>
        <div style="font-size:11px;color:#888;margin-top:4px;" id="progress-label">0% categorizados</div>
        </div>
        <div class="t-footer-actions">
        <button class="t-btn-import-all" onclick="importAll()">⬆ Importar tudo</button>
        <button class="t-btn-import-new" onclick="importNew()">Importar apenas novos</button>
        </div>
    </div>

    </div><!-- /t-page -->

  </div>
</div>

<script>
// ───────────────────── DADOS ─────────────────────
const DATA = { transacoes: @json($transacoes) };
const ID_CARTAO   = '{{ $id_cartao }}';
const DATA_FATURA = '{{ $data_fatura }}';
const CATS     = @json($categorias->map(fn($c) => ['id' => $c->id, 'nome' => $c->nome])->values());
const CONTATOS = @json($contatos->map(fn($c) => ['id' => $c->id, 'nome' => $c->nome])->values());
const TIPOS    = ['Pessoal','Emprestado','Brunity'];

// ───────────────────── UTILS ─────────────────────
const esc = s => String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
const fmtVal = v => 'R$ ' + parseFloat(v).toLocaleString('pt-BR',{minimumFractionDigits:2});
const fmtDate = d => { const [y,m,day]=d.split('-'); return `${day}/${m}/${y.slice(2)}`; };
const fmtDateFull = d => {
  const [y,m,day]=d.split('-');
  const ms=['jan','fev','mar','abr','mai','jun','jul','ago','set','out','nov','dez'];
  return `${parseInt(day)}/${ms[parseInt(m)-1]}`;
};

function getContatoNome(id) {
  const c = CONTATOS.find(c => c.id == id);
  return c ? c.nome : '?';
}

function catSelect(currentId, i) {
  let o = `<select class="t-row-sel cat-sel" id="cat-${i}" onclick="event.stopPropagation()" onchange="onCatChange(${i})">`;
  o += `<option value="" ${!currentId?'selected':''}>Classificar...</option>`;
  CATS.forEach(c => o += `<option value="${c.id}" ${currentId==c.id?'selected':''}>${esc(c.nome)}</option>`);
  o += '</select>'; return o;
}

function tipoSelect(current, i) {
  let o = `<select class="t-row-sel tipo-sel" id="tipo-${i}" onclick="event.stopPropagation()" onchange="onTipoChange(${i})">`;
  TIPOS.forEach(t => o += `<option value="${t}" ${current===t?'selected':''}>${esc(t)}</option>`);
  o += '</select>'; return o;
}

function contatoSelect(currentId, i) {
  let o = `<select id="contato-${i}" onchange="onContatoChange(${i})">`;
  o += `<option value="">Nenhum...</option>`;
  CONTATOS.forEach(c => o += `<option value="${c.id}" ${currentId==c.id?'selected':''}>${esc(c.nome)}</option>`);
  o += '</select>'; return o;
}

// ───────────────────── STATE ─────────────────────
const state = DATA.transacoes.map((t, i) => {
  const dupMatch = detectDup(t, i);
  return {
    ...t, id: i,
    catId:     t.id_categoria || null,
    clienteId: t.id_cliente   || null,
    tipoUser:  t.tipo === 'emprestimo' ? 'Emprestado' : 'Pessoal',
    descartado: false,
    isDup:     !!dupMatch,
    dupDesc:   dupMatch ? dupMatch.descricao : null,
  };
});

function detectDup(t, i) {
    return t.is_duplicada || t.is_similar || t.similar_aproximado;

//   const base = t.descricao.toLowerCase().split(' ')[0];
//   const match = DATA.transacoes.find((o, j) => {
//     if (j >= i) return false;
//     const obase = o.descricao.toLowerCase().split(' ')[0];
//     return obase === base && Math.abs(parseFloat(o.valor) - parseFloat(t.valor)) < parseFloat(t.valor) * 0.5;
//   });
//   return match || null;
}

// ───────────────────── RENDER HELPERS ─────────────────────
function confBadge(c) {
  if (c === 'alta')  return '<span class="t-row-badge b-conf-alta">Alta</span>';
  if (c === 'média') return '<span class="t-row-badge b-conf-media">Média</span>';
  return '<span class="t-row-badge b-conf-baixa">Baixa</span>';
}

function instBadge(t) {
  if (t.total_parcelas > 1)
    return `<span class="t-row-badge b-inst">${t.parcela_atual}/${t.total_parcelas}</span>`;
  return '';
}

function renderExpandInner(t, i) {
  let html = '';

  if (t.confianca === 'baixa')
    html += `<div class="t-expand-alert" style="background:#FEF2F2;border-color:#FCA5A5;color:#7F1D1D;">
      ⚠ Confiança <strong>baixa</strong> na categorização — verifique antes de importar.<br/>
      <span style="font-size:11px;color:#9B1C1C;">Banco: ${esc(t.descricao_banco)}</span>
    </div>`;

  if (t.isDup)
    html += `<div class="t-expand-alert t-expand-dup">
      Duplicata detectada<br/>
      <span style="font-size:11px;">${esc(t.duplicada.descricao)}</span>
    </div>`;

  if (t.tipo === 'emprestimo')
    html += `<div class="t-expand-alert t-expand-emp">
      Empréstimo de cartão — registrado para <strong>${esc(state[i].clienteId ? getContatoNome(state[i].clienteId) : (t.pessoa || '?'))}</strong>
    </div>`;

  if (t.parcelas_futuras && t.parcelas_futuras.length > 0) {
    const pills = t.parcelas_futuras.map(p =>
      `<span class="t-expand-parcela-pill">${p.parcela}/${t.total_parcelas} · ${fmtDateFull(p.data_prevista)} · ${fmtVal(p.valor)}</span>`
    ).join('');
    html += `<div class="t-expand-alert t-expand-inst">
      ${t.parcelas_futuras.length} parcela(s) futura(s) serão planejadas automaticamente:
      <div class="t-expand-parcelas">${pills}</div>
    </div>`;
  }

  html += `<div class="t-expand-grid">
    <div>
      <label>Categoria</label>
      <select onchange="onCatChange(${i})" id="exp-cat-${i}">
        <option value="">Classificar...</option>
        ${CATS.map(c => `<option value="${c.id}" ${state[i].catId==c.id?'selected':''}>${esc(c.nome)}</option>`).join('')}
      </select>
    </div>
    <div>
      <label>Tipo</label>
      <select onchange="onTipoChange(${i})" id="exp-tipo-${i}">
        ${TIPOS.map(tp => `<option value="${tp}" ${state[i].tipoUser===tp?'selected':''}>${esc(tp)}</option>`).join('')}
      </select>
    </div>`;

  if (t.tipo === 'emprestimo')
    html += `<div><label>Para quem</label>${contatoSelect(state[i].clienteId, i)}</div>`;
  else
    html += `<div><label>Observação</label><input type="text" placeholder="Opcional..."/></div>`;

  html += `</div>`;

  if (t.isDup)
    html += `<div class="t-expand-actions">
      <button class="t-btn-ok" onclick="confirmarDup(${i})">Importar mesmo assim</button>
      <button class="t-btn-rm" onclick="descartarItem(${i})">Descartar</button>
    </div>`;

  return html;
}

function buildRow(t, i, badgeExtra = '') {
  const pessoaNome = state[i].clienteId ? getContatoNome(state[i].clienteId) : (t.pessoa || null);

  const dupNote = state[i].isDup && state[i].dupDesc
    ? `<span style="font-size:10px;color:#92400E;font-weight:400;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">≈ ${esc(state[i].dupDesc)}</span>`
    : '';
  const descOuter     = dupNote ? ' style="display:flex;flex-direction:column;gap:1px;overflow:hidden;white-space:normal;"' : '';
  const mainText      = t.tipo === 'emprestimo'
    ? `${esc(t.descricao)}${pessoaNome?' ('+esc(pessoaNome)+')':''}`
    : esc(t.descricao);
  const mainSpanStyle = dupNote ? ' style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"' : '';
  const mainClass     = t.tipo === 'emprestimo' ? 'desc-emp' : (!state[i].catId ? 'desc-pend' : '');
  const desc = `<span class="t-row-desc ${mainClass}"${descOuter}><span${mainSpanStyle}>${mainText}</span>${dupNote}</span>`;

  const badge = buildTags(t, i);

  return `
  <div class="t-row-wrap" id="wrap-${i}">
    <div class="t-row" id="row-${i}" onclick="toggleExpand(${i})">
      <input type="checkbox" class="t-row-cb cb" id="cb-${i}" onclick="event.stopPropagation();updBatch()" />
      ${desc}
      <span class="t-row-val">${fmtVal(t.valor)}</span>
      ${badge}
      ${instBadge(t)}
      ${confBadge(t.confianca)}
      ${catSelect(state[i].catUser, i)}
      ${tipoSelect(state[i].tipoUser, i)}
    </div>
    <div class="t-expand" id="exp-${i}">${renderExpandInner(t, i)}</div>
  </div>`;
}

// ───────────────────── CLASSIFICAÇÃO ─────────────────────
function classify(t, i) {
  if (state[i].descartado) return null;
  if (t.tipo === 'emprestimo') return 'emp';
  if (t.isDup) return 'dup';
  if (!state[i].catId) return 'pend';
  if (t.parcelas_futuras && t.parcelas_futuras.length > 0) return 'fut';
  return 'new';
}

// ───────────────────── AGRUPAMENTO POR FATURA ─────────────────────
function getMonthKey(dateStr) {
  return dateStr ? dateStr.slice(0, 7) : '0000-00';
}

function getMonthLabel(key) {
  const meses = ['Janeiro','Fevereiro','Março','Abril','Maio','Junho',
                 'Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
  const [y, m] = key.split('-');
  return meses[parseInt(m) - 1] + ' ' + y;
}

function buildTags(t, i) {
  let tags = '';
  const sec = classify(t, i);

  if (t.isDup)
    tags += '<span class="t-row-badge b-dup">⚠ Dup?</span>';

  if (sec === 'pend')
    tags += '<span class="t-row-badge b-conf-baixa">Classificar</span>';

  if (t.tipo === 'emprestimo') {
    const nome = state[i].clienteId ? getContatoNome(state[i].clienteId) : (t.pessoa || 'Empréstimo');
    tags += `<span class="t-row-badge b-emp">👤 ${esc(nome)}</span>`;
  }

  if (t.parcelas_futuras && t.parcelas_futuras.length > 0)
    tags += `<span class="t-row-badge b-plan">+${t.parcelas_futuras.length} fut.</span>`;

  return tags;
}

// ───────────────────── FILTROS ─────────────────────
let filterConfianca = '';
let filterDupOnly   = false;

function applyFilter() {
  filterConfianca = document.getElementById('filter-confianca').value;
  filterDupOnly   = document.getElementById('filter-dup').checked;
  renderAll();
}

function clearFilter() {
  filterConfianca = '';
  filterDupOnly   = false;
  document.getElementById('filter-confianca').value = '';
  document.getElementById('filter-dup').checked = false;
  renderAll();
}

function passesFilter(t, i) {
  if (filterConfianca && t.confianca !== filterConfianca) return false;
  if (filterDupOnly && !state[i].isDup) return false;
  return true;
}

function renderAll() {
  // ── 1. Montar mapa de faturas ──────────────────────────
  const faturaKey = getMonthKey(DATA_FATURA);
  const faturas   = {};

  const ensureFatura = (key) => {
    if (!faturas[key]) {
      faturas[key] = {
        label:     getMonthLabel(key),
        isCurrent: key === faturaKey,
        subtotal:  0,
        count:     0,
        rows:      [],
      };
    }
  };

  // ── 2. Distribuir transações por fatura ───────────────
  state.forEach((t, i) => {
    if (state[i].descartado) return;
    if (!passesFilter(t, i)) return;

    // Transação principal → sempre vai para a fatura atual (DATA_FATURA)
    const key = faturaKey;
    ensureFatura(key);
    faturas[key].rows.push({ t, i, isFuture: false });
    faturas[key].subtotal += parseFloat(t.valor) || 0;
    faturas[key].count++;

    // Parcelas futuras → cada uma vai para o mês de data_prevista
    (t.parcelas_futuras || []).forEach(p => {
      const fKey = getMonthKey(p.data_prevista);
      ensureFatura(fKey);
      faturas[fKey].rows.push({
        t: {
          ...t,
          data:             p.data_prevista,
          valor:            p.valor,
          parcela_atual:    p.parcela,
          parcelas_futuras: [],
          _isFutureRow:     true,
          _parentIndex:     i,
        },
        i,
        isFuture: true,
      });
      faturas[fKey].subtotal += parseFloat(p.valor) || 0;
      faturas[fKey].count++;
    });
  });

  // ── 3. Ordenar faturas: atual primeiro, depois crescente ─
  const sortedKeys = Object.keys(faturas).sort((a, b) => {
    if (a === faturaKey) return -1;
    if (b === faturaKey) return  1;
    return a.localeCompare(b);
  });

  // ── 4. Renderizar ─────────────────────────────────────
  const container = document.getElementById('faturas-container');
  container.innerHTML = '';

  sortedKeys.forEach(key => {
    const fatura = faturas[key];
    const secId  = 'fatura-' + key.replace('-', '');

    const headColor  = fatura.isCurrent ? '#065F46' : '#1D4A7C';
    const headSuffix = fatura.isCurrent ? ' — Fatura atual' : ' — Planejado';

    let html = `
    <div class="t-section" id="sec-${secId}-wrap" style="margin-bottom:0;">
      <div class="t-sec-head" style="background:${headColor}" onclick="toggleSec('${secId}')">
        <span>${fatura.isCurrent ? '📄' : '📅'}</span>
        <span>${esc(fatura.label)}${headSuffix}</span>
        <span class="t-sec-count">${fatura.count} lançamento${fatura.count !== 1 ? 's' : ''}</span>
        <span style="margin-left:8px;font-size:12px;opacity:.8;">${fmtVal(fatura.subtotal)}</span>
        <span class="t-sec-caret up" id="car-${secId}">▼</span>
      </div>
      <div class="t-sec-body open" id="body-${secId}">`;

    fatura.rows.forEach(({ t, i, isFuture }) => {
      if (isFuture) {
        html += `
        <div class="t-row-wrap">
          <div class="t-row" style="opacity:.75;cursor:default;background:#F0F6FF;">
            <span style="width:16px;flex-shrink:0;"></span>
            <span class="t-row-desc" style="color:#1D4A7C;">${esc(t.descricao)}</span>
            <span class="t-row-val" style="color:#1D4A7C;">${fmtVal(t.valor)}</span>
            <span class="t-row-badge b-plan">${t.parcela_atual}/${t.total_parcelas}</span>
            <span class="t-row-badge b-plan" style="font-size:10px;">Planejado</span>
          </div>
        </div>`;
      } else {
        html += buildRow(t, i);
      }
    });

    html += `</div></div>`;
    container.innerHTML += html;
  });

  // ── 5. Atualizar counters globais do topbar ────────────
  updateFooter();
  updatePills();
  if (typeof updateRecon === 'function') updateRecon();
}

function updateFooter() {
  let total = 0, totalFut = 0, catCount = 0, totalRows = 0;
  state.forEach((t, i) => {
    if (state[i].descartado) return;
    total += parseFloat(t.valor);
    totalRows++;
    if (state[i].catId) catCount++;
    (t.parcelas_futuras || []).forEach(p => totalFut += parseFloat(p.valor));
  });
  document.getElementById('total-val').textContent = fmtVal(total);
  document.getElementById('total-sub').textContent = totalFut > 0
    ? `+ ${fmtVal(totalFut)} em parcelas futuras planejadas` : '';
  const pct = totalRows ? Math.round(catCount / totalRows * 100) : 0;
  document.getElementById('progress').style.width = pct + '%';
  document.getElementById('progress-label').textContent = `${pct}% categorizados (${catCount}/${totalRows})`;
}

// ───────────────────── RECONCILIAÇÃO ─────────────────────
const TOTAL_FATURA_BANCO = {{ $total_fatura }};

function updateRecon() {
  // Calcular totais por grupo
  let totalJa = 0, countJa = 0;
  let totalNovos = 0, countNovos = 0;
  let totalDup = 0, countDup = 0;
  let totalEmp = 0, countEmp = 0;

  state.forEach((t, i) => {
    if (state[i].descartado) return;
    const sec = classify(t, i);
    const v = parseFloat(t.valor) || 0;

    const jaNoSistema = (t.parcela_atual > 1) && (!t.parcelas_futuras || t.parcelas_futuras.length === 0);

    if (jaNoSistema) {
      totalJa += v; countJa++;
    } else if (sec === 'dup') {
      totalDup += v; countDup++;
    } else if (sec === 'emp') {
      totalEmp += v; countEmp++;
    } else if (sec === 'new' || sec === 'fut' || sec === 'pend') {
      totalNovos += v; countNovos++;
    }
  });

  const totalPos = totalJa + totalNovos + totalDup + totalEmp;
  const diff     = Math.round((totalPos - TOTAL_FATURA_BANCO) * 100) / 100;
  const diffAbs  = Math.abs(diff);

  // Atualizar colunas
  document.getElementById('recon-sistema').textContent     = fmtVal(totalJa);
  document.getElementById('recon-sistema-sub').textContent = countJa + ' lançamento(s)';
  document.getElementById('recon-novos').textContent       = fmtVal(totalNovos + totalDup + totalEmp);
  document.getElementById('recon-novos-sub').textContent   = (countNovos + countDup + countEmp) + ' lançamento(s)';
  document.getElementById('recon-total').textContent       = fmtVal(totalPos);

  // Cor do total pós
  const totalEl = document.getElementById('recon-total');
  totalEl.style.color = diffAbs < 0.05 ? '#10B981' : diffAbs < 50 ? '#F59E0B' : '#EF4444';

  // Diff label
  const diffLabel = document.getElementById('recon-diff-label');
  if (diffAbs < 0.05) {
    diffLabel.textContent  = '✓ Bate com o extrato';
    diffLabel.style.color  = '#059669';
  } else {
    const sinal = diff > 0 ? '+' : '';
    diffLabel.textContent = `Diferença: ${sinal}${fmtVal(diff)}`;
    diffLabel.style.color = diffAbs < 50 ? '#B45309' : '#DC2626';
  }

  // Banner
  const banner     = document.getElementById('recon-banner');
  const bannerIcon = document.getElementById('recon-banner-icon');
  const bannerText = document.getElementById('recon-banner-text');
  if (diffAbs < 0.05) {
    banner.style.background     = '#ECFDF5';
    banner.style.borderColor    = '#A7F3D0';
    bannerIcon.textContent      = '✓';
    bannerText.style.color      = '#065F46';
    bannerText.textContent      = `Valores reconciliados — total do sistema (${fmtVal(totalPos)}) bate com o extrato (${fmtVal(TOTAL_FATURA_BANCO)}).`;
  } else if (diffAbs < 50) {
    banner.style.background     = '#FFFBEB';
    banner.style.borderColor    = '#FDE68A';
    bannerIcon.textContent      = '⚠';
    bannerText.style.color      = '#92400E';
    bannerText.textContent      = `Pequena divergência de ${fmtVal(diffAbs)} — verifique as duplicatas antes de importar.`;
  } else {
    banner.style.background     = '#FEF2F2';
    banner.style.borderColor    = '#FECACA';
    bannerIcon.textContent      = '✕';
    bannerText.style.color      = '#7F1D1D';
    bannerText.textContent      = `Divergência de ${fmtVal(diffAbs)} — revise os lançamentos antes de confirmar.`;
  }

  // Barra de composição
  const T = TOTAL_FATURA_BANCO || 1;
  document.getElementById('seg-ja').style.width   = Math.min(totalJa   / T * 100, 100).toFixed(1) + '%';
  document.getElementById('seg-novo').style.width = Math.min(totalNovos / T * 100, 100).toFixed(1) + '%';
  document.getElementById('seg-dup').style.width  = Math.min(totalDup  / T * 100, 100).toFixed(1) + '%';
  document.getElementById('seg-emp').style.width  = Math.min(totalEmp  / T * 100, 100).toFixed(1) + '%';
}

function updatePills() {
  let pend = 0, dup = 0, total = 0;
  state.forEach((t, i) => {
    if (state[i].descartado) return;
    total++;
    if (!state[i].catId) pend++;
    if (t.isDup) dup++;
  });

  document.getElementById('pill-new').textContent  = total + ' lançamentos';
  document.getElementById('pill-pend').textContent = pend  + ' sem categoria';
  document.getElementById('pill-dup').textContent  = dup   + ' duplicatas';

  const futKeys = new Set();
  state.forEach((t, i) => {
    if (state[i].descartado) return;
    (t.parcelas_futuras || []).forEach(p => {
      const k = getMonthKey(p.data_prevista);
      if (k !== getMonthKey(DATA_FATURA)) futKeys.add(k);
    });
  });
  document.getElementById('pill-inst').textContent = futKeys.size + ' meses futuros';
}

// ───────────────────── INTERAÇÕES ─────────────────────
function toggleSec(id) {
  const body = document.getElementById('body-' + id);
  const car  = document.getElementById('car-'  + id);
  const open = body.classList.toggle('open');
  car.classList.toggle('up', open);
}

function toggleExpand(i) {
  document.getElementById('exp-' + i).classList.toggle('open');
  document.getElementById('row-' + i).classList.toggle('selected');
}

function onCatChange(i) {
  const el = document.getElementById('exp-cat-' + i) || document.getElementById('cat-' + i);
  if (el) state[i].catId = el.value || null;
  renderAll();
}

function onTipoChange(i) {
  const el = document.getElementById('exp-tipo-' + i) || document.getElementById('tipo-' + i);
  if (el) state[i].tipoUser = el.value;
}

function onContatoChange(i) {
  const el = document.getElementById('contato-' + i);
  if (el) state[i].clienteId = el.value || null;
}

function confirmarDup(i) {
  state[i].isDup = false;
  renderAll();
}

function descartarItem(i) {
  state[i].descartado = true;
  const el = document.getElementById('wrap-' + i);
  if (el) el.style.display = 'none';
  renderAll();
}

// Batch
function updBatch() {
  const n = document.querySelectorAll('.cb:checked').length;
  document.getElementById('batchCount').textContent = n;
  document.getElementById('batchBar').classList.toggle('on', n > 0);
}

function clearSel() {
  document.querySelectorAll('.cb').forEach(c => c.checked = false);
  updBatch();
}

function applyBatch() {
  const catId = document.getElementById('bCat').value;
  const tipo  = document.getElementById('bTipo').value;
  if (!catId && !tipo) { alert('Selecione categoria ou tipo.'); return; }
  document.querySelectorAll('.cb:checked').forEach(cb => {
    const i = parseInt(cb.id.replace('cb-', ''));
    if (catId) state[i].catId    = catId;
    if (tipo)  state[i].tipoUser = tipo;
  });
  clearSel();
  renderAll();
}

function quickCat(catId) {
  const sel = document.querySelectorAll('.cb:checked');
  if (!sel.length) { alert('Selecione ao menos um lançamento primeiro.'); return; }
  sel.forEach(cb => {
    const i = parseInt(cb.id.replace('cb-', ''));
    state[i].catId = catId;
  });
  clearSel();
  renderAll();
}

function catIdByNome(nome) {
  const c = CATS.find(c => c.nome.toLowerCase() === nome.toLowerCase());
  return c ? c.id : null;
}

function autoFill() {
  let count = 0;
  state.forEach((t, i) => {
    if (state[i].catId) return;
    const d = t.descricao.toLowerCase() + ' ' + t.descricao_banco.toLowerCase();
    let nome = null;
    if      (/posto|combustiv|veloe|estacion/.test(d))                                  nome = 'Transporte';
    else if (/farmac|droga|saude|higiene|beauty|hair|lash/.test(d))                     nome = 'Beleza / Higiene';
    else if (/superm|atacad|merced|padaria|restaur|pizza|sushi|empadao|capone/.test(d)) nome = 'Alimentação';
    else if (/vivo|tim|claro|oi|internet|energia|luz|agua|gas/.test(d))                 nome = 'Contas mensais';
    else if (/infantil|tip top|kid|crianca/.test(d))                                    nome = 'Moda Infantil';
    else if (/calcad|roupa|moda|confec|lingerie|intima/.test(d))                        nome = 'Roupas';
    else if (/netflix|spotify|cinema|ingress|lazer|evento/.test(d))                     nome = 'Lazer';
    else if (/carro|mecanica|auto|pneu|oleo|revisao/.test(d))                           nome = 'Veículos';
    else if (/shopee|amazon|mercadolivre|magazine|casas bahia/.test(d))                 nome = 'Outros';
    const id = nome ? catIdByNome(nome) : null;
    if (id) { state[i].catId = id; count++; }
  });
  renderAll();
  alert(`Auto-preenchimento: ${count} lançamento(s) categorizado(s) automaticamente.`);
}

function submitImport(includeAll) {
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = '{{ route("transactions.importStore") }}';

  const csrf = document.createElement('input');
  csrf.type = 'hidden'; csrf.name = '_token'; csrf.value = '{{ csrf_token() }}';
  form.appendChild(csrf);

  const df = document.createElement('input');
  df.type = 'hidden'; df.name = 'data_fatura'; df.value = DATA_FATURA;
  form.appendChild(df);

  let idx = 0;
  state.forEach((t, i) => {
    if (state[i].descartado) return;
    const sec = classify(t, i);
    if (!includeAll && sec !== 'new') return;

    const fields = {
      importar:        '1',
      descricao:       t.descricao,
      descricao_banco: t.descricao_banco,
      valor:           t.valor,
      data:            t.data,
      id_cartao:       ID_CARTAO,
      id_categoria:    state[i].catId    || '',
      id_cliente:      state[i].clienteId || '',
      tipo:            t.tipo === 'emprestimo' ? 'emprestimo' : 'despesa',
      chave_banco:     t.chave_banco || '',
      data_banco:      t.data_banco  || '',
    };

    for (const [k, v] of Object.entries(fields)) {
      const inp = document.createElement('input');
      inp.type = 'hidden';
      inp.name = `transacoes[${idx}][${k}]`;
      inp.value = v;
      form.appendChild(inp);
    }
    idx++;
  });

  document.body.appendChild(form);
  form.submit();
}

function importAll() { submitImport(true); }
function importNew() { submitImport(false); }

// ───────────────────── INIT ─────────────────────
renderAll();
</script>

@endsection
