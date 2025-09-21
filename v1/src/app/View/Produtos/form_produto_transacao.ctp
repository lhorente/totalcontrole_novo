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
		<input name="data[TransacoesProduto][<?=$num?>][produto]" class="autocomplete-produto form-control" type="text" id="produto">
		<input name="data[TransacoesProduto][<?=$num?>][id_produto]" class="form-control input-id-produto" type="hidden" id="TransacoesIdProduto">
	</div>
	<div class="form-group col-md-2">
		<label>Quantidade</label>
		<input name="data[TransacoesProduto][<?=$num?>][quantidade]" class="form-control produtoQuantidade" type="text" id="TransacoesProdutoQuantidade">
	</div>					
	<div class="form-group col-md-2">
		<label>Valor unitário</label>
		<input name="data[TransacoesProduto][<?=$num?>][valor_unitario]" class="form-control produtoValorUnitario input-valor" type="text" id="TransacoesProdutoValorUnitario">
	</div>
	<div class="form-group col-md-2">
		<label>Desconto</label>
		<input name="data[TransacoesProduto][<?=$num?>][desconto]" class="form-control produtoDesconto input-valor" type="text" id="TransacoesProdutoDesconto">
	</div>
	<div class="form-group col-md-2">
		<label>Juros</label>
		<input name="data[TransacoesProduto][<?=$num?>][juros]" class="form-control produtoJuros input-valor" type="text" id="TransacoesJuros">
	</div>
</div>