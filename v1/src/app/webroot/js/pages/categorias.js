function carrega_categorias(){
	$('.lista-categorias').load('/categorias/listar');
}
$(document).ready(function(){
	carrega_categorias();
	// salvar cartão
	$("body").on('submit','#CategoriaSalvarForm2',function(){
		$.post($(this).attr('action'),$(this).serialize(),function(response){
			if (response.status == true){
				$("#CartaoSalvarForm").find("input[type=text], textarea").val("");
				$("#CartaoSalvarForm").find("select").each(function(i,e){
					$(this).find('option:first').attr('selected',true);
				});
				carrega_categorias();
			}
		},'json');
		return false;		
	})
	
	// excluir transação /transacoes/index
	$('body').on('click',".btn-excluir-categoria",function(e){
		e.preventDefault();
		$.get($(this).attr('href'),function(response){
			if (response.status == true){
				carrega_categorias();
			}
		},'json');		
	});
});