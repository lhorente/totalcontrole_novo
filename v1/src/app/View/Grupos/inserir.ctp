<ul class="nav nav-tabs" role="tablist">
	<li class="active"><a href="<?=$this->webroot?>grupos/inserir">Inserir grupo</a></li>
	<li class="disabled"><a href="">Editar grupo</a></li>
	<li class=""><a href="<?=$this->webroot?>grupos">Listar grupos</a></li>
</ul>
<div class="tab-content col-md-12">	
	<?=$this->Form->create('Grupo',array('action'=>'inserir'))?>
	<div class="row">
		<div class="col-md-12">
			<h4 class="content-title"><u>Grupo</u></h4>
			<div class="row">
				<div class="form-group col-md-4">
					<label for="">Nome</label>
					<?=$this->Form->input('nome',array('class'=>'form-control','placeholder'=>'','label'=>false,'div'=>false,'errorMessage' => false,'required'=>false));?>
				</div>
				<div class="form-group col-md-1">
					<label>&nbsp;</label>
					<button type="submit" class="form-control btn btn-default">Salvar</button>
				</div>
			</div>
		</div>
	</div>
	<?=$this->Form->end();?>
</div>