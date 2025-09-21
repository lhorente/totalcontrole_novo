<ul class="nav nav-tabs" role="tablist">
	<li class=""><a href="<?=$this->webroot?>grupos/inserir">Inserir grupo</a></li>
	<li class="disabled"><a href="">Editar grupo</a></li>
	<li class="active"><a href="<?=$this->webroot?>grupos">Listar grupos</a></li>
</ul>
<div class="tab-content col-md-12">	
	<div class="row">
		<?php if ($grupos){ ?>
		<div class="col-md-12">
			<table class="table table-striped table-hover table-bordered">
				<thead>
					<tr>
						<th>Nome</th>
						<th>&nbsp;</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($grupos as $grupo){ ?>
					<tr>
						<td><?=$grupo['Grupo']['nome']?></td>
						<td>
							<?=$this->Permissao->makeLink('<span class="glyphicon glyphicon-edit">','grupos','editar',$grupo['Grupo']['id'],'')?>
							<?=$this->Permissao->makeLink('<span class="glyphicon glyphicon-remove">','grupos','excluir',$grupo['Grupo']['id'],'','','Tem certeza que deseja excluir?')?>
						</td>
					</tr>
					<?php } ?>
				</tbody>
			</table>
		</div>
		<?php } ?>
	</div>
</div>