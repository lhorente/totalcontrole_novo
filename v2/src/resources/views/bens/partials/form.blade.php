@php
  $tipoLabels = ['casa'=>'Imóvel (casa/apartamento)','carro'=>'Carro','outro'=>'Outro'];
@endphp

@if ($bem)
  <input type="hidden" name="id" value="{{ $bem->id }}">
@endif

<div class="row">
  <div class="col-sm-6">
    <div class="form-group">
      <label for="nome">Nome</label>
      <input type="text" id="nome" name="nome" class="form-control @error('nome') is-invalid @enderror"
             placeholder="ex: Honda Civic 2019, Casa — Residência principal" value="{{ old('nome', $bem->nome ?? '') }}">
      @error('nome')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
  </div>
  <div class="col-sm-6">
    <div class="form-group">
      <label for="tipo">Tipo</label>
      <select id="tipo" name="tipo" class="form-control @error('tipo') is-invalid @enderror">
        @foreach ($tipoLabels as $value => $label)
          <option value="{{ $value }}" {{ old('tipo', $bem->tipo ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
      </select>
      @error('tipo')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
  </div>
</div>

<div class="form-group">
  <label for="detalhe">Detalhe <small class="text-muted">(opcional)</small></label>
  <input type="text" id="detalhe" name="detalhe" class="form-control @error('detalhe') is-invalid @enderror"
         placeholder="ex: placa, km atual, endereço resumido" value="{{ old('detalhe', $bem->detalhe ?? '') }}">
  @error('detalhe')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="form-group form-check">
  <input type="hidden" name="ativo" value="0">
  <input type="checkbox" id="ativo" name="ativo" value="1" class="form-check-input"
         {{ old('ativo', $bem->ativo ?? true) ? 'checked' : '' }}>
  <label for="ativo" class="form-check-label">Bem ativo</label>
  <small class="text-muted d-block">Desmarque para arquivar um bem que você não usa mais (ex: carro vendido), sem perder o histórico.</small>
</div>
