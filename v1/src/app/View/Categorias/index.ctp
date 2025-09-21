<div class="alert alert-warning">Uma nova versão beta dessa tela está disponível. <a href="<?php echo $NEW_APP_URL ?>/categories" target="_blank">Acesse aqui</a>.</div>

<div class="row">
	<div class="usuario div-tipo-usuario col-md-12">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">Inserir categoria</h3>
			</div>
			<div class="panel-body">
				<?=$this->Form->create('Categoria',array('url' => 'salvar'));?>
					<div class="row">
						<!--<?=$this->Form->input('return_type',array('name'=>'return_type','value'=>'json','type'=>'hidden','class'=>'','label'=>false,'div'=>false));?>-->
						<div class="form-group col-md-2">
							<label>Nome</label>
							<?=$this->Form->input('nome',array('type'=>'text','class'=>'form-control','label'=>false,'div'=>false));?>
						</div>
						<div class="form-group col-md-2">
							<label>Categoria pai</label>
							<?=$this->Form->input('parent_id',array('empty'=>'Selecione...','options'=>$categorias,'class'=>'form-control','label'=>false,'div'=>false));?>
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
	<div class="col-md-12 lista-categorias"></div>
</div>
