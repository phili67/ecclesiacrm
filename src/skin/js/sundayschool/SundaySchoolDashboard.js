$("document").ready(function() {
    function BootboxContentAttendees (start) {
        var time_format;
        var fmt = window.CRM.datePickerformat.toUpperCase();

        var dateAttendees = moment(start).format(fmt);


        var frm_str = '<b><p>' + i18next.t("First, set your date an time.") + '</p></b><hr/><form id="some-form">'
            + '<div class="row">'
            + '     <div class="col-md-12">'
            + '         <div class="row">'
            + '             <div class="col-md-3"><span style="color: red">*</span>'
            +                   i18next.t('Date') + ' :'
            + '             </div>'
            + '             <div class="input-group col-md-3">'
            + '                 <div class="input-group-prepend">'
            + '                      <span class="input-group-text"><i class="fa fa-calendar"></i></span>'
            + '                 </div>'
            + '                 <input class="form-control date-picker input-sm" type="text" id="dateAttendees" name="dateAttendees"  value="' + dateAttendees + '" '
            + '                 maxlength="10" id="sel1" size="11"'
            + '                 placeholder="' + window.CRM.datePickerformat + '">'
            + '             </div>'
            + '             <div class="col-md-3"><span style="color: red">*</span>'
            +                   i18next.t('Time') + ' :'
            + '             </div>'
            + '             <div class="input-group col-md-3">'
            + '                 <div class="input-group-prepend">'
            + '                     <span class="input-group-text"><i class="fa fa-clock-o"></i></span>'
            + '                 </div>'
            + '                 <input type="text" class="form-control timepicker input-sm" id="timeAttendees" name="timeAttendees" value="0:00">'
            + '             </div>'
            + '         </div>'
            + '         <br/>'
            + '         <div class="row">'
            + '             <div class="col-md-3">'
            + i18next.t('Event Type') + ' :'
            + '             </div>'
            + '             <div class="col-md-6">'
            + '                 <select class="bootbox-input bootbox-input-select form-control" id="chosenType">'
            + '             </div>'
            + '             <div class="col-md-3">'
            + '             </div>'
            + '        </div>'
            + '     </div>'
            + '</div>';

        var object = $('<div/>').html(frm_str).contents();

        return object
    }

    function addEventTypes(typeId,bAddAttendees)
    {
        window.CRM.APIRequest({
            method: 'GET',
            path: 'events/types',
        }).done(function(eventTypes) {
            var elt = document.getElementById("chosenType");
            var len = eventTypes.length;

            for (i=0; i<len; ++i) {
                var option = document.createElement("option");
                option.text = eventTypes[i].name;
                option.value = eventTypes[i].eventTypeID;

                elt.appendChild(option);
            }
        });
    }

    $(document).on("click", ".makeCheckOut", function () {
        var start = moment().format('YYYY-MM-DD');

        var modal = bootbox.dialog({
            title: i18next.t("Attendance for all sunday groups"),
            message: BootboxContentAttendees(start),
            size: "large",
            buttons: [
                {
                    label: i18next.t("Cancel"),
                    className: "btn btn-default",
                    callback: function () {
                        console.log("just do something on close");
                    }
                },
                {
                    label: i18next.t('OK'),
                    className: "btn btn-primary",
                    callback: function () {
                        var dateAttendees = $('form #dateAttendees').val();
                        var timeAttendees = $('form #timeAttendees').val();
                        var e = document.getElementById("chosenType");
                        var eventTypeID = e.options[e.selectedIndex].value;

                        var fmt = window.CRM.datePickerformat.toUpperCase();

                        if (window.CRM.timeEnglish == 'true') {
                            time_format = 'h:mm A';
                        } else {
                            time_format = 'H:mm';
                        }

                        fmt = fmt+' '+time_format;

                        var real_dateTime = moment(dateAttendees+' '+timeAttendees,fmt).format('YYYY-MM-DD H:mm');

                        window.CRM.APIRequest({
                            method: 'POST',
                            path: 'attendees/groups',
                            data: JSON.stringify({"dateTime":real_dateTime,"eventTypeID": eventTypeID})
                        }).done(function(data) {
                            alert("coucou");
                        });
                    }
                }
            ],
            show: false/*,
         onEscape: function() {
            modal.modal("hide");
         }*/
        });


        modal.modal("show");

        $('.date-picker').datepicker({format: window.CRM.datePickerformat, language: window.CRM.lang});

        //Timepicker
        $('.timepicker').datetimepicker({
            format: 'LT',
            locale: window.CRM.lang,
            icons:
                {
                    up: 'fa fa-angle-up',
                    down: 'fa fa-angle-down'
                }
        });

        addEventTypes ();
    });
});
