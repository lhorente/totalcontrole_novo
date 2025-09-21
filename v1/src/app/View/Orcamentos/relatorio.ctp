<div class="row">
	<section class="col-lg-12">
		<div class="box no-border">
			<div class="box-header with-border">
				<h3 class="box-title">Filtrar</h3>
			</div>
			<div class="box-body">
				<?=$this->Form->create('Orcamento',array('id'=>'OrcamentoRelatorioForm','type'=>'GET','class'=>'','url' => 'relatorio','inputDefaults'=>array('class'=>'form-control','placeholder'=>'','label'=>false,'div'=>'form-group col-md-4','errorMessage' => false,'required'=>false)));?>
					<div class="row">
						<?php
							echo $this->Form->input('id_categoria',array('options'=>$categorias_lista,'empty'=>'Categoria','label'=>'Categoria*'));
							echo $this->Form->input('id_caixa',array('options'=>$caixas,'label'=>'Caixa','div'=>'form-group col-md-2','value'=>$id_caixa,'empty'=>'Caixa'));
							echo $this->Form->input('ano',array('type'=>'text','value'=>$ano,'div'=>'form-group col-md-1','label'=>'Ano'));
						?>
					</div>
				<?=$this->form->end(); ?>
			</div>
		</div>
	</section>
</div>

<?php if ($categorias){ ?>
	<?php foreach ($categorias as $id_categoria=>$categoria){ ?>
	<div class="row">
		<section class="col-lg-12">
			<div class="box no-border">
				<div class="box-header with-border">
					<h3 class="box-title"><?=$categoria?></h3>
				</div>
				<div class="box-body">
					<table class="table table-hover table-striped">
						<thead>
							<tr>
								<th>&nbsp;</th>
								<?php foreach ($meses as $mes){ ?>
									<th class="text-center"><?=substr($mes['nome_mes'],0,3)?>/<?=$mes['ano']?></th>
								<?php } ?>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>Previsto</td>
								<?php foreach ($meses as $arr_mes){ ?>
									<?php
										$valor_orcamento = null;
										$valor_realizado = 0;
										
										$class_td = '';
										$mes = $arr_mes['mes'];
										$ano = $arr_mes['ano'];
										$chave = "{$id_categoria}_{$ano}_{$mes}";
										if (isset($orcamentos[$chave])){
											$valor_orcamento = $orcamentos[$chave]['valor_orcamento'];
											$valor_realizado = $orcamentos[$chave]['valor_realizado'];										
										}
										
										if ($valor_realizado > $valor_orcamento){
											$class_td = 'text-danger';
										}
									?>
									<?php if ($valor_realizado !== null){ ?>
										<td class="<?=$class_td?>"><?=number_format($valor_orcamento,2,",",".")?></td>
									<?php } else { ?>
										<td class="<?=$class_td?>">&nbsp;</td>
									<?php } ?>
								<?php } ?>
							</tr>
							<tr>
								<td>Realizado</td>
								<?php foreach ($meses as $arr_mes){ ?>
									<?php
										$valor_orcamento = null;
										$valor_realizado = 0;
										
										$class_td = '';
										$mes = $arr_mes['mes'];
										$ano = $arr_mes['ano'];
										$chave = "{$id_categoria}_{$ano}_{$mes}";
										if (isset($orcamentos[$chave])){
											$valor_orcamento = $orcamentos[$chave]['valor_orcamento'];
											$valor_realizado = $orcamentos[$chave]['valor_realizado'];
										}
										
										if ($valor_realizado > $valor_orcamento){
											$class_td = 'text-danger';
										}
									?>
									<?php if ($valor_realizado !== null){ ?>
										<td class="<?=$class_td?>"><?=number_format($valor_realizado,2,",",".")?></td>
									<?php } else { ?>
										<td class="<?=$class_td?>">&nbsp;</td>
									<?php } ?>
								<?php } ?>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</section>
	</div>
	<?php } ?>
<?php } ?>