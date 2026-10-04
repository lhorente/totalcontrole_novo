@extends('layouts.dashboard')

@php
  $mesesCurtos = [1=>'jan',2=>'fev',3=>'mar',4=>'abr',5=>'mai',6=>'jun',7=>'jul',8=>'ago',9=>'set',10=>'out',11=>'nov',12=>'dez'];
@endphp

@section('content')
@include('orcamento.partials.styles')

<div class="content-header">
  <div class="container">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 text-dark">Renda e metas · {{ $orcamento->ano }}</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
          <li class="breadcrumb-item"><a href="{{ route('orcamento.index', $orcamento->ano) }}">Orçamento {{ $orcamento->ano }}</a></li>
          <li class="breadcrumb-item active">Renda e metas</li>
        </ol>
      </div>
    </div>
  </div>
</div>

<div class="content">
  <div class="container">
    <form method="POST" action="{{ route('orcamento.config.update', $orcamento->ano) }}">
      @csrf

      <div class="mr-card mb-3">
        <div class="mr-card-header">
          <div>
            <h6 class="mr-card-title"><i class="fas fa-wallet"></i> Renda prevista</h6>
            <span class="mr-card-sub">quanto deve entrar em cada mês — salários, 13º, férias, bônus</span>
          </div>
          <div class="d-flex align-items-center" style="gap:6px;">
            <label for="oc-renda-todos" class="mb-0" style="font-size:.82rem;">Mesmo valor em todos os meses</label>
            <input type="number" step="0.01" min="0" id="oc-renda-todos" class="form-control form-control-sm" style="width:120px;">
            <button type="button" class="btn btn-sm btn-outline-secondary" id="oc-renda-aplicar">Aplicar</button>
          </div>
        </div>
        <div class="row" style="padding:16px 20px 4px;">
          @foreach ($mesesCurtos as $m => $curto)
            <div class="col-6 col-sm-4 col-md-2 form-group">
              <label for="renda-{{ $m }}" style="font-size:.82rem;">{{ ucfirst($curto) }}</label>
              <input type="number" step="0.01" min="0" class="form-control form-control-sm oc-renda" id="renda-{{ $m }}" name="renda[{{ $m - 1 }}]" value="{{ old('renda.'.($m - 1), $orcamento->rendaMes($m)) }}">
            </div>
          @endforeach
        </div>
      </div>

      <div class="mr-card mb-3">
        <div class="mr-card-header">
          <div>
            <h6 class="mr-card-title"><i class="fas fa-bullseye"></i> Metas por grupo</h6>
            <span class="mr-card-sub">quanto da renda cada grupo deveria levar, em %</span>
          </div>
          <span id="oc-metas-total" style="font-size:.85rem;"></span>
        </div>
        <div class="row" style="padding:16px 20px 4px;">
          @foreach (\App\Models\Orcamento::GRUPOS as $grupo => $nome)
            <div class="col-md-4 form-group">
              <label for="meta-{{ $grupo }}">{{ $nome }}</label>
              <div class="input-group">
                <input type="number" step="0.1" min="0" max="100" class="form-control oc-meta" id="meta-{{ $grupo }}" name="metas[{{ $grupo }}]" value="{{ old('metas.'.$grupo, $orcamento->metas[$grupo] ?? 0) }}">
                <div class="input-group-append"><span class="input-group-text">%</span></div>
              </div>
            </div>
          @endforeach
        </div>
      </div>

      <button type="submit" class="btn btn-primary">Salvar</button>
      <a href="{{ route('orcamento.index', $orcamento->ano) }}" class="btn btn-default">Cancelar</a>
    </form>
  </div>
</div>

<script>
(function () {
  document.getElementById('oc-renda-aplicar').addEventListener('click', function () {
    var v = document.getElementById('oc-renda-todos').value;
    if (v === '') { return; }
    document.querySelectorAll('.oc-renda').forEach(function (el) { el.value = v; });
  });

  function somarMetas() {
    var total = 0;
    document.querySelectorAll('.oc-meta').forEach(function (el) { total += parseFloat(el.value) || 0; });
    var el = document.getElementById('oc-metas-total');
    el.textContent = 'Total: ' + total.toLocaleString('pt-BR') + '%' + (Math.abs(total - 100) > 0.01 ? ' (o ideal é somar 100%)' : '');
    el.style.color = Math.abs(total - 100) > 0.01 ? '#8A5A1C' : '#065F46';
  }
  document.querySelectorAll('.oc-meta').forEach(function (el) { el.addEventListener('input', somarMetas); });
  somarMetas();
})();
</script>
@endsection
