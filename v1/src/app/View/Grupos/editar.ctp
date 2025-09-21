<?php if ($grupo){ ?>
<ul class="nav nav-tabs" role="tablist">
	<li class=""><a href="<?=$this->webroot?>grupos/inserir">Inserir grupo</a></li>
	<li class="active"><a href="">Editar grupo</a></li>
	<li class=""><a href="<?=$this->webroot?>grupos">Listar grupos</a></li>
</ul>
<div class="tab-content col-md-12">	
	<?=$this->Form->create('Grupo')?>
	<div class="row">
		<div class="col-md-12">
			<h4 class="content-title"><u>Grupo</u></h4>	
			<?=$this->Form->input('id',array('type'=>'hidden'));?>
			<div class="row">
				<div class="form-group col-md-4">
					<label for="">Nome</label>
					<?=$this->Form->input('nome',array('class'=>'form-control','placeholder'=>'','label'=>false,'div'=>false,'errorMessage' => false,'required'=>false));?>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<h4 class="content-title"><u>Permissões do grupo</u></h4>
			<?=$this->Form->input('id',array('type'=>'hidden'));?>
			<div class="row">
				<div class="form-group col-md-12">
					<?php if ($telas){ ?>
					<div class="checkbox">
						<label>
							<input type="checkbox" class="selecionar-todos" /> <b>Selecionar todos</b>
						</label>
					</div>
					<div class="checkbox hide">
						<label>
							<input type="checkbox" class="desmarcar-todos" checked="checked" /> <b>Desmarcar todos</b>
						</label>
					</div>				
					<ul class="list-unstyled ul-permissoes ul-permissoes-n1">
						<?php foreach ($telas as $i=>$tela){ ?>
							<li class="n1">
								<div class="checkbox">
									<label>
										<input type="checkbox" value="<?=$i?>" name="data[Grupo][telas][<?=$tela['Tela']['id']?>]" <?=(isset($telas_grupos[$tela['Tela']['id']])?"checked='checked'":'')?> /> <?=$tela['Tela']['nome']?>
									</label>
								</div>
								<?php if ($tela['children']){ ?>
									<ul class="ul-permissoes-n2">
									<?php foreach ($tela['children'] as $i2=>$tela2){ ?>
										<li class="n2">
											<div class="checkbox">
												<label>
													<input type="checkbox" value="<?=$i2?>" name="data[Grupo][telas][<?=$tela2['Tela']['id']?>]" <?=(isset($telas_grupos[$tela2['Tela']['id']])?"checked='checked'":'')?> /> <?=$tela2['Tela']['nome']?>
												</label>
											</div>
											<?php if ($tela2['children']){ ?>
												<ul class="ul-permissoes-n3">
												<?php foreach ($tela2['children'] as $i3=>$tela3){ ?>
													<li class="n3">
														<div class="checkbox">
															<label>
																<input type="checkbox" value="<?=$i3?>" name="data[Grupo][telas][<?=$tela3['Tela']['id']?>]" <?=(isset($telas_grupos[$tela3['Tela']['id']])?"checked='checked'":'')?> /> <?=$tela3['Tela']['nome']?>
															</label>
														</div>
													</li>
												<?php } ?>
												</ul>
											<?php } ?>
										</li>
									<?php } ?>
									</ul>
								<?php } ?>
							</li>
						<?php } ?>
					</ul>
					<?php } ?>
				</div>
			</div>
			<div class="row">
				<div class="form-group col-md-1">
					<button type="submit" class="form-control btn btn-primary">Salvar</button>
				</div>				
			</div>
		</div>
		<?=$this->Form->end();?>
	</div>
</div>
<?php } ?>