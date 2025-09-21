function carrega_cartoes(){
	$('.lista-cartoes').load('/cartoes/listar');
}
$(document).ready(function(){
	carrega_cartoes();
	// salvar cartão
	$("body").on('submit','#CartaoSalvarForm',function(){
		$.post($(this).attr('action'),$(this).serialize(),function(response){
			if (response.status == true){
				$("#CartaoSalvarForm").find("input[type=text], textarea").val("");
				$("#CartaoSalvarForm").find("select").each(function(i,e){
					$(this).find('option:first').attr('selected',true);
				});
				carrega_cartoes();
			}
		},'json');
		return false;		
	})
	
	// excluir transação /transacoes/index
	$('body').on('click',".btn-excluir-cartao",function(e){
		e.preventDefault();
		$.get($(this).attr('href'),function(response){
			if (response.status == true){
				carrega_cartoes();
			}
		},'json');		
	});		
});