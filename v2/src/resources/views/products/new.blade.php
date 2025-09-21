@extends('layouts.dashboard')

@section('content')
<div class="content-header">
  <div class="container">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 text-dark"> Adicionar Produto</h1>
      </div><!-- /.col -->
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
          <li class="breadcrumb-item"><a href="/products">Produtos</a></li>
          <li class="breadcrumb-item active">Adicionar Produto</li>
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
            <form role="form">
              <div class="row">
                <div class="col-sm-12">
                  <div class="form-group">
                    <label>Nome do produto</label>
                    <input type="text" class="form-control" placeholder="Nome do produto">
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-sm-4">
                  <div class="form-group">
                    <label>Código interno</label>
                    <input type="text" class="form-control" placeholder="Código interno">
                  </div>
                </div>
                <div class="col-sm-4">
                  <div class="form-group">
                    <label>EAN</label>
                    <input type="text" class="form-control" placeholder="EAN">
                  </div>
                </div>
                <div class="col-sm-4">
                  <div class="form-group">
                    <label>Valor de venda</label>
                    <input type="text" class="form-control" placeholder="0,00">
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-sm-12">
                  <button type="submit" class="btn btn-primary">Salvar</button>
                  <a href="/products" class="btn btn-default">Cancelar</a>
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
