<?php
App::uses('AppController', 'Controller');

/**
 * Static content controller
 *
 * Override this controller by placing a copy in controllers directory of an application
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers/pages-controller.html
 */
class UsuariosController extends AppController {

/**
 * This controller does not use a model
 *
 * @var array
 */
	public $uses = array('Caixa','Cidade','Estabelecimento','Usuario');
	public $components = array('FlashMessage','Maps');
	public $helpers = array('Tempo');

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow('inserir','login','logout','api_login');
	}

	public function ajax_saldo(){
		// $caixa_atual = $this->Session->read('caixa_atual');
		// pr($caixa_atual);

		// $this->set(compact('caixa_atual'));
		$this->layout = 'ajax';
	}

	public function inserir() {
		if ($this->data){
			$registro = $this->data;
			$this->Usuario->save($registro);
		}
	}

	public function login(){
      if ($this->request->is('post')) {
			$this->autoRender = false;
			// pr($this->Auth->login());
			// exit;
            if ($this->Auth->login()){
                $this->Session->setFlash('Bem vindo, '. $this->Auth->user('nome'),'flash_success');
                $this->redirect($this->Auth->redirectUrl());
            } else {
                $this->Session->setFlash('Email e/ou senha inválido(s)','flash_error');
                $this->redirect('/');
            }
        } else {
			$this->layout = "login";
		}
	}

	public function logout() {
		return $this->redirect($this->Auth->logout());
	}

	/*
		Descrição: Efetua login
		POST:
			email: Email do usuário
			senha: Senha do usuário
		Return:
			status:
				true: Sucesso ao efetuar login
				false: Erro ao efetuar login
			msg: Mensagem de retorno
			data:
				usuario_id: Id do usuário logado
				usuario_nome: Nome do usuário logado
				usuario_email: Email do usuário logado
				usuario_access_token: Token de acesso
	*/
	public function api_login(){
		$this->autoRender = false;
		$p = $this->api_request;
		$fields = array(
			'email' => array('type' => 'email', 'required' => true),
			'senha' => array('type' => 'text', 'required' => true)
		);

		// $ret_fields = ApiComponent::verifyFields($fields,$this->api_request);
		// if ($ret_fields->status){
			$usuario = $this->Usuario->find('first',array(
				'fields' => array('id','nome','email'),
				'conditions' => array(
					'email' => $p['email'],
					'senha' => AuthComponent::password($p['senha'])
				)
			));

			if ($usuario){
				$access_token = $this->Usuario->generateAccessToken($usuario['Usuario']['id']);
				if ($access_token){
					$this->api_ret['status'] = true;
					$this->api_ret['return_data'] = array(
						'usuario_id' => $usuario['Usuario']['id'],
						'usuario_nome' => $usuario['Usuario']['nome'],
						'usuario_email' => $usuario['Usuario']['email'],
						'usuario_access_token' => $access_token,
					);
				} else {
					$this->api_ret['msg'] = "Não foi possível gerar token de acesso";
				}
			} else {
				$this->api_ret['msg'] = "Email/Senha inválido(s)";
			}
			echo json_encode($this->api_ret);exit;
 		// }
	}
}
