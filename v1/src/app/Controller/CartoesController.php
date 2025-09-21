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
class CartoesController extends AppController {

/**
 * This controller does not use a model
 *
 * @var array
 */
	public $uses = array('Cartao','Transacao');
	public $components = array('FlashMessage','Util');
	// public $helpers = array('Tempo');

	public function beforeFilter() {
		parent::beforeFilter();
		// $this->Auth->allow('inserir','login','logout');
	}

	public function index(){

	}
	
	public function listar(){
		$this->layout = 'ajax';
		$cartoes = $this->Cartao->find('all',array(
			'fields' => array('id','descricao','dia_vencimento','dia_fechamento','data_validade')
		));		
		$this->set('cartoes',$cartoes);
	}	
	
	public function salvar(){
		$this->autoRender = false;
		$ret = array();
		if ($this->data){
			$ret['status'] = true;
			$registro = $this->data;
			$registro['Cartao']['id_usuario'] = $this->Auth->User('id');
			$registro['Cartao']['status'] = 'ativo';
			if (!$this->Cartao->save($registro)){
				$ret['status'] = false;
				$ret['errors_msg'] = $this->FlashMessage->humanizar($this->Cartao->validationErrors);			
			}
		}
		echo json_encode($ret);
	}
	
	public function excluir($id = null){
		$this->autoRender = false;
		$ret = array();
		$ret['status'] = false;	
		if ($id){
			$registro = $this->Cartao->find('first',array(
				'fields' => array('Cartao.id'),
				'conditions' => array(
					'Cartao.id' => $id,
					'Cartao.id_usuario' => $this->Auth->user('id')
				)
			));		
			if ($registro){
				$registro['Cartao']['status'] = 'excluido';
				if ($this->Cartao->save($registro)){
					$ret['status'] = true;
				}
			}
		}
		echo json_encode($ret);
	}
	
	public function relatorio_gastos($ano=null){
		if (!$ano){
			$ano = date("Y");
		}
		$cartoes = $this->Cartao->find('all',array(
			'fields' => array('Cartao.id','Cartao.descricao'),
			'conditions' => array(
				'Cartao.id_usuario' => $this->Auth->user('id')
			)
		));
		if  ($cartoes){
			foreach ($cartoes as &$cartao){
				$gastos = array();
				$transacoes = $this->Transacao->find('all',array(
					'conditions' => array(
						'Transacao.id_cartao' => $cartao['Cartao']['id'],
						'Transacao.tipo' => 'despesa',
						'Year(Transacao.data)' => $ano
					)
				));
				if ($transacoes){
					foreach ($transacoes as $transacao){
						if ($transacao['Transacao']['data'] && $transacao['Transacao']['data'] != '0000-00-00'){
							// $transacao['Transacao']['data'] = DateTime::createFromFormat('Y-m-d',$transacao['Transacao']['data']);
							// if ($transacao['Transacao']['data']){
								$mes = substr($transacao['Transacao']['data'],-7,2)*1;
								if (!isset($gastos[$mes])){
									$gastos[$mes] = 0;
								}
								$gastos[$mes] += $transacao['Transacao']['valor'];
							// }
						}
					}
				}
				$cartao['gastos'] = $gastos;
			}
		}
		// pr($gastos);
		$this->set('ano',$ano);
		$this->set('cartoes',$cartoes);
	}
}
