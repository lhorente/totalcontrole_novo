<?php
class ApiComponent extends Component {
	/*
		fields: Array com campos para verificar
		fieldsData: POST ou GET
	*/
	function verifyFields($fields,$fieldsData){
		$erro = false;
		$ret = array(
			'status' => false,
			'fieldsErrors' => array()
		);
		foreach ($fields as $field_name=>$field){
			if ($field['required']){
				if ((isset($fieldsData[$field_name]) && $fieldsData[$field_name])){
					$ret['fieldsErrors'][$field_name] = $field['label'] . " não preenchido/inválido";
					$erro = true;
				}
			}
		}
		
		if (!$erro){
			$ret['status'] = true;
		}
		return $ret;
	}
}
?>