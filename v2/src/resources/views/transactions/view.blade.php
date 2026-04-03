@extends('layouts.dashboard')

@section('content')

@php
  $tipoLabels = ['despesa'=>'Despesa','receita'=>'Receita','transferencia'=>'Transferência','investimento'=>'Investimento','emprestimo'=>'Empréstimo','pagamento_emprestimo'=>'Pgto. Empréstimo'];
  $tipoBadge  = ['despesa'=>'danger','receita'=>'success','transferencia'=>'secondary','investimento'=>'info','emprestimo'=>'warning','pagamento_emprestimo'=>'primary'];
  $isPago = (bool) $transaction->data_pagamento;
@endphp

<div class="content-header">
  <div class="container">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 text-dark">Lançamento #{{ $transaction->id }}</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
          <li class="breadcrumb-item"><a href="{{ route('transactions.month') }}">Lançamentos</a></li>
          <li class="breadcrumb-item active">#{{ $transaction->id }}</li>
        </ol>
      </div>
    </div>
  </div>
</div>

<div class="content">
  <div class="container">

    @if (session('success'))
    <div class="alert alert-success alert-dismissible">
      <button type="button" class="close" data-dismiss="alert">&times;</button>
      {{ session('success') }}
    </div>
    @endif

    <div class="row">

      {{-- Main detail card --}}
      <div class="col-md-8">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">
              @if ($transaction->category)
                <i class="{{ $transaction->category->icon_class }} mr-1"></i>
              @endif
              {{ $transaction->descricao ?: $transaction->descricao_banco ?: '(sem descrição)' }}
            </h3>
            <div class="card-tools">
              <span class="badge badge-{{ $tipoBadge[$transaction->tipo] ?? 'light' }} badge-pill" style="font-size:.9rem">
                {{ $tipoLabels[$transaction->tipo] ?? ucfirst($transaction->tipo) }}
              </span>
            </div>
          </div>

          <div class="card-body">
            <dl class="row mb-0">

              <dt class="col-sm-4">Descrição</dt>
              <dd class="col-sm-8">{{ $transaction->descricao ?: '—' }}</dd>

              @if ($transaction->descricao_banco && $transaction->descricao_banco !== $transaction->descricao)
              <dt class="col-sm-4">Descrição banco</dt>
              <dd class="col-sm-8 text-muted">{{ $transaction->descricao_banco }}</dd>
              @endif

              <dt class="col-sm-4">Valor</dt>
              <dd class="col-sm-8">
                <strong class="text-{{ $transaction->tipo === 'receita' ? 'success' : ($transaction->tipo === 'despesa' ? 'danger' : 'dark') }}" style="font-size:1.15rem">
                  R$ {{ number_format($transaction->valor, 2, ',', '.') }}
                </strong>
              </dd>

              <dt class="col-sm-4">Data</dt>
              <dd class="col-sm-8">{{ $transaction->data->format('d/m/Y') }}</dd>

              <dt class="col-sm-4">Pagamento</dt>
              <dd class="col-sm-8">
                @if ($isPago)
                  <span class="text-success">
                    <i class="fa fa-check-circle"></i>
                    Pago em {{ \Carbon\Carbon::parse($transaction->data_pagamento)->format('d/m/Y') }}
                  </span>
                  <form method="POST" action="{{ route('transactions.quickUpdate', $transaction->id) }}" class="d-inline ml-2">
                    @csrf
                    <input type="hidden" name="field" value="data_pagamento">
                    <input type="hidden" name="value" value="">
                    <button type="submit" class="btn btn-xs btn-outline-secondary" title="Limpar data de pagamento"
                            onclick="return confirm('Remover data de pagamento?')">
                      <i class="fa fa-times fa-xs"></i>
                    </button>
                  </form>
                @else
                  <span class="text-danger"><i class="fa fa-clock"></i> Pendente</span>
                  <form method="POST" action="{{ route('transactions.quickUpdate', $transaction->id) }}" class="d-inline ml-2" id="form-pagamento">
                    @csrf
                    <input type="hidden" name="field" value="data_pagamento">
                    <div class="input-group input-group-sm d-inline-flex" style="width:auto;vertical-align:middle">
                      <input type="date" name="value" class="form-control form-control-sm"
                             value="{{ date('Y-m-d') }}" style="width:140px">
                      <div class="input-group-append">
                        <button type="submit" class="btn btn-sm btn-success">
                          <i class="fa fa-check fa-xs"></i> Confirmar
                        </button>
                      </div>
                    </div>
                  </form>
                @endif
              </dd>

              @if ($transaction->tipo === 'emprestimo')
              <dt class="col-sm-4">Recebimento</dt>
              <dd class="col-sm-8">
                @if ($transaction->data_recebimento)
                  <span class="text-success">
                    <i class="fa fa-check-circle"></i>
                    {{ \Carbon\Carbon::parse($transaction->data_recebimento)->format('d/m/Y') }}
                  </span>
                  <form method="POST" action="{{ route('transactions.quickUpdate', $transaction->id) }}" class="d-inline ml-2">
                    @csrf
                    <input type="hidden" name="field" value="data_recebimento">
                    <input type="hidden" name="value" value="">
                    <button type="submit" class="btn btn-xs btn-outline-secondary" title="Limpar data de recebimento"
                            onclick="return confirm('Remover data de recebimento?')">
                      <i class="fa fa-times fa-xs"></i>
                    </button>
                  </form>
                @else
                  <span class="text-warning"><i class="fa fa-clock"></i> Pendente</span>
                  <form method="POST" action="{{ route('transactions.quickUpdate', $transaction->id) }}" class="d-inline ml-2" id="form-recebimento">
                    @csrf
                    <input type="hidden" name="field" value="data_recebimento">
                    <div class="input-group input-group-sm d-inline-flex" style="width:auto;vertical-align:middle">
                      <input type="date" name="value" class="form-control form-control-sm"
                             value="{{ date('Y-m-d') }}" style="width:140px">
                      <div class="input-group-append">
                        <button type="submit" class="btn btn-sm btn-success">
                          <i class="fa fa-check fa-xs"></i> Confirmar
                        </button>
                      </div>
                    </div>
                  </form>
                @endif
              </dd>
              @endif

              <dt class="col-sm-4">Categoria</dt>
              <dd class="col-sm-8">
                @if ($transaction->category)
                  <i class="{{ $transaction->category->icon_class }} text-muted mr-1"></i>
                  {{ $transaction->category->nome }}
                @else
                  <span class="text-muted">—</span>
                @endif
              </dd>

              <dt class="col-sm-4">Carteira</dt>
              <dd class="col-sm-8">
                @if ($transaction->wallet)
                  <i class="fa fa-wallet text-muted mr-1"></i> {{ $transaction->wallet->titulo }}
                @else
                  <span class="text-muted">—</span>
                @endif
              </dd>

              <dt class="col-sm-4">Cartão</dt>
              <dd class="col-sm-8">
                @if ($transaction->credit_card)
                  <i class="fa fa-credit-card text-muted mr-1"></i> {{ $transaction->credit_card->descricao }}
                @else
                  <span class="text-muted">—</span>
                @endif
              </dd>

              <dt class="col-sm-4">Pessoa</dt>
              <dd class="col-sm-8">
                @if ($transaction->contact)
                  <i class="fa fa-user text-muted mr-1"></i> {{ $transaction->contact->nome }}
                @else
                  <span class="text-muted">—</span>
                @endif
              </dd>

              @if ($transaction->chave_banco)
              <dt class="col-sm-4">Chave banco</dt>
              <dd class="col-sm-8"><small class="text-muted">{{ $transaction->chave_banco }}</small></dd>
              @endif

            </dl>
          </div>

          <div class="card-footer d-flex justify-content-between align-items-center">
            <div>
              <small class="text-muted">
                Criado em {{ \Carbon\Carbon::parse($transaction->created_at)->format('d/m/Y') }}
                @if ($transaction->updated_at && $transaction->updated_at != $transaction->created_at)
                  &mdash; Atualizado em {{ $transaction->updated_at->format('d/m/Y H:i') }}
                @endif
              </small>
            </div>
            <div>
              <a href="{{ route('transactions.month', [$transaction->data->format('Y'), (int)$transaction->data->format('m')]) }}"
                 class="btn btn-sm btn-outline-secondary">
                <i class="fa fa-arrow-left"></i> Voltar
              </a>
              <a href="{{ route('transactions.edit', $transaction->id) }}" class="btn btn-sm btn-primary ml-1">
                <i class="fa fa-edit"></i> Editar
              </a>
              <button type="button" class="btn btn-sm btn-danger ml-1" onclick="confirmDelete()">
                <i class="fa fa-trash"></i> Excluir
              </button>
            </div>
          </div>

          <form id="form-delete" method="POST" action="{{ route('transactions.destroy', $transaction->id) }}" style="display:none">
            @csrf
            @method('DELETE')
          </form>
        </div>
      </div>

      {{-- Sidebar status --}}
      <div class="col-md-4">
        <div class="card card-{{ $isPago ? 'success' : 'warning' }}">
          <div class="card-header">
            <h3 class="card-title">Status</h3>
          </div>
          <div class="card-body text-center">
            @if ($isPago)
              <i class="fas fa-check-circle fa-3x text-success mb-2"></i>
              <p class="mb-0 font-weight-bold">Pago</p>
              <small class="text-muted">{{ \Carbon\Carbon::parse($transaction->data_pagamento)->format('d/m/Y') }}</small>
            @else
              <i class="fas fa-clock fa-3x text-warning mb-2"></i>
              <p class="mb-0 font-weight-bold">Pendente</p>
            @endif
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Valor</h3>
          </div>
          <div class="card-body text-center">
            <h2 class="text-{{ $transaction->tipo === 'receita' ? 'success' : ($transaction->tipo === 'despesa' ? 'danger' : 'dark') }}">
              R$ {{ number_format($transaction->valor, 2, ',', '.') }}
            </h2>
            <span class="badge badge-{{ $tipoBadge[$transaction->tipo] ?? 'light' }}">
              {{ $tipoLabels[$transaction->tipo] ?? ucfirst($transaction->tipo) }}
            </span>
          </div>
        </div>
      </div>

    </div>{{-- /.row --}}
  </div>{{-- /.container --}}
</div>{{-- /.content --}}

<script>
function confirmDelete() {
  if (confirm('Tem certeza que deseja excluir o lançamento #{{ $transaction->id }}? Esta ação não pode ser desfeita.')) {
    document.getElementById('form-delete').submit();
  }
}
</script>
@endsection
