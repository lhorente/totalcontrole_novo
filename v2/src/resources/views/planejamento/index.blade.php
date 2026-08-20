@extends('layouts.dashboard')

@section('content')

@php
  $tipoLabels = ['manutencao'=>'Manutenção','compra'=>'Compra'];
  $prioridadeLabels = ['necessidade'=>'Necessidade','desejo'=>'Desejo'];
  $statusLabels = ['planejado'=>'Planejado','agendado'=>'Agendado','concluido'=>'Concluído','cancelado'=>'Cancelado'];
@endphp

<style>
  .plan-card-header { background: linear-gradient(135deg,#1B5E5C,#2D8B86); border-radius: .25rem .25rem 0 0; }
  .plan-badge-tipo-manutencao { background:#EFF6FF; color:#1D4A7C; border:1px solid #93C5FD; }
  .plan-badge-tipo-compra     { background:#FDF4E7; color:#8A5A1C; border:1px solid #F0C77E; }
  .plan-badge-atrasado        { background:#FEF2F2; color:#9B1C1C; border:1px solid #FCA5A5; }
  .plan-badge-recorrente      { background:#EFF6FF; color:#1D4A7C; }
  tr.plan-row-atrasado { background: #FEF2F2; }
</style>

<div class="content-header">
  <div class="container">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 text-dark">Manutenções &amp; Compras</h1>
      </div><!-- /.col -->
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
          <li class="breadcrumb-item active">Manutenções &amp; Compras</li>
        </ol>
      </div><!-- /.col -->
    </div><!-- /.row -->
  </div><!-- /.container-fluid -->
</div>
<div class="content">
  <div class="container">

    @if (\Session::has('success'))
      <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        {{ Session::get('success') }}
      </div>
    @endif
    @if (\Session::has('error'))
      <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        {{ Session::get('error') }}
      </div>
    @endif

    <div class="row mb-3">
      <div class="col-6 col-md-3">
        <div class="info-box mb-2">
          <span class="info-box-icon bg-secondary"><i class="fas fa-hourglass-half"></i></span>
          <div class="info-box-content">
            <span class="info-box-text">Planejado (pendente)</span>
            <span class="info-box-number" style="font-size:1em">R$ {{ number_format($resumo['valor_pendente'], 2, ',', '.') }}</span>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="info-box mb-2">
          <span class="info-box-icon bg-danger"><i class="fas fa-exclamation-triangle"></i></span>
          <div class="info-box-content">
            <span class="info-box-text">Atrasados</span>
            <span class="info-box-number" style="font-size:1em">{{ $resumo['atrasados'] }}</span>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="info-box mb-2">
          <span class="info-box-icon bg-warning"><i class="fas fa-calendar-day"></i></span>
          <div class="info-box-content">
            <span class="info-box-text">Próximos 30 dias</span>
            <span class="info-box-number" style="font-size:1em">{{ $resumo['proximos_30'] }}</span>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="info-box mb-2">
          <span class="info-box-icon bg-info"><i class="fas fa-car"></i></span>
          <div class="info-box-content">
            <span class="info-box-text">Bens cadastrados</span>
            <span class="info-box-number" style="font-size:1em">{{ $resumo['bens_cadastrados'] }}</span>
            <a href="{{ route('bens.index') }}" class="d-block small">ver bens →</a>
          </div>
        </div>
      </div>
    </div>

    <div class="row justify-content-center">
      <div class="col-md-12">

        <div class="card">
          <div class="card-header plan-card-header d-flex justify-content-between align-items-center flex-wrap">
            <a href="{{ route('planejamento.create') }}" class="btn btn-light mr-2 mb-1">Novo item</a>

            <form method="GET" action="{{ route('planejamento.index') }}" class="form-inline mb-1">
              <select name="tipo" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                <option value="">Todos os tipos</option>
                @foreach ($tipoLabels as $value => $label)
                  <option value="{{ $value }}" {{ $tipo === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
              </select>

              <select name="id_bem" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                <option value="">Todos os bens</option>
                <option value="0" {{ $idBem === '0' ? 'selected' : '' }}>Sem bem vinculado</option>
                @foreach ($bens as $bem)
                  <option value="{{ $bem->id }}" {{ (string) $idBem === (string) $bem->id ? 'selected' : '' }}>{{ $bem->nome }}</option>
                @endforeach
              </select>

              <select name="prioridade" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                <option value="">Todas as prioridades</option>
                @foreach ($prioridadeLabels as $value => $label)
                  <option value="{{ $value }}" {{ $prioridade === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
              </select>

              <select name="status" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                <option value="">Todos os status</option>
                @foreach ($statusLabels as $value => $label)
                  <option value="{{ $value }}" {{ $status === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
              </select>

              @if ($tipo || $idBem || $prioridade || $status)
                <a href="{{ route('planejamento.index') }}" class="btn btn-sm btn-outline-light">Limpar</a>
              @endif
            </form>
          </div>
          <!-- /.card-header -->
          <div class="card-body p-0">
            <table class="table">
              <thead>
                <tr>
                  <th style="width: 10px">#</th>
                  <th>Item</th>
                  <th>Tipo</th>
                  <th>Bem</th>
                  <th>Prioridade</th>
                  <th>Data prevista</th>
                  <th>Valor estimado</th>
                  <th>Status</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                @forelse ($itens as $item)
                  @php
                    $plan = $item->planejamento;
                    $atrasado = !in_array($item->status, ['concluido', 'cancelado']) && $item->data_vencimento && $item->data_vencimento->lt(now());
                  @endphp
                  <tr class="{{ $atrasado ? 'plan-row-atrasado' : '' }}">
                    <td>{{ $item->id }}.</td>
                    <td>
                      <a href="{{ route('planejamento.edit', $item->id) }}">{{ $item->titulo }}</a>
                      @if ($plan && $plan->categoria)
                        <small class="text-muted d-block">{{ $plan->categoria }}</small>
                      @endif
                      @if ($plan && $plan->recorrente)
                        <span class="badge plan-badge-recorrente mt-1">
                          <i class="fas fa-sync-alt fa-xs"></i> a cada {{ $plan->recorrencia_intervalo }} {{ $plan->recorrencia_unidade }}
                        </span>
                      @endif
                    </td>
                    <td>
                      <span class="badge plan-badge-tipo-{{ $item->tipo }}">{{ $tipoLabels[$item->tipo] ?? $item->tipo }}</span>
                    </td>
                    <td>{{ $plan && $plan->bem ? $plan->bem->nome : '—' }}</td>
                    <td>
                      @if ($plan && $plan->prioridade === 'necessidade')
                        <span class="text-danger">● {{ $prioridadeLabels['necessidade'] }}</span>
                      @else
                        <span class="text-muted">○ {{ $prioridadeLabels['desejo'] ?? '' }}</span>
                      @endif
                    </td>
                    <td>
                      @if ($item->data_vencimento)
                        {{ $item->data_vencimento->format('d/m/Y') }}
                        @if ($atrasado)
                          <span class="badge plan-badge-atrasado ml-1">Atrasado</span>
                        @endif
                      @else
                        <span class="text-muted">sem data</span>
                      @endif
                    </td>
                    <td>{{ $item->valor ? 'R$ '.number_format($item->valor, 2, ',', '.') : '—' }}</td>
                    <td>
                      @if ($item->status === 'concluido')
                        <span class="badge badge-success">{{ $statusLabels[$item->status] }}</span>
                      @elseif ($item->status === 'cancelado')
                        <span class="badge badge-light">{{ $statusLabels[$item->status] }}</span>
                      @else
                        <span class="badge badge-secondary">{{ $statusLabels[$item->status] ?? $item->status }}</span>
                      @endif
                    </td>
                    <td class="text-right">
                      <a href="{{ route('planejamento.edit', $item->id) }}" class="btn btn-sm btn-outline-secondary">Editar</a>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="9" class="text-center text-muted py-4">Nenhum item encontrado.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
          <!-- /.card-body -->
        </div>

      </div>
    </div>
  </div>
</div>
@endsection
