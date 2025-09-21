<?php
App::uses('Model', 'Model');

class Tela extends Model {
	// public $actsAs = array('Tree');
	public function getMigalha($controller,$action){
		$id = $this->field('id',array('controller'=>$controller,'action'=>$action));
		$telas = $this->find('list',array(
			'fields' => array('id','parent_id')
		));
		$migalha_ids = array();
		$migalha_ids[] = $id;
		if (isset($telas[$id]) && $telas[$id] != 0){
			$id2 = $telas[$id];
			$migalha_ids[] = $id2;
			if (isset($telas[$id2]) && $telas[$id2] != 0){
				$id3 = $telas[$id2];
				$migalha_ids[] = $id3;
				if (isset($telas[$id3]) && $telas[$id3] != 0){
					$id4 = $telas[$id3];
					$migalha_ids[] = $id4;				
					if (isset($telas[$id4]) && $telas[$id4] != 0){
						$id5 = $telas[$id5];
						$migalha_ids[] = $id5;				
					}
				}
			}
		}
		if ($migalha_ids){
			$migalha_ids = array_reverse($migalha_ids);
			$migalha = $this->find('all',array(
				'fields' => array('controller','action','nome'),
				'conditions' => array(
					'id' => $migalha_ids,
					'controller !=' => '',
					'action !=' => ''
				)
			));
		}
		return $migalha;
	}
	
	function getTabs($controller,$action){
		$tela_atual = $this->find('first',array(
			'conditions' => array(
				'controller'=>$controller,
				'action'=>$action
			)
		));
		$telas = $this->find('all',array(
			'conditions' => array(
				'OR' => array(
					'parent_id' => $tela_atual['Tela']['id'],
					'id' => $tela_atual['Tela']['id']
				),
				'exibe_menu' => 1
			)
		));
		if(count($telas) == 1){
			$telas = $this->find('all',array(
				'conditions' => array(
					'OR' => array(
						'parent_id' => $tela_atual['Tela']['parent_id'],
						'id' => $tela_atual['Tela']['parent_id']
					),
					'exibe_menu' => 1
				)
			));			
		}
		// $telas[] = $tela_atual;
		return $telas;
	}
	
	public function getTela($user_id,$controller,$action){
		$tela = $this->find('first',array(
			'fields' => array('Tela.nome'),
			'joins' => array(
				array(
					'table' => 'grupos_telas',
					'alias' => 'GruposTela',
					'type' => 'INNER',
					'conditions' => array('GruposTela.id_tela = Tela.id')
				),
				array(
					'table' => 'grupos_usuarios',
					'alias' => 'GruposUsuario',
					'type' => 'INNER',
					'conditions' => array('GruposUsuario.id_grupo = GruposTela.id_grupo')
				)
			),
			'conditions' => array(
				'Tela.controller' => $controller,
				'Tela.action' => $action,
				'GruposUsuario.id_usuario' => $user_id
			),			
		));
		if (!$tela){
			$tela = $this->find('first',array(
				'fields' => array('Tela.nome'),
				'conditions' => array(
					'Tela.controller' => $controller,
					'Tela.action' => $action,
					'OR' => array(
						'Tela.exibe_deslogado' => 1,
						'Tela.exibe_para_todos' => 1
					)
				)
			));
		}
		return $tela;
	}	
}
