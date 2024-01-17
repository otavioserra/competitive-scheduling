jQuery(document).ready(function(){
    function cupoms_de_prioridade(){
		// ===== Maks do quantidade.
		
		jQuery('.quantidade').mask("000", {reverse: true});
		
		// ===== Widget calendário opções.
		
		jQuery('#rangestart').calendar({
			type: 'date',
			endCalendar: jQuery('#rangeend'),
			formatter: {
				date: function (date, settings) {
					if (!date) return '';
					
					var day = (date.getDate() < 10 ? '0' : '') + date.getDate();
					var month = ((date.getMonth() + 1) < 10 ? '0' : '') + (date.getMonth() + 1);
					var year = date.getFullYear();
					
					return day + '/' + month + '/' + year;
				}
			}
		});
		jQuery('#rangeend').calendar({
			type: 'date',
			startCalendar: jQuery('#rangestart'),
			formatter: {
				date: function (date, settings) {
					if (!date) return '';
					
					var day = (date.getDate() < 10 ? '0' : '') + date.getDate();
					var month = ((date.getMonth() + 1) < 10 ? '0' : '') + (date.getMonth() + 1);
					var year = date.getFullYear();
					
					return day + '/' + month + '/' + year;
				}
			}
		});
		
		// ===== Requisição para imprimir os cupons.
		
		jQuery('.imprimirCupons').on('mouseup tap',function(e){
			if(e.which != 1 && e.which != 0 && e.which != undefined) return false;
			
			window.open(manager.root+"pagina-de-impressao/","Imprimir","menubar=0,location=0,height=700,width=1024");
		});
	}
	
	function agendamentos_atualizar(p={}){
		// ===== Caso não exista, criar o objeto de controle.
		
		if(!('agendamentos' in manager)){
			manager.agendamentos = {};
		}
		
		// ===== Modificar conforme enviado.
		
		if('data' in p){manager.agendamentos.data = p.data;}
		if('status' in p){manager.agendamentos.status = p.status;}
		
		// ===== Esconder manualmente conteiners não necessários devido os componentes do fomantic-ui se auto-mostrarem no início da DOM.
		
		if(
			!('data' in manager.agendamentos) || 
			!('status' in manager.agendamentos)
		){
			jQuery('.printBtn').hide();
			jQuery('.tablePeople').hide();
		}
		
		// ===== Mostrar o conteiner de resultados.
		
		if(
			('data' in manager.agendamentos)
		){
			jQuery('.resultados').show();
		}
		
		// ===== Somente atualizar caso esteja definido 'data' e 'status'.
		
		if(
			('data' in manager.agendamentos) && 
			('status' in manager.agendamentos)
		){
			// ===== Requisição para atualizar os agendamentos conforme opção.
			
			var opcao = 'agendamentos';
			var ajaxOpcao = 'atualizar';
			
			$.ajax({
				type: 'POST',
				url: manager.root + manager.moduloId + '/',
				data: {
					opcao : opcao,
					ajax : 'sim',
					ajaxOpcao : ajaxOpcao,
					ajaxPagina : 'sim',
					data : manager.agendamentos.data,
					status : manager.agendamentos.status
				},
				dataType: 'json',
				beforeSend: function(){
					carregando('abrir');
				},
				success: function(dados){
					switch(dados.status){
						case 'OK':
							// ===== Montar a tabela.
							
							jQuery('.tablePeople').html(dados.tabela);
							
							// ===== Atualizar o total de pessoas.
							
							jQuery('.totalValue').html(dados.total);
							
							// ===== Mostrar ou não o botão imprimir.
							
							if(dados.imprimir){
								jQuery('.printBtn').show();
							} else {
								jQuery('.printBtn').hide();
							}
							
							// ===== Mostrar os conteiners de informação dos resultados.
							
							jQuery('.totalPeople').show();
							jQuery('.tablePeople').show();
						break;
						default:
							console.log('ERROR - '+opcao+' - '+dados.status);
						
					}
					
					carregando('fechar');
				},
				error: function(txt){
					switch(txt.status){
						case 401: window.open(manager.root + (txt.responseJSON.redirect ? txt.responseJSON.redirect : "signin/"),"_self"); break;
						default:
							console.log('ERROR AJAX - '+opcao+' - Dados:');
							console.log(txt);
							carregando('fechar');
					}
				}
			});
		}
	}
    
    function agendamentos(){
        // ===== Configurações do calendário.
        
        var calendar = manager.calendar;
        
        // ===== Datas disponíveis para agendamento.
        
        var datasDisponiveis = [];
        
        for(var data in calendar.available_dates){
            var dateObj = new Date(data.replace(/-/g, '\/')); // Bug no objeto Date() do javascript. Basta trocar o '-' por '/' que a data funciona corretamente. Senão fica um dia a mais do dia correto.
            
            datasDisponiveis.push(dateObj);
        }
        
        // ===== Calendário ptBR.
        
        var calendarPtBR = {
            days: ['D', 'S', 'T', 'Q', 'Q', 'S', 'S'],
            months: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Júlio', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
            monthsShort: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
            today: 'Hoje',
            now: 'Agora',
            am: 'AM',
            pm: 'PM'
        };
        
        // ===== Variáveis do componente 'calendar'.
        
        var calendarDatasOpt = {
            text: calendarPtBR,
            type: 'date',
            inline: true,
            initialDate: new Date(),
            minDate: new Date(calendar.start_year+'/01/01'),
            maxDate: new Date(calendar.year_end+'/12/31'),
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
                jQuery('.scheduleDate').val(dateFormated);
                jQuery('.dateSelected').find('.dateSelectedValue').html(dateFormated);
                
                var day = (date.getDate() < 10 ? '0' : '') + date.getDate();
                var month = ((date.getMonth() + 1) < 10 ? '0' : '') + (date.getMonth() + 1);
                var year = date.getFullYear();
                
                var data = year + '-' + month + '-' + day;
                
                agendamentos_atualizar({data});
            }
        }
        
        // ===== Iniciar calendário.
        
        jQuery('.ui.calendar').calendar(calendarDatasOpt);
        
        // ===== Acompanhantes dropdown.

        jQuery('.schedule-states .button').on('mouseup tap',function(e){
            if(e.which != 1 && e.which != 0 && e.which != undefined) return false;

            var obj = this;
            
            jQuery(this).parent().find('.button').each(function(){
                jQuery(this).removeClass('active');
                jQuery(this).find('i').removeClass('check');
                jQuery(this).find('i').removeClass('square outline icon');
                
                if(this !== obj){
                    jQuery(this).find('i').addClass('square outline icon');
                }
            });

            jQuery(this).find('i').addClass('check square outline icon');
            jQuery(this).addClass('active');
        });
        
        jQuery('.ui.dropdown').dropdown({
            onChange: function(value){
                agendamentos_atualizar({status:value});
            }
        });
        
        // ===== Imprimir.
        
        jQuery('.printBtn').on('mouseup tap',function(e){
            if(e.which != 1 && e.which != 0 && e.which != undefined) return false;
            
            window.open(manager.root+"pagina-de-impressao/","Imprimir","menubar=0,location=0,height=700,width=1024");
        });
    }

    function start(){
		if(jQuery('#formSchedules').length > 0){
			agendamentos();
		}
		
		if(jQuery('#_gestor-interface-edit-dados').length > 0 || jQuery('#_gestor-interface-insert-dados').length > 0){
			cupoms_de_prioridade();
		}
	}
	
	start();
});