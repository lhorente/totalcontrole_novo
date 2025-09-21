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
class CaixasController extends AppController {

/**
 * This controller does not use a model
 *
 * @var array
 */
	public $uses = array('Categoria','Caixa');
	public $components = array('FlashMessage','Util');
	// public $helpers = array('Tempo');

	public function beforeFilter() {
		parent::beforeFilter();
		// $this->Auth->allow('inserir','login','logout');
	}

	public function index(){
		$caixas = $this->Caixa->getCarteirasUsuario($this->Auth->user('id'));

		$listCaixasPai = [];
		foreach ($caixas->results as $caixa){
			$listCaixasPai[$caixa['Caixa']['id']] = $caixa['Caixa']['titulo'];
		}

		$this->set('caixas',$caixas);
		$this->set('listCaixasPai',$listCaixasPai);
	}

	public function listar(){
		$this->layout = 'ajax';
		$categorias = $this->Categoria->find('threaded',array(
			'conditions' => array(
				'id_usuario' => $this->Auth->user('id')
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
		$ret = array(
			'status'=> false
		);
		if ($this->data){
			$ret['status'] = true;
			$registro = $this->data;
			$registro['Caixa']['id_usuario'] = $this->Auth->user('id');
			if (isset($registro['Caixa']['titulo']) && $registro['Caixa']['titulo']){
				$ret = $this->Caixa->save($registro);
			}
		}
		if (isset($registro['return_type']) && $registro['return_type'] == 'json'){
			echo json_encode($ret);
		} else {
			$this->redirect('/caixas');
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

	public function trocar($id_caixa=null){
		$this->autoRender = false;
		$id_usuario = $this->Auth->user('id');
		if ($id_caixa){
			$caixa = $this->Caixa->getById($id_caixa,$id_usuario);
			if ($caixa){
				$caixa['Caixa']['saldo_seguro'] = $this->Caixa->getSaldoSeguro($id_caixa,$id_usuario);
				$this->Session->write('caixa_atual',$caixa);
			}
		}
		$this->redirect($this->referer());
	}
}
