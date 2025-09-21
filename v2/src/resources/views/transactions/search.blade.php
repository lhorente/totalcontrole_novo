@extends('layouts.dashboard')

@section('content')
<div class="content-header">
  <div class="container">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 text-dark"> Lançamentos</h1>
      </div><!-- /.col -->
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{ url('/') }}'">Dashboard</a></li>
          <li class="breadcrumb-item active">Lançamentos</li>
        </ol>
      </div><!-- /.col -->
    </div><!-- /.row -->
  </div><!-- /.container-fluid -->
</div>
<div class="content">
  <div class="container">
    <div class="row justify-content-center">

      <div class="col-md-12">

        <div class="card collapsed-card">
          <div class="card-header">
            <h3 class="card-title"><i class="fa fa-filter"></i> Filtro <span class="badge badge-info">3</span></h3>
            <div class="card-tools">
              <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-plus"></i>
              </button>
            </div>
          </div>

          <div class="card-body p-0">
            <form>
              <div class="col-md-12">
                <div class="form-group">
                  <label for="filter-year">Filtros pré-definidos</label>
                  <select class="form-control col-md-12 col-sm-2" name="ps">
                    <option value=""></option>
                    <option value="lendings_not_paid">Empréstimos não pagos</option>
                  </select>
                </div>

                <div class="form-group">
                  <label>Período:</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">
                        <i class="far fa-calendar-alt"></i>
                      </span>
                    </div>
                    <input type="number" class="form-control col-md-1 col-sm-2" name="y" value="{{ $year }}" />

                    <select class="form-control" name="m">
                      <option value="">Ano inteiro</option>
                      <option value="1">Janeiro</option>
                      <option value="2">Fevereiro</option>
                      <option value="3">Março</option>
                      <option value="4">Abril</option>
                      <option value="5">Maio</option>
                      <option value="6">Junho</option>
                      <option value="7">Julho</option>
                      <option value="8">Agosto</option>
                      <option value="9">Setembro</option>
                      <option value="10">Outubro</option>
                      <option value="11">Novembro</option>
                      <option value="12">Dezembro</option>
                    </select>
                  </div>

                </div>
              </div>
              <div class="col-md-12">
                <div class="form-group">
                  <button type="submit" class="form-control">Aplicar</button>
                </div>
              </div>
            </form>
          </div>
        </div>

      </div>

      <?php foreach ($transactions as $transaction): ?>
      <div class="col-12 col-xs-12 col-md-12">
          <a href="/transactions/edit/{{ $transaction->id }}" class="info-box info-box-transaction">
            <span class="info-box-icon bg-info">
              @if ($transaction->category)
              <i class="{{ $transaction->category->icon_class }}"></i>
              @endif
            </span>
            <div class="info-box-content">
              <span class="info-box-text">{{ $transaction->descricao }}</span>
              <span class="info-box-number">
                <span>{{ $transaction->data->format('dMY') }}</span>
                <span>R$ {{ number_format($transaction->valor,2,",",".") }}</span>
              </span>
              <div class="categories">
                @if ($transaction->wallet)
                <span><i class="fa fa-wallet"></i> {{ $transaction->wallet->titulo }}</span>
                @endif

                @if ($transaction->tipo)
                <span>{{ $transaction->tipo }}</span>
                @endif

                @if ($transaction->credit_card)
                <span><i class="fa fa-credit-card"></i> {{ $transaction->credit_card->descricao }}</span>
                @endif

                @if ($transaction->contact)
                  @if ($transaction->data_recebimento)
                    <span><i class="fa fa-user-check"></i> {{ $transaction->contact->nome }}</span>
                  @else
                    <span><i class="fa fa-user"></i> {{ $transaction->contact->nome }}</span>
                  @endif
                @endif

                @if ($transaction->data_pagamento)
                <span><i class="fa fa-check"></i> Pago</span>
                @endif
              </div>
            </div>
          </a>
      </div>
      <?php endforeach ?>

    </div>
  </div>
</div>
@endsection
