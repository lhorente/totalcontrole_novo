<?php
	$agora = new DateTime();
?>

<div class="row">
	<section class="col-lg-12">
		<div class="box no-border">
			<div class="box-header with-border">
				<h3 class="box-title">Estatísticas</h3>
            </div>
			<div class="box-body">
				<table class="table table-bordered table-hover">
					<tbody>
						<tr>
							<td>A pagar</td>
							<td>R$ <?=number_format($transacoes['total_pagar'],2,',','.')?></td>
						</tr>
						<tr>
							<td>Pago</td>
							<td>R$ <?=number_format($transacoes['total_pago'],2,',','.')?></td>
						</tr>
						<tr>
							<td>A receber</td>
							<td>R$ <?=number_format($transacoes['total_receber'],2,',','.')?></td>
						</tr>
						<tr>
							<td>Recebido</td>
							<td>R$ <?=number_format($transacoes['total_recebido'],2,',','.')?></td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</section>
</div>

<?php
	$query = parse_url($_SERVER['REQUEST_URI'],PHP_URL_QUERY);
	$path = parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);

	if($query){
		$url_exportar = $path . "?" . $query ."&exportar=csv";
	} else {
		$url_exportar = $path . "?exportar=csv";
	}
?>

<div class="row">
	<section class="col-lg-12">
		<div class="box no-border">
			<div class="box-header with-border">
				<h3 class="box-title">Transações</h3>

				<div class="box-tools pull-right">
					<a href="<?php echo $url_exportar ?>" target="_blank" class="btn btn-box-tool">Exportar CSV</a>
					<button type="button" class="btn btn-box-tool btn-abrir" style="display:none;">Abrir</button>
					<button type="button" class="btn btn-box-tool btn-selecionar-varias">Selecionar</button>
					<button type="button" class="btn btn-box-tool">Filtrar</button>
					<button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>

				</div>
			</div>
			<div class="box-body">
				<div class="row box-acoes-selecionadas hide">
					<div class="form-group col-md-3">
						<select class="form-control" id="select-transacoes-selecionadas">
							<option value="">Selecionadas</option>
							<option value="confirmar_pagamento">Confirmar pagamento</option>
							<option value="remover">Remover</option>
						</select>
					</div>
					<div class="form-group col-md-2 div-acoes-selecionadas div-confirmar-pagamento-selecionadas hide">
						<input class="form-control" id="pagarMultiData" placeholder="Data de pagamento" style="" name="data_pagamento" />
					</div>
					<div class="form-group col-md-3 div-acoes-selecionadas div-confirmar-pagamento-selecionadas hide">
						<button class="btn btn-default" id="btn-salvar-transacoes-selecionadas">Confirmar pagamento de selecionadas</button>
					</div>
					<div class="form-group col-md-2 div-acoes-selecionadas div-remover-selecionadas hide">
						<button class="btn btn-default" id="btn-remover-transacoes-selecionadas">Remover selecionadas</button>
					</div>
				</div>
				<div class="lista-transacoes">
					<?php foreach ($transacoes['transacoes'] as $r){
						$data = null;
						if ($r['Transacao']['data']){
							$data = DateTime::createFromFormat('d/m/Y',$r['Transacao']['data']);
						}

						$data_pagamento = null;
						if ($r['Transacao']['data_pagamento']){
							$data_pagamento = DateTime::createFromFormat('d/m/Y',$r['Transacao']['data_pagamento']);
						}

						// Muda cor do box de data de acordo com o vencimento
						$classTransacaoData = "";
						if ($data_pagamento){
							$classTransacaoData = "em-dia";
						} else if ($data < $agora){
							$classTransacaoData = "atrasado";
						}

						$classTransacaoTipo = $r['Transacao']['tipo'];

						// if ($r['Transacao']['tipo'] == 'transferencia'){
							// $url_editar  = "/transacoes/salvar_modal_transferencia/".$r['Transacao']['id'];
						// } else {
							$url_editar  = "/transacoes/salvar_modal/".$r['Transacao']['id'];
						// }
					?>
					<div class="box-transacao-wrapper">
						<input type="checkbox" class="checkbox-selecionar-varias chk-id-tranasacao" style="display:none;" name="transacoes_ids[]" id="check-transacao-<?php echo $r['Transacao']['id'] ?>" value="<?php echo $r['Transacao']['id'] ?>" />
						<a href="<?php echo $url_editar ?>" class="btn-salvar-transacao box-transacao <?php echo $classTransacaoData . " " . $classTransacaoTipo ?>" title="Editar" id="box-transacao-<?php echo $r['Transacao']['id'] ?>" data-id_transacao="<?php echo $r['Transacao']['id'] ?>">
							<div class="fake-checkbox-wrapper" id="fake-check-transacao-<?php echo $r['Transacao']['id'] ?>">
								<span>
									<i class="fa fa-check-square-o checked"></i>
									<i class="fa fa-square-o nonchecked"></i>
								</span>
							</div>
							<div class="data">
								<?php echo substr($r['Transacao']['data'],0,5) ?>
							</div>
							<div class="conteudo">
								<p class="categoria">
									<?php
										$categoria = "";
										if ($r['Transacao']['tipo'] == 'transferencia'){
											$categoria = '<span>Transferencia</span>';
											if (isset($r['CaixaPai']) && $r['CaixaPai']['id']){
												$categoria .= '<span class="caixa">'.$r['CaixaPai']['titulo'].'</span>';
											}
											$categoria .= '<span class="caixa">'.$r['Caixa']['titulo'].'</span>';
											$categoria .= '<span class="caixa_para"> para '.$r['CaixaPara']['titulo'].'</span>';
										} else {
											if ($r['CategoriaPai']){
												$categoria = '<span>'.$r['CategoriaPai']['nome'].'</span> >';
											}
											$categoria .= '<span>'.$r['Categoria']['nome'].'</span>';

											if (isset($r['CaixaPai']) && $r['CaixaPai']['id']){
												$categoria .= '<span class="caixa">'.$r['CaixaPai']['titulo'].'</span>';
											}
											$categoria .= '<span class="caixa">'.$r['Caixa']['titulo'].'</span>';
										}

										echo $categoria;
									?>

									<?php if ($r['Cartao']){ ?>
										<span class="cartao"><?=$r['Cartao']['descricao']?></span>
									<?php } ?>
								</p>

								<p class="descricao">
									<?php echo $r['Transacao']['descricao'] ?>
								</p>
							</div>
							<div class="preco">
								<span>
								R$ <?php echo number_format($r['Transacao']['valor'],2,",",".") ?>
								</span>
							</div>
						</a>
					</div>
					<?php } ?>
				</div>
			</div><!-- /.box-body -->
		</div>
	</section>
</div>
