<?php 
	$migalhas = $this->Session->read('migalha');
	$total = count($migalhas);
?>
<ol class="breadcrumb">
	<li><a href="<?=$this->webroot?>">Início</a></li>
	<?php if ($migalhas){ ?>
		<?php foreach ($migalhas as $i=>$tela){ ?>
			<?php if ($i==($total-1)){ ?>
				<li class="active"><?=$tela['Tela']['nome']?></li>
			<?php } else { ?>
				<li><a href="<?=Router::url(array('controller'=>$tela['Tela']['controller'],'action'=>$tela['Tela']['action']))?>"><?=$tela['Tela']['nome']?></a></li>
			<?php } ?>
		<?php } ?>
	<?php } ?>
</ol>