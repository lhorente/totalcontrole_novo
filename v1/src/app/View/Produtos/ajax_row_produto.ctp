<div class="row row-produto">
	<div class="form-group col-md-4">
		<label for="">Produto*</label>
		
		<div class="div-selected-produto hide">
			<div class="input-group">
				<input name="data[produtos][<?=$num?>][produto]" class="input-selected-produto form-control" type="text" id="Produto<?=$num?>Nome">
				<span class="input-group-btn">
					<button class="btn btn-default btn-remove-produto" type="button"><span class="glyphicon glyphicon-remove"></span></button>
				</span>
			</div>
		</div>
		<input name="data[produtos][<?=$num?>][produto]" class="autocomplete-produto form-control" type="text" id="Produto<?=$num?>NomeAutocomplete">
		<input name="data[produtos][<?=$num?>][id_produto]" class="form-control input-id-produto" type="hidden" id="Produto<?=$num?>Id">
		
		<!--<input name="data[produtos][<?=$num?>][descricao]" class="form-control input-nome-produto" placeholder="" type="text" id="Produto<?=$num?>Descricao">-->
	</div>
	<div class="form-group col-md-2">
		<label for="">Quantidade*</label>
		<input name="data[produtos][<?=$num?>][quantidade]" class="form-control produtoQuantidade" placeholder="" type="text" id="Produto<?=$num?>Quantidade">
	</div>=
	<div class="form-group col-md-2">
		<label for="">Valor*</label>
		<input name="data[produtos][<?=$num?>][valor_unitario]" class="form-control produtoValorUnitario input-valor" placeholder="" type="text" id="Produto<?=$num?>ValorUnitario">
	</div>								
	<div class="form-group col-md-2">
		<label for="">Desconto</label>
		<input name="data[produtos][<?=$num?>][desconto]" class="form-control produtoDesconto input-valor" placeholder="" type="text" id="Produto<?=$num?>Desconto">
	</div>
	<div class="form-group col-md-2">
		<label for="">Juros</label>
		<input name="data[produtos][<?=$num?>][juros]" class="form-control produtoJuros input-valor" placeholder="" type="text" id="Produto<?=$num?>Juros">
	</div>
</div>