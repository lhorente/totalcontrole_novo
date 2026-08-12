@extends('layouts.dashboard')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/transaction-modal.css') }}">
@endpush

@section('content')
<div class="content-header">
  <div class="container">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 text-dark">Resumo mensal</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
          <li class="breadcrumb-item"><a href="{{ route('transactions.month') }}">Resumo mensal</a></li>
          <li class="breadcrumb-item active">Lançamentos</li>
        </ol>
      </div>
    </div>
  </div>
</div>

<div class="content">
  <div class="container">
    <div class="row justify-content-center">

    {{-- Navigation (only when filtering by month) --}}
      @if ($month)
      <div class="col-md-12 d-flex justify-content-between mb-2">
        <a href="{{ $prevUrl ?? route('transactions.month', [$beforeMonthObj->format('Y'), (int)$beforeMonthObj->format('m')]) }}" class="btn btn-sm btn-outline-secondary">
          <i class="fa fa-chevron-left"></i> {{ $beforeMonthObj->format('M/Y') }}
        </a>
        <strong class="align-self-center" id="current-month-label">
          @php $meses = [1=>'Janeiro',2=>'Fevereiro',3=>'Março',4=>'Abril',5=>'Maio',6=>'Junho',7=>'Julho',8=>'Agosto',9=>'Setembro',10=>'Outubro',11=>'Novembro',12=>'Dezembro']; @endphp
          {{ $meses[$month] ?? $month }} / {{ $year }}
        </strong>
        <a href="{{ $nextUrl ?? route('transactions.month', [$nextMonthObj->format('Y'), (int)$nextMonthObj->format('m')]) }}" class="btn btn-sm btn-outline-secondary">
          {{ $nextMonthObj->format('M/Y') }} <i class="fa fa-chevron-right"></i>
        </a>
      </div>
      @endif

      {{-- Filter card --}}
      <div class="col-md-12">
        <div class="card collapsed-card">
          <div class="card-header">
            <h3 class="card-title"><i class="fa fa-filter"></i> Filtros</h3>
            <div class="card-tools">
              <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-plus"></i>
              </button>
            </div>
          </div>

          <div class="card-body">
            <form method="GET" action="{{ route('transactions.month', [$year, $month]) }}">

              <div class="row">

                {{-- Predefined filters --}}
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Filtros pré-definidos</label>
                    <select class="form-control" name="ps">
                      <option value=""></option>
                      <option value="lendings_not_paid">Empréstimos não pagos</option>
                    </select>
                  </div>
                </div>

                {{-- Type --}}
                <div class="col-md-2">
                  <div class="form-group">
                    <label>Tipo</label>
                    <select class="form-control" name="t">
                      <option value="">Todos</option>
                      <option value="despesa"      @selected($type === 'despesa')>Despesa</option>
                      <option value="lucro"      @selected($type === 'lucro')>Receita</option>
                      <option value="transferencia" @selected($type === 'transferencia')>Transferência</option>
                      <option value="emprestimo"   @selected($type === 'emprestimo')>Empréstimo</option>
                      <option value="pagamento_emprestimo"   @selected($type === 'pagamento_emprestimo')>Pgto. Empréstimo</option>
                    </select>
                  </div>
                </div>

              </div>{{-- /.row --}}

              <div class="row">

                {{-- Category --}}
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Categoria</label>
                    <select class="form-control" name="categoria">
                      <option value="">Todas</option>
                      @foreach ($categorias as $cat)
                        <option value="{{ $cat->id }}" @if($categoria && $categoria->id == $cat->id) selected @endif>{{ $cat->nome }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>

                {{-- Credit card --}}
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Cartão</label>
                    <select class="form-control" name="cartao">
                      <option value="">Todos</option>
                      @foreach ($cartoes as $c)
                        <option value="{{ $c->id }}" @if($cartao && $cartao->id == $c->id) selected @endif>{{ $c->descricao }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>

                {{-- Person/contact --}}
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Pessoa</label>
                    <select class="form-control" name="pessoa">
                      <option value="">Todas</option>
                      @foreach ($pessoas as $p)
                        <option value="{{ $p->id }}" @if($pessoa && $pessoa->id == $p->id) selected @endif>{{ $p->nome }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>

                {{-- Wallet --}}
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Carteira</label>
                    <select class="form-control" name="caixa">
                      <option value="">Todas</option>
                      @foreach ($caixas as $cx)
                        <option value="{{ $cx->id }}" @if($caixa && $caixa->id == $cx->id) selected @endif>{{ $cx->titulo }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>

              </div>{{-- /.row --}}

              <div class="row">
                <div class="col-md-12">
                  <button type="submit" class="btn btn-primary">
                    <i class="fa fa-search"></i> Buscar
                  </button>
                </div>
              </div>

            </form>
          </div>
        </div>
      </div>{{-- /.col filter card --}}

      {{-- Summary: empresa = DRE, pessoal = totais e resumos --}}
      @if (optional($activeWorkspace)->tipo === 'empresa')
        @include('transactions.partials.summary_empresa')
      @else
        @include('transactions.partials.summary_pessoal')
      @endif

      {{-- View toggle + transaction list --}}
      <div class="col-md-12">

        <div class="d-flex justify-content-between align-items-center mb-2">
          <small class="text-muted">{{ $transactions->count() }} lançamento(s)</small>
          <div class="d-flex" style="gap:.5rem">
            <div class="btn-group btn-group-sm" role="group" id="view-toggle">
              <button type="button" class="btn btn-outline-secondary" id="btn-view-table" title="Visualização em tabela">
                <i class="fas fa-table"></i>
              </button>
              <button type="button" class="btn btn-outline-secondary" id="btn-view-cards" title="Visualização em cartões">
                <i class="fas fa-th-list"></i>
              </button>
            </div>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-toggle="modal" data-target="#modal-export" title="Exportar lançamentos">
              <i class="fas fa-download"></i> Exportar
            </button>
          </div>
        </div>

        {{-- Bulk action toolbar (shown when rows are selected) --}}
        <div id="bulk-toolbar" style="display:none" class="card card-body py-2 px-3 mb-2 bg-light border">
          <div class="d-flex align-items-center flex-wrap" style="gap:.5rem">
            <span class="mr-1"><strong id="bulk-count">0</strong> selecionado(s)</span>
            <form id="bulk-form" method="POST" action="{{ route('transactions.bulkUpdate') }}" class="d-flex align-items-center" style="gap:.5rem">
              @csrf
              <input type="hidden" name="field" value="id_categoria">
              <label class="mb-0 text-nowrap">Categoria:</label>
              <select name="value" class="form-control form-control-sm" style="width:200px">
                <option value="">— Sem categoria —</option>
                @foreach ($categorias as $cat)
                  <option value="{{ $cat->id }}">{{ $cat->nome }}</option>
                @endforeach
              </select>
              <button type="submit" class="btn btn-sm btn-primary text-nowrap">
                <i class="fa fa-check"></i> Aplicar
              </button>
            </form>
            @if (isset($workspaces) && $workspaces->count() > 1)
            <form id="bulk-form-ws" method="POST" action="{{ route('transactions.bulkUpdate') }}" class="d-flex align-items-center" style="gap:.5rem">
              @csrf
              <input type="hidden" name="field" value="id_workspace">
              <label class="mb-0 text-nowrap">Workspace:</label>
              <select name="value" class="form-control form-control-sm" style="width:200px">
                @foreach ($workspaces as $ws)
                  <option value="{{ $ws->id }}">{{ $ws->nome }}</option>
                @endforeach
              </select>
              <button type="submit" class="btn btn-sm btn-primary text-nowrap">
                <i class="fa fa-check"></i> Aplicar
              </button>
            </form>
            @endif
            <button type="button" class="btn btn-sm btn-outline-secondary ml-auto" id="bulk-clear">
              <i class="fa fa-times"></i> Cancelar seleção
            </button>
          </div>
        </div>

        {{-- TABLE VIEW --}}
        <div id="view-table">
          @if ($transactions->count() > 0)
          <div class="card">
            <div class="card-body p-0">
              <table class="table table-sm table-hover mb-0">
                <thead>
                  <tr>
                    <th style="width:35px" class="text-center">
                      <input type="checkbox" id="cb-select-all" title="Selecionar todos">
                    </th>
                    <th class="d-none d-sm-table-cell th-sortable" style="width:60px; cursor:pointer; user-select:none" data-sort-key="data" data-sort-type="text">
                      Dia <i class="fas fa-sort sort-icon text-muted ml-1" style="font-size:.75em"></i>
                    </th>
                    <th class="th-sortable" style="cursor:pointer; user-select:none" data-sort-key="descricao" data-sort-type="text">
                      Descrição <i class="fas fa-sort sort-icon text-muted ml-1" style="font-size:.75em"></i>
                    </th>
                    <th class="d-none d-md-table-cell th-sortable" style="width:120px; cursor:pointer; user-select:none" data-sort-key="categoria" data-sort-type="text">
                      Categoria <i class="fas fa-sort sort-icon text-muted ml-1" style="font-size:.75em"></i>
                    </th>
                    <th class="d-none d-md-table-cell th-sortable" style="width:90px; cursor:pointer; user-select:none" data-sort-key="tipo" data-sort-type="text">
                      Tipo <i class="fas fa-sort sort-icon text-muted ml-1" style="font-size:.75em"></i>
                    </th>
                    <th class="d-none d-md-table-cell th-sortable" style="width:120px; cursor:pointer; user-select:none" data-sort-key="cartao" data-sort-type="text">
                      Cartão <i class="fas fa-sort sort-icon text-muted ml-1" style="font-size:.75em"></i>
                    </th>
                    @if (optional($activeWorkspace)->tipo != 'empresa')
                    <th class="d-none d-md-table-cell th-sortable" style="width:120px; cursor:pointer; user-select:none" data-sort-key="pessoa" data-sort-type="text">
                      Pessoa <i class="fas fa-sort sort-icon text-muted ml-1" style="font-size:.75em"></i>
                    </th>
                    @endif
                    @if (!empty($showWorkspaceColumn))
                    <th class="d-none d-md-table-cell th-sortable text-center" style="width:60px; cursor:pointer; user-select:none" data-sort-key="workspace" data-sort-type="text">
                      WS <i class="fas fa-sort sort-icon text-muted ml-1" style="font-size:.75em"></i>
                    </th>
                    @endif
                    <th class="text-right th-sortable" style="width:100px; cursor:pointer; user-select:none" data-sort-key="valor" data-sort-type="number">
                      Valor <i class="fas fa-sort sort-icon text-muted ml-1" style="font-size:.75em"></i>
                    </th>
                    <th class="text-right d-none d-sm-table-cell" style="width:90px">Pgto.</th>
                  </tr>
                </thead>
                <tbody> 
                  @foreach ($transactions as $transaction)
                  @php
                    $tipoLabels = ['despesa'=>'Despesa','lucro'=>'Receita','transferencia'=>'Transferência','emprestimo'=>'Empréstimo','pagamento_emprestimo'=>'Pgto. Empréstimo'];
                    $tipoBadge  = ['despesa'=>'danger','lucro'=>'success','transferencia'=>'secondary','emprestimo'=>'warning','pagamento_emprestimo'=>'success'];
                  @endphp
                  <tr style="cursor:pointer"
                      onclick="bulkRowClick(event, this)"
                      data-id="{{ $transaction->id }}"
                      data-descricao="{{ $transaction->descricao ?: '' }}"
                      data-valor="{{ $transaction->valor }}"
                      data-data="{{ $transaction->data->format('Y-m-d') }}"
                      data-tipo="{{ $transaction->tipo }}"
                      data-id-categoria="{{ $transaction->id_categoria ?? '' }}"
                      data-id-cartao="{{ $transaction->id_cartao ?? '' }}"
                      data-id-cliente="{{ $transaction->id_cliente ?? '' }}"
                      data-data-pagamento="{{ $transaction->data_pagamento ? \Carbon\Carbon::parse($transaction->data_pagamento)->format('Y-m-d') : '' }}"
                      data-data-recebimento="{{ $transaction->data_recebimento ? \Carbon\Carbon::parse($transaction->data_recebimento)->format('Y-m-d') : '' }}"
                      data-id-workspace="{{ $transaction->id_workspace }}"
                      data-descricao-banco="{{ $transaction->descricao_banco ?: '' }}"
                      data-chave-banco="{{ $transaction->chave_banco ?: '' }}"
                      data-sort-data="{{ $transaction->data->format('Y-m-d') }}"
                      data-sort-descricao="{{ $transaction->descricao ?: $transaction->descricao_banco }}"
                      data-sort-categoria="{{ optional($transaction->category)->nome ?? '' }}"
                      data-sort-tipo="{{ $tipoLabels[$transaction->tipo] ?? $transaction->tipo ?? '' }}"
                      data-sort-cartao="{{ optional($transaction->credit_card)->descricao ?? '' }}"
                      data-sort-pessoa="{{ optional($transaction->contact)->nome ?? '' }}"
                      data-sort-workspace="{{ optional($transaction->workspace)->nome ?? '' }}"
                      data-sort-valor="{{ $transaction->valor }}">
                    <td onclick="event.stopPropagation()" class="text-center" style="width:35px">
                      <input type="checkbox" class="cb-row" data-id="{{ $transaction->id }}">
                    </td>
                    <td class="text-nowrap d-none d-sm-table-cell">{{ $transaction->data->format('d') }}</td>
                    <td>
                      @if ($transaction->category)
                        <i class="{{ $transaction->category->icon_class }} text-muted mr-1" style="font-size:.8em"></i>
                      @endif
                      {{ $transaction->descricao ?: $transaction->descricao_banco }}
                      {{-- Mobile-only secondary info --}}
                      <div class="d-sm-none mt-1" style="font-size:.78em; line-height:1.6">
                        @if ($transaction->tipo)
                          <span class="badge badge-{{ $tipoBadge[$transaction->tipo] ?? 'light' }} mr-1">
                            {{ $tipoLabels[$transaction->tipo] ?? ucfirst($transaction->tipo) }}
                          </span>
                        @endif
                        @if ($transaction->category)
                          <span class="text-muted"><i class="fa fa-tag fa-xs"></i> {{ $transaction->category->nome }}</span>
                        @endif
                        @if ($transaction->credit_card)
                          <span class="text-muted ml-1"><i class="fa fa-credit-card fa-xs"></i> {{ $transaction->credit_card->descricao }}</span>
                        @endif
                        @if ($transaction->contact)
                          <span class="text-muted ml-1"><i class="fa fa-user fa-xs"></i> {{ explode(' ', $transaction->contact->nome)[0] }}</span>
                        @endif
                        <br>
                        @if ($transaction->data_pagamento)
                          <span class="text-success"><i class="fa fa-check" label="Pago {{ \Carbon\Carbon::parse($transaction->data_pagamento)->format('d/m') }}"></i></span>
                        @else
                          <span class="text-danger"><i class="fa fa-clock"></i> </span>
                        @endif
                      </div>
                    </td>
                    <td class="d-none d-md-table-cell">{{ $transaction->category->nome ?? '—' }}</td>
                    <td class="d-none d-md-table-cell">
                      @if ($transaction->tipo)
                        <span class="badge badge-{{ $tipoBadge[$transaction->tipo] ?? 'light' }}">
                          {{ $tipoLabels[$transaction->tipo] ?? ucfirst($transaction->tipo) }}
                        </span>
                      @endif
                    </td>
                    <td class="d-none d-md-table-cell">
                      @if ($transaction->credit_card)
                        <span><i class="fa fa-credit-card fa-xs text-muted"></i> {{ $transaction->credit_card->descricao }}</span>
                      @else
                        —
                      @endif
                    </td>
                    @if (optional($activeWorkspace)->tipo != 'empresa')
                    <td class="d-none d-md-table-cell">
                      @if ($transaction->contact)
                        <i class="fa fa-user fa-xs text-muted"></i> {{ explode(' ', $transaction->contact->nome)[0] }}
                      @else
                        —
                      @endif
                    </td>
                    @endif
                    @if (!empty($showWorkspaceColumn))
                    <td class="d-none d-md-table-cell text-center" title="{{ optional($transaction->workspace)->nome ?? '' }}">
                      @php
                        $wsNome = optional($transaction->workspace)->nome ?? '';
                        $wsInitials = $wsNome
                          ? implode('', array_map(fn($w) => mb_strtoupper(mb_substr($w, 0, 1)), array_filter(explode(' ', $wsNome))))
                          : '—';
                      @endphp
                      <span class="badge badge-secondary">{{ $wsInitials }}</span>
                    </td>
                    @endif
                    <td class="text-right font-weight-bold text-nowrap">
                      R$ {{ number_format($transaction->valor, 2, ',', '.') }}
                    </td>
                    <td class="text-right d-none d-sm-table-cell text-nowrap">
                      @if ($transaction->data_pagamento)
                        <span class="text-success"><i class="fa fa-check" label="{{ \Carbon\Carbon::parse($transaction->data_pagamento)->format('d/m/Y') }}"></i></span>
                      @else
                        <span class="text-danger"><i class="fa fa-clock" label="Pendente"></i></span>
                      @endif
                    </td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
          @else
          <div class="alert alert-info">Nenhum lançamento encontrado para os filtros selecionados.</div>
          @endif
        </div>{{-- /#view-table --}}

        {{-- CARD VIEW --}}
        <div id="view-cards" style="display:none">
          @forelse ($transactions as $transaction)
          <a href="#"
             onclick="event.preventDefault(); metOpenModal(this)"
             class="info-box info-box-transaction"
             data-id="{{ $transaction->id }}"
             data-descricao="{{ $transaction->descricao ?: '' }}"
             data-valor="{{ $transaction->valor }}"
             data-data="{{ $transaction->data->format('Y-m-d') }}"
             data-tipo="{{ $transaction->tipo }}"
             data-id-categoria="{{ $transaction->id_categoria ?? '' }}"
             data-id-cartao="{{ $transaction->id_cartao ?? '' }}"
             data-id-cliente="{{ $transaction->id_cliente ?? '' }}"
             data-data-pagamento="{{ $transaction->data_pagamento ? \Carbon\Carbon::parse($transaction->data_pagamento)->format('Y-m-d') : '' }}"
             data-data-recebimento="{{ $transaction->data_recebimento ? \Carbon\Carbon::parse($transaction->data_recebimento)->format('Y-m-d') : '' }}"
             data-id-workspace="{{ $transaction->id_workspace }}">
            <span class="info-box-icon {{ $transaction->data_pagamento ? 'bg-secondary' : ($transaction->tipo === 'receita' ? 'bg-success' : 'bg-danger') }}">
              @if ($transaction->category)
              <i class="{{ $transaction->category->icon_class }}"></i>
              @else
              <i class="fas fa-exchange-alt"></i>
              @endif
            </span>
            <div class="info-box-content">
              <span class="info-box-text">{{ $transaction->descricao ?: $transaction->descricao_banco }}</span>
              <span class="info-box-number">
                <span>{{ $transaction->data->format('d/m/Y') }}</span>
                <span>R$ {{ number_format($transaction->valor, 2, ',', '.') }}</span>
              </span>
              <div class="categories">
                @if ($transaction->wallet)
                <span><i class="fa fa-wallet"></i> {{ $transaction->wallet->titulo }}</span>
                @endif

                @if ($transaction->category)
                <span><i class="fa fa-tag"></i> {{ $transaction->category->nome }}</span>
                @endif

                @if ($transaction->tipo)
                <span>{{ ucfirst($transaction->tipo) }}</span>
                @endif

                @if ($transaction->credit_card)
                <span><i class="fa fa-credit-card"></i> {{ $transaction->credit_card->descricao }}</span>
                @endif

                @if ($transaction->contact)
                  @if ($transaction->data_recebimento)
                    <span><i class="fa fa-user-check"></i> {{ explode(' ', $transaction->contact->nome)[0] }}</span>
                  @else
                    <span><i class="fa fa-user"></i> {{ explode(' ', $transaction->contact->nome)[0] }}</span>
                  @endif
                @endif

                @if ($transaction->data_pagamento)
                <span class="text-success"><i class="fa fa-check"></i> Pago em {{ \Carbon\Carbon::parse($transaction->data_pagamento)->format('d/m/Y') }}</span>
                @else
                <span class="text-danger"><i class="fa fa-clock"></i> Pendente</span>
                @endif
              </div>
            </div>
          </a>
          @empty
          <div class="alert alert-info">Nenhum lançamento encontrado para os filtros selecionados.</div>
          @endforelse
        </div>{{-- /#view-cards --}}

      </div>{{-- /.col --}}

    </div>{{-- /.row --}}
  </div>{{-- /.container --}}
</div>{{-- /.content --}}

@include('transactions.partials.modal_edit')
@include('transactions.partials.modal_create')

{{-- Export Modal --}}
<div class="modal fade" id="modal-export" tabindex="-1" role="dialog" aria-labelledby="modal-export-title" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header py-2 px-3">
        <h6 class="modal-title" id="modal-export-title"><i class="fas fa-download mr-1"></i> Exportar lançamentos</h6>
        <button type="button" class="close" data-dismiss="modal" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body px-3 py-3">
        <p class="text-muted mb-2" style="font-size:.85em">Formato:</p>
        <div class="btn-group btn-group-sm d-flex mb-3" role="group" id="export-fmt-toggle">
          <button type="button" class="btn btn-outline-secondary active" data-fmt="csv">CSV</button>
          <button type="button" class="btn btn-outline-secondary" data-fmt="json">JSON</button>
          <button type="button" class="btn btn-outline-secondary" data-fmt="md">Markdown</button>
          <button type="button" class="btn btn-outline-secondary" data-fmt="image">Imagem</button>
        </div>
        <div class="d-flex flex-column" style="gap:.5rem">
          <button type="button" class="btn btn-primary btn-block" id="export-do-download">
            <i class="fas fa-download mr-1"></i> Baixar arquivo
          </button>
          <button type="button" class="btn btn-outline-secondary btn-block" id="export-do-copy">
            <i class="fas fa-copy mr-1"></i> Copiar para área de transferência
          </button>
        </div>
        <div id="export-copy-feedback" class="mt-2 text-center text-success" style="display:none; font-size:.85em">
          <i class="fas fa-check mr-1"></i> Copiado!
        </div>
        <canvas id="export-image-canvas" style="display:none"></canvas>
      </div>
    </div>
  </div>
</div>

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

  $('#modal-edit-transaction').modal('show');
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
  });

  // Sync checkbox when date field is changed directly
  document.getElementById('met-data-pagamento').addEventListener('change', function () {
    document.getElementById('met-marcar-pago').checked = this.value !== '';
  });

  // Delete button
  document.getElementById('met-btn-delete').addEventListener('click', function () {
    var subtitle = document.getElementById('met-subtitle').textContent;
    if (confirm('Tem certeza que deseja excluir o lançamento ' + subtitle.split('•')[0].trim() + '? Esta ação não pode ser desfeita.')) {
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
  });

  // Create modal — sync checkbox when date is changed directly
  document.getElementById('mcr-data-pagamento').addEventListener('change', function () {
    document.getElementById('mcr-marcar-pago').checked = this.value !== '';
  });
});
// ─────────────────────────────────────────────────────────────────────────────

(function () {
  // ── Bulk selection ────────────────────────────────────────────────────────
  var selectedIds = new Set();

  function updateBulkToolbar() {
    var toolbar  = document.getElementById('bulk-toolbar');
    var countEl  = document.getElementById('bulk-count');
    var headerCb = document.getElementById('cb-select-all');
    var allCbs   = document.querySelectorAll('.cb-row');

    countEl.textContent   = selectedIds.size;
    toolbar.style.display = selectedIds.size > 0 ? '' : 'none';

    if (headerCb) {
      var checkedCount = document.querySelectorAll('.cb-row:checked').length;
      headerCb.indeterminate = checkedCount > 0 && checkedCount < allCbs.length;
      headerCb.checked       = allCbs.length > 0 && checkedCount === allCbs.length;
    }
  }

  document.querySelectorAll('.cb-row').forEach(function (cb) {
    cb.addEventListener('change', function () {
      var id = this.dataset.id;
      if (this.checked) {
        selectedIds.add(id);
        this.closest('tr').classList.add('table-active');
      } else {
        selectedIds.delete(id);
        this.closest('tr').classList.remove('table-active');
      }
      updateBulkToolbar();
    });
  });

  var headerCb = document.getElementById('cb-select-all');
  if (headerCb) {
    headerCb.addEventListener('change', function () {
      document.querySelectorAll('.cb-row').forEach(function (cb) {
        cb.checked = headerCb.checked;
        var id = cb.dataset.id;
        if (headerCb.checked) {
          selectedIds.add(id);
          cb.closest('tr').classList.add('table-active');
        } else {
          selectedIds.delete(id);
          cb.closest('tr').classList.remove('table-active');
        }
      });
      updateBulkToolbar();
    });
  }

  document.getElementById('bulk-clear').addEventListener('click', function () {
    selectedIds.clear();
    document.querySelectorAll('.cb-row').forEach(function (cb) {
      cb.checked = false;
      cb.closest('tr').classList.remove('table-active');
    });
    if (headerCb) { headerCb.checked = false; headerCb.indeterminate = false; }
    updateBulkToolbar();
  });

  document.getElementById('bulk-form').addEventListener('submit', function (e) {
    if (selectedIds.size === 0) { e.preventDefault(); return; }
    this.querySelectorAll('input[name="ids[]"]').forEach(function (el) { el.remove(); });
    var form = this;
    selectedIds.forEach(function (id) {
      var input   = document.createElement('input');
      input.type  = 'hidden';
      input.name  = 'ids[]';
      input.value = id;
      form.appendChild(input);
    });
  });

  var bulkFormWs = document.getElementById('bulk-form-ws');
  if (bulkFormWs) {
    bulkFormWs.addEventListener('submit', function (e) {
      if (selectedIds.size === 0) { e.preventDefault(); return; }
      this.querySelectorAll('input[name="ids[]"]').forEach(function (el) { el.remove(); });
      var form = this;
      selectedIds.forEach(function (id) {
        var input   = document.createElement('input');
        input.type  = 'hidden';
        input.name  = 'ids[]';
        input.value = id;
        form.appendChild(input);
      });
    });
  }

  // Row click opens the quick edit modal (checkbox td blocks propagation)
  window.bulkRowClick = function (event, row) {
    metOpenModal(row);
  };
  // ─────────────────────────────────────────────────────────────────────────

  // ── Table sorting ────────────────────────────────────────────────────────
  var sortState = { key: null, dir: 1 };

  function sortTable(key, type) {
    var table = document.querySelector('#view-table table');
    if (!table) return;
    var tbody = table.querySelector('tbody');
    var rows  = Array.from(tbody.querySelectorAll('tr'));

    // Toggle direction when clicking the same column, otherwise default asc
    if (sortState.key === key) {
      sortState.dir = sortState.dir === 1 ? -1 : 1;
    } else {
      sortState.key = key;
      sortState.dir = 1;
    }

    rows.sort(function (a, b) {
      var aVal = (a.dataset['sort' + key.charAt(0).toUpperCase() + key.slice(1)] || '').trim();
      var bVal = (b.dataset['sort' + key.charAt(0).toUpperCase() + key.slice(1)] || '').trim();

      var cmp;
      if (type === 'number') {
        cmp = parseFloat(aVal) - parseFloat(bVal);
      } else {
        cmp = aVal.localeCompare(bVal, 'pt-BR', { sensitivity: 'base' });
      }
      return cmp * sortState.dir;
    });

    rows.forEach(function (r) { tbody.appendChild(r); });

    // Update icons
    document.querySelectorAll('.th-sortable .sort-icon').forEach(function (icon) {
      icon.className = 'fas fa-sort sort-icon text-muted ml-1';
      icon.style.fontSize = '.75em';
    });
    var activeIcon = document.querySelector('.th-sortable[data-sort-key="' + key + '"] .sort-icon');
    if (activeIcon) {
      activeIcon.className = 'fas ' + (sortState.dir === 1 ? 'fa-sort-up' : 'fa-sort-down') + ' sort-icon text-primary ml-1';
    }
  }

  document.querySelectorAll('.th-sortable').forEach(function (th) {
    th.addEventListener('click', function () {
      sortTable(th.dataset.sortKey, th.dataset.sortType);
    });
  });
  // ─────────────────────────────────────────────────────────────────────────

  // ── View mode toggle ─────────────────────────────────────────────────────
  var STORAGE_KEY = 'transactions_view_mode';
  var mode = localStorage.getItem(STORAGE_KEY) || 'table';

  function applyMode(m) {
    if (m === 'cards') {
      document.getElementById('view-table').style.display = 'none';
      document.getElementById('view-cards').style.display = '';
      document.getElementById('btn-view-table').classList.remove('active');
      document.getElementById('btn-view-cards').classList.add('active');
    } else {
      document.getElementById('view-table').style.display = '';
      document.getElementById('view-cards').style.display = 'none';
      document.getElementById('btn-view-table').classList.add('active');
      document.getElementById('btn-view-cards').classList.remove('active');
    }
    localStorage.setItem(STORAGE_KEY, m);
  }

  document.getElementById('btn-view-table').addEventListener('click', function () { applyMode('table'); });
  document.getElementById('btn-view-cards').addEventListener('click', function () { applyMode('cards'); });

  applyMode(mode);
  // ─────────────────────────────────────────────────────────────────────────

  // ── Export ────────────────────────────────────────────────────────────────
  var exportFmt = 'csv';

  var EXPORT_KEYS    = ['id', 'data', 'descricao', 'descricao_banco', 'chave_banco', 'tipo', 'categoria', 'cartao', 'pessoa', 'valor', 'data_pagamento', 'data_recebimento', 'workspace'];
  var EXPORT_HEADERS = ['ID', 'Data', 'Descrição', 'Desc. Banco', 'Chave Banco', 'Tipo', 'Categoria', 'Cartão', 'Pessoa', 'Valor', 'Pgto.', 'Recebimento', 'Workspace'];

  function getTransactionData() {
    var rows = document.querySelectorAll('#view-table tbody tr');
    var data = [];
    rows.forEach(function (row) {
      var d = row.dataset;
      data.push({
        id:               d.id                || '',
        data:             d.data              || '',
        descricao:        d.sortDescricao     || d.descricao || '',
        descricao_banco:  d.descricaoBanco    || '',
        chave_banco:      d.chaveBanco        || '',
        tipo:             d.tipo              || '',
        categoria:        d.sortCategoria     || '',
        cartao:           d.sortCartao        || '',
        pessoa:           d.sortPessoa        || '',
        valor:            d.valor             || '',
        data_pagamento:   d.dataPagamento     || '',
        data_recebimento: d.dataRecebimento   || '',
        workspace:        d.sortWorkspace     || '',
      });
    });
    return data;
  }

  function downloadFile(filename, content, mimeType) {
    var blob = new Blob([content], { type: mimeType });
    var url  = URL.createObjectURL(blob);
    var a    = document.createElement('a');
    a.href     = url;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
  }

  function buildExportContent(fmt) {
    var data = getTransactionData();
    if (fmt === 'json') {
      return { content: JSON.stringify(data, null, 2), filename: 'lancamentos.json', mimeType: 'application/json' };
    }
    if (fmt === 'csv') {
      var lines = [EXPORT_HEADERS.map(function (h) { return '"' + h + '"'; }).join(';')];
      data.forEach(function (row) {
        lines.push(EXPORT_KEYS.map(function (k) { return '"' + String(row[k]).replace(/"/g, '""') + '"'; }).join(';'));
      });
      return { content: '\uFEFF' + lines.join('\r\n'), filename: 'lancamentos.csv', mimeType: 'text/csv;charset=utf-8' };
    }
    // markdown
    var sep   = EXPORT_HEADERS.map(function () { return '---'; });
    var lines = [
      '| ' + EXPORT_HEADERS.join(' | ') + ' |',
      '| ' + sep.join(' | ') + ' |',
    ];
    data.forEach(function (row) {
      lines.push('| ' + EXPORT_KEYS.map(function (k) { return String(row[k]).replace(/\|/g, '\\|'); }).join(' | ') + ' |');
    });
    return { content: lines.join('\n'), filename: 'lancamentos.md', mimeType: 'text/markdown;charset=utf-8' };
  }

  function showCopyFeedback(el) {
    el.style.display = '';
    setTimeout(function () { el.style.display = 'none'; }, 2000);
  }

  function formatMoneyBRL(v) {
    var n     = parseFloat(v) || 0;
    var sign  = n < 0 ? '-' : '';
    var fixed = Math.abs(n).toFixed(2);
    var parts = fixed.split('.');
    parts[0]  = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    return sign + 'R$ ' + parts[0] + ',' + parts[1];
  }

  var EXPORT_TYPE_COLORS = {
    lucro:                 '#28a745',
    despesa:                '#dc3545',
    transferencia:          '#6c757d',
    emprestimo:             '#e0a800',
    pagamento_emprestimo:   '#28a745',
  };

  function buildExportImageBlob(callback) {
    var data      = getTransactionData();
    var canvas    = document.getElementById('export-image-canvas');
    var ctx       = canvas.getContext('2d');
    var scale     = 2;
    var width     = 640;
    var padding   = 24;
    var rowHeight = 34;
    var headerHeight = 96;
    var footerHeight = 90;
    var rowsCount = data.length || 1;
    var height    = headerHeight + rowsCount * rowHeight + footerHeight;

    canvas.width  = width * scale;
    canvas.height = height * scale;
    canvas.style.width  = width + 'px';
    canvas.style.height = height + 'px';
    ctx.setTransform(scale, 0, 0, scale, 0, 0);

    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, width, height);

    var monthLabelEl = document.getElementById('current-month-label');
    var monthLabel   = monthLabelEl ? monthLabelEl.textContent.trim().replace(/\s+/g, ' ') : '';

    ctx.fillStyle = '#212529';
    ctx.font      = 'bold 20px Arial, sans-serif';
    ctx.fillText('Lançamentos' + (monthLabel ? ' — ' + monthLabel : ''), padding, 34);

    ctx.fillStyle = '#6c757d';
    ctx.font      = '13px Arial, sans-serif';
    ctx.fillText(data.length + ' lançamento(s)', padding, 54);

    ctx.strokeStyle = '#dee2e6';
    ctx.lineWidth   = 1;
    ctx.beginPath();
    ctx.moveTo(padding, 66);
    ctx.lineTo(width - padding, 66);
    ctx.stroke();

    function truncateText(text, maxWidth) {
      if (ctx.measureText(text).width <= maxWidth) { return text; }
      var t = text;
      while (t.length > 0 && ctx.measureText(t + '…').width > maxWidth) {
        t = t.slice(0, -1);
      }
      return t + '…';
    }

    var y     = headerHeight - rowHeight + 24;
    var total = 0;

    if (data.length === 0) {
      ctx.fillStyle = '#6c757d';
      ctx.font      = '14px Arial, sans-serif';
      ctx.fillText('Nenhum lançamento encontrado.', padding, y);
      y += rowHeight;
    } else {
      data.forEach(function (row, idx) {
        var val = parseFloat(row.valor) || 0;
        total += val;

        if (idx % 2 === 1) {
          ctx.fillStyle = '#f8f9fa';
          ctx.fillRect(padding - 8, y - 22, width - (padding - 8) * 2, rowHeight);
        }

        var dateParts = String(row.data || '').split('-');
        var dateLabel = dateParts.length === 3 ? dateParts[2] + '/' + dateParts[1] : '';
        ctx.fillStyle = '#adb5bd';
        ctx.font      = '12px Arial, sans-serif';
        ctx.fillText(dateLabel, padding, y);

        var valueText  = formatMoneyBRL(val);
        ctx.font       = 'bold 13px Arial, sans-serif';
        var valueWidth = ctx.measureText(valueText).width;
        var descX      = padding + 46;
        var descMaxWidth = width - padding - descX - valueWidth - 16;

        ctx.font      = '14px Arial, sans-serif';
        ctx.fillStyle = '#212529';
        var desc = truncateText(row.descricao || '(Sem descrição)', descMaxWidth);
        ctx.fillText(desc, descX, y);

        ctx.font      = 'bold 13px Arial, sans-serif';
        ctx.fillStyle = EXPORT_TYPE_COLORS[row.tipo] || '#212529';
        ctx.textAlign = 'right';
        ctx.fillText(valueText, width - padding, y);
        ctx.textAlign = 'left';

        y += rowHeight;
      });
    }

    y += 14;
    ctx.strokeStyle = '#212529';
    ctx.lineWidth   = 1.5;
    ctx.beginPath();
    ctx.moveTo(padding, y);
    ctx.lineTo(width - padding, y);
    ctx.stroke();

    y += 26;
    ctx.fillStyle = '#212529';
    ctx.font      = 'bold 16px Arial, sans-serif';
    ctx.fillText('Total', padding, y);

    ctx.font      = 'bold 16px Arial, sans-serif';
    ctx.fillStyle = total < 0 ? '#dc3545' : '#28a745';
    ctx.textAlign = 'right';
    ctx.fillText(formatMoneyBRL(total), width - padding, y);
    ctx.textAlign = 'left';

    y += 24;
    ctx.fillStyle = '#adb5bd';
    ctx.font      = '11px Arial, sans-serif';
    var now = new Date();
    ctx.fillText(
      'Gerado em ' + now.toLocaleDateString('pt-BR') + ' às ' + now.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' }),
      padding,
      y
    );

    canvas.toBlob(function (blob) { callback(blob); }, 'image/png');
  }

  function fallbackCopy(text, feedback) {
    var ta = document.createElement('textarea');
    ta.value = text;
    ta.style.cssText = 'position:fixed;top:-9999px;left:-9999px;opacity:0';
    document.body.appendChild(ta);
    ta.focus();
    ta.select();
    try { document.execCommand('copy'); if (feedback) { showCopyFeedback(feedback); } } catch (e) {}
    document.body.removeChild(ta);
  }

  document.querySelectorAll('#export-fmt-toggle [data-fmt]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      exportFmt = this.dataset.fmt;
      document.querySelectorAll('#export-fmt-toggle [data-fmt]').forEach(function (b) { b.classList.remove('active'); });
      this.classList.add('active');
      document.getElementById('export-copy-feedback').style.display = 'none';
    });
  });

  document.getElementById('export-do-download').addEventListener('click', function () {
    if (exportFmt === 'image') {
      buildExportImageBlob(function (blob) {
        var url = URL.createObjectURL(blob);
        var a   = document.createElement('a');
        a.href     = url;
        a.download = 'lancamentos.png';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
        $('#modal-export').modal('hide');
      });
      return;
    }
    var result = buildExportContent(exportFmt);
    downloadFile(result.filename, result.content, result.mimeType);
    $('#modal-export').modal('hide');
  });

  document.getElementById('export-do-copy').addEventListener('click', function () {
    var feedback = document.getElementById('export-copy-feedback');

    if (exportFmt === 'image') {
      buildExportImageBlob(function (blob) {
        if (navigator.clipboard && window.ClipboardItem) {
          navigator.clipboard.write([new window.ClipboardItem({ 'image/png': blob })])
            .then(function () { showCopyFeedback(feedback); })
            .catch(function () {
              var url = URL.createObjectURL(blob);
              var a   = document.createElement('a');
              a.href     = url;
              a.download = 'lancamentos.png';
              document.body.appendChild(a);
              a.click();
              document.body.removeChild(a);
              URL.revokeObjectURL(url);
            });
        } else {
          var url = URL.createObjectURL(blob);
          var a   = document.createElement('a');
          a.href     = url;
          a.download = 'lancamentos.png';
          document.body.appendChild(a);
          a.click();
          document.body.removeChild(a);
          URL.revokeObjectURL(url);
        }
      });
      return;
    }

    var result = buildExportContent(exportFmt);
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(result.content)
        .then(function () { showCopyFeedback(feedback); })
        .catch(function () { fallbackCopy(result.content, feedback); });
    } else {
      fallbackCopy(result.content, feedback);
    }
  });
  // ─────────────────────────────────────────────────────────────────────────
})();
</script>
@endsection
