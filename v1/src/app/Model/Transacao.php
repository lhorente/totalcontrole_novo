<?php
/**
 * Application model for CakePHP.
 *
 * This file is application-wide model file. You can put all
 * application-wide model-related methods here.
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Model
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

App::uses('Model', 'Model','AuthComponent', 'Controller/Component');

/**
 * Application model for Cake.
 *
 * Add your application-wide methods in the class below, your models
 * will inherit them.
 *
 * @package       app.Model
 */
class Transacao extends AppModel {
	public $useTable = 'transacoes';
    public $validate = array(
		'data' => array(
			'rule' => array('validateData'),
			'message' => 'Data não preenchida/inválida.'
		),
        'valor' => array(
			'rule'    => 'notBlank',
			'message' => 'Valor não preenchido/inválido',
			'on' => 'create'
        ),
        'descricao' => array(
			'rule'    => 'notBlank',
			'message' => 'Descrição não preenchida/inválida',
			'on' => 'create'
        ),
        'id_categoria' => array(
			'rule'    => 'notBlank',
			'message' => 'Categoria não preenchida/inválida',
			'on' => 'create'
        )
    );

    public $belongsTo = array(
        'Categoria' => array(
            'className' => 'Categoria',
            'foreignKey' => 'id_categoria'
        ),
        'Cliente' => array(
            'className' => 'Cliente',
            'foreignKey' => 'id_cliente'
        ),
		'Servico' => array(
            'className' => 'Servico',
            'foreignKey' => 'id_servico'
        ),
		'Cartao' => array(
            'className' => 'Cartao',
            'foreignKey' => 'id_cartao'
        )
    );

	public function validateData($check){
		if (isset($check['data']) && $check['data']){
			$date = DateTime::createFromFormat('d/m/Y', $check['data']);
			if ($date instanceof DateTime) {
				return true;
			}
		}
		return false;
	}

	public function afterFind($results, $primary = false) {
		foreach ($results as $key => $val) {
			if (isset($val['Transacao']['data'])){
				$results[$key]['Transacao']['data'] = date('d/m/Y',strtotime($results[$key]['Transacao']['data']));
			}
			if (isset($val['Transacao']['data_pagamento'])){
				if ($val['Transacao']['data_pagamento'] == '0000-00-00'){
					$results[$key]['Transacao']['data_pagamento'] = false;
				} else {
					$results[$key]['Transacao']['data_pagamento'] = date('d/m/Y',strtotime($results[$key]['Transacao']['data_pagamento']));
				}
			}

			if (isset($val['Transacao']['data_recebimento'])){
				if ($val['Transacao']['data_recebimento'] == '0000-00-00'){
					$results[$key]['Transacao']['data_recebimento'] = false;
				} else {
					$results[$key]['Transacao']['data_recebimento'] = date('d/m/Y',strtotime($results[$key]['Transacao']['data_recebimento']));
				}
			}

			if (isset($val['Transacao']['valor'])){
				$results[$key]['Transacao']['valor_formatado'] = 'R$ '. number_format($results[$key]['Transacao']['valor'],2,',','.');
			}
			if (isset($val['Transacao']['valor_pago'])){
				$results[$key]['Transacao']['valor_pago_formatado'] = 'R$ '. number_format($results[$key]['Transacao']['valor_pago'],2,',','.');
			}
		}
		return $results;
	}

	public function beforeFind($query){
		if (!(isset($query['conditions']) && isset($query['conditions']['Transacao.id_usuario']))){
		// if (!isset($query['conditions']) || (isset($query['conditions']) && !isset($query['conditions']['Transacao.id_usuario']))){
			// pr($query);exit;
			$user = $this->getCurrentUser();
			$query['conditions']['Transacao.id_usuario'] = $user['id'];
		}
		return $query;
	}

	public function beforeSave($options = array()) {
		$ret = true;

			// Validar data
			if (isset($this->data[$this->alias]['data'])) {
				$date = DateTime::createFromFormat('d/m/Y', $this->data[$this->alias]['data']);
				if ($date instanceof DateTime) {
					$this->data[$this->alias]['data'] = $date->format('Y-m-d');
				} else {
					$this->invalidate('data', 'Data inválida/não informada.');
					$ret = false;
				}
			} else {
				$this->invalidate('data', 'Data inválida/não informada.');
				$ret = false;
			}

			// Validar data de pagamento
			if (isset($this->data[$this->alias]['data_pagamento']) && $this->data[$this->alias]['data_pagamento']) {
				$date = DateTime::createFromFormat('d/m/Y', $this->data[$this->alias]['data_pagamento']);
				if ($date instanceof DateTime) {
					$this->data[$this->alias]['data_pagamento'] = $date->format('Y-m-d');
				} else {
					$this->invalidate('data_pagamento', 'Data de pagamento inválida');
					$ret = false;
				}
			}

			// Validar data de recebimento
			if (isset($this->data[$this->alias]['data_recebimento']) && $this->data[$this->alias]['data_recebimento']) {
				$date = DateTime::createFromFormat('d/m/Y', $this->data[$this->alias]['data_recebimento']);
				if ($date instanceof DateTime) {
					$this->data[$this->alias]['data_recebimento'] = $date->format('Y-m-d');
				} else {
					$this->invalidate('data_recebimento', 'Data de recebimento inválida');
					$ret = false;
				}
			}

			if (isset($this->data[$this->alias]['valor'])) {
				$this->data[$this->alias]['valor'] = $this->trocaVirgulaPonto($this->data[$this->alias]['valor']);
			}
			if (isset($this->data[$this->alias]['valor_pago'])) {
				$this->data[$this->alias]['valor_pago'] = $this->trocaVirgulaPonto($this->data[$this->alias]['valor_pago']);
			}

		if ($options['validate'] == 1){
			return $ret;
		}
		return true;
	}

	public function get($id_usuario=null,$ano=null,$mes=null,$dia=null,$id_categoria=null,$id_cartao=null,$tipo=null,$id_cliente=null,$id_caixa=null){
		App::import('model','Caixa');
		App::import('model','Produto');
		App::import('model','Categoria');
		$Caixa = new Caixa();
		$Produto = new Produto();
		$Categoria = new Categoria();

		if (!$id_usuario){
			$id_usuario = CakeSession::read("Auth.User.id");
		}

		$conditions = array(
			'Transacao.status !=' => 'cancelado',
		);

		if ($id_usuario){
			$conditions['Transacao.id_usuario'] = $id_usuario;
		}

		if ($id_caixa){
			$conditions['Transacao.id_caixa'] = $id_caixa;
		}

		if ($ano){
			$conditions['YEAR(data)'] = $ano;
		}

		if ($mes){
			$conditions['MONTH(data)'] = $mes;
		}

		if ($dia){
			$conditions['DAY(data)'] = $dia;
		}

		if ($id_categoria){
			$categorias = $Categoria->getSubCategories($id_categoria);
			// var_dump($categorias);
			if ($categorias){
				$categorias[] = $id_categoria;
			} else {
				$categorias = $id_categoria;
			}
			$conditions['Transacao.id_categoria'] = $categorias;
		}

		if ($id_cartao){
			$conditions['Transacao.id_cartao'] = $id_cartao;
		}

		if ($tipo){
			$conditions['Transacao.tipo'] = $tipo;
		}

		if ($id_cliente){
			$conditions['Transacao.id_cliente'] = $id_cliente;
		}

		$transacoes = $this->find('all',array(
			'recursive' => 2,
			'fields' => array('Transacao.id','Transacao.data','Transacao.data_pagamento','Transacao.data_recebimento','Transacao.descricao','Transacao.valor','Transacao.tipo','Transacao.transacao_origem', 'Transacao.id_caixa_para',
								'Categoria.id','Categoria.nome','Categoria.parent_id','Categoria.icon_class','Cliente.id','Cliente.nome','Servico.id','Servico.descricao','Transacao.id_cartao',
								'Caixa.id','Caixa.titulo','CaixaPai.id','CaixaPai.titulo'),
			'joins' => [
				[
					'table' => 'caixas',
					'alias' => 'Caixa',
					'type' => 'INNER',
					'conditions' => ['Caixa.id = Transacao.id_caixa']
				],
				[
					'table' => 'caixas',
					'alias' => 'CaixaPai',
					'type' => 'LEFT',
					'conditions' => ['CaixaPai.id = Caixa.parent_id']
				]
			],
			'conditions' => $conditions,
			'order' => array(
				'Transacao.data' => 'desc',
				'Transacao.id_cartao' => 'asc',
				'Transacao.descricao' => 'asc'
			)
		));

		if ($transacoes){
			foreach ($transacoes as &$t){
				$id_transacao = $t['Transacao']['id'];
				$categoria_pai = $Categoria->find('first',array(
					'conditions' => array(
						'id' => $t['Categoria']['parent_id']
					)
				));
				if ($categoria_pai){
					$t['CategoriaPai'] = $categoria_pai['Categoria'];
				} else {
					$t['CategoriaPai'] = null;
				}

				// Transferências
				if ($t['Transacao']['tipo'] == "transferencia"){
					$t['Categoria']['titulo'] = "Transferência";

					$caixaPara = $Caixa->getById($t['Transacao']['id_caixa_para'],$id_usuario);
					if ($caixaPara){
						$t['CaixaPara'] = $caixaPara['Caixa'];
					}
				}
				//$t['Produtos'] = $Produto->getByTransacao($id_transacao);
			}
		}

		return $transacoes;
	}

	// ID usuário necessário para API
	public function listar($id_usuario=null,$ano=null,$mes=null,$dia=null,$id_categoria=null,$cartao=null,$tipo=null,$id_cliente=null,$id_caixa=null){
		$ret = array(
			'transacoes' => array(),
			'total_pagar' => 0,
			'total_pago' => 0,
			'total_despesa' => 0,
			'total_receber' => 0,
			'total_recebido' => 0,
			'total_lucro' => 0,
			'emprestimos_recebido' => 0,
			'emprestimos_receber' => 0,
			'total_emprestimo' => 0,
			'emprestimos' => []
		);

		$transacoes = $this->get($id_usuario,$ano,$mes,$dia,$id_categoria,$cartao,$tipo,$id_cliente,$id_caixa);

		if ($transacoes){
			foreach ($transacoes as &$t){
				$id_transacao = $t['Transacao']['id'];
				$id_cartao = $t['Transacao']['id_cartao'];
				$tipo = $t['Transacao']['tipo'];
				$data = $t['Transacao']['data'];
				$data_pagamento = $t['Transacao']['data_pagamento'];
				$data_recebimento = $t['Transacao']['data_recebimento'];
				$valor = $t['Transacao']['valor'];
				$id_cliente = $t['Cliente']['id'];
				$nome_cliente = $t['Cliente']['nome'];

				if ($tipo == 'despesa'){
					$ret['total_despesa'] += $valor;
					if ($data_pagamento){
						$ret['total_pago'] += $valor;
					} else {
						$ret['total_pagar'] += $valor;
					}
				}
				if ($tipo == 'lucro'){
					$ret['total_lucro'] += $valor;
					if ($data_pagamento){
						$ret['total_recebido'] += $valor;
					} else {
						$ret['total_receber'] += $valor;
					}
				}
				if ($tipo == 'emprestimo'){
					$ret['total_emprestimo'] += $valor;
					if ($data_recebimento){
						$ret['emprestimos_recebido'] += $valor;
					} else {
						$ret['emprestimos_receber'] += $valor;
					}

					if (!isset($ret['emprestimos'][$id_cliente])){
						$ret['emprestimos'][$id_cliente] = [
							'id' => $id_cliente,
							'nome' => $nome_cliente,
							'total' => 0,
							'total_pago' => 0,
							'total_pagar' => 0,
							'progresso' => 0
						];
					}

					$ret['emprestimos'][$id_cliente]['total'] += $valor;
					if ($data_recebimento){
						$ret['emprestimos'][$id_cliente]['total_pago'] += $valor;
					} else {
						$ret['emprestimos'][$id_cliente]['total_pagar'] += $valor;
					}

					if ($ret['emprestimos'][$id_cliente]['total'] > 0){
						$ret['emprestimos'][$id_cliente]['progresso'] = $ret['emprestimos'][$id_cliente]['total_pago'] / $ret['emprestimos'][$id_cliente]['total'] * 100;
					}
				}
			}
		}
		$ret['transacoes'] = $transacoes;
		return $ret;
	}

	// ID usuário necessário para API
	public function listarAgrupadoCartao($id_usuario=null,$ano=null,$mes=null,$dia=null,$id_categoria=null,$cartao=null,$tipo=null,$id_cliente=null,$id_caixa=null){
		App::import('model','Cartao');
		$Cartao = new Cartao();

		$ret = array(
			'transacoes' => [],
			'cartoes' => [],
			'total_pagar' => 0,
			'total_pago' => 0,
			'total_despesa' => 0,
			'total_receber' => 0,
			'total_recebido' => 0,
			'total_lucro' => 0,
			'emprestimos_recebido' => 0,
			'emprestimos_receber' => 0,
			'total_emprestimo' => 0,
			'emprestimos' => []
		);

		$_cartoes = $this->Cartao->find('all',array(
			'fields' => array('id','descricao','dia_vencimento')
		));
		if ($_cartoes){
			foreach($_cartoes as $_cartao){
				$_id_cartao = $_cartao['Cartao']['id'];

				$ret['cartoes'][$_id_cartao] = [
					'id' => $_cartao['Cartao']['id'],
					'descricao' => $_cartao['Cartao']['descricao'],
					'dia_vencimento' => $_cartao['Cartao']['dia_vencimento'],
					'transacoes' => [],
					'total' => 0,
				];
			//
			}
		}
		// $cartoes = foreach($_cartoes as $cartao){
		//
		// }

		$transacoes = $this->get($id_usuario,$ano,$mes,$dia,$id_categoria,$cartao,$tipo,$id_cliente,$id_caixa);

		if ($transacoes){
			foreach ($transacoes as &$t){
				$id_transacao = $t['Transacao']['id'];
				$id_cartao = $t['Transacao']['id_cartao'];
				$tipo = $t['Transacao']['tipo'];
				$data = $t['Transacao']['data'];
				$data_pagamento = $t['Transacao']['data_pagamento'];
				$data_recebimento = $t['Transacao']['data_recebimento'];
				$valor = $t['Transacao']['valor'];
				$id_cliente = $t['Cliente']['id'];
				$nome_cliente = $t['Cliente']['nome'];

				if ($id_cartao){
					$ret['cartoes'][$_id_cartao]['total'] += $valor;
				}

				if ($tipo == 'despesa'){
					$ret['total_despesa'] += $valor;
					if ($data_pagamento){
						$ret['total_pago'] += $valor;
					} else {
						$ret['total_pagar'] += $valor;
					}
				}
				if ($tipo == 'lucro'){
					$ret['total_lucro'] += $valor;
					if ($data_pagamento){
						$ret['total_recebido'] += $valor;
					} else {
						$ret['total_receber'] += $valor;
					}
				}
				if ($tipo == 'emprestimo'){
					$ret['total_emprestimo'] += $valor;
					if ($data_recebimento){
						$ret['emprestimos_recebido'] += $valor;
					} else {
						$ret['emprestimos_receber'] += $valor;
					}

					if (!isset($ret['emprestimos'][$id_cliente])){
						$ret['emprestimos'][$id_cliente] = [
							'id' => $id_cliente,
							'nome' => $nome_cliente,
							'total' => 0,
							'total_pago' => 0,
							'total_pagar' => 0,
							'progresso' => 0
						];
					}

					$ret['emprestimos'][$id_cliente]['total'] += $valor;
					if ($data_recebimento){
						$ret['emprestimos'][$id_cliente]['total_pago'] += $valor;
					} else {
						$ret['emprestimos'][$id_cliente]['total_pagar'] += $valor;
					}

					if ($ret['emprestimos'][$id_cliente]['total'] > 0){
						$ret['emprestimos'][$id_cliente]['progresso'] = $ret['emprestimos'][$id_cliente]['total_pago'] / $ret['emprestimos'][$id_cliente]['total'] * 100;
					}
				}
			}
		}
		$ret['transacoes'] = $transacoes;
		return $ret;
	}

	public function listarAgrupadoDia($id_usuario=null,$ano=null,$mes=null,$dia=null,$id_categoria=null,$tipo=null,$id_cliente=null){
		// App::import('model','Produto');
		// $Produto = new Produto();

		$ret = array(
			'transacoes' => array(),
			'total_pagar' => 0,
			'total_pago' => 0,
			'total_receber' => 0,
			'total_recebido' => 0,
		);
		$arr_transacoes = array();

		$transacoes = $this->get($id_usuario,$ano,$mes,$dia,$id_categoria,$tipo,$id_cliente);

		if ($transacoes){
			foreach ($transacoes as &$t){
				$id_transacao = $t['Transacao']['id'];
				$id_cartao = $t['Transacao']['id_cartao'];
				$tipo = $t['Transacao']['tipo'];
				$data = $t['Transacao']['data'];
				$data_pagamento = $t['Transacao']['data_pagamento'];
				$valor = $t['Transacao']['valor'];

				if ($tipo == 'despesa'){
					if ($data_pagamento){
						$ret['total_pago'] += $valor;
					} else {
						$ret['total_pagar'] += $valor;
					}
				}
				if ($tipo == 'lucro'){
					if ($data_pagamento){
						$ret['total_recebido'] += $valor;
					} else {
						$ret['total_receber'] += $valor;
					}
				}

				if (!isset($arr_transacoes[$data])){
					$arr_transacoes[$data] = array(
						'total_despesa' => 0,
						'total_lucro' => 0,
						'transacoes' => array(),
						'cartoes' => array()
					);
				}

				$arr_transacoes[$data]['total_'.$tipo] += $t['Transacao']['valor'];
				// $t['Produtos'] = $Produto->getByTransacao($id_transacao);

				// Agrupa por cartão de crédito
				if ($id_cartao){
					if (!isset($arr_transacoes[$data]['cartoes'][$id_cartao])){
						$arr_transacoes[$data]['cartoes'][$id_cartao] = array(
							'id_cartao' => $id_cartao,
							'total_despesa' => 0,
							'total_lucro' => 0,
							'transacoes' => array()
						);
					}

					$arr_transacoes[$data]['cartoes'][$id_cartao]['total_'.$tipo] += $t['Transacao']['valor'];
					$arr_transacoes[$data]['cartoes'][$id_cartao]['transacoes'][] = $t;
				} else {
					$arr_transacoes[$data]['transacoes'][] = $t;
				}
			}
		}
		$ret['transacoes'] = $arr_transacoes;
		return $ret;
	}

	/*
		Função que retorna o total de cada mês, separado por categoria.
	*/
	// public function getTotalPorMes($id_usuario,$ano_i,$mes_i,$ano_f,$mes_f,$tipo='despesa'){
	public function getTotalPorMes($id_usuario,$filter=array()){
		$ano_i = isset($filter['ano_i']) ? $filter['ano_i'] : date("Y");
		$mes_i = isset($filter['mes_i']) ? $filter['mes_i'] : date("m");
		$ano_f = isset($filter['ano_f']) ? $filter['ano_f'] : date("Y");
		$mes_f = isset($filter['mes_f']) ? $filter['mes_f'] : date("m");
		$tipo = isset($filter['tipo']) ? $filter['tipo'] : "despesa";
		$id_caixa = isset($filter['id_caixa']) ? $filter['id_caixa'] : null;

		$arr_realizado = array();

		$mes_f_time = mktime(0,0,0,$mes_f,1,$ano_f);
		$dia_f = date('t', $mes_f_time);

		$data_i = "{$ano_i}-{$mes_i}-01";
		$data_f = "{$ano_f}-{$mes_f}-{$dia_f}";

		$conditions = [
			'id_categoria' => 6,
			'tipo' => $tipo,
			'status' => 'disponivel',
			'data_pagamento between ? and ?' => array($data_i,$data_f)
		];

		if ($id_caixa){
			$conditions['id_caixa'] = $id_caixa;
		}

		$results = $this->find('all',array(
			'recursive' => -1,
			'fields' => array('id_categoria','year(data_pagamento) ano','month(data_pagamento) mes','valor_pago'),
			'conditions' => $conditions,
			//'group' => array('id_categoria','left(data_pagamento,7)')
		));

// pr($results);

		if ($results){
			foreach ($results as $result){
				$id_categoria = $result['Transacao']['id_categoria'];
				$ano = $result[0]['ano'];
				$mes = $result[0]['mes'];
				$valor = $result['Transacao']['valor_pago'];

				$chave = "{$id_categoria}_{$ano}_{$mes}";

				if (!isset($arr_realizado[$chave])){
					$arr_realizado[$chave] = array('valor'=>0);
				}
				$arr_realizado[$chave]['valor'] += $valor;
			}
		}
		pr($arr_realizado);
		exit;
		return $arr_realizado;
	}

	/*
		Função que retorna o total de cada mês, separado por categoria.
	*/
	// public function getTotalPorMes($id_usuario,$ano_i,$mes_i,$ano_f,$mes_f,$tipo='despesa'){
	public function getTotalPorAno($id_usuario,$filter=array()){
		$ano_i = isset($filter['ano_i']) ? $filter['ano_i'] : date("Y");
		$ano_f = isset($filter['ano_f']) ? $filter['ano_f'] : date("Y");
		$tipo = isset($filter['tipo']) ? $filter['tipo'] : "despesa";

		$arr_realizado = array();

		$data_i = "{$ano_i}-01-01";
		$data_f = "{$ano_f}-12-31";

		$results = $this->find('all',array(
			'recursive' => -1,
			'fields' => array('id_categoria','year(data_pagamento) ano','month(data_pagamento) mes','valor_pago'),
			'conditions' => array(
				'tipo' => $tipo,
				'data_pagamento between ? and ?' => array($data_i,$data_f)
			),
			//'group' => array('id_categoria','left(data_pagamento,7)')
		));
		// pr($results);

		if ($results){
			foreach ($results as $result){
				$id_categoria = $result['Transacao']['id_categoria'];
				$ano = $result[0]['ano'];
				$valor = $result['Transacao']['valor_pago'];

				$chave = "{$id_categoria}_{$ano}";

				if (!isset($arr_realizado[$chave])){
					$arr_realizado[$chave] = array('valor'=>0);
				}
				$arr_realizado[$chave]['valor'] += $valor;
			}
		}
		return $arr_realizado;
	}

	function getTotalMensal($id_usuario,$ano){
		$categories = [];

		$results = $this->find('all',array(
			'recursive' => -1,
			'fields' => array('month(data) as mes','id_categoria', 'SUM(valor) as total','Categoria.nome'),
			'joins' => [
				[
					'table' => 'categorias',
					'alias' => 'Categoria',
					'type' => 'INNER',
					'conditions' => ['Categoria.id = Transacao.id_categoria']
				],
			],
			'conditions' => array(
				'Transacao.id_usuario' => $id_usuario,
				'Transacao.tipo' => 'despesa',
				'Transacao.status' => 'disponivel',
				'year(Transacao.data)' => $ano,
			),
			'group' => array('month(data)','id_categoria'),
			'order' => ['Categoria.nome']
		));

		$months = [1=>0,2=>0,3=>0,4=>0,5=>0,6=>0,7=>0,8=>0,9=>0,10=>0,11=>0,12=>0];
		foreach ($results as $result){
			$id_categoria = $result['Transacao']['id_categoria'];
			$nome_categoria = $result['Categoria']['nome'];
			$mes = $result[0]['mes'];
			$total = $result[0]['total'];

			// Cria array de categorias
			if (!isset($categories[$id_categoria]))	{
				$categories[$id_categoria] = [
					'nome' => $nome_categoria,
					'total' => 0,
					'months' => [1=>0,2=>0,3=>0,4=>0,5=>0,6=>0,7=>0,8=>0,9=>0,10=>0,11=>0,12=>0]
				];
			}

			$categories[$id_categoria]['months'][$mes] = $total;
			$categories[$id_categoria]['total'] += $total;

			// Cria Array de meses
			$months[$mes] += $total;
		}

		$data = ['categories'=>$categories,'months' => $months];
		// $categories = $results;

		return $data;
	}

	// Retorna array simples com todos os anos em que tem transações
	function getAnos($id_usuario){
		$_results = $this->find('all',array(
			'recursive' => -1,
			'fields' => array('year(Transacao.data) as ano'),
			'conditions' => array(
				'Transacao.id_usuario' => $id_usuario
			),
			'group' => array('year(Transacao.data)'),
			'order' => ['year(Transacao.data)']
		));

		$results = [];

		foreach ($_results as $_result){
			$results[$_result[0]['ano']] = $_result[0]['ano'];
		}

		return $results;
	}

	/*
		Salva uma transacao
		dados: Array
			id_usuario: (int) (Obrigatório) Id do usuário
			id_categoria: (Int) (Obrigatório) Id da categoria
			id_cliente: (Int) Id do cliente
			id_cartao: (Int) Id do cartão
			id_caixa: (Int) (Obrigatório) Id do caixa
			status: (enum) (Obrigatório) 'disponivel', 'cancelado'
			tipo: (enum) (Obrigatório) 'despesa','lucro'
			parcelas: (Int) Quantidade de parcelas. Valor padrão: 1
	*/
	public function inserir($dados){
		App::import('model','Categoria');
		App::import('model','Caixa');

		$Categoria = new Categoria();
		$Caixa = new Caixa();

		$ret = array(
			'status' => 0,
			'msg' => "Erro ao inserir"
		);
		$erro = false;

		if ($dados){
			if (isset($dados['Transacao']['id_usuario']) && $dados['Transacao']['id_usuario']){
				$id_usuario = $dados['Transacao']['id_usuario'];
			}

			if (isset($dados['Transacao']['tipo']) && $dados['Transacao']['tipo']){
				if (isset($dados['Transacao']['id_categoria']) && $dados['Transacao']['id_categoria']){
					// Verifica categoria
					$categoria = $Categoria->find('first',array(
						'id' => $dados['Transacao']['id_categoria'],
						'id_usuario' => $id_usuario
					));

					if ($categoria){
						if ($dados['parcelas']){
							$transacoes_salvas = array();
							$parcelas = $dados['parcelas'];
							foreach ($parcelas as $parcela){
								$transacao = array('Transacao'=>array(
									'id_usuario' => $id_usuario,
									'id_categoria' => $dados['Transacao']['id_categoria'],
									'id_cliente' => $dados['Transacao']['id_cliente'],
									'id_cartao' => $dados['Transacao']['id_cartao'],
									'id_caixa' => $dados['Transacao']['id_caixa'],
									'status' => 'disponivel',
									'tipo' => $dados['Transacao']['tipo']
								));
								if ($parcela['data']){
									$transacao['Transacao']['data'] = $parcela['data'];
									if ($parcela['valor'] && $parcela['descricao']){
										$transacao['Transacao']['valor'] = $parcela['valor'];
										if (isset($parcela['data_pagamento'])){
											$transacao['Transacao']['data_pagamento'] = $parcela['data_pagamento'];
										} else {
											$transacao['Transacao']['data_pagamento'] = null;
										}
										$transacao['Transacao']['descricao'] = $parcela['descricao'];
										$transacao['Transacao']['valor_pago'] = 0;
										if ($transacao['Transacao']['data_pagamento']){ // Se tiver data de pagamento, adiciona valor pago
											$transacao['Transacao']['valor_pago'] = $transacao['Transacao']['valor'];
										}

										$this->set($transacao);
										if ($this->validates()){
											$transacoes_to_save[] = $transacao;
											// $parcelas_validadas++;
										}
									}
								}
							}

							if (count($transacoes_to_save) == count($parcelas)){
								foreach ($transacoes_to_save as $transacao){
									$saved_transacao = $this->save($transacao);
									$this->id = null;
									if ($saved_transacao['Transacao']['tipo'] == 'despesa'){
										$Caixa->removeSaldo($saved_transacao['Transacao']['valor_pago'],$saved_transacao['Transacao']['id_caixa'],$id_usuario);
									} else {
										$Caixa->addSaldo($saved_transacao['Transacao']['valor_pago'],$saved_transacao['Transacao']['id_caixa'],$id_usuario);
									}
									$transacoes_salvas[] = $saved_transacao['Transacao']['id'];
								}
								$ret['status'] = 1;
								if ($dados['Transacao']['tipo'] == 'despesa'){
									$ret['msg'] = "Depesa salva com sucesso.";
								} else {
									$ret['msg'] = "Receita salva com sucesso.";
								}
							} else {
								$ret['msg'] = "Erro ao salvar.";
							}
						} else {

						}
					} else {
						$ret['msg'] = "Categoria inválida.";
					}
				} else {
					$ret['msg'] = "Categoria não informada.";
				}
			} else {
				$ret['msg'] = "Tipo de transação não informado/inválido.";
			}
		} else {

		}

		return $ret;
	}

	public function getParcelasFromApiPost($post){
		$parcelas = array();
		foreach ($post as $field_name=>$value){
			if (substr($field_name,0,8) == 'parcela_'){ // É uma parcela
				$_parcela = explode("_",substr($field_name,8),2);
				$num = $_parcela[0];
				$parcela_field = $_parcela[1];

				if (!isset($parcelas[$num])){
					$parcelas[$num] = array();
				}
				$parcelas[$num][$parcela_field] = $value;
			}
		}
		return $parcelas;
	}
}
