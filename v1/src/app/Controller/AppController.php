<?php
/**
 * Application level Controller
 *
 * This file is application-wide controller file. You can put all
 * application-wide controller-related methods here.
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
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

App::uses('Controller', 'Controller');

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @package		app.Controller
 * @link		http://book.cakephp.org/2.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller {
	public $api_request = array();
	public $api_ret = array(
		'status' => false,
		'msg' => '',
		'return_data' => array()
	);
	public $aplicativo = false;
	public $erro_api = false;

	public $uses = array('Aplicativo','Caixa','Tela','Usuario','User');

	public $components = array(
		'Session',
		'Auth' => array(
            'loginAction' => array('admin' => false,'controller' => 'usuarios','action' => 'login'),
            'loginRedirect' => array('controller' => 'pages', 'action' => 'home'),
            'logoutRedirect' => array('controller' => 'usuarios', 'action' => 'login'),
			'authenticate' => array(
				'Form' => array(
					'passwordHasher' => 'Blowfish',
					'userModel' => 'User',
					'fields' => array('username' => 'document', 'password' => 'password')
				)
			),
		),
		'FlashMessage',
		'Util',
		'Api'
	);

	public function getTelaAtual(){
		$tela = array();
		$controller = $this->params['controller'];
		$action = $this->params['action'];
		if ($this->Auth->user()){
			$user_id = $this->Auth->user('id');
			$tela = $this->Tela->getTela($user_id,$controller,$action);
		}
		return $tela;
	}

	public function isAuthorized($controller=null,$action=null){
		if (!$controller && !$action){
			$controller = $this->params['controller'];
			$action = $this->params['action'];
		}
		if ($this->Auth->user()){
			$user = $this->Auth->user();
			$this->Session->write('User',$user);
			$autorizado = $this->Tela->find('count',array(
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
					'GruposUsuario.id_usuario' => $user['id']
				),
			));
			if (!$autorizado){
				$autorizado = $this->Tela->find('count',array(
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
			if (!$autorizado){
				if ($controller == 'telas' && $user['email'] == 'willian.lhorente@gmail.com'){
					$autorizado = true;
				}
			}
		} else {
			$autorizado = $this->Tela->find('count',array(
				'conditions' => array(
					'Tela.controller' => $controller,
					'Tela.action' => $action,
					'Tela.exibe_deslogado' => 1
				)
			));
		}
		// Breadcrumps
		$migalha = $this->Tela->getMigalha($controller,$action);
		$this->Session->write('migalha',$migalha);

		return $autorizado;
	}

	public function validaApi(){
		$this->api_request = $this->data; // _POST
		if ($this->api_request){
			if (isset($this->api_request['token']) && $this->api_request['token']){ // Verifica se token foi informado
				$app = $this->Aplicativo->find('first',array(
					'conditions' => array(
						'token' => $this->api_request['token']
					)
				));
				if ($app){
					$this->aplicativo = $app;
				} else {
					$this->api_ret['msg'] = "Token inválido.";
					echo json_encode($this->api_ret);exit;
				}
			} else {
				$this->api_ret['msg'] = "Token não informado.";
				echo json_encode($this->api_ret);exit;
			}
		} else {
			$this->api_ret['msg'] = "Token não informado.";
			echo json_encode($this->api_ret);exit;
		}
	}

	public function validaApiLogado(){
		if (isset($this->api_request['user_access_token']) && $this->api_request['user_access_token']){ // Verifica se token do usuário foi informado
			$this->api_usuario = $this->Usuario->find('first',array(
				'fields' => array('id','nome','email'),
				'conditions' => array(
					'access_token' => $this->api_request['user_access_token']
				)
			));
			if (!$this->api_usuario){
				$this->api_ret['msg'] = "Token de acesso do usuário inválido.";
				echo json_encode($this->api_ret);exit;
			}
		} else {
			$this->api_ret['msg'] = "Token de acesso do usuário não informado.";
			echo json_encode($this->api_ret);exit;
		}
	}

	public function beforeFilter() {
		$PageTitle = '';
		$tela = $this->getTelaAtual();
		if ($tela){
			$PageTitle = $tela['Tela']['nome'];
		}
		$this->set(compact('PageTitle'));

		$NEW_APP_URL = '';
		if ($_SERVER[ 'SERVER_NAME' ] == 'd.totalcontrole.com.br'){
			$NEW_APP_URL = 'http://d-app.totalcontrole.com.br';
		} elseif ($_SERVER[ 'SERVER_NAME' ] == 'h.totalcontrole.com.br'){
			$NEW_APP_URL = 'http://h-app.totalcontrole.com.br';
		} else if ($_SERVER[ 'SERVER_NAME' ] == 'www.totalcontrole.com.br' || $_SERVER[ 'SERVER_NAME' ] == 'totalcontrole.com.br'){
			$NEW_APP_URL = 'http://app.totalcontrole.com.br';
		}

		$this->set(compact('NEW_APP_URL'));

		if (isset($this->request->params['prefix']) && $this->request->params['prefix'] == 'api'){
			$this->autoRender = false;
			$this->validaApi();
		} else {
			if ($this->Auth->user()){
				$this->set('User',$this->Auth->user());

				$menu = $this->Tela->find('threaded',array(
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
						'exibe_menu' => 1,
						'status' => 'ativo',
						'GruposUsuario.id_usuario' => $this->Auth->user("id")
					),
					'order' => array(
						'posicao' => 'asc'
					),
					'group' => array(
						'Tela.id'
					)
				));
				$arr_telas = array();
				$telas = $this->Tela->find('all',array(
					'fields' => array('Tela.id','Tela.controller','Tela.action'),
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
						'GruposUsuario.id_usuario' => $this->Auth->user("id")
					),
					'order' => array(
						'posicao' => 'asc'
					),
					'group' => array(
						'Tela.id'
					)
				));
				if ($telas){
					foreach ($telas as $tela){
						if ($tela['Tela']['controller'] && $tela['Tela']['action']){
							$arr_telas[$tela['Tela']['controller'] . "/" . $tela['Tela']['action']] = true;
						}
					}
				}

				$id_usuario = $this->Auth->user('id');

				// $saldo_total = 0;
				// $saldo_seguro = 0;

				// Pega o perfil do usuário
				$caixas = $this->Caixa->getCarteirasUsuario($id_usuario);
				$saldo_seguro = $this->Caixa->getSaldoSeguroTotal($id_usuario);
				// $caixa = $this->Session->read('caixa_atual');
				// if ($caixa){
					// $id_caixa = $caixa['Caixa']['id'];
				// } else {
					// $caixa = null;
					// if (isset($this->request->query['perfil'])){
						// $id_caixa = $this->request->query['perfil'];
						// $caixa = $this->Caixa->getById($id_caixa,$id_usuario);
					// }

					// if (!$caixa){
						// $caixa = $this->Caixa->findByIdUsuario($id_usuario);
						// $id_caixa = $caixa['Caixa']['id'];
					// }
				// }

				// $caixa = $this->Caixa->findById($id_caixa,$id_usuario);
				// $caixa['Caixa']['saldo_seguro'] = $this->Caixa->getSaldoSeguro($id_caixa,$id_usuario);

				// $this->Session->write('caixa_atual',$caixa);

				// $this->set("CAIXA_ATUAL",$caixa);
				$this->set("SALDO_SEGURO",$saldo_seguro);
				$this->set("CAIXAS",$caixas);

				$this->Session->write('telas',$arr_telas);
			} else {
				$this->set('User',array());
				$menu = array();
			}

			$this->set('Menu',$menu);
			if (!$this->isAuthorized()){
				if ($this->Auth->user()){
					throw new NotFoundException('Você não tem permissão para acessar esta página');
				} else {
					$url = $this->request->url;
					$query = http_build_query($this->request->query);
					if ($url){
						if ($query){
							$url = $url . "?" . $query;
						}
						// pr(base64_encode($url));exit;
						// $this->redirect("/login?r=".base64_encode($url));
						$this->redirect("/login");
					} else {
						$this->redirect("/login");
					}
				}
			}
		}
	}

	function stringToSlug($texto){
		/* função que gera uma texto limpo pra virar URL:
		- limpa acentos e transforma em letra normal
		- limpa cedilha e transforma em c normal, o mesmo com o ñ
		- transforma espaços em hifen (-)
		- tira caracteres invalidos
		by Micox - elmicox.blogspot.com - www.ievolutionweb.com
		*/
		//desconvertendo do padrão entitie (tipo á para á)
		$texto = trim(html_entity_decode($texto));
		//tirando os acentos
		$texto= preg_replace('![áàãâä]+!u','a',$texto);
		$texto= preg_replace('![éèêë]+!u','e',$texto);
		$texto= preg_replace('![íìîï]+!u','i',$texto);
		$texto= preg_replace('![óòõôö]+!u','o',$texto);
		$texto= preg_replace('![úùûü]+!u','u',$texto);
		// retira caracteres especiais
		$texto= preg_replace('![,:+\']+!u','',$texto);
		$texto= preg_replace('[\/]','-',$texto);
		//parte que tira o cedilha e o ñ
		$texto= preg_replace('![ç]+!u','c',$texto);
		$texto= preg_replace('![ñ]+!u','n',$texto);
		//tirando outros caracteres invalidos
		$texto= preg_replace('[^a-z0-9\-]','-',$texto);
		//tirando espaços
		$texto = str_replace(' ','-',$texto);
		//trocando duplo espaço (hifen) por 1 hifen só
		$texto = str_replace('--','-',$texto);

		return strtolower($texto);
	}
}
