<?php if ($transacao){ ?>
	<td><?=$this->Form->input('Transacao.data',array('id'=>'TransacaoEditarData','type'=>'text','class'=>'form-control datepicker','label'=>false,'div'=>false));?></td>
	<td><?=$this->Form->input('Transacao.data_pagamento',array('id'=>'TransacaoEditarDataPagamento','type'=>'text','class'=>'form-control datepicker','label'=>false,'div'=>false));?></td>
	<td><?=$this->Form->input('Transacao.descricao',array('type'=>'text','class'=>'form-control','label'=>false,'div'=>false));?></td>
	<td><?=$this->Form->input('Transacao.valor',array('type'=>'text','class'=>'form-control input-valor','label'=>false,'div'=>false));?></td>
	<td><?=$this->Form->input('Transacao.id_categoria',array('options'=>$categorias,'class'=>'form-control','label'=>false,'div'=>false));?></td>
	<td><?=$this->Form->input('Transacao.id_cliente',array('options'=>$clientes,'class'=>'form-control','label'=>false,'div'=>false,'empty'=>'Selecione...'));?></td>
	<td><?=$this->Form->input('Transacao.id_servico',array('options'=>$servicos,'class'=>'form-control','label'=>false,'div'=>false,'empty'=>'Selecione...'));?></td>
	<td>
		<?=$this->Form->input('Transacao.id',array('type'=>'hidden','class'=>'form-control','label'=>false,'div'=>false));?>
		<?=$this->Form->input('Transacao.tipo',array('type'=>'hidden','class'=>'form-control','label'=>false,'div'=>false));?>
		<button type="submit" class="btn btn-default btn-xs" title="Salvar"><span class="glyphicon glyphicon-ok"></span></button>
		<button type="button" class="btn btn-default btn-xs btn-cancelar-edicao-transacao" title="Cancelar edição"><span class="glyphicon glyphicon-remove"></span></button>
	</td>
<?php } ?>