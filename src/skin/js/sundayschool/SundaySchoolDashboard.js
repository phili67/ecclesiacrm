$("document").ready(function() {
    $(document).on("click",".AddAllStudentsToCart", function(){
        clickedButton = $(this);
        window.CRM.cart.addAllStudents(function()
        {
            $(clickedButton).addClass("RemoveAllStudentsFromCart");
            $(clickedButton).removeClass("AddAllStudentsToCart");
            $('i',clickedButton).addClass("fa-times");
            $('i',clickedButton).removeClass("fa-cart-plus");
            text = $(clickedButton).find("span.cartActionDescription")
            if(text){
                $(text).text(i18next.t("Remove Students from Cart"));
            }
        });
    });

    $(document).on("click",".RemoveAllStudentsFromCart", function(){
        clickedButton = $(this);
        window.CRM.cart.removeAllStudents(function()
        {
            $(clickedButton).addClass("AddAllStudentsToCart");
            $(clickedButton).removeClass("RemoveAllStudentsFromCart");
            $('i',clickedButton).removeClass("fa-times");
            $('i',clickedButton).addClass("fa-cart-plus");
            text = $(clickedButton).find("span.cartActionDescription")
            if(text){
                $(text).text(i18next.t("Add Students to Cart"));
            }
        });
    });

    $(document).on("click",".AddAllTeachersToCart", function(){
        clickedButton = $(this);
        window.CRM.cart.addAllTeachers(function()
        {
            $(clickedButton).addClass("RemoveAllTeachersFromCart");
            $(clickedButton).removeClass("AddAllTeachersToCart");
            $('i',clickedButton).addClass("fa-times");
            $('i',clickedButton).removeClass("fa-cart-plus");
            text = $(clickedButton).find("span.cartActionDescription")
            if(text){
                $(text).text(i18next.t("Remove Teachers from Cart"));
            }
        });
    });


    $(document).on("click",".RemoveAllTeachersFromCart", function(){
        clickedButton = $(this);
        window.CRM.cart.removeAllTeachers(function()
        {
            $(clickedButton).addClass("AddAllTeachersToCart");
            $(clickedButton).removeClass("RemoveAllTeachersFromCart");
            $('i',clickedButton).removeClass("fa-times");
            $('i',clickedButton).addClass("fa-cart-plus");
            text = $(clickedButton).find("span.cartActionDescription")
            if(text){
                $(text).text(i18next.t("Add Teachers to Cart"));
            }
        });
    });

    // newMessage event subscribers  : Listener CRJSOM.js
    $(document).on("updateCartMessage", emptyButtons);

    // newMessage event handler
    function emptyButtons(e) {
        if (e.people.length == 0) {
            $("#AddAllTeachersToCart").addClass("AddAllTeachersToCart");
            $("#AddAllTeachersToCart").removeClass("RemoveAllTeachersFromCart");
            $('i',"#AddAllTeachersToCart").removeClass("fa-times");
            $('i',"#AddAllTeachersToCart").addClass("fa-cart-plus");
            text = $("#AddAllTeachersToCart").find("span.cartActionDescription")
            if(text){
                $(text).text(i18next.t("Add Teachers to Cart"));
            }

            $("#AddAllStudentsToCart").addClass("AddAllStudentsToCart");
            $("#AddAllStudentsToCart").removeClass("RemoveAllStudentsFromCart");
            $('i',"#AddAllStudentsToCart").removeClass("fa-times");
            $('i',"#AddAllStudentsToCart").addClass("fa-cart-plus");
            text = $("#AddAllStudentsToCart").find("span.cartActionDescription")
            if(text){
                $(text).text(i18next.t("Add Students to Cart"));
            }
        }
    }

    function BootboxContentAttendees (start) {
        var time_format;
        var fmt = window.CRM.datePickerformat.toUpperCase();

        var dateAttendees = moment(start).format(fmt);


        var frm_str = '<b>' +
            '<p style="color:red">'+
            i18next.t('Be careful! You are about to create or recreate all the events of all the Sunday school classes to call the register.')+
            "<br/>" +
            i18next.t('If the events are already created, go to the "Events" menu and then "Call the register".')+
            '</p>' +
            '<p>' + i18next.t("First, set your date and time.") + '</p>' +
            '</b><hr/><form id="some-form">'
            + '<div class="row">'
            + '     <div class="col-md-12">'
            + '         <div class="row">'
            + '             <div class="col-md-3"><span style="color: red">*</span>'
            +                   i18next.t('Date') + ' :'
            + '             </div>'
            + '             <div class="input-group col-md-3">'
            + '                 <div class="input-group-prepend">'
            + '                      <span class="input-group-text"><i class="fas fa-calendar"></i></span>'
            + '                 </div>'
            + '                 <input class="form-control date-picker form-control-sm" type="text" id="dateAttendees" name="dateAttendees"  value="' + dateAttendees + '" '
            + '                 maxlength="10" id="sel1" size="11"'
            + '                 placeholder="' + window.CRM.datePickerformat + '">'
            + '             </div>'
            + '             <div class="col-md-3"><span style="color: red">*</span>'
            +                   i18next.t('Time') + ' :'
            + '             </div>'
            + '             <div class="input-group col-md-3">'
            + '                 <div class="input-group-prepend">'
            + '                     <span class="input-group-text"><i class="fas fa-clock"></i></span>'
            + '                 </div>'
            + '                 <input type="text" class="form-control timepicker form-control-sm" id="timeAttendees" name="timeAttendees" value="0:00">'
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
        },function(eventTypes) {
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

    $(document).on("click", ".callRegister", function () {
        var start = moment().format('YYYY-MM-DD');

        var modal = bootbox.dialog({
            title: i18next.t("Call the Register for all sunday groups"),
            message: BootboxContentAttendees(start),
            size: "large",
            buttons: [
                {
                    label: '<i class="fas fa-times"></i> ' + i18next.t("Cancel"),
                    className: "btn btn-default",
                    callback: function () {
                        console.log("just do something on close");
                    }
                },
                {
                    label: '<i class="fas fa-check"></i> ' + i18next.t('OK'),
                    className: "btn btn-primary",
                    callback: function () {
                        var dateAttendees = $('form #dateAttendees').val();
                        var timeAttendees = $('form #timeAttendees').val();
                        var e = document.getElementById("chosenType");
                        var eventTypeID = e.options[e.selectedIndex].value;

                        var fmt = window.CRM.datePickerformat.toUpperCase();

                        if (window.CRM.timeEnglish == true) {
                            time_format = 'h:mm A';
                        } else {
                            time_format = 'H:mm';
                        }

                        fmt = fmt+' '+time_format;

                        var real_dateTime = moment(dateAttendees+' '+timeAttendees,fmt).format('YYYY-MM-DD H:mm');

                        window.CRM.APIRequest({
                            method: 'POST',
                            path: 'attendees/groups',
                            data: JSON.stringify({"dateTime":real_dateTime,"eventTypeID": eventTypeID, "rangeInHours": 2})
                        },function(data) {
                            location.href = window.CRM.root + "/Checkin.php";
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
                    up: 'fas fa-angle-up',
                    down: 'fas fa-angle-down'
                }
        });

        addEventTypes ();
    });
});
