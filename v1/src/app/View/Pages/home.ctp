<div class="alert alert-warning">Uma nova versão beta dessa tela está disponível. <a href="<?php echo $NEW_APP_URL ?>/dashboard" target="_blank">Acesse aqui</a>.</div>

<div class="row">
	<div class="col-md-6 col-sd-12">
		<div class="panel panel-default">
			<div class="panel-heading">Próximas contas a pagar</div>
			<div class="panel-body">
				<table class="table">
					<?php foreach ($contas_a_pagar as $t){ ?>
					<tr class="text-danger">
						<td><?=$t['Transacao']['data']?></td>
						<td>
							<?php if ($t['Cartao']['id']){ ?>
								<span class="fa fa-cc-visa tooltip-title" title="<?=$t['Cartao']['descricao']?>"></span>
							<?php } ?>
							<?php if ($t['Cliente']['id']){ ?>
								<span class="label label-default tooltip-title" title="<?=$t['Cliente']['nome']?>"><?=strtoupper(substr($t['Cliente']['nome'],0,1))?></span>
							<?php } ?>
							<?php if ($t['Servico']['id']){ ?>
								<span class="fa fa-puzzle-piece tooltip-title" title="<?=$t['Servico']['descricao']?>"></span>
							<?php } ?>
							<?=$t['Transacao']['descricao']?>
						</td>
						<td>R$ <?=number_format($t['Transacao']['valor'],2,",",".")?></td>
						<td>
							<a href="/transacoes/pagar/<?=$t['Transacao']['id']?>?data=<?=date('d/m/Y')?>" class="btn-pagar-transacao fa fa-check tooltip-title" title="Confirmar pagamento em <?=date("d/m/Y")?>"></a>
							<a href="/transacoes/salvar_modal/<?=$t['Transacao']['id']?>" class="btn-salvar-transacao" title="Editar"><span class="glyphicon glyphicon-edit"></span></a>
							<a href="/transacoes/excluir/<?=$t['Transacao']['id']?>" class="btn-excluir-transacao" title="Excluir"><span class="glyphicon glyphicon-remove"></span></a>
						</td>
					</tr>
					<?php } ?>
				</table>
			</div>
		</div>
	</div>

	<div class="col-md-6 col-sd-12">
		<div class="panel panel-default">
			<div class="panel-heading">Próximas contas a receber</div>
			<div class="panel-body">
				<table class="table">
					<?php foreach ($contas_a_receber as $t){ ?>
					<tr class="text-primary">
						<td><?=$t['Transacao']['data']?></td>
						<td>
							<?php if ($t['Cartao']['id']){ ?>
								<span class="fa fa-cc-visa tooltip-title" title="<?=$t['Cartao']['descricao']?>"></span>
							<?php } ?>
							<?php if ($t['Cliente']['id']){ ?>
								<span class="label label-default tooltip-title" title="<?=$t['Cliente']['nome']?>"><?=strtoupper(substr($t['Cliente']['nome'],0,1))?></span>
							<?php } ?>
							<?php if ($t['Servico']['id']){ ?>
								<span class="fa fa-puzzle-piece tooltip-title" title="<?=$t['Servico']['descricao']?>"></span>
							<?php } ?>
							<?=$t['Transacao']['descricao']?>
						</td>
						<td>R$ <?=number_format($t['Transacao']['valor'],2,",",".")?></td>
						<td>
							<a href="/transacoes/pagar/<?=$t['Transacao']['id']?>?data=<?=date('d/m/Y')?>" class="btn-pagar-transacao fa fa-check tooltip-title" title="Confirmar pagamento em <?=date("d/m/Y")?>"></a>
							<a href="/transacoes/salvar_modal/<?=$t['Transacao']['id']?>" class="btn-salvar-transacao" title="Editar"><span class="glyphicon glyphicon-edit"></span></a>
							<a href="/transacoes/excluir/<?=$t['Transacao']['id']?>" class="btn-excluir-transacao" title="Excluir"><span class="glyphicon glyphicon-remove"></span></a>
						</td>
					</tr>
					<?php } ?>
				</table>
			</div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-md-6 col-sd-12">
		<div class="panel panel-default">
			<div class="panel-heading">Despesas atrasadas</div>
			<div class="panel-body">
				<table class="table">
					<?php foreach ($despesas_atrasadas as $t){ ?>
					<tr class="text-danger">
						<td><?=$t['Transacao']['data']?></td>
						<td>
							<?php if ($t['Cartao']['id']){ ?>
								<span class="fa fa-cc-visa tooltip-title" title="<?=$t['Cartao']['descricao']?>"></span>
							<?php } ?>
							<?php if ($t['Cliente']['id']){ ?>
								<span class="label label-default tooltip-title" title="<?=$t['Cliente']['nome']?>"><?=strtoupper(substr($t['Cliente']['nome'],0,1))?></span>
							<?php } ?>
							<?php if ($t['Servico']['id']){ ?>
								<span class="fa fa-puzzle-piece tooltip-title" title="<?=$t['Servico']['descricao']?>"></span>
							<?php } ?>
							<?=$t['Transacao']['descricao']?>
						</td>
						<td>R$ <?=number_format($t['Transacao']['valor'],2,",",".")?></td>
						<td>
							<a href="/transacoes/pagar/<?=$t['Transacao']['id']?>?data=<?=date('d/m/Y')?>" class="btn-pagar-transacao fa fa-check tooltip-title" title="Confirmar pagamento em <?=date("d/m/Y")?>"></a>
							<a href="/transacoes/salvar_modal/<?=$t['Transacao']['id']?>" class="btn-salvar-transacao" title="Editar"><span class="glyphicon glyphicon-edit"></span></a>
							<a href="/transacoes/excluir/<?=$t['Transacao']['id']?>" class="btn-excluir-transacao" title="Excluir"><span class="glyphicon glyphicon-remove"></span></a>
						</td>
					</tr>
					<?php } ?>
				</table>
			</div>
		</div>
	</div>
	<div class="col-md-6 col-sd-12">
		<div class="panel panel-default">
			<div class="panel-heading">Lucros atrasados</div>
			<div class="panel-body">
				<table class="table">
					<?php foreach ($lucros_atrasados as $t){ ?>
					<tr class="text-primary">
						<td><?=$t['Transacao']['data']?></td>
						<td>
							<?php if ($t['Cartao']['id']){ ?>
								<span class="fa fa-cc-visa tooltip-title" title="<?=$t['Cartao']['descricao']?>"></span>
							<?php } ?>
							<?php if ($t['Cliente']['id']){ ?>
								<span class="label label-default tooltip-title" title="<?=$t['Cliente']['nome']?>"><?=strtoupper(substr($t['Cliente']['nome'],0,1))?></span>
							<?php } ?>
							<?php if ($t['Servico']['id']){ ?>
								<span class="fa fa-puzzle-piece tooltip-title" title="<?=$t['Servico']['descricao']?>"></span>
							<?php } ?>
							<?=$t['Transacao']['descricao']?>
						</td>
						<td>R$ <?=number_format($t['Transacao']['valor'],2,",",".")?></td>
						<td>
							<a href="/transacoes/pagar/<?=$t['Transacao']['id']?>?data=<?=date('d/m/Y')?>" class="btn-pagar-transacao fa fa-check tooltip-title" title="Confirmar pagamento em <?=date("d/m/Y")?>"></a>
							<a href="/transacoes/salvar_modal/<?=$t['Transacao']['id']?>" class="btn-salvar-transacao" title="Editar"><span class="glyphicon glyphicon-edit"></span></a>
							<a href="/transacoes/excluir/<?=$t['Transacao']['id']?>" class="btn-excluir-transacao" title="Excluir"><span class="glyphicon glyphicon-remove"></span></a>
						</td>
					</tr>
					<?php } ?>
				</table>
			</div>
		</div>
	</div>
</div>
