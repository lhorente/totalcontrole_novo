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
class CidadesController extends AppController {

/**
 * This controller does not use a model
 *
 * @var array
 */
	public $uses = array('Cidade');
	// public $helpers = array('Tempo');

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow('autocomplete');
	}	
	
	public function autocomplete($nome = null){
		$this->autoRender = false;
		if ($nome){
			$cidades = $this->Cidade->find('all',array(
				'fields' => array('Cidade.id','Cidade.nome','Estado.sigla'),
				'conditions' => array(
					'Cidade.nome LIKE' => "{$nome}%"
				)
			));
			if ($cidades){
				$arr_cidades = array();
				foreach ($cidades as $c){
					$cidade = array(
						'id' => $c['Cidade']['id'],
						'nome' => "{$c['Cidade']['nome']} / {$c['Estado']['sigla']}"
					);
					$arr_cidades[] = $cidade;
				}
				echo json_encode(array(
					'cidades' => $arr_cidades
				));
			}
		}
		echo "";
	}
}
