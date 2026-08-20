@extends('layouts.dashboard')

@section('content')

<div class="content-header">
  <div class="container">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 text-dark">Editar item de planejamento</h1>
      </div><!-- /.col -->
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
          <li class="breadcrumb-item"><a href="{{ route('planejamento.index') }}">Manutenções &amp; Compras</a></li>
          <li class="breadcrumb-item active">Editar item</li>
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
            <form role="form" method="post" action="{{ route('planejamento.update', $item->id) }}">
              @csrf

              @include('planejamento.partials.form', ['item' => $item])

              <div class="row">
                <div class="col-sm-6">
                  <button type="submit" class="btn btn-primary">Salvar</button>
                  <a href="{{ route('planejamento.index') }}" class="btn btn-default">Cancelar</a>
                </div>
                <div class="col-sm-6 text-right">
                  <a href="#" class="btn btn-danger" onclick="event.preventDefault(); document.getElementById('form-delete').submit();">Excluir</a>
                </div>
              </div>
            </form>

            <form id="form-delete" method="POST" action="{{ route('planejamento.destroy', $item->id) }}" style="display:none">
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
  if (!confirm('Tem certeza que deseja excluir "{{ $item->titulo }}"? Esta ação não pode ser desfeita.')) {
    e.preventDefault();
  }
});
</script>
@endsection
