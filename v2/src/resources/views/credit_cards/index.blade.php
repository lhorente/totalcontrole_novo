@extends('layouts.dashboard')

@section('content')
<div class="content-header">
  <div class="container">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 text-dark"> Cartões de crédito</h1>
      </div><!-- /.col -->
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{ url('/') }}'">Dashboard</a></li>
          <li class="breadcrumb-item active">Cartões de crédito</li>
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
            <a href="{{ url('credit_cards/new') }}" class="btn btn-primary">Adicionar cartão de crédito</a>
          </div>
          <!-- /.card-header -->
          <div class="card-body p-0">
            <table class="table">
              <thead>
                <tr>
                  <th style="width: 10px">#</th>
                  <th>Nome</th>
                  <th>Vencimento</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($credit_cards as $credit_card){?>
                <tr>
                  <td><?php echo $credit_card->id ?>.</td>
                  <td>
                    <a href="{{ url('/credit_cards/edit/') }}/<?php echo $credit_card->id ?>"><?php echo $credit_card->descricao ?></a>
                  </td>
                  <td><?php echo $credit_card->dia_vencimento ?></td>
                </tr>
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
