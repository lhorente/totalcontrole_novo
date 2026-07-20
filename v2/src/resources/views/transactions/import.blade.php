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

          <form action="{{ route('transactions.importPreview') }}" method="POST" enctype="multipart/form-data" id="import-form">
            @csrf

            <div class="card-body">

              {{-- Cartão e data da fatura --}}
              <div class="form-group">
                <label>Cartão de Crédito</label>
                <input type="hidden" id="id_cartao" name="id_cartao">
                <div class="d-flex flex-wrap" style="gap:.5rem;" id="cartao-picker">
                  @foreach ($cartoes as $cartao)
                    <div class="cartao-card border rounded py-2 px-3"
                         style="cursor:pointer; user-select:none; transition: background .15s, border-color .15s;"
                         data-id="{{ $cartao->id }}"
                         data-nome="{{ $cartao->descricao }}">
                      <i class="fas fa-credit-card mr-1"></i> {{ $cartao->descricao }}
                    </div>
                  @endforeach
                </div>
                <small class="form-text text-muted">Clique no cartão desejado.</small>
              </div>

              <div class="form-group">
                <label for="data_fatura">Data da Fatura</label>
                <input type="date" class="form-control" id="data_fatura" name="data_fatura" required>
              </div>

              {{-- Gerar prompt para IA --}}
              <div class="d-flex align-items-center justify-content-between flex-wrap mt-3 mb-3 rounded px-3 py-2"
                   style="gap:.75rem; background-color:#e8f4fd; border:1px solid #17a2b8;">
                <div>
                  <strong><i class="fas fa-robot"></i> Gerar arquivo com IA</strong><br>
                  <small class="text-muted">Cole na IA junto com o PDF da fatura. Ela devolve um <strong>.csv</strong> pronto para importar abaixo.</small>
                </div>
                <button type="button" class="btn btn-sm" id="btn-copy-prompt"
                        style="background-color:#17a2b8; border-color:#17a2b8; color:#fff;">
                  <i class="fas fa-copy"></i> Copiar prompt
                </button>
              </div>

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
                  <label for="file">Arquivo CSV</label>
                  <div class="input-group">
                    <div class="custom-file">
                      <input type="file" class="custom-file-input" id="file" name="file" accept=".csv,.txt">
                      <label class="custom-file-label" for="file">Escolher arquivo</label>
                    </div>
                  </div>
                  <small class="form-text text-muted">Selecione um arquivo <strong>.csv</strong> contendo as transações.</small>
                </div>
              </div>

              {{-- Seção: colar conteúdo --}}
              <div id="section-text" style="display:none;">
                <div class="form-group">
                  <label for="csv_content">Conteúdo CSV</label>
                  <textarea class="form-control" id="csv_content" name="csv_content" rows="10" placeholder="Cole aqui o conteúdo CSV gerado pela IA..."></textarea>
                  <small class="form-text text-muted">Cole o conteúdo CSV diretamente na caixa acima.</small>
                </div>
              </div>

            </div>

            <div class="card-footer">
              <button type="submit" class="btn btn-primary" formaction="{{ route('transactions.importPreview') }}">
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
  var sectionFile  = document.getElementById('section-file');
  var sectionText  = document.getElementById('section-text');
  var fileInput    = document.getElementById('file');
  var textInput    = document.getElementById('csv_content');
  var cartaoInput  = document.getElementById('id_cartao');

  // Cartão picker
  document.querySelectorAll('.cartao-card').forEach(function(card) {
    card.addEventListener('click', function() {
      document.querySelectorAll('.cartao-card').forEach(function(c) {
        c.style.backgroundColor = '';
        c.style.borderColor     = '';
        c.style.color           = '';
        c.style.fontWeight      = '';
      });
      card.style.backgroundColor = '#e8f4fd';
      card.style.borderColor     = '#17a2b8';
      card.style.color           = '#17a2b8';
      card.style.fontWeight      = '600';
      cartaoInput.value          = card.dataset.id;
      cartaoInput.dataset.nome   = card.dataset.nome;
    });
  });

  // Valida cartão selecionado no submit
  document.getElementById('import-form').addEventListener('submit', function(e) {
    if (!cartaoInput.value) {
      e.preventDefault();
      alert('Selecione um cartão antes de avançar.');
      document.getElementById('cartao-picker').scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
  });

  // Atualiza o label do custom-file ao selecionar arquivo
  fileInput.addEventListener('change', function(e) {
    var file = e.target.files[0];
    if (!file) return;
    e.target.nextElementSibling.innerText = file.name;
  });

  document.getElementById('btn-upload').addEventListener('click', function() {
    sectionFile.style.display = '';
    sectionText.style.display = 'none';
    fileInput.required = true;
    textInput.required = false;
    textInput.value = '';
  });

  document.getElementById('btn-text').addEventListener('click', function() {
    sectionFile.style.display = 'none';
    sectionText.style.display = '';
    fileInput.required = false;
    fileInput.value = '';
    document.querySelector('.custom-file-label').innerText = 'Escolher arquivo';
    textInput.required = true;
  });

  document.getElementById('btn-copy-prompt').addEventListener('click', function() {
    var cartao = cartaoInput.dataset.nome || '';
    if (!cartaoInput.value) {
      alert('Selecione um cartão antes de copiar o prompt.');
      return;
    }
var prompt = 'Analise a fatura do cartão "' + cartao + '" (Bradesco) que estou enviando em PDF e extraia todas as transações.\n\n' +
  'Retorne SOMENTE um arquivo CSV, sem explicações adicionais, com as seguintes colunas:\n' +
  'data;descricao;valor\n\n' +
  'Regras de leitura do PDF:\n' +
  '- O PDF pode listar mais de um cartão, cada um com seu próprio bloco "Data / Histórico / R$" e linha "Total para [NOME]"; extraia os lançamentos de todos os cartões\n' +
  '- As datas vêm sem ano (DD/MM); use a data do extrato impressa no topo do PDF para inferir o ano: meses iguais ou anteriores ao mês do extrato pertencem ao mesmo ano do extrato, meses posteriores (parcelas antigas, ex: out/nov/dez) pertencem ao ano anterior\n' +
  '- Alguns lançamentos quebram em 3 linhas quando a descrição é longa (nome do estabelecimento em uma linha, "DD/MM valor" na linha seguinte, e o número da parcela tipo "2/5" na linha depois); reconstrua essas quebras como um único lançamento\n' +
  '- NÃO inclua as linhas "SALDO ANTERIOR" e "PAGTO. POR DEB EM C/C" (ou equivalentes); são apenas o saldo transportado e o pagamento da fatura anterior, não são transações do período\n\n' +
  'Regras de formatação da saída:\n' +
  '- data no formato DD/MM/YYYY\n' +
  '- descricao: nome do estabelecimento/lançamento, incluindo o número da parcela quando houver (ex: "MODA INFANTIL TIP TOP 1/3")\n' +
  '- valor: número decimal com vírgula (ex: 49,90)\n' +
  '- sinal do valor: positivo para compras; negativo para pagamentos e estornos/créditos\n' +
  '- Não inclua cabeçalho com acento ou espaço\n' +
  '- Separe os campos por ponto e vírgula\n' +
  '- Uma transação por linha\n\n' +
  'Validação obrigatória antes de responder: some os valores de cada cartão (compras positivas) e confira contra "Total para [NOME]" impresso no PDF; some os totais de todos os cartões e confira contra "Total da Fatura em Real". Corrija a extração caso haja divergência.';
    navigator.clipboard.writeText(prompt).then(function() {
      var btn = document.getElementById('btn-copy-prompt');
      var original = btn.innerHTML;
      btn.innerHTML = '<i class="fas fa-check"></i> Copiado!';
      setTimeout(function() {
        btn.innerHTML = original;
      }, 2000);
    }).catch(function() {
      alert('Não foi possível copiar. Tente manualmente.');
    });
  });
</script>

@endsection

