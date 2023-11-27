jQuery( document ).ready( function(){
    function confirm(){
		// Mostrar a tela de confirmação.
		
		jQuery('.confirm').show();
		
		// Iniciar popup.
		
		jQuery('.button').popup({addTouchEvents:false});
		
		// Form da confirmacao.jQuery(
		
		var formSelector = '.confirmacaoForm';
		
		jQuery(formSelector)
			.form({
				
			});
		
		// Botão de confirmação.
		
		jQuery('.confirmScheduleBtn').on('mouseup tap',function(e){
			if(e.which != 1 && e.which != 0 && e.which != undefined) return false;
			
			jQuery(formSelector).find('input[name="escolha"]').val('confirm');
			jQuery(formSelector).form('submit');
		});
		
		// Botão de cancelamento.
		
		jQuery('.cancelSchedulingBtn').on('mouseup tap',function(e){
			if(e.which != 1 && e.which != 0 && e.which != undefined) return false;
			
			jQuery(formSelector).form('submit');
		});
	}
	
	function cancel(){
		// Mostrar a tela de confirmação pública.
		
		jQuery('.cancel').show();
		
		// Iniciar popup.
		
		jQuery('.button').popup({addTouchEvents:false});
		
		// Form da confirmacao.
		
		var formSelector = '.cancelamentoForm';
		
		jQuery(formSelector)
			.form({
				
			});
		
		// Botão de cancelamento.
		
		jQuery('.cancelSchedulingBtn').on('mouseup tap',function(e){
			if(e.which != 1 && e.which != 0 && e.which != undefined) return false;
			
			jQuery(formSelector).form('submit');
		});
	}
	
	function ExpiredOrNotFound(){
		// Mostrar a tela de confirmação pública.
		
		jQuery('.ExpiredOrNotFound').show();
	}
	
	function schedulingActive(){
		// Configurações do calendário.
		
		var calendario = manager.calendario;
		
		// Datas disponíveis para agendamento.
		
		var datasDisponiveis = [];
		
		for(var data in calendario.datas_disponiveis){
			var dateObj = new Date(data.replace(/-/g, '\/')); // Bug no objeto Date() do javascript. Basta trocar o '-' por '/' que a data funciona corretamente. Senão fica um dia a mais do dia correto.
			
			datasDisponiveis.push(dateObj);
		}
		
		// Form Agendamentos.
		
		var formId = 'formAgendamentos';
		var formSelector = '#formAgendamentos';
		
		jQuery(formSelector)
			.form({
				fields : (manager.formulario[formId].regrasValidacao ? manager.formulario[formId].regrasValidacao : {}),
				onSuccess(event, fields){
					
				}
			});
		
		// Cupom de prioridade mask.
		
		jQuery('.cupom').mask('AAAA-AAAA', {clearIfNotMatch: true});
		
		// Calendário ptBR.
		
		var calendarPtBR = {
			days: ['D', 'S', 'T', 'Q', 'Q', 'S', 'S'],
			months: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
			monthsShort: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
			today: 'Hoje',
			now: 'Agora',
			am: 'AM',
			pm: 'PM'
		};
		
		// Variáveis do componente 'calendar' datas-multiplas.
		
		var calendarDatasOpt = {
			text: calendarPtBR,
			type: 'date',
			inline: true,
			initialDate: new Date(),
			minDate: new Date(calendario.ano_inicio+'/01/01'),
			maxDate: new Date(calendario.ano_fim+'/12/31'),
			eventClass: 'inverted blue',
			enabledDates: datasDisponiveis,
			eventDates: datasDisponiveis,
			formatter: {
				date: function (date, settings) {
					if (!date) return '';
					
					var day = (date.getDate() < 10 ? '0' : '') + date.getDate();
					var month = ((date.getMonth() + 1) < 10 ? '0' : '') + (date.getMonth() + 1);
					var year = date.getFullYear();
					
					return day + '/' + month + '/' + year;
				}
			},
			onChange: function(date,dateFormated,mode){
				jQuery(this).parent().find('.agendamentoData').val(dateFormated);
				jQuery(this).parent().find('.dataSelecionada').show();
				jQuery(this).parent().find('.dataSelecionada').find('.dataSelecionadaValor').html(dateFormated);
				
				jQuery(formSelector).form('validate form');
			}
		}
		
		// Iniciar calendário.
		
		jQuery('.ui.calendar').calendar(calendarDatasOpt);
		
		// Iniciar popup.
		
		jQuery('.button').popup({addTouchEvents:false});
		
		// Acompanhantes dropdown.
		
		jQuery('.ui.dropdown').dropdown({
			onChange: function(value){
				var objPai = jQuery(this).parents('.field');
				var acompanhantesCont = objPai.find('.acompanhantesCont');
				var acompanhantesTemplateCont = objPai.find('.acompanhantesTemplateCont');
				var numAcom = acompanhantesCont.find('.field').length;
				
				value = parseInt(value);
				
				if(value > numAcom){
					for(var i=numAcom;i<value;i++){
						var field = acompanhantesTemplateCont.find('.field').clone();
						var num = (i+1);
						
						field.attr('data-num',num);
						field.find('label').html('Acompanhante '+num);
						field.find('input').prop('name','acompanhante-'+num);
						field.find('input').prop('placeholder','Nome Completo do Acompanhante '+num);
						field.find('input').attr('data-validate','acompanhante'+num);
						
						acompanhantesCont.append(field);
						
						jQuery(formSelector).form('add rule', ('acompanhante'+num),{ rules : manager.formulario[formId].regrasValidacao[('acompanhante'+num)].rules });
					}
				} else {
					var num = 0;
					
					acompanhantesCont.find('.field').each(function(){
						num++;
						
						jQuery(formSelector).form('remove fields', ['acompanhante'+num]);
						
						if(num > value){
							jQuery(this).hide();
						} else {
							jQuery(this).show();
							jQuery(formSelector).form('add rule', ('acompanhante'+num),{ rules : manager.formulario[formId].regrasValidacao[('acompanhante'+num)].rules });
						}
					});
				}
			}
		});
		
		// Tratamento de telas.
		
		jQuery('.agendarBtn').on('mouseup tap',function(e){
			if(e.which != 1 && e.which != 0 && e.which != undefined) return false;
			
			jQuery('.agendamentosTela').hide();
			jQuery('.agendar').show();
		});
		
		jQuery('.agendamentosBtn').on('mouseup tap',function(e){
			if(e.which != 1 && e.which != 0 && e.which != undefined) return false;
			
			jQuery('.agendamentosTela').hide();
			jQuery('.agendamentos').show();
		});
		
		if('tela' in manager){
			switch(manager.tela){
				case 'agendamentos-anteriores':
					jQuery('.agendamentos').show();
				break;
				default:
					jQuery('.agendar').show();
			}
		} else {
			jQuery('.agendar').show();
		}
		
		// Tab de informações dos agendamentos.
		
		jQuery('.tabular.menu .item').tab();
		
		// Botão confirmar.
		
		jQuery(document.body).on('mouseup tap','.confirmScheduleBtn',function(e){
			if(e.which != 1 && e.which != 0 && e.which != undefined) return false;
			
			var agendamento_id = jQuery(this).attr('data-id');
			
			window.open("/agendamentos/?acao=confirm&agendamento_id="+agendamento_id,"_self");
		});
		
		// Botão cancelar.
		
		jQuery(document.body).on('mouseup tap','.cancelSchedulingBtn',function(e){
			if(e.which != 1 && e.which != 0 && e.which != undefined) return false;
			
			var agendamento_id = jQuery(this).attr('data-id');
			
			window.open("/agendamentos/?acao=cancel&agendamento_id="+agendamento_id,"_self");
		});
		
		// Informações de um agendamento.
		
		jQuery(document.body).on('mouseup tap','.dadosAgendamentoBtn',function(e){
			if(e.which != 1 && e.which != 0 && e.which != undefined) return false;
			
			// Verificar o tipo de agendamento.
			
			var tipo = '';
			if(jQuery(this).hasClass('preAgendamento')){tipo = 'preAgendamento';}
			if(jQuery(this).hasClass('agendamento')){tipo = 'agendamento';}
			if(jQuery(this).hasClass('agendamentoAntigo')){tipo = 'agendamentoAntigo';}
			
			// Buscar os dados no servidor e montar na tela o resultado.
			
			var opcao = 'agendamentos-host';
			var ajaxOpcao = 'dados-do-agendamento';
			
			$.ajax({
				type: 'POST',
				url: manager.raiz + 'agendamentos/',
				data: {
					opcao : opcao,
					ajax : 'sim',
					ajaxPagina : 'sim',
					ajaxOpcao : ajaxOpcao,
					tipo : tipo,
					agendamento_id : jQuery(this).attr('data-id')
				},
				dataType: 'json',
				beforeSend: function(){
					carregando('abrir');
				},
				success: function(dados){
					switch(dados.status){
						case 'OK':
							modal({mensagem:dados.dadosAgendamentos});
						break;
						case 'ERROR':
							modal({mensagem:dados.msg});
						break;
						default:
							console.log('ERROR - '+opcao+' - '+dados.status);
							carregando('fechar');
						
					}
				},
				error: function(txt){
					switch(txt.status){
						case 401: window.open(manager.raiz + (txt.responseJSON.redirect ? txt.responseJSON.redirect : "signin/"),"_self"); break;
						default:
							console.log('ERROR AJAX - '+opcao+' - Dados:');
							console.log(txt);
							carregando('fechar');
					}
				}
			});
		});
		
		// Regras para ler mais entradas de agendamentos.
		
		var carregarObjs = {};
		var button_id = '.carregarMaisPre,.carregarMaisAgendamentos,.carregarMaisAntigos';
		
		jQuery(button_id).on('mouseup tap',function(e){
			if(e.which != 1 && e.which != 0 && e.which != undefined) return false;
			
			var obj = this;
			
			// Verificar o tipo de agendamento.
			
			var tipo = '';
			if(jQuery(obj).hasClass('carregarMaisPre')){tipo = 'carregarMaisPre';}
			if(jQuery(obj).hasClass('carregarMaisAgendamentos')){tipo = 'carregarMaisAgendamentos';}
			if(jQuery(obj).hasClass('carregarMaisAntigos')){tipo = 'carregarMaisAntigos';}
			
			// Carregar objetos.
			
			if(!(tipo in carregarObjs)){
				carregarObjs[tipo] = {
					maxPaginas : parseInt(jQuery(obj).attr('data-num-paginas')),
					paginaAtual : 0
				};
			}
			
			// Carregar dados do servidor.
			
			var opcao = 'agendamentos-host';
			var ajaxOpcao = 'mais-resultados';
			
			carregarObjs[tipo].paginaAtual++;
			
			var paginaAtual = carregarObjs[tipo].paginaAtual;
			
			$.ajax({
				type: 'POST',
				url: manager.raiz + 'agendamentos/',
				data: { 
					opcao,
					ajax : 'sim',
					ajaxPagina : 'sim',
					ajaxOpcao,
					tipo,
					paginaAtual
				},
				dataType: 'json',
				beforeSend: function(){
					carregando('abrir');
				},
				success: function(dados){
					switch(dados.status){
						case 'OK':
							// Incluir os registros nas tabelas correspondentes aos tipos de agendamento.
							
							switch(tipo){
								case 'carregarMaisPre': jQuery('.tabelaPreAgendamentos').append(dados.registros); break;
								case 'carregarMaisAgendamentos': jQuery('.tabelaAgendamentos').append(dados.registros); break;
								case 'carregarMaisAntigos': jQuery('.tabelaAgendamentosAntigos').append(dados.registros); break;
							}
							
							// Esconder o botão quando chegar na última página.
							
							if(carregarObjs[tipo].paginaAtual >= carregarObjs[tipo].maxPaginas - 1){
								jQuery(obj).parent().hide();
							}
							
							// Iniciar popup.
							
							jQuery('.button').popup({addTouchEvents:false});
						break;
						default:
							console.log('ERROR - '+opcao+' - '+dados.status);
						
					}
					
					carregando('fechar');
				},
				error: function(txt){
					switch(txt.status){
						case 401: window.open(manager.raiz + (txt.responseJSON.redirect ? txt.responseJSON.redirect : "signin/"),"_self"); break;
						default:
							console.log('ERROR AJAX - '+opcao+' - Dados:');
							console.log(txt);
							carregando('fechar');
					}
				}
			});
		});
		
		function carregando(opcao){
			switch(opcao){
				case 'abrir':
					if(!('carregando' in manager)){
						jQuery('.paginaCarregando').dimmer({
							closable: false
						});
						
						manager.carregando = true;
					}
					
					jQuery('.paginaCarregando').dimmer('show');
				break;
				case 'fechar':
					jQuery('.paginaCarregando').dimmer('hide');
				break;
			}
		}
		
		function modal(p={}){
			if(p.mensagem){
				jQuery('.ui.modal.informativo .content').html(p.mensagem);
			}
			
			jQuery('.ui.modal.informativo').modal({
				dimmerSettings:{
					dimmerName:'paginaCarregando' //className, NOT id (!)
				}
			}).modal('show');
		}
		
		
	}
	
	function start(){
		// Active scheduling.
		console.log('Scheduling active!');
        
		if( jQuery('.active-scheduling').length > 0 ){ schedulingActive(); }
		
		// Handle scheduling changes.
		if('confirm' in manager){ confirm(); }
		if('cancel' in manager){ cancel(); }
		if('ExpiredOrNotFound' in manager){ ExpiredOrNotFound(); }
	}
	
	start();
});