<?php
App::uses('Model', 'Model','AuthComponent', 'Controller/Component');

class Categoria extends AppModel {
    public $validate = array(
        'nome' => array(
			'rule'    => 'notBlank',
			'message' => 'Nome não preenchido/inválido',
			'on' => 'create'
        )
    );

	public function getCategoriaById($id){
		$id_usuario = CakeSession::read("Auth.User.id");
		$categoria = $this->find('first',array(
			'conditions' => array(
				'id' => $id,
				'id_usuario' => $id_usuario
			)
		));
		return $categoria;
	}

	public function getThreadedList(){
		$id_usuario = CakeSession::read("Auth.User.id");
		$list_categorias = array();
		$categorias = $this->find('threaded',array(
			'conditions' => array(
				'id_usuario' => $id_usuario,
				'status' => 'a'
			),
			'order' => array('Categoria.nome' => 'asc')
		));
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
		return $list_categorias;
	}

	public function getSubCategories($id_categoria){
		$id_usuario = CakeSession::read("Auth.User.id");
		$categorias = $this->find('list',array(
			'fields' => array('id'),
			'conditions' => array(
				'parent_id' => $id_categoria,
				'id_usuario' => $id_usuario,
				'status' => 'a'
			),
			'order' => array('Categoria.nome' => 'asc')
		));
		return $categorias;
	}

	public function getById($id_categoria){
		$id_usuario = CakeSession::read("Auth.User.id");
		if ($id_usuario){
			$categoria = $this->find('first',array(
				'conditions' => array(
					'Categoria.id_usuario' => $id_usuario,
					'Categoria.id' => $id_categoria
				)
			));
			return $categoria;
		}
		return false;
	}

  function getActiveList(){
    $id_usuario = CakeSession::read("Auth.User.id");
    $results = $this->find('list',array(
			'fields' => array('id','nome'),
			'conditions' => [
				'status' => 'a'
			],
			'order' => array(
				'Categoria.nome' => 'asc'
			)
		));

    return $results;
  }
}
