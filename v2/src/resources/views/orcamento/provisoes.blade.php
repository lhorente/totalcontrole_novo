@extends('layouts.dashboard')

@php
  $mesesCurtos = [1=>'jan',2=>'fev',3=>'mar',4=>'abr',5=>'mai',6=>'jun',7=>'jul',8=>'ago',9=>'set',10=>'out',11=>'nov',12=>'dez'];
  $brl = fn($v) => 'R$ '.number_format($v, 2, ',', '.');
@endphp

@section('content')
@include('orcamento.partials.styles')

<div class="content-header">
  <div class="container">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 text-dark">Converter provisões · {{ $ano }}</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
          <li class="breadcrumb-item"><a href="{{ route('orcamento.index', $ano) }}">Orçamento {{ $ano }}</a></li>
          <li class="breadcrumb-item active">Converter provisões</li>
        </ol>
      </div>
    </div>
  </div>
</div>

<div class="content">
  <div class="container">

    @if ($provisoes->isEmpty())
      <div class="mr-card" style="padding:20px;">
        <p class="text-muted mb-0">Nenhum lançamento “Prov …” ativo em {{ $ano }}. <a href="{{ route('orcamento.index', $ano) }}">Voltar ao orçamento</a></p>
      </div>
    @else
    <p class="text-muted">
      Cada provisão abaixo vira um item do orçamento com o valor de cada mês em que havia “Prov”. Se já existe um item com o mesmo nome, só os meses com “Prov” são atualizados.
      Os lançamentos “Prov …” convertidos ficam no histórico como <strong>cancelados</strong> — eles deixam de contar como despesa, e a previsão passa a vir do orçamento.
      @unless ($orcamento) O orçamento {{ $ano }} é criado junto, com renda zerada para você preencher depois. @endunless
    </p>

    <form method="POST" action="{{ route('orcamento.provisoes.converter', $ano) }}">
      @csrf
      <div class="mr-card mb-3">
        <div style="overflow-x:auto;">
          <table class="table table-sm mb-0" style="font-size:.85rem;min-width:1000px;">
            <thead>
              <tr class="text-muted">
                <th style="padding-left:20px;width:36px;"><input type="checkbox" id="oc-todos" checked aria-label="Selecionar todas"></th>
                <th>Provisão</th>
                <th>Categoria</th>
                <th>Meses com “Prov”</th>
                <th class="text-right">Total</th>
                <th style="width:200px;">Grupo no orçamento</th>
                <th style="padding-right:20px;">Vai para</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($provisoes as $p)
                @php $existente = $itensPorNome->get($p['chave']); @endphp
                <tr>
                  <td style="padding-left:20px;"><input type="checkbox" class="oc-conv" name="converter[]" value="{{ $p['chave'] }}" checked aria-label="Converter {{ $p['nome'] }}"></td>
                  <td style="font-weight:600;">{{ $p['nome'] }} <span class="text-muted" style="font-weight:400;font-size:.76rem;">· {{ count($p['ids']) }} lançamento{{ count($p['ids']) > 1 ? 's' : '' }}</span></td>
                  <td class="text-muted">{{ $p['categoria'] ?? '—' }}</td>
                  <td>
                    <span class="d-flex" style="gap:2px;">
                      @foreach ($p['valores'] as $i => $v)
                        <span title="{{ $mesesCurtos[$i + 1] }}: {{ $brl($v) }}" style="display:block;width:8px;height:14px;border-radius:2px;background:{{ $v > 0 ? '#2D8B86' : '#e9ecef' }};"></span>
                      @endforeach
                    </span>
                  </td>
                  <td class="text-right">{{ $brl($p['total']) }}</td>
                  <td>
                    <select class="form-control form-control-sm" name="grupo[{{ md5($p['chave']) }}]" aria-label="Grupo de {{ $p['nome'] }}" @if ($existente) disabled @endif>
                      @foreach (\App\Models\Orcamento::GRUPOS as $k => $nome)
                        <option value="{{ $k }}" {{ (($existente->grupo ?? 'necessidades') === $k) ? 'selected' : '' }}>{{ $nome }}</option>
                      @endforeach
                    </select>
                    @if ($existente)<input type="hidden" name="grupo[{{ md5($p['chave']) }}]" value="{{ $existente->grupo }}">@endif
                  </td>
                  <td style="padding-right:20px;">
                    @if ($existente)
                      <span class="mr-badge mr-badge-planejado">soma ao item “{{ $existente->nome }}”</span>
                    @else
                      <span class="mr-badge mr-badge-tranquilo">novo item</span>
                    @endif
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
      <button type="submit" class="btn btn-primary" id="oc-converter">Converter selecionadas</button>
      <a href="{{ route('orcamento.index', $ano) }}" class="btn btn-default">Cancelar</a>
    </form>
    @endif

  </div>
</div>

<script>
(function () {
  var todos = document.getElementById('oc-todos');
  if (!todos) { return; }
  todos.addEventListener('change', function () {
    document.querySelectorAll('.oc-conv').forEach(function (el) { el.checked = todos.checked; });
  });
  document.getElementById('oc-converter').closest('form').addEventListener('submit', function (e) {
    var n = document.querySelectorAll('.oc-conv:checked').length;
    if (n === 0 || !confirm('Converter ' + n + ' provisões em itens do orçamento? Os lançamentos “Prov …” delas serão marcados como cancelados.')) {
      e.preventDefault();
    }
  });
})();
</script>
@endsection
