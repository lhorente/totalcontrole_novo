<?php
$agora = new DateTime();

$output = fopen('php://output', 'w');

$header = ['Tipo','Data','Data de Pagamento','Categoria','Descrição','Contato','Cartão','Carteira','Carteira Para','Serviço','Valor'];

fputcsv($output, $header,';');

foreach ($transacoes['transacoes'] as $r){
	$transacaoCSV = [
		'tipo'=>'',
		'data'=>'',
		'data_pagamento'=>'',
		'categoria'=>'',
		'descricao'=>'',
		'contato'=>'',
		'cartao'=>'',
		'carteira'=>'',
		'carteira_para'=>'',
		'servico'=>'',
		'valor'=>''
	];

	if ($r['Transacao']['tipo']){
		$transacaoCSV['tipo'] = $r['Transacao']['tipo'];
	}

	if ($r['Transacao']['data']){
		$transacaoCSV['data'] = $r['Transacao']['data'];
	}

	if ($r['Transacao']['data_pagamento']){
		$transacaoCSV['data_pagamento'] = $r['Transacao']['data_pagamento'];
	}

	if ($r['CategoriaPai']){
		$transacaoCSV['categoria'] = utf8_decode($r['CategoriaPai']['nome']) . " > ";
	}
	$transacaoCSV['categoria'] .= utf8_decode($r['Categoria']['nome']);

	if ($r['Transacao']['descricao']){
		$transacaoCSV['descricao'] = utf8_decode($r['Transacao']['descricao']);
	}

	if (isset($r['Cliente']) && $r['Cliente']['id']){
		$transacaoCSV['contato'] = utf8_decode($r['Cliente']['nome']);
	}

	if ($r['Cartao']){
		$transacaoCSV['cartao'] = utf8_decode($r['Cartao']['descricao']);
	}

	if (isset($r['CaixaPai']) && $r['CaixaPai']['id']){
		$transacaoCSV['carteira'] = utf8_decode($r['CaixaPai']['titulo']) . " > ";
	}
	$transacaoCSV['carteira'] .= utf8_decode($r['Caixa']['titulo']);

	if (isset($r['CaixaPara']) && $r['CaixaPara']['id']){
		$transacaoCSV['carteira_para'] = utf8_decode($r['CaixaPara']['titulo']);
	}

	if (isset($r['Servico']) && $r['Servico']['id']){
		$transacaoCSV['servico'] = utf8_decode($r['Servico']['descricao']);
	}

	if ($r['Transacao']['valor']){
		$transacaoCSV['valor'] = number_format($r['Transacao']['valor'],2,",",".");
	}

	fputcsv($output, $transacaoCSV,';');

	$filename = "lancamentos_{$ano}_{$mes}";

	header('Pragma: public');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Cache-Control: private', false);
	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment; filename="' . $filename . '.csv";');
	header('Content-Transfer-Encoding: binary');
	// pr($transacaoCSV);
}
?>
