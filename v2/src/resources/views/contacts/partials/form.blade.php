@php
  $tipoLabels = ['fornecedor'=>'Fornecedor','cliente'=>'Cliente comercial','familiar'=>'Familiar','pessoal'=>'Pessoal','outro'=>'Outro'];
  $statusLabels = ['ativo'=>'Ativo','inativo'=>'Inativo'];
  $fornecedor = $contact?->fornecedor;
  $clienteComercial = $contact?->clienteComercial;
  $tipoAtual = old('tipo', $contact->tipo ?? '');
@endphp

@if ($contact)
  <input type="hidden" name="id" value="{{ $contact->id }}">
@endif

<div class="row">
  <div class="col-sm-6">
    <div class="form-group">
      <label for="nome">Nome</label>
      <input type="text" id="nome" name="nome" class="form-control @error('nome') is-invalid @enderror"
             placeholder="ex: João da Silva, Padaria Central" value="{{ old('nome', $contact->nome ?? '') }}">
      @error('nome')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
  </div>
  <div class="col-sm-3">
    <div class="form-group">
      <label for="tipo">Tipo</label>
      <select id="tipo" name="tipo" class="form-control @error('tipo') is-invalid @enderror" onchange="contatoToggleTipo(this.value)">
        <option value="">Selecione</option>
        @foreach ($tipoLabels as $value => $label)
          <option value="{{ $value }}" {{ $tipoAtual === $value ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
      </select>
      @error('tipo')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
  </div>
  <div class="col-sm-3">
    <div class="form-group">
      <label for="status">Status</label>
      <select id="status" name="status" class="form-control @error('status') is-invalid @enderror">
        @foreach ($statusLabels as $value => $label)
          <option value="{{ $value }}" {{ old('status', $contact->status ?? 'ativo') === $value ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
      </select>
      @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
  </div>
</div>

<div class="row">
  <div class="col-sm-4">
    <div class="form-group">
      <label for="documento">CPF/CNPJ <small class="text-muted">(opcional)</small></label>
      <input type="text" id="documento" name="documento" class="form-control @error('documento') is-invalid @enderror"
             value="{{ old('documento', $contact->documento ?? '') }}">
      @error('documento')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
  </div>
  <div class="col-sm-4">
    <div class="form-group">
      <label for="email">E-mail <small class="text-muted">(opcional)</small></label>
      <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror"
             value="{{ old('email', $contact->email ?? '') }}">
      @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
  </div>
  <div class="col-sm-4">
    <div class="form-group">
      <label for="telefone">Telefone <small class="text-muted">(opcional)</small></label>
      <input type="text" id="telefone" name="telefone" class="form-control @error('telefone') is-invalid @enderror"
             value="{{ old('telefone', $contact->telefone ?? '') }}">
      @error('telefone')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
  </div>
</div>

<div id="campos-fornecedor" style="display:none">
  <hr>
  <h5>Dados do fornecedor</h5>
  <div class="row">
    <div class="col-sm-6">
      <div class="form-group">
        <label for="tipo_servico">Tipo de serviço/produto <small class="text-muted">(opcional)</small></label>
        <input type="text" id="tipo_servico" name="tipo_servico" class="form-control @error('tipo_servico') is-invalid @enderror"
               placeholder="ex: Encanador, Contador" value="{{ old('tipo_servico', $fornecedor->tipo_servico ?? '') }}">
        @error('tipo_servico')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
    </div>
    <div class="col-sm-6">
      <div class="form-group">
        <label for="razao_social">Razão social <small class="text-muted">(opcional)</small></label>
        <input type="text" id="razao_social" name="razao_social" class="form-control @error('razao_social') is-invalid @enderror"
               value="{{ old('razao_social', $fornecedor->razao_social ?? '') }}">
        @error('razao_social')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-sm-4">
      <div class="form-group">
        <label for="cnpj">CNPJ <small class="text-muted">(opcional)</small></label>
        <input type="text" id="cnpj" name="cnpj" class="form-control @error('cnpj') is-invalid @enderror"
               value="{{ old('cnpj', $fornecedor->cnpj ?? '') }}">
        @error('cnpj')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
    </div>
    <div class="col-sm-4">
      <div class="form-group">
        <label for="contato_responsavel">Contato responsável <small class="text-muted">(opcional)</small></label>
        <input type="text" id="contato_responsavel" name="contato_responsavel" class="form-control @error('contato_responsavel') is-invalid @enderror"
               value="{{ old('contato_responsavel', $fornecedor->contato_responsavel ?? '') }}">
        @error('contato_responsavel')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
    </div>
    <div class="col-sm-4">
      <div class="form-group">
        <label for="forma_pagamento_preferida">Forma de pagamento preferida <small class="text-muted">(opcional)</small></label>
        <input type="text" id="forma_pagamento_preferida" name="forma_pagamento_preferida" class="form-control @error('forma_pagamento_preferida') is-invalid @enderror"
               value="{{ old('forma_pagamento_preferida', $fornecedor->forma_pagamento_preferida ?? '') }}">
        @error('forma_pagamento_preferida')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
    </div>
  </div>
  <div class="form-group">
    <label for="observacoes_fornecedor">Observações <small class="text-muted">(opcional)</small></label>
    <textarea id="observacoes_fornecedor" name="observacoes_fornecedor" rows="2" class="form-control @error('observacoes_fornecedor') is-invalid @enderror">{{ old('observacoes_fornecedor', $fornecedor->observacoes ?? '') }}</textarea>
    @error('observacoes_fornecedor')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
</div>

<div id="campos-cliente" style="display:none">
  <hr>
  <h5>Dados do cliente comercial</h5>
  <div class="row">
    <div class="col-sm-4">
      <div class="form-group">
        <label for="valor_hora">Valor/hora <small class="text-muted">(opcional)</small></label>
        <input type="number" step="0.01" min="0" id="valor_hora" name="valor_hora" class="form-control @error('valor_hora') is-invalid @enderror"
               placeholder="0,00" value="{{ old('valor_hora', $clienteComercial->valor_hora ?? '') }}">
        @error('valor_hora')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
    </div>
    <div class="col-sm-4">
      <div class="form-group">
        <label for="forma_cobranca">Forma de cobrança <small class="text-muted">(opcional)</small></label>
        <input type="text" id="forma_cobranca" name="forma_cobranca" class="form-control @error('forma_cobranca') is-invalid @enderror"
               placeholder="ex: hora, fixo, mensal" value="{{ old('forma_cobranca', $clienteComercial->forma_cobranca ?? '') }}">
        @error('forma_cobranca')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
    </div>
    <div class="col-sm-4">
      <div class="form-group">
        <label for="contrato_url">Link do contrato <small class="text-muted">(opcional)</small></label>
        <input type="url" id="contrato_url" name="contrato_url" class="form-control @error('contrato_url') is-invalid @enderror"
               value="{{ old('contrato_url', $clienteComercial->contrato_url ?? '') }}">
        @error('contrato_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
    </div>
  </div>
  <div class="form-group">
    <label for="observacoes_cliente">Observações <small class="text-muted">(opcional)</small></label>
    <textarea id="observacoes_cliente" name="observacoes_cliente" rows="2" class="form-control @error('observacoes_cliente') is-invalid @enderror">{{ old('observacoes_cliente', $clienteComercial->observacoes ?? '') }}</textarea>
    @error('observacoes_cliente')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
</div>

<div class="form-group">
  <label for="observacoes">Observações gerais <small class="text-muted">(opcional)</small></label>
  <textarea id="observacoes" name="observacoes" rows="3" class="form-control @error('observacoes') is-invalid @enderror">{{ old('observacoes', $contact->observacoes ?? '') }}</textarea>
  @error('observacoes')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<script>
function contatoToggleTipo(tipo) {
  document.getElementById('campos-fornecedor').style.display = tipo === 'fornecedor' ? 'block' : 'none';
  document.getElementById('campos-cliente').style.display = tipo === 'cliente' ? 'block' : 'none';
}
contatoToggleTipo(document.getElementById('tipo').value);
</script>
