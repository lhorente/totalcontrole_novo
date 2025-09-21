@extends('layouts.dashboard')

@section('content')
<div class="content-header">
      <div class="container">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0 text-dark"> Produtos</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Dashboard</a></li>
              <li class="breadcrumb-item active">Produtos</li>
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
                          <a href="products/new" class="btn btn-primary">Adicionar produto</a>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body p-0">
                          <table class="table">
                            <thead>
                              <tr>
                                <th style="width: 10px">#</th>
                                <th>Nome</th>
                                <th>Valor de venda</th>
                                <th style="width: 40px">Estoque</th>
                              </tr>
                            </thead>
                            <tbody>
                              <tr>
                                <td>1.</td>
                                <td>
                                  <a href="/products/edit">Baby Doll TAM G</a>
                                </td>
                                <td>65,00</td>
                                <td>2</td>
                              </tr>
                              <tr>
                                <td>1.</td>
                                <td>Baby Doll TAM G</td>
                                <td>65,00</td>
                                <td>2</td>
                              </tr>
                              <tr>
                                <td>1.</td>
                                <td>Baby Doll TAM G</td>
                                <td>65,00</td>
                                <td>2</td>
                              </tr>
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
