<?php
class FlashMessageComponent extends Component {
    function humanizar(array $validationErrors){
		if (is_array($validationErrors)){
			$html = "";
			$arr_errors = array();
			foreach ($validationErrors as $e){
				$arr_errors[] = implode("<br />",$e);
			}
			$html = implode("<br />",$arr_errors);
			return $html;
		} else {
			return false;
		}
    }
}
?>