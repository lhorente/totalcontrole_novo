@extends('layouts.dashboard')

@section('content')
<div class="content-header">
  <div class="container">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 text-dark"> Dashboard Total Controle</h1>
      </div><!-- /.col -->
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{ url('/') }}'">Dashboard</a></li>
        </ol>
      </div><!-- /.col -->
    </div><!-- /.row -->
  </div><!-- /.container-fluid -->
</div>
<div class="content">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-6 col-xs-12">

        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Empréstimos <?php echo __($current_month->format('F')) ?></h3>
            <div class="card-tools">
              <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
              </button>
              <button type="button" class="btn btn-tool" data-card-widget="remove">
                <i class="fas fa-times"></i>
              </button>
            </div>
          </div>

          <div class="card-body p-0">
            <ul class="products-list product-list-in-card pl-2 pr-2">
              <?php if ($lendings): ?>
                <?php foreach ($lendings as $lending): ?>
                  <li class="item">
                    <div class="product-info">
                      <a href="javascript:void(0)" class="product-title"><?php echo $lending->contact_name ?></a>
                      <span class="product-description">
                        Total: <b>R$ <?php echo number_format($lending->total,2,",",".") ?></b> | Pendente: <b>R$ <?php echo number_format($lending->total_pending,2,",",".") ?></b>
                      </span>
                    </div>
                  </li>
                <?php endforeach ?>
              <?php endif ?>
            </ul>
          </div>

          <div class="card-footer text-center">
            <a href="/transactions/search?ps=lendings_not_paid" class="uppercase">Ver detalhes</a>
          </div>
        </div>
      </div>

      <div class="col-md-6 col-xs-12">

        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Empréstimos Atrasados</h3>
            <div class="card-tools">
              <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
              </button>
              <button type="button" class="btn btn-tool" data-card-widget="remove">
                <i class="fas fa-times"></i>
              </button>
            </div>
          </div>

          <div class="card-body p-0">
            <ul class="products-list product-list-in-card pl-2 pr-2">
              <?php if ($lendings_notPaid): ?>
                <?php foreach ($lendings_notPaid as $id_cliente=>$lending): ?>
                  <li class="item">
                    <div class="product-info">
                      <a href="/transactions/search?ps=lendings_not_paid&ct={{ $id_cliente }}" class="product-title"><?php echo $lending->contact_name ?></a>
                      <span class="product-description">
                        Total: <b>R$ <?php echo number_format($lending->total,2,",",".") ?></b>
                      </span>
                    </div>
                  </li>
                <?php endforeach ?>
              <?php endif ?>
            </ul>
          </div>

          <div class="card-footer text-center">
            <a href="/transactions/search?ps=lendings_not_paid" class="uppercase">Ver detalhes</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
