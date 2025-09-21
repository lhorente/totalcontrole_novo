@extends('layouts.dashboard')

@section('content')
<div class="content-header">
  <div class="container">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 text-dark"> Lançamentos</h1>
      </div><!-- /.col -->
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{ url('/') }}'">Dashboard</a></li>
          <li class="breadcrumb-item active">Lançamentos</li>
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
            <h3 class="card-title">
              <i class="fa fa-coins"></i>
              Lançamentos pendentes
            </h3>

            <div class="card-tools">
              <div class="btn-group">
                <button type="button" class="btn btn-tool dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                  <i class="fas fa-filter"></i> Filtrar
                </button>
                <div class="dropdown-menu dropdown-menu-right" role="menu" x-placement="bottom-end" style="position: absolute; transform: translate3d(-123px, 19px, 0px); top: 0px; left: 0px; will-change: transform;">
                  <a href="#" class="dropdown-item">A receber</a>
                  <a href="#" class="dropdown-item">A pagar</a>
                  <a href="#" class="dropdown-item">Transferências</a>
                  <a class="dropdown-divider"></a>
                  <a href="#" class="dropdown-item">Filtro personalizado</a>
                </div>
              </div>
            </div>

            <!--<a href="{{ url('transactions/new') }}" class="btn btn-primary">Novo lançamento</a>-->
          </div>
          <!-- /.card-header -->
          <div class="card-body">

            <div class="timeline">
              <!-- timeline time label -->
              <div class="time-label">
                <span class="bg-red">20 de outubro</span>
              </div>
              <!-- /.timeline-label -->
              <div>
                <i class="fas fa-sync bg-red"></i>
                <div class="timeline-item">
                  <span class="time">R$ 11,50</span>
                  <h3 class="timeline-header no-border">Transferência de Conta para Poupança</h3>
                </div>
              </div>

              <!-- timeline time label -->
              <div class="time-label">
                <span class="bg-green">28 de outubro</span>
              </div>
              <!-- /.timeline-label -->

              <div>
                <i class="fas fa-utensils bg-green"></i>
                <div class="timeline-item">
                  <span class="time">R$ 11,50</span>
                  <h3 class="timeline-header no-border">Panificadora</h3>
                </div>
              </div>

              <!-- timeline time label -->
              <div class="time-label">
                <span class="bg-green">31 de outubro</span>
              </div>
              <!-- /.timeline-label -->

              <!-- timeline item -->
              <div>
                <i class="fas fa-heart bg-green"></i>
                <div class="timeline-item">
                  <span class="time"> R$ 15,00</span>
                  <h3 class="timeline-header no-border">Corte cabelo Vinícius</h3>
                </div>
              </div>
              <!-- END timeline item -->

              <!-- timeline item -->
              <div>
                <i class="fas fa-heart bg-green"></i>
                <div class="timeline-item">
                  <span class="time"> R$ 15,00</span>
                  <h3 class="timeline-header no-border">Corte cabelo Willian</h3>
                </div>
              </div>
              <!-- END timeline item -->

          </div>

          </div>
          <!-- /.card-body -->
          <div class="card-footer text-center">
            <a href="javascript::">Carregar mais lançamentos</a>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>
@endsection
