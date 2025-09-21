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
class Cartao extends AppModel {
	public $useTable = 'cartoes';
    public $validate = array(
        'descricao' => array(
			'rule'    => 'notBlank',
			'message' => 'Informe uma descrição',
			'on' => 'create'
        ),
        'id_usuario' => array(
			'rule'    => 'notBlank',
			'message' => 'Erro',
			'on' => 'create'
        ),
        'dia_vencimento' => array(
			'rule'    => 'notBlank',
			'message' => 'Informe um dia de vencimento',
			'on' => 'create'
        ),
		'status' => array(
			'rule'    => 'notBlank',
			'message' => 'Erro',
			'on' => 'create'
        )
    );
	
	public function beforeFind($query){
		if (!isset($query['conditions']) || (isset($query['conditions']) && !isset($query['conditions']['Cartao.id_usuario']))){
			$user = $this->getCurrentUser();
			$query['conditions']['Cartao.id_usuario'] = $user['id'];
		}
		if (!isset($query['conditions']) || (isset($query['conditions']) && !isset($query['conditions']['Cartao.status']))){
			$user = $this->getCurrentUser();
			$query['conditions']['Cartao.status !='] = 'excluido';
		}		
		return $query;
	}
	
	public function getById($id_cartao){
		$id_usuario = CakeSession::read("Auth.User.id");
		if ($id_usuario){
			$cartao = $this->find('first',array(
				'conditions' => array(
					'Cartao.id_usuario' => $id_usuario,
					'Cartao.id' => $id_cartao
				)
			));
			return $cartao;
		}
		return false;
	}
}
