<?php
	$current_year = date("Y");
	$start_year = $current_year-3;
	$end_year = $current_year+1;
?>

<div class="row">
	<section class="col-lg-12">
		<div class="box">
			<div class="box-body">
				<?php if ($categorias){ ?>
				<table class="table table-hover table-condensed table-striped table-bordered">
					<tr>
						<th>Nome</th>
						<?php for($year=$start_year;$year<=$end_year;$year++){ ?>
							<th><?php echo $year ?> </th>
						<?php } ?>
					</tr>
					<?php foreach ($categorias as $id_categoria=>$nome_categoria){?>

						<tr class="item" data-item="<?=$id_categoria?>">
							<td><?=$nome_categoria?></td>
							<?php for($year=$start_year;$year<=$end_year;$year++){ ?>
								<?php
									$valor = "0,00";
									if (isset($orcamento['categories'][$id_categoria])){
										$valor = number_format($orcamento['categories'][$id_categoria]['years'][$year],2,",",".");
									}
								?>
								<td class="td-orcamento">
									<div class="has-feedback">
										<input type="text" class="form-control input-valor input-orcamento" data-id_categoria="<?=$id_categoria?>" data-ano="<?=$year?>" value="<?php echo $valor ?>" data-ivalue="<?php echo $valor ?>" />
										<span class="fa fa-check form-control-feedback input-status" style="display:none;"></span>
									</div>
								</td>
							<?php } ?>
						</tr>
					<?php } ?>

					<tr class="item" data-item="<?=$id_categoria?>">
						<td>TOTAL</td>
						<?php for($year=$start_year;$year<=$end_year;$year++){ ?>
							<?php
								$valor = "0,00";
								if (isset($orcamento['categories'][$id_categoria])){
									$valor = number_format($orcamento['total'][$year],2,",",".");
								}
							?>
							<td class="td-orcamento">
								<?php echo $valor ?>
							</td>
						<?php } ?>
					</tr>
				</table>
				<?php } ?>
			</div><!-- /.box-body -->
		</div>
	</section>
</div>
