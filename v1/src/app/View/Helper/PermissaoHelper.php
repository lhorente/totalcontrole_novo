<?php
	App::uses('AppHelper', 'View/Helper');

	class PermissaoHelper extends AppHelper {
		var $helpers = array('Session');
		
		public function temPermissao($controller,$action){
			$telas = $this->Session->read('telas');
			if (isset($telas[$controller."/".$action])){
				return true;
			} else {
				return false;
			}			
		}
		
		public function makeLink($title, $controller, $action, $id, $class='',$query_string='',$msg_confirmacao='') {
			$html = "";
			$telas = $this->Session->read('telas');
			if ($this->temPermissao($controller,$action)){
				if ($id){
					$url = Router::url(array('controller' => $controller,'url' => $action,$id));
				} else {
					$url = Router::url(array('controller' => $controller,'url' => $action));
				}
				if ($msg_confirmacao){
					$confirm = "onclick='return confirm(\"{$msg_confirmacao}\");'";
				} else {
					$confirm = "";
				}
				
				if ($query_string){
					$html = "<a href='{$url}?{$query_string}' class='{$class}' {$confirm}>{$title}</a>";
				} else {
					$html = "<a href='{$url}' class='{$class}' {$confirm}>{$title}</a>";
				}
			}
			return $html;
		}
	}
?>