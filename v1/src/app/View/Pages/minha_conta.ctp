<script>
	$(document).ready(function(){
		$(".input_tipo_cadastro").change(function(){
			var tipo = $(this).val();
			$(".div-tipo-usuario").addClass('hide');
			$("."+tipo).removeClass('hide');
		});
		$('.btn-alterar-senha').click(function(){
			$('.div-senha').addClass('hide');
			$('.div-alterar-senha').removeClass('hide');
			$('.input-senha').attr('disabled',false);
			$('.input-senha').attr('required',true);
			$('.input-confirma-senha').attr('disabled',false);
			$('.input-confirma-senha').attr('required',true);
		});
	})
</script>
<div class="panel panel-default">
	<div class="panel-body">
		<div class="usuario div-tipo-usuario col-md-12">
			<form role="form" method="post" action="/usuarios/alterar">
				<div class="panel panel-default">
					<div class="panel-heading">
						<h3 class="panel-title">Informações pessoais</h3>
					</div>			
					<div class="panel-body">
						<div class="row">
							<?=$this->Form->input('Usuario.id', array('type' => 'hidden', 'label' => false, 'div' => false));?>
							<div class="form-group col-md-6 col-sd-12">
								<label>Nome completo*: </label>
								<?=$this->Form->input('Usuario.nome', array('class' => 'form-control', 'label' => false, 'div' => false));?>
							</div>			
							<div class="form-group col-md-6 col-sd-12">
								<label>Email:</label>
								<?=$this->Form->input('Usuario.email', array('disabled' => true,'class' => 'form-control', 'type' => 'email', 'label' => false, 'div' => false));?>
							</div>
						</div>
						<div class="row div-senha">
							<div class="form-group col-md-6 col-sd-12">
								<label>Senha:</label>
								<div class="input-group">
									<?=$this->Form->input('Usuario.senha', array('value' => '*********', 'disabled' => true,'class' => 'form-control', 'label' => false, 'div' => false));?>
									<span class="input-group-btn">
										<button class="btn btn-default btn-alterar-senha" type="button">Alterar senha</button>
									</span>
								</div>								
							</div>					
						</div>
						<div class="row div-alterar-senha hide">
							<div class="form-group col-md-6 col-sd-12 div-confirma-senha">
								<label>Senha*:</label>
								<?=$this->Form->input('Usuario.senha', array('disabled' => true,'class' => 'input-senha form-control', 'type' => 'password', 'label' => false, 'div' => false));?>
							</div>
							<div class="form-group col-md-6 col-sd-12 div-confirma-senha">
								<label>Confirma senha*:</label>
								<?=$this->Form->input('Usuario.confirma_senha', array('disabled' => true,'class' => 'input-confirma-senha form-control', 'type' => 'password', 'label' => false, 'div' => false));?>
							</div>							
						</div>						
						<span class="help-block">*: Campos obrigatórios</span>
						<button type="submit" class="btn btn-default">Alterar</button>
					</div>
				</div>
			</form>
		</div>
		<div class="usuario div-tipo-usuario col-md-12">
			<div class="panel panel-default">
				<div class="panel-heading">
					<h3 class="panel-title">Meus estabelcimentos</h3>
				</div>			
				<div class="panel-body">
					<div class="row">
						<div class="col-md-12">
							<a href="/minha_conta/estabelecimentos/inserir" class="btn btn-primary">Adicionar novo local</a>
						</div>
					</div>				
					<?php if ($estabelecimentos){ ?>
					<table class="table table-hover">
						<tr>
							<th>Nome</th>
							<th>Endereço</th>
							<th>Site</th>
							<th>Ações</th>
						</tr>
						<?php foreach ($estabelecimentos as $e){ ?>
						<tr>
							<td><a href="/locais/<?=$e['Estabelecimento']['permalink']?>"><?=$e['Estabelecimento']['nome']?></a></td>
							<td><?=$e['Estabelecimento']['logradouro']?>, <?=$e['Estabelecimento']['numero']?> - <?=$e['Estabelecimento']['bairro']?> - <?=$e['Cidade']['nome']?> / <?=$e['Estado']['sigla']?></td>
							<td><a href="/out?url=<?=base64_encode($e['Estabelecimento']['site'])?>" target="_blank"><?=$e['Estabelecimento']['site']?></a></td>
							<td><a href="/minha_conta/estabelecimentos/editar/<?=$e['Estabelecimento']['id']?>"><span class="glyphicon glyphicon-edit"></span> Editar</a></td>
						</tr>
						<?php } ?>
					</table>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>
</div>