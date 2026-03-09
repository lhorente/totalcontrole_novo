@extends('layouts.dashboard')

@section('content')

@php
  $tipoLabels = ['despesa'=>'Despesa','receita'=>'Receita','transferencia'=>'Transferência','emprestimo'=>'Empréstimo'];
@endphp

<div class="content-header">
  <div class="container">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 text-dark">Editar Lançamento #{{ $transaction->id }}</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
          <li class="breadcrumb-item"><a href="{{ route('transactions.month') }}">Lançamentos</a></li>
          <li class="breadcrumb-item"><a href="{{ route('transactions.view', $transaction->id) }}">#{{ $transaction->id }}</a></li>
          <li class="breadcrumb-item active">Editar</li>
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

    @if ($errors->any())
    <div class="alert alert-danger alert-dismissible">
      <button type="button" class="close" data-dismiss="alert">&times;</button>
      <ul class="mb-0">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('transactions.update', $transaction->id) }}">
      @csrf

      <div class="row">

        {{-- Main form card --}}
        <div class="col-md-8">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title"><i class="fa fa-edit mr-1"></i> Dados do lançamento</h3>
            </div>

            <div class="card-body">

              {{-- Descrição --}}
              <div class="form-group">
                <label for="descricao">Descrição</label>
                <input type="text" id="descricao" name="descricao" class="form-control @error('descricao') is-invalid @enderror"
                       value="{{ old('descricao', $transaction->descricao) }}" placeholder="Descrição do lançamento" />
                @error('descricao')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>

              {{-- Valor + Tipo --}}
              <div class="form-row">
                <div class="form-group col-md-6">
                  <label for="valor">Valor (R$)</label>
                  <input type="number" id="valor" name="valor" class="form-control @error('valor') is-invalid @enderror"
                         step="0.01" min="0"
                         value="{{ old('valor', $transaction->valor) }}" />
                  @error('valor')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group col-md-6">
                  <label for="tipo">Tipo</label>
                  <select id="tipo" name="tipo" class="form-control @error('tipo') is-invalid @enderror">
                    @foreach ($tipoLabels as $val => $label)
                        <option value="{{ $val }}" {{ old('tipo', $transaction->tipo) == $val ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                  </select>
                  @error('tipo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
              </div>

              {{-- Data + Data pagamento --}}
              <div class="form-row">
                <div class="form-group col-md-6">
                  <label for="data">Data</label>
                  <input type="date" id="data" name="data" class="form-control @error('data') is-invalid @enderror"
                         value="{{ old('data', $transaction->data->format('Y-m-d')) }}" />
                  @error('data')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group col-md-6">
                  <label for="data_pagamento">Data de pagamento</label>
                  <input type="date" id="data_pagamento" name="data_pagamento"
                         class="form-control @error('data_pagamento') is-invalid @enderror"
                         value="{{ old('data_pagamento', $transaction->data_pagamento ? \Carbon\Carbon::parse($transaction->data_pagamento)->format('Y-m-d') : '') }}" />
                  <small class="text-muted">Deixe em branco se ainda não foi pago.</small>
                  @error('data_pagamento')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
              </div>

              {{-- Data de recebimento (apenas empréstimo) --}}
              <div class="form-group" id="field-data-recebimento" style="{{ old('tipo', $transaction->tipo) !== 'emprestimo' ? 'display:none' : '' }}">
                <label for="data_recebimento">Data de recebimento</label>
                <input type="date" id="data_recebimento" name="data_recebimento"
                       class="form-control @error('data_recebimento') is-invalid @enderror"
                       value="{{ old('data_recebimento', $transaction->data_recebimento ? \Carbon\Carbon::parse($transaction->data_recebimento)->format('Y-m-d') : '') }}" />
                <small class="text-muted">Data em que o valor foi devolvido. Deixe em branco se ainda está pendente.</small>
                @error('data_recebimento')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>

              {{-- Categoria --}}
              <div class="form-group">
                <label for="id_categoria">Categoria</label>
                <select id="id_categoria" name="id_categoria" class="form-control @error('id_categoria') is-invalid @enderror">
                  <option value="">Sem categoria</option>
                  @foreach ($categorias as $cat)
                    <option value="{{ $cat->id }}" {{ old('id_categoria', $transaction->id_categoria) == $cat->id ? 'selected' : '' }}>
                        {{ $cat->nome }}
                    </option>
                  @endforeach
                </select>
                @error('id_categoria')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>

              {{-- Carteira --}}
              <div class="form-group">
                <label for="id_caixa">Carteira</label>
                <select id="id_caixa" name="id_caixa" class="form-control @error('id_caixa') is-invalid @enderror">
                  <option value="">Nenhuma</option>
                  @foreach ($caixas as $cx)
                    <option value="{{ $cx->id }}" {{ old('id_caixa', $transaction->id_caixa ?? null) == $cx->id ? 'selected' : '' }}>
                        {{ $cx->titulo }}
                    </option>
                  @endforeach
                </select>
                @error('id_caixa')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>

              {{-- Cartão --}}
              <div class="form-group">
                <label for="id_cartao">Cartão de crédito</label>
                <select id="id_cartao" name="id_cartao" class="form-control @error('id_cartao') is-invalid @enderror">
                  <option value="">Nenhum</option>
                  @foreach ($cartoes as $c)
                    <option value="{{ $c->id }}" {{ old('id_cartao', $transaction->id_cartao ?? null) == $c->id ? 'selected' : '' }}>
                        {{ $c->descricao }}
                    </option>
                  @endforeach
                </select>
                @error('id_cartao')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>

              {{-- Pessoa --}}
              <div class="form-group">
                <label for="id_cliente">Pessoa</label>
                <select id="id_cliente" name="id_cliente" class="form-control @error('id_cliente') is-invalid @enderror">
                  <option value="">Nenhuma</option>
                  @foreach ($pessoas as $p)
                    <option value="{{ $p->id }}" {{ old('id_cliente', $transaction->id_cliente ?? null) == $p->id ? 'selected' : '' }}>
                        {{ $p->nome }}
                    </option>
                  @endforeach
                </select>
                @error('id_cliente')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>

            </div>{{-- /.card-body --}}

            <div class="card-footer d-flex justify-content-between">
              <a href="{{ route('transactions.view', $transaction->id) }}" class="btn btn-outline-secondary">
                <i class="fa fa-times"></i> Cancelar
              </a>
              <button type="submit" class="btn btn-primary">
                <i class="fa fa-save"></i> Salvar alterações
              </button>
            </div>

          </div>
        </div>{{-- /.col-md-8 --}}

        {{-- Sidebar info --}}
        <div class="col-md-4">
          <div class="card">
            <div class="card-header"><h3 class="card-title">Informações</h3></div>
            <div class="card-body">
              <dl class="mb-0">
                <dt>ID</dt>
                <dd class="text-muted">#{{ $transaction->id }}</dd>

                @if ($transaction->descricao_banco)
                <dt>Descrição banco</dt>
                <dd class="text-muted small">{{ $transaction->descricao_banco }}</dd>
                @endif

                @if ($transaction->chave_banco)
                <dt>Chave banco</dt>
                <dd class="text-muted" style="word-break:break-all;font-size:.78rem">{{ $transaction->chave_banco }}</dd>
                @endif

                <dt>Criado em</dt>
                <dd class="text-muted">{{ \Carbon\Carbon::parse($transaction->created_at)->format('d/m/Y') }}</dd>

                @if ($transaction->updated_at && $transaction->updated_at != $transaction->created_at)
                <dt>Atualizado em</dt>
                <dd class="text-muted">{{ $transaction->updated_at->format('d/m/Y H:i') }}</dd>
                @endif
              </dl>
            </div>
          </div>
        </div>

      </div>{{-- /.row --}}
    </form>

  </div>{{-- /.container --}}
</div>{{-- /.content --}}

<script>
(function () {
  var tipoSelect = document.getElementById('tipo');
  var fieldRecebimento = document.getElementById('field-data-recebimento');

  function toggleRecebimento() {
    fieldRecebimento.style.display = tipoSelect.value === 'emprestimo' ? '' : 'none';
  }

  tipoSelect.addEventListener('change', toggleRecebimento);
})();
</script>
@endsection
