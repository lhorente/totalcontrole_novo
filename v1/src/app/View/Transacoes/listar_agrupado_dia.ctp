<div class="panel panel-default">
	<div class="panel-heading">
		<h3 class="panel-title text-center">
			<?php
				if ($q){
					$qs = "?".http_build_query($q);
				} else {
					$qs = "";
				}
			?>
			<?php foreach ($arr_meses as $arr_mes){ ?>
				<?php if ($arr_mes['selected']){ ?>
					<?=$arr_mes['mes_nome']?> / <?=$arr_mes['ano']?>
				<?php } else { ?>
					<a class="link-mes" href='/transacoes/<?=$arr_mes['ano']?>/<?=$arr_mes['mes']?><?=$qs?>'><small><?=$arr_mes['mes_nome']?> / <?=$arr_mes['ano']?></small></a>
				<?php } ?>
			<?php } ?>
		</h3>
	</div>	
	<div class="panel-body lista-transacoes">
		<ul class="list-inline">
			<li>
				<b>Saldo:</b> 
				<span class="<?=($saldo>0?"text-primary":"text-danger")?>">R$ <?=number_format($saldo,2,',','.')?></span>
			</li>
			<li>
				<b>Saldo seguro:</b> 
				<span class="<?=($saldo_seguro>0?"text-primary":"text-danger")?>">R$ <?=number_format($saldo_seguro,2,',','.')?> </span>
			</li>
			<li>
				<b>Total a pagar:</b> 
				<span class="text-danger">R$ <?=number_format($transacoes['total_pagar'],2,',','.')?> </span>
			</li>
			<li>
				<b>Total pago:</b> 
				<span class="text-danger">R$ <?=number_format($transacoes['total_pago'],2,',','.')?> </span>
			</li>			
			<li>
				<b>Total a receber:</b> 
				<span class="text-primary">R$ <?=number_format($transacoes['total_receber'],2,',','.')?> </span>
			</li>
			<li>
				<b>Total recebido:</b> 
				<span class="text-primary">R$ <?=number_format($transacoes['total_recebido'],2,',','.')?> </span>
			</li>			
		</ul>
		<?=$this->Form->create('Transacao',array('action'=>'index','type'=>'post','id'=>'TransacaoFiltrarForm'));?>
			<div class="row">
				<div class="form-group col-md-2">
					<label for="">Categoria</label>
					<?=$this->Form->input('id_categoria',array('name'=>'id_categoria','empty'=>'Todas','options'=>$categorias,'class'=>'form-control','label'=>false,'div'=>false,'errorMessage' => false,'required'=>false));?>
				</div>
				<div class="form-group col-md-1">
					<label for="">&nbsp;</label>
					<button type="submit" class="btn btn-default">Filtrar</button>
				</div>					
			</div>
		<?=$this->Form->end()?>
		<a href="/transacoes/<?=$ano?>/<?=$mes?>">Remover agrupamento</a>
		<?php if ($transacoes && $transacoes['transacoes']){ ?>
			<?=$this->Form->create('Transacao',array('url' => 'index','id'=>'TransacoesMultiEditar'));?>
			<div class="table-responsive">
				<table class="table table-hover table-condensed table-striped table-bordered">
					<tr>
						<th class="text-center">Descrição</th>
						<th class="text-center">Valor</th>
						<th class="text-center">Categoria</th>
						<th class="text-center">Ações</th>
					</tr>
					<?php
						$id_dia_anterior = '';
					?>
					<?php foreach ($transacoes['transacoes'] as $dia=>$d){?>
						<?php $id_dia = str_replace('/','',$dia); ?>
						<tr class="item" data-item="d<?=$id_dia?>" data-parent="">
							<td colspan="">
								<a href="" class="bt-toggle-expand"><i class="fa fa-plus"></i></a>
								<?=$dia?>						
							</td>
							<td class="text-center">
								<span class="text-danger">R$ <?=number_format($d['total_despesa'],2,",",".")?></span>
								&nbsp;&nbsp;&nbsp;&nbsp;
								<span class="text-primary">R$ <?=number_format($d['total_lucro'],2,",",".")?></span>
							</td>
							<td class="text-center"></td>
							<td class="" style="width:80px;">
							</td>
						</tr>					
						<?php if ($d['transacoes']){ ?>
							<?php foreach ($d['transacoes'] as $r){?>
							<tr class="<?=($r['Transacao']['tipo'] == 'despesa'?'text-danger':'text-primary')?> hide item" data-parent="d<?=$id_dia?>">
								<td colspan="">
									&nbsp;&nbsp;&nbsp;&nbsp;
									<input type="checkbox" class="chk-id-tranasacao" name="transacoes_ids[]" value="<?=$r['Transacao']['id']?>" />
									<?=$r['Transacao']['descricao']?>
									<?php if ($r['Cartao']){ ?>
										<span class="fa fa-cc-visa tooltip-title" title="<?=$r['Cartao']['descricao']?>"></span>
									<?php } ?>
									<?php if ($r['Cliente']['id']){ ?>
										<span class="label label-default tooltip-title" title="<?=$r['Cliente']['nome']?>"><?=strtoupper(substr($r['Cliente']['nome'],0,1))?></span>
									<?php } ?>
									<?php if ($r['Servico']['id']){ ?>
										<span class="fa fa-puzzle-piece tooltip-title" title="<?=$r['Servico']['descricao']?>"></span>
									<?php } ?>						
									<?php if ($r['Produtos']){ ?>
										<?php foreach ($r['Produtos'] as $p){ ?>
											<span class="fa fa-shopping-cart tooltip-title" title="<?=$p['TransacoesProduto']['quantidade']?>x <?=$p['Produto']['nome']?>"></span>
										<?php } ?>
									<?php } ?>
									<?php if ($r['Transacao']['data_pagamento']){ ?>
										<span class="tooltip-title fa fa-check" title="Pago em <?=$r['Transacao']['data_pagamento']?>"></span>
									<?php } ?>							
								</td>
								<td class="text-center">R$ <?=number_format($r['Transacao']['valor'],2,",",".")?></td>
								<td class="text-center"><?=$r['Categoria']['nome']?></td>
								<td class="" style="width:80px;">
									<?php if (!$r['Transacao']['data_pagamento']){ ?>
										<a href="/transacoes/pagar/<?=$r['Transacao']['id']?>?data=<?=date('d/m/Y')?>" class="btn-pagar-transacao fa fa-check tooltip-title" title="Confirmar pagamento em <?=date("d/m/Y")?>"></a>					
									<?php } ?>
									<a href="/transacoes/salvar_modal/<?=$r['Transacao']['id']?>" class="btn-salvar-transacao" title="Editar"><span class="glyphicon glyphicon-edit"></span></a>
									<a href="/transacoes/excluir/<?=$r['Transacao']['id']?>" class="btn-excluir-transacao" title="Excluir"><span class="glyphicon glyphicon-remove"></span></a>
								</td>
							</tr>
							<?php } ?>
						<?php } ?>
						<?php if ($d['cartoes']){ ?>
							<?php foreach ($d['cartoes'] as $c){ ?>
								<tr class="item hide" data-item="c<?=$c['id_cartao']?>" data-parent="d<?=$id_dia?>">
									<td colspan="">
										&nbsp;&nbsp;&nbsp;&nbsp;
										<a href="" class="bt-toggle-expand"><i class="fa fa-plus"></i></a>
										Cartão
									</td>
									<td class="text-center">
										<span class="text-danger">R$ <?=number_format($c['total_despesa'],2,",",".")?></span>
										&nbsp;&nbsp;&nbsp;&nbsp;
										<span class="text-primary">R$ <?=number_format($c['total_lucro'],2,",",".")?></span>									
									</td>
									<td class="text-center"></td>
									<td class="" style="width:80px;">
									</td>
								</tr>
								<?php foreach ($c['transacoes']  as $r){?>
								<tr class="<?=($r['Transacao']['tipo'] == 'despesa'?'text-danger':'text-primary')?> hide item" data-parent="c<?=$c['id_cartao']?>">
									<td colspan="">
										&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
										<input type="checkbox" class="chk-id-tranasacao" name="transacoes_ids[]" value="<?=$r['Transacao']['id']?>" />
										<?php if ($r['Cartao']){ ?>
											<span class="fa fa-cc-visa tooltip-title" title="<?=$r['Cartao']['descricao']?>"></span>
										<?php } ?>
										<?php if ($r['Cliente']['id']){ ?>
											<span class="label label-default tooltip-title" title="<?=$r['Cliente']['nome']?>"><?=strtoupper(substr($r['Cliente']['nome'],0,1))?></span>
										<?php } ?>
										<?php if ($r['Servico']['id']){ ?>
											<span class="fa fa-puzzle-piece tooltip-title" title="<?=$r['Servico']['descricao']?>"></span>
										<?php } ?>						
										<?=$r['Transacao']['descricao']?>
										<?php if ($r['Produtos']){ ?>
											<?php foreach ($r['Produtos'] as $p){ ?>
												<span class="fa fa-shopping-cart tooltip-title" title="<?=$p['TransacoesProduto']['quantidade']?>x <?=$p['Produto']['nome']?>"></span>
											<?php } ?>
										<?php } ?>
										<?php if ($r['Transacao']['data_pagamento']){ ?>
											<span class="tooltip-title fa fa-check" title="Pago em <?=$r['Transacao']['data_pagamento']?>"></span>
										<?php } ?>							
									</td>
									<td class="text-center">R$ <?=number_format($r['Transacao']['valor'],2,",",".")?></td>
									<td class="text-center"><?=$r['Categoria']['nome']?></td>
									<td class="" style="width:80px;">
										<?php if (!$r['Transacao']['data_pagamento']){ ?>
											<a href="/transacoes/pagar/<?=$r['Transacao']['id']?>?data=<?=date('d/m/Y')?>" class="btn-pagar-transacao fa fa-check tooltip-title" title="Confirmar pagamento em <?=date("d/m/Y")?>"></a>					
										<?php } ?>
										<a href="/transacoes/salvar_modal/<?=$r['Transacao']['id']?>" class="btn-salvar-transacao" title="Editar"><span class="glyphicon glyphicon-edit"></span></a>
										<a href="/transacoes/excluir/<?=$r['Transacao']['id']?>" class="btn-excluir-transacao" title="Excluir"><span class="glyphicon glyphicon-remove"></span></a>
									</td>
								</tr>
								<?php } ?>
							<?php } ?>
						<?php } ?>						
					<?php } ?>
					<tr class="row-selecionados hide">
						<td colspan="2">
							Selecionados:
							<input id="pagarMultiData" style="display:none;" name="data_pagamento" />
							<a href="/transacoes/pagar_multi" class="btn-pagar-transacao-multi btn-pagar-multi fa fa-check tooltip-title" title="Confirmar pagamento"></a>
							<a href="/transacoes/excluir_multi" class="btn-excluir-transacao-multi btn-excluir-multi" title="Excluir"><span class="glyphicon glyphicon-remove"></span></a>
						</td>
						<td colspan="4">
							<span class="status-multi hide"></span>
						</td>
					</tr>
				</table>
			</div>
			<?=$this->Form->end();?>
		<?php } ?>	
	</div>
</div>