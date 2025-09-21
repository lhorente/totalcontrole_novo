function carrega_categorias(){
	var tipo = $("#TransacaoTipo").val();
	$("#TransacaoIdCategoria").html('<option value="">Carregando...</option>');
	$(".div-categoria").load('/categorias/combo/'+tipo);
}

function grafico_gastos(){
	var legendas = new Array();
	var gastos = new Array();
	var lucros = new Array();
	var series = new Array();
	$(".row-legendas .col-valor").each(function(){
		legendas.push($(this).html());
	});
	$(".row-grafico").each(function(i,e){
		var serie = {name:$(e).find('.col-legenda').html()};
		serie.data = new Array();
		$(e).find('.col-valor').each(function(i2,e2){
			// console.log(serie.data);
			serie.data.push(parseFloat($(e2).html().replace(".","").replace(",",".")));
			// console.log(e2);
		});
		series.push(serie);
	})
	// console.log(series);
	// $(".row-depesas .col-valor").each(function(){
		// gastos.push(parseFloat($(this).html().replace(".","").replace(",",".")));
	// });

	$('#chart_gastos').highcharts({
        title: {
            text: 'Transações',
            x: -20 //center
        },
        subtitle: {
            text: 'Período de: ',
            x: -20
        },
        xAxis: {
			categories: legendas
        },
        yAxis: {
            title: {
                text: 'R$'
            },
            plotLines: [{
                value: 0,
                width: 1,
                color: '#808080'
            }]
        },
        tooltip: {
            valuePrefix: 'R$ '
        },
        legend: {
            layout: 'vertical',
            align: 'right',
            verticalAlign: 'middle',
            borderWidth: 0
        },
        series: series
    });
}

function calcula_total_produtos(){
	var valor_total = 0;
	$('.row-produto').each(function(index,element){
		var quantidade = parseFloat($(element).find('.produtoQuantidade').val().replace(',','.'));
		var valor_unitario = parseFloat($(element).find('.produtoValorUnitario').val().replace(',','.'));
		var desconto = parseFloat($(element).find('.produtoDesconto').val().replace(',','.'));
		var juros = parseFloat($(element).find('.produtoJuros').val().replace(',','.'));
		if (!isNaN(quantidade) && !isNaN(valor_total)){
			valor_total += Math.round(quantidade*valor_unitario*100)/100;
			// valor_total += quantidade*valor_unitario;
		}
		if (!isNaN(desconto)){
			valor_total -= desconto;
		}
		if (!isNaN(juros)){
			valor_total += juros;
		}
		valor_total = Math.round(valor_total*100)/100;
		$("#TransacaoValor").val(valor_total.toString().replace('.',','));
	});
}

function insere_novo_produto(){
	var num = $('.row-produto').length;
	$("<div>").load('/produtos/form_produto_transacao/'+num, function() {
		  $(".produtos").append($(this).html());
		  formata_campos();
		  autocomplete_produtos();
		  // $( ".autocomplete-produto" ).autocomplete( "option", "appendTo", ".eventInsForm" );
		  $(".msg-nenhum-produto").addClass("hide");
	});
}

function carrega_transacoes(){
	if ($('.lista-transacoes').length){
		showLoading();
		var qr = document.location.search.toString();
		var anomes = document.location.pathname.toString().substr(11);

		var url = '/transacoes/listar'+anomes+qr;
		$('.lista-transacoes').load(url,function(){
			hideLoading();
			$("#pagarMultiData").datepicker($.datepicker.regional["pt-BR"]);
			reloadSaldoMenu();
		});
	}
}

$(document).ready(function(){
	grafico_gastos();
	carrega_categorias();
	autocomplete_produtos();
	carrega_transacoes();

	$("body").on('submit','#TransacaoEditarForm',function(){
		$.post($(this).attr('action'),$(this).serialize(),function(response){
			if (response.status == true){
				$('#modal').modal('hide')
				$('#modal').html("");
				carrega_transacoes();
			}
		},'json');
		return false;
	});

	// $('body').on('click',".btn-salvar-transacao",function(e){
		// e.preventDefault();
		// showLoading();
		// $('#modal').load($(this).attr('href'),function(){
			// $('#modal').modal();
			// hideLoading();
			// formata_campos($('#modal'));
		// });
	// });

	$("body").on('change','.load-transacoes-ajax',function(e){
		var filters = [];
		$(".load-transacoes-ajax").each(function(k,e){
			var value = $(e).val();
			var name = $(e).attr('name');
			if (value){
				filters.push(name + "=" + value);
			}
		});
		// console.log(filters);
		// var value = $(this).val();
		// var name = $(this).attr('name');

		var uri = document.location.pathname;
		// var qs = document.location.search;

		// console.log("/"+uri+"?"+name+"="+value);

		history.pushState('object', "Total Controle", uri+"?"+filters.join("&"));
		carrega_transacoes();
	});

	// Editar data de pagamento /transacoes/index
	$('body').on('click',".btn-pagar-transacao",function(e){
		e.preventDefault();
		var btn_salvar = $(this);
		var tr = btn_salvar.parents("tr");
		var block_status = tr.find(".block-status");
		btn_salvar.removeClass("fa-check").addClass("fa-spinner fa-spin");
		$.ajax({
			url: btn_salvar.attr('href'),
			dataType: 'json',
			success:function(response){
				if (response.status == true){
					btn_salvar.remove();
					reloadSaldoMenu();
					block_status.removeClass("background-vermelho").addClass("background-verde");
				}
			},
			error: function(){
				btn_salvar.removeClass("fa-spinner fa-spin").addClass("fa-check");
			}
		});
	});

	/*
		Excluir Transação.
	*/
	$('body').on('click',".btn-excluir-transacao",function(e){
		e.preventDefault();
		if (confirm('Tem certeza que deseja excluir?')){


			var btn_excluir = $(this);
			var id = btn_excluir.data("id_transacao");

			$.ajax({
				url: '/transacoes/excluir/'+id,
				dataType: 'json',
				success:function(response){
					if (response.status == true){
						$("#box-transacao-"+id).fadeOut(300,function(){
							$(this).remove();
						});
					}
				}
			});
		}
	});

	/***************************
	Selecionar várias transações
	***************************/
	$('body').on('change',"#select-transacoes-selecionadas",function(e){
		if($(this).val() == 'confirmar_pagamento'){
			$(".div-acoes-selecionadas").addClass("hide");
			$(".div-confirmar-pagamento-selecionadas").removeClass('hide');
		} else if ($(this).val() == 'remover') {
			$(".div-acoes-selecionadas").addClass("hide");
			$(".div-remover-selecionadas").removeClass('hide');
		} else {
			$(".div-acoes-selecionadas").addClass("hide");
		}
	})

	$("body").on('click','#btn-salvar-transacoes-selecionadas',function(){
		var data_pagamento = $("#pagarMultiData").val();
		var ids = new Array();
		if ($(".chk-id-tranasacao:checked").length){
			$.each($(".chk-id-tranasacao:checked"),function(k,e){
				ids.push($(e).val());
			});
		}

		if (ids){
			$.post("/transacoes/pagar_multi",{transacoes_ids:ids,data_pagamento:data_pagamento},function(response){
				if (response.status){
					carrega_transacoes();
				} else {
					$(".status-multi").html("Não foi possível confirmar pagamento das transações selecionadas. Por favor, tente novamente.").removeClass('hide');
				}
			},'json');
		}
	})

	$('body').on('click',".btn-selecionar-varias",function(e){
		e.preventDefault();
		$(".lista-transacoes").addClass("multiselect");
		$(".btn-abrir").show();
		$(".box-acoes-selecionadas").removeClass('hide');
		$(this).hide();
	});

	$('body').on('click',".btn-abrir",function(e){
		e.preventDefault();
		$(".lista-transacoes").removeClass("multiselect");
		$(".btn-selecionar-varias").show();
		$(".box-acoes-selecionadas").addClass('hide');
		$(this).hide();
	});

	$("body").on("click","#btn-remover-transacoes-selecionadas",function(e){
		e.preventDefault();
		var element = $(this);
		var ids = new Array();
		if ($(".chk-id-tranasacao:checked").length){
			$.each($(".chk-id-tranasacao:checked"),function(k,e){
				ids.push($(e).val());
			});
		}
		if (confirm("Tem certeza?")){
			$.post("/transacoes/excluir_multi",{transacoes_ids:ids},function(response){
				if (response.status){
					carrega_transacoes();
				} else {
					$(".status-multi").html("Não foi possível excluir as transações selecionadas. Por favor, tente novamente.").removeClass('hide');
				}
			},'json');
		}
	});





	$('body').on('click',".box-transacao",function(e){
		e.preventDefault();

		if ($(".lista-transacoes").hasClass("multiselect")){
			var id = $(this).data('id_transacao');
			$("#check-transacao-"+id).click();
			$("#fake-check-transacao-"+id).toggleClass("active");
		} else {
			showLoading();
			$('#modal').load($(this).attr('href'),function(){
				$('#modal').modal();
				hideLoading();
				formata_campos($('#modal'));
			});
		}
	})

	// cencelar edição /transacoes/index
	$('body').on('click',".btn-cancelar-edicao-transacao",function(e){
		e.preventDefault();
		carrega_transacoes();
	});

	$("body").on("click",".btn-add-transacao",function(e){
		e.preventDefault();
		$(".box-transacao").toggleClass('hide');
	})

	$("body").on("change","#TransacaoTipo",function(){
		carrega_categorias();
	});

	$("body").on('click','.btn-add-produto',function(){
		insere_novo_produto();
	})

	$("body").on('click','.btn-remove-produto',function(){
		$(this).parents('.group-produto').find('.input-selected-produto').attr('disabled',false);
		$(this).parents('.group-produto').find('.input-selected-produto').val('');
		$(this).parents('.group-produto').find('.input-id-produto').val('');
		$(this).parents('.group-produto').find('.div-selected-produto').addClass('hide');
		$(this).parents('.group-produto').find(".autocomplete-produto").removeClass('hide');
		$(this).parents('.group-produto').find(".help-produto").addClass("hide");
		$(this).parents('.group-produto').find(".help-produto-digitar").removeClass("hide");
		if (!$(".produtos .group-produto").length){
			$(".msg-nenhum-produto").removeClass("hide");
		}
	});

	// $("body").on('change','.row-produto input',function(){
	// $("body").on('change','.row-produto input',function(){
		// calcula_total_produtos();
	// });

	$(".row-depesas .col-legenda").click(function(){
		$(".row-gastos-categoria").toggleClass('hide');
	});
	$(".row-lucros .col-legenda").click(function(){
		$(".row-lucros-categoria").toggleClass('hide');
	});

	$(".bt-expand-transacao-part").click(function(){
		$(this).toggleClass('glyphicon-chevron-up glyphicon-chevron-down');
		$(this).parents('.box-transacao-part').find(".row-transacao-part").toggleClass("hide");
	});

	// Salvar transação - /transacoes/index
	$("#TransacaoSalvarForm").submit(function(){
		$(".btn-salvar").html('<i class="fa fa-spinner fa-spin"></i>');
		$.post($(this).attr('action'),$(this).serialize(),function(response){
			$(".btn-salvar").html('Salvar');
			if (response.status == true){
				$("#TransacaoSalvarForm").find("input[type=text], textarea").val("");
				$("#TransacaoSalvarForm").find("select").each(function(i,e){
					$(this).find('option:first').attr('selected',true);
				});
				$(".alert-transacoes span").html("Salvo com sucesso.");
				$(".alert-transacoes").removeClass("alert-danger");
				$(".alert-transacoes").addClass("alert-success");
				$(".produtos").html("");
				carrega_transacoes();
			} else {
				$(".alert-transacoes span").html(response.errors_msg);
				$(".alert-transacoes").removeClass("alert-success");
				$(".alert-transacoes").addClass("alert-danger");
			}
			$(".alert-transacoes").removeClass("hide");
			carrega_categorias();
		},'json');
		return false;
	});

	$("#TransacaoData").change(function(){
		if (!$("#TransacaoDataPagamento").val()){
			$("#TransacaoDataPagamento").val($(this).val());
		}
	});

	$("body").on("change",".chk-id-tranasacao",function(){
		if ($(".chk-id-tranasacao:checked").length){
			$(".row-selecionados").removeClass("hide");
		} else {
			$(".row-selecionados").addClass("hide");
		}
	});

	$("body").on("change","#TransacaoParcelas",function(){
		var parcelas = $(this).val();
		$.ajax({
			url: '/transacoes/ajax_row_parcela/'+parcelas,
			method: 'POST',
			data: $(".row-parcela input").serialize(),
			success: function(response){
				$(".rows-parcelas").html(response);
			}
		});
	});

	$("body").on("change",".autocomplete-produto:last, .input-id-produto:last",function(){
		var num = $(".row-produto").length;
		$.ajax({
			url: '/produtos/ajax_row_produto/'+num,
			method: 'POST',
			success: function(response){
				$(".rows-produtos").append(response);
			}
		});
	});
});

// orçamentos
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
							input.data("ivalue",valor)
						} else {
							input.parents('.td-orcamento').find('.input-status').removeClass('fa-spinner fa-spin').addClass('fa-warning');
						}

						// Atualiza total do ano
						if (response.year_total){
							$(".orcamento_total_ano").html(numberToReal(response.year_total));
							var total_ano = realToNumber($(".total_ano").html());

							if (total_ano > response.year_total){
								$(".total_ano").removeClass('alert-success').addClass('alert-danger');
							} else {
								$(".total_ano").removeClass('alert-danger').addClass('alert-success');
							}
						}

						// Atualiza cor da célula
						var td_categoria = input.parents('tr').find('.td-total-categoria');
						var total_categoria = realToNumber(td_categoria.html());
						var total_orcamento = realToNumber(valor);

						if (total_categoria > total_orcamento){
							td_categoria.removeClass('alert-success').addClass('alert-danger');
						} else {
							td_categoria.removeClass('alert-danger').addClass('alert-success');
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
