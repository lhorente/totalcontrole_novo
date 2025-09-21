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
class CategoriasController extends AppController {

/**
 * This controller does not use a model
 *
 * @var array
 */
	public $uses = array('Categoria');
	public $components = array('FlashMessage','Util');
	// public $helpers = array('Tempo');

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow('api_list');
	}

	public function index(){
		$categorias = $this->Categoria->find('list',array(
			'fields' => array('Categoria.id','Categoria.nome'),
			'conditions' => array(
				'id_usuario' => $this->Auth->user('id'),
				'parent_id' => null,
				'status' => 'a'
			),
			'order' => array(
				'Categoria.nome' => 'asc'
			)
		));
		$this->set('categorias',$categorias);
	}
	
	public function listar(){
		$this->layout = 'ajax';
		$categorias = $this->Categoria->find('threaded',array(
			'conditions' => array(
				'id_usuario' => $this->Auth->user('id'),
				'status' => 'a'
			),
			'order' => array(
				'Categoria.nome' => 'asc'
			)
		));
		// pr($categorias);
		$this->set('categorias',$categorias);
	}	
	
	public function salvar(){
		$this->autoRender = false;
		$ret = array();
		if ($this->data){
			$ret['status'] = true;
			$registro = $this->data;
			if (isset($registro['Categoria']['id']) && $registro['Categoria']['id']){
				// Verifica se categoria é do usuário
				$categoria = $this->Categoria->find('count',array(
					'conditions' => array(
						'Categoria.id' => $registro['Categoria']['id'],
						'Categoria.id_usuario' => $this->Auth->user('id')
					)
				));
				if (!$categoria){
					unset($registro['Categoria']['id']);
				}

				if ($registro['Categoria']['parent_id']){
					// verifica se categoria pai é do mesmo usuário
					$categoria = $this->Categoria->find('count',array(
						'conditions' => array(
							'Categoria.id' => $registro['Categoria']['parent_id'],
							'Categoria.id_usuario' => $this->Auth->user('id')
						)
					));
					if (!$categoria){
						unset($registro['Categoria']['parent_id']);
					}
				}
			}
			$registro['Categoria']['id_usuario'] = $this->Auth->User('id');
			if (!$this->Categoria->save($registro)){
				if (isset($registro['return_type']) && $registro['return_type'] == 'json'){
					$ret['status'] = false;
					$ret['errors_msg'] = $this->FlashMessage->humanizar($this->Categoria->validationErrors);			
				} else {
					$errors_msg = $this->FlashMessage->humanizar($this->Categoria->validationErrors);
					if ($errors_msg){
						$this->Session->setFlash($errors_msg, 'flash_error');
					} else {
						$this->Session->setFlash('Não foi possível salvar, por favor tente novamente.', 'flash_error');
					}
					$this->Session->write('form_data',$registro);					
				}
			}
		}
		if (isset($registro['return_type']) && $registro['return_type'] == 'json'){
			echo json_encode($ret);
		} else {
			$this->redirect('/categorias');
		}
	}
	
	public function editar($id=null){
		if ($id){
			$categoria = $this->Categoria->find('first',array(
				'conditions' => array(
					'Categoria.id' => $id,
					'Categoria.id_usuario' => $this->Auth->user('id')
				)
			));
			if (!$categoria){
				
			}
			$categorias = $this->Categoria->find('list',array(
				'fields' => array('Categoria.id','Categoria.nome'),
				'conditions' => array(
					'id_usuario' => $this->Auth->user('id'),
					'parent_id' => null
				),
				'order' => array(
					'Categoria.nome' => 'asc'
				)
			));
			$this->set('categorias',$categorias);			
			$this->data = $categoria;
			$this->set('categoria',$categoria);
		}
	}
	
	public function excluir($id=null){
		if ($id){
			$categoria = $this->Categoria->getCategoriaById($id);
			// pr($categoria);exit;
			if ($categoria){
				$categoria['Categoria']['status'] = 'i';
				$this->Categoria->save($categoria);
				$this->Categoria->id = null;
				// pr($this->FlashMessage->humanizar($this->Categoria->validationErrors));
				// exit;
				// $this->Categoria->id = $id;
				// $this->Categoria->saveField('status','i');
			}
		}
		$this->redirect('/categorias');
	}
	
	public function combo($tipo='despesa'){
		$this->layout = 'ajax';
		if ($tipo == 'despesa'){
			$tipos = array('despesa','despesa_lucro');
		} else if ($tipo == 'lucro'){
			$tipos = array('lucro','despesa_lucro');
		} else {
			$tipos = array('despesa_lucro');
		}
		$categorias = $this->Categoria->find('threaded',array(
			'order' => array('Categoria.nome' => 'asc'),
			'conditions' => array(
				'id_usuario' => $this->Auth->user('id'),
				'tipo' => $tipos
			)
		));
		$list_categorias = array();
		if ($categorias){
			foreach ($categorias as $categoria){
				$list_categorias[$categoria['Categoria']['id']] = $categoria['Categoria']['nome'];
				if ($categoria['children']){
					foreach ($categoria['children'] as $categoria2){
						$list_categorias[$categoria2['Categoria']['id']] = $categoria['Categoria']['nome'] . " > " . $categoria2['Categoria']['nome'];
					}
				}
			}
		}
		$this->set('categorias',$list_categorias);
	}
	
	public function api_list(){
		$categorias = $this->Categoria->find('all',array(
			'fields' => array('Categoria.id','Categoria.nome'),
			'conditions' => array(
				'parent_id' => null
			),
			'order' => array(
				'Categoria.nome' => 'asc'
			)
		));
		echo json_encode($categorias);
	}
}
