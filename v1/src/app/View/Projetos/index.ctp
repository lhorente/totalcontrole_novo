<div class="row">
	<div class="usuario div-tipo-usuario col-md-12">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">Inserir projeto</h3>
			</div>			
			<div class="panel-body">
				<?=$this->Form->create('Projeto',array('url' => 'salvar'));?>
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
				<?=$this->Form->end();?>
			</div>
		</div>
	</div>
	<div class="usuario div-tipo-usuario col-md-12">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">Projetos</h3>
			</div>			
			<div class="panel-body lista-projetos">
				<?php if ($projetos){ ?>
				<?=$this->Form->create('Projeto',array('url' => 'salvar','id'=>'ProjetoEditarForm'));?>
				<table class="table table-hover table-condensed">
					<tr>
						<th>Nome</th>
						<th>Cliente</th>
						<th>Ações</th>
					</tr>
					<?php foreach ($projetos as $r){ ?>
					<tr>
						<td><a href="/projetos/editar/<?=$r['Projeto']['id']?>"><?=$r['Projeto']['nome']?></a></td>
						<td><?=$r['Cliente']['nome']?></td>
						<td>
							<a href="/projetos/excluir/<?=$r['Projeto']['id']?>" class="btn-excluir-projeto" title="Excluir"><span class="glyphicon glyphicon-remove"></span></a>
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