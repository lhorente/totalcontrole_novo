<div class="panel panel-default">
	<div class="panel-heading">
		<h3 class="panel-title">Categorias</h3>
	</div>			
	<div class="panel-body lista-clientes">
		<?php if ($categorias){ ?>
		<table class="table table-hover table-condensed">
			<tr>
				<th class="col-expand">&nbsp;</th>
				<th>Nome</th>
				<th>Tipo</th>
				<th>Ações</th>
			</tr>
			<?php foreach ($categorias as $i=>$r){ ?>
				<tr class="item" data-item="<?=$r['Categoria']['id']?>">
					<td class="col-expand">
						<?php if ($r['children']){ ?>
							<a href="" class="bt-toggle-expand"><i class="fa fa-plus"></i></a>
						<?php } ?>
					</td>
					<td><?=$r['Categoria']['nome']?></td>
					<td><?=$r['Categoria']['tipo']?></td>
					<td>
						<a href="/categorias/editar/<?=$r['Categoria']['id']?>" class="btn-editar-categoria" title="Editar"><span class="glyphicon glyphicon-edit"></span></a>
						<a href="/categorias/excluir/<?=$r['Categoria']['id']?>" class="btn-excluir-categoria2" title="Excluir"><span class="glyphicon glyphicon-remove"></span></a>
					</td>
				</tr>
				<?php if ($r['children']){ ?>
					<?php foreach ($r['children'] as $i2=>$r2){ ?>
					<tr class="tr-with-parent item hide" data-item="<?=$r2['Categoria']['id']?>" data-parent="<?=$r['Categoria']['id']?>">
						<td class="col-expand"></td>
						<td>&nbsp;&nbsp;&nbsp;&nbsp;<?=$r2['Categoria']['nome']?></td>
						<td><?=$r2['Categoria']['tipo']?></td>
						<td>
							<a href="/categorias/editar/<?=$r2['Categoria']['id']?>" class="btn-editar-categoria" title="Editar"><span class="glyphicon glyphicon-edit"></span></a>
							<a href="/categorias/excluir/<?=$r2['Categoria']['id']?>" class="btn-excluir-categoria2" title="Excluir"><span class="glyphicon glyphicon-remove"></span></a>
						</td>
					</tr>					
					<?php } ?>
				<?php } ?>
			<?php } ?>
		</table>
		<?php } ?>			
	</div>
</div>