{{-- JS dos modais de lançamento (editar/criar) e dos controles mobile de data/valor.
     Usado pelo Resumo Mensal e pela Busca de lançamentos. --}}
<script>
// ── Quick Edit Modal ──────────────────────────────────────────────────────────
function metOpenModal(el) {
  var d = el.dataset;

  document.getElementById('form-met').action        = '/transactions/modal-update/' + d.id;
  document.getElementById('form-met-delete').action = '/transactions/' + d.id;
  document.getElementById('met-view-link').href     =
    '/transactions/view/' + d.id + '?_back=' + encodeURIComponent(window.location.href);

  document.getElementById('met-subtitle').textContent =
    '#' + d.id + ' • ' + metFmtDate(d.data);

  document.getElementById('met-descricao').value         = d.descricao        || '';
  document.getElementById('met-valor').value             = d.valor            || '';
  document.getElementById('met-data').value              = d.data             || '';
  document.getElementById('met-id-workspace').value      = d.idWorkspace      || '';
  document.getElementById('met-data-recebimento').value  = d.dataRecebimento  || '';

  document.getElementById('met-tipo').value = d.tipo || 'despesa';

  var catId = d.idCategoria || '';
  document.getElementById('met-id-categoria').value = catId;
  document.querySelectorAll('#modal-edit-transaction .met-quick-btn').forEach(function (qb) {
    qb.classList.toggle('active', qb.dataset.value === catId);
  });

  document.getElementById('met-id-cartao').value  = d.idCartao  || '';
  document.getElementById('met-id-cliente').value = d.idCliente || '';

  var dataPgto = d.dataPagamento || '';
  document.getElementById('met-data-pagamento').value = dataPgto;
  document.getElementById('met-marcar-pago').checked  = dataPgto !== '';

  var origemLine = document.getElementById('met-origem-line');
  if (d.origem) {
    document.getElementById('met-origem-text').textContent = metFmtOrigem(d.origem);
    origemLine.style.display = '';
  } else {
    origemLine.style.display = 'none';
  }

  $('#modal-edit-transaction').modal('show');
}

function metFmtOrigem(origem) {
  var providerLabels = { nubank: 'Nubank', bradesco: 'Bradesco' };
  if (origem.indexOf('pluggy_') === 0) {
    var provider = origem.replace('pluggy_', '');
    return 'Importado automaticamente via Pluggy (' + (providerLabels[provider] || provider) + ')';
  }
  if (origem === 'smartpos') {
    return 'Importado via Smartpos';
  }
  return 'Origem: ' + origem;
}

function metFmtDate(ymd) {
  if (!ymd) return '';
  var p = ymd.split('-');
  return p[2] + '/' + p[1] + '/' + p[0];
}

document.addEventListener('DOMContentLoaded', function () {
  // Edit modal — category quick buttons (scoped to edit modal)
  document.querySelectorAll('#modal-edit-transaction .met-quick-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
      document.querySelectorAll('#modal-edit-transaction .met-quick-btn').forEach(function (b) { b.classList.remove('active'); });
      this.classList.add('active');
      document.getElementById('met-id-categoria').value = this.dataset.value;
    });
  });

  // "Marcar como pago" checkbox
  document.getElementById('met-marcar-pago').addEventListener('change', function () {
    var pgto = document.getElementById('met-data-pagamento');
    if (this.checked && !pgto.value) {
      pgto.value = new Date().toISOString().split('T')[0];
    } else if (!this.checked) {
      pgto.value = '';
    }
    metMobileRefresh(pgto);
  });

  // Sync checkbox when date field is changed directly
  document.getElementById('met-data-pagamento').addEventListener('change', function () {
    document.getElementById('met-marcar-pago').checked = this.value !== '';
  });

  // Delete button
  document.getElementById('met-btn-delete').addEventListener('click', function () {
    var subtitle = document.getElementById('met-subtitle').textContent;
    if (confirm('Tem certeza que deseja excluir o lançamento ' + subtitle.split('•')[0].trim() + '? Esta ação não pode ser desfeita.')) {
      // Guarda onde a linha estava (vizinhas na ordem atual da tabela) para marcar o lugar após recarregar
      try {
        var m   = document.getElementById('form-met-delete').action.match(/(\d+)$/);
        var row = m ? document.querySelector('#view-table tr[data-id="' + m[1] + '"]') : null;
        if (row) {
          var params = new URLSearchParams(location.search);
          params.sort();
          var prev = row.previousElementSibling, next = row.nextElementSibling;
          sessionStorage.setItem('transactions_deleted', JSON.stringify({
            page:  location.pathname + '?' + params.toString(),
            prev:  prev ? prev.dataset.id : null,
            next:  next ? next.dataset.id : null,
            desc:  row.dataset.sortDescricao || '',
            valor: parseFloat(row.dataset.sortValor || '0'),
            ts:    Date.now()
          }));
        }
      } catch (e) {}
      document.getElementById('form-met-delete').submit();
    }
  });
});
// ─────────────────────────────────────────────────────────────────────────────

// ── Create Modal ──────────────────────────────────────────────────────────────
function metOpenCreateModal() {
  var today = new Date().toISOString().split('T')[0];

  document.getElementById('mcr-descricao').value      = '';
  document.getElementById('mcr-valor').value          = '';
  document.getElementById('mcr-data').value           = today;
  document.getElementById('mcr-tipo').value           = 'despesa';
  document.getElementById('mcr-id-cartao').value      = '';
  document.getElementById('mcr-id-cliente').value     = '';
  document.getElementById('mcr-data-pagamento').value = '';
  document.getElementById('mcr-marcar-pago').checked  = false;
  document.getElementById('mcr-id-categoria').value   = '';

  document.querySelectorAll('#mcr-quick-buttons .met-quick-btn').forEach(function (b) {
    b.classList.toggle('active', b.dataset.value === '');
  });

  $('#modal-create-transaction').modal('show');

  // Focus on description field after modal opens
  $('#modal-create-transaction').one('shown.bs.modal', function () {
    document.getElementById('mcr-descricao').focus();
  });
}

document.addEventListener('DOMContentLoaded', function () {
  if (!document.getElementById('modal-create-transaction')) return;

  // Intercept floating "Novo lançamento" button
  var btnNovo = document.getElementById('btn-novo-lancamento');
  if (btnNovo) {
    btnNovo.addEventListener('click', function (e) {
      e.preventDefault();
      metOpenCreateModal();
    });
  }

  // Create modal — category quick buttons
  document.querySelectorAll('#mcr-quick-buttons .met-quick-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
      document.querySelectorAll('#mcr-quick-buttons .met-quick-btn').forEach(function (b) { b.classList.remove('active'); });
      this.classList.add('active');
      document.getElementById('mcr-id-categoria').value = this.dataset.value;
    });
  });

  // Create modal — "Marcar como pago" checkbox
  document.getElementById('mcr-marcar-pago').addEventListener('change', function () {
    var pgto = document.getElementById('mcr-data-pagamento');
    if (this.checked && !pgto.value) {
      pgto.value = new Date().toISOString().split('T')[0];
    } else if (!this.checked) {
      pgto.value = '';
    }
    metMobileRefresh(pgto);
  });

  // Create modal — sync checkbox when date is changed directly
  document.getElementById('mcr-data-pagamento').addEventListener('change', function () {
    document.getElementById('mcr-marcar-pago').checked = this.value !== '';
  });
});
// ─────────────────────────────────────────────────────────────────────────────

// ── Mobile: data sem teclado + valor estilo caixa eletrônico ─────────────────
// Os inputs nativos (date / number) continuam sendo os enviados no form; no
// celular o CSS os esconde e estes controles só escrevem neles. Quem altera
// o .value por código chama metMobileRefresh() para redesenhar.
var MET_MESES  = ['janeiro','fevereiro','março','abril','maio','junho','julho','agosto','setembro','outubro','novembro','dezembro'];
var MET_SEMANA = ['domingo','segunda-feira','terça-feira','quarta-feira','quinta-feira','sexta-feira','sábado'];
var MET_SIGLA  = ['dom','seg','ter','qua','qui','sex','sáb'];

function metYmd(y, m, d) {
  return y + '-' + String(m + 1).padStart(2, '0') + '-' + String(d).padStart(2, '0');
}

function metMobileRefresh(el) {
  if (el && el._metRefresh) el._metRefresh();
}

function metInitDatePicker(input) {
  var box = document.createElement('div');
  box.className = 'met-datepicker';
  box.innerHTML =
    '<div class="met-dp-head">' +
      '<button type="button" class="met-dp-nav" data-step="-1" aria-label="Mês anterior"><i class="fas fa-chevron-left"></i></button>' +
      '<div class="met-dp-title"><span class="met-dp-month"></span><span class="met-dp-summary"></span></div>' +
      '<button type="button" class="met-dp-nav" data-step="1" aria-label="Próximo mês"><i class="fas fa-chevron-right"></i></button>' +
    '</div>' +
    '<div class="met-dp-days"></div>';
  input.parentNode.insertBefore(box, input.nextSibling);

  var days = box.querySelector('.met-dp-days');
  var view = null; // {y, m} exibido quando o campo está vazio

  function parse() {
    var p = (input.value || '').split('-');
    return p.length === 3 ? { y: +p[0], m: +p[1] - 1, d: +p[2] } : null;
  }

  function setValue(y, m, d) {
    input.value = metYmd(y, m, d);
    input.dispatchEvent(new Event('change', { bubbles: true }));
    render();
  }

  function render() {
    var sel = parse(), now = new Date();
    if (sel) view = { y: sel.y, m: sel.m };
    if (!view) view = { y: now.getFullYear(), m: now.getMonth() };

    var nome = MET_MESES[view.m];
    box.querySelector('.met-dp-month').textContent = nome.charAt(0).toUpperCase() + nome.slice(1) + ' ' + view.y;

    var resumo = 'Nenhuma data';
    if (sel) {
      var ehHoje = sel.y === now.getFullYear() && sel.m === now.getMonth() && sel.d === now.getDate();
      resumo = MET_SEMANA[new Date(sel.y, sel.m, sel.d).getDay()] + ', ' + sel.d + (ehHoje ? ' · hoje' : '');
    }
    box.querySelector('.met-dp-summary').textContent = resumo;

    var ult = new Date(view.y, view.m + 1, 0).getDate(), html = '';
    for (var d = 1; d <= ult; d++) {
      var cls = 'met-dp-day';
      if (sel && sel.y === view.y && sel.m === view.m && sel.d === d) cls += ' active';
      if (view.y === now.getFullYear() && view.m === now.getMonth() && d === now.getDate()) cls += ' today';
      html += '<button type="button" class="' + cls + '" data-day="' + d + '" aria-label="' + d + ' de ' + MET_MESES[view.m] + '">' +
                '<span class="met-dp-day-wd">' + MET_SIGLA[new Date(view.y, view.m, d).getDay()] + '</span>' +
                '<span class="met-dp-day-num">' + d + '</span>' +
              '</button>';
    }
    days.innerHTML = html;
    scrollToSelected();
  }

  // Centraliza o dia selecionado (ou hoje) na régua; só funciona com o modal visível
  function scrollToSelected() {
    var alvo = days.querySelector('.met-dp-day.active') || days.querySelector('.met-dp-day.today');
    if (alvo && days.clientWidth) {
      days.scrollLeft = alvo.offsetLeft - days.offsetLeft - (days.clientWidth - alvo.offsetWidth) / 2;
    } else if (days.clientWidth) {
      days.scrollLeft = 0;
    }
  }

  box.querySelectorAll('.met-dp-nav').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var step = +this.dataset.step, sel = parse();
      if (sel) {
        // Troca o mês já na data, mantendo o dia (31/out → 30/nov)
        var ult = new Date(sel.y, sel.m + step + 1, 0).getDate();
        var alvo = new Date(sel.y, sel.m + step, Math.min(sel.d, ult));
        setValue(alvo.getFullYear(), alvo.getMonth(), alvo.getDate());
      } else {
        var v = new Date(view.y, view.m + step, 1);
        view = { y: v.getFullYear(), m: v.getMonth() };
        render();
      }
    });
  });

  days.addEventListener('click', function (e) {
    var btn = e.target.closest('.met-dp-day');
    if (btn) setValue(view.y, view.m, +btn.dataset.day);
  });

  input.addEventListener('change', render);
  input._metRefresh = function () { view = null; render(); };
  input._metScroll  = scrollToSelected;
  render();
}

function metInitValorMobile(display) {
  var target = document.getElementById(display.dataset.valorFor);

  function fmt(centavos) {
    return (centavos / 100).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  // Dígitos entram pela direita: 1 2 3 4 → 12,34
  display.addEventListener('input', function () {
    var dig = this.value.replace(/\D/g, '').replace(/^0+/, '').slice(0, 11);
    var centavos = parseInt(dig || '0', 10);
    this.value   = centavos ? fmt(centavos) : '';
    target.value = centavos ? (centavos / 100).toFixed(2) : '';
    this.setSelectionRange(this.value.length, this.value.length);
  });

  target._metRefresh = function () {
    var v = parseFloat(target.value);
    display.value = v ? fmt(Math.round(v * 100)) : '';
  };
  target._metRefresh();
}

document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('input[data-mobile-picker]').forEach(metInitDatePicker);
  document.querySelectorAll('.met-valor-mobile').forEach(metInitValorMobile);

  // Os modais preenchem os campos antes de abrir: redesenha ao abrir e
  // centraliza a régua de dias quando já há largura para medir
  $('#modal-edit-transaction, #modal-create-transaction')
    .on('show.bs.modal', function () {
      this.querySelectorAll('input[data-mobile-picker], .met-currency-wrap input[type="number"]').forEach(metMobileRefresh);
    })
    .on('shown.bs.modal', function () {
      this.querySelectorAll('input[data-mobile-picker]').forEach(function (el) { el._metScroll && el._metScroll(); });
    });
});
// ─────────────────────────────────────────────────────────────────────────────
</script>
