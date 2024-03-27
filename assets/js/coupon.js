jQuery( document ).ready( function() {
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

    function start(){
        // Calendar ptBR.
        var calendarPtBR = {
            days: ['D', 'S', 'T', 'Q', 'Q', 'S', 'S'],
            months: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Júlio', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
            monthsShort: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
            today: 'Hoje',
            now: 'Agora',
            am: 'AM',
            pm: 'PM'
        };

        // Calendar configs.
        var calendarConfigStart = {
            type: 'date',
            endCalendar: jQuery( '#rangeend' ),
            formatter: {
                date: function (date) {
                    if (!date) return '';
                    
                    var day = (date.getDate() < 10 ? '0' : '') + date.getDate();
                    var month = ((date.getMonth() + 1) < 10 ? '0' : '') + (date.getMonth() + 1);
                    var year = date.getFullYear();
                    
                    return day + '/' + month + '/' + year;
                }
            }
        };

        var calendarConfigEnd = {
            type: 'date',
            startCalendar: jQuery( '#rangestart' ),
            formatter: {
                date: function (date) {
                    if (!date) return '';
                    
                    var day = (date.getDate() < 10 ? '0' : '') + date.getDate();
                    var month = ((date.getMonth() + 1) < 10 ? '0' : '') + (date.getMonth() + 1);
                    var year = date.getFullYear();
                    
                    return day + '/' + month + '/' + year;
                }
            }
        };

        if( jQuery( '.form-table' ).attr( 'data-locale' ) === "pt_BR" ) {
            calendarConfigStart.text = calendarPtBR;
            calendarConfigEnd.text = calendarPtBR;
        }

        // Options calendar widget.
        jQuery( '#rangestart' ).calendar( calendarConfigStart );
        jQuery( '#rangeend' ).calendar( calendarConfigEnd );

        // Print.
        jQuery( '.printBtn' ).on( 'mouseup tap', function( e ){
            if( e.which != 1 && e.which != 0 && e.which != undefined ) return false;

			var element = jQuery('#fomantic-ui-css');
			var cssUrl = element.attr('href');

			printJS({
				printable: 'popup-content',
				type: 'html',
				css: cssUrl,
				documentTitle: manager.printTitle,
			});
        });

        // Print.
        jQuery( '#print-coupon' ).on( 'mouseup tap', function( e ){
            if( e.which != 1 && e.which != 0 && e.which != undefined ) return false;

			var element = jQuery('#fomantic-ui-css');
			var cssUrl = element.attr('href');

			printJS({
				printable: 'popup-content',
				type: 'html',
				css: cssUrl,
				documentTitle: manager.printTitle,
			});
        });
    }

    start();

} );
