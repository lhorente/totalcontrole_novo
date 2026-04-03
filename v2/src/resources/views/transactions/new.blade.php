@extends('layouts.dashboard')

@section('content')

@php
  $tipoLabels = ['despesa'=>'Despesa','lucro'=>'Receita','transferencia'=>'Transferência','investimento'=>'Investimento','emprestimo'=>'Empréstimo','pagamento_emprestimo'=>'Pgto. Empréstimo'];
@endphp

<div class="content-header">
  <div class="container">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 text-dark">Novo Lançamento</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
          <li class="breadcrumb-item"><a href="{{ route('transactions.month') }}">Lançamentos</a></li>
          <li class="breadcrumb-item active">Novo</li>
        </ol>
      </div>
    </div>
  </div>
</div>

<div class="content">
  <div class="container">

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

    <form method="POST" action="{{ route('transactions.store') }}">
      @csrf
      <input type="hidden" name="_back" value="{{ request('_back') }}">

      <div class="row">

        {{-- Main form card --}}
        <div class="col-md-8">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title"><i class="fa fa-plus mr-1"></i> Dados do lançamento</h3>
            </div>

            <div class="card-body">

              {{-- Descrição --}}
              <div class="form-group">
                <label for="descricao">Descrição</label>
                <input type="text" id="descricao" name="descricao"
                       class="form-control @error('descricao') is-invalid @enderror"
                       value="{{ old('descricao') }}" placeholder="Descrição do lançamento" />
                @error('descricao')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>

              {{-- Valor + Tipo --}}
              <div class="form-row">
                <div class="form-group col-md-6">
                  <label for="valor">Valor (R$)</label>
                  <input type="number" id="valor" name="valor"
                         class="form-control @error('valor') is-invalid @enderror"
                         step="0.01" min="0" value="{{ old('valor') }}" />
                  @error('valor')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group col-md-6">
                  <label for="tipo">Tipo</label>
                  <select id="tipo" name="tipo" class="form-control @error('tipo') is-invalid @enderror">
                    @foreach ($tipoLabels as $val => $label)
                      <option value="{{ $val }}" @selected(old('tipo', $defaults['tipo']) === $val)>{{ $label }}</option>
                    @endforeach
                  </select>
                  @error('tipo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
              </div>

              {{-- Data + Data pagamento --}}
              <div class="form-row">
                <div class="form-group col-md-6">
                  <label for="data">Data</label>
                  <input type="date" id="data" name="data"
                         class="form-control @error('data') is-invalid @enderror"
                         value="{{ old('data', $defaults['data']) }}" />
                  @error('data')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group col-md-6">
                  <label for="data_pagamento">Data de pagamento</label>
                  <input type="date" id="data_pagamento" name="data_pagamento"
                         class="form-control @error('data_pagamento') is-invalid @enderror"
                         value="{{ old('data_pagamento') }}" />
                  <small class="text-muted">Deixe em branco se ainda não foi pago.</small>
                  @error('data_pagamento')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
              </div>

              {{-- Categoria --}}
              <div class="form-group">
                <label for="id_categoria">Categoria</label>
                <select id="id_categoria" name="id_categoria"
                        class="form-control @error('id_categoria') is-invalid @enderror">
                  <option value="">Sem categoria</option>
                  @foreach ($categorias as $cat)
                    <option value="{{ $cat->id }}">
                        {{ $cat->nome }}
                    </option>
                  @endforeach
                </select>
                @error('id_categoria')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>

              {{-- Carteira --}}
              <div class="form-group">
                <label for="id_caixa">Carteira</label>
                <select id="id_caixa" name="id_caixa"
                        class="form-control @error('id_caixa') is-invalid @enderror">
                  <option value="">Nenhuma</option>
                  @foreach ($caixas as $cx)
                    <option value="{{ $cx->id }}">
                        {{ $cx->titulo }}
                    </option>
                  @endforeach
                </select>
                @error('id_caixa')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>

              {{-- Cartão --}}
              <div class="form-group">
                <label for="id_cartao">Cartão de crédito</label>
                <select id="id_cartao" name="id_cartao"
                        class="form-control @error('id_cartao') is-invalid @enderror">
                  <option value="">Nenhum</option>
                  @foreach ($cartoes as $c)
                    <option value="{{ $c->id }}">
                        {{ $c->descricao }}
                    </option>
                  @endforeach
                </select>
                @error('id_cartao')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>

              {{-- Pessoa --}}
              <div class="form-group">
                <label for="id_cliente">Pessoa</label>
                <select id="id_cliente" name="id_cliente"
                        class="form-control @error('id_cliente') is-invalid @enderror">
                  <option value="">Nenhuma</option>
                  @foreach ($pessoas as $p)
                    <option value="{{ $p->id }}">
                        {{ $p->nome }}
                    </option>
                  @endforeach
                </select>
                @error('id_cliente')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>

            </div>{{-- /.card-body --}}

            <div class="card-footer d-flex justify-content-between">
              <a href="{{ request('_back') ?: route('transactions.month') }}" class="btn btn-outline-secondary">
                <i class="fa fa-times"></i> Cancelar
              </a>
              <button type="submit" class="btn btn-primary">
                <i class="fa fa-save"></i> Salvar lançamento
              </button>
            </div>

          </div>
        </div>{{-- /.col-md-8 --}}

        {{-- Sidebar tips --}}
        <div class="col-md-4">
          <div class="card">
            <div class="card-header"><h3 class="card-title">Dicas</h3></div>
            <div class="card-body">
              <ul class="pl-3 mb-0 small text-muted">
                <li>Preencha a <strong>data de pagamento</strong> apenas se a transação já foi liquidada.</li>
                <li>Selecione <strong>Carteira</strong> ou <strong>Cartão</strong>, não ambos.</li>
                <li>A <strong>Pessoa</strong> é útil para rastrear empréstimos e recebimentos.</li>
              </ul>
            </div>
          </div>
        </div>

      </div>{{-- /.row --}}
    </form>

  </div>{{-- /.container --}}
</div>{{-- /.content --}}
@endsection
