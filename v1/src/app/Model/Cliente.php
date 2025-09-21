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
class Cliente extends AppModel {
    public $validate = array(
        'nome' => array(
            'minLength' => array(
                'rule'    => array('minLength', '3'),
                'message' => 'A descrição deve ter no mínimo 3 caracteres',
				'on' => 'create'
            )
        )
    );
	
	public function beforeFind($query){
		if (!isset($query['conditions']) || (isset($query['conditions']) && !isset($query['conditions']['id_usuario']))){
			$user = $this->getCurrentUser();
			$query['conditions']['id_usuario'] = $user['id'];
		}
		return $query;
	}
	
	public function getById($id_cliente){
		$id_usuario = CakeSession::read("Auth.User.id");
		if ($id_usuario){
			$cliente = $this->find('first',array(
				'conditions' => array(
					'Cliente.id_usuario' => $id_usuario,
					'Cliente.id' => $id_cliente
				)
			));
			return $cliente;
		}
		return false;
	}	
}
