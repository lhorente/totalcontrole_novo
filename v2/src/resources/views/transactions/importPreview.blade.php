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
          $valorAproximadoDuplicadas = $transactions->filter(function($t) { return $t['is_duplicada_por_valor_aproximado'] ?? false; })->count();
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
        </div>
        @endif

        @if($valorAproximadoDuplicadas > 0)
        <div class="alert alert-dismissible" style="background-color:#e8f4fd;border-color:#7ab8e8;color:#1a4a7a;">
          <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
          <h5><i class="icon fas fa-balance-scale"></i> Valor aproximado já lançado neste mês!</h5>
          <p><strong>{{ $valorAproximadoDuplicadas }}</strong> transação(ões) possuem valor aproximado (desconsiderando centavos) de uma transação já existente no mesmo cartão e mês.</p>
          <p>As linhas em <span style="background:#e8f4fd;padding:2px 6px;">azul</span> estão marcadas, mas verifique se não são repetições com diferença de arredondamento.</p>
          <ul class="mb-0 mt-1">
            @foreach($transactions->filter(fn($t) => $t['is_duplicada_por_valor_aproximado'] ?? false) as $tSimilar)
              <li>
                <strong>{{ $tSimilar['descricao_banco'] }}</strong>
                (R$ {{ number_format($tSimilar['valor'], 2, ',', '.') }})
                → encontrou: <em>{{ $tSimilar['duplicada_por_valor_aproximado_descricao'] }}</em>
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
                    @php
                      $sortedTransactions = $transactions->sortBy(function($t) {
                        if ($t['is_duplicada'] ?? false) return 0;
                        if ($t['is_duplicada_por_valor'] ?? false) return 1;
                        if ($t['is_duplicada_por_valor_aproximado'] ?? false) return 2;
                        return 3;
                      })->values();
                      $groups = [
                        0 => ['label' => 'Chave duplicada (já existem no sistema)', 'class' => 'table-warning', 'icon' => 'fas fa-clone'],
                        1 => ['label' => 'Mesmo valor já lançado neste mês', 'class' => 'tr-valor-similar', 'icon' => 'fas fa-search'],
                        2 => ['label' => 'Valor aproximado já lançado neste mês', 'class' => 'tr-valor-aproximado', 'icon' => 'fas fa-balance-scale'],
                        3 => ['label' => 'Novas transações', 'class' => '', 'icon' => 'fas fa-plus-circle'],
                      ];
                      $currentGroup = -1;
                    @endphp
                    @foreach ($sortedTransactions as $index => $transaction)
                    @php
                      $group = ($transaction['is_duplicada'] ?? false) ? 0
                        : (($transaction['is_duplicada_por_valor'] ?? false) ? 1
                        : (($transaction['is_duplicada_por_valor_aproximado'] ?? false) ? 2 : 3));
                    @endphp
                    @if($group !== $currentGroup)
                      @php $currentGroup = $group; @endphp
                      <tr>
                        <td colspan="8" class="text-white font-weight-bold py-1 px-3"
                            style="background-color: {{ $group === 0 ? '#b8860b' : ($group === 1 ? '#c0732a' : ($group === 2 ? '#2a72a8' : '#2d7a2d')) }}; font-size: 13px;">
                          <i class="{{ $groups[$group]['icon'] }}"></i> {{ $groups[$group]['label'] }}
                        </td>
                      </tr>
                    @endif
                    <tr class="{{ $transaction['is_duplicada'] ? 'table-warning' : ($transaction['is_duplicada_por_valor'] ? 'tr-valor-similar' : ($transaction['is_duplicada_por_valor_aproximado'] ? 'tr-valor-aproximado' : '')) }}">
                      <td class="text-center">
                        <input type="checkbox" 
                               class="import-checkbox" 
                               name="transacoes[{{ $loop->index }}][importar]" 
                               value="1" 
                               {{ $transaction['is_duplicada'] || $transaction['is_duplicada_por_valor'] ? '' : 'checked' }}>
                      </td>
                      
                      <td>
                        {{ $loop->iteration }}
                      </td>
                      
                      <td>
                        <input type="text" 
                               class="form-control form-control-sm" 
                               name="transacoes[{{ $loop->index }}][descricao_banco]" 
                               value="{{ $transaction['descricao_banco'] }}" 
                               readonly>

                          @if($transaction['installment'] ?? null)
                            @php $inst = $transaction['installment']; @endphp
                            <span class="badge badge-info mt-1">
                              Parcela {{ $inst['current'] }}/{{ $inst['total'] }}
                            </span>
                          @endif

                          @if($transaction['is_duplicada'])
                            <span class="">Já existe no sistema (chave duplicada)</span>
                          @elseif($transaction['is_duplicada_por_valor'])
                            <span class="">Mesmo valor já lançado neste mês para este cartão: {{ $transaction['duplicada_por_valor_descricao'] }}</span>
                          @elseif($transaction['is_duplicada_por_valor_aproximado'])
                            <span class="">Valor aproximado (sem centavos) já lançado neste mês para este cartão: {{ $transaction['duplicada_por_valor_aproximado_descricao'] }}</span>
                          @endif
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
                                data-key="{{ $transaction['_key'] ?? $loop->index }}"
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
                                data-key="{{ $transaction['_key'] ?? $loop->index }}"
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

          {{-- ============================================================ --}}
          {{-- Seção de Parcelas Futuras                                     --}}
          {{-- ============================================================ --}}
          @if(!empty($installmentGroups))
          <div class="card card-warning mt-4">
            <div class="card-header">
              <h3 class="card-title">
                <i class="fas fa-layer-group"></i>
                Parcelas Futuras Detectadas
              </h3>
              <div class="card-tools">
                <span class="badge badge-light">
                  {{ collect($installmentGroups)->sum(fn($g) => count($g['futures'])) }} parcela(s) futura(s) em
                  {{ count($installmentGroups) }} série(s)
                </span>
              </div>
            </div>
            <div class="card-body p-0">
              <div class="alert alert-warning m-3 mb-0">
                <i class="fas fa-info-circle"></i>
                As parcelas abaixo foram detectadas automaticamente. Marque as que deseja criar nos meses seguintes.
                A categoria e tipo serão copiados da transação original ao confirmar.
              </div>

              @php
                $pf_total_dup       = collect($installmentGroups)->flatMap(fn($g) => $g['futures'])->filter(fn($f) => $f['is_duplicada'] ?? false)->count();
                $pf_total_dupVal    = collect($installmentGroups)->flatMap(fn($g) => $g['futures'])->filter(fn($f) => $f['is_duplicada_por_valor'] ?? false)->count();
                $pf_total_dupAprox  = collect($installmentGroups)->flatMap(fn($g) => $g['futures'])->filter(fn($f) => $f['is_duplicada_por_valor_aproximado'] ?? false)->count();
              @endphp

              @if($pf_total_dup > 0)
              <div class="alert alert-warning alert-dismissible m-3 mb-0">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="icon fas fa-clone"></i>
                <strong>{{ $pf_total_dup }}</strong> parcela(s) futura(s) já existem no sistema (chave duplicada) e foram desmarcadas.
              </div>
              @endif

              @if($pf_total_dupVal > 0)
              <div class="alert alert-dismissible m-3 mb-0" style="background-color:#fde8d0;border-color:#f59f55;color:#7a4a0a;">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="icon fas fa-search"></i>
                <strong>{{ $pf_total_dupVal }}</strong> parcela(s) futura(s) possuem o mesmo valor de transação já existente no mesmo mês e foram desmarcadas.
              </div>
              @endif

              @if($pf_total_dupAprox > 0)
              <div class="alert alert-dismissible m-3 mb-0" style="background-color:#e8f4fd;border-color:#7ab8e8;color:#1a4a7a;">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="icon fas fa-balance-scale"></i>
                <strong>{{ $pf_total_dupAprox }}</strong> parcela(s) futura(s) possuem valor aproximado de transação já existente no mesmo mês. Verifique antes de criar.
              </div>
              @endif

              @php $pfIndex = 0; @endphp
              @foreach($installmentGroups as $srcIdx => $group)
              <div class="p-3 border-bottom">
                <div class="d-flex align-items-center mb-2">
                  <strong class="mr-2">{{ $group['source_desc'] }}</strong>
                  <span class="badge badge-secondary mr-2">{{ $group['current'] }}/{{ $group['total'] }}</span>
                  <span class="text-muted small">R$ {{ number_format($group['valor'], 2, ',', '.') }}</span>
                  <button type="button"
                          class="btn btn-xs btn-outline-info ml-auto toggle-group-btn"
                          data-group="{{ $srcIdx }}">
                    Selecionar todas
                  </button>
                </div>

                <table class="table table-sm table-bordered mb-0">
                  <thead class="thead-light">
                    <tr>
                      <th style="width:3%"></th>
                      <th style="width:5%">Parcela</th>
                      <th>Descrição</th>
                      <th style="width:12%">Valor</th>
                      <th style="width:14%">Data</th>
                      <th style="width:18%">Categoria</th>
                      <th style="width:10%">Tipo</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($group['futures'] as $future)
                    @php
                      $pf_dup       = $future['is_duplicada'] ?? false;
                      $pf_dupVal    = $future['is_duplicada_por_valor'] ?? false;
                      $pf_dupValApr = $future['is_duplicada_por_valor_aproximado'] ?? false;
                      $pf_trClass   = $pf_dup ? 'table-warning'
                                    : ($pf_dupVal    ? 'tr-valor-similar'
                                    : ($pf_dupValApr ? 'tr-valor-aproximado' : ''));
                    @endphp
                    <tr class="{{ $pf_trClass }}">
                      <td class="text-center">
                        <input type="checkbox"
                               class="pf-checkbox pf-group-{{ $srcIdx }}"
                               name="parcelas_futuras[{{ $pfIndex }}][criar]"
                               value="1"
                               {{ ($pf_dup || $pf_dupVal) ? '' : 'checked' }}>
                      </td>
                      <td class="text-center">
                        <span class="badge badge-info">{{ $future['parcel'] }}/{{ $future['total'] }}</span>
                      </td>
                      <td>
                        <input type="text"
                               class="form-control form-control-sm pf-descricao"
                               name="parcelas_futuras[{{ $pfIndex }}][descricao]"
                               value="{{ $future['descricao'] }}">
                        <input type="hidden"
                               name="parcelas_futuras[{{ $pfIndex }}][descricao_banco]"
                               value="{{ $future['descricao_banco'] }}">
                        @if($pf_dup)
                          <span style="font-size:12px;">Já existe no sistema (chave duplicada)</span>
                        @elseif($pf_dupVal)
                          <span style="font-size:12px;">Mesmo valor já lançado neste mês: {{ $future['duplicada_por_valor_descricao'] }}</span>
                        @elseif($pf_dupValApr)
                          <span style="font-size:12px;">Valor aproximado já lançado neste mês: {{ $future['duplicada_por_valor_aproximado_descricao'] }}</span>
                        @endif
                      </td>
                      <td>
                        <input type="number"
                               class="form-control form-control-sm"
                               name="parcelas_futuras[{{ $pfIndex }}][valor]"
                               value="{{ $future['valor'] }}"
                               step="0.01">
                      </td>
                      <td>
                        <input type="date"
                               class="form-control form-control-sm"
                               name="parcelas_futuras[{{ $pfIndex }}][data]"
                               value="{{ $future['data'] }}">
                      </td>
                      <td>
                        <select class="form-control form-control-sm pf-categoria"
                                name="parcelas_futuras[{{ $pfIndex }}][id_categoria]"
                                data-source-index="{{ $srcIdx }}">
                          <option value="">Selecione</option>
                          @foreach($categorias as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->nome }}</option>
                          @endforeach
                        </select>
                      </td>
                      <td>
                        <select class="form-control form-control-sm pf-tipo"
                                name="parcelas_futuras[{{ $pfIndex }}][tipo]"
                                data-source-index="{{ $srcIdx }}">
                          <option value="despesa">Despesa</option>
                          <option value="lucro">Receita</option>
                          <option value="emprestimo">Empréstimo</option>
                        </select>
                      </td>
                      <input type="hidden" name="parcelas_futuras[{{ $pfIndex }}][id_cartao]" value="{{ $future['id_cartao'] }}">
                    </tr>
                    @php $pfIndex++; @endphp
                    @endforeach
                  </tbody>
                </table>
              </div>
              @endforeach
            </div>
            <div class="card-footer">
              <small class="text-muted">
                <i class="fas fa-info-circle"></i>
                As parcelas desmarcadas não serão criadas. Você pode importá-las manualmente mais tarde.
              </small>
            </div>
          </div>
          @endif

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
  .tr-warning span {
    font-size: 12px;
  }
  .table-warning:hover {
    background-color: #ffe8a1 !important;
  }
  .tr-valor-similar {
    background-color: #fde8d0 !important;
  }
  .tr-valor-similar span {
    font-size: 12px;
  }
  .tr-valor-similar:hover {
    background-color: #fbd4b0 !important;
  }
  .tr-valor-aproximado {
    background-color: #e8f4fd !important;
  }
  .tr-valor-aproximado span {
    font-size: 12px;
  }
  .tr-valor-aproximado:hover {
    background-color: #cce6f8 !important;
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

  // -----------------------------------------------------------------------
  // Parcelas Futuras: Toggle "Selecionar todas" por grupo
  // -----------------------------------------------------------------------
  @if(!empty($installmentGroups))
  document.querySelectorAll('.toggle-group-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
      const groupId = this.getAttribute('data-group');
      const checkboxes = document.querySelectorAll('.pf-group-' + groupId);
      const allChecked = Array.from(checkboxes).every(cb => cb.checked);
      checkboxes.forEach(cb => cb.checked = !allChecked);
      this.textContent = allChecked ? 'Selecionar todas' : 'Desmarcar todas';
    });
  });

  // -----------------------------------------------------------------------
  // Parcelas Futuras: sincroniza categoria e tipo com a transação de origem
  // Usa data-key para ligar o select da tabela principal aos selects futuros
  // -----------------------------------------------------------------------
  function syncPfByKey(sourceKey) {
    var catSrc  = document.querySelector('.categoria-select[data-key="' + sourceKey + '"]');
    var tipoSrc = document.querySelector('.tipo-select[data-key="' + sourceKey + '"]');

    document.querySelectorAll('.pf-categoria[data-source-index="' + sourceKey + '"]')
      .forEach(function(sel) {
        if (catSrc) sel.value = catSrc.value;
      });

    document.querySelectorAll('.pf-tipo[data-source-index="' + sourceKey + '"]')
      .forEach(function(sel) {
        if (tipoSrc) sel.value = tipoSrc.value;
      });
  }

  // Quando o usuário muda categoria/tipo de uma transação de origem, sincroniza
  document.querySelectorAll('.categoria-select, .tipo-select').forEach(function(sel) {
    sel.addEventListener('change', function() {
      var key = this.getAttribute('data-key');
      if (key !== null) syncPfByKey(key);
    });
  });
  @endif
</script>

@endsection
