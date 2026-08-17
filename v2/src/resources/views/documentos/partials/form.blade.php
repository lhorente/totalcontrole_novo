@php
  $tipoLabels = ['seguro'=>'Seguro','ipva'=>'IPVA','cnh'=>'CNH','garantia'=>'Garantia','contrato'=>'Contrato','outro'=>'Outro'];
  $statusLabels = ['ativo'=>'Ativo','concluido'=>'Concluído','cancelado'=>'Cancelado','vencido'=>'Vencido'];
  $doc = $documento?->documento;
@endphp

@if ($documento)
  <input type="hidden" name="id" value="{{ $documento->id }}">
@endif

<div class="row">
  <div class="col-sm-6">
    <div class="form-group">
      <label for="titulo">Título</label>
      <input type="text" id="titulo" name="titulo" class="form-control @error('titulo') is-invalid @enderror"
             placeholder="ex: Seguro do carro" value="{{ old('titulo', $documento->titulo ?? '') }}">
      @error('titulo')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
  </div>
  <div class="col-sm-6">
    <div class="form-group">
      <label for="tipo">Tipo</label>
      <select id="tipo" name="tipo" class="form-control @error('tipo') is-invalid @enderror">
        @foreach ($tipoLabels as $value => $label)
          <option value="{{ $value }}" {{ old('tipo', $documento->tipo ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
      </select>
      @error('tipo')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
  </div>
</div>

<div class="row">
  <div class="col-sm-4">
    <div class="form-group">
      <label for="data_evento">Data de emissão</label>
      <input type="date" id="data_evento" name="data_evento" class="form-control @error('data_evento') is-invalid @enderror"
             value="{{ old('data_evento', optional($documento->data_evento ?? null)->format('Y-m-d')) }}">
      @error('data_evento')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
  </div>
  <div class="col-sm-4">
    <div class="form-group">
      <label for="data_vencimento">Data de vencimento <small class="text-muted">(opcional)</small></label>
      <input type="date" id="data_vencimento" name="data_vencimento" class="form-control @error('data_vencimento') is-invalid @enderror"
             value="{{ old('data_vencimento', optional($documento->data_vencimento ?? null)->format('Y-m-d')) }}">
      @error('data_vencimento')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
  </div>
  <div class="col-sm-4">
    <div class="form-group">
      <label for="valor">Valor <small class="text-muted">(opcional)</small></label>
      <input type="number" step="0.01" min="0" id="valor" name="valor" class="form-control @error('valor') is-invalid @enderror"
             placeholder="0,00" value="{{ old('valor', $documento->valor ?? '') }}">
      @error('valor')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
  </div>
</div>

<div class="row">
  <div class="col-sm-6">
    <div class="form-group">
      <label for="id_cliente">Pessoa <small class="text-muted">(opcional)</small></label>
      <select id="id_cliente" name="id_cliente" class="form-control @error('id_cliente') is-invalid @enderror">
        <option value="">Nenhuma</option>
        @foreach ($contacts as $contact)
          <option value="{{ $contact->id }}" {{ old('id_cliente', $documento->id_cliente ?? '') == $contact->id ? 'selected' : '' }}>
            {{ $contact->nome }}
          </option>
        @endforeach
      </select>
      @error('id_cliente')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
  </div>
  <div class="col-sm-6">
    <div class="form-group">
      <label for="status">Status</label>
      <select id="status" name="status" class="form-control @error('status') is-invalid @enderror">
        @foreach ($statusLabels as $value => $label)
          <option value="{{ $value }}" {{ old('status', $documento->status ?? 'ativo') === $value ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
      </select>
      @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
  </div>
</div>

<div class="row">
  <div class="col-sm-6">
    <div class="form-group">
      <label for="numero_documento">Número do documento <small class="text-muted">(opcional)</small></label>
      <input type="text" id="numero_documento" name="numero_documento" class="form-control @error('numero_documento') is-invalid @enderror"
             placeholder="ex: nº da apólice, placa, nº do contrato" value="{{ old('numero_documento', $doc->numero_documento ?? '') }}">
      @error('numero_documento')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
  </div>
  <div class="col-sm-6">
    <div class="form-group">
      <label for="emissor">Emissor <small class="text-muted">(opcional)</small></label>
      <input type="text" id="emissor" name="emissor" class="form-control @error('emissor') is-invalid @enderror"
             placeholder="ex: seguradora, órgão emissor, contratante" value="{{ old('emissor', $doc->emissor ?? '') }}">
      @error('emissor')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
  </div>
</div>

<div class="form-group">
  <label for="observacoes">Observações <small class="text-muted">(opcional)</small></label>
  <textarea id="observacoes" name="observacoes" rows="3" class="form-control @error('observacoes') is-invalid @enderror">{{ old('observacoes', $doc->observacoes ?? '') }}</textarea>
  @error('observacoes')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
