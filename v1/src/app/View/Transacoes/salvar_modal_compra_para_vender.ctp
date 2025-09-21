  <div class="modal-dialog modal-lg">
	<?=$this->Form->create('Transacao',array('id'=>'TransacaoEditarForm','url' => 'salvar'));?>
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="myModalLabel">Salvar transação</h4>
      </div>
      <div class="modal-body">
			<div class="panel-body">
				<?php if (isset($transacao)){ ?>
				<?=$this->Form->input('id',array('type'=>'hidden','label'=>false,'div'=>false));?>
				<?php } ?>
				<div class="row">
					<div class="form-group col-md-2">
						<label>Data</label>
						<?=$this->Form->input('data',array('id'=>'TransacaoEditarData','type'=>'text','class'=>'form-control datepicker','label'=>false,'div'=>false));?>
					</div>
					<div class="form-group col-md-2">
						<label>Pago em</label>
						<?=$this->Form->input('data_pagamento',array('id'=>'TransacaoEditarDataPagamento','type'=>'text','class'=>'form-control datepicker','label'=>false,'div'=>false));?>
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
						<label>Tipo</label>
						<?=$this->Form->input('tipo',array('options'=>array('despesa'=>'Despesa','lucro'=>'Lucro'),'class'=>'form-control','label'=>false,'div'=>false));?>
					</div>
					<div class="form-group col-md-2">
						<label>Repetir</label>
						<?=$this->Form->input('repetir',array('options'=>array(0=>'Não repetir',1,2,3,4,5,6,7,8,9,10,11),'class'=>'form-control','label'=>false,'div'=>false));?>
					</div>
					<div class="form-group col-md-2">
						<label>Cliente</label>
						<?=$this->Form->input('id_cliente',array('options'=>$clientes,'empty'=>'Selecione...','class'=>'form-control','label'=>false,'div'=>false));?>
					</div>
					<div class="form-group col-md-2">
						<label>Serviço</label>
						<?=$this->Form->input('id_servico',array('options'=>$servicos,'empty'=>'Selecione...','class'=>'form-control','label'=>false,'div'=>false));?>
					</div>
					<div class="form-group col-md-2">
						<label>Cartão</label>
						<?=$this->Form->input('id_cartao',array('options'=>$cartoes,'empty'=>'Selecione...','class'=>'form-control','label'=>false,'div'=>false));?>
					</div>
				</div>
				<div class="produtos">
					<?php if ($produtos){ ?>
					<?php foreach ($produtos as $num=>$p){ ?>
					<div class="row row-produto">
						<div class="form-group col-md-4 group-produto">
							<label>Produto</label>
							<div class="div-selected-produto hide">
								<div class="input-group">
									<!-- <?=$this->Form->input('produto', array('class' => 'input-selected-produto form-control', 'label' => false, 'div' => false));?> -->
									<input name="data[TransacoesProduto][<?=$num?>][produto]" class="input-selected-produto form-control" type="text" id="produto">
									<span class="input-group-btn">
										<button class="btn btn-default btn-remove-produto" type="button"><span class="glyphicon glyphicon-remove"></span></button>
									</span>
								</div>
							</div>
							<!-- <?=$this->Form->input('produto',array('type'=>'text','class'=>'form-control autocomplete-produto','label'=>false,'div'=>false));?> -->
							<input name="data[TransacoesProduto][<?=$num?>][produto]" class="autocomplete-produto form-control" type="text" id="produto" value="<?=$p['Produto']['nome']?>">
							<input name="data[TransacoesProduto][<?=$num?>][id_produto]" class="form-control input-id-produto" type="hidden" id="TransacoesIdProduto" value="<?=$p['Produto']['id']?>">
						</div>
						<div class="form-group col-md-2">
							<label>Quantidade</label>
							<input name="data[TransacoesProduto][<?=$num?>][quantidade]" class="form-control produtoQuantidade" type="text" id="TransacoesProdutoQuantidade" value="<?=$p['TransacoesProduto']['quantidade']?>">
						</div>					
						<div class="form-group col-md-2">
							<label>Valor unitário</label>
							<input name="data[TransacoesProduto][<?=$num?>][valor_unitario]" class="form-control produtoValorUnitario input-valor" type="text" id="TransacoesProdutoValorUnitario" value="<?=$p['TransacoesProduto']['valor_unitario']?>">
						</div>
						<div class="form-group col-md-2">
							<label>Desconto</label>
							<input name="data[TransacoesProduto][<?=$num?>][desconto]" class="form-control produtoDesconto input-valor" type="text" id="TransacoesProdutoDesconto" value="<?=$p['TransacoesProduto']['desconto']?>">
						</div>
						<div class="form-group col-md-2">
							<label>Juros</label>
							<input name="data[TransacoesProduto][<?=$num?>][juros]" class="form-control produtoJuros input-valor" type="text" id="TransacoesJuros" value="<?=$p['TransacoesProduto']['juros']?>">
						</div>
					</div>
					<?php } ?>
					<?php } ?>
				</div>
				<div class="row">
					<div class="form-group col-md-2">
						<button type="button" class="btn btn-primary btn-add-produto">Adicionar produto</button>
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