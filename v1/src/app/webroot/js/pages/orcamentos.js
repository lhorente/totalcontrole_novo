function carrega_orcamentos(){
	var ano = $("#OrcamentoAno").val();
	var semestre = $("#OrcamentoSemestre").val();
	$('.lista-orcamentos').html('<i class="fa fa-spinner fa-spin loading-page"></i>');
	$('.lista-orcamentos').load('/orcamentos/listar/'+ano+'/'+semestre,function(){
		formata_campos();
	});
}

function salvar_orcamento(input){
	input.parents('.td-orcamento').find('.input-status').removeClass('fa-check').addClass('fa-spinner fa-spin').show();
	if (input){
		var id_categoria = input.attr('data-id_categoria');
		var ano = input.attr('data-ano');
		// var mes = input.attr('data-mes');
		var valor = input.val();
		if (id_categoria && ano){
			$.ajax({
				url: '/orcamentos/salvar',
				method: 'POST',
				dataType: 'json',
				data: {id_categoria:id_categoria,ano:ano,valor:valor},
				success:function(response){
					if (response.status){
						if (response.data && response.data.Orcamento && response.data.Orcamento.id){
							input.parents('.td-orcamento').find('.input-id_orcamento').val(response.data.Orcamento.id);
							input.parents('.td-orcamento').find('.input-status').removeClass('fa-spinner fa-spin').addClass('fa-check');
						} else {
							input.parents('.td-orcamento').find('.input-status').removeClass('fa-spinner fa-spin').addClass('fa-warning');
						}
					} else {
						input.parents('.td-orcamento').find('.input-status').removeClass('fa-spinner fa-spin').addClass('fa-warning');
					}
				},
				error: function(){
					input.parents('.td-orcamento').find('.input-status').removeClass('fa-spinner fa-spin').addClass('fa-warning');
				}
			});
		}
	}
}

$(document).ready(function(){
	carrega_orcamentos();

	$("body").on("change",".input-orcamento",function(){
		var input = $(this);
		if (input.val() != input.data("ivalue")){
			salvar_orcamento(input);
		}
	});
});
