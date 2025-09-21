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
class ClientesController extends AppController {

/**
 * This controller does not use a model
 *
 * @var array
 */
	public $uses = array('Cliente','Servico','Transacao');
	public $components = array('FlashMessage','Util');
	public $helpers = array('Tempo');

	public function beforeFilter() {
		parent::beforeFilter();
		// $this->Auth->allow('inserir','login','logout');
	}

	public function index(){
		$clientes = $this->Cliente->find('all',array(
			'fields' => array(),
			'order' => array(
				'nome' => 'asc'
			)
		));
		if ($clientes){
			foreach ($clientes as &$cliente){
				$cliente['Servicos'] = $this->Servico->find('all',array(
					'conditions' => array(
						'id_cliente' => $cliente['Cliente']['id']
					)
				));
				$cliente['Cliente']['total_servicos'] = count($cliente['Servicos']);
				$cliente['Cliente']['valor_total_servicos'] = 0;
				$cliente['Cliente']['valor_total_servicos_pagos'] = 0;
				$cliente['Cliente']['valor_total_servicos_a_pagar'] = 0;
				$cliente['Cliente']['horas_total_servicos'] = 0;
				
				if ($cliente['Servicos']){
					foreach ($cliente['Servicos'] as $servico){
						$valor = $this->Transacao->find('first',array(
							'fields' => array('sum(valor_pago) as total'),
							'conditions' => array(
								'Transacao.id_servico' => $servico['Servico']['id'],
								'Transacao.tipo' => 'lucro',
								'Transacao.status' => 'disponivel'
							)
						));
						if ($valor && $valor[0] && $valor[0]['total']){
							$servico['Servico']['valor_pago'] = $valor[0]['total'];
						} else {
							$servico['Servico']['valor_pago'] = 0;
						}
						
						$cliente['Cliente']['valor_total_servicos_pagos'] += $servico['Servico']['valor_pago'];
						$cliente['Cliente']['horas_total_servicos'] += $servico['Servico']['quantidade_horas'];
						$cliente['Cliente']['valor_total_servicos'] += $servico['Servico']['valor_hora']*$servico['Servico']['quantidade_horas'];
						if (!$servico['Servico']['valor_pago']){
							$cliente['Cliente']['valor_total_servicos_a_pagar'] += $servico['Servico']['valor_hora']*$servico['Servico']['quantidade_horas'];
						}						
					}
				}
			}
		}
		$this->set('clientes',$clientes);
	}

	public function salvar(){
		$this->autoRender = false;
		if ($this->data){
			$ret['status'] = true;
			$registro = $this->data;
			
			$registro['Cliente']['id_usuario'] = $this->Auth->user('id');
			if ($this->Cliente->save($registro)){
				$this->Session->setFlash('Cadastro realizado com sucesso.', 'flash_success');
			} else {
				$errors_msg = $this->FlashMessage->humanizar($this->Cliente->validationErrors);
				if ($errors_msg){
					$this->Session->setFlash("Não foi possível salvar o cliente: <br />{$errors_msg}", 'flash_error');
				} else {
					$this->Session->setFlash('Não foi possível salvar o cliente.', 'flash_error');
				}	
			}
		} else {
			$this->Session->setFlash('Não foi possível salvar o cliente.', 'flash_error');
		}
		$this->redirect('/clientes');
	}
	
	public function editar($id=null){
		if ($id){
			$cliente = $this->Cliente->find('first',array(
				'conditions' => array(
					'Cliente.id' => $id,
					'Cliente.id_usuario' => $this->Auth->user('id')
				)
			));
			if (!$cliente){
				
			}
			
			$this->data = $cliente;
			$this->set('cliente',$cliente);
		}
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
	
	function ver(){
		
	}
}
