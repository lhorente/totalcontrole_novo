<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <title>Total Controle</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <link href="/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <!--<link href="http://code.ionicframework.com/ionicons/2.0.0/css/ionicons.min.css" rel="stylesheet" type="text/css" />    -->
    <link href="/dist/css/AdminLTE.min.css" rel="stylesheet" type="text/css" />

	<link rel="stylesheet" href="/css/font-awesome.min.css"/>
	<link rel="stylesheet" href="/css/vendor/colorbox/colorbox.css"/>
	<link rel="stylesheet" href="/css/vendor/jqueryui/jquery-ui.min.css"/>

    <link href="/dist/css/skins/_all-skins.min.css" rel="stylesheet" type="text/css" />
    <link href="/plugins/iCheck/flat/blue.css" rel="stylesheet" type="text/css" />
    <link href="/plugins/morris/morris.css" rel="stylesheet" type="text/css" />
    <link href="/plugins/jvectormap/jquery-jvectormap-1.2.2.css" rel="stylesheet" type="text/css" />
    <link href="/plugins/datepicker/datepicker3.css" rel="stylesheet" type="text/css" />
    <link href="/plugins/daterangepicker/daterangepicker-bs3.css" rel="stylesheet" type="text/css" />
    <link href="/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css" rel="stylesheet" type="text/css" />

	<link rel="stylesheet" href="/css/main.css?v=<?php echo time() ?>"/>

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
  </head>
  <body class="skin-blue">
    <div class="wrapper">

      <header class="main-header">
        <!-- Logo -->
        <a href="/" class="logo"><b>Total</b>Controle</a>
        <!-- Header Navbar: style can be found in header.less -->
        <nav class="navbar navbar-static-top" role="navigation">
          <!-- Sidebar toggle button-->
          <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
            <span class="sr-only">Toggle navigation</span>
          </a>
          <div class="navbar-custom-menu user-info-menu">
            <ul class="nav navbar-nav">
              <!-- User Account: style can be found in dropdown.less -->
              <li class="dropdown user user-menu">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                  <span class="hidden-xs"><?php echo $User['name'] ?></span>
                </a>
                <ul class="dropdown-menu menu-usuario">
                  <!-- User image -->
                  <li class="user-header" style="height:auto;">
                    <p><?php echo $User['name'] ?></p>
					<p><a href="/logout">Logout</a></p>
                  </li>
					<li class="user-body">
            <div class="row box-saldo">
        		  <div class="col-xs-12 text-center">
        			<table class="table">
        				<tr>
        					<th>Carteira</th>
        					<th>Disponível</th>
        					<th>Reservado</th>
        				</tr>
        				<?php
        					$total_disponivel = 0;
        					$total_reservado = 0;
        				?>
        				<?php foreach ($CAIXAS->results as $CAIXA){ ?>
        				<?php
        					$total_disponivel += $CAIXA['Caixa']['saldo'];
        					$total_reservado += 0;
        				?>
        				<tr>
        					<td><a href="/caixas/trocar/<?php echo $CAIXA['Caixa']['id'] ?>"><?php echo $CAIXA['Caixa']['titulo']?></a></td>
        					<td>R$ <?php echo number_format($CAIXA['Caixa']['saldo_liquido'],2,",",".") ?></td>
        					<td>R$ <?php echo number_format($CAIXA['Caixa']['saldo_reserva'],2,",",".") ?></td>
        				</tr>
        				<?php } ?>
        				<tr>
        					<th>TOTAL</th>
        					<th>R$ <?php echo number_format($CAIXAS->saldo_liquido,2,",",".") ?></th>
        					<th>R$ <?php echo number_format($CAIXAS->saldo_reserva,2,",",".") ?></th>
        				</tr>
        			</table>
        		  </div>

        		  <div class="col-xs-12 text-center">
        			<span style="font-weight: bold;">Saldo seguro</span><br>
        			<span>R$ <?php echo number_format($SALDO_SEGURO,2,",",".") ?></span>
        		  </div>
        		</div>
					<!-- /.row -->
					</li>
                </ul>
              </li>
            </ul>
          </div>
        </nav>
      </header>
      <!-- Left side column. contains the logo and sidebar -->
		<aside class="main-sidebar">
			<!-- sidebar: style can be found in sidebar.less -->
			<section class="sidebar">

				<!-- sidebar menu: : style can be found in sidebar.less -->
				<?php if (isset($User) && $User){ ?>
					<?php if ($Menu){ ?>
						<ul class="sidebar-menu">
							<?php foreach ($Menu as $tela) { ?>
								<?php if ($tela['children']){ ?>
								<li class="treeview">
									<a href="#">
										<i class="fa fa-dashboard"></i> <span><?=$tela['Tela']['nome']?></span> <i class="fa fa-angle-left pull-right"></i>
									</a>
									<ul class="treeview-menu">
										<?php foreach ($tela['children'] as $tela2){ ?>
											<li class="active">
												<a href="<?=Router::url(array('controller'=>$tela2['Tela']['controller'], 'action'=>$tela2['Tela']['action']));?>">
													<?php if ($tela2['Tela']['class_icone']){ ?>
														<i class="<?=$tela2['Tela']['class_icone']?>"></i>
													<?php } else { ?>
														<i class="fa fa-circle-o"></i>
													<?php } ?>
													<?=$tela2['Tela']['nome']?>
												</a>
											</li>
										<?php } ?>
									</ul>
								</li>
								<?php } else { ?>
									<li><a href="<?=Router::url(array('controller'=>$tela['Tela']['controller'], 'action'=>$tela['Tela']['action']));?>"><i class="fa fa-circle-o text-danger"></i> <?=$tela['Tela']['nome']?></a></li>
								<?php } ?>
							<?php } ?>
						</ul>
					  <?php } ?>
				  <?php } ?>
			</section>
			<!-- /.sidebar -->
		</aside>
		<!-- Content Wrapper. Contains page content -->
		<div class="content-wrapper">
			<!-- Content Header (Page header) -->
			<section class="content-header">
				<h1>
					<?=$PageTitle?>
				</h1>
				<?=$this->element('migalha');?>
			</section>

			<!-- Main content -->
			<section class="content" style="position:relative;">
				<?=$this->Session->flash();?>
				<?php echo $this->fetch('content'); ?>
				<div id="content_mask" style="display:none;z-index: 999;position: absolute;width: 100%;opacity: 0.2;height: 830px;background-color: #000;left: 0;top: 0;"></div>
				<i id="loading_mask" class="fa fa-spinner fa-spin fa-5x" style="display:none;z-index: 9999;top:400px;left:600px;color: #87CB31;position: absolute;opacity: 1;"></i>
			</section><!-- /.content -->
		</div><!-- /.content-wrapper -->
		<footer class="main-footer">
			<div class="pull-right hidden-xs">
				<b>Versão</b> 2.0
			</div>
			<strong>Total Controle 2012-2015
		</footer>
    </div><!-- ./wrapper -->

	<div class="modal fade" id="modal-loading" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	  <div class="modal-dialog">
		<div class="modal-content" style="background-color:transparent;">
		  <div class="modal-body">
				<div class="panel-body" style="text-align:center;">
					<i class="fa fa-spin fa-spinner" style="font-size:40px;color:#FFF"></i>
				</div>
		  </div>
		</div>
	  </div>
	</div>

	<div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true"></div>

	<script type="text/javascript" src="/js/vendor/jquery-1.11.0.min.js"></script>
	<script type="text/javascript" src="/js/vendor/jquery-ui.min.js"></script>
	<script type="text/javascript" src="/js/vendor/jquery.ui.datepicker-pt-BR.min.js"></script>
	<script type="text/javascript" src="/js/vendor/jquery.colorbox-min.js"></script>
	<script type="text/javascript" src="/js/vendor/jquery.maskedinput.min.js"></script>
	<script type="text/javascript" src="/js/vendor/highcharts.js"></script>
	<script type="text/javascript" src="/js/vendor/simple-mask-money.js"></script>
	<!--<script type="text/javascript" src="/js/vendor/jquery.maskMoney.min.js"></script>-->

    <!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
    <script>
      //$.widget.bridge('uibutton', $.ui.button);
    </script>
    <!-- Bootstrap 3.3.2 JS -->
    <script src="/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
    <script src="/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js" type="text/javascript"></script>
    <script src="/dist/js/app.min.js" type="text/javascript"></script>

	<script type="text/javascript" src="/js/main.js?v=<?php echo time() ?>"></script>
	<?php if (file_exists(WWW_ROOT . "/js/pages/{$this->request->params['controller']}.js")){ ?>
		<script type="text/javascript" src="/js/pages/<?=$this->request->params['controller']?>.js?v=<?php echo time() ?>"></script>
	<?php } ?>
  </body>
</html>
