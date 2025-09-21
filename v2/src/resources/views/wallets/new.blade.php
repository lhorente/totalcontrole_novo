@extends('layouts.dashboard')

@section('content')
<div class="content-header">
  <div class="container">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 text-dark"> Adicionar Carteira</h1>
      </div><!-- /.col -->
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
          <li class="breadcrumb-item"><a href="{{ url('wallets/') }}">Carteiras</a></li>
          <li class="breadcrumb-item active">Adicionar Carteira</li>
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
            <form role="form" method="post" action="{{ url('wallets/store') }}">
              @csrf
              @method('POST')

              <div class="row">
                <div class="col-sm-12">
                  <div class="form-group">
                    <label>Nome</label>
                    <input type="text" name="titulo" class="form-control" placeholder="Nome do Carteira">
                  </div>

                  <div class="form-group">
                    <label>Carteira pai</label>
                    <select class="form-control" name="parent_id">
                      <option value="">Selecionar</option>
                      <?php
                      foreach ($wallets as $wallet){
                        echo '<option value="'.$wallet->id.'">'.$wallet->titulo.'</option>';
                      }
                      ?>
                    </select>
                  </div>

                  <div class="form-group">
                    <div class="checkbox">
                      <label>
                        <input type="checkbox" name="exibir_no_saldo" value="1"> Exibir o saldo dessa carteira na soma de valor disponível para gastos
                      </label>
                    </div>
                  </div>

                </div>
              </div>

              <div class="row">
                <div class="col-sm-12">
                  <button type="submit" class="btn btn-primary">Salvar</button>
                  <a href="{{ url('wallets') }}" class="btn btn-default">Cancelar</a>
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
