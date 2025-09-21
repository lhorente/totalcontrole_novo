<?php
App::uses('AppController', 'Controller');

class OrcamentosController extends AppController {

	public $uses = array('Categoria','Orcamento');
	public $components = array('FlashMessage','Util');
	public $helpers = array('Tempo');

	public function beforeFilter() {
		parent::beforeFilter();
		// $this->Auth->allow('inserir','login','logout');
	}

	public function index($ano=null,$semestre=null){
		$q = $this->request->query;

		if (!$ano){
			$ano = date("Y");
		}
		if (!$semestre){
			$semestre = 1;
		}
		$this->set(compact('ano','semestre'));
	}

	public function listar($ano=null,$semestre=null){
		$this->layout = 'ajax';

		$q = $this->request->query;

		if (!$ano){
			$ano = date("Y");
		}
		if (!$semestre){
			$semestre = 1;
		}
		if ($semestre == 1){
			$mes_i = 1;
			$mes_f = 6;
		} else {
			$mes_i = 7;
			$mes_f = 12;
		}

		$current_year = date("Y");
		$start_year = $current_year-3;
		$end_year = $current_year+1;

		$categorias = $this->Categoria->getActiveList();

		$orcamento = $this->Orcamento->listarPorCategoriaAno($start_year,$end_year);

		// $meses = Configure::read('meses');
		// $arr_semestres = $this->Util->semestresArray($semestre,$ano,4);

		// $orcamentos = $this->Orcamento->get($this->Auth->user('id'),$ano);
		$this->set(compact('categorias','orcamento'));
	}

	public function salvar(){
		$this->autoRender = false;
		$ret = array(
			'status' => 0,
			'msg' => '',
			'data' => array(),
			'year_total' => 0
		);
		if ($this->Auth->user()){
			$user_id = $this->Auth->user('id');
			$registro = $this->data;
			if ($registro['ano'] && $registro['id_categoria']){
				$categoria = $this->Categoria->find('first',array(
					'conditions' => array(
						'id' => $registro['id_categoria'],
						'id_usuario' => $user_id
					)
				));
				if ($categoria){
					$conditions = array(
						'ano' => $registro['ano'],
						// 'mes' => $registro['mes'],
						'id_categoria' => $registro['id_categoria'],
						'id_usuario' => $user_id
					);

					if ($registro['valor']){
						$valor = $registro['valor'];
					} else {
						$valor = 0;
					}

					$orcamento = array("Orcamento"=>array(
						'valor' => str_replace('.','',$valor),
						'ano' => $registro['ano'],
						// 'mes' => $registro['mes'],
						'id_categoria' => $registro['id_categoria'],
						'id_usuario' => $user_id
					));

					$existe = $this->Orcamento->find('first',array(
						'conditions' => $conditions
					));
					if ($existe){
						$orcamento['Orcamento']['id'] = $existe['Orcamento']['id'];
					}

					$saved = $this->Orcamento->save($orcamento);
					if ($saved){
						// Busca orçamento total do ano atualizado
						$year_total = $this->Orcamento->field('sum(valor)',[
							'id_usuario' => $user_id,
							'ano' => $registro['ano']
						]);

						$ret['data'] = $saved;
						$ret['status'] = 1;
						$ret['year_total'] = $year_total;
					}
				} else {
					$ret['msg'] = "Categoria inválida";
				}
			} else {
				$ret['msg'] = 'Por favor, informe o ano e valor.';
			}
		} else {
			$ret['msg'] = "Por favor, faça login novamente.";
		}
		echo json_encode($ret);
	}

	public function relatorio(){
		$categorias = array();
		$orcamentos = array();
		$ano=null;
		$ano_i=null;
		$mes_i=null;
		$ano_f=null;
		$mes_f=null;
		$id_categoria=null;
		$id_caixa=null;
		$meses = null;

		$categorias_lista = $this->Categoria->getThreadedList();
		$caixas = $this->Caixa->find('list',[
			'fields' => ['id','titulo'],
			'conditions' => [
				'id_usuario' => $this->Auth->user('id'),
			]
		]);

		$q = $this->request->query;
		if ($q){
			if ($q['id_categoria']){
				$id_categoria = $q['id_categoria'];
			}
			if ($q['id_caixa']){
				$id_caixa = $q['id_caixa'];
			}
			if ($q['ano']){
				$ano = $q['ano'];
				$ano_i = $ano;
				$mes_i = 1;
				$ano_f = $ano;
				$mes_f = 12;
			} else {
				if (isset($q['ano_i'])){
					$ano_i = $q['ano_i'];
				}
				if (isset($q['mes_i'])){
					$mes_i = $q['mes_i'];
				}
				if (isset($q['ano_f'])){
					$ano_f = $q['ano_f'];
				}
				if (isset($q['mes_f'])){
					$mes_f = $q['mes_f'];
				}
			}

			if ($ano_i && $mes_i && $ano_f && $mes_f){
				$categorias = $this->Categoria->getThreadedList();
				$meses = meses_periodo($ano_i,$mes_i,$ano_f,$mes_f);
				// pr($meses);
				$orcamentos = $this->Orcamento->get_relatorio($this->Auth->user('id'),$ano_i,$mes_i,$ano_f,$mes_f,$id_categoria,$id_caixa);
				// pr($orcamentos);exit;
			}
		}
		$this->set(compact('categorias','categorias_lista','orcamentos','meses','ano_i','mes_i','ano_f','mes_f','ano','caixas','id_caixa'));
	}
}
