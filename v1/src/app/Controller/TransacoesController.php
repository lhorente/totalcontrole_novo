<?php
App::uses('AppController', 'Controller');

class TransacoesController extends AppController {

	public $uses = array('Categoria','Caixa','Cartao','Cliente','Produto','Servico','Transacao','TransacoesProduto','Usuario','Pagamento','Orcamento');
	public $helpers = array('Tempo');
	public $components = array('FlashMessage','Util');

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow('api_listar','api_inserir');
	}

	public function index($ano=null,$mes=null){
		if (!$ano){
			$ano = date("Y");
		}

		if (!$mes){
			if ($ano){ // Se for informado ano, não considera mês, para exibir o ano inteiro
				$mes = null;
			} else {
				$mes = date("n");
			}
		}

		$q = $this->request->query;

		$clientes = $this->Cliente->find('list',array(
			'fields' => array('id','nome')
		));

		// $list_categorias = array();
		// $categorias = $this->Categoria->find('threaded',array(
		// 	'order' => array('Categoria.nome' => 'asc')
		// ));
		// if ($categorias){
		// 	foreach ($categorias as $categoria){
		// 		$list_categorias[$categoria['Categoria']['id']] = $categoria['Categoria']['nome'];
		// 		if ($categoria['children']){
		// 			foreach ($categoria['children'] as $categoria2){
		// 				$list_categorias[$categoria2['Categoria']['id']] = $categoria['Categoria']['nome'] . " > " . $categoria2['Categoria']['nome'];
		// 			}
		// 		}
		// 	}
		// }

		$servicos = $this->Servico->find('list',array(
			'fields' => array('id','descricao')
		));
		$cartoes = $this->Cartao->find('list',array(
			'fields' => array('id','descricao')
		));

		$this->set(compact('cartoes','clientes','servicos'));
		// $this->set('categorias',$list_categorias);
	}

	public function listar($ano=null,$mes=null){
		$this->layout = 'ajax';
		$template_busca = false;

		$exportar = filter_input(INPUT_GET,'exportar');

		$id_usuario = $this->Auth->user('id');

		$q = $this->request->query;

		// if (!$ano){
		// }
		if (!$mes && !$ano){
		  // Se for informado ano, não considera mês, para exibir o ano inteiro
			$ano = date("Y");
			$mes = date("n");
		} else if ($ano && !$mes){
			$template_busca = true;
		}

		// Filtro por caixa
		$id_caixa = null;
		$caixa = $this->Session->read('caixa_atual');
		if ($caixa){
			$id_caixa = $caixa['Caixa']['id'];
		}
		// $caixa = null;
		// if (isset($q['caixa']) && $q['caixa']){
			// $id_caixa = $q['caixa'];
			// $caixa = $this->Caixa->getById($id_caixa,$id_usuario);
		// }

		// Filtro por carteira
		$id_caixa = null;
		$caixa = null;
		if (isset($q['caixa']) && $q['caixa']){
			$id_caixa = $q['caixa'];
				$caixa = $this->Caixa->getById($id_caixa,$id_usuario);
		}

		// pr($caixa);exit;

		// Filtro por cartão
		$id_cartao = null;
		$cartao = null;
		if (isset($q['cartao']) && $q['cartao']){
			$id_cartao = $q['cartao'];
			$cartao = $this->Cartao->getById($id_cartao);
		}

		// Filtro por categoria
		$id_categoria = null;
		$categoria = null;
		if (isset($q['categoria'])){
			$id_categoria = $q['categoria'];
			$categoria = $this->Categoria->getById($id_categoria);
		}

		// Filtro por pessoa
		$id_pessoa = null;
		$pessoa = null;
		if (isset($q['pessoa'])){
			$id_pessoa = $q['pessoa'];
			$pessoa = $this->Cliente->getById($id_pessoa);
		}

		$tipo = null;
		if (isset($q['tipo'])){
			$tipo = $q['tipo'];
		}

		if(!$template_busca){
			$arr_meses = $this->Util->mesesArray($mes,$ano,1);
			$link_mes_atual = $this->Util->nome_mes($mes) . " / " . $ano;
			$mes_anterior = $this->Util->mes_anterior($mes,$ano);
			$link_mes_anterior = "<a href='/transacoes/{$mes_anterior['ano']}/{$mes_anterior['mes']}'><small>" . $this->Util->nome_mes($mes_anterior['mes']) . " / {$mes_anterior['ano']}</small></a>";
			$proximo_mes = $this->Util->proximo_mes($mes,$ano);
			$link_proximo_mes = "<a href='/transacoes/{$proximo_mes['ano']}/{$proximo_mes['mes']}'><small>" . $this->Util->nome_mes($proximo_mes['mes']) . " / {$proximo_mes['ano']}</small></a>";
			$this->set(compact('arr_meses','link_mes_atual','link_mes_anterior','link_proximo_mes'));
		}

		// $usuario = $this->User->findById($id_usuario,array('saldo'));

		if (isset($q['agrupar']) && $q['agrupar'] == 'dia'){
			$transacoes = $this->Transacao->listarAgrupadoDia($id_usuario,$ano,$mes,null,$id_categoria,$tipo,$id_pessoa,$id_caixa);
		} else {
			$transacoes = $this->Transacao->listar($id_usuario,$ano,$mes,null,$id_categoria,$id_cartao,$tipo,$id_pessoa,$id_caixa);
		}
		// pr($transacoes);exit;

		$cartoes = array();

		// $saldo = $usuario['Usuario']['saldo'];
		$total = 0;
		$contas_a_pagar = 0;
		$contas_pagas = 0;
		$contas_a_receber = 0;
		$contas_recebidas = 0;

		// pr($transacoes);
		// $saldo_seguro = $saldo-$transacoes['total_pagar'];

		$categorias = $this->Categoria->find('list',array(
			'fields' => array('id','nome'),
			'conditions' => [
				'status' => 'a'
			],
			'order' => array(
				'Categoria.nome' => 'asc'
			)
		));

		$cartoes = $this->Cartao->find('all',array(
			'fields' => array('id','descricao','dia_vencimento')
		));


		$cartoesKeys = array_column(array_column($cartoes,'Cartao'),'id');
		$cartoesNames = array_column(array_column($cartoes,'Cartao'),'descricao');
		$cartoesList = array_combine($cartoesKeys,$cartoesNames);

		$pessoas = $this->Cliente->find('list',array(
			'fields' => array('id','nome'),
			'order' => ['nome']
		));

		$caixas = $this->Caixa->getCaixasUsuario($id_usuario,'list');

		$this->set(compact('caixas','caixa','cartao','cartoes','cartoesList','categoria','contas_a_pagar','contas_a_receber','pessoa','pessoas','categorias','ano','mes','q','tipo'));
		$this->set('transacoes',$transacoes);

		if ($exportar){
			$this->layout = null;
			$this->render('exportar_csv');
		}

		if($template_busca){
			$this->render('busca');
		}

		if (isset($q['agrupar']) && $q['agrupar'] == 'dia'){
			$this->render('listar_agrupado_dia');
		}
	}

	// depreciado após finalizar modal_inserir e modal_editar
	function salvar_modal($id=null){
		$this->layout = 'ajax';

		$id_usuario = $this->Auth->user('id');

		$clientes = $this->Cliente->find('list',array(
			'fields' => array('id','nome'),
			'order' => array('nome' => 'asc')
		));

		$list_categorias = $this->Categoria->find('list',array(
			'fields' => array('id','nome'),
			'conditions' => [
				'status' => 'a'
			],
			'order' => array(
				'Categoria.nome' => 'asc'
			)
		));

		$servicos = $this->Servico->comboAceitos($id_usuario);
		$cartoes = $this->Cartao->find('list',array(
			'fields' => array('id','descricao'),
			'order' => array('descricao' => 'asc')
		));
		$produtos = array();
		$caixas = $this->Caixa->getCaixasUsuario($id_usuario,'list');

		$list_caixas = array();
		$caixas = $this->Caixa->find('threaded',array(
			'conditions' => ['id_usuario' => $id_usuario],
			'order' => array('Caixa.titulo' => 'asc')
		));
		if ($caixas){
			foreach ($caixas as $caixa){
				$list_caixas[$caixa['Caixa']['id']] = $caixa['Caixa']['titulo'];
				if ($caixa['children']){
					foreach ($caixa['children'] as $caixa2){
						$list_caixas[$caixa2['Caixa']['id']] = $caixa['Caixa']['titulo'] . " > " . $caixa2['Caixa']['titulo'];
					}
				}
			}
		}

		$this->set('clientes',$clientes);
		$this->set('categorias',$list_categorias);
		$this->set('servicos',$servicos);
		$this->set('cartoes',$cartoes);
		$this->set('caixas',$caixas);
		$this->set('list_caixas',$list_caixas);
		if ($id){
			// $transacao = $this->Transacao->findById($id);
			$transacao = $this->Transacao->find('first',array(
				'conditions' => array(
					'Transacao.id' => $id,
					'Transacao.id_usuario' => $this->Auth->user('id')
				)
			));
			if ($transacao){
				// $transacao['Transacao']['valor'] = str_replace(".", ",",$transacao['Transacao']['valor']);
				$produtos = $this->TransacoesProduto->find('all',array(
					'recursive' => -1,
					'fields' => array('Produto.id','Produto.nome','TransacoesProduto.quantidade','TransacoesProduto.valor_unitario','TransacoesProduto.juros','TransacoesProduto.desconto'),
					'joins' => array(
						array(
							'alias' => 'Produto',
							'table' => 'produtos',
							'type' => 'INNER',
							'conditions' => array('TransacoesProduto.id_produto = Produto.id')
						)
					),
					'conditions' => array(
						'TransacoesProduto.id_transacao' => $transacao['Transacao']['id']
					)
				));
			}

			$this->set('transacao',$transacao);
			$this->data = $transacao;

			if ($transacao['Transacao']['tipo'] == 'transferencia'){
				$this->render('salvar_modal_transferencia');
			} else if ($transacao['Transacao']['tipo'] == 'emprestimo'){
				$this->render('salvar_modal_emprestimo');
			}

		}
		$this->set('produtos',$produtos);
		if (isset($this->request->query['tipo'])){
			$tipo = $this->request->query['tipo'];
			if ($tipo=='a_pagar' || $tipo=='a_receber' || $tipo=='compra' || $tipo=='venda' || $tipo == 'compra_para_vender'){
				$this->render('salvar_modal_'.$tipo);
			}
		}
	}

	function salvar_modal_transferencia($id=null){
		$this->layout = 'ajax';

		$caixas = $this->Caixa->getCaixasUsuario($id_usuario,'list');

		$this->set('caixas',$caixas);
		if ($id){
			$transacao = $this->Transacao->find('first',array(
				'conditions' => array(
					'Transacao.id' => $id,
					'Transacao.id_usuario' => $this->Auth->user('id')
				)
			));
			$this->set('transacao',$transacao);
			$this->data = $transacao;
		}

		if (isset($this->request->query['tipo'])){
			$tipo = $this->request->query['tipo'];
			if ($tipo=='a_pagar' || $tipo=='a_receber' || $tipo=='compra' || $tipo=='venda' || $tipo == 'compra_para_vender'){
			}
		}
		$this->render('salvar_modal_transferencia');
	}

	public function modal_inserir($tipo='despesa'){
		$this->layout = 'ajax';

		$id_usuario = $this->Auth->user('id');

		$clientes = $this->Cliente->find('list',array(
			'fields' => array('id','nome'),
			'order' => ['nome'=>'asc']
		));

		// $list_categorias = array();
		// $categorias = $this->Categoria->find('threaded',array(
		// 	'order' => array('Categoria.nome' => 'asc')
		// ));
		// if ($categorias){
		// 	foreach ($categorias as $categoria){
		// 		$list_categorias[$categoria['Categoria']['id']] = $categoria['Categoria']['nome'];
		// 		if ($categoria['children']){
		// 			foreach ($categoria['children'] as $categoria2){
		// 				$list_categorias[$categoria2['Categoria']['id']] = $categoria['Categoria']['nome'] . " > " . $categoria2['Categoria']['nome'];
		// 			}
		// 		}
		// 	}
		// }

		$list_categorias = $this->Categoria->find('list',array(
			'fields' => array('id','nome'),
			'conditions' => [
				'status' => 'a'
			],
			'order' => array(
				'Categoria.nome' => 'asc'
			)
		));

		$servicos = $this->Servico->comboAbertos($id_usuario);

		$cartoes = $this->Cartao->find('list',array(
			'fields' => array('id','descricao')
		));
		$caixas = $this->Caixa->getCaixasUsuario($id_usuario,'list');

		$this->set(compact('caixas','cartoes','clientes','servicos','tipo'));
		$this->set('categorias',$list_categorias);
	}

	public function modal_transferir(){
		$this->layout = 'ajax';

		$id_usuario = $this->Auth->user('id');

		$list_caixas = array();
		$caixas = $this->Caixa->find('threaded',array(
			'conditions' => ['id_usuario' => $id_usuario],
			'order' => array('Caixa.titulo' => 'asc')
		));
		if ($caixas){
			foreach ($caixas as $caixa){
				$list_caixas[$caixa['Caixa']['id']] = $caixa['Caixa']['titulo'];
				if ($caixa['children']){
					foreach ($caixa['children'] as $caixa2){
						$list_caixas[$caixa2['Caixa']['id']] = $caixa['Caixa']['titulo'] . " > " . $caixa2['Caixa']['titulo'];
					}
				}
			}
		}

		$this->set(compact('list_caixas'));
	}

	public function modal_emprestimo(){
		$this->layout = 'ajax';

		$id_usuario = $this->Auth->user('id');

		$caixas = $this->Caixa->getCaixasUsuario($id_usuario,'list');

		$clientes = $this->Cliente->find('list',array(
			'fields' => array('id','nome'),
			'order' => ['nome'=>'asc']
		));

		$cartoes = $this->Cartao->find('list',array(
			'fields' => array('id','descricao')
		));

		$this->set(compact('caixas','clientes','cartoes'));
	}

	public function modal_editar(){
		$this->layout = 'ajax';
		$clientes = $this->Cliente->find('list',array(
			'fields' => array('id','nome'),
			'order' => ['nome'=>'asc']
		));
		$list_categorias = array();
		$categorias = $this->Categoria->find('threaded',array(
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
		$servicos = $this->Servico->find('list',array(
			'fields' => array('id','descricao')
		));
		$cartoes = $this->Cartao->find('list',array(
			'fields' => array('id','descricao')
		));

		$this->set(compact('cartoes','clientes','servicos','tipo'));
		$this->set('categorias',$list_categorias);
	}

	public function ajax_row_parcela($qtd=1){
		$this->layout = 'ajax';
		$parcelas = array();

		if ($this->data){
			$post = $this->data;
			if ($post['parcelas']){
				$parcelas = $post['parcelas'];
				$data = false;
				$valor = null;
				$descricao = null;

				for($i=0;$i<$qtd;$i++){
					if (isset($parcelas[$i])){
						if ($parcelas[$i]['valor']){
							$valor = $parcelas[$i]['valor'];
						}
						if ($parcelas[$i]['descricao']){
							$descricao = $parcelas[$i]['descricao'];
						}
						if ($parcelas[$i]['data']){
							$data = DateTime::createFromFormat('d/m/Y',$parcelas[$i]['data']);
						} else {
							if ($data){
								$data->add(new DateInterval('P1M'));
								$parcelas[$i]['data'] = $data->format("d/m/Y");
							}
						}
					} else {
						$parcelas[$i]['valor'] = $valor;
						$parcelas[$i]['descricao'] = $descricao;
						$parcelas[$i]['data'] = null;

						if ($data){
							$data->add(new DateInterval('P1M'));
							$parcelas[$i]['data'] = $data->format("d/m/Y");
						}
					}
				}
			}
		}
		$this->set(compact('qtd','parcelas'));
	}

	public function inserir(){
		$this->autoRender = false;
		$ret = array(
			'status' => 0,
			'msg' => "Erro ao inserir"
		);
		$erro = false;

		$transacoes_to_save = array();
		if ($this->data){
			$registro = $this->data;
			if (isset($registro['Transacao']['tipo']) && $registro['Transacao']['tipo']){
				if (isset($registro['Transacao']['id_categoria']) && $registro['Transacao']['id_categoria']){
					// Verifica categoria
					$categoria = $this->Categoria->find('first',array(
						'id' => $registro['Transacao']['id_categoria'],
						'id_usuario' => $this->Auth->user('id')
					));

					// $id_caixa = null;
					// $caixa = $this->Session->read('caixa_atual');
					// if ($caixa){
						// $id_caixa = $caixa['Caixa']['id'];
					// }

					if ($categoria){
						if ($registro['parcelas']){
							$transacoes_salvas = array();
							$parcelas = $registro['parcelas'];
							foreach ($parcelas as $parcela){
								$transacao = array('Transacao'=>array(
									'id_usuario' => $this->Auth->user('id'),
									'id_categoria' => $registro['Transacao']['id_categoria'],
									'id_cliente' => $registro['Transacao']['id_cliente'],
									'id_cartao' => $registro['Transacao']['id_cartao'],
									'id_caixa' => $registro['Transacao']['id_caixa'],
									'status' => 'disponivel',
									'tipo' => $registro['Transacao']['tipo']
								));
								if ($parcela['data']){
									$transacao['Transacao']['data'] = $parcela['data'];
									if ($parcela['valor'] && $parcela['descricao']){
										$transacao['Transacao']['valor'] = $parcela['valor'];
										$transacao['Transacao']['data_pagamento'] = $parcela['data_pagamento'];
										$transacao['Transacao']['descricao'] = $parcela['descricao'];
										$transacao['Transacao']['valor_pago'] = 0;
										if ($transacao['Transacao']['data_pagamento']){ // Se tiver data de pagamento, adiciona valor pago
											$transacao['Transacao']['valor_pago'] = $transacao['Transacao']['valor'];
										}

										$this->Transacao->set($transacao);
										if ($this->Transacao->validates()){
											$transacoes_to_save[] = $transacao;
											// $parcelas_validadas++;
										}
									}
								}
							}

							if (count($transacoes_to_save) == count($parcelas)){
								foreach ($transacoes_to_save as $transacao){
									$saved_transacao = $this->Transacao->save($transacao);
									$this->Transacao->id = null;
									if ($saved_transacao['Transacao']['tipo'] == 'despesa'){
										$this->Caixa->removeSaldo($saved_transacao['Transacao']['valor_pago'],$saved_transacao['Transacao']['id_caixa']);
									} else {
										$this->Caixa->addSaldo($saved_transacao['Transacao']['valor_pago'],$saved_transacao['Transacao']['id_caixa']);
									}
									$transacoes_salvas[] = $saved_transacao['Transacao']['id'];
								}
								$ret['status'] = 1;
								if ($registro['Transacao']['tipo'] == 'despesa'){
									$ret['msg'] = "Depesa salva com sucesso.";
								} else {
									$ret['msg'] = "Receita salva com sucesso.";
								}
							} else {
								$ret['msg'] = "Erro ao salvar.";
							}
						}
					} else {
						$ret['msg'] = "Categoria inválida.";
					}
				} else {
					$ret['msg'] = "Categoria não informada.";
				}
			} else {
				$ret['msg'] = "Tipo de transação não informado/inválido.";
			}
		}
		echo json_encode($ret);
	}

	public function transferir(){
		$this->autoRender = false;
		$ret = array(
			'status' => 0,
			'msg' => "Erro ao transferir"
		);

		$id_usuario = $this->Auth->user('id');

		$erro = false;

		if ($this->data){
			$registro = $this->data;
			$id_caixa_de = $registro['Transacao']['id_caixa_de'];
			$id_caixa_para = $registro['Transacao']['id_caixa_para'];
			$data = $registro['Transacao']['data'];
			$data_pagamento = $registro['Transacao']['data_pagamento'];
			$valor = $registro['Transacao']['valor'];
			$descricao = $registro['Transacao']['descricao'];

			$caixaDe = $this->Caixa->getById($id_caixa_de,$id_usuario);
			$caixaPara = $this->Caixa->getById($id_caixa_para,$id_usuario);

			if ($caixaDe && $caixaPara){
				$descricao_de = $descricao . ("Transferido para ".$caixaPara['Caixa']['titulo']);
				$descricao_para = $descricao . ("Recebido de ".$caixaDe['Caixa']['titulo']);

				$transacao = ['Transacao' =>[
					'tipo' => 'transferencia',
					'id_usuario' => $id_usuario,
					'id_caixa' => $id_caixa_de,
					'id_caixa_para' => $id_caixa_para,
					'data' => $data,
					'data_pagamento' => $data_pagamento,
					'valor' => $valor,
					'descricao' => $descricao,
					'valor_pago' => 0
				]];

				if ($transacao['Transacao']['data_pagamento']){ // Se tiver data de pagamento, adiciona valor pago
					$transacao['Transacao']['valor_pago'] = $transacao['Transacao']['valor'];
				}

				$savedTransacao = $this->Transacao->save($transacao);
				if ($savedTransacao){
					// Remove do saldo valor da conta de origem
					$this->Caixa->removeSaldo($savedTransacao['Transacao']['valor_pago'],$savedTransacao['Transacao']['id_caixa']);

					// Adiciona ao saldo da conta de destino
					$this->Caixa->addSaldo($savedTransacao['Transacao']['valor_pago'],$savedTransacao['Transacao']['id_caixa_para']);

					$ret['status'] = 1;
					$ret['msg'] = 'Transferência realizada com sucesso.';
				}
			}
		}

		echo json_encode($ret);
	}

	public function emprestar(){
		$this->autoRender = false;
		$ret = array(
			'status' => 0,
			'msg' => "Erro ao criar emprestimo"
		);

		$id_usuario = $this->Auth->user('id');

		$erro = false;

		if ($this->data){
			$registro = $this->data;
			$id_caixa = $registro['Transacao']['id_caixa'];
			$id_cliente = $registro['Transacao']['id_cliente'];
			$id_cartao = $registro['Transacao']['id_cartao'];
			$parcelas = $registro['Transacao']['parcelas'];
			$data = $registro['Transacao']['data'];
			$valor = $registro['Transacao']['valor'];
			$descricao = $registro['Transacao']['descricao'];

			// if ($parcelas > 1){
				$months2add = $parcelas-1;

				$begin = DateTime::createFromFormat('d/m/Y',$data);
				$begin->setTime(0,0,0);

				$end = clone $begin;
				$end->add(new DateInterval("P{$months2add}M"));
				$end->setTime(23,59,59);

				$interval = new DateInterval('P1M');
				$daterange = new DatePeriod($begin, $interval ,$end);

				$data = [];

				$i = 0;

				foreach($daterange as $date){
					$i++;
					if ($parcelas > 1){
						$descricao = $registro['Transacao']['descricao'] . " ({$i}/{$parcelas})";
					}

					$transacao = ['Transacao' =>[
						'tipo' => 'emprestimo',
						'id_caixa' => $id_caixa,
						'id_usuario' => $id_usuario,
						'id_cliente' => $id_cliente,
						'id_cartao' => $id_cartao,
						'data' => $date->format("d/m/Y"),
						'valor' => $valor,
						'descricao' => $descricao,
						'valor_pago' => 0
					]];

					$savedTransacao = $this->Transacao->save($transacao);
					if ($savedTransacao){
						$ret['status'] = 1;
						$ret['msg'] = 'Transferência realizada com sucesso.';
					}
					$this->Transacao->id = null;

				}
			// }


			for ($i=1;$i<=$parcelas;$i++){
				if ($parcelas > 1){
					$descricao = $registro['Transacao']['descricao'] . " {$i} / {$parcelas}";
				}

				$transacao = ['Transacao' =>[
					'tipo' => 'emprestimo',
					'id_caixa' => $id_caixa,
					'id_usuario' => $id_usuario,
					'id_cliente' => $id_cliente,
					'id_cartao' => $id_cartao,
					'data' => $data,
					'valor' => $valor,
					'descricao' => $descricao,
					'valor_pago' => 0
				]];

				$savedTransacao = $this->Transacao->save($transacao);
				if ($savedTransacao){
					$ret['status'] = 1;
					$ret['msg'] = 'Transferência realizada com sucesso.';
				}
				$this->Transacao->id = null;
			}
		}

		echo json_encode($ret);
	}

	// Em desenvolvimento
	public function editar(){
		if ($this->data){
			$registro = $this->data;

		}
	}

	// Será descontinuada quando finalizar inserir e editar
	public function salvar(){
		$this->autoRender = false;
		$ret = array();
		if ($this->data){
			$ret['status'] = true;
			$registro = $this->data;
			$registro['Transacao']['status'] = 'disponivel';
			if (isset($registro['Transacao']['data_pagamento']) && $registro['Transacao']['data_pagamento']){ // Se estiver pago, define o valor de pagamento
				$registro['Transacao']['valor_pago'] = $registro['Transacao']['valor'];
			} else {
				$registro['Transacao']['data_pagamento'] = null;
				$registro['Transacao']['valor_pago'] = 0;
			}

			if (isset($registro['Transacao']['id']) && $registro['Transacao']['id']){
				$registro_anterior = $this->Transacao->find('first',array(
					'conditions' => array(
						'Transacao.id' => $registro['Transacao']['id'],
						'Transacao.id_usuario' => $this->Auth->user('id')
					)
				));
				if (!$registro_anterior){
					$ret['status'] = false;
					echo json_encode($ret);
					return false;
				}
			} else {
				$registro['Transacao']['id_usuario'] = $this->Auth->user('id');
			}

			if (!isset($registro['Transacao']['repetir'])){
				$registro['Transacao']['repetir'] = 0;
			}
			if ($registro['Transacao']['data']){
				$data = explode("/",$registro['Transacao']['data']);
				$dia = (int)$data[0];
				$mes = (int)$data[1];
				$ano = (int)$data[2];
				$mes_final = $mes+$registro['Transacao']['repetir'];
				$num = 1;
				$total = $registro['Transacao']['repetir']+1;
				$descricao = $registro['Transacao']['descricao'];

				for($i=$mes;$i<=$mes_final;$i++){
					$registro['Transacao']['data'] = date("d/m/Y",mktime(0,0,0,$i,$dia,$ano));
					if (isset($registro['Transacao']['id']) && $registro['Transacao']['id']){
						$registro['Transacao']['descricao'] = $descricao;
					} else {
						if ($total > 1){
							$registro['Transacao']['descricao'] = $descricao . "({$num}/{$total})";
						}
					}
					if ($this->Transacao->save($registro)){
						if (isset($registro_anterior)){
							if ($registro_anterior['Transacao']['tipo'] == 'despesa'){
								$this->Caixa->addSaldo($registro_anterior['Transacao']['valor_pago'],$registro_anterior['Transacao']['id_caixa']);
							} else if ($registro_anterior['Transacao']['tipo'] == 'lucro') {
								$this->Caixa->removeSaldo($registro_anterior['Transacao']['valor_pago'],$registro_anterior['Transacao']['id_caixa']);
							} else if ($registro_anterior['Transacao']['tipo'] == 'emprestimo'){
								if ($registro_anterior['Transacao']['data_recebimento']){ // Se o registro anterior tinha data de recebimento, remove o saldo que foi adicionado.
									$this->Caixa->removeSaldo($registro_anterior['Transacao']['valor'],$registro_anterior['Transacao']['id_caixa']);
								}
								
								if ($registro_anterior['Transacao']['data_pagamento']){ // Se o registro anterior tinha data de pagamento, devolve o saldo que foi adicionado.
									$this->Caixa->addSaldo($registro_anterior['Transacao']['valor'],$registro_anterior['Transacao']['id_caixa']);
								}
							}
						} else {
							$registro['Transacao']['id'] = $this->Transacao->getInsertId();
						}

						// Insere produtos
						$this->TransacoesProduto->deleteAll(array(
							'TransacoesProduto.id_transacao' => $registro['Transacao']['id']
						));
						if (isset($registro['TransacoesProduto']) && $registro['TransacoesProduto']){
							foreach ($registro['TransacoesProduto'] as $k=>$p){
								if ($p['id_produto'] || $p['produto']){
									$tp = array();
									if(!$p['id_produto'] && $p['produto']){
										$produto = $this->Produto->verificaExiste($p['produto']);
										if (!$produto){
											$produto = array();
											$produto['Produto']['nome'] = $p['produto'];
											$produto['Produto']['id_usuario'] = $this->Auth->user('id');
											$produto = $this->Produto->save($produto);
											if ($produto){
												$p['id_produto'] = $produto['Produto']['id'];
											}
											$this->Produto->id = null;
											$produto = null;
										} else {
											$p['id_produto'] = $produto['Produto']['id'];
										}
									}
									$tp['TransacoesProduto'] = $p;
									$tp['TransacoesProduto']['id_transacao'] = $registro['Transacao']['id'];
									$this->TransacoesProduto->save($tp);
									$this->TransacoesProduto->id = null;
								}
							}
						}
						// Fim insere produtos

						if ($registro['Transacao']['tipo'] == 'despesa'){
							$this->Caixa->removeSaldo($registro['Transacao']['valor_pago'],$registro['Transacao']['id_caixa']);
						} else if ($registro['Transacao']['tipo'] == 'lucro'){
							$this->Caixa->addSaldo($registro['Transacao']['valor_pago'],$registro['Transacao']['id_caixa']);
						} else if ($registro['Transacao']['tipo'] == 'emprestimo') {
							if ($registro['Transacao']['data_recebimento']){ // Se o registro tem data de recebimento, adiciona o saldo que foi adicionado.
								$this->Caixa->addSaldo($registro['Transacao']['valor'],$registro['Transacao']['id_caixa']);
							}
							
							if ($registro['Transacao']['data_pagamento']){ // Se o registro tem data de pagamento, remove o saldo que foi adicionado.
								$this->Caixa->removeSaldo($registro['Transacao']['valor'],$registro['Transacao']['id_caixa']);
							}
						}
						unset($registro['Transacao']['id']);
						$this->Transacao->id = null;
					} else {
						$ret['status'] = false;
						$ret['errors_msg'] = $this->FlashMessage->humanizar($this->Transacao->validationErrors);
					}
					$num++;
				}
			} else {
				$ret['errors_msg'] = "Data não informada/inválida.";
				$ret['status'] = false;
			}
		} else {
			$ret['status'] = false;
		}
		echo json_encode($ret);
	}

	public function salvarTransferencia(){
		$this->autoRender = false;
		$ret = array(
			'status' => false,
			'errors_msg' => "Erro ao editar transferência"
		);

		$id_usuario = $this->Auth->user('id');

		$erro = false;

		if ($this->data){
			$registro = $this->data;
			$id = $registro['Transacao']['id'];
			$id_caixa_de = $registro['Transacao']['id_caixa'];
			$id_caixa_para = $registro['Transacao']['id_caixa_para'];
			$data = $registro['Transacao']['data'];
			$data_pagamento = $registro['Transacao']['data_pagamento'];
			$valor = $registro['Transacao']['valor'];
			$descricao = $registro['Transacao']['descricao'];

			$caixaDe = $this->Caixa->getById($id_caixa_de,$id_usuario);
			$caixaPara = $this->Caixa->getById($id_caixa_para,$id_usuario);

			if ($caixaDe && $caixaPara){
				$registro_anterior = $this->Transacao->find('first',array(
					'conditions' => array(
						'Transacao.id' => $id,
						'Transacao.id_usuario' => $id_usuario
					)
				));
				if (!$registro_anterior){
					$ret['status'] = false;
					echo json_encode($ret);
					return false;
				}

				// Revertendo alteradções: Adiciona saldo valor da conta de origem
				$this->Caixa->addSaldo($registro_anterior['Transacao']['valor_pago'],$registro_anterior['Transacao']['id_caixa']);

				// Revertendo alteradções: Remove saldo da conta de destino
				$this->Caixa->removeSaldo($registro_anterior['Transacao']['valor_pago'],$registro_anterior['Transacao']['id_caixa_para']);

				$descricao_de = $descricao . ("Transferido para ".$caixaPara['Caixa']['titulo']);
				$descricao_para = $descricao . ("Recebido de ".$caixaDe['Caixa']['titulo']);

				$transacao = ['Transacao' =>[
					'id' => $id,
					'tipo' => 'transferencia',
					'id_usuario' => $id_usuario,
					'id_caixa' => $id_caixa_de,
					'id_caixa_para' => $id_caixa_para,
					'data' => $data,
					'data_pagamento' => $data_pagamento,
					'valor' => $valor,
					'descricao' => $descricao,
					'valor_pago' => 0
				]];

				if ($transacao['Transacao']['data_pagamento']){ // Se tiver data de pagamento, adiciona valor pago
					$transacao['Transacao']['valor_pago'] = $transacao['Transacao']['valor'];
				}

				$savedTransacao = $this->Transacao->save($transacao);
				if ($savedTransacao){
					// Remove do saldo valor da conta de origem
					$this->Caixa->removeSaldo($savedTransacao['Transacao']['valor_pago'],$savedTransacao['Transacao']['id_caixa']);

					// Adiciona ao saldo da conta de destino
					$this->Caixa->addSaldo($savedTransacao['Transacao']['valor_pago'],$savedTransacao['Transacao']['id_caixa_para']);

					$ret['status'] = true;
					$ret['msg'] = 'Transferência alterada com sucesso.';
				}
			}
		}

		echo json_encode($ret);
	}

	function pagar($id=null){
		$this->autoRender = false;
		$ret = array();
		$ret['status'] = false;
		if ($id){
			if (isset($this->request->query['data'])){
				$data_pagamento = DateTime::createFromFormat('d/m/Y',$this->request->query['data']);
				if ($data_pagamento && is_a($data_pagamento,'DateTime')){
					$registro = $this->Transacao->find('first',array(
						'fields' => array('Transacao.id','Transacao.valor','Transacao.tipo','Transacao.id_caixa','Transacao.id_caixa_para'),
						'conditions' => array(
							'Transacao.id' => $id,
							'Transacao.id_usuario' => $this->Auth->user('id')
						)
					));
					if ($registro){
						$registro['Transacao']['valor_pago'] = $registro['Transacao']['valor'];
						$registro['Transacao']['data_pagamento'] = $data_pagamento->format("Y-m-d");
						if ($this->Transacao->save($registro,false)){
							if ($registro['Transacao']['tipo'] == 'despesa'){
								$this->Caixa->removeSaldo($registro['Transacao']['valor_pago'],$registro['Transacao']['id_caixa']);
							} else if ($registro['Transacao']['tipo'] == 'lucro') {
								$this->Caixa->addSaldo($registro['Transacao']['valor_pago'],$registro['Transacao']['id_caixa']);
							} else if ($registro['Transacao']['tipo'] == 'transferencia'){
								$this->Caixa->removeSaldo($registro['Transacao']['valor_pago'],$registro['Transacao']['id_caixa']); // Remove do saldo valor da conta de origem
								$this->Caixa->addSaldo($registro['Transacao']['valor_pago'],$registro['Transacao']['id_caixa_para']); // Adiciona ao saldo da conta de destino
							}
							$ret['status'] = true;
						}
					}
				} else {
					$ret['msg'] = "Erro ao salvar.";
				}
			}
		}
		echo json_encode($ret);
	}

	public function excluir($id = null){
		$this->autoRender = false;
		$ret = array();
		$ret['status'] = false;
		if ($id){
			$registro = $this->Transacao->find('first',array(
				'fields' => array('Transacao.id','Transacao.tipo','Transacao.valor_pago','Transacao.id_caixa','Transacao.id_caixa_para'),
				'conditions' => array(
					'Transacao.id' => $id,
					'Transacao.id_usuario' => $this->Auth->user('id')
				)
			));
			if ($registro){
				$registro['Transacao']['status'] = 'cancelado';
				if ($this->Transacao->save($registro,false)){
					if ($registro['Transacao']['tipo'] == 'despesa'){
						$this->Caixa->addSaldo($registro['Transacao']['valor_pago'],$registro['Transacao']['id_caixa']);
					} else if ($registro['Transacao']['tipo'] == 'lucro') {
						$this->Caixa->removeSaldo($registro['Transacao']['valor_pago'],$registro['Transacao']['id_caixa']);
					} else if ($registro['Transacao']['tipo'] == 'transferencia'){
						$this->Caixa->addSaldo($registro['Transacao']['valor_pago'],$registro['Transacao']['id_caixa']);
						$this->Caixa->removeSaldo($registro['Transacao']['valor_pago'],$registro['Transacao']['id_caixa_para']);
					}
					$ret['status'] = true;
				}
			}
		}
		echo json_encode($ret);
	}

	function relatorio_gastos_categoria($ano=null){
		set_time_limit(0);
		if (!$ano){
			$ano = date("Y");
		}

		$conditions = array();

		$data_inicio = null;
		$data_fim = null;
		$categoria = null;

		if (isset($this->request->query['data_inicio'])){
			$data_inicio = DateTime::createFromFormat('d/m/Y',$this->request->query['data_inicio']);
		}
		if (isset($this->request->query['data_fim'])){
			$data_fim = DateTime::createFromFormat('d/m/Y',$this->request->query['data_fim']);
		}
		if (isset($this->request->query['categoria'])){
			$categoria = DateTime::createFromFormat('d/m/Y',$this->request->query['categoria']);
		}

		if ($data_inicio && $data_fim){
			$conditions['Transacao.data between ? and ?'] = array($data_inicio->format("Y-m-d"),$data_fim->format("Y-m-d").' 23:59:59');
		} else if ($data_inicio){
			$conditions['Transacao.data >='] = $data_inicio->format("Y-m-d");
		} else if ($data_fim){
			$conditions['Transacao.data <='] = $data_inicio->format("Y-m-d");
		}

		$total_gastos = array();
		$total_lucros = array();
		$table_transacoes = array();
		if ($conditions){
			$conditions['Transacao.data_pagamento !='] = '0000-00-00';
			$conditions['Categoria.id_usuario'] = $this->Auth->user('id');
			$transacoes = $this->Transacao->find('all',array(
				'conditions' => $conditions,
				'order' => array(
					'Transacao.data' => 'asc'
				)
			));
			if ($transacoes){
				foreach ($transacoes as &$transacao){
					$mes = substr($transacao['Transacao']['data'],-7);
					if (!isset($table_transacoes[$mes])){
						$table_transacoes[$mes] = array('gastos'=>0,'lucros'=>0);
					}
					if ($transacao['Transacao']['tipo'] == 'despesa'){
						$table_transacoes[$mes]['gastos'] += $transacao['Transacao']['valor'];
					} else if ($transacao['Transacao']['tipo'] == 'lucro'){
						$table_transacoes[$mes]['lucros'] += $transacao['Transacao']['valor'];
					}
				}
			}
		}

		$table_transacoes_categorias = array();
		$categorias = $this->Categoria->find("all",array(
			'conditions' => array(
				'Categoria.id_usuario' => $this->Auth->user('id')
			),
			'order' => array(
				'Categoria.nome' => 'asc'
			)
		));
		if ($categorias){
			foreach ($categorias as &$categoria){
				if (!isset($table_transacoes_categorias[$categoria['Categoria']['id']])){
					$table_transacoes_categorias[$categoria['Categoria']['id']] = array('nome'=>$categoria['Categoria']['nome']);
				}

				$conditions['Transacao.id_categoria'] = $categoria['Categoria']['id'];
				$transacoes = $this->Transacao->find('all',array(
					'conditions' => $conditions
				));
				if ($transacoes){
					foreach ($transacoes as $transacao){
						if ($transacao['Transacao']['data'] && $transacao['Transacao']['data'] != '0000-00-00'){
							$mes = substr($transacao['Transacao']['data'],-7);
							if (!isset($table_transacoes_categorias[$categoria['Categoria']['id']][$mes])){
								$table_transacoes_categorias[$categoria['Categoria']['id']][$mes] = array('despesas'=>0,'lucros'=>0);
							}

							if ($transacao['Transacao']['tipo'] == 'despesa'){
								$table_transacoes_categorias[$categoria['Categoria']['id']][$mes]['despesas'] += $transacao['Transacao']['valor'];
							} else if ($transacao['Transacao']['tipo'] == 'lucros'){
								$table_transacoes_categorias[$categoria['Categoria']['id']][$mes]['lucros'] += $transacao['Transacao']['valor'];
							}
							// $transacao['Transacao']['data'] = DateTime::createFromFormat('Y-m-d',$transacao['Transacao']['data']);
							// if ($transacao['Transacao']['data']){
								// if (!isset($gastos[$mes])){
									// $gastos[$mes] = 0;
								// }
								// $gastos[$mes] += $transacao['Transacao']['valor'];
							// }
						}
					}
				}
				// $categoria['gastos'] = $gastos;
			}
		}

		// pr($table_transacoes_categorias);
		$this->set('table_transacoes_categorias',$table_transacoes_categorias);
		$this->set('table_transacoes',$table_transacoes);
		$this->set('data_inicio',$data_inicio);
		$this->set('data_fim',$data_fim);
	}

	public function pagar_multi(){
		$this->autoRender = false;
		$ret = array(
			'status' => false,
			'msg' => 'Não foi possível confirmar pagamento das transações selecionadas. Por favor, tente novamente.'
		);
		$ret['status'] = false;
		if ($this->data && isset($this->data['transacoes_ids'])){
			$qtd_salvos = 0;
			$qtd_transacoes = count($this->data['transacoes_ids']);

			foreach ($this->data['transacoes_ids'] as $id){
				if ($id){
					if (isset($this->data['data_pagamento'])){
						$data_pagamento = DateTime::createFromFormat("d/m/Y",$this->data['data_pagamento']);
						if ($data_pagamento && is_a($data_pagamento,'DateTime')){
							$registro = $this->Transacao->find('first',array(
								'fields' => array('Transacao.id','Transacao.valor','Transacao.tipo','Transacao.data_pagamento','Transacao.id_caixa','Transacao.id_caixa_para'),
								'conditions' => array(
									'Transacao.id' => $id,
									'Transacao.id_usuario' => $this->Auth->user('id')
								)
							));
							if ($registro){
								if (!$registro['Transacao']['data_pagamento']){
									$registro['Transacao']['valor_pago'] = $registro['Transacao']['valor'];
									$registro['Transacao']['data_pagamento'] = $data_pagamento->format("d/m/Y");

									if ($this->Transacao->save($registro,false)){
										if ($registro['Transacao']['tipo'] == 'despesa'){
											$this->Caixa->removeSaldo($registro['Transacao']['valor_pago'],$registro['Transacao']['id_caixa']);
										} else if ($registro['Transacao']['tipo'] == 'lucro') {
											$this->Caixa->addSaldo($registro['Transacao']['valor_pago'],$registro['Transacao']['id_caixa']);
										} else if ($registro['Transacao']['tipo'] == 'transferencia'){
											$this->Caixa->removeSaldo($registro['Transacao']['valor_pago'],$registro['Transacao']['id_caixa']); // Remove do saldo valor da conta de origem
											$this->Caixa->addSaldo($registro['Transacao']['valor_pago'],$registro['Transacao']['id_caixa_para']); // Adiciona ao saldo da conta de destino
										}

										$this->Transacao->id = null;
										$ret['status'] = true;
										$qtd_salvos++;
									}
								}
							}
						}
					} else {
						$ret['msg'] = "Data pagamento inválida, tente novamente.";
					}
				}
			}

			if ($qtd_salvos == 0){
				$ret['msg'] = "Erro ao salvar, por favor, tente novamente.";
			} else if ($qtd_salvos == $qtd_transacoes){
				$ret['msg'] = "Salvo com sucesso.";
			} else {
				$ret['msg'] = "Não foi possível salvar todas as transações.";
			}

		} else {
			$ret['msg'] = "Nenhuma transacao selecionada.";
		}
		echo json_encode($ret);
	}

	public function excluir_multi(){
		$this->autoRender = false;
		$ret = array();
		$ret['status'] = false;
		if ($this->data && isset($this->data['transacoes_ids'])){
			foreach ($this->data['transacoes_ids'] as $id){
				if ($id){
					$registro = $this->Transacao->find('first',array(
						'fields' => array('Transacao.id','Transacao.valor','Transacao.valor_pago','Transacao.tipo','Transacao.status','Transacao.id_caixa'),
						'conditions' => array(
							'Transacao.id' => $id,
							'Transacao.id_usuario' => $this->Auth->user('id')
						)
					));
					if ($registro){
						if ($registro['Transacao']['status'] != 'cancelado'){
							$registro['Transacao']['status'] = 'cancelado';
							if ($this->Transacao->save($registro,false)){
								if ($registro['Transacao']['tipo'] == 'despesa'){
									$this->Caixa->addSaldo($registro['Transacao']['valor_pago'],$registro['Transacao']['id_caixa']);
								} else {
									$this->Caixa->removeSaldo($registro['Transacao']['valor_pago'],$registro['Transacao']['id_caixa']);
								}
								$this->Transacao->id = null;
								$ret['status'] = true;
							}
						}
					}
				}
			}
		}
		echo json_encode($ret);
	}

	public function estatisticas(){
		$id_usuario = $this->Auth->user('id');

		$categorias = $this->Categoria->getThreadedList();

		$mes = isset($this->request->query['mes']) ? $this->request->query['mes'] : null;
		$ano = isset($this->request->query['ano']) ? $this->request->query['ano'] : null;

		$ano_anterior = null;
		$mes_anterior = null;

		$anos = $this->Transacao->getAnos($id_usuario);

		if ($ano){ // Gastos mensais
			$data = $this->Transacao->getTotalMensal($id_usuario,$ano);
			$orcamento = $this->Orcamento->listarPorCategoriaAno($ano,$ano);

			$begin = new DateTime( $ano.'-01-01 00:00:00' );
			$end = new DateTime( $ano.'-12-31 23:59:59' );

			$interval = new DateInterval('P1M');
			$daterange = new DatePeriod($begin, $interval ,$end);

			$this->set(compact('ano','anos','categorias','data','daterange','orcamento'));
			$this->render('estatisticas_ano');
		}

		$ano_i = isset($this->request->query['ano_i']) ? $this->request->query['ano_i'] : date("Y")-5;
		$ano_f = isset($this->request->query['ano_f']) ? $this->request->query['ano_f'] : date("Y");

		if ($ano_i && $ano_f){ // Gastos anuais
			$begin = new DateTime( $ano_i.'-01-01 00:00:00' );
			$end = new DateTime( $ano_f.'-12-31 23:59:59' );

			$interval = new DateInterval('P1Y');
			$daterange = new DatePeriod($begin, $interval ,$end);

			$data = [];
			foreach($daterange as $date){
				$year = $date->format("Y");
				$data[$year] = $this->Transacao->getTotalPorAno($id_usuario,['ano_i' => $year,'ano_f' => $year]);
			}

			$this->set(compact('categorias','data','daterange'));
		}

		if ($mes){
			if ($mes==1){
				$mes_anterior = 12;
				$ano_anterior = $ano-1;
			} else {
				$mes_anterior = $mes-1;
				$ano_anterior = $ano;
			}

			$filter_anterior = array('ano_i' => $ano_anterior,'mes_i' => $mes_anterior,'ano_f' => $ano_anterior,'mes_f' => $mes_anterior);
			$estats_anterior = $this->Transacao->getTotalPorMes($id_usuario,$filter_anterior);

			$filter = array('ano_i' => $ano,'mes_i' => $mes,'ano_f' => $ano,'mes_f' => $mes);
			$estats = $this->Transacao->getTotalPorMes($id_usuario,$filter);
		} else {
			$ano_anterior = $ano-1;
			$filter_anterior = array('ano_i' => $ano_anterior,'ano_f' => $ano_anterior);
			$estats_anterior = $this->Transacao->getTotalPorAno($id_usuario,$filter_anterior);

			$filter = array('ano_i' => $ano,'ano_f' => $ano);
			$estats = $this->Transacao->getTotalPorAno($id_usuario,$filter);
		}

		// $this->set(compact('ano','ano_anterior','categorias','estats','estats_anterior','mes','mes_anterior','data','daterange','tipo'));
		// $this->render('estatisticas_ano');
	}

	/*
		Descrição: Próximas transacoes do usuário
		POST:
			user_access_token: Token do usuário
			ano:
			mes:
		Return:
			status:
				true: Sucesso
				false: Erro
			msg: Mensagem de retorno
			data:
				transacoes:
					id
					descricao
					data
					valor
	*/
	public function api_listar(){
		$this->autoRender = false;
		$p = $this->api_request;
		$fields = array(
			'ano' => array('type' => 'ano', 'required' => true),
			'mes' => array('type' => 'mes', 'required' => true)
		);

		$this->validaApiLogado();

		// $ano=null,$mes=null,$dia=null,$id_categoria=null,$cartao=null,$tipo=null,$id_cliente=null
		$transacoes = $this->Transacao->listar($this->api_usuario['Usuario']['id'],$p['ano'],$p['mes'],null,null,null,null);

		if ($transacoes){
			$this->api_ret['status'] = true;
			$this->api_ret['return_data'] = $transacoes;
		}

		echo json_encode($this->api_ret);exit;
	}

	/*
		Descrição: Adiciona uma transação
		POST:
			user_access_token: Token do usuário
			valor: Valor da transação no formato 4.50 (R$ 4,50)
			data: Data no formato: dd/mm/YYYY
			Descrição: Texto com descrição da transacao
		Return:
			status:
				true: Sucesso
				false: Erro
			msg: Mensagem de retorno
			id_transacao: ID da transação inserida
	*/
	public function api_inserir(){
		$this->autoRender = false;
		$p = $this->api_request;
		$this->validaApiLogado();

		$id_usuario = $this->api_usuario['Usuario']['id'];

		$fields = array(
			'transacao_id_categoria' => array('obrigatorio'=>true,'msg'=>"Categoria não preenchida/inválida."),
			'transacao_id_caixa' => array('obrigatorio'=>true,'msg'=>"Caixa não preenchida/inválida."),
			'transacao_tipo' => array('obrigatorio'=>true,'msg'=>"Tipo não preenchido/inválido."),
			'parcela_0_valor' => array('obrigatorio'=>true,'msg'=>"Valor da primeira parcela não preenchido/inválido."),
			'parcela_0_data' => array('obrigatorio'=>true,'msg'=>"Data da primeira parcela não preenchida/inválida."),
			'parcela_0_descricao' => array('obrigatorio'=>true,'msg'=>"Descrição da primeira parcela não preenchida/inválida.")
		);
		$ret_form = validar_form($fields,$p);

		if ($ret_form){
			$parcelas = $this->Transacao->getParcelasFromApiPost($p);

			$id_categoria = $p['transacao_id_categoria'];
			$id_caixa = $p['transacao_id_caixa'];
			$tipo = $p['transacao_tipo'];

			$dados = Array(
				'Transacao' => array(
					'id_usuario' => $id_usuario,
					'id_categoria' => $id_categoria,
					'id_cliente' => null,
					'id_cartao' => null,
					'id_caixa' => $id_caixa,
					'status' => 'disponivel',
					'tipo' => $tipo
				),
				'parcelas' => $parcelas
			);

			$ret = $this->Transacao->inserir($dados);
			pr($ret);exit;
		}

		$fields = array(
			'ano' => array('type' => 'ano', 'required' => true),
			'mes' => array('type' => 'mes', 'required' => true)
		);


		// $ano=null,$mes=null,$dia=null,$id_categoria=null,$cartao=null,$tipo=null,$id_cliente=null
		$transacoes = $this->Transacao->listar($this->api_usuario['Usuario']['id'],$p['ano'],$p['mes'],null,null,null,null);

		if ($transacoes){
			$this->api_ret['status'] = true;
			$this->api_ret['return_data'] = $transacoes;
		}

		echo json_encode($this->api_ret);exit;
	}
}
