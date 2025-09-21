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
class ProjetosController extends AppController {

/**
 * This controller does not use a model
 *
 * @var array
 */
	public $uses = array('Cliente','Projeto');
	public $components = array('FlashMessage','Util');

	public function beforeFilter() {
		parent::beforeFilter();
		// $this->Auth->allow('inserir','login','logout');
	}

	public function index(){
		$clientes = $this->Cliente->find('list',array(
			'fields' => array('Cliente.id','Cliente.nome'),
			'conditions' => array(
				'Cliente.id_usuario' => $this->Auth->user('id')
			),
			'order' => array(
				'nome' => 'asc'
			)
		));
		$projetos = $this->Projeto->find('all',array(
			'fields' => array('Projeto.id','Projeto.nome','Cliente.id','Cliente.nome'),
			'joins' => array(
				array(
					'table' => 'clientes',
					'alias' => 'Cliente',
					'type' => 'INNER',
					'conditions' => array('Cliente.id = Projeto.id_cliente')
				)
			),
			'order' => array(
				'Projeto.nome' => 'asc'
			)
		));
		
		$this->set('clientes',$clientes);
		$this->set('projetos',$projetos);
	}
	
	function editar($id=null){
		if ($id){
			$projeto = $this->Projeto->find('first',array(
				'recursive' => -1,
				'fields' => array('Projeto.id','Projeto.nome','Projeto.valor_hora','Cliente.id','Cliente.nome'),
				'joins' => array(
					array(
						'table' => 'clientes',
						'alias' => 'Cliente',
						'type' => 'INNER',
						'conditions' => array('Projeto.id_cliente = Cliente.id')
					)
				),
				'conditions' => array(
					'Projeto.id' => $id,
					'Cliente.id_usuario' => $this->Auth->user('id')
				)
			));

			$clientes = $this->Cliente->find('list',array(
				'fields' => array('Cliente.id','Cliente.nome'),
				'conditions' => array(
					'Cliente.id_usuario' => $this->Auth->user('id')
				),
				'order' => array(
					'nome' => 'asc'
				)
			));
		
			$this->set('clientes',$clientes);
			$this->set('servico',$projeto);
		}
	}
	
	public function salvar(){
		$this->autoRender = false;
		if ($this->data){
			$ret['status'] = true;
			$registro = $this->data;
			$cliente = $this->Cliente->find('count',array(
				'conditions' => array(
					'Cliente.id' => $registro['Projeto']['id_cliente'],
					'Cliente.id_usuario' => $this->Auth->user('id')
				)
			));
			if ($cliente){
				$registro['Projeto']['id_usuario'] = $this->Auth->user('id');
				// pr($registro);exit;
				$projeto = $this->Projeto->save($registro);
				if ($projeto){
					$this->Session->setFlash('Cadastro realizado com sucesso.', 'flash_success');
				} else {
					$errors_msg = $this->FlashMessage->humanizar($this->Projeto->validationErrors);
					if ($errors_msg){
						$this->Session->setFlash("Não foi possível salvar o serviço: <br />{$errors_msg}", 'flash_error');
					} else {
						$this->Session->setFlash('Não foi possível salvar o serviço.', 'flash_error');
					}	
				}
			} else {
				$this->Session->setFlash('Não foi possível salvar o serviço.', 'flash_error');
			}
		} else {
			$this->Session->setFlash('Não foi possível salvar o serviço.', 'flash_error');
		}
		$this->redirect($this->referer());
	}
	
	public function excluir($id = null){
		$this->autoRender = false;
		if ($id){
			$registro = $this->Cliente->find('first',array(
				'fields' => array('Cliente.id'),
				'conditions' => array(
					'Cliente.id' => $id,
					'Cliente.id_usuario' => $this->Auth->user('id')
				)
			));		
			if ($registro){
				if ($this->Cliente->delete($id)){
					$this->Session->setFlash('Cliente excluído com sucesso.', 'flash_success');
				} else {
					$this->Session->setFlash('Não foi possível excluir o cliente.', 'flash_error');
				}
			} else {
				$this->Session->setFlash('Não foi possível excluir o cliente.', 'flash_error');
			}
		} else {
			$this->Session->setFlash('Não foi possível excluir o cliente.', 'flash_error');
		}
		$this->redirect($this->referer());
	}
}
