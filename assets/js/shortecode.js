jQuery( document ).ready( function(){
    function confirm(){
		// Show the confirmation screen.
		jQuery('.confirm').show();
		
		// Start popup.
		jQuery('.button').popup({addTouchEvents:false});
		
		// Form confirm.
		var formSelector = '.confirmationForm';
		
		jQuery(formSelector)
			.form({
				
			});
		
		// Confirmation button.
		jQuery('.confirmScheduleBtn').on('mouseup tap',function(e){
			if(e.which != 1 && e.which != 0 && e.which != undefined) return false;
			
			jQuery(formSelector).find('input[name="choice"]').val('confirm');
			jQuery(formSelector).form('submit');
		});
		
		// Cancel button.
		jQuery('.cancelSchedulingBtn').on('mouseup tap',function(e){
			if(e.which != 1 && e.which != 0 && e.which != undefined) return false;
			
			jQuery(formSelector).form('submit');
		});
	}
	
	function cancel(){
		// Show the public confirmation screen.
		jQuery('.cancel').show();
		
		// Start popup.
		jQuery('.button').popup({addTouchEvents:false});
		
		// Form confirm.
		var formSelector = '.cancelForm';
		
		jQuery(formSelector)
			.form({
				
			});
		
		// Cancel button.
		
		jQuery('.cancelSchedulingBtn').on('mouseup tap',function(e){
			if(e.which != 1 && e.which != 0 && e.which != undefined) return false;
			
			jQuery(formSelector).form('submit');
		});
	}
	
	function expiredOrNotFound(){
		// Show the public confirmation screen.
		
		jQuery('.ExpiredOrNotFound').show();
	}
	
	function schedulingActive(){
		// Calendar settings.
		var calendar = manager.calendar;
		
		// Texts settings.
		var texts = manager.texts;
		
		// Dates available for scheduling.
		var availableDates = [];
		
		for( var date in calendar.available_dates ){
			var dateObj = new Date(date.replace(/-/g, '\/')); // Bug in the javascript Date() object. Just change the '-' to '/' and the date works correctly. Otherwise it will be one day longer than the correct day.
			
			availableDates.push(dateObj);
		}
		
		// Form Schedules.
		var formId = 'formSchedules';
		var formSelector = '#formSchedules';
		
		jQuery( formSelector )
			.form({
				fields : ( manager.form[formId].validationRules ? manager.form[formId].validationRules : {} ),
				onSuccess( event, fields ){
					
				}
			});
		
		// Mask priority coupon.
		jQuery( '.coupon' ).mask( 'AAAA-AAAA', { clearIfNotMatch: true } );
		
		// ptBR Calendar.
		var calendarPtBR = {
			days: ['D', 'S', 'T', 'Q', 'Q', 'S', 'S'],
			months: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
			monthsShort: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
			today: 'Hoje',
			now: 'Agora',
			am: 'AM',
			pm: 'PM'
		};
		
		// Variables of the 'calendar' component multiple-dates.
		var calendarDatesOpt = {
			text: calendarPtBR,
			type: 'date',
			inline: true,
			initialDate: new Date(),
			minDate: new Date(calendar.start_year+'/01/01'),
			maxDate: new Date(calendar.year_end+'/12/31'),
			eventClass: 'inverted blue',
			enabledDates: availableDates,
			eventDates: availableDates,
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
				jQuery(this).parent().find('.scheduleDate').val(dateFormated);
				jQuery(this).parent().find('.dateSelected').show();
				jQuery(this).parent().find('.dateSelected').find('.dateSelectedValue').html(dateFormated);
				
				jQuery(formSelector).form('validate form');
			}
		}
		
		// Start calendar.
		jQuery( '.ui.calendar' ).calendar( calendarDatesOpt );
		
		// Start popup.
		jQuery( '.button' ).popup( { addTouchEvents:false } );
		
		// Companions dropdown.
		jQuery( '.ui.dropdown' ).dropdown( {
			onChange: function( value ){
				var parentObj = jQuery( this ).parents( '.field' );
				var companionsCont = parentObj.find('.companionsCont');
				var companionsTemplateCont = parentObj.find('.companionsTemplateCont');
				var numComp = companionsCont.find('.field').length;
				
				value = parseInt(value);
				
				if(value > numComp){
					for(var i=numComp;i<value;i++){
						var field = companionsTemplateCont.find('.field').clone();
						var num = (i+1);
						
						field.attr('data-num',num);
						field.find('label').html(texts['companion-label']+' '+num);
						field.find('input').prop('name','companion-'+num);
						field.find('input').prop('placeholder',texts['companion-label']+' '+num);
						field.find('input').attr('data-validate','companion'+num);
						
						companionsCont.append(field);
						
						jQuery(formSelector).form('add rule', ('companion'+num),{ rules : manager.form[formId].validationRules[('companion'+num)].rules });
					}
				} else {
					var num = 0;
					
					companionsCont.find('.field').each(function(){
						num++;
						
						jQuery(formSelector).form('remove fields', ['companion'+num]);
						
						if(num > value){
							jQuery(this).hide();
						} else {
							jQuery(this).show();
							jQuery(formSelector).form('add rule', ('companion'+num),{ rules : manager.form[formId].validationRules[('companion'+num)].rules });
						}
					});
				}
			}
		});
		
		// Screen treatment.
		jQuery('.scheduleBtn').on('mouseup tap',function(e){
			if(e.which != 1 && e.which != 0 && e.which != undefined) return false;
			
			jQuery('.scheduleWindow').hide();
			jQuery('.schedule').show();
		});
		
		jQuery('.schedulesBtn').on('mouseup tap',function(e){
			if(e.which != 1 && e.which != 0 && e.which != undefined) return false;
			
			jQuery('.scheduleWindow').hide();
			jQuery('.schedules').show();
		});
		
		if('window' in manager){
			switch(manager.window){
				case 'previous-schedules':
					jQuery('.schedules').show();
				break;
				default:
					jQuery('.schedule').show();
			}
		} else {
			jQuery('.schedule').show();
		}
		
		// Schedule information tab.
		jQuery( '.tabular.menu .item' ).tab();
		
		// Confirm button.
		jQuery( document.body ).on( 'mouseup tap', '.confirmScheduleBtn', function(e){
			if( e.which != 1 && e.which != 0 && e.which != undefined ) return false;
			
			var schedule_id = jQuery(this).attr('data-id');
			
			window.open("?action=confirm&schedule_id="+schedule_id,"_self");
		});
		
		// Cancel button.
		jQuery(document.body).on('mouseup tap','.cancelSchedulingBtn',function(e){
			if(e.which != 1 && e.which != 0 && e.which != undefined) return false;
			
			var schedule_id = jQuery(this).attr('data-id');
			
			window.open("?action=cancel&schedule_id="+schedule_id,"_self");
		});
		
		// Appointment information.
		jQuery(document.body).on('mouseup tap','.dataScheduleBtn',function(e){
			if(e.which != 1 && e.which != 0 && e.which != undefined) return false;
			
			// Check the type of appointment.
			var type = '';
			if(jQuery(this).hasClass('preAgendamento')){type = 'preAgendamento';}
			if(jQuery(this).hasClass('agendamento')){type = 'agendamento';}
			if(jQuery(this).hasClass('agendamentoAntigo')){type = 'agendamentoAntigo';}
			
			// Search the data on the server and display the result on the screen.
			var option = 'schedules-host';
			var ajaxOption = 'scheduling-data';
			
			$.ajax({
				type: 'POST',
				url: manager.root + 'schedules/',
				data: {
					option : option,
					ajax : 'sim',
					ajaxPage : 'sim',
					ajaxOption : ajaxOption,
					type : type,
					schedule_id : jQuery(this).attr('data-id')
				},
				dataType: 'json',
				beforeSend: function(){
					loading('open');
				},
				success: function(data){
					switch(data.status){
						case 'OK':
							modal({message:data.dataSchedules});
						break;
						case 'ERROR':
							modal({message:data.msg});
						break;
						default:
							console.log('ERROR - '+option+' - '+data.status);
							loading('close');
						
					}
				},
				error: function(txt){
					switch(txt.status){
						case 401: window.open(manager.root + (txt.responseJSON.redirect ? txt.responseJSON.redirect : "signin/"),"_self"); break;
						default:
							console.log('ERROR AJAX - '+option+' - Data:');
							console.log(txt);
							loading('close');
					}
				}
			});
		});
		
		// Rules for reading more appointment entries.
		var loadObjs = {};
		var button_id = '.loadMorePre,.loadMoreAppointments,.loadOldest';
		
		jQuery(button_id).on('mouseup tap',function(e){
			if(e.which != 1 && e.which != 0 && e.which != undefined) return false;
			
			var obj = this;
			
			// Check the type of appointment.
			var type = '';
			if(jQuery(obj).hasClass('loadMorePre')){type = 'loadMorePre';}
			if(jQuery(obj).hasClass('loadMoreAppointments')){type = 'loadMoreAppointments';}
			if(jQuery(obj).hasClass('loadOldest')){type = 'loadOldest';}
			
			// Load objects.
			if(!(type in loadObjs)){
				loadObjs[type] = {
					maxPages : parseInt(jQuery(obj).attr('data-num-pages')),
					actualPage : 0
				};
			}
			
			// Load data from server.
			var option = 'schedules-host';
			var ajaxOption = 'more-results';
			
			loadObjs[type].actualPage++;
			
			var actualPage = loadObjs[type].actualPage;
			
			$.ajax({
				type: 'POST',
				url: manager.root + 'schedules/',
				data: { 
					option,
					ajax : 'sim',
					ajaxPage : 'sim',
					ajaxOption,
					type,
					actualPage
				},
				dataType: 'json',
				beforeSend: function(){
					loading('open');
				},
				success: function(data){
					switch(data.status){
						case 'OK':
							// Include records in tables corresponding to scheduling types.
							switch(type){
								case 'loadMorePre': jQuery('.tabelaPreAgendamentos').append(data.records); break;
								case 'loadMoreAppointments': jQuery('.tabelaAgendamentos').append(data.records); break;
								case 'loadOldest': jQuery('.tabelaAgendamentosAntigos').append(data.records); break;
							}
							
							// Hide the button when you reach the last page.
							if(loadObjs[type].actualPage >= loadObjs[type].maxPages - 1){
								jQuery(obj).parent().hide();
							}
							
							// Start popup.
							jQuery('.button').popup({addTouchEvents:false});
						break;
						default:
							console.log('ERROR - '+option+' - '+data.status);
						
					}
					
					loading('close');
				},
				error: function(txt){
					switch(txt.status){
						case 401: window.open(manager.root + (txt.responseJSON.redirect ? txt.responseJSON.redirect : "signin/"),"_self"); break;
						default:
							console.log('ERROR AJAX - '+option+' - Data:');
							console.log(txt);
							loading('close');
					}
				}
			});
		});
		
		function loading(option){
			switch(option){
				case 'open':
					if(!('loading' in manager)){
						jQuery('.pageLoading').dimmer({
							closable: false
						});
						
						manager.loading = true;
					}
					
					jQuery('.pageLoading').dimmer('show');
				break;
				case 'close':
					jQuery('.pageLoading').dimmer('hide');
				break;
			}
		}
		
		function modal(p={}){
			if(p.message){
				jQuery('.ui.modal.info .content').html(p.message);
			}
			
			jQuery('.ui.modal.info').modal({
				dimmerSettings:{
					dimmerName:'pageLoading' //className, NOT id (!)
				}
			}).modal('show');
		}
	}

    function alert(p={}){
		if(p.msg){
			jQuery('.ui.modal.alert .content p').html(p.msg);
		}
		
		jQuery('.ui.modal.alert').modal('show');
	}
	
	function start(){
		// Active scheduling.
		if( jQuery('.active-scheduling').length > 0 ){ schedulingActive(); }
		
		// Handle scheduling changes.
		if('confirm' in manager){ confirm(); }
		if('cancel' in manager){ cancel(); }
		if('ExpiredOrNotFound' in manager){ expiredOrNotFound(); }

        // Alert
        if('interface' in manager){
            if('alert' in manager.interface){
				alert(manager.interface.alert);
			}
		}
	}
	
	start();
});