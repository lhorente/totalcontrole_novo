<div class="panel panel-default">
	<div class="panel-body">
		<div class="usuario div-tipo-usuario col-md-12">
			<form role="form" method="post" action="/usuarios/inserir">
				<div class="panel panel-default">
					<div class="panel-heading">
						<h3 class="panel-title">Informações pessoais</h3>
					</div>			
					<div class="panel-body">
						<div class="row">
							<div class="form-group col-md-6 col-sd-12">
								<label>Nome completo*: </label>
								<?=$this->Form->input('Usuario.nome', array('class' => 'form-control', 'label' => false, 'div' => false));?>
							</div>			
							<div class="form-group col-md-6 col-sd-12">
								<label>Email*:</label>
								<?=$this->Form->input('Usuario.email', array('class' => 'form-control', 'type' => 'email', 'label' => false, 'div' => false));?>
								<span class="help-block">Seu email será usado como login para acesso.</span>
							</div>
						</div>
						<div class="row">
							<div class="form-group col-md-6 col-sd-12">
								<label>Senha*:</label>
								<?=$this->Form->input('Usuario.senha', array('class' => 'form-control', 'type' => 'password', 'label' => false, 'div' => false));?>
							</div>
							<div class="form-group col-md-6 col-sd-12">
								<label>Confirma senha*:</label>
								<?=$this->Form->input('Usuario.confirma_senha', array('class' => 'form-control', 'type' => 'password', 'label' => false, 'div' => false));?>
							</div>
						</div>
						<span class="help-block">*: Campos obrigatórios</span>
					</div>
				</div>
				<button type="submit" class="btn btn-default">Finalizar</button>
			</form>
		</div>
	</div>
</div>