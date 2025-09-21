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
class Orcamento extends AppModel {
	public function beforeSave($options = array()) {
		if (isset($this->data[$this->alias]['valor'])) {
			$this->data[$this->alias]['valor'] = $this->trocaVirgulaPonto($this->data[$this->alias]['valor']);
		}
		return true;
	}

	public function get($id_usuario,$ano,$mes=null,$id_categoria=null){
		$list_orcamentos = array();
		$orcamentos = $this->find('all',array(
			'fields' => array('tipo','id_categoria','ano','mes','valor'),
			'conditions' => array(
				'id_usuario' => $id_usuario,
				'ano' => $ano
			)
		));
		if ($orcamentos){
			foreach ($orcamentos as $o){
				$id_categoria = $o['Orcamento']['id_categoria'];
				$ano = $o['Orcamento']['ano'];
				$mes = $o['Orcamento']['mes'];
				$valor = $o['Orcamento']['valor'];
				$chave = "{$id_categoria}_{$ano}_{$mes}";
				$list_orcamentos[$chave] = array(
					'valor' => $valor
				);
			}
		}
		return $list_orcamentos;
	}

	public function get_periodo($id_usuario,$ano_i,$mes_i,$ano_f,$mes_f,$id_categoria=null){
		$list_orcamentos = array();
		$orcamentos = $this->find('all',array(
			'fields' => array('tipo','id_categoria','ano','mes','valor'),
			'conditions' => array(
				'id_usuario' => $id_usuario,
				'ano between ? and ?' => array($ano_i,$ano_f),
				'mes between ? and ?' => array($mes_i,$mes_f)
			)
		));

		if ($orcamentos){
			foreach ($orcamentos as $o){
				$id_categoria = $o['Orcamento']['id_categoria'];
				$ano = $o['Orcamento']['ano'];
				$mes = $o['Orcamento']['mes'];
				$valor = $o['Orcamento']['valor'];
				$chave = "{$id_categoria}_{$ano}_{$mes}";
				$list_orcamentos[$chave] = array(
					'valor' => $valor
				);
			}
		}
		return $list_orcamentos;
	}

	function listarPorCategoriaAno($start_year,$end_year){
		$id_usuario = CakeSession::read("Auth.User.id");
		$results = $this->find('all',array(
			'fields' => array('tipo','id_categoria','ano','valor'),
			'conditions' => array(
				'id_usuario' => $id_usuario,
				'ano between ? and ?' => array($start_year,$end_year)
			)
		));

		$years = [];
		$total = [];
		for($year=$start_year;$year<=$end_year;$year++){
			$years[$year] = 0;
			$total[$year] = 0;
		}

		$categories = [];
		foreach ($results as $result){
			$id_categoria = $result['Orcamento']['id_categoria'];
			$ano = $result['Orcamento']['ano'];
			$valor = $result['Orcamento']['valor'];

			// Por categoria
			if (!isset($categories[$id_categoria]))	{
				$categories[$id_categoria] = [
					'years' => $years
				];
			}

			$categories[$id_categoria]['years'][$ano] = $valor;

			$total[$ano] += $valor;
		}

		$orcamento = [
			'categories' => $categories,
			'total' => $total
		];

// pr($orcamento);

		return $orcamento;
	}

	public function get_relatorio($id_usuario,$ano_i,$mes_i,$ano_f,$mes_f,$_id_categoria=null,$id_caixa=null){
		App::import('model','Categoria');
		App::import('model','Transacao');
		$Categoria = new Categoria();
		$Transacao = new Transacao();

		$orcamentos = array();
		$orcamentos_table = array(
			'header' => array(),
			'body' => array()
		);

		$categorias = $Categoria->find('all',array(
			'order' => array('nome' => 'asc')
		));

		$filter = [
			'id_usuario' => $id_usuario,
			'id_caixa' => $id_caixa,
			'ano_i' => $ano_i,
			'mes_i' => $mes_i,
			'ano_f' => $ano_f,
			'mes_f' => $mes_f,
			'tipo' => 'despesa'
		];

		$arr_realizados = $Transacao->getTotalPorMes($id_usuario,$filter);
		$arr_orcamentos = $this->get_periodo($id_usuario,$ano_i,$mes_i,$ano_f,$mes_f);
		// pr($arr_orcamentos);exit;

		$meses = meses_periodo($ano_i,$mes_i,$ano_f,$mes_f);
		if ($meses){
			foreach ($meses as $mes){
				// $orcamentos_table['header'][] = $mes['mes'];
				foreach ($categorias as $categoria){
					$id_categoria = $categoria['Categoria']['id'];
					$chave = "{$id_categoria}_{$mes['ano']}_{$mes['mes']}";

					$valor_realizado = 0;
					if (isset($arr_realizados[$chave])){
						$valor_realizado = $arr_realizados[$chave]['valor'];
					}

					$valor_orcamento = 0;
					if (isset($arr_orcamentos[$chave])){
						$valor_orcamento = $arr_orcamentos[$chave]['valor'];
					}

					$orcamentos[$chave] = array(
						'valor_orcamento' => $valor_orcamento,
						'valor_realizado' => $valor_realizado
					);
				}
			}
			// $orcamentos_table['body'] = $orcamentos;
		}


		return $orcamentos;
	}
}
