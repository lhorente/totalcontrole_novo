@extends('layouts.dashboard')

@section('content')

@php
  $tipoLabels = ['casa'=>'Imóvel','carro'=>'Carro','outro'=>'Outro'];
  $tipoIcons  = ['casa'=>'fa-home','carro'=>'fa-car','outro'=>'fa-box'];
@endphp

<style>
  .bem-card-header { background: linear-gradient(135deg,#1B5E5C,#2D8B86); border-radius: .25rem .25rem 0 0; }
  .bem-tile { transition: box-shadow .15s ease; }
  .bem-tile:hover { box-shadow: 0 0 12px rgba(0,0,0,.08); }
  .bem-tile-icon { width: 44px; height: 44px; border-radius: .5rem; display:flex; align-items:center; justify-content:center; background:#EFF6FF; color:#1D4A7C; flex:none; }
  .bem-tile-inativo { opacity: .6; }
</style>

<div class="content-header">
  <div class="container">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 text-dark">Bens</h1>
      </div><!-- /.col -->
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
          <li class="breadcrumb-item active">Bens</li>
        </ol>
      </div><!-- /.col -->
    </div><!-- /.row -->
  </div><!-- /.container-fluid -->
</div>
<div class="content">
  <div class="container">

    @if (\Session::has('success'))
      <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        {{ Session::get('success') }}
      </div>
    @endif
    @if (\Session::has('error'))
      <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        {{ Session::get('error') }}
      </div>
    @endif

    <div class="row justify-content-center">
      <div class="col-md-12">

        <div class="card">
          <div class="card-header bem-card-header">
            <a href="{{ route('bens.create') }}" class="btn btn-light">Cadastrar bem</a>
          </div>
          <!-- /.card-header -->
          <div class="card-body">
            @if ($bens->isEmpty())
              <p class="text-center text-muted py-4 mb-0">Nenhum bem cadastrado ainda.</p>
            @else
              <div class="row">
                @foreach ($bens as $bem)
                  <div class="col-md-4 mb-4">
                    <a href="{{ route('bens.edit', $bem->id) }}" class="text-decoration-none text-dark">
                      <div class="card bem-tile h-100 mb-0 {{ !$bem->ativo ? 'bem-tile-inativo' : '' }}">
                        <div class="card-body">
                          <div class="d-flex align-items-center mb-2">
                            <div class="bem-tile-icon mr-3">
                              <i class="fas {{ $tipoIcons[$bem->tipo] ?? 'fa-box' }}"></i>
                            </div>
                            <div>
                              <h5 class="mb-0">{{ $bem->nome }}</h5>
                              <small class="text-muted">{{ $tipoLabels[$bem->tipo] ?? $bem->tipo }}</small>
                            </div>
                          </div>
                          @if ($bem->detalhe)
                            <p class="text-muted small mb-0">{{ $bem->detalhe }}</p>
                          @endif
                          @if (!$bem->ativo)
                            <span class="badge badge-light mt-2">Inativo</span>
                          @endif
                        </div>
                      </div>
                    </a>
                  </div>
                @endforeach
              </div>
            @endif
          </div>
          <!-- /.card-body -->
        </div>

      </div>
    </div>
  </div>
</div>
@endsection
