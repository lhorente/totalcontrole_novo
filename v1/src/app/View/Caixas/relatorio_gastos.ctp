<div class="row">
	<div class="col-md-12">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">Gastos por categoria</h3>
			</div>	
			<div class="panel-body">
				<?php if ($categorias){ ?>
				<div class="table-responsive">
					<table class="table table-hover table-condensed">
						<tr>
							<th>Categoria</th>
							<?php for($i=1;$i<=12;$i++){ ?>
								<th><?=$i?>/<?=$ano?></th>
							<?php } ?>
						</tr>
						<?php foreach ($categorias as $categoria){ ?>
							<tr>
								<td><?=$categoria['Categoria']['nome']?></td>
								<?php for($i=1;$i<=12;$i++){ ?>
									<?php if (isset($categoria['gastos'][$i])){ ?>
										<td><?=number_format($categoria['gastos'][$i],2,",",".")?></td>
									<?php } else { ?>
										<td>R$ 0,00</td>
									<?php } ?>
								<?php } ?>
							</tr>
						<?php } ?>
					</table>
				</div>
				<?php } ?>
			</div>
		</div>	
	</div>		
</div>