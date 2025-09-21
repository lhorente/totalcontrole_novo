<div class="panel panel-default">
	<div class="panel-heading">
		<h3 class="panel-title">Cartões</h3>
	</div>			
	<div class="panel-body lista-clientes">
		<?php if ($cartoes){ ?>
		<table class="table table-hover table-condensed">
			<tr>
				<th>Nome</th>
				<th>Dia de vencimento</th>
				<th>Ações</th>
			</tr>
			<?php foreach ($cartoes as $r){ ?>
			<tr>
				<td><?=$r['Cartao']['descricao']?></td>
				<td><?=$r['Cartao']['dia_vencimento']?></td>
				<td>
					<a href="/cartoes/excluir/<?=$r['Cartao']['id']?>" class="btn-excluir-cartao" title="Excluir"><span class="glyphicon glyphicon-remove"></span></a>
				</td>
			</tr>
			<?php } ?>
		</table>
		<?php } ?>			
	</div>
</div>