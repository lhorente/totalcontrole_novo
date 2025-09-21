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
class Servico extends AppModel {
    public $validate = array(
        'data_pedido' => array(
			'rule'    => 'notBlank',
			'message' => 'Informe um valor',
			'on' => 'create'
        ),
        'id_cliente' => array(
			'rule'    => 'notBlank',
			'message' => 'Informe um valor',
			'on' => 'create'
        )		
    );
	
    public $belongsTo = array(
        'Cliente' => array(
            'className' => 'Cliente',
            'foreignKey' => 'id_cliente'
        )
    );
	
	public function beforeFind($query){
		if (!isset($query['conditions']) || (isset($query['conditions']) && !isset($query['conditions']['Servico.id_usuario']))){
			$user = $this->getCurrentUser();
			$query['conditions']['Servico.id_usuario'] = $user['id'];
		}
		return $query;
	}	
	
	public function afterFind($results, $primary = false) {
		foreach ($results as $key => $val) {
			if (isset($val['Servico']['data_pedido'])){
				if ($val['Servico']['data_pedido'] == '0000-00-00'){
					$results[$key]['Servico']['data_pedido'] = false;
				} else {
					$results[$key]['Servico']['data_pedido'] = date('d/m/Y',strtotime($results[$key]['Servico']['data_pedido']));
				}
			}	
			if (isset($val['Servico']['data_aprovacao'])){
				$results[$key]['Servico']['data_aprovacao'] = date('d/m/Y',strtotime($results[$key]['Servico']['data_aprovacao']));
			}
			if (isset($val['Servico']['data_inicio'])){
				if ($val['Servico']['data_inicio'] == '0000-00-00'){
					$results[$key]['Servico']['data_inicio'] = false;
				} else {
					$results[$key]['Servico']['data_inicio'] = date('d/m/Y',strtotime($results[$key]['Servico']['data_inicio']));
				}
			}
			if (isset($val['Servico']['data_fim'])){
				if ($val['Servico']['data_fim'] == '0000-00-00'){
					$results[$key]['Servico']['data_fim'] = false;
				} else {
					$results[$key]['Servico']['data_fim'] = date('d/m/Y',strtotime($results[$key]['Servico']['data_fim']));
				}
			}			
			if (isset($val['Servico']['valor'])){
				$results[$key]['Servico']['valor_formatado'] = 'R$ '. number_format($results[$key]['Servico']['valor'],2,',','.');
			}		
		}
		return $results;
	}
	
	public function beforeSave($options = array()) {
		if (isset($this->data[$this->alias]['data_pedido']) && $this->data[$this->alias]['data_pedido']) {
			$date = DateTime::createFromFormat('d/m/Y', $this->data[$this->alias]['data_pedido']);
			$this->data[$this->alias]['data_pedido'] = $date->format('Y-m-d');
		}	
		if (isset($this->data[$this->alias]['data_aprovacao']) && $this->data[$this->alias]['data_aprovacao']) {
			$date = DateTime::createFromFormat('d/m/Y', $this->data[$this->alias]['data_aprovacao']);
			$this->data[$this->alias]['data_aprovacao'] = $date->format('Y-m-d');
		}
		if (isset($this->data[$this->alias]['data_inicio']) && $this->data[$this->alias]['data_inicio']) {
			$date = DateTime::createFromFormat('d/m/Y', $this->data[$this->alias]['data_inicio']);
			$this->data[$this->alias]['data_inicio'] = $date->format('Y-m-d');
		}
		if (isset($this->data[$this->alias]['data_fim']) && $this->data[$this->alias]['data_fim']) {
			$date = DateTime::createFromFormat('d/m/Y', $this->data[$this->alias]['data_fim']);
			$this->data[$this->alias]['data_fim'] = $date->format('Y-m-d');
		}		
		if (isset($this->data[$this->alias]['valor'])) {
			$this->data[$this->alias]['valor'] = $this->trocaVirgulaPonto($this->data[$this->alias]['valor']);
		}	
		return true;
	}
	
	/**
		* Lista os serviços cadastrados de um usuário.
		*
		* @param int $id_usuario Id do usuário
		* @param array $params Array de parâmetros de filtro e ordenação
		* 
	*/
	public function listar($id_usuario,$params){
		$return_type = isset($params['return_type']) ? $params['return_type'] : 'all';
		$status = isset($params['status']) ? $params['status'] : null;
		
		$conditions = array();
		if ($status){
			$conditions['Servico.status'] = $status;
		}
		
		$fields = array();
		if ($return_type == 'list'){
			$fields = array('Servico.id','Servico.descricao');
		}
		
		$servicos = $this->find($return_type,array(
			'fields' => $fields,
			'conditions' => $conditions,
			'order' => array('descricao' => 'asc')
		));
		
		return $servicos;
	}
	
	public function listarAbertos($id_usuario){
		$params = array(
			'return_type' => 'all',
			'status' => array('pendente','desenvolvimento')
		);
		return $this->listar($id_usuario,$params);
	}
	
	public function comboAbertos($id_usuario){
		$params = array(
			'return_type' => 'list',
			'status' => array('pendente','desenvolvimento')
		);
		return $this->listar($id_usuario,$params);
	}
	
	public function comboAceitos($id_usuario){
		$params = array(
			'return_type' => 'list',
			'status' => array('pendente','aprovado','desenvolvimento','finalizado')
		);
		return $this->listar($id_usuario,$params);
	}	
}
