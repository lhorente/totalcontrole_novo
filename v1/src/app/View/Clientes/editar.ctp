<div class="row">
	<div class="usuario div-tipo-usuario col-md-12">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">Editar pessoa</h3>
			</div>			
			<div class="panel-body">
				<?=$this->Form->create('Cliente',array('url' => 'salvar','id'=>'ClienteEditarForm'));?>
					<?=$this->Form->input('id',array('type'=>'hidden','class'=>'form-control','label'=>false,'div'=>false));?>
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
</div>