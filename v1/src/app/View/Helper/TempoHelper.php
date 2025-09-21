<?php
class TempoHelper extends AppHelper {
    function dataExtenso($data) {
        $meses = array(
			1 => 'janeiro',
			2 => 'fevereiro',
			3 => 'março',
			4 => 'abril',
			5 => 'maio',
			6 => 'junho',
			7 => 'julho',
			8 => 'agosto',
			9 => 'setembro',
			10 => 'outubro',
			11 => 'novembro',
			12 => 'dezembro'
		);
		$semana = array('domingo','segunda','terça','quarta','quinta','sexta','sábado');
		
		if (stripos($data,"-")){
			$date = DateTime::createFromFormat('Y-m-d',$data);
		} else {
			$date = DateTime::createFromFormat('d/m/Y',$data);
		}
		$dia = date_format($date,'j');
		$mes = date_format($date,'n');
		$ano = date_format($date,'Y');
		$dia_semana = date_format($date,'w');
		$extenso = "{$semana[$dia_semana]}, {$dia} de {$meses[$mes]} de {$ano}";
		return $extenso;
    }
	
	function inverteData($data){
        if (stripos($data,":")){
            $dt = explode(" ",$data);
            $data = $dt[0];
            $hora = $dt[1];
        }

        if (stripos($data,"-")){
            $d = implode("/", array_reverse(explode("-", $data)));
        } elseif (stripos($data,"/")){
            //$d = implode("-", array_reverse(explode("/", $data)));
            $d = explode("/",$data);
            $dia = $d[0];
            $mes = $d[1];
            $ano = $d[2];
            $d = date('Y-m-d',mktime(0,0,0,$mes,$dia,$ano));
        } else {
            return false;
        }

        if (isset($hora)){
            return $d . " " . $hora;
        } else {
            return $d;
        }	
	}
}
?>