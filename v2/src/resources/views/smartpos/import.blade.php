@extends('layouts.dashboard')

@section('content')
<div class="content-header">
  <div class="container">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 text-dark">Importar Vendas SmartPOS</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
          <li class="breadcrumb-item active">Importar SmartPOS</li>
        </ol>
      </div>
    </div>
  </div>
</div>

<div class="content">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-8">

        @if (session('success'))
          <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <h5><i class="icon fas fa-check"></i> Sucesso!</h5>
            {{ session('success') }}
          </div>
        @endif

        @if ($errors->any())
          <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <h5><i class="icon fas fa-ban"></i> Erro</h5>
            <ul class="mb-0">
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <div class="card card-primary">
          <div class="card-header">
            <h3 class="card-title">
              <i class="fas fa-file-excel mr-1"></i>
              Upload do arquivo XLSX
            </h3>
          </div>

          <form action="{{ route('smartpos.preview') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="card-body">
              <div class="alert alert-info mb-3">
                <i class="fas fa-info-circle mr-1"></i>
                Importe o arquivo XLSX exportado do <strong>SmartPOS</strong>.
                Apenas vendas com status <strong>"Finalizada"</strong> serão importadas.
                Registros já existentes serão ignorados automaticamente.
              </div>

              <div class="form-group">
                <label for="file">Arquivo XLSX <span class="text-danger">*</span></label>
                <div class="input-group">
                  <div class="custom-file">
                    <input type="file" class="custom-file-input" id="file" name="file" accept=".xlsx,.xls" required>
                    <label class="custom-file-label" for="file">Escolher arquivo</label>
                  </div>
                </div>
                <small class="form-text text-muted">Formatos aceitos: XLSX, XLS. Tamanho máximo: 10MB.</small>
              </div>
            </div>

            <div class="card-footer">
              <button type="submit" class="btn btn-primary">
                <i class="fas fa-search mr-1"></i>
                Pré-visualizar
              </button>
            </div>
          </form>
        </div>

      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  // Update custom file label with file name
  document.getElementById('file').addEventListener('change', function (e) {
    var fileName = e.target.files[0] ? e.target.files[0].name : 'Escolher arquivo';
    e.target.nextElementSibling.innerText = fileName;
  });
</script>
@endpush
