@extends('layouts.dashboard')

@section('content')
<div class="content-header">
  <div class="container">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 text-dark"> Carteiras</h1>
      </div><!-- /.col -->
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{ url('/') }}'">Dashboard</a></li>
          <li class="breadcrumb-item active">Carteiras</li>
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
            <a href="{{ url('wallets') }}" class="btn btn-primary">Gerenciar carteiras</a>
          </div>
          <!-- /.card-header -->
          <div class="card-body p-0">
            <table class="table table-wallets">
              <thead>
                <tr>
                  <th>Nome</th>
                  <th>Tipo</th>
                  <th>Saldo</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($wallets as $wallet){?>
                <tr>
                  <td>
                    <a href="{{ url('/wallets/edit/') }}/<?php echo $wallet->id ?>">
                      <?php echo $wallet->titulo; ?>
                    </a>
                  </td>
                  <td><?php echo $wallet->exibir_no_saldo ? "Conta" : "Reserva" ?></td>
                  <td>R$ <?php echo number_format($wallet->saldo,2,",",".") ?></td>
                </tr>
                <?php
                  if ($wallet->childs){
                    foreach ($wallet->childs as $walletChild){
                ?>
                <tr class="child">
                  <td>
                    <a href="{{ url('/wallets/edit/') }}/<?php echo $walletChild->id ?>">
                      <?php echo "{$walletChild->parent->titulo} -> {$walletChild->titulo}"; ?>
                    </a>
                  </td>
                  <td><?php echo $walletChild->exibir_no_saldo ? "Conta" : "Reserva" ?></td>
                  <td>R$ <?php echo number_format($walletChild->saldo,2,",",".") ?></td>
                </tr>
                <?php
                  }
                }
                ?>
              <?php } ?>
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
