<div class="panel panel-default">
	<div class="panel-heading">
		<h3 class="panel-title">Caixas</h3>
	</div>			
	<div class="panel-body lista-clientes">
		<?php if ($caixas){ ?>
		<table class="table table-hover table-condensed">
			<tr>
				<th>Título</th>
				<th>Exibe no Saldo Geral</th>
				<th>Ações</th>
			</tr>
			<?php foreach ($caixas as $i=>$r){ ?>
				<tr class="item" data-item="<?=$r['Caixa']['id']?>">
					<td><?=$r['Caixa']['titulo']?></td>
					<td><?=$r['Caixa']['exibir_no_saldo']?></td>
					<td>
						<a href="/caixas/editar/<?=$r['Caixa']['id']?>" class="btn-editar-caixa" title="Editar"><span class="glyphicon glyphicon-edit"></span></a>
						<a href="/caixas/excluir/<?=$r['Caixa']['id']?>" class="btn-excluir-caixa" title="Excluir"><span class="glyphicon glyphicon-remove"></span></a>
					</td>
				</tr>
			<?php } ?>
		</table>
		<?php } ?>			
	</div>
</div>