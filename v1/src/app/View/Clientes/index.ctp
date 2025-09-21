<div class="alert alert-warning">Uma nova versão beta dessa tela está disponível. <a href="<?php echo $NEW_APP_URL ?>/contacts" target="_blank">Acesse aqui</a>.</div>

<div class="row">
	<div class="usuario div-tipo-usuario col-md-12">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">Inserir cliente</h3>
			</div>
			<div class="panel-body">
				<?=$this->Form->create('Cliente',array('url' => 'salvar'));?>
					<div class="row">
						<div class="form-group col-md-2">
							<label>Nome</label>
							<?=$this->Form->input('nome',array('type'=>'text','class'=>'form-control','label'=>false,'div'=>false));?>
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
				<h3 class="panel-title">Clientes</h3>
			</div>
			<div class="panel-body lista-clientes">
				<?php if ($clientes){ ?>
				<?=$this->Form->create('Cliente',array('url' => 'salvar','id'=>'ClienteEditarForm'));?>
				<table class="table table-hover table-condensed">
					<tr>
						<th>Nome</th>
						<th>Total serviços</th>
						<th>Horas</th>
						<th>Total</th>
						<th>Total pago</th>
						<th>Total a pagar</th>
						<th>Ações</th>
					</tr>
					<?php foreach ($clientes as $r){ ?>
					<tr>
						<td><?=$r['Cliente']['nome']?></td>
						<td><?=$r['Cliente']['total_servicos']?></td>
						<td><?=$r['Cliente']['horas_total_servicos']?></td>
						<td>R$ <?=number_format($r['Cliente']['valor_total_servicos'],2,",",".")?></td>
						<td>R$ <?=number_format($r['Cliente']['valor_total_servicos_pagos'],2,",",".")?></td>
						<td>R$ <?=number_format($r['Cliente']['valor_total_servicos_a_pagar'],2,",",".")?></td>
						<td>
							<a href="/clientes/editar/<?=$r['Cliente']['id']?>" class="btn-excluir-cliente" title="Editar"><span class="glyphicon glyphicon-edit"></span></a>
							<a href="/clientes/excluir/<?=$r['Cliente']['id']?>" class="btn-excluir-cliente" title="Excluir"><span class="glyphicon glyphicon-remove"></span></a>
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
