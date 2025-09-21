<div class="alert alert-warning">Uma nova versão beta dessa tela está disponível. <a href="<?php echo $NEW_APP_URL ?>/credit_cards" target="_blank">Acesse aqui</a>.</div>

<div class="row">
	<div class="usuario div-tipo-usuario col-md-12">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">Inserir cartão</h3>
			</div>
			<div class="panel-body">
				<?=$this->Form->create('Cartao',array('action' => 'salvar'));?>
					<div class="row">
						<div class="form-group col-md-2">
							<label>Descrição</label>
							<?=$this->Form->input('descricao',array('type'=>'text','class'=>'form-control','label'=>false,'div'=>false));?>
						</div>
						<div class="form-group col-md-2">
							<label>Dia de vencimento</label>
							<?=$this->Form->input('dia_vencimento',array('type'=>'text','class'=>'form-control','label'=>false,'div'=>false));?>
						</div>
						<div class="form-group col-md-1">
							<label>&nbsp;</label>
							<button type="submit" class="btn btn-default form-control">Salvar</button>
						</div>
					</div>
				<?=$this->Form->end();?>
			</div>
		</div>
	</div>
	<div class="col-md-12 lista-cartoes"></div>
</div>
