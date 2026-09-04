@extends('layouts.dashboard')

@section('content')
<div class="content-header">
  <div class="container">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 text-dark"> Editar Cartão de crédito</h1>
      </div><!-- /.col -->
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
          <li class="breadcrumb-item"><a href="{{ url('credit_cards/') }}">Cartões de crédito</a></li>
          <li class="breadcrumb-item active">Editar Cartão de crédito</li>
        </ol>
      </div><!-- /.col -->
    </div><!-- /.row -->
  </div><!-- /.container-fluid -->
</div>
<div class="content">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-12">

        <div class="card">
          <!-- /.card-header -->
          <div class="card-body">
            <form role="form" method="post" action="{{ url('credit_cards/store') }}">
              @csrf
              @method('POST')

              <input type="hidden" name="id" value="{{ $credit_card->id }}">

              <div class="row">
                <div class="col-sm-12">
                  <div class="form-group">
                    <label>Nome</label>
                    <input type="text" name="descricao" class="form-control" placeholder="Nome do cartão de crédito" value="{{ $credit_card->descricao }}">
                  </div>

                  <div class="form-group">
                    <label>Dia de vencimento</label>
                    <input type="text" name="dia_vencimento" class="form-control" placeholder="Dia de vencimento do cartão. Valores permitidos de 1 a 31." value="{{ $credit_card->dia_vencimento }}">
                  </div>

                  <div class="form-group">
                    <label>Cartão pai</label>
                    <select name="id_cartao_pai" class="form-control">
                      <option value="">Nenhum (é um cartão físico)</option>
                      @foreach ($parent_cards as $parent_card)
                        <option value="{{ $parent_card->id }}" {{ $credit_card->id_cartao_pai == $parent_card->id ? 'selected' : '' }}>{{ $parent_card->descricao }}</option>
                      @endforeach
                    </select>
                    <small class="form-text text-muted">Selecione um cartão físico se este for um cartão virtual (subcartão) vinculado a ele.</small>
                  </div>

                  <div class="form-group">
                    <label>Últimos 4 dígitos</label>
                    <input type="text" name="ultimos_digitos" class="form-control" maxlength="4" placeholder="Só necessário para cartões virtuais, ex.: 1234" value="{{ $credit_card->ultimos_digitos }}">
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-sm-6">
                  <button type="submit" class="btn btn-primary">Salvar</button>
                  <a href="{{ url('credit_cards') }}" class="btn btn-default">Cancelar</a>
                </div>
                <div class="col-sm-6 text-right">
                  <a href="{{ url('credit_cards/remove/'.$credit_card->id) }}" class="btn btn-danger" onclick="return confirm('Tem certeza que deseja excluir o cartão de crédito #{{ $credit_card->id }}: {{ $credit_card->descricao }}')">Excluir</a>
                </div>
              </div>
            </div>
            <!-- /.card-body -->
          </div>

        </div>
      </div>
    </div>
  </div>
  @endsection
