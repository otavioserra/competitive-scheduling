jQuery( document ).ready( function() {
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
} );
