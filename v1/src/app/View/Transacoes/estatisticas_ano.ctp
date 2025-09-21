
<div class="row">
	<div class="col-md-12">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">Filtrar</h3>
			</div>
			<div class="panel-body">
				<?=$this->Form->create('Transacao',array('type'=>'GET','inputDefaults'=>array('class'=>'form-control','label'=>false,'div'=>false)));?>
					<div class="row">
						<div class="form-group col-md-2">
							<label>Ano</label>
							<?=$this->Form->input('data_inicio',array('name'=>'ano','type'=>'select','options'=>$anos,'value'=>$ano));?>
						</div>
						<div class="form-group col-md-2">
							<label>&nbsp;</label>
							<button type="submit" class="btn btn-default form-control">Atualizar</button>
						</div>
					</div>
				<?=$this->Form->end();?>
			</div>
		</div>
	</div>
</div>

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
								$month = $date->format("M"); ?>
								<th><a href="?ano=<?php echo $month ?>"><?php echo $month ?></a></th>
							<?php } ?>
							<th>Total</th>
							<th>Orçamento</th>
						</tr>
					</thead>
					<tbody>
						<?php
						foreach ($data['categories'] as $id_categoria=>$categoria){
								$total_categoria = $categoria['total'];
								$total_orcamento = 0;

								if (isset($orcamento['categories'][$id_categoria])){
									$total_orcamento = $orcamento['categories'][$id_categoria]['years'][$ano];
								}

								// Calcula media mensal
								$media_orcamento = $total_orcamento/12;
								$media_categoria = $total_categoria/12;

								if ($media_categoria > $media_orcamento){
									$class_categoria = "alert-danger";
								} else {
									$class_categoria = "alert-success";
								}
							?>
							<tr>
								<td><?php echo $categoria['nome'] ?></td>
								<?php
								foreach($categoria['months'] as $mes=>$total){
									$total = number_format($total,2,",",".");
									$link_valor = "/transacoes/$ano/$mes?categoria=$id_categoria";
									echo '<td><a href="'.$link_valor.'">'.$total.'</a></td>';
								}
								?>
								<td class="td-total-categoria <?php echo $class_categoria ?>"><?php echo number_format($total_categoria,2,",",".") ?></td>
								<td class="td-orcamento">
									<div class="has-feedback">
										<input type="text" class="form-control input-valor input-orcamento" data-id_categoria="<?=$id_categoria?>" data-ano="<?=$ano?>" value="<?php echo number_format($total_orcamento,2,",",".") ?>" data-ivalue="<?php echo number_format($total_orcamento,2,",",".") ?>" />
										<span class="fa fa-check form-control-feedback input-status" style="display:none;"></span>
									</div>
								</td>
								<!--<td><?php echo number_format($total_orcamento,2,",",".") ?></td>-->
							</tr>
						<?php } ?>

						<tfoot>
							<tr>
								<th>Categoria</th>
								<?php
								$total_ano = 0;
								foreach($daterange as $date){
									$month = $date->format("n");
									$total_mes = $data['months'][$month];
									$total_ano += $total_mes;
									?>
									<th><?php echo number_format($total_mes,2,",",".") ?></th>
								<?php } ?>

								<?php

									if ($total_ano > $orcamento['total'][$ano]){
										$class_categoria = "alert-danger";
									} else {
										$class_categoria = "alert-success";
									}

								?>

								<th class="total_ano <?php echo $class_categoria ?>"><?php echo number_format($total_ano,2,",",".") ?></th>
								<th class="orcamento_total_ano">
									<?php echo number_format($orcamento['total'][$ano],2,",",".") ?>
								</th>
							</tr>
						</tfoot>


					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
