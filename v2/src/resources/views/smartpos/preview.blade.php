@extends('layouts.dashboard')

@section('content')
<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 text-dark">Pré-visualização — SmartPOS</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
          <li class="breadcrumb-item"><a href="{{ route('smartpos.import') }}">Importar SmartPOS</a></li>
          <li class="breadcrumb-item active">Pré-visualização</li>
        </ol>
      </div>
    </div>
  </div>
</div>

<div class="content">
  <div class="container-fluid">

    {{-- Resumo --}}
    <div class="row mb-3">
      <div class="col-6 col-md-3">
        <div class="small-box bg-secondary">
          <div class="inner">
            <h3>{{ $totalLinhas }}</h3>
            <p>Total de linhas</p>
          </div>
          <div class="icon"><i class="fas fa-list"></i></div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="small-box bg-success">
          <div class="inner">
            <h3>{{ $totalValidas }}</h3>
            <p>Serão importadas</p>
          </div>
          <div class="icon"><i class="fas fa-check"></i></div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="small-box bg-warning">
          <div class="inner">
            <h3>{{ $totalIgnoradas }}</h3>
            <p>Ignoradas / com erro</p>
          </div>
          <div class="icon"><i class="fas fa-ban"></i></div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="small-box bg-info">
          <div class="inner">
            <h3>R$ {{ number_format($valorTotal, 2, ',', '.') }}</h3>
            <p>Valor total a importar</p>
          </div>
          <div class="icon"><i class="fas fa-dollar-sign"></i></div>
        </div>
      </div>
    </div>

    @if ($totalValidas === 0)
      <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle mr-1"></i>
        Nenhum registro válido encontrado para importar.
        <a href="{{ route('smartpos.import') }}" class="alert-link ml-2">Voltar e tentar outro arquivo</a>
      </div>
    @endif

    {{-- Tabela de preview --}}
    <div class="card">
      <div class="card-header">
        <h3 class="card-title"><i class="fas fa-table mr-1"></i> Registros do arquivo</h3>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-sm table-hover mb-0">
            <thead class="thead-light">
              <tr>
                <th>Código</th>
                <th>Data</th>
                <th>Cliente</th>
                <th class="text-right">Valor</th>
                <th>Categoria</th>
                <th>Status original</th>
                <th>Ação</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($parsed as $row)
                @php
                  switch ($row['action']) {
                    case 'importar':
                      $rowClass = '';
                      $badge = '<span class="badge badge-success">Será importada</span>';
                      break;
                    case 'ignorada_status':
                      $rowClass = 'table-secondary';
                      $badge = '<span class="badge badge-secondary">Ignorada: status inválido</span>';
                      break;
                    case 'ignorada_duplicada':
                      $rowClass = 'table-warning';
                      $badge = '<span class="badge badge-warning">Ignorada: duplicada</span>';
                      break;
                    default:
                      $rowClass = 'table-danger';
                      $badge = '<span class="badge badge-danger">Erro: ' . e($row['error_msg']) . '</span>';
                  }
                @endphp
                <tr class="{{ $rowClass }}">
                  <td><code>{{ $row['codigo'] }}</code></td>
                  <td>
                    @if ($row['data'])
                      {{ \Carbon\Carbon::parse($row['data'])->format('d/m/Y') }}
                    @else
                      <span class="text-muted">{{ $row['data_raw'] }}</span>
                    @endif
                  </td>
                  <td>{{ $row['cliente'] }}</td>
                  <td class="text-right">
                    @if ($row['valor'] !== null && $row['valor'] > 0)
                      R$ {{ number_format($row['valor'], 2, ',', '.') }}
                    @else
                      <span class="text-muted">{{ $row['valor_raw'] }}</span>
                    @endif
                  </td>
                  <td>{{ $row['categoria_nome'] }}</td>
                  <td>{{ $row['status_row'] }}</td>
                  <td>{!! $badge !!}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>

    {{-- Botões de ação --}}
    <div class="row">
      <div class="col-12 d-flex justify-content-between">
        <a href="{{ route('smartpos.import') }}" class="btn btn-secondary">
          <i class="fas fa-arrow-left mr-1"></i> Cancelar
        </a>

        @if ($totalValidas > 0)
          @php
            $confirmMsg = "Confirmar importação de {$totalValidas} venda(s) totalizando R$ " . number_format($valorTotal, 2, ',', '.') . "?";
          @endphp
          <form action="{{ route('smartpos.store') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-success"
              onclick="return confirm({{ json_encode($confirmMsg) }})">
              <i class="fas fa-check mr-1"></i>
              Confirmar e Importar {{ $totalValidas }} venda(s)
            </button>
          </form>
        @endif
      </div>
    </div>

  </div>
</div>
@endsection
