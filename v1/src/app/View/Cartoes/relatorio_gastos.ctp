<div class="row">
	<div class="col-md-12">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">Gastos com cartão</h3>
			</div>	
			<div class="panel-body">
				<?php if ($cartoes){ ?>
				<div class="table-responsive">
					<table class="table table-hover table-condensed">
						<tr>
							<th>Cartão</th>
							<?php for($i=1;$i<=12;$i++){ ?>
								<th><?=$i?>/<?=$ano?></th>
							<?php } ?>
						</tr>
						<?php foreach ($cartoes as $cartao){ ?>
							<tr>
								<td><?=$cartao['Cartao']['descricao']?></td>
								<?php for($i=1;$i<=12;$i++){ ?>
									<?php if (isset($cartao['gastos'][$i])){ ?>
										<td><?=number_format($cartao['gastos'][$i],2,",",".")?></td>
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