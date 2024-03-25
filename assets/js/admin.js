jQuery( document ).ready( function(){
	var apiVersion = 'v1';

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
			// Get the nonce of appointments.
			var nonce = jQuery('input[name="schedules-nonce"]').val();

			// Set up the data object.
			var data = {
				option : 'schedules',
				date : manager.schedules.date,
				status : manager.schedules.status,
				nonce
			};

			// Request to update schedules as option.
			jQuery.ajax( {
				url: wpApiSettings.root + 'competitive-scheduling/'+apiVersion+'/admin-page/',
				method: 'GET',
				xhrFields: { withCredentials: true },
				beforeSend: function ( xhr ) {
					xhr.setRequestHeader( 'X-WP-Nonce', wpApiSettings.nonce );
					loading( 'open' );
				},
				data
			} ).done( function ( response ) {
				if( response.status === 'OK' ){
					// Set up the table.
					jQuery( '.tablePeople' ).html( response.table );
							
					// Update the total number of people.
					jQuery( '.totalValue' ).html( response.total );
					
					// Show or not the print button.
					if( response.print ){
						jQuery( '.printBtn' ).removeClass( 'hidden' ).show();
					} else {
						jQuery( '.printBtn' ).addClass( 'hidden' ).hide();
					}
					
					// Show results information containers.
					jQuery( '.totalPeople' ).show();
					jQuery( '.tablePeople' ).show();

					jQuery( '#popup-content' ).html( response.tablePrint );

					if( 'printTitle' in response ){
						manager.printTitle = response.printTitle;
					}
				}

				if( 'nonce' in response ){
					jQuery( 'input[name="schedules-nonce"]' ).val( response.nonce );
				}

				loading('close');
			} ).fail( function ( response ) {
				loading('close');
				if( 'responseJSON' in response ){ console.log( response.status + ': ' + response.responseJSON.message ); } else { console.log( response ); }
			} );
		}
	}
    
    function schedules(){
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
		var lastButton;
        jQuery( '.schedule-states .button' ).on( 'mouseup tap', function( e ){
            if( e.which != 1 && e.which != 0 && e.which != undefined ) return false;

            var obj = this;

			if( lastButton !== obj ){	
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

				var status = jQuery( obj ).attr( 'data-value' );

				schedules_update( { status } );

				lastButton = obj;
			}
        } );
        
        // Print.
        jQuery( '.printBtn' ).on( 'mouseup tap', function( e ){
            if( e.which != 1 && e.which != 0 && e.which != undefined ) return false;
			
			var element = jQuery('#fomantic-ui-css');
			var ajaxurl = element.attr('href');

			var xhr = new XMLHttpRequest();
			xhr.open('POST', ajaxurl);
			xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
			xhr.onload = function() {
			if (xhr.status === 200) {
				var css = xhr.responseText;

				// Create a regular expression to find url() references.
				var regex = /url\((.*?)\)/g;

				// Replace url() references with the root variable.
				var newCss = css.replace(regex, function(match, url) {
					return 'url(' + root + url + ')';
				});

				// Print the stylesheet on the page.
				var popupWindow = window.open('', 'Print', 'menubar=0,location=0,width=600,height=400');

				// Set the page title in the print window.
				popupWindow.document.write('<title>'+manager.printTitle+'</title>');
				popupWindow.document.write('<style>'+newCss+'</style>');
				popupWindow.document.write(document.getElementById('popup-content').innerHTML);

				// Start printing.
				popupWindow.print({
					printJob: {
						filename: manager.printTitle + '.pdf'
					}
				});
			} else {
				console.log('Error loading stylesheet');
			}
			};
			xhr.send('action=wp_print_styles&stylesheet=' + ajaxurl);

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
				if( ! ('loading' in manager ) ){
					jQuery('.page.dimmer').dimmer({
						closable: false,
					});
					
					manager.loading = true;
				}
				
				jQuery('.page.dimmer').dimmer('show');
			break;
			case 'close':
				jQuery('.page.dimmer').dimmer('hide');
			break;
		}
	}
	
} );