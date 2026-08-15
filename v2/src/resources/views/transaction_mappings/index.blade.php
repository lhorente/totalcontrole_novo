@extends('layouts.dashboard')

@section('content')
<div class="content-header">
  <div class="container">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 text-dark"> De &lt;&gt; Para de descrições</h1>
      </div><!-- /.col -->
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
          <li class="breadcrumb-item active">De &lt;&gt; Para de descrições</li>
        </ol>
      </div><!-- /.col -->
    </div><!-- /.row -->
  </div><!-- /.container-fluid -->
</div>
<div class="content">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-12">

        <div class="card">
          <div class="card-header">
            <a href="{{ url('transaction-mappings/new') }}" class="btn btn-primary">Adicionar mapeamento</a>
            <small class="text-muted d-block mt-2">
              Regras que traduzem o texto da fatura do cartão (ex: <code>CBQ*CZTO COMERCIO DE</code>) para um local e categoria (ex: <code>McDonald's</code>).
              Regras marcadas como <span class="badge badge-info">Automático</span> foram aprendidas quando você salvou uma transação com um apelido diferente da descrição do banco.
            </small>
          </div>
          <!-- /.card-header -->
          <div class="card-body p-0">
            <table class="table">
              <thead>
                <tr>
                  <th style="width: 10px">#</th>
                  <th>Padrão da fatura</th>
                  <th>Vira</th>
                  <th>Categoria</th>
                  <th>Origem</th>
                  <th class="text-right">Uso</th>
                  <th>Última vez</th>
                  <th>Status</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                @forelse ($mappings as $mapping)
                <tr class="{{ !$mapping->ativo ? 'text-muted' : '' }}">
                  <td>{{ $mapping->id }}.</td>
                  <td><code>{{ $mapping->padrao }}</code></td>
                  <td>
                    <a href="{{ url('transaction-mappings/edit/'.$mapping->id) }}">{{ $mapping->descricao_local }}</a>
                  </td>
                  <td>
                    @if ($mapping->category)
                      <span class="badge badge-warning">{{ $mapping->category->nome }}</span>
                    @else
                      <span class="text-muted">—</span>
                    @endif
                  </td>
                  <td>
                    @if ($mapping->origem === 'automatico')
                      <span class="badge badge-info">Automático</span>
                    @else
                      <span class="badge badge-secondary">Manual</span>
                    @endif
                  </td>
                  <td class="text-right">{{ $mapping->ocorrencias }}</td>
                  <td>{{ $mapping->ultima_utilizacao ? $mapping->ultima_utilizacao->format('d/m/Y') : '—' }}</td>
                  <td>
                    <form action="{{ url('transaction-mappings/quick-toggle/'.$mapping->id) }}" method="post" class="d-inline">
                      @csrf
                      <button type="submit" class="btn btn-link p-0" style="border:0;background:none;">
                        @if ($mapping->ativo)
                          <span class="badge badge-success">Ativo</span>
                        @else
                          <span class="badge badge-light">Inativo</span>
                        @endif
                      </button>
                    </form>
                  </td>
                  <td class="text-right">
                    <a href="{{ url('transaction-mappings/edit/'.$mapping->id) }}" class="btn btn-sm btn-outline-secondary">Editar</a>
                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="9" class="text-center text-muted py-4">Nenhum mapeamento cadastrado ainda.</td>
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
