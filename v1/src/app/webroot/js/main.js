function realToNumber(t) {
	if(t){
		return (numero = parseFloat(t.replace(".", "").replace(",", "").replace("R$", "").replace("- ", "")) / 100, numero)
	}
}

function numberToReal(valor){
	if (valor){
		valor = parseFloat(valor);
		return valor.toFixed(2).replace(".",",").replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1.");
	}
	return null;
}
function autocomplete_produtos(){
    $(".autocomplete-produto").autocomplete({
		source: function(request,response){
			$.get('/produtos/autocomplete/'+request.term,function(data){
				if (data){
					response($.map(data, function(item){
						return {
							label: item.Produto.nome,
							value: item.Produto.id
						}
					}));
				};
			},'json');
		},
		minLength: 2,
		select: function(event,ui){
			$(this).parents('.row-produto').find('.input-selected-produto').val(ui.item.label);
			$(this).parents('.row-produto').find('.input-selected-produto').attr('disabled',true);
			$(this).parents('.row-produto').find(".input-id-produto").val(ui.item.value);
		},
		close: function(event,ui){
			if ($(this).parents('.row-produto').find(".input-id-produto").val()){
				$(this).val('');
				$(this).addClass('hide');
				$(this).parents('.row-produto').find('.div-selected-produto').removeClass('hide');
				$(this).parents('.row-produto').find(".help-produto").addClass("hide");
				$(this).parents('.row-produto').find(".help-produto-remover").removeClass("hide");
			}
		}
    });
	$(".ui-autocomplete").css('z-index','3000');
}

function formata_campos(form){
	if (form){
		form.find('.input-cep').mask('99999-999');
		form.find('.input-telefone').mask('(99) 9999-9999');
		form.find('.input-hora').mask('99:99');
		// form.find(".input-valor").maskMoney({symbol:"R$",decimal:",",thousands:"."});
		form.find(".input-valor").addClass('campo-formatado');
		form.find(".datepicker").datepicker($.datepicker.regional["pt-BR"]);
		let input = SimpleMaskMoney.setMask('.input-valor');
	} else {
		$('.input-cep').mask('99999-999');
		$('.input-telefone').mask('(99) 9999-9999');
		$('.input-hora').mask('99:99');
		// $(".input-valor").not($(".campo-formatado")).maskMoney({symbol:"R$",decimal:",",thousands:"."});
		$(".input-valor").addClass('campo-formatado');
		$(".datepicker").datepicker($.datepicker.regional["pt-BR"]);
	}
}

function showLoading(){
	var h = $('#modal-loading .modal-dialog').outerHeight(true);
	var wh = $(window).height();
	var margin_top = (wh-h)/2
	$('#modal-loading .modal-dialog').css('margin-top',margin_top);
	$('#modal-loading').modal();
}

function hideLoading(){
	$('#modal-loading').modal('hide');
}

function reloadSaldoMenu(){
	console.log("OOO");
	$(".user-info-menu").load('/usuarios/ajax_saldo');
}

$(document).ready(function(){
	formata_campos();

	$("body").on("click",".box-saldo .menos-mais",function(e){
		var btnMenosMais = $(this);
		btnMenosMais.parents(".box-saldo").find(".caixas").slideToggle("fast",function(){
			if (btnMenosMais.parents(".box-saldo").find(".caixas:visible").length){
				btnMenosMais.html('menos <i class="fa fa-caret-up"></i>');
			} else {
				btnMenosMais.html('mais <i class="fa fa-caret-down"></i>');
			}
		});
	});

	// Editar transacao /transacoes/index
	// $('body').on('click',".btn-editar-transacao",function(e){
		// e.preventDefault();
		// $("<tr>").load($(this).attr("href"),function(response){
			// $(".input-valor").maskMoney({symbol:"R$",decimal:",",thousands:"."});
			// $(".datepicker").datepicker($.datepicker.regional["pt-BR"]);
		// }).insertAfter($(this).parents('tr'));
		// $(this).parents('tr').addClass('hide');
	// });

	$('body').on('click',".btn-close-alert",function(e){
		e.preventDefault();
		$(this).parents(".alert").addClass("hide");
	});

	$('.tooltip-title').tooltip();

	$("body").on('click','.table .bt-toggle-expand',function(e){
		e.preventDefault();
		var id = $(this).parents('.item').attr('data-item');
		$(".item[data-parent="+id+"]").toggleClass("hide");
		$(this).find('.fa').toggleClass("fa-minus fa-plus");
		$(".item[data-parent="+id+"]").each(function(i,e){
			var id2 = $(e).attr('data-item');
			if ($(e).hasClass("hide")){
				$(".table .item[data-parent="+id2+"]").addClass("hide");
				$(".table .item[data-item="+id2+"] .bt-toggle-expand .fa").addClass("fa-plus");
				$(".table .item[data-item="+id2+"] .bt-toggle-expand .fa").removeClass("fa-minus");
			}
		});
	});

	$("body").on("click",".carrega-modal",function(e){
		e.preventDefault();
		showLoading();
		$('#modal').load($(this).attr('href'),function(){
			$('#modal').modal();
			hideLoading();
			formata_campos($('#modal'));
			autocomplete_produtos();
		});
	});

	$("body").on('submit',".form-modal-ajax",function(e){
		e.preventDefault();
		var form_modal = $(this);
		form_modal.find(".btn-salvar").prop("disabled",true).html('Salvando <i class="fa fa-spinner fa-spin"></i>');
		$.ajax({
			url: $(this).attr('action'),
			method: 'POST',
			dataType: 'json',
			data: $(this).serialize(),
			success:function(response){
				if (response){
					if (response.status == 1){
						$(".form-modal-ajax .alert").addClass('alert-success');
						$(".form-modal-ajax .alert").removeClass('alert-danger');
						$('#modal').modal('hide');
						carrega_transacoes();
					} else {
						form_modal.find(".btn-salvar").prop("disabled",false).html('Salvar');
						$(".form-modal-ajax .alert").removeClass('alert-success');
						$(".form-modal-ajax .alert").addClass('alert-danger');
					}
					$(".form-modal-ajax .alert").removeClass("hide");
					$(".form-modal-ajax .alert").html(response.msg);
				}
			}
		});
		return false;
	});

})
