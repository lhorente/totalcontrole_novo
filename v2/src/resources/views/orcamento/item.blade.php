@extends('layouts.dashboard')

@php
  $mesesCurtos = [1=>'jan',2=>'fev',3=>'mar',4=>'abr',5=>'mai',6=>'jun',7=>'jul',8=>'ago',9=>'set',10=>'out',11=>'nov',12=>'dez'];
  $novo = !$item->exists;
  $tipo = old('tipo', $item->tipo ?? 'fixo');
  $subitens = old('subitens', $novo ? [] : $item->subitens->map(fn($s) => ['mes' => $s->mes, 'descricao' => $s->descricao, 'valor' => $s->valor])->all());
@endphp

@section('content')
@include('orcamento.partials.styles')

<div class="content-header">
  <div class="container">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 text-dark">{{ $novo ? 'Novo item' : 'Editar item · '.$item->nome }}</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
          <li class="breadcrumb-item"><a href="{{ route('orcamento.index', $orcamento->ano) }}">Orçamento {{ $orcamento->ano }}</a></li>
          <li class="breadcrumb-item active">{{ $novo ? 'Novo item' : 'Editar item' }}</li>
        </ol>
      </div>
    </div>
  </div>
</div>

<div class="content">
  <div class="container">
    <form method="POST" action="{{ $novo ? route('orcamento.item.store', $orcamento->ano) : route('orcamento.item.update', $item->id) }}">
      @csrf

      <div class="mr-card mb-3" style="padding:16px 20px 4px;">
        <div class="row">
          <div class="col-md-4 form-group">
            <label for="nome">Nome</label>
            <input type="text" class="form-control" id="nome" name="nome" required maxlength="255" value="{{ old('nome', $item->nome) }}">
          </div>
          <div class="col-md-4 form-group">
            <label for="grupo">Grupo</label>
            <select class="form-control" id="grupo" name="grupo">
              @foreach (\App\Models\Orcamento::GRUPOS as $k => $nome)
                <option value="{{ $k }}" {{ (old('grupo', $item->grupo) === $k) ? 'selected' : '' }}>{{ $nome }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-4 form-group">
            <label for="id_categoria">Categoria no sistema</label>
            <select class="form-control" id="id_categoria" name="id_categoria">
              <option value="">—</option>
              @foreach ($categorias as $cat)
                <option value="{{ $cat->id }}" {{ ((string) old('id_categoria', $item->id_categoria) === (string) $cat->id) ? 'selected' : '' }}>{{ $cat->nome }}</option>
              @endforeach
            </select>
          </div>
        </div>
      </div>

      <div class="mr-card mb-3">
        <div class="mr-card-header">
          <h6 class="mr-card-title"><i class="fas fa-calendar-alt"></i> Como o valor se distribui no ano</h6>
        </div>
        <div style="padding:14px 20px;">
          <div class="d-flex flex-wrap mb-3" style="gap:18px;">
            @foreach (\App\Models\OrcamentoItem::TIPOS as $k => $rotulo)
              <div class="custom-control custom-radio">
                <input type="radio" class="custom-control-input oc-tipo" id="tipo-{{ $k }}" name="tipo" value="{{ $k }}" {{ ($tipo === $k) ? 'checked' : '' }}>
                <label class="custom-control-label" for="tipo-{{ $k }}">{{ $rotulo }}</label>
              </div>
            @endforeach
          </div>

          <div class="oc-painel" data-tipo="fixo">
            <div class="form-group mb-0" style="max-width:220px;">
              <label for="valor_fixo">Valor por mês</label>
              <input type="number" step="0.01" min="0" class="form-control" id="valor_fixo" name="valor_fixo" value="{{ old('valor_fixo', $item->valor_fixo) }}">
            </div>
          </div>

          <div class="oc-painel" data-tipo="mensal">
            <div class="row">
              @foreach ($mesesCurtos as $m => $curto)
                <div class="col-6 col-sm-4 col-md-2 form-group">
                  <label for="valor-{{ $m }}" style="font-size:.82rem;">{{ ucfirst($curto) }}</label>
                  <input type="number" step="0.01" min="0" class="form-control form-control-sm" id="valor-{{ $m }}" name="valores[{{ $m - 1 }}]" value="{{ old('valores.'.($m - 1), $item->valorMes($m) ?: '') }}">
                </div>
              @endforeach
            </div>
          </div>

          <div class="oc-painel" data-tipo="detalhado">
            <p class="text-muted" style="font-size:.85rem;">Para itens montados a partir de uma lista — aniversários e presentes, assinaturas, roupas de cada um.</p>
            <table class="table table-sm mb-2" style="font-size:.87rem;">
              <thead><tr class="text-muted"><th style="width:110px;">Mês</th><th>Descrição</th><th style="width:150px;">Valor</th><th style="width:40px;"></th></tr></thead>
              <tbody id="oc-subitens">
                @foreach ($subitens as $i => $sub)
                  <tr>
                    <td>
                      <select class="form-control form-control-sm" name="subitens[{{ $i }}][mes]" aria-label="Mês">
                        @foreach ($mesesCurtos as $m => $curto)<option value="{{ $m }}" {{ ((int) ($sub['mes'] ?? 0) === $m) ? 'selected' : '' }}>{{ $curto }}</option>@endforeach
                      </select>
                    </td>
                    <td><input type="text" class="form-control form-control-sm" name="subitens[{{ $i }}][descricao]" value="{{ $sub['descricao'] ?? '' }}" aria-label="Descrição"></td>
                    <td><input type="number" step="0.01" min="0" class="form-control form-control-sm" name="subitens[{{ $i }}][valor]" value="{{ $sub['valor'] ?? '' }}" aria-label="Valor"></td>
                    <td><button type="button" class="btn btn-sm btn-link text-danger oc-sub-remover" aria-label="Remover sub-item"><i class="fas fa-times"></i></button></td>
                  </tr>
                @endforeach
              </tbody>
            </table>
            <button type="button" class="btn btn-sm btn-outline-secondary mb-3" id="oc-sub-adicionar"><i class="fas fa-plus"></i> Adicionar sub-item</button>
            <div class="d-flex flex-wrap" style="gap:18px;">
              @foreach (\App\Models\OrcamentoItem::DISTRIBUICOES as $k => $rotulo)
                <div class="custom-control custom-radio">
                  <input type="radio" class="custom-control-input" id="dist-{{ $k }}" name="distribuicao" value="{{ $k }}" {{ (old('distribuicao', $item->distribuicao ?? 'no_mes') === $k) ? 'checked' : '' }}>
                  <label class="custom-control-label" for="dist-{{ $k }}">{{ $rotulo }}</label>
                </div>
              @endforeach
            </div>
            <div class="text-muted mt-1" style="font-size:.8rem;" id="oc-sub-total"></div>
          </div>
        </div>
      </div>

      <div class="mr-card mb-3">
        <div class="mr-card-header">
          <h6 class="mr-card-title"><i class="fas fa-link"></i> Como reconhecer o gasto real deste item</h6>
        </div>
        <div style="padding:14px 20px;">
          <div class="form-group" style="max-width:420px;">
            <label for="padrao_descricao">Descrição contém <span class="text-muted" style="font-weight:400;">(opcional)</span></label>
            <input type="text" class="form-control" id="padrao_descricao" name="padrao_descricao" maxlength="255" placeholder="ex.: NETFLIX" value="{{ old('padrao_descricao', $item->padrao_descricao) }}">
          </div>
          <p class="text-muted mb-0" style="font-size:.85rem;">
            Com a descrição preenchida, o item recebe os lançamentos do mês cuja descrição (ou descrição do banco) contém esse texto, de qualquer categoria.
            Sem ela, recebe os lançamentos da categoria escolhida acima — se outros itens sem descrição usam a mesma categoria, o valor é dividido entre eles na proporção do planejado.
            Quando o gasto real aparece, a provisão do mês é abatida sozinha.
          </p>
        </div>
      </div>

      <div class="d-flex justify-content-between">
        <div>
          <button type="submit" class="btn btn-primary">Salvar</button>
          <a href="{{ route('orcamento.index', $orcamento->ano) }}" class="btn btn-default">Cancelar</a>
        </div>
        @unless ($novo)
          <button type="button" class="btn btn-outline-danger" id="oc-excluir">Excluir item</button>
        @endunless
      </div>
    </form>

    @unless ($novo)
      <form id="oc-form-excluir" method="POST" action="{{ route('orcamento.item.destroy', $item->id) }}" style="display:none;">
        @csrf
        @method('DELETE')
      </form>
    @endunless
  </div>
</div>

<template id="oc-sub-modelo">
  <tr>
    <td>
      <select class="form-control form-control-sm" data-name="mes" aria-label="Mês">
        @foreach ($mesesCurtos as $m => $curto)<option value="{{ $m }}">{{ $curto }}</option>@endforeach
      </select>
    </td>
    <td><input type="text" class="form-control form-control-sm" data-name="descricao" aria-label="Descrição"></td>
    <td><input type="number" step="0.01" min="0" class="form-control form-control-sm" data-name="valor" aria-label="Valor"></td>
    <td><button type="button" class="btn btn-sm btn-link text-danger oc-sub-remover" aria-label="Remover sub-item"><i class="fas fa-times"></i></button></td>
  </tr>
</template>

<script>
(function () {
  function mostrarPainel() {
    var tipo = document.querySelector('.oc-tipo:checked').value;
    document.querySelectorAll('.oc-painel').forEach(function (p) { p.style.display = p.dataset.tipo === tipo ? '' : 'none'; });
  }
  document.querySelectorAll('.oc-tipo').forEach(function (el) { el.addEventListener('change', mostrarPainel); });
  mostrarPainel();

  var corpo = document.getElementById('oc-subitens');
  var proximo = corpo.querySelectorAll('tr').length;

  function totalSub() {
    var total = 0;
    corpo.querySelectorAll('input[type=number]').forEach(function (el) { total += parseFloat(el.value) || 0; });
    document.getElementById('oc-sub-total').textContent = total > 0
      ? 'Total no ano: R$ ' + total.toLocaleString('pt-BR', { minimumFractionDigits: 2 }) + ' · R$ ' + (total / 12).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '/mês se reservado igual'
      : '';
  }

  document.getElementById('oc-sub-adicionar').addEventListener('click', function () {
    var linha = document.getElementById('oc-sub-modelo').content.firstElementChild.cloneNode(true);
    linha.querySelectorAll('[data-name]').forEach(function (el) { el.name = 'subitens[' + proximo + '][' + el.dataset.name + ']'; });
    proximo++;
    corpo.appendChild(linha);
    linha.querySelector('input[type=text]').focus();
  });
  corpo.addEventListener('click', function (e) {
    var btn = e.target.closest('.oc-sub-remover');
    if (btn) { btn.closest('tr').remove(); totalSub(); }
  });
  corpo.addEventListener('input', totalSub);
  totalSub();

  var excluir = document.getElementById('oc-excluir');
  if (excluir) {
    excluir.addEventListener('click', function () {
      if (confirm('Excluir este item do orçamento? As provisões dele deixam de existir.')) {
        document.getElementById('oc-form-excluir').submit();
      }
    });
  }
})();
</script>
@endsection
