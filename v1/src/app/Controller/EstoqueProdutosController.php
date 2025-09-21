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
class EstoqueProdutosController extends AppController {

/**
 * This controller does not use a model
 *
 * @var array
 */
	public $uses = array('EstoqueProduto','Produto','TransacoesProduto');
	// public $components = array('FlashMessage','Util');
	// public $helpers = array('Tempo');

	public function beforeFilter() {
		parent::beforeFilter();
		// $this->Auth->allow('inserir','login','logout');
	}

	public function index(){
		$produtos_list = $this->Produto->find('list',array(
			'fields' => array('Produto.id','Produto.nome'),
			'conditions' => array(
				'id_usuario' => $this->Auth->user('id')
			),
			'order' => array(
				'Produto.nome' => 'asc'
			)
		));
		
		$produtos = $this->EstoqueProduto->find('all',array(
			'fields' => array('EstoqueProduto.id','EstoqueProduto.valor_venda','Produto.id','Produto.nome'),
			'joins' => array(
				array(
					'table' => 'produtos',
					'alias' => 'Produto',
					'type' => 'INNER',
					'conditions' => array(
						'Produto.id = EstoqueProduto.id_produto'
					)
				)
			),
			'conditions' => array(
				'EstoqueProduto.id_usuario' => $this->Auth->user('id')
			),
			'order' => array(
				'Produto.nome' => 'asc'
			)
		));
		$produtos_ids = array();
		if ($produtos){
			foreach ($produtos as &$produto){
				$produtos_ids = 
				$transacoes_produtos = $this->TransacoesProduto->find('all',array(
					'fields' => array('TransacoesProduto.valor_unitario','TransacoesProduto.quantidade','TransacoesProduto.juros','TransacoesProduto.desconto','Transacao.tipo'),
					'joins' => array(
						array(
							'table' => 'transacoes',
							'alias' => 'Transacao',
							'type' => 'INNER',
							'conditions' => array('Transacao.id = TransacoesProduto.id_transacao')
						)
					),
					'conditions' => array(
						'TransacoesProduto.id_produto' => $produto['Produto']['id']
					)
				));
				$produto['EstoqueProduto']['total_compras'] = 0;
				$produto['EstoqueProduto']['total_vendas'] = 0;
				$produto['EstoqueProduto']['valor_total_compras'] = 0;
				$produto['EstoqueProduto']['valor_total_vendas'] = 0;
				if ($transacoes_produtos){
					foreach ($transacoes_produtos as $transacoes_produto){
						if ($transacoes_produto['Transacao']['tipo'] == 'lucro'){
							$produto['EstoqueProduto']['total_vendas'] += $transacoes_produto['TransacoesProduto']['quantidade'];
							$produto['EstoqueProduto']['valor_total_vendas'] += ($transacoes_produto['TransacoesProduto']['valor_unitario']*$transacoes_produto['TransacoesProduto']['quantidade'])-$transacoes_produto['TransacoesProduto']['desconto']+$transacoes_produto['TransacoesProduto']['juros'];
						} else if ($transacoes_produto['Transacao']['tipo'] == 'despesa') {
							$produto['EstoqueProduto']['total_compras'] += $transacoes_produto['TransacoesProduto']['quantidade'];
							$produto['EstoqueProduto']['valor_total_compras'] += ($transacoes_produto['TransacoesProduto']['valor_unitario']*$transacoes_produto['TransacoesProduto']['quantidade'])-$transacoes_produto['TransacoesProduto']['desconto']+$transacoes_produto['TransacoesProduto']['juros'];
						}
					}
				}
				$produto['EstoqueProduto']['quantidade_estoque'] = $produto['EstoqueProduto']['total_compras']-$produto['EstoqueProduto']['total_vendas'];
			}
		}
		$this->set('produtos_list',$produtos_list);
		$this->set('produtos',$produtos);
	}
	
	public function salvar(){
		$this->autoRender = false;
		if ($this->data){
			$ret['status'] = true;
			$registro = $this->data;
			$produto = $this->Produto->find('count',array(
				'conditions' => array(
					'Produto.id' => $registro['EstoqueProduto']['id_produto'],
					'Produto.id_usuario' => $this->Auth->user('id')
				)
			));
			if ($produto){
				// pr($registro);exit;
				$registro['EstoqueProduto']['id_usuario'] = $this->Auth->user('id');
				$estoque_produto_saved = $this->EstoqueProduto->save($registro);
				if ($estoque_produto_saved){
					$this->Session->setFlash('Cadastro realizado com sucesso.', 'flash_success');
				} else {
					$errors_msg = $this->FlashMessage->humanizar($this->EstoqueProduto->validationErrors);
					if ($errors_msg){
						$this->Session->setFlash("Não foi possível salvar o produto: <br />{$errors_msg}", 'flash_error');
					} else {
						$this->Session->setFlash('Não foi possível salvar o produto.', 'flash_error');
					}	
				}
			} else {
				$this->Session->setFlash('Não foi possível salvar o produto.', 'flash_error');
			}
		} else {
			$this->Session->setFlash('Não foi possível salvar o produto.', 'flash_error');
		}
		$this->redirect($this->referer());
	}
}
