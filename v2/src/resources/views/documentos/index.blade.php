@extends('layouts.dashboard')

@section('content')

@php
  $tipoLabels = ['seguro'=>'Seguro','ipva'=>'IPVA','cnh'=>'CNH','garantia'=>'Garantia','contrato'=>'Contrato','outro'=>'Outro'];
  $statusLabels = ['ativo'=>'Ativo','concluido'=>'Concluído','cancelado'=>'Cancelado','vencido'=>'Vencido'];
@endphp

<style>
  .doc-card-header { background: linear-gradient(135deg,#1B5E5C,#2D8B86); border-radius: .25rem .25rem 0 0; }
  .doc-badge-vencendo { background:#EFF6FF; color:#1D4A7C; border:1px solid #93C5FD; }
  .doc-badge-vencido  { background:#FEF2F2; color:#9B1C1C; border:1px solid #FCA5A5; }
  tr.doc-row-vencendo { background: #EFF6FF; }
</style>

<div class="content-header">
  <div class="container">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 text-dark">Documentos e Prazos</h1>
      </div><!-- /.col -->
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
          <li class="breadcrumb-item active">Documentos e Prazos</li>
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

    <div class="row justify-content-center">
      <div class="col-md-12">

        <div class="card">
          <div class="card-header doc-card-header">
            <a href="{{ route('documentos.create') }}" class="btn btn-light">Adicionar documento</a>
          </div>
          <!-- /.card-header -->
          <div class="card-body p-0">
            <table class="table">
              <thead>
                <tr>
                  <th style="width: 10px">#</th>
                  <th>Título</th>
                  <th>Tipo</th>
                  <th>Pessoa</th>
                  <th>Vencimento</th>
                  <th>Valor</th>
                  <th>Status</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                @forelse ($documentos as $documento)
                  @php
                    $vencendo = $documento->status === 'ativo' && $documento->data_vencimento && $documento->data_vencimento->lte(now()->addDays(30));
                    $vencido  = $documento->status === 'ativo' && $documento->data_vencimento && $documento->data_vencimento->lt(now());
                  @endphp
                  <tr class="{{ $vencendo ? 'doc-row-vencendo' : '' }}">
                    <td>{{ $documento->id }}.</td>
                    <td>
                      <a href="{{ route('documentos.edit', $documento->id) }}">{{ $documento->titulo }}</a>
                      @if ($documento->documento && $documento->documento->arquivo_url)
                        <a href="{{ $documento->documento->arquivo_url }}" target="_blank" rel="noopener" title="Abrir documento">
                          <i class="fas fa-external-link-alt fa-xs"></i>
                        </a>
                      @endif
                      @if ($documento->documento && $documento->documento->numero_documento)
                        <small class="text-muted d-block">{{ $documento->documento->numero_documento }}</small>
                      @endif
                    </td>
                    <td><span class="badge badge-secondary">{{ $tipoLabels[$documento->tipo] ?? $documento->tipo }}</span></td>
                    <td>{{ $documento->contact->nome ?? '—' }}</td>
                    <td>
                      @if ($documento->data_vencimento)
                        {{ $documento->data_vencimento->format('d/m/Y') }}
                        @if ($vencido)
                          <span class="badge doc-badge-vencido ml-1">Vencido</span>
                        @elseif ($vencendo)
                          <span class="badge doc-badge-vencendo ml-1">Vence em breve</span>
                        @endif
                      @else
                        <span class="text-muted">—</span>
                      @endif
                    </td>
                    <td>{{ $documento->valor ? 'R$ '.number_format($documento->valor, 2, ',', '.') : '—' }}</td>
                    <td>
                      @if ($documento->status === 'ativo')
                        <span class="badge badge-success">{{ $statusLabels[$documento->status] }}</span>
                      @elseif ($documento->status === 'vencido')
                        <span class="badge badge-danger">{{ $statusLabels[$documento->status] }}</span>
                      @else
                        <span class="badge badge-light">{{ $statusLabels[$documento->status] ?? $documento->status }}</span>
                      @endif
                    </td>
                    <td class="text-right">
                      <a href="{{ route('documentos.edit', $documento->id) }}" class="btn btn-sm btn-outline-secondary">Editar</a>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="8" class="text-center text-muted py-4">Nenhum documento cadastrado ainda.</td>
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
