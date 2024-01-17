jQuery(document).ready(function(){
    function priority_coupons(){
		// Quantity mask.
		jQuery( '.amount' ).mask( "000", { reverse: true } );
		
		// Options calendar widget.
		jQuery( '#rangestart' ).calendar({
			type: 'date',
			endCalendar: jQuery( '#rangeend' ),
			formatter: {
				date: function ( date ) {
					if ( ! date ) return '';
					
					var day = ( date.getDate() < 10 ? '0' : '' ) + date.getDate();
					var month = ( ( date.getMonth() + 1 ) < 10 ? '0' : '' ) + ( date.getMonth() + 1 );
					var year = date.getFullYear();
					
					return day + '/' + month + '/' + year;
				}
			}
		});
		jQuery( '#rangeend' ).calendar({
			type: 'date',
			startCalendar: jQuery('#rangestart'),
			formatter: {
				date: function ( date ) {
					if ( ! date ) return '';
					
					var day = ( date.getDate() < 10 ? '0' : '' ) + date.getDate();
					var month = ( ( date.getMonth() + 1 ) < 10 ? '0' : '' ) + ( date.getMonth() + 1 );
					var year = date.getFullYear();
					
					return day + '/' + month + '/' + year;
				}
			}
		});
		
		// Request to print coupons.
		jQuery( '.printCoupons' ).on( 'mouseup tap', function( e ){
			if( e.which != 1 && e.which != 0 && e.which != undefined ) return false;
			
			window.open(manager.root+"pagina-de-impressao/","Imprimir","menubar=0,location=0,height=700,width=1024");
		});
	}
	
	function schedules_update(p={}){
		// If it does not exist, create the control object.
		if( ! ( 'schedules' in manager ) ){
			manager.schedules = {};
		}
		
		// Modify as submitted.
		if( 'date' in p ){ manager.schedules.date = p.date; }
		if( 'status' in p ){ manager.schedules.status = p.status; }
		
		// Manually hide unnecessary containers due to fomantic-ui components showing themselves at the beginning of the DOM.
		if(
			! ( 'date' in manager.schedules) || 
			! ( 'status' in manager.schedules )
		){
			jQuery( '.printBtn' ).hide();
			jQuery( '.tablePeople' ).hide();
		}
		
		// Show the results container.
		if(
			( 'date' in manager.schedules )
		){
			jQuery( '.resultados' ).show();
		}
		
		// Only update if 'data' and 'status' are defined.
		if(
			( 'date' in manager.schedules ) && 
			( 'status' in manager.schedules )
		){
			// Request to update schedules as option.
			var option = 'schedules';
			var ajaxOpcao = 'update';
			
			$.ajax({
				type: 'POST',
				url: manager.root + manager.moduloId + '/',
				data: {
					option : option,
					ajax : 'sim',
					ajaxOpcao : ajaxOpcao,
					ajaxPagina : 'sim',
					date : manager.schedules.date,
					status : manager.schedules.status
				},
				dataType: 'json',
				beforeSend: function(){
					loading( 'open' );
				},
				success: function( data ){
					switch( data.status ){
						case 'OK':
							// Set up the table.
							jQuery( '.tablePeople' ).html( data.table );
							
							// Update the total number of people.
							jQuery( '.totalValue' ).html( data.total );
							
							// Show or not the print button.
							if( data.print ){
								jQuery( '.printBtn' ).show();
							} else {
								jQuery( '.printBtn' ).hide();
							}
							
							// Show results information containers.
							jQuery( '.totalPeople' ).show();
							jQuery( '.tablePeople' ).show();
						break;
						default:
							console.log('ERROR - '+option+' - '+data.status);
					}
					
					loading( 'close' );
				},
				error: function(txt){
					switch(txt.status){
						case 401: window.open(manager.root + (txt.responseJSON.redirect ? txt.responseJSON.redirect : "signin/"),"_self"); break;
						default:
							console.log('ERROR AJAX - '+option+' - Dados:');
							console.log(txt);
							loading( 'close' );
					}
				}
			});
		}
	}
    
    function schedules(){
        // Calendar settings.
        var calendar = manager.calendar;
        
        // Dates available for scheduling.
        var availableDates = [];
        
        for( var date in calendar.available_dates ){
            var dateObj = new Date( date.replace( /-/g, '\/' ) ); // Bug in the javascript Date() object. Just change the '-' to '/' and the date works correctly. Otherwise it will be one day longer than the correct day.
            
            availableDates.push(dateObj);
        }
        
        // ptBR calendar.
        var calendarPtBR = {
            days: ['D', 'S', 'T', 'Q', 'Q', 'S', 'S'],
            months: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Júlio', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
            monthsShort: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
            today: 'Hoje',
            now: 'Agora',
            am: 'AM',
            pm: 'PM'
        };
        
        // Variables of the 'calendar' component.
        var calendarDatasOpt = {
            text: calendarPtBR,
            type: 'date',
            inline: true,
            initialDate: new Date(),
            minDate: new Date( calendar.start_year+'/01/01' ),
            maxDate: new Date( calendar.year_end+'/12/31' ),
            eventClass: 'inverted blue',
            enabledDates: availableDates,
            eventDates: availableDates,
            formatter: {
                date: function ( date ) {
                    if ( ! date ) return '';
                    
                    var day = ( date.getDate() < 10 ? '0' : '' ) + date.getDate();
                    var month = ( ( date.getMonth() + 1 ) < 10 ? '0' : '' ) + ( date.getMonth() + 1 );
                    var year = date.getFullYear();
                    
                    return day + '/' + month + '/' + year;
                }
            },
            onChange: function( date, dateFormated ){
                jQuery( '.scheduleDate' ).val( dateFormated );
                jQuery( '.dateSelected' ).find( '.dateSelectedValue' ).html( dateFormated );
                
                var day = ( date.getDate() < 10 ? '0' : '' ) + date.getDate();
                var month = ( ( date.getMonth() + 1 ) < 10 ? '0' : '' ) + ( date.getMonth() + 1 );
                var year = date.getFullYear();
                
                var date = year + '-' + month + '-' + day;
                
                schedules_update( { date } );
            }
        }
        
        // Start calendar.
        jQuery( '.ui.calendar' ).calendar( calendarDatasOpt );
        
        // Escorts dropdown.
        jQuery( '.schedule-states .button' ).on( 'mouseup tap', function(e){
            if( e.which != 1 && e.which != 0 && e.which != undefined ) return false;

            var obj = this;
            
            jQuery( this ).parent().find( '.button' ).each( function(){
                jQuery( this ).removeClass( 'active' );
                jQuery( this ).find( 'i' ).removeClass( 'check' );
                jQuery( this ).find( 'i' ).removeClass( 'square outline icon' );
                
                if( this !== obj ){
                    jQuery( this ).find( 'i' ).addClass( 'square outline icon' );
                }
            });

            jQuery( this ).find( 'i' ).addClass( 'check square outline icon' );
            jQuery( this ).addClass( 'active' );
        });
        
        jQuery( '.ui.dropdown' ).dropdown( {
            onChange: function( value ){
                schedules_update( { status:value } );
            }
        } );
        
        // Imprimir.
        
        jQuery( '.printBtn' ).on( 'mouseup tap', function( e ){
            if( e.which != 1 && e.which != 0 && e.which != undefined ) return false;
            
            window.open(manager.root+"pagina-de-impressao/","Print","menubar=0,location=0,height=700,width=1024");
        });
    }

    function start(){
		if( jQuery('#formSchedules').length > 0 ){
			schedules();
		}
		
		if( jQuery( '#_gestor-interface-edit-dados' ).length > 0 || jQuery( '#_gestor-interface-insert-dados' ).length > 0 ){
			priority_coupons();
		}
	}
	
	start();

	function loading( option ){
		switch( option ){
			case 'open':
				if( ! ( 'loading' in manager ) ){
					jQuery( '.pageLoading' ).dimmer( {
						closable: false
					} );
					
					manager.loading = true;
				}
				
				jQuery( '.pageLoading' ).dimmer('show');
			break;
			case 'close':
				jQuery( '.pageLoading' ).dimmer('hide');
			break;
		}
	}
	
});