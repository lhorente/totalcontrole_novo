<ul class="nav navbar-nav">
  <!-- User Account: style can be found in dropdown.less -->
  <li class="dropdown user user-menu">
	<a href="#" class="dropdown-toggle" data-toggle="dropdown">
	  <span class="hidden-xs"><?=$User['name']?></span>
	</a>
	<ul class="dropdown-menu menu-usuario">
	  <!-- User image -->
	  <li class="user-header" style="height:auto;">
		<p><?=$User['name']?></p>
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
