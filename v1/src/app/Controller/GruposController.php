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
class GruposController extends AppController {

/**
 * This controller does not use a model
 *
 * @var array
 */
	public $uses = array('Grupo','GruposTela','TelasDependencia');
	public $components = array('FlashMessage');

	public function beforeFilter() {
		parent::beforeFilter();
		// $this->Auth->allow();
	}
	
	public function index(){
		$grupos = $this->Grupo->find('all');
		
		$this->data = $this->Session->read("form_data");
		$this->Session->delete('form_data');		
		
		$this->set('grupos',$grupos);
	}
	
	public function inserir(){
		// $this->autoRender = false;
		if ($this->data){
			$registro = $this->data;
			$grupo = $this->Grupo->save($registro);
			// pr($grupo);
			if ($grupo){
				$this->Session->setFlash('Grupo salvo com sucesso.', 'flash_success');
				$this->redirect(array('controller' => 'grupos', 'action' => 'editar',$grupo['Grupo']['id']));
				$this->redirect(array('controller'=>'grupos','action'=>'index'));
			} else {
				$errors_msg = $this->FlashMessage->humanizar($this->Grupo->validationErrors);
				if ($errors_msg){
					$this->Session->setFlash($errors_msg, 'flash_error');
				} else {
					$this->Session->setFlash('Não foi possível salvar, por favor tente novamente.', 'flash_error');
				}
				$this->Session->write('form_data',$registro);
			}
		}
		// $this->redirect($this->referer());
	}
	
	public function editar($id=null){
		if ($id){
			$grupo = $this->Grupo->findById($id);
			if ($grupo){
				if ($this->data){
					$registro = $this->data;
					$grupo = $this->Grupo->save($registro);
					if ($grupo){
						$this->GruposTela->deleteAll(array('GruposTela.id_grupo' => $grupo['Grupo']['id']), false);
						if ($registro['Grupo']['telas']){
							foreach ($registro['Grupo']['telas'] as $id_tela=>$posicao){
								$tela = array("Tela"=>array());
								$tela['Tela']['id'] = $id_tela;
								$tela['Tela']['posicao'] = $posicao;
								$this->Tela->save($tela);
								$this->Tela->id = null;
							
								$grupo_tela = array('GruposTela'=>array());
								$grupo_tela['GruposTela']['id_grupo'] = $grupo['Grupo']['id'];
								$grupo_tela['GruposTela']['id_tela'] = $id_tela;
								$grupo_tela = $this->GruposTela->save($grupo_tela);
								$this->GruposTela->id = null;
								$dependencias = $this->TelasDependencia->find("list",array(
									'fields'=>array("id_tela_dependencia"),
									'conditions' => array(
										'id_tela' => $id_tela
									)
								));
								if ($dependencias){
									foreach ($dependencias as $id=>$id_tela){
										$grupo_tela = array('GruposTela'=>array());
										$grupo_tela['GruposTela']['id_grupo'] = $grupo['Grupo']['id'];
										$grupo_tela['GruposTela']['id_tela'] = $id_tela;
										$grupo_tela = $this->GruposTela->save($grupo_tela);
										$this->GruposTela->id = null;
									}
								}
							}
						}
						$this->Session->setFlash('Grupo salvo com sucesso.', 'flash_success');
						$this->redirect(array('controller'=>'grupos','action'=>'index'));
					} else {
						$errors_msg = $this->FlashMessage->humanizar($this->Grupo->validationErrors);
						if ($errors_msg){
							$this->Session->setFlash($errors_msg, 'flash_error');
						} else {
							$this->Session->setFlash('Não foi possível salvar, por favor tente novamente.', 'flash_error');
						}			
					}					
				}
				$telas = $this->Tela->find('threaded',array(
					'fields' => array('Tela.*'),
					'conditions' => array(
						'Tela.exibe_edicao_grupo' => 1,
						'Tela.exibe_para_todos' => 0,
						'Tela.status' => 'ativo'
					),
					'order' => array(
						'Tela.posicao' => 'asc',
						'Tela.id' => 'asc'
					)
				));
				// pr($telas);
				$telas_grupos = $this->GruposTela->find('list',array(
					'fields' => array('GruposTela.id_tela','GruposTela.id_tela'),
					'conditions' => array(
						'GruposTela.id_grupo' => $id
					)
				));
				// pr($telas_grupos);
				$this->data = $grupo;
				
				$this->set('grupo',$grupo);
				$this->set('telas',$telas);
				$this->set('telas_grupos',$telas_grupos);
			}
		}
	}
	
	public function excluir($id=null){
		if ($id){
			if ($this->Grupo->delete($id)){
				$this->Session->setFlash('Grupo deletada com sucesso.', 'flash_success');
			} else {
				$this->Session->setFlash('Não foi possível excluir, por favor, tente novamente.', 'flash_error');
			}
		} else {
			$this->Session->setFlash('Não foi possível excluir, por favor, tente novamente.', 'flash_error');
		}
		$this->redirect($this->referer());			
	}		
}
