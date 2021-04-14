//
//  This code is under copyright not under MIT Licence
//  copyright   : 2020 Philippe Logel all right reserved not MIT licence
//                This code can't be included in another software.
//  Updated     : 2020/05/07
//

var anniversary = true;
var birthday = true;
var withlimit = false;
var eventCreated = false;
var eventAttendees = false;

window.CRM.eventID = 0;

var birthD = localStorage.getItem("birthday");
if (birthD != null) {
    if (birthD == 'checked') {
        birthday = true;
    } else {
        birthday = false;
    }

    $('#isBirthdateActive').prop('checked', birthday);
}

var ann = localStorage.getItem("anniversary");
if (ann != null) {
    if (ann == 'checked') {
        anniversary = true;
    } else {
        anniversary = false;
    }

    $('#isAnniversaryActive').prop('checked', anniversary);
}

var wLimit = localStorage.getItem("withlimit");
if (wLimit != null) {
    if (wLimit == 'checked') {
        withlimit = true;
    } else {
        withlimit = false;
    }

    $('#isWithLimit').prop('checked', withlimit);
}

$("#isBirthdateActive").on('change', function () {
    var _val = $(this).is(':checked') ? 'checked' : 'unchecked';

    if (_val == 'checked') {
        birthday = true;
    } else {
        birthday = false;
    }
    window.CRM.calendar.refetchEvents();

    localStorage.setItem("birthday", _val);
});

$("#isAnniversaryActive").on('change', function () {
    var _val = $(this).is(':checked') ? 'checked' : 'unchecked';
    if (_val == 'checked') {
        anniversary = true;
    } else {
        anniversary = false;
    }

    window.CRM.calendar.refetchEvents();

    localStorage.setItem("anniversary", _val);
});

$("#isWithLimit").on('change', function () {
    var _val = $(this).is(':checked') ? 'checked' : 'unchecked';
    if (_val == 'checked') {
        withlimit = true;
    } else {
        withlimit = false;
    }

    var options = window.CRM.calendar.getOption('eventLimit');
    window.CRM.calendar.setOption('eventLimit', withlimit);

    window.CRM.calendar.refetchEvents()

    localStorage.setItem("withlimit", _val);
});

window.calendarFilterID = 0;
window.CRM.EventTypeFilterID = 0;

localStorage.setItem("calendarFilterID", window.calendarFilterID);
localStorage.setItem("EventTypeFilterID", window.CRM.EventTypeFilterID);

$("#EventCalendarFilter").on('change', function () {
    var e = document.getElementById("EventCalendarFilter");
    window.calendarFilterID = e.options[e.selectedIndex].value;

    window.CRM.calendar.refetchEvents();

    if (window.calendarFilterID == 0)
        $("#ATTENDENCES").parents("tr").hide();

    localStorage.setItem("calendarFilterID", calendarFilterID);
});


$("#EventTypeFilter").on('change', function () {
    var e = document.getElementById("EventTypeFilter");
    window.CRM.EventTypeFilterID = e.options[e.selectedIndex].value;

    window.CRM.calendar.refetchEvents();

    localStorage.setItem("EventTypeFilterID", window.calendarFilterID);
});

$('body').on('click', '.date-title, .date-range', function () {
    $(".date-title").slideUp();
    $('.ATTENDENCES-title').slideDown();
    $(".map-title").slideUp();
    $('.date-start').slideDown();
    $('.date-end').slideDown();
    $('.date-recurrence').slideDown();
    $(".ATTENDENCES").slideUp();
    $(".eventNotes").slideUp();
    $('#EventDesc').attr("rows", "1");
});

$('body').on('click', '#EventTitle, .EventTitle', function () {
    $(".date-title").slideDown();
    $('.ATTENDENCES-title').slideDown();
    $(".map-title").slideUp();
    $('.date-start').slideUp();
    $('.date-end').slideUp();
    $('.date-recurrence').slideUp();
    $(".ATTENDENCES").slideUp();
    $(".eventNotes").slideUp();
    $('#EventDesc').attr("rows", "1");
});

$('body').on('click', '#EventLocation, .EventLocation', function () {
    $(".map-title").slideDown();
    $('.ATTENDENCES-title').slideDown();
    $('.date-start').slideUp();
    $('.date-end').slideUp();
    $('.date-recurrence').slideUp();
    $(".ATTENDENCES").slideUp();
    $(".eventNotes").slideUp();
    $('#EventDesc').attr("rows", "1");

    updateMap();
});


$('body').on('click', '#EventDesc, .EventDesc', function () {
    $(".date-title").slideDown();
    $('.ATTENDENCES-title').slideDown();
    $(".map-title").slideUp();
    $('.date-start').slideUp();
    $('.date-end').slideUp();
    $('.date-recurrence').slideUp();
    $(".ATTENDENCES").slideUp();
    $(".eventNotes").slideUp();
    $('#EventDesc').attr("rows", "3");
});

$('body').on('click', '#ATTENDENCES-title, .ATTENDENCES-title', function () {
    $(".date-title").slideDown();
    //$('.ATTENDENCES-title').slideUp();
    $(".map-title").slideUp();
    $('.date-start').slideUp();
    $('.date-end').slideUp();
    $('.date-recurrence').slideUp();
    $(".eventNotes").slideUp();
    $(".ATTENDENCES").slideDown("slow");
    $('#EventDesc').attr("rows", "1");
});

$('body').on('click', '.calendar-title', function () {
    $(".date-title").slideDown();
    //$('.ATTENDENCES-title').slideUp();
    $(".map-title").slideUp();
    $('.date-start').slideUp();
    $('.date-end').slideUp();
    $('.date-recurrence').slideUp();
    $(".eventNotes").slideUp();
    $(".ATTENDENCES").slideUp("slow");
    $('#EventDesc').attr("rows", "1");
});

// I have to do this because EventCalendar isn't yet present when you load the page the first time
$(document).on('change', '#EventCalendar', function () {
    $(".date-title").slideDown();
    $('.ATTENDENCES-title').slideDown();
    $(".ATTENDENCES").slideUp();
    $(".map-title").slideUp();
    $('.date-start').slideUp();
    $('.date-end').slideUp();
    $('.date-recurrence').slideUp();
    $(".eventNotes").slideUp();
    $('#EventDesc').attr("rows", "1");

    var e = document.getElementById("EventCalendar");
    var _val = e.options[e.selectedIndex].value;
    var _grpID = e.options[e.selectedIndex].getAttribute("data-calendar-id");

    /*if (_val == 0)
      $( ".ATTENDENCES" ).slideUp();
    else
      $( ".ATTENDENCES" ).slideDown( "slow");*/

    $("#addGroupAttendees").prop("disabled", (_grpID == "0") ? true : false);
    $("#addGroupAttendees").prop('checked', (_grpID == "0") ? false : true);

    localStorage.setItem("calendarFilterID", calendarFilterID);
});

$('body').on('click', '.eventNotesTitle', function () {
    $(".date-title").slideDown();
    $('.ATTENDENCES-title').slideDown();
    $(".map-title").slideUp();
    $('.date-start').slideUp();
    $('.date-end').slideUp();
    $('.date-recurrence').slideUp();
    $(".ATTENDENCES").slideUp();
    $(".eventNotes").slideDown();
    $('#EventDesc').attr("rows", "1");
});

// I have to do this because EventCalendar isn't yet present when you load the page the first time
$(document).on('change', '#checkboxEventrecurrence', function (value) {
    var _val = $('#checkboxEventrecurrence').is(":checked");

    $("#typeEventrecurrence").prop("disabled", (_val == 0) ? true : false);
    $("#endDateEventrecurrence").prop("disabled", (_val == 0) ? true : false);
});

$(document).on('change', '#eventType', function (val) {
    var e = document.getElementById("eventType");
    var typeID = e.options[e.selectedIndex].value;

    addAttendees(typeID, false);
});


function addAttendees(typeID, first_time, eventID) {
    if (first_time === undefined) {
        first_time = true;
    }

    if (window.CRM.eventID > 0) {
        eventID = window.CRM.eventID;
    }

    if (eventID === undefined) {
        eventID = 0;
    }


    if (first_time) {
        $('.ATTENDENCES-title').slideDown();
    }

    $('.date-title').slideDown();

    $('.date-start').slideUp();
    $('.date-end').slideUp();
    $('.date-recurrence').slideUp();
    $('.eventNotes').slideUp();
    $(".map-title").slideUp();

    window.CRM.APIRequest({
        method: 'POST',
        path: 'events/attendees',
        data: JSON.stringify({"typeID": typeID, "eventID": eventID})
    }).done(function (eventTypes) {
        var len = eventTypes.length;

        if (len == 0) {
            $('.ATTENDENCES-title').slideDown();
            $(".ATTENDENCES-fields").empty();
            $(".ATTENDENCES").slideUp();
            $(".ATTENDENCES-fields").html('<input id="countFieldsId" name="countFieldsId" type="hidden" value="0"><br>' + i18next.t('No attendees') + '<br>');

        } else {
            if ( (eventID == 0 && typeID != undefined || typeID != undefined) && !first_time ) {
                var time_format;

                if (window.CRM.timeEnglish == true) {
                    time_format = 'h:mm A';
                } else {
                    time_format = 'H:mm';
                }

                var time = moment().format('YYYY-MM-DD') + ' ' + eventTypes[0].startHour + ':' + eventTypes[0].startMin;

                var timeStart = moment(time).format(time_format);
                var timeEnd = moment(time).add(1, 'hours');

                timeEnd = timeEnd.format(time_format);

                $('#timeEventStart').val(timeStart);
                $('#timeEventEnd').val(timeEnd);

                // now we have to change the
                var dateStart = $("#rangeStart").data('datestart');
                $('#dateEventEnd').val(dateStart);

                $("#rangeStart").html(i18next.t('From') + ' : ' + dateStart + ' ' + timeStart);
                $("#rangeEnd").html(i18next.t('to') + ' : ' + dateStart + ' ' + timeEnd);
            }

            //$('.ATTENDENCES-title').slideUp();

            var innerHtml = '<input id="countFieldsId" name="countFieldsId" type="hidden" value="' + len + '">';

            innerHtml += '<table>';

            var notes = "";

            for (i = 0; i < len; ++i) {
                innerHtml += '<tr>'
                    + "<td><label>" + eventTypes[i].countName + ":&nbsp;</label></td>"
                    + '<td>'
                    + '<input type="text" id="field' + i + '" data-name="' + eventTypes[i].countName + '" data-countid="' + eventTypes[i].countID + '" value="' + eventTypes[i].count + '" size="8" class="form-control input-sm"  width="100%" style="width: 100%">'
                    + '</td>'
                    + '</tr>'
                notes = eventTypes[i].notes;
            }  //typeID


            innerHtml += '<tr>'
                + "<td><label>" + i18next.t('Attendance Notes: ') + " &nbsp;</label></td>"
                + '<td><input type="text" id="EventCountNotes" value="' + notes + '" class="form-control input-sm">'
                + '</td>'
                + '</tr>';

            innerHtml += '</table><br>';

            $(".ATTENDENCES-fields").html(innerHtml);

            //$(".ATTENDENCES").slideDown();
        }
    });
}

function installAndfinishEventEditorWindow() {
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

    $('#timeEventStart').on("dp.change", function(e) {
        var time_format;

        if (window.CRM.timeEnglish == true) {
            time_format = 'h:mm A';
        } else {
            time_format = 'H:mm';
        }

        var val = $(this).val();

        var time = moment().format('YYYY-MM-DD') + ' ' + val;

        var timeStart = moment(time).format(time_format);

        // now we have to change the
        var dateStart = $("#rangeStart").data('datestart');

        $("#rangeStart").html(i18next.t('From') + ' : ' + dateStart + ' ' + timeStart);
    });

    $('#timeEventEnd').on("dp.change", function(e) {
        var time_format;

        if (window.CRM.timeEnglish == true) {
            time_format = 'h:mm A';
        } else {
            time_format = 'H:mm';
        }

        var val = $(this).val();

        var time = moment().format('YYYY-MM-DD') + ' ' + val;

        var timeEnd = moment(time).format(time_format);

        // now we have to change the
        var dateEnd = $("#rangeEnd").data('dateend');

        $("#rangeEnd").html(i18next.t('to') + ' : ' + dateEnd + ' ' + timeEnd);
    });


    $('.date-picker').datepicker({
        format: window.CRM.datePickerformat,
        language: window.CRM.lang
    });

    $('.date-picker').click('focus', function (e) {
        e.preventDefault();
        $(this).datepicker('show');
    });

    $('.date-start').hide();
    $('.date-end').hide();
    $('.date-recurrence').hide();
    $(".eventNotes").hide();

    var theme = 'n1theme,/skin/js/ckeditor/themes/n1theme/';
    if (window.CRM.bDarkMode) {
        theme = 'moono-dark,/skin/js/ckeditor/themes/moono-dark/';
    }

    // this will create the toolbar for the textarea
    if (window.CRM.editor == null) {
        if (window.CRM.bEDrive) {
            window.CRM.editor = CKEDITOR.replace('eventNotes', {
                customConfig: window.CRM.root + '/skin/js/ckeditor/configs/calendar_event_editor_config.js',
                language: window.CRM.lang,
                width: '100%',
                extraPlugins: 'uploadfile,uploadimage,filebrowser',
                uploadUrl: window.CRM.root + '/uploader/upload.php?type=publicDocuments',
                imageUploadUrl: window.CRM.root + '/uploader/upload.php?type=publicImages',
                filebrowserUploadUrl: window.CRM.root + '/uploader/upload.php?type=publicDocuments',
                filebrowserBrowseUrl: window.CRM.root + '/browser/browse.php?type=publicDocuments',
                skin: theme
            });
        } else {
            window.CRM.editor = CKEDITOR.replace('eventNotes', {
                customConfig: window.CRM.root + '/skin/js/ckeditor/configs/calendar_event_editor_config.js',
                language: window.CRM.lang,
                width: '100%',
                skin: theme
            });
        }

        add_ckeditor_buttons(window.CRM.editor);
    }

    $(".ATTENDENCES").hide();
}

function addCalendarEventTypes(typeId, bAddAttendees) {
    if (typeId === undefined) {
        typeId = false;
    }

    if (bAddAttendees === undefined) {
        bAddAttendees = false;
    }

    window.CRM.APIRequest({
        method: 'GET',
        path: 'events/types',
    }).done(function (eventTypes) {
        var elt = document.getElementById("eventType");
        var len = eventTypes.length;
        var passed = false;

        var global_typeID = 0;

        for (i = 0; i < len; ++i) {
            var option = document.createElement("option");
            option.text = eventTypes[i].name;
            option.value = eventTypes[i].eventTypeID;

            if (typeId && typeId === eventTypes[i].eventTypeID) {
                option.setAttribute('selected', 'selected');
            }

            elt.appendChild(option);

            if (!passed) {
                global_typeID = eventTypes[i].eventTypeID;
                passed = true;
            }
        }

        if (bAddAttendees) {
            addAttendees((typeId != false )?global_typeID:undefined);
        }
    });
}

function addCalendars(calendarId, attendees) {
    if (typeof calendarId === 'undefined') {
        calendarId = [0, 0];
    }

    let calAttendees = true;

    if (typeof attendees === 'undefined') {
        calAttendees = false;
    }

    window.CRM.APIRequest({
        method: 'POST',
        path: 'calendar/getallforuser',
        data: JSON.stringify({"type": "all", "onlyvisible": true, "allCalendars": false})
    }).done(function (calendars) {
        var elt = document.getElementById("EventCalendar");
        var len = calendars.length;

        var option = document.createElement("option");
        option.text = i18next.t("None");
        option.title = 'none';
        option.value = -1;
        elt.appendChild(option);

        for (i = 0; i < len; ++i) {
            if (calendars[i].calendarShareAccess != 2) {
                var option = document.createElement("option");
                var typeSup = "";

                switch (calendars[i].calType) {
                    case "2":
                        typeSup = " : " + i18next.t("Room");
                        break;
                    case "3":
                        typeSup = " : " + i18next.t("Computer");
                        break;
                    case "4":
                        typeSup = " : " + i18next.t("Video");
                        break;
                }
                // there is a calendars.type in function of the new plan of schema
                option.text = "(" + i18next.t(calendars[i].type.charAt(0).toUpperCase() + calendars[i].type.slice(1)) + typeSup + ") " + calendars[i].calendarName;
                option.title = calendars[i].type;
                option.value = calendars[i].calendarID;
                option.setAttribute("data-calendar-id", calendars[i].grpid);

                var aCalendarId = calendars[i].calendarID.split(",");

                if (calendarId[0] == Number(aCalendarId[0])) {
                    option.setAttribute('selected', 'selected');
                }

                elt.appendChild(option);


            }
        }

        // By default attendees are checked
        $("#addGroupAttendees").prop("disabled", (calAttendees == "0") ? true : false);
        $("#addGroupAttendees").prop('checked', (calAttendees == "0") ? false : true);
    });
}

function setActiveState(value) {
    $("input[name='EventStatus'][value='" + value + "']").prop('checked', true);
}


function BootboxContent(start, end, windowtitle, title) {
    var time_format;
    var fmt = window.CRM.datePickerformat.toUpperCase();

    if (window.CRM.timeEnglish == true) {
        time_format = 'h:mm A';
    } else {
        time_format = 'H:mm';
    }

    var dateStart = moment(start).format(fmt);
    var timeStart = moment(start).format(time_format);
    var dateEnd = moment(end).format(fmt);
    var timeEnd = moment(end).format(time_format);

    var frm_str = '<h3 style="margin-top:-5px">' + windowtitle + '</h3><form id="some-form">'
        + '<div>'
        + '  <div class="row div-title EventTitle">'
        + '      <div class="col-md-3"><span style="color: red">*</span>' + i18next.t('Title') + ":</div>"
        + '          <div class="col-md-9">'
        + "              <input type='text' id='EventTitle' placeholder='" + i18next.t("Calendar Title") + "' size='30' maxlength='100' class='form-control input-sm'  width='100%' style='width: 100%' required " + ((title != undefined) ? ("value='" + title + "'") : "") + ">"
        + '          </div>'
        + '      </div>'
        + '  <div class="row  div-title EventLocation">'
        + '      <div class="col-md-3">' + i18next.t('Location') + ":</div>"
        + '      <div class="col-md-9">'
        + '          <div class="form-group has-warning location_group_warning">'
        + '              <label class="control-label location_label_warning" for="inputWarning"><i class="fa fa-bell-o location_label_warning"></i>' + i18next.t("To validate your address : <b>\"hit return\"</b>.") + '</label>'
        + "              <input type='text' id='EventLocation' placeholder='" + i18next.t("Location") + "' size='30' maxlength='100' class='form-control input-sm'  width='100%' style='width: 100%' required>"
        + '              <span class="help-block location_span_warning">' + i18next.t("To see the Map click this text field.") + '</span>'
        + '          </div>'
        + '      </div>'
        + '  </div>'
        + '  <div class="row div-title map-title">'
        + '      <div class="col-md-3">' + i18next.t("Map") + "</div>"
        + '      <div class="col-md-9">'
        + '          <div id="MyMap"></div>'
        + '      </div>'
        + '  </div>'
        + '  <div class="row div-title EventDesc">'
        + '      <div class="col-md-3"><span style="color: red">*</span>' + i18next.t('Desc') + ":</div>"
        + '      <div class="col-md-9">'
        + "          <textarea id='EventDesc' rows='1' maxlength='100' class='form-control input-sm'  width='100%' style='width: 100%' required placeholder='" + i18next.t("Event description") + "'></textarea>"
        + '      </div>'
        + '  </div>'
        + '  <div class="row div-title ATTENDENCES-title ">'
        + '      <div class="col-md-3"><span style="color: red">*</span>' + i18next.t('Event Type') + ":</div>"
        + '      <div class="col-md-9">'
        + '          <select type="text" id="eventType" value="39"  width="100%" style="width: 100%" class="form-control input-sm">'
        //+"<option value='0' >" + i18next.t("Personal") + "</option>"
        + '          </select>'
        + '      </div>'
        + '  </div>'
        + '  <div class="row div-block ATTENDENCES">'
        + '      <div class="col-md-3">' + i18next.t('Attendance Counts') + "</div>"
        + '      <div class="col-md-9 ATTENDENCES-fields"></div>'
        + '      <hr/>'
        + '  </div>'
        + '  <div class="row date-title div-title">'
        + '      <div class="date-range" id="rangeStart" data-datestart="' + dateStart + '">'
        + i18next.t('From') + ' : ' + dateStart + ' ' + timeStart
        + '</div>'
        + '      <div class="date-range" id="rangeEnd" data-dateend="' + dateEnd + '">'
        + i18next.t('to') + ' : ' + dateEnd + ' ' + timeEnd
        + '</div>'
        + '  </div>'
        + '  <div class="row date-start div-block">'
        + '      <div class="col-md-12">'
        + '          <div class="row">'
        + '              <div class="col-md-3"><span style="color: red">*</span>'
        + i18next.t('Start Date') + ' :'
        + '              </div>'
        + '              <div class="input-group col-md-4">'
        + '                  <div class="input-group-prepend">'
        + '                      <span class="input-group-text"><i class="fa fa-calendar"></i></span>'
        + '                  </div>'
        + '                  <input class="form-control date-picker input-sm" type="text" id="dateEventStart" name="dateEventStart"  value="' + dateStart + '" '
        + '                      maxlength="10" id="sel1" size="11"'
        + '                      placeholder="' + window.CRM.datePickerformat + '">'
        + '              </div>'
        + '              <div class="input-group col-md-4">'
        + '                  <div class="input-group-prepend">'
        + '                      <span class="input-group-text"><i class="fa fa-clock-o"></i></span>'
        + '                  </div>'
        + '                  <input type="text" class="form-control timepicker input-sm" id="timeEventStart" name="timeEventStart" value="' + timeStart + '">'
        + '              </div>'
        + '          </div>'
        + '      </div>'
        + '  </div>'
        + '  <div class="row date-end div-block">'
        + '      <div class="col-md-12">'
        + '          <div class="row">'
        + '              <div class="col-md-3"><span style="color: red">*</span>'
        + i18next.t('End Date') + ' :'
        + '              </div>'
        + '              <div class="input-group col-md-4">'
        + '                  <div class="input-group-prepend">'
        + '                      <span class="input-group-text"><i class="fa fa-calendar"></i></span>'
        + '                  </div>'
        + '                  <input class="form-control date-picker  input-sm" type="text" id="dateEventEnd" name="dateEventEnd"  value="' + dateEnd + '" '
        + '                      maxlength="10" id="sel1" size="11"'
        + '                      placeholder="' + window.CRM.datePickerformat + '">'
        + '              </div>'
        + '              <div class="input-group col-md-4">'
        + '                  <div class="input-group-prepend">'
        + '                      <span class="input-group-text"><i class="fa fa-clock-o"></i></span>'
        + '                  </div>'
        + '                  <input type="text" class="form-control timepicker input-sm" id="timeEventEnd" name="timeEventEnd" value="' + timeEnd + '">'
        + '              </div>'
        + '          </div>'
        + '      </div>'
        + '  </div>'
        + '  <div class="row date-recurrence div-block">'
        + '      <div class="col-md-12">'
        + '          <div class="row">'
        + '              <div class="col-md-3">'
        + '                  <input type="checkbox" id="checkboxEventrecurrence" name="checkboxEventrecurrence"> ' + i18next.t('Repeat') + ' :'
        + '              </div>'
        + '              <div class="col-md-3">'
        + '                  <select class="form-control input-sm" id="typeEventrecurrence" name="typeEventrecurrence">'
        + '                      <option value="FREQ=DAILY">' + i18next.t("Daily") + '</option>'
        + '                      <option value="FREQ=WEEKLY">' + i18next.t("Weekly") + '</option>'
        + '                      <option value="FREQ=MONTHLY">' + i18next.t("Monthly") + '</option>'
        + '                      <option value="FREQ=MONTHLY;INTERVAL=3">' + i18next.t("Quarterly") + '</option>'
        + '                      <option value="FREQ=MONTHLY;INTERVAL=6">' + i18next.t("Semesterly") + '</option>'
        + '                      <option value="FREQ=YEARLY">' + i18next.t("Yearly") + '</option>'
        + '                  </select>'
        + '               </div>'
        + '               <div class="col-md-2">'
        + i18next.t('End') + ' :'
        + '               </div>'
        + '               <div class="input-group col-md-4">'
        + '                  <div class="input-group-prepend">'
        + '                      <span class="input-group-text"><i class="fa fa-calendar"></i></span>'
        + '                  </div>'
        + '                  <input class="form-control date-picker input-sm" type="text" id="endDateEventrecurrence" name="endDateEventrecurrence"  value="' + dateStart + '" '
        + '                      maxlength="10" id="sel1" size="11"'
        + '                      placeholder="' + window.CRM.datePickerformat + '">'
        + '               </div>'
        + '            </div>'
        + '            <div class="col-md-12" style="padding-top:10px">'
        + '              <div class="row">'
        + '                  <div class="col-md-3"><span style="color: red">*</span>' + i18next.t('Alarm') + ":</div>"
        + '                  <div class="col-md-9">'
        + '                    <select class="form-control input-sm" id="EventAlarm" name="EventAlarm">'
        + '                          <option value="NONE">' + i18next.t("NONE") + '</option>'
        + '                          <option value="PT0S">' + i18next.t("At time of event") + '</option>'
        + '                          <option value="-PT5M">' + i18next.t("5 minutes before") + '</option>'
        + '                          <option value="-PT10M">' + i18next.t("10 minutes before") + '</option>'
        + '                          <option value="-PT15M">' + i18next.t("15 minutes before") + '</option>'
        + '                          <option value="-PT30M">' + i18next.t("30 minutes before") + '</option>'
        + '                          <option value="-PT1H">' + i18next.t("1 hour before") + '</option>'
        + '                          <option value="-PT2H">' + i18next.t("2 hour before") + '</option>'
        + '                          <option value="-P1D">' + i18next.t("1 day before") + '</option>'
        + '                          <option value="-P2D">' + i18next.t("2 day before") + '</option>'
        + '                      </select>'
        + '                  </div>'
        + '              </div>'
        + '          </div>'
        + '      </div>'
        + '  </div>'
        + '  <div class="row  div-title calendar-title">'
        + '      <div class="col-md-3"><span style="color: red">*</span>' + i18next.t('Calendar') + ":</div>"
        + '      <div class="col-md-4">'
        + '          <select type="text" id="EventCalendar" value="39" width="100%" style="width: 100%" class="form-control input-sm"></select>'
        + '      </div>'
        + '      <div class="col-md-5">'
        + '          <div class="checkbox">'
        + '              <label>'
        + '                  <input type="checkbox" id="addGroupAttendees" disabled> ' + i18next.t('Add as attendees')
        + '              </label>'
        + '          </div>'
        + '      </div>'
        + '  </div>'
        + '  <div class="row div-title eventNotesTitle">'
        + '      <div class="col-md-12">'
        + i18next.t('Notes')
        + '      </div>'
        + '  </div>'
        + '  <div class="row  eventNotes  div-block">'
        + '      <div class="col-md-12" style="margin-top:-15px;padding-left:0px;padding-right:2px;">'
        + '          <textarea name="EventText" cols="80" class="form-control input-sm eventNotes" id="eventNotes"  width="100%" style="margin-top:-58px;width: 100%;height: 4em;"></textarea>'
        + '      </div>'
        + '  </div>'
        + '  <div class="row  div-title">'
        + '      <div class="status-event-title">'
        + '          <span style="color: red">*</span>' + i18next.t('Status')
        + '      </div>'
        + '      <div class="status-event">'
        + '          <input type="radio" name="EventStatus" value="0" checked/> ' + i18next.t('Active')
        + '      </div>'
        + '      <div class="status-event">'
        + '          <input type="radio" name="EventStatus" value="1" /> ' + i18next.t('inactive')
        + '      </div>'
        + '  </div>'
        + '</form>';

    var object = $('<div/>').html(frm_str).contents();

    return object
}

function createEventEditorWindow(start, end, dialogType, eventID, reccurenceID, page, windowtitle, title) // dialogType : createEvent or modifyEvent, eventID is when you modify and event
{
    if (dialogType === undefined) {
        dialogType = 'createEvent';
    }

    if (reccurenceID === undefined) {
        reccurenceID = '';
    }

    if (page === undefined) {
        page = window.CRM.root + '/v2/calendar';
    }

    if (eventID === undefined) {
        eventID = -1;
    }

    window.CRM.eventID = eventID;

    if (end == null) {
        end = start;
    }

    if (windowtitle == undefined) {
        windowtitle = i18next.t("Event Creation");
    }

    var modal = bootbox.dialog({
        message: BootboxContent(start, end, windowtitle, title),
        size: 'large',
        buttons: [
            {
                label: '<i class="fa fa-times"></i> ' + i18next.t("Close"),
                className: "btn btn-default",
                callback: function () {
                    console.log("just do something on close");
                }
            },
            {
                label: '<i class="fa fa-check"></i> ' + i18next.t("Save"),
                className: "btn btn-primary",
                callback: function () {
                    var EventTitle = $('form #EventTitle').val();

                    if (EventTitle) {
                        var e = document.getElementById("EventCalendar");
                        var EventCalendarID = e.options[e.selectedIndex].value;

                        if (EventCalendarID == -1) {
                            window.CRM.DisplayNormalAlert(i18next.t("Error"), i18next.t("You've to chose a calendar."));

                            return false;
                        }

                        var loc = $('form #EventLocation').val();

                        var e = document.getElementById("eventType");
                        var eventTypeID = e.options[e.selectedIndex].value;

                        var EventDesc = $('form #EventDesc').val();

                        var dateStart = $('form #dateEventStart').val();
                        var timeStart = $('form #timeEventStart').val();
                        var dateEnd = $('form #dateEventEnd').val();
                        var timeEnd = $('form #timeEventEnd').val();

                        var recurrenceValid = $('#checkboxEventrecurrence').is(":checked");
                        var recurrenceType = $("#typeEventrecurrence").val();
                        var endrecurrence = $("#endDateEventrecurrence").val();

                        var alarm = $("#EventAlarm").val();

                        var fmt = window.CRM.datePickerformat.toUpperCase();

                        if (window.CRM.timeEnglish == true) {
                            time_format = 'h:mm A';
                        } else {
                            time_format = 'H:mm';
                        }

                        fmt = fmt + ' ' + time_format;

                        var real_start = moment(dateStart + ' ' + timeStart, fmt).format('YYYY-MM-DD H:mm');
                        var real_end = moment(dateEnd + ' ' + timeEnd, fmt).format('YYYY-MM-DD H:mm');
                        var real_endrecurrence = moment(endrecurrence + ' ' + timeStart, fmt).format('YYYY-MM-DD H:mm');

                        var addGroupAttendees = document.getElementById("addGroupAttendees").checked;

                        var eventInActive = $('input[name="EventStatus"]:checked').val();

                        if (addGroupAttendees) {
                            eventAttendees = true;
                        }

                        var countFieldsId = $('form #countFieldsId').val();

                        var fields = new Array();

                        for (i = 0; i < countFieldsId; i++) {
                            var myObj = new Object();

                            var name = $('form #field' + i).data('name');
                            var countid = $('form #field' + i).data('countid');
                            var value = $('form #field' + i).val();

                            myObj.name = name;
                            myObj.countid = countid;
                            myObj.value = value;

                            fields[i] = myObj;
                        }

                        var EventCountNotes = $('form #EventCountNotes').val();

                        var eventNotes = CKEDITOR.instances['eventNotes'].getData();//$('form #eventNotes').val();

                        var add = false;

                        window.CRM.APIRequest({
                            method: 'POST',
                            path: 'events/',
                            data: JSON.stringify({
                                "eventAction": dialogType,
                                "eventID": eventID,
                                "eventTypeID": eventTypeID,
                                "EventTitle": EventTitle,
                                "EventDesc": EventDesc,
                                "calendarID": EventCalendarID,
                                "Fields": fields,
                                "EventCountNotes": EventCountNotes,
                                "eventNotes": eventNotes,
                                "start": real_start,
                                "end": real_end,
                                "addGroupAttendees": addGroupAttendees,
                                "eventInActive": eventInActive,
                                "recurrenceValid": recurrenceValid,
                                "recurrenceType": recurrenceType,
                                "endrecurrence": real_endrecurrence,
                                "reccurenceID": reccurenceID,
                                "location": loc,
                                "alarm": alarm
                            })
                        }).done(function (data) {
                            add = true;
                            modal.modal("hide");

                            if (dialogType == 'createEvent') {
                                eventCreated = true;
                            }

                            if ( page.includes("ListEvent.php") ) {
                                location.reload();
                            } else if ( page.includes("Checkin.php") ) {
                                window.location.href = window.CRM.root + '/Checkin.php';
                            } else if (page.includes("/v2/calendar")) {
                                // we reload all the events
                                window.CRM.calendar.refetchEvents();
                            } else {
                                window.location.href = window.CRM.root + '/' + page;
                            }

                            return true;
                        });

                        return add;
                    } else {
                        window.CRM.DisplayNormalAlert(i18next.t("Error"), i18next.t("You have to set a Title for your event"));

                        return false;
                    }
                }
            }
        ],
        show: false/*,
         onEscape: function() {
            modal.modal("hide");
         }*/
    });

    // this will ensure that image and table can be focused
    $(document).on('focusin', function (e) {
        e.stopImmediatePropagation();
    });

    return modal;
}

function addEvent(dateStart, dateEnd, windowTitle, title, calendarID, attendees)
{
    window.CRM.APIRequest({
        method: 'POST',
        path: 'calendar/numberofcalendars',
    }).done(function(data) {
        if (data.CalendarNumber > 0) {
            if (window.CRM.editor != null) {
                CKEDITOR.remove(window.CRM.editor);
                window.CRM.editor = null;
            }

            modal = createEventEditorWindow(dateStart, dateEnd, 'createEvent', 0, '', 'v2/calendar', windowTitle, title);

            // we add the calendars and the types
            addCalendars(calendarID, attendees);
            addCalendarEventTypes(-1, true);

            // finish installing the window
            installAndfinishEventEditorWindow();

            $("#typeEventrecurrence").prop("disabled", true);
            $("#endDateEventrecurrence").prop("disabled", true);

            $('#EventCalendar option:first-child').attr("selected", "selected");

            modal.modal("show");

            initMap();
        } else {
            window.CRM.DisplayAlert(i18next.t("Error"),i18next.t("To add an event, You have to create a calendar or activate one first."));
        }
    });
}
