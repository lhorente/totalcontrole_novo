<div class="row">
	<div class="col-md-12">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">Filtrar</h3>
			</div>	
			<div class="panel-body">
				<?=$this->Form->create('Transacao',array('type'=>'GET'));?>
					<div class="row">
						<div class="form-group col-md-2">
							<label>Data de ínicio</label>
							<?php if ($data_inicio){ ?>
								<?=$this->Form->input('data_inicio',array('value'=>$data_inicio->format("d/m/Y"),'name'=>'data_inicio','type'=>'text','class'=>'form-control datepicker','label'=>false,'div'=>false));?>
							<?php } else { ?>
								<?=$this->Form->input('data_inicio',array('name'=>'data_inicio','type'=>'text','class'=>'form-control datepicker','label'=>false,'div'=>false));?>
							<?php } ?>
						</div>
						<div class="form-group col-md-2">
							<label>Data de fim</label>
							<?php if ($data_fim){ ?>
								<?=$this->Form->input('data_fim',array('value'=>$data_fim->format("d/m/Y"),'name'=>'data_fim','type'=>'text','class'=>'form-control datepicker','label'=>false,'div'=>false));?>
							<?php } else { ?>
								<?=$this->Form->input('data_fim',array('name'=>'data_fim','type'=>'text','class'=>'form-control datepicker','label'=>false,'div'=>false));?>
							<?php } ?>
						</div>
						<div class="form-group col-md-1">
							<label>&nbsp;</label>
							<button type="submit" class="btn btn-default form-control">Salvar</button>
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
				<h3 class="panel-title">Gastos</h3>
			</div>	
			<div class="panel-body">
				<?php if ($table_transacoes){ ?>
				<div class="table-responsive">
					<table class="table table-hover table-condensed table-gastos">
						<tr class="row-legendas">
							<th class="col-legenda">&nbsp;</th>
							<?php foreach ($table_transacoes as $mes=>$transacao){ ?>
								<th class="col-valor"><?=$mes?></th>
							<?php } ?>
						</tr>
						<tr class="row-depesas row-grafico">
							<td class="col-legenda">Gastos</td>
							<?php foreach ($table_transacoes as $mes=>$transacao){ ?>
								<td class="col-valor"><?=number_format($transacao['gastos'],2,",",".")?></td>
							<?php } ?>
						</tr>
						<?php if ($table_transacoes_categorias){ ?>
							<?php foreach ($table_transacoes_categorias as $id_categoria=>$categoria){ ?>
								<tr class="row-gastos-categoria hide">
									<td class="col-legenda"><span class="fa fa-level-up fa-rotate-90"></span> <?=$categoria['nome']?></td>
								<?php foreach ($table_transacoes as $mes=>$transacao){ ?>
									<?php if (isset($categoria[$mes])){ ?>
									<td class="col-valor"><?=number_format($categoria[$mes]['despesas'],2,",",".")?></td>
									<?php } else { ?>
									<td class="col-valor">0,00</td>
									<?php } ?>
								<?php } ?>
								</tr>
							<?php } ?>
						<?php } ?>
						<tr class="row-lucros row-grafico">
							<td class="col-legenda">Lucros</td>
							<?php foreach ($table_transacoes as $mes=>$transacao){ ?>
								<td class="col-valor"><?=number_format($transacao['lucros'],2,",",".")?></td>
							<?php } ?>
						</tr>
						<?php if ($table_transacoes_categorias){ ?>
							<?php foreach ($table_transacoes_categorias as $id_categoria=>$categoria){ ?>
								<tr class="row-lucros-categoria hide">
									<td class="col-legenda"><span class="fa fa-level-up fa-rotate-90"></span> <?=$categoria['nome']?></td>
								<?php foreach ($table_transacoes as $mes=>$transacao){ ?>
									<?php if (isset($categoria[$mes])){ ?>
									<td class="col-valor"><?=number_format($categoria[$mes]['lucros'],2,",",".")?></td>
									<?php } else { ?>
									<td class="col-valor">0,00</td>
									<?php } ?>
								<?php } ?>
								</tr>
							<?php } ?>
						<?php } ?>
					</table>
				</div>
				<div class="col-md-12 col-sd-12" id="chart_gastos"></div>
				<?php } ?>
			</div>
		</div>	
	</div>		
</div>