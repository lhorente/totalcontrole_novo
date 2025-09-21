<div class="modal-dialog modal-lg">
	<?=$this->Form->create('Transacao',array('id'=>'TransacaoEditarForm','url' => 'salvar','inputDefaults'=>array('class'=>'form-control','placeholder'=>'','autocomplete'=>'off','label'=>false,'div'=>false,'errorMessage' => false,'required'=>false)));?>
	<?=$this->Form->input('id',array('type'=>'hidden','label'=>false,'div'=>false));?>
	<?=$this->Form->input('tipo',array('type'=>'hidden','label'=>false,'div'=>false));?>
	<div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h4 class="modal-title" id="myModalLabel"><?=ucfirst($transacao['Transacao']['tipo'])?></h4>
		</div>
		<div class="modal-body">
			<div class="panel-body">
				<div class="row">
					<?=$this->Form->input('descricao',array('label'=>"Descrição",'div'=>'form-group col-md-12'));?>
				</div>
				<div class="row">
						<?=$this->Form->input('id_categoria',array('options'=>$categorias,'empty'=>'Selecione...','label'=>'Categoria','div'=>'form-group col-md-4'));?>
						<?=$this->Form->input('id_cliente',array('options'=>$clientes,'empty'=>'Selecione...','label'=>'Contato','div'=>'form-group col-md-4'));?>
						<?=$this->Form->input('id_cartao',array('options'=>$cartoes,'empty'=>'Selecione...','label'=>'Cartão','div'=>'form-group col-md-4'));?>
				</div>
				<div class="row">
						<?=$this->Form->input('id_caixa',array('options'=>$list_caixas,'label'=>'Carteira','div'=>'form-group col-md-4'));?>
						<?=$this->Form->input('id_servico',array('options'=>$servicos,'empty'=>'Selecione...','label'=>'Serviço','div'=>'form-group col-md-4'));?>
				</div>
				<div class="rows-parcelas">
					<div class="row row-parcela">
							<?php
							echo $this->Form->input('data',array('id'=>'TransacaoEditarData','type'=>'text','class'=>'form-control datepicker','label'=>'Data','div'=>'form-group col-md-3'));
							echo $this->Form->input('data_pagamento',array('id'=>'TransacaoEditarDataPagamento','type'=>'text','class'=>'form-control datepicker','label'=>'Pagamento','div'=>'form-group col-md-3'));

							if ($transacao['Transacao']['tipo'] == 'emprestimo'){
								echo $this->Form->input('data_recebimento',array('id'=>'TransacaoEditarDataRecebimento','type'=>'text','class'=>'form-control datepicker','label'=>'Recebimento','div'=>'form-group col-md-3'));
							}

							echo $this->Form->input('valor',array('type'=>'text','class'=>'form-control input-valor', 'label'=>'Valor','div'=>'form-group col-md-3', 'value'=>number_format($transacao['Transacao']['valor'],2,",",".")));
							?>
					</div>
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
