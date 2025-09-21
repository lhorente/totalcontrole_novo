<?php
	function nome_mes($m){
        $mes[1] = "Janeiro";
        $mes[2] = "Fevereiro";
        $mes[3] = "Março";
        $mes[4] = "Abril";
        $mes[5] = "Maio";
        $mes[6] = "Junho";
        $mes[7] = "Julho";
        $mes[8] = "Agosto";
        $mes[9] = "Setembro";
        $mes[10] = "Outubro";
        $mes[11] = "Novembro";
        $mes[12] = "Dezembro";
		$m = (int)$m;
        return $mes[$m];
    }

	function proximo_mes($mes,$ano){
		if ($mes == 12){
			$arr['ano'] = $ano + 1;
			$arr['mes'] = 1;
		} else {
			$arr['ano'] = $ano;
			$arr['mes'] = $mes + 1;
		}
		$arr['mes_nome'] = nome_mes($arr['mes']);
		return $arr;
	}
	
	function mes_anterior($mes,$ano){
		if ($mes == 1){
			$arr['ano'] = $ano - 1;
			$arr['mes'] = 12;
		} else {
			$arr['ano'] = $ano;
			$arr['mes'] = $mes - 1;
		}
		$arr['mes_nome'] = nome_mes($arr['mes']);
		return $arr;
	}
	
	function meses_periodo($ano_i,$mes_i,$ano_f,$mes_f){
		$arr_meses = array();
		$ano = $ano_i;
		$mes = $mes_i;
		$i = $ano_i . "_" . $mes_i;
		$f = $ano_f . "_" . $mes_f;
		while($i != $f){
			$nome = nome_mes($mes);
			$arr_meses[] = array('ano'=>$ano,'mes'=>$mes,'nome_mes'=>$nome);
			$proximo_mes = proximo_mes($mes,$ano);
			$ano = $proximo_mes['ano'];
			$mes = $proximo_mes['mes'];
			$i = $ano . "_" . $mes;
		}
		$nome = nome_mes($mes_f);
		$arr_meses[] = array('ano'=>$ano_f,'mes'=>$mes_f,'nome_mes'=>$nome);
		return $arr_meses;
	}
	
	function validar_form($fields,$post){
		$ret = array(
			'fields_errors' => array(),
			'status' => true
		);
		
		if ($fields){
			foreach ($fields as $field_name => $field){
				if (isset($field['obrigatorio']) && $field['obrigatorio']){
					if (!(isset($post[$field_name]) && $post[$field_name])){
						$ret['status'] = false;
						$ret['fields_errors'][$field_name] = array('msg' => $field['msg']);
					}
				}
			}
		}
		
		return $ret;
	}
?>