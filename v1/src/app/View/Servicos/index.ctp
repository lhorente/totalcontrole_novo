<div class="row">
	<div class="usuario div-tipo-usuario col-md-12">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">Inserir serviço</h3>
			</div>			
			<div class="panel-body">
				<?=$this->Form->create('Servico',array('url' => 'salvar'));?>
					<div class="row">
						<div class="form-group col-md-2">
							<label>Descrição</label>
							<?=$this->Form->input('descricao',array('type'=>'text','class'=>'form-control','label'=>false,'div'=>false));?>
						</div>
						<div class="form-group col-md-2">
							<label>Data pedido</label>
							<?=$this->Form->input('data_pedido',array('type'=>'text','class'=>'form-control datepicker','label'=>false,'div'=>false));?>
						</div>
						<div class="form-group col-md-2">
							<label>Data aprovação</label>
							<?=$this->Form->input('data_aprovacao',array('type'=>'text','class'=>'form-control datepicker','label'=>false,'div'=>false));?>
						</div>
						<div class="form-group col-md-2">
							<label>Cliente</label>
							<?=$this->Form->input('id_cliente',array('options'=>$clientes,'class'=>'form-control','label'=>false,'div'=>false));?>
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
	<div class="usuario div-tipo-usuario col-md-12">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">Serviços</h3>
			</div>			
			<div class="panel-body lista-servicos">
				<?php if ($servicos){ ?>
				<?=$this->Form->create('Servico',array('url' => 'salvar','id'=>'ServicoEditarForm'));?>
				<table class="table table-hover table-condensed">
					<tr>
						<th>Descrição</th>
						<th>Valor</th>
						<th>Valor Pago</th>
						<th>Data Pedido</th>
						<th>Data Aprovação</th>
						<th>Cliente</th>
						<th>Status</th>
						<th>Ações</th>
					</tr>
					<?php foreach ($servicos as $r){ ?>
					<tr class="<?=($r['Servico']['valor']>$r['Servico']['valor_pago']?"danger":"")?>">
						<td><a href="/servicos/editar/<?=$r['Servico']['id']?>"><?=$r['Servico']['descricao']?></a></td>
						<td><?=number_format($r['Servico']['valor'],2,",",".")?></td>
						<td><?=number_format($r['Servico']['valor_pago'],2,",",".")?></td>
						<td><?=$r['Servico']['data_pedido']?></td>
						<td><?=$r['Servico']['data_aprovacao']?></td>
						<td><?=$r['Cliente']['nome']?></td>
						<td><?=$r['Servico']['status']?></td>
						<td>
							<a href="/servicos/excluir/<?=$r['Servico']['id']?>" class="btn-excluir-servico" title="Excluir"><span class="glyphicon glyphicon-remove"></span></a>
						</td>
					</tr>
					<?php } ?>
				</table>
				<?=$this->Form->end();?>
				<?php } ?>			
			</div>
		</div>
	</div>		
</div>