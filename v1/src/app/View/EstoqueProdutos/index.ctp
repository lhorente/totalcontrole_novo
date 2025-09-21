<div class="row">
	<div class="usuario div-tipo-usuario col-md-12">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">Inserir</h3>
			</div>			
			<div class="panel-body">
				<?=$this->Form->create('EstoqueProduto',array('url' => 'salvar'));?>
					<div class="row">
						<div class="form-group col-md-2">
							<label>Produto</label>
							<?=$this->Form->input('id_produto',array('options'=>$produtos_list,'empty'=>'Selecione...','class'=>'form-control','label'=>false,'div'=>false));?>
						</div>
						<div class="form-group col-md-2">
							<label>Valor</label>
							<?=$this->Form->input('valor_venda',array('type'=>'text','class'=>'form-control input-valor','label'=>false,'div'=>false));?>
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
	<div class="usuario div-tipo-usuario col-md-12">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">Produtos</h3>
			</div>			
			<div class="panel-body lista-produtos">
				<?php if ($produtos){ ?>
				<table class="table table-hover table-condensed">
					<tr>
						<th>Nome</th>
						<th>Quantidade</th>
						<th>Total vendas</th>
						<th>Valor total vendas</th>
						<th>Total compras</th>
						<th>Valor total compras</th>
						<th>Valor venda</th>
						<th>Ações</th>
					</tr>
					<?php foreach ($produtos as $r){ ?>
					<tr>
						<td><?=$r['Produto']['nome']?></td>
						<td><?=$r['EstoqueProduto']['quantidade_estoque']?></td>
						<td><?=$r['EstoqueProduto']['total_vendas']?></td>
						<td>R$ <?=number_format($r['EstoqueProduto']['valor_total_vendas'],2,",",".")?></td>
						<td><?=$r['EstoqueProduto']['total_compras']?></td>
						<td>R$ <?=number_format($r['EstoqueProduto']['valor_total_compras'],2,",",".")?></td>						
						<td>R$ <?=number_format($r['EstoqueProduto']['valor_venda'],2,",",".")?></td>						
						<td>
							<a href="/servicos/excluir/<?=$r['EstoqueProduto']['id']?>" class="btn-excluir-produto" title="Excluir"><span class="glyphicon glyphicon-remove"></span></a>
						</td>
					</tr>
					<?php } ?>
				</table>
				<?php } ?>			
			</div>
		</div>
	</div>		
</div>