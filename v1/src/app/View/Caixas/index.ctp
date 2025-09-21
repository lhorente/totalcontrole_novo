<div class="alert alert-warning">Uma nova versão beta dessa tela está disponível. <a href="<?php echo $NEW_APP_URL ?>/wallets/dashboard" target="_blank">Acesse aqui</a>.</div>

<div class="row">
	<div class="usuario div-tipo-usuario col-md-12">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">Inserir caixa</h3>
			</div>
			<div class="panel-body">
				<?=$this->Form->create('Caixa',array('url' => 'salvar','inputDefaults'=>array('class'=>'form-control','label'=>false,'div'=>false)));?>
					<div class="row">
						<?php //echo $this->Form->input('return_type',array('name'=>'return_type','value'=>'json','type'=>'hidden','class'=>''));?>
						<div class="form-group col-md-2">
							<label>Título</label>
							<?=$this->Form->input('titulo',array('type'=>'text'));?>
						</div>
						<div class="form-group col-md-2">
							<label>Caixa Pai</label>
							<?=$this->Form->input('parent_id',array('options'=>$listCaixasPai,'empty'=>'Nenhum'));?>
						</div>
						<div class="form-group col-md-2">
							<label>Reserva</label>
							<?=$this->Form->input('exibir_no_saldo',array('options'=>array(0=>"Sim",1=>"Não")));?>
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
	<div class="col-md-12 lista-categorias">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">Caixas</h3>
			</div>
			<div class="panel-body lista-clientes">

				<?php foreach ($caixas->results as $i=>$r){ ?>
				<div class="panel panel-primary">
				  <div class="panel-heading">
				    <h3 class="panel-title"><?php echo $r['Caixa']['titulo'] ?></h3>
				  </div>
				  <div class="panel-body">
						<?php if ($r['children']){ ?>
					  <table class="table">
								<tr>
									<th style="width:50%;">Título</th>
									<th style="width:30%;">Saldo</th>
									<th style="width:20%;">Reserva</th>
								</tr>
								<?php foreach ($r['children'] as $child){ ?>
									<tr class="item" data-item="<?=$child['Caixa']['id']?>">
										<td><?=$child['Caixa']['titulo']?></td>
										<td>R$ <?php echo number_format($child['Caixa']['saldo'],2,",",".") ?></td>
										<td><?php echo $child['Caixa']['exibir_no_saldo'] ? "Não" : "Sim" ?></td>
									</tr>
									<?php } ?>
					  </table>
					<?php } ?>
				  </div>
					 <div class="panel-footer">
						 <span>Saldo líquido: R$ <?php echo number_format($r['Caixa']['saldo_liquido'],2,",",".") ?></span>
						 |
						 <span>Saldo reserva: R$ <?php echo number_format($r['Caixa']['saldo_reserva'],2,",",".") ?></span>
						</div>
				</div>
				<?php } ?>

				<table class="table">
						<tr>
							<th>Saldo líquido</th>
							<th>Saldo seguro</th>
							<th>Saldo reserva</th>
						</tr>
					</tr>
						<td>R$ <?php echo number_format($caixas->saldo_liquido,2,",",".") ?></td>
						<td>R$ <?php echo number_format($caixas->saldo_seguro,2,",",".") ?></td>
						<td>R$ <?php echo number_format($caixas->saldo_reserva,2,",",".") ?></td>
					<tr>
				</table>
			</div>
		</div>
	</div>
</div>
