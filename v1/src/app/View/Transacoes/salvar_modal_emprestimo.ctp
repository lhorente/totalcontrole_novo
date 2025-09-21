<div class="modal-dialog modal-lg">
	<?=$this->Form->create('Transacao',array('id'=>'TransacaoEditarForm','url' => 'salvar','inputDefaults'=>array('class'=>'form-control','placeholder'=>'','label'=>false,'div'=>'form-group col-md-4','errorMessage' => false,'required'=>false)));?>
	<?=$this->Form->input('id',array('type'=>'hidden','label'=>false,'div'=>false));?>
	<?=$this->Form->input('tipo',array('type'=>'hidden','label'=>false,'div'=>false,'value'=>'emprestimo'));?>
	<div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h4 class="modal-title" id="myModalLabel">Empréstimo</h4>
		</div>
		<div class="modal-body">
			<div class="panel-body">
				<div class="row">
				<?php
				echo $this->Form->input('id_caixa',array('options'=>$list_caixas,'empty'=>'Selecione...','label'=>'Caixa'));
				echo $this->Form->input('id_cliente',array('options'=>$clientes,'empty'=>'Selecione...','label'=>'Contato'));
				echo $this->Form->input('id_cartao',array('options'=>$cartoes,'empty'=>'Selecione...','label'=>'Cartão'));
				echo $this->Form->input('data',array('label'=>'Data*','type'=>'text','default'=>date('d/m/Y'),'class'=>'form-control datepicker datepicker-readonly'));
				echo $this->Form->input('data_pagamento',array('label'=>'Pagamento','type'=>'text','class'=>'form-control datepicker datepicker-readonly'));
				echo $this->Form->input('data_recebimento',array('label'=>'Recebimento','type'=>'text','class'=>'form-control datepicker datepicker-readonly'));
				echo $this->Form->input('valor',array('label'=>'Valor*','type'=>'text','class'=>'form-control input-valor','value'=>number_format($transacao['Transacao']['valor'],2,",",".")));
				echo $this->Form->input('descricao',array('label'=>'Descrição*'));
				?>
				</div>
			</div>
		</div>
		<div class="modal-footer">
			<button data-id_transacao="<?php echo $transacao['Transacao']['id'] ?>" class="btn-excluir-transacao btn btn-default" data-dismiss="modal">
				<i class="fa fa-remove"></i> Excluir
			</button>
			<button type="submit" class="btn btn-primary btn-salvar-tranasacao">Salvar</button>
		</div>
	</div>
	<?=$this->Form->end();?>
</div>
