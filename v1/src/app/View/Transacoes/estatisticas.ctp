<div class="row">
	<div class="col-md-12">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">Gastos por categoria</h3>
			</div>
			<div class="panel-body">
				<table class="table table-hover table-striped table-condensed">
					<thead>
						<tr>
							<th>Categoria</th>
							<?php
							foreach($daterange as $date){
								$year = $date->format("Y"); ?>
								<th><a href="?ano=<?php echo $year ?>"><?php echo $year ?></a></th>
							<?php } ?>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($categorias as $id_categoria=>$categoria){ ?>
							<tr>
								<!--<td><?php echo $id_categoria ?></td>-->
								<td><?php echo $categoria ?></td>
								<?php
								foreach($daterange as $date){
									$year = $date->format("Y");
									$chave = "{$id_categoria}_{$year}";

									$valor = 0;
									if (isset($data[$year][$chave])){
										$valor = $data[$year][$chave]['valor'];
										$valor = number_format($valor,2,",",".");
									}
									// pr($valor);

									$link_valor = "/transacoes/{$year}?categoria={$id_categoria}";
									echo '<td><a href="'.$link_valor.'">'.$valor.'</a></td>';
								}
								?>
							</tr>
						<?php } ?>
					</tbody>
				</table>
				<!--<table class="table table-hover table-striped table-condensed">
					<thead>
						<tr>
							<th>Categoria</th>
							<?php if ($mes){ ?>
								<th><?php echo nome_mes($mes_anterior)."/".$ano_anterior ?></th>
							<?php } else { ?>
								<th><?php echo $ano_anterior ?></th>
							<?php } ?>

							<?php if ($mes){ ?>
								<th><?php echo nome_mes($mes)."/".$ano ?></th>
							<?php } else { ?>
								<th><?php echo $ano ?></th>
							<?php } ?>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($categorias as $id_categoria=>$categoria){
								if ($mes){
									$chave_anterior = "{$id_categoria}_{$ano_anterior}_{$mes_anterior}";
									$chave = "{$id_categoria}_{$ano}_{$mes}";
									if (isset($estats_anterior[$chave_anterior]) || isset($estats[$chave])){ ?>
									<tr>
										<td><?php echo $categoria ?></td>
										<?php if (isset($estats_anterior[$chave_anterior])){ ?>
											<td>R$ <?php echo number_format($estats_anterior[$chave_anterior]['valor'],2,",",".") ?></td>
										<?php } else { ?>
											<td>R$ 0,00</td>
										<?php } ?>
										<?php if (isset($estats[$chave])){ ?>
											<td>R$ <?php echo number_format($estats[$chave]['valor'],2,",",".") ?></td>
										<?php } else { ?>
											<td>R$ 0,00</td>
										<?php } ?>
									</tr>
									<?php }
								} else {
									$chave_anterior = "{$id_categoria}_{$ano_anterior}";
									$chave = "{$id_categoria}_{$ano}";
									if (isset($estats_anterior[$chave_anterior]) || isset($estats[$chave])){ ?>
									<tr>
										<td><?php echo $categoria ?></td>
										<?php if (isset($estats_anterior[$chave_anterior])){ ?>
											<td>R$ <?php echo number_format($estats_anterior[$chave_anterior]['valor'],2,",",".") ?></td>
										<?php } else { ?>
											<td>R$ 0,00</td>
										<?php } ?>
										<?php if (isset($estats[$chave]))
										{ ?>
											<td>R$ <?php echo number_format($estats[$chave]['valor'],2,",",".") ?></td>
										<?php } else { ?>
											<td>R$ 0,00</td>
										<?php } ?>
										</tr>
								<?php }
								}
							} ?>
					</tbody>
				</table>-->
			</div>
		</div>
	</div>
</div>
