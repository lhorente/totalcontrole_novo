<div class="modal-dialog modal-lg">
	<?=$this->Form->create('Transacao',array('id'=>'TransacaoSalvarForm','class'=>'form-modal-ajax','url' => 'inserir','inputDefaults'=>array('class'=>'form-control','placeholder'=>'','label'=>false,'div'=>false,'errorMessage' => false,'required'=>false)));?>
	<?=$this->Form->input('tipo',array('type'=>'hidden','value'=>$tipo));?>
	<div class="modal-content">
		<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		<h4 class="modal-title" id="myModalLabel">Adicionar <?=$tipo?></h4>
		</div>
		<div class="modal-body">
			<div class="alert hide" role="alert">Teste</div>
			<div class="box box-primary">
				<div class="box-header">
				  <h3 class="box-title"><?=ucfirst($tipo)?></h3>
				</div>
				  <div class="box-body">
					<div class="row">
						<div class="form-group col-md-4">
							<label for="">Categoria*</label>
							<?=$this->Form->input('id_categoria',array('options'=>$categorias,'empty'=>'Selecione...'));?>
						</div>
						<div class="form-group col-md-3">
							<label for="">Contato</label>
							<?=$this->Form->input('id_cliente',array('options'=>$clientes,'empty'=>'Selecione...'));?>
						</div>
						<div class="form-group col-md-3">
							<label for="">Cartão</label>
							<?=$this->Form->input('id_cartao',array('options'=>$cartoes,'empty'=>'Selecione...'));?>
						</div>
						<div class="form-group col-md-2">
							<label for="">Parcelamento</label>
							<?=$this->Form->input('parcelas',array('options'=>array('1'=>'Nenhum','2'=>'2 meses','3'=>'3 meses','4'=>'4 meses','5'=>'5 meses','6'=>'6 meses',
																					'7'=>'7 meses','8'=>'8 meses','9'=>'9 meses','10'=>'10 meses','11'=>'11 meses','12'=>'12 meses')));?>
						</div>
					</div>
					<div class="row">
						<div class="form-group col-md-4">
							<label for="">Serviço</label>
							<?=$this->Form->input('id_servico',array('options'=>$servicos,'empty'=>'Selecione...'));?>
						</div>
						<div class="form-group col-md-4">
							<label for="">Carteira*</label>
							<?=$this->Form->input('id_caixa',array('options'=>$caixas));?>
						</div>
					</div>
					<div class="rows-parcelas">
						<div class="row row-parcela">
							<div class="form-group col-md-2">
								<label for="">Data*</label>
								<input name="data[parcelas][0][data]" class="form-control datepicker datepicker-readonly" placeholder="" type="text" id="Parcela0TransacaoData" autocomplete="off" readonly>
							</div>
							<div class="form-group col-md-2">
								<label for="">Pagamento</label>
								<input name="data[parcelas][0][data_pagamento]" class="form-control datepicker datepicker-readonly" placeholder="" type="text" id="Parcela0TransacaoDataPagamento" autocomplete="off" readonly>
							</div>
							<div class="form-group col-md-3">
								<label for="">Valor*</label>
								<input name="data[parcelas][0][valor]" class="form-control input-valor" placeholder="" type="tel" id="Parcela0TransacaoValor" autocomplete="off">
							</div>
							<div class="form-group col-md-5">
								<label for="">Descrição*</label>
								<input name="data[parcelas][0][descricao]" class="form-control" placeholder="" type="text" id="Parcela0TransacaoDescricao">
							</div>
						</div>
					  </div>
				  </div>
			</div>
			<div class="box box-primary hide">
				<div class="box-header">
				  <h3 class="box-title">Produtos</h3>
				</div>
				<div class="box-body">
					<div class="rows-produtos">
						<div class="row row-produto">
							<div class="form-group col-md-4">
								<label for="">Produto*</label>
								<div class="div-selected-produto hide">
									<div class="input-group">
										<input name="data[produtos][0][produto]" class="input-selected-produto form-control" type="text" id="Produto0Nome">
										<span class="input-group-btn">
											<button class="btn btn-default btn-remove-produto" type="button"><span class="glyphicon glyphicon-remove"></span></button>
										</span>
									</div>
								</div>
								<input name="data[produtos][0][produto]" class="autocomplete-produto form-control" type="text" id="Produto0NomeAutocomplete">
								<input name="data[produtos][0][id_produto]" class="form-control input-id-produto" type="hidden" id="Produto0Id">
							</div>
							<div class="form-group col-md-2">
								<label for="">Quantidade*</label>
								<input name="data[produtos][0][quantidade]" class="form-control" placeholder="" type="text" id="Produto0Quantidade">
							</div>
							<div class="form-group col-md-2">
								<label for="">Valor*</label>
								<input name="data[produtos][0][valor]" class="form-control" placeholder="" type="text" id="Produto0ValorUnitario">
							</div>
							<div class="form-group col-md-2">
								<label for="">Desconto</label>
								<input name="data[produtos][0][desconto]" class="form-control" placeholder="" type="text" id="Produto0Desconto">
							</div>
							<div class="form-group col-md-2">
								<label for="">Juros</label>
								<input name="data[produtos][0][juros]" class="form-control" placeholder="" type="text" id="Produto0Juros">
							</div>
						</div>
					</div>
				</div><!-- /.box-body -->
			</div>
			<div class="box box-primary hide">
				<div class="box-header">
				  <h3 class="box-title">Veículo</h3>
				</div>
				  <div class="box-body">
					<div class="row">
						<div class="form-group col-md-4">
						  <label for="exampleInputEmail1">Veículo</label>
							<select name="data[Transacao][id_veiculo]" class="form-control input-valor" placeholder="" id="TransacaoIdCategoria">
								<option value="">Selecione...</option>
								<option value="6">CB 300R</option>
								<option value="29">Carro</option>
							</select>
						</div>
					</div>
				  </div><!-- /.box-body -->
			</div>
		</div>
		<div class="modal-footer">
			<button type="button" class="btn btn-default btn-cancelar" data-dismiss="modal">Cancelar</button>
			<button type="submit" class="btn btn-primary btn-salvar">Salvar</button>
		</div>
	</div>
	<?=$this->Form->end()?>
</div>
