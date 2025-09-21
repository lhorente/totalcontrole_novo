<div class="row">
	<?=$this->Form->create('Servico',array('url' => 'salvar'));?>
	<?=$this->Form->input('id',array('type'=>'hidden','label'=>false,'div'=>false,'value'=>$servico['Servico']['id']));?>
	<div class="usuario col-md-12">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">Editar Projeto</h3>
			</div>			
			<div class="panel-body">
				<div class="row">
					<div class="form-group col-md-2">
						<label>Nome</label>
						<?=$this->Form->input('nome',array('type'=>'text','class'=>'form-control','label'=>false,'div'=>false));?>
					</div>
					<div class="form-group col-md-2">
						<label>Valor/Hora</label>
						<?=$this->Form->input('valor_hora',array('type'=>'text','class'=>'form-control input-valor','label'=>false,'div'=>false));?>
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
			</div>
		</div>
	</div>
	<div class="usuario col-md-12">
		<div class="row">
			<div class="form-group col-md-1">
				<button type="submit" class="btn btn-default form-control">Salvar</button>
			</div>
		</div>			
	</div>		
	<?=$this->Form->end();?>
</div>