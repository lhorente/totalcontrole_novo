@extends('layouts.dashboard')

@section('content')
<div class="content-header">
  <div class="container">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 text-dark"> Adicionar Categoria</h1>
      </div><!-- /.col -->
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
          <li class="breadcrumb-item"><a href="{{ url('categories/') }}">Categorias</a></li>
          <li class="breadcrumb-item active">Adicionar Categoria</li>
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
            <form role="form" method="post" action="{{ url('categories/store') }}">
              @csrf
              @method('POST')

              <div class="row">
                <div class="col-sm-12">
                  <div class="form-group">
                    <label>Nome</label>
                    <input type="text" name="nome" class="form-control" placeholder="Nome do Categoria">
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-sm-12">
                  <button type="submit" class="btn btn-primary">Salvar</button>
                  <a href="{{ url('categories') }}" class="btn btn-default">Cancelar</a>
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
