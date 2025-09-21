<?php

App::uses('Model', 'Model','AuthComponent', 'Controller/Component');

class Caixa extends AppModel {
	public function beforeFind($query){
		// Na consulta padrão, não traz carteiras deletadas
		if (!(isset($query['conditions']) && isset($query['conditions']['Caixa.deleted_at']))){
			$query['conditions']['Caixa.deleted_at'] = null;
		}
		return $query;
	}

	function getById($id_caixa,$id_usuario){
		$caixa = $this->find('first',array(
			'conditions' => array(
				'id' => $id_caixa,
				'id_usuario' => $id_usuario
			)
		));

		return $caixa;
	}

	function getCaixasUsuario($id_usuario,$return_type='all'){
		if ($return_type == 'list'){
			$fields = array('id','titulo');
		} else {
			$fields = array('id','titulo','saldo','exibir_no_saldo');
		}
		$caixas = $this->find($return_type,array(
			'fields' => $fields,
			'conditions' => array(
				'id_usuario' => $id_usuario
			)
		));
		return $caixas;
	}

	function getCarteirasUsuario($id_usuario){
		App::import('model','Transacao');
		$Transacao = new Transacao();

		$caixas = $this->find('threaded',array(
			'fields' => array('id','titulo','exibir_no_saldo','saldo','parent_id'),
			'conditions' => array(
				'id_usuario' => $id_usuario
			),
			'order' => array(
				'Caixa.titulo' => 'asc'
			)
		));

		$_caixas = new stdClass;
		$_caixas->saldo_liquido = 0;
		$_caixas->saldo_reserva = 0;

		if ($caixas){
			foreach ($caixas as $i=>$caixa){
				$fake_child = $caixa;
				unset($fake_child['children']);
				// Adiciona caixa pai como filha para facilitar leitura dos dados pelo usuário
				$caixas[$i]['children'][] = $fake_child;

				$caixas[$i]['Caixa']['saldo_liquido'] = 0;
				$caixas[$i]['Caixa']['saldo_reserva'] = 0;

				if ($caixas[$i]['children']){
					foreach ($caixas[$i]['children'] as $child){
						if ($child['Caixa']['exibir_no_saldo']){
							$caixas[$i]['Caixa']['saldo_liquido'] += $child['Caixa']['saldo'];
						} else {
							$caixas[$i]['Caixa']['saldo_reserva'] += $child['Caixa']['saldo'];
						}

					}
				}

				$_caixas->saldo_liquido += $caixas[$i]['Caixa']['saldo_liquido'];
				$_caixas->saldo_reserva += $caixas[$i]['Caixa']['saldo_reserva'];
			}
		}

		$_caixas->saldo_seguro = $_caixas->saldo_liquido;

		$ano = date("Y");
		$mes = date("m");
		$transacoes = $Transacao->listar($id_usuario,$ano,$mes,null,null,null,'despesa',null,null);
		if ($transacoes){
			$_caixas->saldo_seguro = $_caixas->saldo_seguro-$transacoes['total_pagar'];
		} else {
			$_caixas->saldo_seguro = $saldo;
		}

		$_caixas->results = $caixas;

		return $_caixas;
	}

	function getCarteirasUsuarioDep($id_usuario,$return_type='all'){
		if ($return_type == 'list'){
			$fields = array('id','titulo');
		} else {
			$fields = array('id','titulo','saldo','exibir_no_saldo');
		}
		$caixas = $this->find($return_type,array(
			'fields' => $fields,
			'conditions' => array(
				'id_usuario' => $id_usuario,
				'parent_id' => null
			)
		));
		return $caixas;
	}

	function getSaldo($id_usuario){
		$saldo = 0;
		$caixas = $this->getCaixasUsuario($id_usuario);
		if ($caixas){
			foreach ($caixas as $caixa){
				if ($caixa["Caixa"]["exibir_no_saldo"]){
					$saldo += $caixa['Caixa']['saldo'];
				}
			}
		}
		return $saldo;
	}

	function getSaldoSeguro($id_caixa,$id_usuario){
		App::import('model','Transacao');
		$Transacao = new Transacao();

		if ($id_usuario && $id_caixa){
			$caixa = $this->getById($id_caixa,$id_usuario);
			if ($caixa){
				$saldo = $caixa['Caixa']['saldo'];

				$ano = date("Y");
				$mes = date("m");
				// $transacoes = $Transacao->listar($id_usuario,$ano,$mes);
				$transacoes = $Transacao->listar($id_usuario,$ano,$mes,null,null,null,'despesa',null,$id_caixa);
				if ($transacoes){
					$saldo_seguro = $saldo-$transacoes['total_pagar'];
				} else {
					$saldo_seguro = $saldo;
				}

				return $saldo_seguro;
			}
		}
		return 0;
	}

	function getSaldoSeguroTotal($id_usuario){
		App::import('model','Transacao');
		App::import('model','Caixa');
		$Transacao = new Transacao();
		$Caixa = new Caixa();

		$saldo_seguro = 0;

		if ($id_usuario){
			$caixas = $Caixa->getCarteirasUsuario($id_usuario);
			if ($caixas->results){
				foreach ($caixas->results as $caixa){
					if ($caixa['Caixa']['exibir_no_saldo']){
						$saldo_seguro += $caixa['Caixa']['saldo'];
					}
				}
			}

			$ano = date("Y");
			$mes = date("m");
			$transacoes = $Transacao->listar($id_usuario,$ano,$mes,null,null,null,'despesa',null,null);
			if ($transacoes){
				$saldo_seguro = $saldo_seguro-$transacoes['total_pagar'];
			} else {
				$saldo_seguro = $saldo;
			}

			return $saldo_seguro;
		}
		return 0;
	}

	function removeSaldo($valor,$id_caixa,$id_usuario=null){
		if (!$id_usuario){
			$id_usuario = CakeSession::read("Auth.User.id");
		}

		$caixa = $this->getById($id_caixa,$id_usuario);
		if ($caixa){
			$caixa['Caixa']['saldo'] -= $this->trocaVirgulaPonto($valor);
			if ($this->save($caixa)){
				$caixa['Caixa']['saldo_seguro'] = $this->getSaldoSeguro($id_caixa,$id_usuario);
				// $saldo = $this->getSaldo($id_usuario);
				CakeSession::write("caixa_atual",$caixa);
				return true;
			}
		}

		return false;
	}


	function addSaldo($valor,$id_caixa,$id_usuario=null){
		if (!$id_usuario){
			$id_usuario = CakeSession::read("Auth.User.id");
		}
		$usuario = $this->findById($id_usuario,array('id','saldo'));
		// $usuario['Usuario']['saldo'] += $this->trocaVirgulaPonto($valor);
		$caixa = $this->getById($id_caixa,$id_usuario);
		if ($caixa){
			$caixa['Caixa']['saldo'] += $this->trocaVirgulaPonto($valor);
			if ($this->save($caixa)){
				$caixa['Caixa']['saldo_seguro'] = $this->getSaldoSeguro($id_caixa,$id_usuario);
				// $saldo = $this->getSaldo($id_usuario);
				CakeSession::write("caixa_atual",$caixa);
				return true;
			}
		}

		return false;
	}
}
