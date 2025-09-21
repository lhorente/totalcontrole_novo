<?php
App::uses('Model', 'Model','AuthComponent', 'Controller/Component');

class Produto extends AppModel {
	public function verificaExiste($nome){
		$produto = $this->find('first',array(
			'fields' => array('id'),
			'conditions' => array(
				'trim(upper(nome))' => trim(strtoupper($nome))
			)
		));
		return $produto;
	}
	
	public function getByTransacao($id_transacao){
		App::import('model','TransacoesProduto');
		$TransacoesProduto = new TransacoesProduto();
		$produtos = $TransacoesProduto->find('all',array(
			'fields' => array('Produto.nome','TransacoesProduto.quantidade','TransacoesProduto.valor_unitario','TransacoesProduto.desconto','TransacoesProduto.juros'),
			'joins' => array(
				array(
					'table' => 'produtos',
					'alias' => 'Produto',
					'type' => 'INNER',
					'conditions' => array('Produto.id = TransacoesProduto.id_produto')
				)
			),
			'conditions' => array(
				'id_transacao' => $id_transacao
			)
		));
		return $produtos;
	}
}
