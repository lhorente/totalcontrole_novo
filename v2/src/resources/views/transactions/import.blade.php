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

              {{-- Gerar prompt para IA --}}
              <div class="card card-outline card-info mt-3 mb-3">
                <div class="card-header">
                  <h3 class="card-title"><i class="fas fa-robot"></i> Gerar arquivo com IA</h3>
                </div>
                <div class="card-body">
                  <div class="form-group mb-2">
                    <label for="cartao_prompt">Cartão de crédito</label>
                    <select class="form-control" id="cartao_prompt">
                      <option value="">Selecione um cartão</option>
                      @foreach ($cartoes as $cartao)
                        <option value="{{ $cartao->descricao }}">{{ $cartao->descricao }}</option>
                      @endforeach
                    </select>
                  </div>
                  <button type="button" class="btn btn-info" id="btn-copy-prompt">
                    <i class="fas fa-copy"></i> Copiar prompt
                  </button>
                  <small class="form-text text-muted mt-2">
                    Cole na IA junto com o PDF da fatura. Ela devolve um <strong>.csv</strong> pronto para importar abaixo.
                  </small>
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

  document.getElementById('btn-copy-prompt').addEventListener('click', function() {
    var cartao = document.getElementById('cartao_prompt').value;
    if (!cartao) {
      alert('Selecione um cartão antes de copiar o prompt.');
      return;
    }
var prompt = 'Analise a fatura do cartão "' + cartao + '" (Bradesco) que estou enviando em PDF e extraia todas as transações.\n\n' +
  'Retorne SOMENTE um arquivo CSV, sem explicações adicionais, com as seguintes colunas:\n' +
  'data,descricao,valor\n\n' +
  'Regras de leitura do PDF:\n' +
  '- O PDF pode listar mais de um cartão, cada um com seu próprio bloco "Data / Histórico / R$" e linha "Total para [NOME]"; extraia os lançamentos de todos os cartões\n' +
  '- As datas vêm sem ano (DD/MM); use a data do extrato impressa no topo do PDF para inferir o ano: meses iguais ou anteriores ao mês do extrato pertencem ao mesmo ano do extrato, meses posteriores (parcelas antigas, ex: out/nov/dez) pertencem ao ano anterior\n' +
  '- Alguns lançamentos quebram em 3 linhas quando a descrição é longa (nome do estabelecimento em uma linha, "DD/MM valor" na linha seguinte, e o número da parcela tipo "2/5" na linha depois); reconstrua essas quebras como um único lançamento\n' +
  '- Inclua também as linhas "SALDO ANTERIOR" e "PAGTO. POR DEB EM C/C" (ou equivalentes) como lançamentos normais, na ordem em que aparecem\n\n' +
  'Regras de formatação da saída:\n' +
  '- data no formato DD/MM/YYYY\n' +
  '- descricao: nome do estabelecimento/lançamento, incluindo o número da parcela quando houver (ex: "MODA INFANTIL TIP TOP 1/3")\n' +
  '- valor: número decimal com ponto (ex: 49.90)\n' +
  '- sinal do valor: negativo para lançamentos que aumentam o valor devido (compras e SALDO ANTERIOR); positivo para lançamentos que reduzem o valor devido (pagamentos, estornos, créditos)\n' +
  '- Não inclua cabeçalho com acento ou espaço\n' +
  '- Separe os campos por vírgula\n' +
  '- Uma transação por linha\n\n' +
  'Validação obrigatória antes de responder: some os valores de cada cartão e confira contra "Total para [NOME]" impresso no PDF; some os totais de todos os cartões e confira contra "Total da Fatura em Real". Corrija a extração caso haja divergência.';
    navigator.clipboard.writeText(prompt).then(function() {
      var btn = document.getElementById('btn-copy-prompt');
      var original = btn.innerHTML;
      btn.innerHTML = '<i class="fas fa-check"></i> Copiado!';
      btn.classList.replace('btn-info', 'btn-success');
      setTimeout(function() {
        btn.innerHTML = original;
        btn.classList.replace('btn-success', 'btn-info');
      }, 2000);
    }).catch(function() {
      alert('Não foi possível copiar. Tente manualmente.');
    });
  });
</script>

@endsection

