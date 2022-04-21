$(document).ready(function () {
    moment.locale(window.CRM.shortLocale);

    window.CRM.fmt = "";

    if (window.CRM.timeEnglish == true) {
        window.CRM.fmt = window.CRM.datePickerformat.toUpperCase() + ' hh:mm a';
    } else {
        window.CRM.fmt = window.CRM.datePickerformat.toUpperCase() + ' HH:mm';
    }

    var DataEventsListTable = {
        ajax: {
            url: window.CRM.root + "/api/calendar/getalleventsForEventsList",
            type: 'POST',
            contentType: "application/json",
            dataSrc: "EventsListResults",
            data: function (json) {
                var search_Term = $("#SearchTerm").val();

                if (search_Term !='') {
                    $('.in-progress').css("color", "red");
                    $('.in-progress').html("  "+ i18next.t("In progress...."));
                }

                return JSON.stringify({
                    "start": window.CRM.yVal + '-01-01',
                    "end" : window.CRM.yVal + '-12-31',
                    "isBirthdayActive": false,
                    "isAnniversaryActive": false,
                    "forEventslist": true});
            }
        },
        rowGroup: {
            dataSrc: 'month',
            startRender: function(rows, group) {
                var oneDate = moment('02-' + group + '-2021', 'DD-MM-YYYY');
                return rows.count() + ' ' + ((rows.count()>1)?i18next.t('Events for'):i18next.t('Event for')) + ' ' + ' : ' + oneDate.format('MMMM');
            }
        },
        "pageLength": 20000,
        "language": {
            "url": window.CRM.plugin.dataTable.language.url
        },
        "searching": true,
        "initComplete": function( settings, json ) {
            $('.in-progress').css("color", "green");
            $('.in-progress').html("  "+ i18next.t("Loading finished...."));
        },
        //"deferRender": true,
        //orderFixed: [3, 'asc'],
        columns: [
            {
                width: 'auto',
                title: i18next.t('Month'),
                visible: true,
                data: 'month',
                render: function (data, type, full, meta) {
                    var oneDate = moment('02-' + full.month + '-2021', 'DD-MM-YYYY');
                    return oneDate.format('MMMM');
                }
            },
            {
                width: 'auto',
                title: i18next.t('Actions'),
                visible: true,
                data: 'icon',
                render: function (data, type, full, meta) {
                    //full.backgroundColor
                    return '<table class="table-responsive" style="width:120px">\n' +
                        '                <tbody><tr class="no-background-theme">\n' +
                        '                  <td style="width:48px;padding: 7px 2px;border:none;text-align: right">\n' +
                        '                    <button type="submit"  name="Action" data-link="' + full.Link +'" data-id="' + full.eventID + '" title="' + i18next.t('Edit') + '" style="color:' + ((full.Rights != "")?'blue':'gray') + '" class="EditEvent btn btn-default btn-xs" '+ ((full.Rights)?'':'disabled') +'>\n' +
                                                data +
                        '                    </button>\n' +
                        '                  </td>\n' +
                        '                  <td style="width:18px;padding: 7px 2px;border:none;">\n' +
                        '                      <button type="submit" name="Action" data-dateStart="' + full.start + '" data-reccurenceid="' + full.reccurenceID + '" data-recurrent="' + full.recurrent + '" data-calendarid="' + full.calendarID + '" data-id="' + full.eventID+ '" title="' + i18next.t('Delete') + '"  style="color:' + ((full.Rights != "")?'red':'gray') + '" class="DeleteEvent btn btn-default btn-xs" ' + ((full.Rights)?'':'disabled') + '>\n' +
                        '                        <i class="fas fa-trash-alt"></i>\n' +
                        '                      </button>\n' +
                        '                  </td>\n' +
                        '                  <td style="width:18px;padding: 7px 2px;border:none;text-align: left">\n' +
                        '                      <button type="submit" name="Action" data-id="' + full.eventID+ '" title="' + i18next.t('Info') + '" style="color:' + ((full.Text != "" && full.Rights)?'green':'gray') + '" class="EventInfo btn btn-default btn-xs" ' + ((full.Text != "")?'':'disabled') + '>\n' +
                        '                        <i class="far fa-file"></i>\n' +
                        '                      </button>\n' +
                        '                  </td>\n' +
                        '                </tr>\n' +
                        '              </tbody></table>';
                }
            },
            {
                width: 'auto',
                title: i18next.t('Title') + ' (' + i18next.t('Desc') + ')',
                data: 'title',
                render: function (data, type, full, meta) {
                    var ret = data;

                    if ( full.Desc != '') {
                        ret += "<br/>(" + full.Desc  + ")";
                    }
                    return ret;
                }
            },
            {
                width: 'auto',
                title: i18next.t('Calendar'),
                data: 'CalendarName',
                render: function (data, type, full, meta) {
                    return i18next.t('Name') + ' : <b>' + data + "</b><br/>"+
                        full.Login;

                }
            },
            {
                width: 'auto',
                title: i18next.t('Attendance Counts with real Attendees'),
                data: 'RealStats',
                render: function (data, type, full, meta) {
                    return data;

                }
            },
            {
                width: 'auto',
                title: i18next.t('Free Attendance Counts without Attendees'),
                data: 'FreeStats',
                render: function (data, type, full, meta) {
                    return data;

                }
            },
            {
                width: 'auto',
                title: i18next.t('Start Date'),
                visible: true,
                data: 'start',
                render: function (data, type, full, meta) {
                    return moment(data).format(window.CRM.fmt);
                }
            },
            {
                width: 'auto',
                title: i18next.t('End Date'),
                visible: true,
                data: 'end',
                render: function (data, type, full, meta) {
                    return moment(data).format(window.CRM.fmt);
                }
            },
            {
                width: 'auto',
                title: i18next.t('Event Type'),
                data: 'TypeName',
                visible: false,
                render: function (data, type, full, meta) {
                    return data;
                }
            },
            {
                width: 'auto',
                title: i18next.t('Calendar Type'),
                data: 'cal_category_translated',
                visible: false,
                render: function (data, type, full, meta) {
                    return data;
                }
            },
            {
                width: 'auto',
                title: i18next.t('Active'),
                data: 'Status',
                visible: true,
                render: function (data, type, full, meta) {
                    return data;
                }
            }
        ]
    };

    $.extend(DataEventsListTable,window.CRM.plugin.dataTable);

    // add the date time filter
    $.fn.dataTable.moment(window.CRM.fmt);

    // create the table
    window.CRM.DataEventsListTable = $("#DataEventsListTable").DataTable(DataEventsListTable);

    // filter by month correctelly
    window.CRM.DataEventsListTable
        .order( [ 6, 'desc' ] )
        .draw();

    // the function to reload the datas in the table
    window.CRM.reloadListEventPage = function() {
        window.CRM.DataEventsListTable.ajax.reload(function (){
            $('.in-progress').css("color", "green");
            $('.in-progress').html("  "+ i18next.t("Loading finished...."));

            window.CRM.DataEventsListTable
                .order( [ 6, 'desc' ] )
                .draw();
        });
    }

    // the actions
    $("#YearSelector").change(function() {
        window.CRM.yVal = $(this).val();

        $("#main-Title-events").html(i18next.t('Events in Year') + " : " + window.CRM.yVal);

        window.CRM.reloadListEventPage();
    });

    $("#MonthSelector").change(function() {
        if (this.value == 'all') {
            window.CRM.DataEventsListTable.search( "" ).draw();
            $("#main-Title-events").html(i18next.t('Events in Year') + " : " + window.CRM.yVal);
        } else {
            window.CRM.DataEventsListTable.search(this.value).draw();
            $("#main-Title-events").html(i18next.t('Events in month') + " : " + this.value);
        }
    });

    $("#EventTypeSelector").change(function() {
        if (this.value == 'all') {
            window.CRM.DataEventsListTable.search( "" ).draw();
            $("#main-Title-events").html(i18next.t('Events in Year') + " : " + window.CRM.yVal);
        } else {
            window.CRM.DataEventsListTable.search(this.value).draw();
            $("#main-Title-events").html(i18next.t('Events by Type') + " : " + this.value);
        }
    });

    $(document).on("click", ".EditEvent", function () {
        var eventID    = $(this).data("id");
        var link    = $(this).data("link");

        if (link !== null) {
            window.location.href = window.CRM.root + '/' + link;
            return;
        }


        window.CRM.APIRequest({
            method: 'POST',
            path: 'events/info',
            data: JSON.stringify({"eventID":eventID})
        },function(calEvent) {
            if (window.CRM.editor != null) {
                CKEDITOR.remove(window.CRM.editor);
                window.CRM.editor = null;
            }

            modal = createEventEditorWindow (calEvent.start,calEvent.end,'modifyEvent',eventID,'','v2/calendar/events/list');

            $('form #EventTitle').val(calEvent.Title);
            $('form #EventDesc').val(calEvent.Desc);
            $('form #eventNotes').val(calEvent.Text);
            $('form #EventLocation').val(calEvent.location);

            $("form #addGroupAttendees").prop("disabled", (calEvent.groupID == "0") ? true : false);
            $("form #addGroupAttendees").prop('checked', (calEvent.groupID == "0") ? false : true);


            if (calEvent.alarm !== null) {
                $("form #EventAlarm").val(calEvent.alarm.trigger).trigger('change');
            }

            // we add the calendars and the types
            addCalendars(calEvent.calendarID);
            addCalendarEventTypes(calEvent.eventTypeID,false);
            addAttendees(calEvent.eventTypeID,true,calEvent.eventID);
            setActiveState(calEvent.inActive);

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

            $('.date-picker').datepicker({format:window.CRM.datePickerformat, language: window.CRM.lang});

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
                    window.CRM.editor = CKEDITOR.replace('eventNotes',{
                        customConfig: window.CRM.root+'/skin/js/ckeditor/configs/calendar_event_editor_config.js',
                        language : window.CRM.lang,
                        width : '100%',
                        extraPlugins : 'uploadfile,uploadimage,filebrowser,html5video',
                        uploadUrl: window.CRM.root+'/uploader/upload.php?type=publicDocuments',
                        imageUploadUrl: window.CRM.root+'/uploader/upload.php?type=publicImages',
                        filebrowserUploadUrl: window.CRM.root+'/uploader/upload.php?type=publicDocuments',
                        filebrowserBrowseUrl: window.CRM.root+'/browser/browse.php?type=publicDocuments',
                        skin:theme
                    });
                } else {
                    window.CRM.editor = CKEDITOR.replace('eventNotes',{
                        customConfig: window.CRM.root+'/skin/js/ckeditor/configs/calendar_event_editor_config.js',
                        language : window.CRM.lang,
                        width : '100%',
                        skin:theme
                    });
                }

                add_ckeditor_buttons(window.CRM.editor);
            }

            $(".ATTENDENCES").hide();

            modal.modal("show");

            initMap(calEvent.longitude,calEvent.latitude,calEvent.title+'('+calEvent.Desc+')',calEvent.location,calEvent.title+'('+calEvent.Desc+')',calEvent.Text);
        });
    });

    $(document).on("click", ".DeleteEvent", function () {
        var eventID    = $(this).data("id");
        var calendarID = $(this).data("calendarid").split(',');
        var recurrent  = $(this).data("recurrent");
        var reccurenceID = $(this).data("reccurenceid");
        var dateStart    = $(this).data("datestart");


        var box = bootbox.dialog({
            title: i18next.t("Modify Event"),
            message: i18next.t("What would you like to do ? Be careful with the deletion, it's impossible to revert !!!"),
            size: 'large',
            buttons: {
                delete: {
                    label: '<i class="fas fa-times"></i> ' + i18next.t("Delete Event"),
                    className: 'btn btn-danger',
                    callback: function () {
                        if (recurrent == 0) {
                            bootbox.confirm(i18next.t("Are you sure to delete this event?"), function (confirmed) {
                                if (confirmed) {
                                    window.CRM.APIRequest({
                                        method: 'POST',
                                        path: 'events/',
                                        data: JSON.stringify({
                                            "calendarID": calendarID,
                                            "eventAction": 'suppress',
                                            "eventID": eventID
                                        })
                                    },function (data) {
                                        if (data.status == "failed") {
                                            window.CRM.DisplayNormalAlert(i18next.t("Error"), data.message);
                                        }
                                        window.CRM.reloadListEventPage();
                                    });
                                }
                            });
                        } else if (recurrent == 1) {
                            var reccurenceID = moment(reccurenceID).format(fmt);

                            var box = bootbox.dialog({
                                title: i18next.t("Delete all repeated Events"),
                                message: i18next.t("You are about to delete all the repeated Events linked to this event. Are you sure? This can't be undone."),
                                buttons: {
                                    cancel: {
                                        label: i18next.t('No'),
                                        className: 'btn btn-success'
                                    },
                                    add: {
                                        label: i18next.t('Only this event'),
                                        className: 'btn btn-info',
                                        callback: function () {
                                            window.CRM.APIRequest({
                                                method: 'POST',
                                                path: 'events/',
                                                data: JSON.stringify({
                                                    "calendarID": calendarID,
                                                    "eventAction": 'suppress',
                                                    "eventID": eventID,
                                                    "dateStart": dateStart,
                                                    "reccurenceID": reccurenceID
                                                })
                                            },function (data) {
                                                if (data.status == "failed") {
                                                    window.CRM.DisplayNormalAlert(i18next.t("Error"), data.message);
                                                }
                                                window.CRM.reloadListEventPage();
                                            });
                                        }
                                    },
                                    confirm: {
                                        label: i18next.t('Every Events linked to this Event'),
                                        className: 'btn btn-danger',
                                        callback: function () {
                                            window.CRM.APIRequest({
                                                method: 'POST',
                                                path: 'events/',
                                                data: JSON.stringify({
                                                    "calendarID": calendarID,
                                                    "eventAction": 'suppress',
                                                    "eventID": eventID
                                                })
                                            },function (data) {
                                                window.CRM.reloadListEventPage();
                                            });
                                        }
                                    }
                                }
                            });
                        }
                    }
                },
                cancel: {
                    label: '<i class="fas fa-check"></i> ' + i18next.t('Cancel'),
                    className: 'btn btn-primary',
                    callback: function () {
                    }
                }
            }
        });

        box.show();
    });

    function BootboxInfo(data) {
        var frm_str = data;
        var object = $('<div/>').html(frm_str).contents();
        return object
    }

    $(document).on("click", ".EventInfo", function () {
        var eventID    = $(this).data("id");

        window.CRM.APIRequest({
            method: 'POST',
            path: 'events/info',
            data: JSON.stringify({
                "eventID": eventID
            })
        },function (data) {
            var box = bootbox.dialog({
                title: i18next.t("Text for Event ID") + "   (" + data.eventID  + ") : " + data.Title,
                message: BootboxInfo(data.Text),
                size: 'extra-large',
                buttons: {
                    ok: {
                        label: '<i class="fas fa-check"></i> ' + i18next.t("Ok"),
                        className: 'btn btn-primary',
                    }
                }
            });
        });
    });

    // the main add event button
    $('#add-event').click('focus', function (e) {
        var fmt = 'YYYY-MM-DD HH:mm:ss';

        var dateStart = moment().format(fmt);
        var dateEnd = moment().format(fmt);

        window.CRM.APIRequest({
            method: 'POST',
            path: 'calendar/numberofcalendars',
        },function(data) {
            if (data.CalendarNumber > 0) {
                if (window.CRM.editor != null) {
                    CKEDITOR.remove(window.CRM.editor);
                    window.CRM.editor = null;
                }

                modal = createEventEditorWindow(dateStart, dateEnd, 'createEvent', 0, '', 'v2/calendar/events/list');

                // we add the calendars and the types
                addCalendars();
                addCalendarEventTypes(-1, true);

                // finish installing the window
                installAndfinishEventEditorWindow();

                $("#typeEventrecurrence").prop("disabled", true);
                $("#endDateEventrecurrence").prop("disabled", true);

                modal.modal("show");

                initMap();
            } else {
                window.CRM.DisplayAlert(i18next.t("Error"),i18next.t("To add an event, You have to create a calendar or activate one first."));
            }
        });
    });
});
