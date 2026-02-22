@extends('layouts.dashboard')

@section('content')
<div class="content-header">
  <div class="container">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 text-dark">Revisar Importação</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
          <li class="breadcrumb-item"><a href="{{ url('/transactions') }}">Lançamentos</a></li>
          <li class="breadcrumb-item"><a href="{{ route('transactions.import') }}">Importar CSV</a></li>
          <li class="breadcrumb-item active">Revisar</li>
        </ol>
      </div>
    </div>
  </div>
</div>

<div class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-12">

        <div class="alert alert-info alert-dismissible">
          <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
          <h5><i class="icon fas fa-info"></i> Informações da Importação</h5>
          <p><strong>Cartão:</strong> ID {{ $id_cartao }}</p>
          <p><strong>Data da Fatura:</strong> {{ $data_fatura }}</p>
          <p><strong>Total de Transações:</strong> {{ $transactions->count() }}</p>
        </div>

        @php
          $duplicadas = $transactions->filter(function($t) { return $t['is_duplicada'] ?? false; })->count();
          $valorDuplicadas = $transactions->filter(function($t) { return $t['is_duplicada_por_valor'] ?? false; })->count();
        @endphp

        @if($duplicadas > 0)
        <div class="alert alert-warning alert-dismissible">
          <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
          <h5><i class="icon fas fa-exclamation-triangle"></i> Possíveis Duplicatas!</h5>
          <p><strong>{{ $duplicadas }}</strong> transação(ões) já existem no sistema (mesma chave) e foram desmarcadas automaticamente.</p>
          <p>As linhas em <span style="background:#fff3cd;padding:2px 6px;">amarelo</span> já existem. Você pode marcá-las novamente se desejar importar mesmo assim.</p>
        </div>
        @endif

        @if($valorDuplicadas > 0)
        <div class="alert alert-dismissible" style="background-color:#fde8d0;border-color:#f59f55;color:#7a4a0a;">
          <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
          <h5><i class="icon fas fa-search"></i> Valor já lançado neste mês!</h5>
          <p><strong>{{ $valorDuplicadas }}</strong> transação(ões) possuem o mesmo valor, cartão e mês de uma transação já existente.</p>
          <p>As linhas em <span style="background:#fde8d0;padding:2px 6px;">laranja</span> estão marcadas, mas verifique se não são repetições.</p>
          <ul class="mb-0 mt-1">
            @foreach($transactions->filter(fn($t) => $t['is_duplicada_por_valor'] ?? false) as $tSimilar)
              <li>
                <strong>{{ $tSimilar['descricao_banco'] }}</strong>
                (R$ {{ number_format($tSimilar['valor'], 2, ',', '.') }})
                → encontrou: <em>{{ $tSimilar['duplicada_por_valor_descricao'] }}</em>
              </li>
            @endforeach
          </ul>
        </div>
        @endif

        <form action="{{ route('transactions.importStore') }}" method="POST">
          @csrf
          
          <input type="hidden" name="id_cartao" value="{{ $id_cartao }}">
          <input type="hidden" name="data_fatura" value="{{ $data_fatura }}">

          <div class="card card-primary">
            <div class="card-header">
              <h3 class="card-title">
                <i class="fas fa-list"></i>
                Transações para Importar
              </h3>
            </div>

            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover">
                  <thead>
                    <tr>
                      <th style="width: 3%;">
                        <input type="checkbox" id="select-all" checked>
                      </th>
                      <th style="width: 4%;">#</th>
                      <th style="width: 18%;">Descrição Original</th>
                      <th style="width: 18%;">Descrição Editável</th>
                      <th style="width: 12%;">Valor</th>
                      <th style="width: 17%;">Categoria</th>
                      <th style="width: 10%;">Tipo</th>
                      <th style="width: 18%;">Pessoa</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach ($transactions as $index => $transaction)
                    <tr class="{{ $transaction['is_duplicada'] ? 'table-warning' : ($transaction['is_duplicada_por_valor'] ? 'tr-valor-similar' : '') }}">
                      <td class="text-center">
                        <input type="checkbox" 
                               class="import-checkbox" 
                               name="transacoes[{{ $loop->index }}][importar]" 
                               value="1" 
                               {{ $transaction['is_duplicada'] ? '' : 'checked' }}>
                      </td>
                      
                      <td>
                        {{ $loop->iteration }}
                        @if($transaction['is_duplicada'])
                          <i class="fas fa-exclamation-triangle text-warning" title="Já existe no sistema (chave duplicada)"></i>
                        @elseif($transaction['is_duplicada_por_valor'])
                          <i class="fas fa-search" style="color:#e07b1a;" title="Mesmo valor já lançado neste mês para este cartão"></i>
                        @endif
                      </td>
                      
                      <td>
                        <input type="text" 
                               class="form-control form-control-sm" 
                               name="transacoes[{{ $loop->index }}][descricao_banco]" 
                               value="{{ $transaction['descricao_banco'] }}" 
                               readonly>
                      </td>
                      
                      <td>
                        <input type="text" 
                               class="form-control form-control-sm" 
                               name="transacoes[{{ $loop->index }}][descricao]" 
                               value="{{ $transaction['descricao_banco'] }}">
                      </td>
                      
                      <td>
                        <input type="number" 
                               class="form-control form-control-sm" 
                               name="transacoes[{{ $loop->index }}][valor]" 
                               value="{{ $transaction['valor'] }}" 
                               step="0.01" 
                               {{ $transaction['is_duplicada'] ? '' : 'required' }}>
                      </td>
                      
                      <td>
                        <select class="form-control form-control-sm categoria-select" 
                                name="transacoes[{{ $loop->index }}][id_categoria]"
                                data-index="{{ $loop->index }}"
                                {{ $transaction['is_duplicada'] ? '' : 'required' }}>
                          <option value="">Selecione</option>
                          @foreach ($categorias as $categoria)
                            <option value="{{ $categoria->id }}">{{ $categoria->nome }}</option>
                          @endforeach
                        </select>
                      </td>
                      
                      <td>
                        <select class="form-control form-control-sm tipo-select" 
                                name="transacoes[{{ $loop->index }}][tipo]" 
                                data-index="{{ $loop->index }}" 
                                {{ $transaction['is_duplicada'] ? '' : 'required' }}>
                          <option value="despesa" {{ $transaction['tipo_lancamento'] == 'despesa' ? 'selected' : '' }}>Despesa</option>
                          <option value="receita" {{ $transaction['tipo_lancamento'] == 'receita' ? 'selected' : '' }}>Receita</option>
                          <option value="emprestimo" {{ $transaction['tipo_lancamento'] == 'emprestimo' ? 'selected' : '' }}>Empréstimo</option>
                        </select>
                      </td>
                      
                      <td>
                        <select class="form-control form-control-sm pessoa-select" 
                                name="transacoes[{{ $loop->index }}][id_cliente]" 
                                id="pessoa-{{ $loop->index }}" 
                                style="display: none;">
                          <option value="">Selecione</option>
                          @foreach ($pessoas as $pessoa)
                            <option value="{{ $pessoa->id }}">{{ $pessoa->nome }}</option>
                          @endforeach
                        </select>
                      </td>
                      
                      <input type="hidden" name="transacoes[{{ $loop->index }}][id_cartao]" value="{{ $id_cartao }}">
                      <input type="hidden" name="transacoes[{{ $loop->index }}][data_banco]" value="{{ $transaction['data_banco'] ?? '' }}">
                      <input type="hidden" name="transacoes[{{ $loop->index }}][chave_banco]" value="{{ $transaction['chave_banco'] ?? '' }}">
                    </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            </div>

            <div class="card-footer">
              <button type="submit" class="btn btn-success">
                <i class="fas fa-save"></i> Salvar Transações
              </button>
              <a href="{{ route('transactions.import') }}" class="btn btn-default">
                <i class="fas fa-arrow-left"></i> Voltar
              </a>
            </div>
          </div>
        </form>

      </div>
    </div>
  </div>
</div>

<style>
  input[readonly] {
    background-color: #e9ecef;
  }
  .table-warning {
    background-color: #fff3cd !important;
  }
  .table-warning:hover {
    background-color: #ffe8a1 !important;
  }
  .tr-valor-similar {
    background-color: #fde8d0 !important;
  }
  .tr-valor-similar:hover {
    background-color: #fbd4b0 !important;
  }
</style>

<script>
  // Função para mostrar/esconder o select de pessoa baseado no tipo
  function togglePessoaSelect(tipoSelect) {
    const index = tipoSelect.getAttribute('data-index');
    const pessoaSelect = document.getElementById('pessoa-' + index);
    
    if (tipoSelect.value === 'emprestimo') {
      pessoaSelect.style.display = 'block';
    } else {
      pessoaSelect.style.display = 'none';
      pessoaSelect.value = '';
    }
  }

  // Adiciona listener para todos os selects de tipo
  document.querySelectorAll('.tipo-select').forEach(function(tipoSelect) {
    // Inicializa o estado correto ao carregar a página
    togglePessoaSelect(tipoSelect);
    
    // Adiciona listener para mudanças
    tipoSelect.addEventListener('change', function() {
      togglePessoaSelect(this);
    });
  });

  // Checkbox "Selecionar todos"
  document.getElementById('select-all').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.import-checkbox');
    checkboxes.forEach(function(checkbox) {
      checkbox.checked = this.checked;
      toggleCategoriaRequired(checkbox);
    }.bind(this));
  });

  // Atualiza o checkbox "select-all" se algum item for desmarcado
  document.querySelectorAll('.import-checkbox').forEach(function(checkbox) {
    checkbox.addEventListener('change', function() {
      const selectAll = document.getElementById('select-all');
      const allChecked = Array.from(document.querySelectorAll('.import-checkbox')).every(cb => cb.checked);
      selectAll.checked = allChecked;
      
      // Gerencia o atributo required da categoria baseado no checkbox
      toggleCategoriaRequired(this);
    });
  });

  // Função para gerenciar o required da categoria
  function toggleCategoriaRequired(checkbox) {
    const row = checkbox.closest('tr');
    const categoriaSelect = row.querySelector('.categoria-select');
    const valorInput = row.querySelector('input[name*="[valor]"]');
    const tipoSelect = row.querySelector('.tipo-select');
    
    if (checkbox.checked) {
      categoriaSelect.setAttribute('required', 'required');
      valorInput.setAttribute('required', 'required');
      tipoSelect.setAttribute('required', 'required');
    } else {
      categoriaSelect.removeAttribute('required');
      valorInput.removeAttribute('required');
      tipoSelect.removeAttribute('required');
    }
  }

  // Inicializa o estado correto ao carregar a página
  document.querySelectorAll('.import-checkbox').forEach(function(checkbox) {
    toggleCategoriaRequired(checkbox);
  });
</script>

@endsection
