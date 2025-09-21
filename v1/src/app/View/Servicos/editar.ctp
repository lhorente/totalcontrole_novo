<div class="row">
	<?=$this->Form->create('Servico',array('url' => 'salvar','inputDefaults'=>array('class'=>'form-control','div'=>'form-group col-md-2')));?>
	<?=$this->Form->input('id',array('type'=>'hidden','value'=>$servico['Servico']['id']));?>
	<div class="usuario div-tipo-usuario col-md-12">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">Editar serviço</h3>
			</div>			
			<div class="panel-body">
				<div class="row">
					<?=$this->Form->input('descricao',array('type'=>'text','label'=>'Descrição','value'=>$servico['Servico']['descricao']));?>
					<?=$this->Form->input('valor_hora',array('type'=>'text','class'=>'form-control input-valor input-servico-orcamento-calcula','label'=>'Valor/Hora','value'=>number_format($servico['Servico']['valor_hora'],2,",",".")));?>
					<?=$this->Form->input('desconto',array('type'=>'text','class'=>'form-control input-valor input-servico-orcamento-calcula','label'=>'Desconto','value'=>number_format($servico['Servico']['desconto'],2,",",".")));?>
					<?=$this->Form->input('quantidade_horas',array('type'=>'text','readonly'=>true,'label'=>'Quantidade de horas','value'=>$servico['Servico']['quantidade_horas']));?>
					<?=$this->Form->input('valor',array('type'=>'text','disabled'=>true,'label'=>'Valor total','value'=>number_format($servico['Servico']['quantidade_horas']*$servico['Servico']['valor_hora'],2,",",".")));?>
					<?=$this->Form->input('id_cliente',array('options'=>$clientes,'label'=>'Cliente','value'=>$servico['Cliente']['id']));?>
				</div>
				<div class="row">
					<?=$this->Form->input('data_pedido',array('type'=>'text','class'=>'form-control datepicker','label'=>'Data pedido','value'=>$servico['Servico']['data_pedido']));?>
					<?=$this->Form->input('data_aprovacao',array('type'=>'text','class'=>'form-control datepicker','label'=>'Data aprovação','value'=>$servico['Servico']['data_aprovacao']));?>
					<?=$this->Form->input('status',array('options'=>array('aprovado'=>'Aprovado','desenvolvimento'=>'Desenvolvimento','finalizado'=>'Finalizado','pendente'=>'Pendente','recusado'=>'Recusado'),'label'=>'Situação','value'=>$servico['Servico']['status']));?>
				</div>
			</div>
		</div>
	</div>
	<div class="usuario div-tipo-usuario col-md-12">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">Orçamento</h3>
			</div>			
			<div class="panel-body lista-servicos">
				<table class="table table-hover table-condensed table-servicos-orcamento">
					<tr>
						<th>Descrição</th>
						<th>Horas</th>
						<th>&nbsp;</th>
					</tr>
					<?php if ($servico['ServicosOrcamentos']){ ?>
						<?php foreach ($servico['ServicosOrcamentos'] as $i=>$s){ ?>
						<tr class="row-servicos-orcamento">
							<td class="col-md-10">
								<input name="data[orcamentos][<?=$i?>][ServicosOrcamento][descricao]" class="form-control input-sm" type="text" value="<?=$s['ServicosOrcamento']['descricao']?>" />	
							</td>
							<td class="col-md-1">
								<input name="data[orcamentos][<?=$i?>][ServicosOrcamento][horas]" class="form-control input-sm input-servico-orcamento-calcula input-servico-orcamento-horas" type="text" value="<?=$s['ServicosOrcamento']['horas']?>" />	
							</td>
							<td class="col-md-1">
								<a href="/servicos/excluir/" class="btn-excluir-servico" title="Excluir"><span class="glyphicon glyphicon-remove"></span></a>
							</td>
						</tr>
						<?php } ?>
					<?php } ?>
				</table>	
			</div>
		</div>
		<div class="row">
			<div class="form-group col-md-1">
				<button type="submit" class="btn btn-default form-control">Salvar</button>
			</div>
		</div>			
	</div>		
	<?=$this->Form->end();?>
</div>