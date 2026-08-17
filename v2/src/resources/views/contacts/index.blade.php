@extends('layouts.dashboard')

@section('content')

@php
  $tipoLabels = ['fornecedor'=>'Fornecedor','cliente'=>'Cliente comercial','familiar'=>'Familiar','pessoal'=>'Pessoal','outro'=>'Outro'];
@endphp

<div class="content-header">
  <div class="container">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 text-dark">Contatos</h1>
      </div><!-- /.col -->
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
          <li class="breadcrumb-item active">Contatos</li>
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
          <div class="card-header">
            <a href="{{ route('contacts.create') }}" class="btn btn-primary">Adicionar contato</a>
          </div>
          <!-- /.card-header -->
          <div class="card-body p-0">
            <table class="table">
              <thead>
                <tr>
                  <th style="width: 10px">#</th>
                  <th>Nome</th>
                  <th>Tipo</th>
                  <th>Status</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                @forelse ($contacts as $contact)
                <tr>
                  <td>{{ $contact->id }}.</td>
                  <td>
                    <a href="{{ route('contacts.edit', $contact->id) }}">{{ $contact->nome }}</a>
                  </td>
                  <td>
                    @if ($contact->tipo)
                      <span class="badge badge-secondary">{{ $tipoLabels[$contact->tipo] ?? $contact->tipo }}</span>
                    @else
                      <span class="text-muted">—</span>
                    @endif
                  </td>
                  <td>
                    @if ($contact->status === 'ativo')
                      <span class="badge badge-success">Ativo</span>
                    @else
                      <span class="badge badge-secondary">Inativo</span>
                    @endif
                  </td>
                  <td class="text-right">
                    <a href="{{ route('contacts.edit', $contact->id) }}" class="btn btn-sm btn-outline-secondary">Editar</a>
                  </td>
                </tr>
                @empty
                  <tr>
                    <td colspan="5" class="text-center text-muted py-4">Nenhum contato cadastrado ainda.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
          <!-- /.card-body -->
        </div>

      </div>
    </div>
  </div>
</div>
@endsection
