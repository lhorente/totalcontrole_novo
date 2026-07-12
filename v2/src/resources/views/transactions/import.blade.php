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
          <li class="breadcrumb-item active">Importar CSV / JSON</li>
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
              <i class="fas fa-file-import"></i>
              Importar Transações
            </h3>
          </div>

          <form action="{{ route('transactions.importPreview') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="card-body">

              {{-- Seleção do método de entrada --}}
              <div class="form-group">
                <label>Método de Importação</label>
                <div class="btn-group btn-group-toggle d-block" data-toggle="buttons">
                  <label class="btn btn-outline-secondary active" id="btn-upload">
                    <input type="radio" name="input_method" value="file" checked> <i class="fas fa-upload"></i> Enviar Arquivo
                  </label>
                  <label class="btn btn-outline-secondary" id="btn-text">
                    <input type="radio" name="input_method" value="text"> <i class="fas fa-paste"></i> Colar Conteúdo
                  </label>
                </div>
              </div>

              {{-- Seção: upload de arquivo --}}
              <div id="section-file">
                <div class="form-group">
                  <label for="file">Arquivo CSV ou JSON</label>
                  <div class="input-group">
                    <div class="custom-file">
                      <input type="file" class="custom-file-input" id="file" name="file" accept=".csv,.json">
                      <label class="custom-file-label" for="file">Escolher arquivo</label>
                    </div>
                  </div>
                  <small class="form-text text-muted">Selecione um arquivo <strong>.csv</strong> ou <strong>.json</strong> (gerado pelo Claude) contendo as transações.</small>
                </div>
              </div>

              {{-- Seção: colar conteúdo --}}
              <div id="section-text" style="display:none;">
                <div class="form-group">
                  <label for="json_content">Conteúdo JSON</label>
                  <textarea class="form-control" id="json_content" name="json_content" rows="10" placeholder="Cole aqui o conteúdo JSON gerado pelo Claude..."></textarea>
                  <small class="form-text text-muted">Cole o conteúdo JSON diretamente na caixa acima.</small>
                </div>
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
              <button type="submit" id="btn-submit-file" class="btn btn-primary" formaction="{{ route('transactions.importPreview') }}">
                <i class="fas fa-arrow-right"></i> Avançar para Revisão
              </button>
              <button type="submit" id="btn-submit-json" class="btn btn-primary" formaction="{{ route('transactions.importPreviewJson') }}" style="display:none">
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
  var sectionFile   = document.getElementById('section-file');
  var sectionText   = document.getElementById('section-text');
  var fileInput     = document.getElementById('file');
  var textInput     = document.getElementById('json_content');
  var btnSubmitFile = document.getElementById('btn-submit-file');
  var btnSubmitJson = document.getElementById('btn-submit-json');

  function useJsonSubmit() {
    btnSubmitFile.style.display = 'none';
    btnSubmitJson.style.display = '';
  }

  function useCsvSubmit() {
    btnSubmitFile.style.display = '';
    btnSubmitJson.style.display = 'none';
  }

  // Atualiza o label e detecta se o arquivo é JSON
  fileInput.addEventListener('change', function(e) {
    var file = e.target.files[0];
    if (!file) return;
    e.target.nextElementSibling.innerText = file.name;
    file.name.toLowerCase().endsWith('.json') ? useJsonSubmit() : useCsvSubmit();
  });

  document.getElementById('btn-upload').addEventListener('click', function() {
    sectionFile.style.display = '';
    sectionText.style.display = 'none';
    fileInput.required = true;
    textInput.required = false;
    textInput.value = '';
    // Revalida o botão com base no arquivo já selecionado (se houver)
    var file = fileInput.files[0];
    (file && file.name.toLowerCase().endsWith('.json')) ? useJsonSubmit() : useCsvSubmit();
  });

  document.getElementById('btn-text').addEventListener('click', function() {
    sectionFile.style.display = 'none';
    sectionText.style.display = '';
    fileInput.required = false;
    fileInput.value = '';
    document.querySelector('.custom-file-label').innerText = 'Escolher arquivo';
    textInput.required = true;
    useJsonSubmit();
  });
</script>

@endsection

