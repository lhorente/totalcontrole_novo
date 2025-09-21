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
            <a href="{{ url('wallets/new') }}" class="btn btn-primary">Adicionar Carteira</a>
          </div>
          <!-- /.card-header -->
          <div class="card-body p-0">
            <table class="table">
              <thead>
                <tr>
                  <th style="width: 10px">#</th>
                  <th>Nome</th>
                  <th>Tipo</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($wallets as $wallet){?>
                <tr>
                  <td><?php echo $wallet->id ?>.</td>
                  <td>
                    <a href="{{ url('/wallets/edit/') }}/<?php echo $wallet->id ?>">
                      <?php echo $wallet->titulo; ?>
                    </a>
                  </td>
                  <td><?php echo $wallet->exibir_no_saldo ? "Conta" : "Reserva" ?></td>
                </tr>
                <?php
                  if ($wallet->childs){
                    foreach ($wallet->childs as $walletChild){
                ?>
                <tr>
                  <td><?php echo $walletChild->id ?>.</td>
                  <td>
                    <a href="{{ url('/wallets/edit/') }}/<?php echo $walletChild->id ?>">
                      <?php echo "{$walletChild->parent->titulo} -> {$walletChild->titulo}"; ?>
                    </a>
                  </td>
                  <td><?php echo $walletChild->exibir_no_saldo ? "Conta" : "Reserva" ?></td>
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
