<?php
	$agora = new DateTime();
?>

<!-- Main row -->
<div class="row">
	<section class="col-lg-12">
		<div class="box no-border">
			<div class="box-body">

				<nav class="nav-meses">
					<ul>
						<?php $maior_que_atual = 0;
							foreach ($arr_meses as $arr_mes){
								if ($arr_mes['selected']){
									$maior_que_atual = 1;
									?>
									<li class="mes-selecionado"><?php echo $arr_mes['mes_nome'] ?></li>
								<?php } else { ?>
									<li>
										<?php if ($maior_que_atual){ ?>
											<a href='/transacoes/<?=$arr_mes['ano']?>/<?=$arr_mes['mes']?>?<?php echo $_SERVER["QUERY_STRING"] ?>'><?php echo $arr_mes['mes_nome'] ?> <i class="fa fa-chevron-right seta-mes"></i></a>
										<?php } else { ?>
											<a href='/transacoes/<?=$arr_mes['ano']?>/<?=$arr_mes['mes']?>?<?php echo $_SERVER["QUERY_STRING"] ?>'><i class="fa fa-chevron-left seta-mes"></i> <?php echo $arr_mes['mes_nome'] ?></a>
										<?php } ?>
									</li>
								<?php } ?>
						<?php } ?>
					</ul>
				</nav>

				<a class="btn btn-primary btn-sm carrega-modal" href="/transacoes/modal_inserir/despesa">Adicionar despesa</a>
				<a class="btn btn-primary btn-sm carrega-modal" href="/transacoes/modal_inserir/lucro">Adicionar lucro</a>
				<a class="btn btn-primary btn-sm carrega-modal" href="/transacoes/modal_transferir">Adicionar transferência</a>
				<a class="btn btn-primary btn-sm carrega-modal" href="/transacoes/modal_emprestimo">Adicionar Empréstimo</a>

				<div class="filtros">
					<?php
						$form_caixa_id = null;
						if ($caixa){
							$form_caixa_id = $caixa['Caixa']['id'];
						}

						$form_cartao_id = null;
						if ($cartao){
							$form_cartao_id = $cartao['Cartao']['id'];
						}

						$form_categoria_id = null;
						if ($categoria){
							$form_categoria_id = $categoria['Categoria']['id'];
						}

						$form_pessoa_id = null;
						if ($pessoa){
							$form_pessoa_id = $pessoa['Cliente']['id'];
						}

						$tipos = [
								'despesa' => 'Despesa',
								'lucro' => 'Lucro',
								'transferencia' => 'Transferencia',
								'emprestimo' => 'Empréstimo',
						];

						echo $this->Form->create('Transacao',array('id'=>'TransacaoListarForm','url' => 'listar','inputDefaults'=>array('class'=>'form-control load-transacoes-ajax','placeholder'=>'','div'=>'form-group col-md-3','errorMessage' => false,'required'=>false)));
						echo $this->Form->input('caixa',array('name'=>'caixa','options'=>$caixas,'empty'=>'Todos','label'=>'Carteira','default'=>$form_caixa_id,'div'=>'form-group col-md-2 div-filtro'));
						echo $this->Form->input('cartao',array('name'=>'cartao','options'=>$cartoesList,'empty'=>'Todos','label'=>'Cartão','default'=>$form_cartao_id,'div'=>'form-group col-md-2 div-filtro'));
						echo $this->Form->input('categoria',array('name'=>'categoria','options'=>$categorias,'empty'=>'Todas','label'=>'Categoria','default'=>$form_categoria_id,'div'=>'form-group col-md-2 div-filtro'));
						echo $this->Form->input('pessoa',array('name'=>'pessoa','options'=>$pessoas,'empty'=>'Todas','label'=>'Pessoa','default'=>$form_pessoa_id,'div'=>'form-group col-md-2 div-filtro'));
						echo $this->Form->input('tipo',array('name'=>'tipo','options'=>$tipos,'empty'=>'Todos','label'=>'Tipo','default'=>$tipo,'div'=>'form-group col-md-2 div-filtro'));
						echo $this->Form->end();
					?>
				</div>
			</div>
		</div>
	</section>
</div>

<div class="row">
	<section class="col-lg-12">
		<?php
			$progress_despesas = $transacoes['total_despesa'] > 0 ? round($transacoes['total_pago']/$transacoes['total_despesa']*100) : 0;
			$progress_lucros = $transacoes['total_lucro'] > 0 ? round($transacoes['total_recebido']/$transacoes['total_lucro']*100) : 0;
			$progress_emprestimos = $transacoes['total_emprestimo'] > 0 ? round($transacoes['emprestimos_recebido']/$transacoes['total_emprestimo']*100) : 0;
		?>

		<div class="box">
      <div class="box-header">
        <h3 class="box-title">Estatísticas</h3>
      </div>
      <!-- /.card-header -->
      <div class="box-body p-0">
        <table class="table">
          <thead>
            <tr>
              <th>Tipo</th>
							<th>Pendente</th>
              <th>Concluído/Total</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>
								Despesas
							</td>
							<td>R$ <?=number_format($transacoes['total_pagar'],2,',','.')?></td>
              <td>R$ <?=number_format($transacoes['total_pago'],2,',','.')?> / <?=number_format($transacoes['total_despesa'],2,',','.')?></td>
            </tr>
						<tr>
              <td>
								Receitas
							</td>
							<td>R$ <?=number_format($transacoes['total_receber'],2,',','.')?></td>
              <td>R$ <?=number_format($transacoes['total_recebido'],2,',','.')?> / <?=number_format($transacoes['total_lucro'],2,',','.')?></td>
            </tr>
						<tr>
              <td>
								Empréstimos
							</td>
							<td>R$ <?=number_format($transacoes['emprestimos_receber'],2,',','.')?></td>
              <td>R$ <?=number_format($transacoes['emprestimos_recebido'],2,',','.')?> / <?=number_format($transacoes['total_emprestimo'],2,',','.')?></td>
            </tr>
          </tbody>
					<tfoot>
						<tr>
              <th>
								Despesas + Empréstimo
							</th>
							<th>R$ <?=number_format($transacoes['total_pagar']+$transacoes['emprestimos_receber'],2,',','.')?></th>
              <th>R$ <?=number_format($transacoes['total_pago']+$transacoes['emprestimos_recebido'],2,',','.')?>/<?=number_format($transacoes['total_despesa']+$transacoes['total_emprestimo'],2,',','.')?></th>
            </tr>
          </tfoot>
        </table>
      </div>
      <!-- /.card-body -->
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
					<?php /*foreach ($transacoes['cartoes'] as $c): ?>
					<div class="box-transacao-wrapper">
						<input type="checkbox" class="checkbox-selecionar-varias chk-id-tranasacao" style="display:none;" name="transacoes_ids[]" id="check-transacao-" value="" />
						<a href="" class="btn-salvar-transacao box-transacao" title="Editar" id="box-transacao-" data-id_transacao="">
							<div class="fake-checkbox-wrapper" id="fake-check-transacao-">
								<span>
									<i class="fa fa-check-square-o checked"></i>
									<i class="fa fa-square-o nonchecked"></i>
								</span>
							</div>
							<div class="data">
								<?php echo $c['dia_vencimento'] ?>
							</div>
							<div class="conteudo">
								<p class="categoria">Cartão de crédito</p>
								<p class="descricao">
									<?php echo $c['descricao'] ?>
								</p>
							</div>
							<div class="preco">
								<span>
								R$ <?php echo number_format($c['total'],2,",",".") ?>
								</span>
							</div>
						</a>
					</div>
					<?php endforeach */ ?>

					<?php foreach ($transacoes['transacoes'] as $r){
						$data = null;
						if ($r['Transacao']['data']){
							$data = DateTime::createFromFormat('d/m/Y',$r['Transacao']['data']);
						}

						$data_pagamento = null;
						if ($r['Transacao']['data_pagamento']){
							$data_pagamento = DateTime::createFromFormat('d/m/Y',$r['Transacao']['data_pagamento']);
						}

						$data_recebimento = null;
						if ($r['Transacao']['data_recebimento']){
							$data_recebimento = DateTime::createFromFormat('d/m/Y',$r['Transacao']['data_recebimento']);
						}

						$classTransacaoTipo = $r['Transacao']['tipo'];

						$classTransacaoData = "";
						if ($classTransacaoTipo == 'emprestimo' && $data_recebimento){
							$classTransacaoData = "em-dia";
						} else if ($classTransacaoTipo != 'emprestimo' && $data_pagamento){
							$classTransacaoData = "em-dia";
						} else if ($data < $agora){
							$classTransacaoData = "atrasado";
						}


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
								<?php echo substr($r['Transacao']['data'],0,2) ?>
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
