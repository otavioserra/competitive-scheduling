jQuery(document).ready(function(){

    var eventDates = [];

    var calendarPtBR = {
        days: ['D', 'S', 'T', 'Q', 'Q', 'S', 'S'],
        months: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Júlio', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
        monthsShort: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
        today: 'Hoje',
        now: 'Agora',
        am: 'AM',
        pm: 'PM'
    };

    var calendarDatasMultiplasOpt = {
        type: 'date',
        closable: false,
        inline: true,
        formatter: {
            date: function (date, settings) {
                if (!date) return '';
                
                var day = (date.getDate() < 10 ? '0' : '') + date.getDate();
                var month = ((date.getMonth() + 1) < 10 ? '0' : '') + (date.getMonth() + 1);
                var year = date.getFullYear();
                
                return day + '/' + month + '/' + year;
            }
        },
        onChange: function(date,dateFormated){
            var parentCont = jQuery(this).parents('.datas-multiplas');
            var datesStr = parentCont.find('.calendar-dates-input').val();
            var dateFound = false;
            var id = parentCont.attr('data-id');

            if(date === null){
                return;
            }

            var dateFormatedID = (date.getDate() < 10 ? '0' : '') + date.getDate() + '/' + ((date.getMonth() + 1) < 10 ? '0' : '') + (date.getMonth() + 1) + '/' + date.getFullYear();
            
            if(datesStr !== undefined){
                var datesArr = datesStr.split('|');
                
                jQuery.each(datesArr, function(index, date) {
                    if(date == dateFormated){
                        dateFound = true;
                        return false;
                    }
                });
            } else {
                datesStr = '';
            }
            
            if(!dateFound){
                var dateBtn = jQuery('<a class="ui red label transition noselect date-value" data-value="'+dateFormated+'">'+dateFormated+'<i class="delete icon date-delete"></i></a>');
                
                parentCont.find('.calendar-dates').append(dateBtn);
                
                parentCont.find('.calendar-dates-input').val(datesStr + (datesStr.length > 0 ? '|' : '') + dateFormated);

                eventDates[id].push({
                    date,
                    class: 'red',
                    variation: 'red',
                    dateFormatedID
                });
            } else {
                var dateStrNew = '';
                jQuery.each(datesArr, function(index, date) {
                    if(date != dateFormated){
                        dateStrNew = dateStrNew + (dateStrNew.length > 0 ? '|' : '') + date;
                    }
                });

                parentCont.find('.calendar-dates').find('a[data-value="'+dateFormated+'"]').remove();

                parentCont.find('.calendar-dates-input').val(dateStrNew);

                eventDates[id] = eventDates[id].filter((item) => item.dateFormatedID !== dateFormatedID);
            }
            
            calendarDatasMultiplasOpt.eventDates = eventDates[id];
            jQuery(this).calendar('destroy').html('').calendar(calendarDatasMultiplasOpt);
        }
    }

    if(jQuery('.ui.datas-multiplas').attr('data-locale') === "pt_BR") {
        calendarDatasMultiplasOpt.text = calendarPtBR;
    }

    jQuery('.ui.datas-multiplas').each(function(){
        var parentCont = jQuery(this);
        var datesStr = parentCont.find('.calendar-dates-input').val();
        var dates = new Array();
        var id = parentCont.attr('data-id');
        
        if(datesStr !== undefined){
            if(datesStr.length > 0){
                var datesArr = datesStr.split('|');
                
                jQuery.each(datesArr, function(index, dateFormated) {
                    var dateBtn = jQuery('<a class="ui red label transition noselect date-value" data-value="'+dateFormated+'">'+dateFormated+'<i class="delete icon date-delete"></i></a>');
                    
                    parentCont.find('.calendar-dates').append(dateBtn);

                    var dateArr = dateFormated.split('/');
                    var date = new Date(parseInt(dateArr[2]), (parseInt(dateArr[1])-1), parseInt(dateArr[0]));
                    var dateFormatedID = (date.getDate() < 10 ? '0' : '') + date.getDate() + '/' + ((date.getMonth() + 1) < 10 ? '0' : '') + (date.getMonth() + 1) + '/' + date.getFullYear();

                    dates.push({
                        date,
                        class: 'red',
                        variation: 'red',
                        dateFormatedID
                    });
                });
            }
        }
        
        eventDates[id] = dates;
        calendarDatasMultiplasOpt.eventDates = dates;
    });
    jQuery('.ui.calendar.multiplo').calendar(calendarDatasMultiplasOpt);

    jQuery(document.body).on('mouseup tap','.date-value',function(e){
        if(e.which != 1 && e.which != 0 && e.which != undefined) return false;
        
        var parentCont = jQuery(this).parents('.calendar-dates');
        var thisDate = this;
        
        if(e.ctrlKey || e.shiftKey){
            if(e.shiftKey){
                var makeActive = false;
                parentCont.find('.date-value').each(function(){
                    if(thisDate === this || jQuery(this).hasClass('last-active')){
                        if(!makeActive){
                            makeActive = true;
                        } else {
                            return false;
                        }
                    } else {
                        if(makeActive){
                            jQuery(this).addClass('active');
                        }
                    }
                });
            }
        } else {
            parentCont.find('.date-value').each(function(){
                jQuery(this).removeClass('active');
            });
        }
        
        parentCont.find('.date-value').removeClass('last-active');
        
        jQuery(thisDate).addClass('active');
        jQuery(thisDate).addClass('last-active');
    });

    jQuery(document.body).on('mouseup tap','.date-delete',function(e){
        if(e.which != 1 && e.which != 0 && e.which != undefined) return false;
        
        var parentCont = jQuery(this).parents('.calendar-dates');
        var datesInput = jQuery(this).parents('.datas-multiplas').find('.calendar-dates-input');
        var datesStr = datesInput.val();
        var inputRemoveDates = [];
        var id = parentCont.attr('data-id');
        
        var dateObj = jQuery(this).parents('.date-value');
        inputRemoveDates.push(dateObj.attr('data-value'));
        
        dateObj.remove();
        
        parentCont.find('.date-value').each(function(){
            if(jQuery(this).hasClass('active')){
                inputRemoveDates.push(jQuery(this).attr('data-value'));
                jQuery(this).remove();
            }
        });
        
        if(datesStr !== undefined){
            var datesArr = datesStr.split('|');
            var datesUpdated = '';
            
            jQuery.each(datesArr, function(index, currentDate) {
                var found = false;
                jQuery.each(inputRemoveDates, function(index2, removeDate) {
                    if(currentDate == removeDate){
                        found = true;
                        return false;
                    }
                });
                
                if(!found){
                    datesUpdated = datesUpdated + (datesUpdated.length > 0 ? '|' : '') + currentDate;
                } else {
                    eventDates[id] = eventDates[id].filter((item) => item.dateFormatedID !== currentDate);
                }
            });
            
            datesInput.val(datesUpdated);
        }
        
        calendarDatasMultiplasOpt.eventDates = eventDates[id];

        parentCont.parents('.datas-multiplas').find('.ui.calendar.multiplo').calendar('destroy').html('').calendar(calendarDatasMultiplasOpt);
        
        e.stopPropagation();
    });

    var codeMirrorTextArea = jQuery('#codemirror_editor');

    var codeMirrorEditor = CodeMirror.fromTextArea(codeMirrorTextArea[0], {
        mode: 'htmlmixed',
        lineNumbers: true,
        theme: 'default',
        lineWrapping: true,
        styleActiveLine: true,
        matchBrackets: true,
        htmlMode: true,
        indentUnit: 4,
        extraKeys: {
            "F11": function(cm) {
                cm.setOption("fullScreen", !cm.getOption("fullScreen"));
            },
            "Esc": function(cm) {
                if (cm.getOption("fullScreen")) cm.setOption("fullScreen", false);
            }
        }
    });

    codeMirrorEditor.getWrapperElement().style.maxWidth = '1250px';
});