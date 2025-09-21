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
class ServicosController extends AppController {

/**
 * This controller does not use a model
 *
 * @var array
 */
	public $uses = array('Cliente','Servico','ServicosOrcamento','Transacao');
	public $components = array('FlashMessage','Util');

	public function beforeFilter() {
		parent::beforeFilter();
		// $this->Auth->allow('inserir','login','logout');
	}

	public function index(){
		$clientes = $this->Cliente->find('list',array(
			'fields' => array('Cliente.id','Cliente.nome'),
			'order' => array(
				'nome' => 'asc'
			)
		));
		$servicos = $this->Servico->find('all',array(
			'fields' => array(),
			'order' => array(
				'data_aprovacao' => 'asc',
				'data_pedido' => 'asc'
			)
		));
		if ($servicos){
			foreach ($servicos as &$s){
				$s['Servico']['valor'] = $s['Servico']['quantidade_horas']*$s['Servico']['valor_hora'];
				$valor_pago = $this->Transacao->field('sum(valor_pago)',array(
					'id_servico' => $s['Servico']['id']
				));
				$s['Servico']['valor_pago'] = $valor_pago;
			}
		}
		
		$this->set('clientes',$clientes);
		$this->set('servicos',$servicos);
	}
	
	function editar($id=null){
		if ($id){
			$servico = $this->Servico->find('first',array(
				'recursive' => -1,
				'fields' => array('Servico.id','Servico.descricao','Servico.data_pedido','Servico.data_aprovacao','Servico.quantidade_horas','Servico.valor_hora','Servico.desconto','Servico.data_aprovacao','Servico.data_inicio','Servico.data_fim','Servico.status','Cliente.id','Cliente.nome'),
				'joins' => array(
					array(
						'table' => 'clientes',
						'alias' => 'Cliente',
						'type' => 'INNER',
						'conditions' => array('Servico.id_cliente = Cliente.id')
					)
				),
				'conditions' => array(
					'Servico.id' => $id
				)
			));
			
			$servico['ServicosOrcamentos'] = $this->ServicosOrcamento->find('all',array(
				'conditions' => array(
					'id_servico' => $id
				)
			));
			
			$clientes = $this->Cliente->find('list',array('fields' => array('id','nome')));
			$this->set('clientes',$clientes);
			$this->set('servico',$servico);
		}
	}
	
	public function salvar(){
		$this->autoRender = false;
		if ($this->data){
			$ret['status'] = true;
			$registro = $this->data;
			$cliente = $this->Cliente->find('count',array(
				'conditions' => array(
					'Cliente.id' => $registro['Servico']['id_cliente'],
					'Cliente.id_usuario' => $this->Auth->user('id')
				)
			));
			if ($cliente){
				$registro['Servico']['id_usuario'] = $this->Auth->user('id');
				// pr($registro);exit;
				$servico = $this->Servico->save($registro);
				if ($servico){
					$this->ServicosOrcamento->deleteAll(array('id_servico' => $servico['Servico']['id']));
					if ($registro['orcamentos']){
						foreach ($registro['orcamentos'] as $orcamento){
							$orcamento['ServicosOrcamento']['id_servico'] = $servico['Servico']['id'];
							$this->ServicosOrcamento->save($orcamento);
							$this->ServicosOrcamento->id = null;
						}
					}
					$this->Session->setFlash('Cadastro realizado com sucesso.', 'flash_success');
				} else {
					$errors_msg = $this->FlashMessage->humanizar($this->Servico->validationErrors);
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
			$registro = $this->Servico->find('first',array(
				'fields' => array('Servico.id'),
				'conditions' => array(
					'Servico.id' => $id,
					'Servico.id_usuario' => $this->Auth->user('id')
				)
			));		
			if ($registro){
				if ($this->Servico->delete($id)){
					$this->Session->setFlash('Serviço excluído com sucesso.', 'flash_success');
				} else {
					$this->Session->setFlash('Não foi possível excluir o Serviço.', 'flash_error');
				}
			} else {
				$this->Session->setFlash('Não foi possível excluir o Serviço.', 'flash_error');
			}
		} else {
			$this->Session->setFlash('Não foi possível excluir o Serviço.', 'flash_error');
		}
		$this->redirect($this->referer());
	}
}
