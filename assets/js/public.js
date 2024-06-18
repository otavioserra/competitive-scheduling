jQuery( document ).ready( function(){
	function confirmPublic(){
		// Show the public confirmation screen.
		jQuery( '.confirmPublic' ).show();
		
		// Start popup.
		jQuery( '.button' ).popup( { addTouchEvents:false } );
		
		// Confirmation form.
		var formSelector = '.confirmationPublicForm';
		
		jQuery( formSelector )
			.form( {
				
			} );
		
		// Confirmation button.
		jQuery( '.confirmPublicSchedulleBtn' ).on( 'mouseup tap', function(e){
			if( e.which != 1 && e.which != 0 && e.which != undefined ) return false;
			
			loading( 'open' );
			jQuery( formSelector ).find( 'input[name="choice"]' ).val( 'confirm' );
			jQuery( formSelector ).form( 'submit' );
		} );
		
		// Cancel button.
		jQuery( '.cancelPublicSchedulingBtn' ).on( 'mouseup tap', function(e){
			if( e.which != 1 && e.which != 0 && e.which != undefined ) return false;
			
			loading( 'open' );
			jQuery( formSelector ).form( 'submit' );
		} );
	}
	
	function cancelPublic(){
		// Show the public cancellation screen.
		jQuery( '.cancelPublic' ).show();
		
		// Start popup.
		jQuery( '.button' ).popup( { addTouchEvents:false } );
		
		// Confirmation form.
		var formSelector = '.cancellationPublicoForm';
		
		jQuery( formSelector )
			.form( {
				
			} );
		
		// Cancel button.
		jQuery( '.cancelPublicSchedulingBtn' ).on( 'mouseup tap', function(e){
			if( e.which != 1 && e.which != 0 && e.which != undefined ) return false;

			loading( 'open' );
			jQuery( formSelector ).form( 'submit' );
		} );
	}

	function expiredOrNotFound(){
		// Show the public expired or not found.
		
		jQuery( '.expiredOrNotFound' ).show();
	}

	function errorInfo(){
		// Show the public error info.
		
		jQuery( '.errorInfo' ).show();
	}

	function successInfo(){
		// Show the public success info.
		
		jQuery( '.successInfo' ).show();
	}
	
	function calendarShortcode(){
		// Calendar settings.
        var calendar = manager.calendar;
        var root = manager.root;
        
        // Dates available for scheduling.
        var availableDates = [];
        
        for( var date in calendar.available_dates ){
            var dateObj = new Date( date.replace( /-/g, '\/' ) ); // Bug in the javascript Date() object. Just change the '-' to '/' and the date works correctly. Otherwise it will be one day longer than the correct day.
            
            availableDates.push(dateObj);
        }
        
        // ptBR calendar.
        var calendarPtBR = {
            days: ['D', 'S', 'T', 'Q', 'Q', 'S', 'S'],
            months: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
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
	}
	
	function start(){
		// Handle scheduling changes.
		
		if( 'confirm' in cs_manager ){ confirmPublic(); }
		if( 'cancel' in cs_manager ){ cancelPublic(); }
		if( 'expiredOrNotFound' in cs_manager ){ expiredOrNotFound(); }
		if( 'errorInfo' in cs_manager ){ errorInfo(); }
		if( 'successInfo' in cs_manager ){ successInfo(); }
		if( 'calendarShortcode' in cs_manager ){ calendarShortcode(); }
	}
	
	start();

	function loading( option ){
		switch( option ){
			case 'open':
				if( ! ('loading' in cs_manager ) ){
					jQuery('.page.dimmer').dimmer({
						closable: false,
						onVisible: function(){
							jQuery('.page.dimmer').removeClass( 'transition' );
						}
					});
					
					cs_manager.loading = true;
				}
				
				jQuery('.page.dimmer').dimmer('show');
				jQuery('.page.dimmer').removeClass( 'transition' );
			break;
			case 'close':
				jQuery('.page.dimmer').dimmer('hide');
			break;
		}
	}
	
} );