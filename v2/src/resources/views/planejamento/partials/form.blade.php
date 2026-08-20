@php
  $tipoLabels = ['manutencao'=>'Manutenção','compra'=>'Compra'];
  $prioridadeLabels = ['necessidade'=>'Preciso (necessidade)','desejo'=>'Quero (desejo)'];
  $statusLabels = ['planejado'=>'Planejado','agendado'=>'Agendado','concluido'=>'Concluído','cancelado'=>'Cancelado'];
  $categoriaSugestoes = ['Segurança','Estrutura','Estética','Mecânica','Eletrônico','Outros'];
  $plan = $item?->planejamento;
@endphp

@if ($item)
  <input type="hidden" name="id" value="{{ $item->id }}">
@endif

<div class="row">
  <div class="col-sm-3">
    <div class="form-group">
      <label for="tipo">Tipo</label>
      <select id="tipo" name="tipo" class="form-control @error('tipo') is-invalid @enderror">
        @foreach ($tipoLabels as $value => $label)
          <option value="{{ $value }}" {{ old('tipo', $item->tipo ?? 'manutencao') === $value ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
      </select>
      @error('tipo')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
  </div>
  <div class="col-sm-9">
    <div class="form-group">
      <label for="titulo">Título</label>
      <input type="text" id="titulo" name="titulo" class="form-control @error('titulo') is-invalid @enderror"
             placeholder="ex: Instalar alarme no carro, Trocar telhado" value="{{ old('titulo', $item->titulo ?? '') }}">
      @error('titulo')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
  </div>
</div>

<div class="row">
  <div class="col-sm-6">
    <div class="form-group">
      <label for="id_bem">Bem vinculado <small class="text-muted">(opcional)</small></label>
      <select id="id_bem" name="id_bem" class="form-control @error('id_bem') is-invalid @enderror">
        <option value="">Nenhum (item genérico)</option>
        @foreach ($bens as $bem)
          <option value="{{ $bem->id }}" {{ old('id_bem', $plan->id_bem ?? '') == $bem->id ? 'selected' : '' }}>
            {{ $bem->nome }}
          </option>
        @endforeach
      </select>
      @error('id_bem')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
  </div>
  <div class="col-sm-6">
    <div class="form-group">
      <label for="categoria">Categoria <small class="text-muted">(opcional)</small></label>
      <input type="text" id="categoria" name="categoria" list="categoria-sugestoes" class="form-control @error('categoria') is-invalid @enderror"
             placeholder="ex: Segurança, Estrutura..." value="{{ old('categoria', $plan->categoria ?? '') }}">
      <datalist id="categoria-sugestoes">
        @foreach ($categoriaSugestoes as $sugestao)
          <option value="{{ $sugestao }}">
        @endforeach
      </datalist>
      @error('categoria')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
  </div>
</div>

<div class="row">
  <div class="col-sm-4">
    <div class="form-group">
      <label for="prioridade">Prioridade</label>
      <select id="prioridade" name="prioridade" class="form-control @error('prioridade') is-invalid @enderror">
        @foreach ($prioridadeLabels as $value => $label)
          <option value="{{ $value }}" {{ old('prioridade', $plan->prioridade ?? 'necessidade') === $value ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
      </select>
      @error('prioridade')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
  </div>
  <div class="col-sm-4">
    <div class="form-group">
      <label for="data_vencimento">Data prevista <small class="text-muted">(opcional)</small></label>
      <input type="date" id="data_vencimento" name="data_vencimento" class="form-control @error('data_vencimento') is-invalid @enderror"
             value="{{ old('data_vencimento', optional($item->data_vencimento ?? null)->format('Y-m-d')) }}">
      <small class="text-muted">Deixe em branco se ainda não sabe quando vai fazer/comprar.</small>
      @error('data_vencimento')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
  </div>
  <div class="col-sm-4">
    <div class="form-group">
      <label for="valor">Valor estimado <small class="text-muted">(opcional)</small></label>
      <input type="number" step="0.01" min="0" id="valor" name="valor" class="form-control @error('valor') is-invalid @enderror"
             placeholder="0,00" value="{{ old('valor', $item->valor ?? '') }}">
      @error('valor')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
  </div>
</div>

<div class="row">
  <div class="col-sm-4">
    <div class="form-group">
      <label for="status">Status</label>
      <select id="status" name="status" class="form-control @error('status') is-invalid @enderror">
        @foreach ($statusLabels as $value => $label)
          <option value="{{ $value }}" {{ old('status', $item->status ?? 'planejado') === $value ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
      </select>
      @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
  </div>
</div>

<div id="bloco-recorrencia" class="form-group border rounded p-3 bg-light">
  <div class="custom-control custom-checkbox">
    <input type="hidden" name="recorrente" value="0">
    <input type="checkbox" id="recorrente" name="recorrente" value="1" class="custom-control-input"
           {{ old('recorrente', $plan->recorrente ?? false) ? 'checked' : '' }}>
    <label for="recorrente" class="custom-control-label">Esta manutenção se repete periodicamente</label>
  </div>
  <small class="text-muted d-block mt-1">Ao marcar este item como concluído, a próxima ocorrência é criada automaticamente.</small>

  <div id="campos-recorrencia" class="row mt-2">
    <div class="col-sm-3">
      <label for="recorrencia_intervalo">A cada</label>
      <input type="number" min="1" id="recorrencia_intervalo" name="recorrencia_intervalo"
             class="form-control @error('recorrencia_intervalo') is-invalid @enderror"
             value="{{ old('recorrencia_intervalo', $plan->recorrencia_intervalo ?? 6) }}">
      @error('recorrencia_intervalo')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-sm-3">
      <label for="recorrencia_unidade">Período</label>
      <select id="recorrencia_unidade" name="recorrencia_unidade" class="form-control @error('recorrencia_unidade') is-invalid @enderror">
        <option value="meses" {{ old('recorrencia_unidade', $plan->recorrencia_unidade ?? 'meses') === 'meses' ? 'selected' : '' }}>meses</option>
        <option value="anos" {{ old('recorrencia_unidade', $plan->recorrencia_unidade ?? 'meses') === 'anos' ? 'selected' : '' }}>anos</option>
      </select>
      @error('recorrencia_unidade')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
  </div>
</div>

<div class="form-group">
  <label for="observacoes">Observações <small class="text-muted">(opcional)</small></label>
  <textarea id="observacoes" name="observacoes" rows="3" class="form-control @error('observacoes') is-invalid @enderror">{{ old('observacoes', $plan->observacoes ?? '') }}</textarea>
  @error('observacoes')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<script>
(function(){
  var tipoSelect = document.getElementById('tipo');
  var blocoRecorrencia = document.getElementById('bloco-recorrencia');
  var recorrenteCheckbox = document.getElementById('recorrente');
  var camposRecorrencia = document.getElementById('campos-recorrencia');

  function syncTipo(){
    blocoRecorrencia.style.display = tipoSelect.value === 'manutencao' ? '' : 'none';
  }
  function syncRecorrencia(){
    camposRecorrencia.style.display = recorrenteCheckbox.checked ? '' : 'none';
  }

  tipoSelect.addEventListener('change', syncTipo);
  recorrenteCheckbox.addEventListener('change', syncRecorrencia);

  syncTipo();
  syncRecorrencia();
})();
</script>
