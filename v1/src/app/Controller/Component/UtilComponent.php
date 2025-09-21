<?php
class UtilComponent extends Component {
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
		$arr['mes_nome'] = $this->nome_mes($arr['mes']);
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
		$arr['mes_nome'] = $this->nome_mes($arr['mes']);
		return $arr;
	}
	
	public function mesesArray($mes_atual,$ano_atual,$quantidade){
		$arr_meses = array();
		$mes = $mes_atual;
		$ano = $ano_atual;
		for ($i=0;$i<$quantidade;$i++){
			$mes_anteiror = $this->mes_anterior($mes,$ano);
			$mes_anteiror['selected'] = false;
			$arr_meses[] = $mes_anteiror;
			$mes = $mes_anteiror['mes'];
			$ano = $mes_anteiror['ano'];
		}
		$arr_meses = array_reverse($arr_meses);

		$arr_meses[] = array(
			'mes' => $mes_atual,
			'ano' => $ano_atual,
			'mes_nome' => $this->nome_mes($mes_atual),
			'selected' => true
		);
		$mes = $mes_atual;
		$ano = $ano_atual;		
		for ($i=0;$i<$quantidade;$i++){
			$proximo_mes = $this->proximo_mes($mes,$ano);
			$proximo_mes['selected'] = false;
			$arr_meses[] = $proximo_mes;
			$mes = $proximo_mes['mes'];
			$ano = $proximo_mes['ano'];
		}		
		return $arr_meses;
	}
	
    function nome_semestre($semestre){
        $nome[1] = "Jan-Jun";
        $nome[2] = "Jul-Dez";
		$semestre = (int)$semestre;
        return $nome[$semestre];
    }	
	
	function proximo_semestre($semestre,$ano){
		if ($semestre == 2){
			$arr['ano'] = $ano + 1;
			$arr['semestre'] = 1;
		} else {
			$arr['ano'] = $ano;
			$arr['semestre'] = $semestre + 1;
		}
		$arr['semestre_nome'] = $this->nome_semestre($arr['semestre']);
		return $arr;
	}
	
	function semestre_anterior($semestre,$ano){
		if ($semestre == 1){
			$arr['ano'] = $ano - 1;
			$arr['semestre'] = 2;
		} else {
			$arr['ano'] = $ano;
			$arr['semestre'] = $semestre - 1;
		}
		$arr['semestre_nome'] = $this->nome_semestre($arr['semestre']);
		return $arr;
	}	
	
	public function semestresArray($semestre_atual,$ano_atual,$quantidade){
		$arr_semestres = array();
		$semestre = $semestre_atual;
		$ano = $ano_atual;
		for ($i=0;$i<$quantidade;$i++){
			$semestre_anterior = $this->semestre_anterior($semestre,$ano);
			$semestre_anterior['selected'] = false;
			$arr_semestres[] = $semestre_anterior;
			$semestre = $semestre_anterior['semestre'];
			$ano = $semestre_anterior['ano'];
		}
		$arr_semestres = array_reverse($arr_semestres);

		$arr_semestres[] = array(
			'semestre' => $semestre_atual,
			'ano' => $ano_atual,
			'semestre_nome' => $this->nome_semestre($semestre_atual),
			'selected' => true
		);
		$semestre = $semestre_atual;
		$ano = $ano_atual;		
		for ($i=0;$i<$quantidade;$i++){
			$proximo_semestre = $this->proximo_semestre($semestre,$ano);
			$proximo_semestre['selected'] = false;
			$arr_semestres[] = $proximo_semestre;
			$semestre = $proximo_semestre['semestre'];
			$ano = $proximo_semestre['ano'];
		}		
		return $arr_semestres;
	}
	
	public function mesesPeriodoArray($ano_mes_inicio,$ano_mes_fim){ //Formato: 2014-05
		$arr_meses = array();
		$ano_i = substr($ano_mes_inicio,0,4);
		$mes_i = substr($ano_mes_inicio,5,2);
		$ano_f = substr($ano_mes_fim,0,4);
		$mes_f = substr($ano_mes_fim,5,2);
		
		pr($ano_i);
		pr($mes_i);
		pr($ano_f);
		pr($mes_f);
	}
}
?>