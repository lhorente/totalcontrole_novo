function calcula_orcamento(){
	var total_horas = 0;
	var valor_total = 0;
	$('.row-servicos-orcamento').each(function(index,element){
		var quantidade_horas = parseFloat($(element).find('.input-servico-orcamento-horas').val().replace(',','.'));
		var valor_hora = parseFloat($("#ServicoValorHora").val().replace(',','.'));
		var desconto = parseFloat($("#ServicoDesconto").val().replace(',','.'));
		if (!isNaN(quantidade_horas)){
			total_horas += quantidade_horas;
		}
		if (!isNaN(quantidade_horas) && !isNaN(!isNaN(quantidade_horas))){
			valor_total += quantidade_horas*valor_hora;
		}		
		$("#ServicoQuantidadeHoras").val(total_horas.toString());
		$("#ServicoValor").val(valor_total.toString().replace('.',','));
	});
}

function carrega_tr(){
	var num = $('.row-servicos-orcamento').length;
	$("<div>").load('/servicos_orcamentos/tr/'+num, function() {
		$(".table-servicos-orcamento").append($(this).html());
		formata_campos();
	});
}
$(document).ready(function(){
	carrega_tr()
	$("body").on('focus','.row-servicos-orcamento:last input',function(){
		carrega_tr();
	})
	
	$("body").on('change','.row-servicos-orcamento input',function(){
		calcula_orcamento();
	});	
});