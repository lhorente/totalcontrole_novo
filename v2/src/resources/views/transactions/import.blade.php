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
                         data-nome="{{ $cartao->descricao }}"
                         data-dia-vencimento="{{ $cartao->dia_vencimento }}">
                      <i class="fas fa-credit-card mr-1"></i> {{ $cartao->descricao }}
                    </div>
                  @endforeach
                </div>
                <small class="form-text text-muted">Clique no cartão desejado.</small>
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

              {{-- Data da Fatura --}}
              <div class="form-group">
                <label>Fatura</label>
                <input type="hidden" id="data_fatura" name="data_fatura">
                @php
                  $meses = [
                    \Carbon\Carbon::now()->subMonth(),
                    \Carbon\Carbon::now(),
                    \Carbon\Carbon::now()->addMonth(),
                  ];
                  $nomeMeses = ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
                @endphp
                <div class="d-flex flex-wrap" style="gap:.5rem;" id="mes-picker">
                  @foreach($meses as $mes)
                    <div class="mes-card border rounded py-2 px-3"
                         style="cursor:pointer; user-select:none; transition: background .15s, border-color .15s;"
                         data-ano="{{ $mes->year }}"
                         data-mes="{{ $mes->month }}">
                      <i class="fas fa-calendar-alt mr-1"></i>
                      {{ $nomeMeses[$mes->month - 1] }} {{ $mes->year }}
                    </div>
                  @endforeach
                  <div class="border rounded py-2 px-3"
                       style="cursor:pointer; user-select:none; transition: background .15s, border-color .15s;"
                       id="btn-mes-manual">
                    <i class="fas fa-pencil-alt mr-1"></i> Outra data
                  </div>
                </div>
                <div id="data-manual-wrapper" style="display:none;" class="mt-2">
                  <input type="date" class="form-control" id="data_fatura_manual" style="max-width:200px;">
                </div>
                <small class="form-text text-muted">Selecione o mês da fatura.</small>
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

@php
  $subcartoesPorCartaoData = $cartoes->mapWithKeys(function ($c) {
    $children = $c->children->map(function ($sub) {
      return [
        'descricao' => $sub->descricao,
        'ultimos_digitos' => $sub->ultimos_digitos,
        'categoria_padrao_id' => $sub->defaultCategory->id ?? null,
        'categoria_padrao_nome' => $sub->defaultCategory->nome ?? null,
      ];
    })->values();
    return [$c->id => $children];
  });
@endphp
<script>
  var categoriasPrompt = @json($categorias->map(fn($c) => $c->id . ' - ' . $c->nome)->join("\n"));
  var subcartoesPorCartao = @json($subcartoesPorCartaoData);

  var sectionFile     = document.getElementById('section-file');
  var sectionText     = document.getElementById('section-text');
  var fileInput       = document.getElementById('file');
  var textInput       = document.getElementById('csv_content');
  var cartaoInput     = document.getElementById('id_cartao');
  var dataFaturaInput = document.getElementById('data_fatura');
  var diaVencimento   = null;

  // Compõe a data da fatura a partir de ano, mês e dia de vencimento do cartão
  function setDataFatura(ano, mes) {
    var dia = diaVencimento || 1;
    var maxDia = new Date(ano, mes, 0).getDate(); // último dia do mês
    dia = Math.min(dia, maxDia);
    dataFaturaInput.value = ano + '-' + String(mes).padStart(2, '0') + '-' + String(dia).padStart(2, '0');
  }

  // Retorna o card de mês atualmente selecionado (se houver)
  function selectedMesCard() {
    return document.querySelector('.mes-card[data-selected="1"]');
  }

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
      cartaoInput.value        = card.dataset.id;
      cartaoInput.dataset.nome = card.dataset.nome;
      diaVencimento            = parseInt(card.dataset.diaVencimento) || null;
      // Recalcula a data da fatura se um mês já estiver selecionado
      var sel = selectedMesCard();
      if (sel) setDataFatura(parseInt(sel.dataset.ano), parseInt(sel.dataset.mes));
    });
  });

  // Mês picker
  document.querySelectorAll('.mes-card').forEach(function(card) {
    card.addEventListener('click', function() {
      // Desmarca todos os meses e oculta manual
      document.querySelectorAll('.mes-card').forEach(function(c) {
        c.style.backgroundColor = '';
        c.style.borderColor     = '';
        c.style.color           = '';
        c.style.fontWeight      = '';
        c.dataset.selected      = '';
      });
      var manualBtn = document.getElementById('btn-mes-manual');
      manualBtn.style.backgroundColor = '';
      manualBtn.style.borderColor     = '';
      manualBtn.style.color           = '';
      manualBtn.style.fontWeight      = '';
      document.getElementById('data-manual-wrapper').style.display = 'none';

      card.style.backgroundColor = '#e8f4fd';
      card.style.borderColor     = '#17a2b8';
      card.style.color           = '#17a2b8';
      card.style.fontWeight      = '600';
      card.dataset.selected      = '1';
      setDataFatura(parseInt(card.dataset.ano), parseInt(card.dataset.mes));
    });
  });

  // Botão "Outra data"
  document.getElementById('btn-mes-manual').addEventListener('click', function() {
    document.querySelectorAll('.mes-card').forEach(function(c) {
      c.style.backgroundColor = '';
      c.style.borderColor     = '';
      c.style.color           = '';
      c.style.fontWeight      = '';
      c.dataset.selected      = '';
    });
    this.style.backgroundColor = '#e8f4fd';
    this.style.borderColor     = '#17a2b8';
    this.style.color           = '#17a2b8';
    this.style.fontWeight      = '600';
    document.getElementById('data-manual-wrapper').style.display = '';
    dataFaturaInput.value = document.getElementById('data_fatura_manual').value || '';
  });

  document.getElementById('data_fatura_manual').addEventListener('change', function() {
    dataFaturaInput.value = this.value;
  });

  // Valida cartão e data no submit
  document.getElementById('import-form').addEventListener('submit', function(e) {
    if (!cartaoInput.value) {
      e.preventDefault();
      alert('Selecione um cartão antes de avançar.');
      document.getElementById('cartao-picker').scrollIntoView({ behavior: 'smooth', block: 'center' });
      return;
    }
    if (!dataFaturaInput.value) {
      e.preventDefault();
      alert('Selecione o mês da fatura antes de avançar.');
      document.getElementById('mes-picker').scrollIntoView({ behavior: 'smooth', block: 'center' });
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
var prompt;
    var subcartoes = subcartoesPorCartao[cartaoInput.value] || [];
    if (subcartoes.length > 0) {
      var listaSubcartoes = subcartoes.map(function(s) {
        var linha = '- ' + s.descricao + (s.ultimos_digitos ? ' (final ' + s.ultimos_digitos + ')' : ' (final ainda não cadastrado)');
        if (s.categoria_padrao_id) {
          linha += ' → categoria padrão: ' + s.categoria_padrao_id + ' - ' + s.categoria_padrao_nome;
        }
        return linha;
      }).join('\n');
      var temCategoriaPadrao = subcartoes.some(function(s) { return s.categoria_padrao_id; });
      prompt = 'Analise a fatura do cartão "' + cartao + '" que estou enviando em PDF e extraia todas as transações, identificando também qual cartão virtual foi usado em cada uma pelos últimos 4 dígitos — o PDF agrupa os lançamentos por cartão virtual, cada bloco mostrando os últimos 4 dígitos daquele cartão.\n\n' +
        'Retorne SOMENTE um arquivo CSV, sem explicações adicionais, com as seguintes colunas:\n' +
        'data;descricao;valor;categoria;ultimos_digitos\n\n' +
        'Cartões virtuais já cadastrados neste cartão físico (use como referência, mas confie no que estiver escrito no PDF):\n' +
        listaSubcartoes + '\n\n' +
        'Regras de leitura do PDF:\n' +
        '- O PDF agrupa as transações por cartão virtual, cada grupo trazendo os últimos 4 dígitos daquele cartão\n' +
        '- Preencha ultimos_digitos com os 4 dígitos do grupo a que a transação pertence; se não conseguir identificar com certeza, deixe o campo vazio\n' +
        '- As datas vêm sem ano (DD/MM); use a data do extrato impressa no topo do PDF para inferir o ano: meses iguais ou anteriores ao mês do extrato pertencem ao mesmo ano do extrato, meses posteriores (parcelas antigas) pertencem ao ano anterior\n' +
        '- Alguns lançamentos quebram em várias linhas quando a descrição é longa; reconstrua essas quebras como um único lançamento\n' +
        '- NÃO inclua linhas de saldo anterior ou pagamento da fatura anterior; são apenas o saldo transportado, não transações do período\n\n' +
        'Regras de formatação da saída:\n' +
        '- data no formato DD/MM/YYYY\n' +
        '- descricao: nome do estabelecimento/lançamento, incluindo o número da parcela quando houver\n' +
        '- valor: número decimal com vírgula (ex: 49,90)\n' +
        '- sinal do valor: positivo para compras; negativo para pagamentos e estornos/créditos\n' +
        '- ultimos_digitos: 4 dígitos numéricos do cartão virtual, ou vazio se não identificado\n' +
        '- Não inclua cabeçalho com acento ou espaço\n' +
        '- Separe os campos por ponto e vírgula\n' +
        '- Uma transação por linha\n\n' +
        (temCategoriaPadrao
          ? '- Na coluna categoria, siga esta prioridade: (1) se você identificou o cartão virtual da transação (ultimos_digitos preenchido) E esse cartão tem "categoria padrão" na lista acima, use o ID dessa categoria padrão diretamente, sem tentar inferir outra; (2) caso contrário (cartão não identificado ou sem categoria padrão cadastrada), infira o ID a partir da relação de categorias do sistema abaixo:\n\n'
          : '- Na coluna categoria, adicione o ID da categoria, com base na relação de categorias abaixo:\n\n') +
        categoriasPrompt + '\n\n' +
        'Validação obrigatória antes de responder: some os valores de todas as transações e confira contra o "Total da Fatura" impresso no PDF. Corrija a extração caso haja divergência.';
    } else if (/nubank/i.test(cartao)) {
      prompt = 'Analise o arquivo OFX da fatura do cartão "' + cartao + '" (Nubank) que estou enviando e extraia todas as transações.\n\n' +
        'Retorne SOMENTE um arquivo CSV, sem explicações adicionais, com as seguintes colunas:\n' +
        'data;descricao;valor;categoria\n\n' +
        'Regras de leitura do OFX:\n' +
        '- Cada transação está em um bloco <STMTTRN>...</STMTTRN>, dentro de <BANKTRANLIST>; pode haver mais de um bloco <CCSTMTTRNRS> caso o arquivo traga mais de um cartão/conta — extraia os lançamentos de todos eles\n' +
        '- A data já vem completa e correta em <DTPOSTED> no formato AAAAMMDD (ex: 20260719); não é necessário inferir o ano\n' +
        '- A descrição do lançamento está em <MEMO>; use o texto como está, sem tentar completar nomes truncados\n' +
        '- O tipo em <TRNTYPE> indica DEBIT (compra) ou CREDIT (pagamento/estorno); <TRNAMT> já vem assinado pelo banco (negativo para DEBIT, positivo para CREDIT) — não são 3 linhas quebradas como no PDF, cada <STMTTRN> já é um lançamento completo\n' +
        '- NÃO inclua lançamentos do tipo CREDIT cujo <MEMO> seja "Pagamento recebido" (ou equivalente); é apenas o pagamento da fatura anterior, não uma transação do período\n' +
        '- Estornos/créditos legítimos (ex.: devolução de uma compra) devem ser incluídos normalmente\n\n' +
        'Regras de formatação da saída:\n' +
        '- data no formato DD/MM/YYYY\n' +
        '- descricao: texto do <MEMO>, incluindo o número da parcela quando houver\n' +
        '- valor: número decimal com vírgula (ex: 49,90)\n' +
        '- sinal do valor: positivo para compras (DEBIT); negativo para pagamentos e estornos/créditos (CREDIT) — inverta o sinal original do OFX, que segue a convenção contábil padrão (oposto da que usamos aqui)\n' +
        '- Não inclua cabeçalho com acento ou espaço\n' +
        '- Separe os campos por ponto e vírgula\n' +
        '- Uma transação por linha\n\n' +
        '- Na coluna categoria, adicione o ID da categoria, com base na relação de categorias abaixo:\n\n' +
        categoriasPrompt + '\n\n' +
        'Validação obrigatória antes de responder: some os valores de compra (DEBIT) e confira contra o <LEDGERBAL><BALAMT> do OFX (em módulo, desconsiderando pagamentos e estornos já excluídos); caso haja mais de um <CCSTMTRS>, some os totais de todos os cartões/contas e confira contra o total geral. Corrija a extração caso haja divergência.';
    } else {
      prompt = 'Analise a fatura do cartão "' + cartao + '" (Bradesco) que estou enviando em PDF e extraia todas as transações.\n\n' +
        'Retorne SOMENTE um arquivo CSV, sem explicações adicionais, com as seguintes colunas:\n' +
        'data;descricao;valor;categoria\n\n' +
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
        '- Na coluna categoria, adicione o ID da categoria, com base na relação de categorias abaixo:\n\n' +
        categoriasPrompt + '\n\n' +
        'Validação obrigatória antes de responder: some os valores de cada cartão (compras positivas) e confira contra "Total para [NOME]" impresso no PDF; some os totais de todos os cartões e confira contra "Total da Fatura em Real". Corrija a extração caso haja divergência.';
    }
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

