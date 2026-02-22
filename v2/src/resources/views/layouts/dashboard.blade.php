<!DOCTYPE html>
<!--
This is a starter template page. Use this page to start your new project from
scratch. This page gets rid of all links and provides the needed markup only.
-->
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="x-ua-compatible" content="ie=edge">

  <title>Dashboard | TotalControle</title>

  <!-- Font Awesome Icons -->
  <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">

  <link href="https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css" rel="stylesheet">

  <!-- Theme style -->
  <link rel="stylesheet" href="{{ asset('dist/css/adminlte.min.css') }}">
  <!-- Google Font: Source Sans Pro -->
  <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">

  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
</head>
<body class="hold-transition layout-top-nav">
<div class="wrapper">

  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand-md navbar-light navbar-white">
    <div class="container">
      <a href="/" class="navbar-brand">
        <b>Total</b>Controle
      </a>

      <button class="navbar-toggler order-1" type="button" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse order-3" id="navbarCollapse">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
          <li class="nav-item">
            <a href="{{ url('/dashboard') }}" class="nav-link">Dashboard</a>
          </li>
          <li class="nav-item dropdown">
            <a id="dropdownSubMenu1" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="nav-link dropdown-toggle">Financeiro</a>
            <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
              <li><a href="{{ url('/transactions') }}" class="dropdown-item">Movimentações</a></li>
              <li><a href="{{ url('/wallets/dashboard') }}" class="dropdown-item">Resumo de carteiras</a></li>
              <li><a href="{{ url('/transactions/import') }}" class="dropdown-item">Importar Tabela Cartão</a></li>
            </ul>
          </li>
          <li class="nav-item dropdown">
            <a id="dropdownSubMenu1" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="nav-link dropdown-toggle">Cadastros</a>
            <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
              <li><a href="{{ url('/categories') }}" class="dropdown-item">Categorias</a></li>
              <li><a href="{{ url('/wallets') }}" class="dropdown-item">Carteiras</a></li>
              <li><a href="{{ url('/credit_cards') }}" class="dropdown-item">Cartões de crédito</a></li>
              <li><a href="{{ url('/contacts') }}" class="dropdown-item">Contatos</a></li>
              <li><a href="{{ url('/products') }}" class="dropdown-item">Produtos</a></li>
            </ul>
          </li>
        </ul>

      <!-- Right navbar links -->
      <ul class="order-1 order-md-3 navbar-nav navbar-no-expand ml-auto">
        <!-- Notifications Dropdown Menu -->
        <li class="nav-item dropdown">
          <a class="nav-link" data-toggle="dropdown" href="#">
            <i class="far fa-user"></i> Willian
          </a>
          <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
            <a href="#" class="dropdown-item">
              <i class="fas fa-envelope mr-2"></i> Configurações
            </a>
            <a href="#" class="dropdown-item">
              <i class="fas fa-envelope mr-2"></i> Faturamento
            </a>
            <a href="#" class="dropdown-item">
              <i class="fas fa-envelope mr-2"></i> Sair
            </a>
          </div>
        </li>
      </ul>
    </div>
  </nav>
  <!-- /.navbar -->

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    @if($errors->any())
    <div class="content">
      <div class="container">
        <div class="row">
          <div class="col-md-12">
            <div class="alert alert-danger">
            @foreach ($errors->all() as $error)
              {{ $error }}<br />
            @endforeach
            </div>
          </div>
        </div>
      </div>
    </div>
    @endif

    @if (\Session::has('success'))
    <div class="content">
      <div class="container">
        <div class="row">
          <div class="col-md-12">
            <div class="alert alert-success">{!! \Session::get('success') !!}</div>
          </div>
        </div>
      </div>
    </div>
    @endif

    @yield('content')

    <a id="open-modal-transacions-save" href="#" class="btn btn-primary back-to-top" role="button" aria-label="Scroll to top">
      <i class="fas fa-plus"></i> Novo lançamento
    </a>
  </div>
  <!-- /.content-wrapper -->

  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
    <div class="p-3">
      <h5>Title</h5>
      <p>Sidebar content</p>
    </div>
  </aside>
  <!-- /.control-sidebar -->

  <!-- Main Footer -->
  <footer class="main-footer">
    <!-- To the right -->
    <div class="float-right d-none d-sm-inline">
      Anything you want
    </div>
    <!-- Default to the left -->
    <strong>Copyright &copy; 2014-2019 <a href="https://adminlte.io">AdminLTE.io</a>.</strong> All rights reserved.
  </footer>
</div>
<!-- ./wrapper -->

<div class="modal fade show" id="modal" aria-modal="true">
  <div class="modal-dialog modal-xl modal-loading">
    <div class="modal-content">
      <div class="modal-body">
        <div class="spinner"></div>
      </div>
      <div class="modal-footer justify-content-between">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
      </div>
    </div>
    <!-- /.modal-content -->
  </div>
  <!-- /.modal-dialog -->
</div>

<!-- REQUIRED SCRIPTS -->

<!-- jQuery -->
<script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>
<!-- Bootstrap 4 -->
<script src="{{ asset('plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<!-- AdminLTE App -->
<script src="{{ asset('dist/js/adminlte.min.js') }}"></script>

<script src="{{ asset('js/main.js') }}"></script>

<script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>

</body>
</html>
