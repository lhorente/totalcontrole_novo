<?php
	$_pluralIrregular = array(
	 'transacao' => 'transacoes',
	 'cartao' => 'cartoes',
	 'user' => 'users',
	);

	$_uninflected = array('atlas', 'lapis', 'onibus', 'pires', 'virus', '.*x', 'status');

	Inflector::rules('plural', array(
	'rules' => array(
	'/^(.*)ao$/i' => '\1oes',
	'/^(.*)(r|s|z)$/i' => '\1\2es',
	'/^(.*)(a|e|o|u)l$/i' => '\1\2is',
	'/^(.*)il$/i' => '\1is',
	'/^(.*)(m|n)$/i' => '\1ns',
	'/^(.*)$/i' => '\1s'
	),
	'uninflected' => $_uninflected,
	'irregular' => $_pluralIrregular
	), true);
?>
