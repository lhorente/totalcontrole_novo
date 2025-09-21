<div class="modal-dialog modal-lg">
	<?=$this->Form->create('Transacao',array('id'=>'TransacaoTransferenciaForm','class'=>'form-modal-ajax','url' => 'transferir','inputDefaults'=>array('class'=>'form-control','placeholder'=>'','label'=>false,'div'=>'form-group col-md-4','errorMessage' => false,'required'=>false)));?>
	<div class="modal-content">
		<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		<h4 class="modal-title" id="myModalLabel">Adicionar transferência</h4>
		</div>
		<div class="modal-body">
			<div class="alert hide" role="alert"></div>
			<div class="box box-primary">
				<div class="box-header">
				  <h3 class="box-title">Transferência</h3>
				</div>
				  <div class="box-body">
					<div class="row">
						<?php
							echo $this->Form->input('id_caixa_de',array('label'=>'Carteira De*','options'=>$list_caixas));
							echo $this->Form->input('id_caixa_para',array('label'=>'Carteira Para*','options'=>$list_caixas));
							echo $this->Form->input('data',array('label'=>'Data*','type'=>'text','default'=>date('d/m/Y'),'class'=>'form-control datepicker datepicker-readonly'));
							echo $this->Form->input('data_pagamento',array('label'=>'Pagamento*','type'=>'text','class'=>'form-control datepicker datepicker-readonly'));
							echo $this->Form->input('valor',array('label'=>'Valor*','type'=>'text','class'=>'form-control input-valor'));
							echo $this->Form->input('descricao',array('label'=>'Descrição*'));
						?>
					</div>
				  </div>
			</div>
		</div>
		<div class="modal-footer">
			<button type="button" class="btn btn-default btn-cancelar" data-dismiss="modal">Cancelar</button>
			<button type="submit" class="btn btn-primary btn-salvar">Salvar</button>
		</div>
	</div>
	<?=$this->Form->end()?>
</div>
