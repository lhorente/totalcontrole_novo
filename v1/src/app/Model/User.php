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
class User extends AppModel {
    public $validate = array(
      'name' => array(
        'minLength' => array(
          'rule'    => array('minLength', '3'),
          'message' => 'O nome deve ter no mínimo 3 caracteres'
        )
      ),
      'email' => array(
        'rule'    => array('verificaEmailExiste'),
        'message' => 'Este email já está cadastrado em nossa base.',
        'on' => 'create'
      ),
      'password' => array(
        'required' => true,
        'rule'    => array('minLength', '6'),
        'message' => 'A senha deve ter no mínimo 6 caracteres.',
        'on' => 'create'
      ),
      'password_confirm' => array(
        'equaltofield' => array(
          'rule' => array('equaltofield','password'),
          'message' => 'Você deve digitar a mesma senha nos dois campos.',
          'on' => 'create',
        )
      )
    );

	public function beforeSave($options = array()) {
		if (isset($this->data[$this->alias]['password']) && $this->data[$this->alias]['password']) {
			$this->data[$this->alias]['password'] = AuthComponent::password($this->data[$this->alias]['password']);
		}
		return true;
	}

    public function verificaEmailExiste($check) {
        // $check will have value: array('email' => 'some-value')
        $email_existe = $this->find('count', array(
            'conditions' => $check,
            'recursive' => -1
        ));
        return !$email_existe;
    }

	function equaltofield($check,$otherfield){
		$fname = '';
		foreach ($check as $key => $value){
			$fname = $key;
			break;
		}
		return $this->data[$this->name][$otherfield] === $this->data[$this->name][$fname];
	}

	function generateAccessToken($id){
		$token = md5(uniqid()).$id;
		if ($this->save(array('Usuario'=>array('id'=>$id,'access_token' => $token)))){
			$this->id = null;
			return $token;
		} else {
			return null;
		}
	}
}
