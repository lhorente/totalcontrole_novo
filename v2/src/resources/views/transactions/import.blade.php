@extends('layouts.dashboard')

@section('content')
<div class="content-header">
  <div class="container">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 text-dark">Importar Transações</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
          <li class="breadcrumb-item"><a href="{{ url('/transactions') }}">Lançamentos</a></li>
          <li class="breadcrumb-item active">Importar CSV</li>
        </ol>
      </div>
    </div>
  </div>
</div>

<div class="content">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-8">

        @if ($errors->any())
          <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <h5><i class="icon fas fa-ban"></i> Erros de validação</h5>
            <ul>
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <div class="card card-primary">
          <div class="card-header">
            <h3 class="card-title">
              <i class="fas fa-file-csv"></i>
              Importar Transações via CSV
            </h3>
          </div>

          <form action="{{ route('transactions.importPreview') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="card-body">
              <div class="form-group">
                <label for="file">Arquivo CSV</label>
                <div class="input-group">
                  <div class="custom-file">
                    <input type="file" class="custom-file-input" id="file" name="file" accept=".csv" required>
                    <label class="custom-file-label" for="file">Escolher arquivo</label>
                  </div>
                </div>
                <small class="form-text text-muted">Selecione um arquivo CSV contendo as transações.</small>
              </div>

              <div class="form-group">
                <label for="id_cartao">Cartão de Crédito</label>
                <select class="form-control" id="id_cartao" name="id_cartao" required>
                  <option value="">Selecione um cartão</option>
                  @foreach ($cartoes as $cartao)
                    <option value="{{ $cartao->id }}">{{ $cartao->descricao }}</option>
                  @endforeach
                </select>
              </div>

              <div class="form-group">
                <label for="data_fatura">Data da Fatura</label>
                <input type="date" class="form-control" id="data_fatura" name="data_fatura" required>
              </div>
            </div>

            <div class="card-footer">
              <button type="submit" class="btn btn-primary">
                <i class="fas fa-arrow-right"></i> Avançar para Revisão
              </button>
              <a href="{{ url('/transactions') }}" class="btn btn-default">
                <i class="fas fa-times"></i> Cancelar
              </a>
            </div>
          </form>
        </div>

      </div>
    </div>
  </div>
</div>

<script>
  // Atualiza o label do input file quando um arquivo é selecionado
  document.querySelector('.custom-file-input').addEventListener('change', function(e) {
    var fileName = e.target.files[0].name;
    var nextSibling = e.target.nextElementSibling;
    nextSibling.innerText = fileName;
  });
</script>

@endsection

