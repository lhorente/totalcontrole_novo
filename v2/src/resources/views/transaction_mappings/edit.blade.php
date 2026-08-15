@extends('layouts.dashboard')

@section('content')
<div class="content-header">
  <div class="container">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 text-dark"> Editar mapeamento</h1>
      </div><!-- /.col -->
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
          <li class="breadcrumb-item"><a href="{{ url('transaction-mappings/') }}">De &lt;&gt; Para de descrições</a></li>
          <li class="breadcrumb-item active">Editar mapeamento</li>
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
          <div class="card-header">
            @if ($mapping->origem === 'automatico')
              <span class="badge badge-info">Automático</span>
            @else
              <span class="badge badge-secondary">Manual</span>
            @endif
            <span class="text-muted ml-2">Usado {{ $mapping->ocorrencias }}x
              @if ($mapping->ultima_utilizacao) — última vez em {{ $mapping->ultima_utilizacao->format('d/m/Y') }} @endif
            </span>
          </div>
          <!-- /.card-header -->
          <div class="card-body">
            <form role="form" method="post" action="{{ url('transaction-mappings/store') }}">
              @csrf
              @method('POST')

              <input type="hidden" name="id" value="{{ $mapping->id }}">

              <div class="row">
                <div class="col-sm-6">
                  <div class="form-group">
                    <label for="padrao">Padrão da fatura</label>
                    <input type="text" id="padrao" name="padrao" class="form-control @error('padrao') is-invalid @enderror"
                           placeholder="ex: CBQ*CZTO COMERCIO DE" value="{{ old('padrao', $mapping->padrao) }}">
                    <small class="text-muted">Trecho da descrição do banco. Qualquer fatura que contenha esse trecho será reconhecida.</small>
                    @error('padrao')<div class="invalid-feedback">{{ $message }}</div>@enderror
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="form-group">
                    <label for="descricao_local">Vira (local / apelido)</label>
                    <input type="text" id="descricao_local" name="descricao_local" class="form-control @error('descricao_local') is-invalid @enderror"
                           placeholder="ex: McDonald's" value="{{ old('descricao_local', $mapping->descricao_local) }}">
                    @error('descricao_local')<div class="invalid-feedback">{{ $message }}</div>@enderror
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-sm-6">
                  <div class="form-group">
                    <label for="id_categoria">Categoria</label>
                    <select id="id_categoria" name="id_categoria" class="form-control @error('id_categoria') is-invalid @enderror">
                      <option value="">Sem categoria</option>
                      @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('id_categoria', $mapping->id_categoria) == $cat->id ? 'selected' : '' }}>
                          {{ $cat->nome }}
                        </option>
                      @endforeach
                    </select>
                    @error('id_categoria')<div class="invalid-feedback">{{ $message }}</div>@enderror
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="form-group">
                    <label for="id_cliente">Pessoa <small class="text-muted">(opcional)</small></label>
                    <select id="id_cliente" name="id_cliente" class="form-control @error('id_cliente') is-invalid @enderror">
                      <option value="">Nenhuma</option>
                      @foreach ($contacts as $contact)
                        <option value="{{ $contact->id }}" {{ old('id_cliente', $mapping->id_cliente) == $contact->id ? 'selected' : '' }}>
                          {{ $contact->nome }}
                        </option>
                      @endforeach
                    </select>
                    @error('id_cliente')<div class="invalid-feedback">{{ $message }}</div>@enderror
                  </div>
                </div>
              </div>

              <div class="form-group">
                <div class="custom-control custom-switch">
                  <input type="checkbox" class="custom-control-input" id="ativo" name="ativo" value="1" {{ old('ativo', $mapping->ativo) ? 'checked' : '' }}>
                  <label class="custom-control-label" for="ativo">Mapeamento ativo</label>
                </div>
              </div>

              <div class="row">
                <div class="col-sm-6">
                  <button type="submit" class="btn btn-primary">Salvar</button>
                  <a href="{{ url('transaction-mappings') }}" class="btn btn-default">Cancelar</a>
                </div>
                <div class="col-sm-6 text-right">
                  <a href="{{ url('transaction-mappings/remove/'.$mapping->id) }}" class="btn btn-danger" onclick="return confirm('Tem certeza que deseja excluir o mapeamento #{{ $mapping->id }}: {{ $mapping->padrao }}')">Excluir</a>
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
