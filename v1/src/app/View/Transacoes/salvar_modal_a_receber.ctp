  <div class="modal-dialog modal-lg">
	<?=$this->Form->create('Transacao',array('id'=>'TransacaoEditarForm','url' => 'salvar'));?>
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="myModalLabel">Adicionar conta a pagar</h4>
      </div>
      <div class="modal-body">
			<div class="panel-body">
				<?php if (isset($transacao)){ ?>
				<?=$this->Form->input('id',array('type'=>'hidden','label'=>false,'div'=>false));?>
				<?php } ?>
				<?=$this->Form->input('tipo',array('type'=>'hidden','value'=>'lucro','class'=>'form-control','label'=>false,'div'=>false));?>
				<div class="row">
					<div class="form-group col-md-2">
						<label>Data</label>
						<?=$this->Form->input('data',array('id'=>'TransacaoEditarData','type'=>'text','class'=>'form-control datepicker','label'=>false,'div'=>false));?>
					</div>					
					<div class="form-group col-md-2">
						<label>Valor</label>
						<?=$this->Form->input('valor',array('type'=>'text','class'=>'form-control input-valor','label'=>false,'div'=>false));?>
					</div>
					<div class="form-group col-md-2">
						<label>Descrição</label>
						<?=$this->Form->input('descricao',array('class'=>'form-control','label'=>false,'div'=>false));?>
					</div>
					<div class="form-group col-md-2">
						<label>Categoria</label>
						<?=$this->Form->input('id_categoria',array('options'=>$categorias,'empty'=>'Selecione...','class'=>'form-control','label'=>false,'div'=>false));?>
					</div>
					<div class="form-group col-md-2">
						<label>Repetir</label>
						<?=$this->Form->input('repetir',array('options'=>array(0=>'Não repetir',1,2,3,4,5,6,7,8,9,10,11),'class'=>'form-control','label'=>false,'div'=>false));?>
					</div>
					<div class="form-group col-md-2">
						<label>Contato</label>
						<?=$this->Form->input('id_cliente',array('options'=>$clientes,'empty'=>'Selecione...','class'=>'form-control','label'=>false,'div'=>false));?>
					</div>
				</div>
			</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
        <button type="submit" class="btn btn-primary btn-salvar-tranasacao">Salvar</button>
      </div>
    </div>
	<?=$this->Form->end();?>
  </div>