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
class ProdutosController extends AppController {

/**
 * This controller does not use a model
 *
 * @var array
 */
	public $uses = array('Produto','TransacoesProduto');
	public $components = array('FlashMessage','Util');
	// public $helpers = array('Tempo');

	public function beforeFilter() {
		parent::beforeFilter();
		// $this->Auth->allow('inserir','login','logout');
	}

	public function relatorio_gastos(){
		$produtos = $this->TransacoesProduto->find('all',array(
			'fields' => array('Transacao.data','Produto.*','sum(TransacoesProduto.valor_unitario) totalProduto'),
			'joins' => array(
				array(
					'table' => 'produtos',
					'alias' => 'Produto',
					'type' => 'INNER',
					'conditions' => array('Produto.id = TransacoesProduto.id_produto')
				),
				array(
					'table' => 'transacoes',
					'alias' => 'Transacao',
					'type' => 'INNER',
					'conditions' => array('Transacao.id = TransacoesProduto.id_transacao')
				)				
			),
			'group' => array(
				'left(Transacao.data,7)',
				'Produto.id'
			),
			'order' => array(
				'Transacao.data' => 'asc'
			)
		));
		
		$meses = array();
		$table_produtos = array();
		if ($produtos){
			$primeiro_mes = substr($produtos[0]['Transacao']['data'],0,7);
			$ultimo_mes = substr($produtos[count($produtos)-1]['Transacao']['data'],0,7);
			$this->Util->mesesPeriodoArray($primeiro_mes,$ultimo_mes);
			// foreach ($produtos as $p){
				// $meses[substr($p['Transacao']['data'],0,7)] = array();
			// }
		}
		
		// pr($primeiro_mes);
		// pr($ultimo_mes);
		exit;
		$this->set('produtos',$produtos);
		// pr($produtos);
		// exit;
	}
	
	public function estoque(){
		$produtos = $this->Produto->find('all',array(
			'conditions' => array(
				'tipo' => 'profissional'
			)
		));
		$this->set('produtos',$produtos);
	}
	
	public function autocomplete($nome){
		$this->autoRender = false;
		$produtos = $this->Produto->find('all',array(
			'fields' => array('id','nome'),
			'conditions' => array(
				'trim(upper(nome)) like' => "%".trim(strtoupper($nome))."%"
			)
		));
		echo json_encode($produtos);
	}
	
	public function form_produto_transacao($num=0){
		$this->layout = 'ajax';
		$this->set('num',$num);
	}
	
	public function ajax_row_produto($num=0){
		$this->layout = 'ajax';
		$this->set('num',$num);
	}	
}
