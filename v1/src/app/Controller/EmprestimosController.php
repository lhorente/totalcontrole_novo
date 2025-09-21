<?php
App::uses('AppController', 'Controller');

class EmprestimosController extends AppController {

	public $uses = array('Categoria','Caixa','Cartao','Cliente','Transacao','Usuario');
	public $helpers = array('Tempo');
	public $components = array('FlashMessage','Util');

	public function beforeFilter() {
		parent::beforeFilter();
	}

	public function index(){
		$id_usuario = $this->Auth->user('id');

		$q = $this->request->query;

		$ano = date("Y");
		$mes = date("n");
		$id_categoria = null;
		$id_cartao = null;
		$tipo = 'emprestimo';
		$id_pessoa = null;
		$id_caixa = null;

		if (isset($q['ano']) && $q['ano']){
			$ano = $q['ano'];
		}

		if (isset($q['mes']) && $q['mes']){
			$mes = $q['mes'];
		}

		$one_year = new DateInterval('P1Y');
		$one_year_ago = new DateTime();
		$one_year_ago->sub($one_year);

		// Array de meses
		$start = DateTime::createFromFormat('Y-m-d',"$ano-$mes-1");
		$start->sub(new DateInterval('P2M'));

		$end = DateTime::createFromFormat('Y-m-d',"$ano-$mes-1");
		$end->add(new DateInterval('P3M'));

		$interval = new DateInterval('P1M');
		$period = new DatePeriod($start, $interval, $end);


		$transacoes = $this->Transacao->listar($id_usuario,$ano,$mes,null,$id_categoria,$id_cartao,$tipo,$id_pessoa,$id_caixa);


		$this->set(compact('transacoes','period','ano','mes'));
	}
}
