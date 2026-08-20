@extends('layouts.dashboard')

@section('content')

<div class="content-header">
  <div class="container">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 text-dark">Novo item de planejamento</h1>
      </div><!-- /.col -->
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
          <li class="breadcrumb-item"><a href="{{ route('planejamento.index') }}">Manutenções &amp; Compras</a></li>
          <li class="breadcrumb-item active">Novo item</li>
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
            <form role="form" method="post" action="{{ route('planejamento.store') }}">
              @csrf

              @include('planejamento.partials.form', ['item' => null])

              <div class="row">
                <div class="col-sm-12">
                  <button type="submit" class="btn btn-primary">Salvar</button>
                  <a href="{{ route('planejamento.index') }}" class="btn btn-default">Cancelar</a>
                </div>
              </div>
            </form>
          </div>
          <!-- /.card-body -->
        </div>

      </div>
    </div>
  </div>
</div>
@endsection
