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
class RelatoriosController extends AppController {

/**
 * This controller does not use a model
 *
 * @var array
 */
	public $uses = array('Categoria','Cartao','Cliente','Servico','Transacao','Usuario');
	public $components = array('FlashMessage','Util');
	public $helpers = array('Tempo');

	public function beforeFilter() {
		parent::beforeFilter();
		// $this->Auth->allow('inserir','login','logout');
	}

	public function gastos_cartao($ano=null,$mes=null){
		
	}
}
