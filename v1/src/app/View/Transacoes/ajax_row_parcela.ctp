<?php for ($i=0;$i<$qtd;$i++){ ?>
<div class="row row-parcela">
	<div class="form-group col-md-2">
		<label for="">Data*</label>
		<input name="data[parcelas][<?=$i?>][data]" class="form-control datepicker" placeholder="" type="text" id="Parcela<?=$i?>TransacaoData" value="<?=isset($parcelas[$i]) ? $parcelas[$i]['data'] : ''?>">								
	</div>
	<div class="form-group col-md-2">
		<label for="">Pagamento</label>
		<input name="data[parcelas][<?=$i?>][data_pagamento]" class="form-control input-valor campo-formatado" placeholder="" type="text" id="Parcela<?=$i?>TransacaoDataPagamento">								
	</div>			
	<div class="form-group col-md-3">
		<label for="">Valor*</label>
		<input name="data[parcelas][<?=$i?>][valor]" class="form-control" placeholder="" type="text" id="Parcela<?=$i?>TransacaoValor" value="<?=isset($parcelas[$i]) ? $parcelas[$i]['valor'] : ''?>">								
	</div>								
	<div class="form-group col-md-5">
		<label for="">Descrição*</label>
		<input name="data[parcelas][<?=$i?>][descricao]" class="form-control" placeholder="" type="text" id="Parcela<?=$i?>TransacaoDescricao" value="<?=isset($parcelas[$i]) ? $parcelas[$i]['descricao'] : ''?>">								
	</div>					
</div>
<?php } ?>