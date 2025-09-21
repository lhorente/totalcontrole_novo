<?php
class MapsComponent extends Component {
    function getCoordenadas($endereco) {
		$endereco = urlencode($endereco);
		$url = "http://maps.googleapis.com/maps/api/geocode/json?address={$endereco}&sensor=false";
		$get = json_decode(file_get_contents($url));
		if ($get && $get->results){
			$coords = $get->results[0]->geometry->location;
			return $coords;
		}
		return false;
    }
}
?>