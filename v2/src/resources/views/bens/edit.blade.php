@extends('layouts.dashboard')

@section('content')

<div class="content-header">
  <div class="container">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 text-dark">Editar bem</h1>
      </div><!-- /.col -->
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
          <li class="breadcrumb-item"><a href="{{ route('bens.index') }}">Bens</a></li>
          <li class="breadcrumb-item active">Editar bem</li>
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
            <form role="form" method="post" action="{{ route('bens.update', $bem->id) }}">
              @csrf

              @include('bens.partials.form', ['bem' => $bem])

              <div class="row">
                <div class="col-sm-6">
                  <button type="submit" class="btn btn-primary">Salvar</button>
                  <a href="{{ route('bens.index') }}" class="btn btn-default">Cancelar</a>
                </div>
                <div class="col-sm-6 text-right">
                  <a href="#" class="btn btn-danger" onclick="event.preventDefault(); document.getElementById('form-delete').submit();">Excluir</a>
                </div>
              </div>
            </form>

            <form id="form-delete" method="POST" action="{{ route('bens.destroy', $bem->id) }}" style="display:none">
              @csrf
              @method('DELETE')
            </form>
          </div>
          <!-- /.card-body -->
        </div>

      </div>
    </div>
  </div>
</div>

<script>
document.getElementById('form-delete').addEventListener('submit', function(e){
  if (!confirm('Tem certeza que deseja excluir o bem "{{ $bem->nome }}"?')) {
    e.preventDefault();
  }
});
</script>
@endsection
