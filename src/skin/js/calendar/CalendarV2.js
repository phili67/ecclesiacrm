//
//  This code is under copyright not under MIT Licence
//  copyright   : 2020 Philippe Logel all right reserved not MIT licence
//                This code can't be included in another software.
//
//  Updated : 2020/05/07
//

window.CRM.editor = null;

document.addEventListener('DOMContentLoaded', function () {
    var calendarEl = document.getElementById('calendar');

    window.CRM.calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: wAgendaName,
        dayMaxEventRows: withlimit, // for all non-TimeGrid views
        customButtons: {
            actualizeButton: {
                text: i18next.t('Actualize'),
                click: function() {
                    window.CRM.calendar.refetchEvents();
                }
            },
            todayButton: {
                text: i18next.t('Today'),
                click: function() {
                    window.CRM.calendar.changeView('today');
                    window.CRM.calendar.refetchEvents();
                }
            },
            listButton: {
                text: i18next.t('List'),
                click: function() {
                    window.CRM.calendar.changeView('list');
                    window.CRM.calendar.refetchEvents();
                }
            },
            listWeekButton: {
                text: i18next.t('List Week'),
                click: function() {
                    window.CRM.calendar.changeView('listWeek');
                    window.CRM.calendar.refetchEvents();
                }
            },
            listMonthButton: {
                text: i18next.t('List Month'),
                click: function() {
                    window.CRM.calendar.changeView('listMonth');
                    window.CRM.calendar.refetchEvents();
                }
            },

        },
        locale: window.CRM.lang,
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonthButton actualizeButton',//timeGridDayButton listButton listWeekButton listMonthButton '*/
        },
        views: {
            timeGrid: {
                dayMaxEventRows: 6 // adjust to 7 only for timeGridWeek/timeGridDay
            }
        },
        viewDidMount: function(info){
            localStorage.setItem("wAgendaName",info.view.type);
        },
        editable: true,
        selectable: true,
        eventClick: function(calEvent) {
            var event = calEvent.event;

            if (event.extendedProps.writeable == false) {
                window.CRM.DisplayAlert(i18next.t("Error"),i18next.t("This event isn't modifiable !!!"));

                return;
            }

            var fmt = 'YYYY-MM-DD HH:mm:ss';

            var dateStart = moment(event.start).format(fmt);
            var dateEnd = moment(event.end).format(fmt);


            var type = event.extendedProps.realType;

            if (type == "event" && window.CRM.isModifiable) {
                // only with group event We create the dialog,
                if (type == "event") {
                    var box = bootbox.dialog({
                        title: i18next.t("Modify Event"),
                        message: i18next.t("What would you like to do ? Be careful with the deletion, it's impossible to revert !!!"),
                        size: 'large',
                        buttons: {
                            cancel: {
                                label: '<i class="fa fa-times"></i> ' + i18next.t("Delete Event"),
                                className: 'btn btn-danger',
                                callback: function () {
                                    if (type == "event" && event.extendedProps.recurrent == 0) {
                                        bootbox.confirm(i18next.t("Are you sure to delete this event?"), function (confirmed) {
                                            if (confirmed) {
                                                window.CRM.APIRequest({
                                                    method: 'POST',
                                                    path: 'events/',
                                                    data: JSON.stringify({
                                                        "calendarID": event.extendedProps.calendarID,
                                                        "eventAction": 'suppress',
                                                        "eventID": event.extendedProps.eventID
                                                    })
                                                }).done(function (data) {
                                                    window.CRM.calendar.refetchEvents();
                                                    window.CRM.calendar.unselect();
                                                });
                                            }
                                        });
                                    } else if (type == "event" && event.extendedProps.recurrent == 1) {
                                        var reccurenceID = moment(event.extendedProps.reccurenceID).format(fmt);

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
                                                                "calendarID": event.extendedProps.calendarID,
                                                                "eventAction": 'suppress',
                                                                "eventID": event.extendedProps.eventID,
                                                                "dateStart": dateStart,
                                                                "reccurenceID": reccurenceID
                                                            })
                                                        }).done(function (data) {
                                                            window.CRM.calendar.refetchEvents();
                                                            window.CRM.calendar.unselect();
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
                                                                "calendarID": event.extendedProps.calendarID,
                                                                "eventAction": 'suppress',
                                                                "eventID": event.extendedProps.eventID
                                                            })
                                                        }).done(function (data) {
                                                            window.CRM.calendar.refetchEvents();
                                                            window.CRM.calendar.unselect();
                                                        });
                                                    }
                                                }
                                            }
                                        });

                                        //window.CRM.DisplayAlert(i18next.t("Error"), i18next.t("To add an event, You have to create a calendar or activate one first."));

                                        //box.show();
                                    } else {
                                        // the other event type
                                    }
                                }
                            },
                            add: {
                                label: '<i class="fa fa-plus"></i> ' + i18next.t('Add More Attendees'),
                                className: 'btn btn-info',
                                callback: function () {
                                    window.CRM.APIRequest({
                                        method: 'POST',
                                        path: 'events/',
                                        data: JSON.stringify({
                                            "eventAction": 'attendeesCheckinEvent',
                                            "eventID": event.extendedProps.eventID
                                        })
                                    }).done(function (data) {
                                        location.href = window.CRM.root + '/EditEventAttendees.php';
                                    });
                                }
                            },
                            attendance: {
                                label: '<i class="fa fa-check"></i> ' + i18next.t('Make Attendance'),
                                className: 'btn btn-primary',
                                callback: function () {
                                    window.CRM.APIRequest({
                                        method: 'POST',
                                        path: 'events/',
                                        data: JSON.stringify({
                                            "eventAction": 'attendeesCheckinEvent',
                                            "eventID": event.extendedProps.eventID
                                        })
                                    }).done(function (data) {
                                        location.href = window.CRM.root + '/Checkin.php';
                                    });

                                }
                            },
                            Edit: {
                                label: '<i class="fa fa-search-plus"></i> ' + i18next.t('Edit'),
                                className: 'btn btn-success',
                                callback: function () {
                                    if (window.CRM.editor != null) {
                                        CKEDITOR.remove(window.CRM.editor);
                                        window.CRM.editor = null;
                                    }

                                    modal = createEventEditorWindow(event.start, event.end, 'modifyEvent', event.extendedProps.eventID, event.extendedProps.reccurenceID);

                                    $('form #EventTitle').val(event.title);
                                    $('form #EventDesc').val(event.extendedProps.Desc);
                                    $('form #eventNotes').val(event.extendedProps.Text);
                                    $('form #EventLocation').val(event.extendedProps.location);
                                    $("form #addGroupAttendees").prop("disabled", (event.extendedProps.groupID == "0") ? true : false);
                                    $("form #addGroupAttendees").prop('checked', (event.extendedProps.groupID == "0") ? false : true);

                                    if (event.extendedProps.alarm !== null) {
                                        $("form #EventAlarm").val(event.extendedProps.alarm.trigger).trigger('change');
                                    }

                                    if (event.extendedProps.recurrent == 1) {
                                        $("#checkboxEventrecurrence").prop( "checked", true );

                                        $("form #typeEventrecurrence").val(event.extendedProps.freq).trigger('change');

                                        var fmt = window.CRM.datePickerformat.toUpperCase();
                                        var dateStart = moment(event.extendedProps.rrule).format(fmt);
                                        $("#endDateEventrecurrence").val(dateStart);
                                    }


                                    // we add the calendars and the types
                                    addCalendars(event.extendedProps.calendarID, ((event.extendedProps.groupID == "0") ? false : true));
                                    addCalendarEventTypes(event.extendedProps.eventTypeID, false);
                                    addAttendees(event.extendedProps.eventTypeID, true, event.extendedProps.eventID);

                                    // finish installing the window
                                    installAndfinishEventEditorWindow();

                                    initMap(event.extendedProps.longitude, event.extendedProps.latitude, event.title + '(' + event.extendedProps.Desc + ')', event.extendedProps.location, event.title + '(' + event.extendedProps.Desc + ')', event.extendedProps.Text);

                                    modal.modal("show");
                                }
                            }
                        }
                    });

                    box.show();
                } else {
                    // we are with other event type
                }
            }
        },
        events: function(info, successCallback, failureCallback) {
            var real_start = moment(info.start.valueOf()).format('YYYY-MM-DD HH:mm:ss');
            var real_end = moment(info.end.valueOf()).format('YYYY-MM-DD HH:mm:ss');

            window.CRM.APIRequest({
                method: 'POST',
                path: 'calendar/getallevents',
                data: JSON.stringify({"start":real_start,"end":real_end, 'isBirthdayActive': birthday, 'isAnniversaryActive':anniversary})
            }).done(function(events) {
                successCallback(events);
            });
        },
        eventContent: function(calEvent) {
            let elt = document.createElement('span')

            elt.style.background = calEvent.backgroundColor;
            elt.style.width = '100%';
            elt.style.height = '20px';

            elt.innerHTML =  '<div class="fc-event-main"><div class="fc-event-main-frame"><div class="fc-event-title-container"><div class="fc-event-title fc-sticky">'
                + calEvent.event.extendedProps.icon + " " + calEvent.event.title
                + '</div></div></div></div><div class="fc-event-resizer fc-event-resizer-end"></div>';

            let arrayOfDomNodes = [ elt ]
            return { domNodes: arrayOfDomNodes }
        },
        eventDidMount: function(calEvent) {
            var calendarFilterID = window.calendarFilterID;
            var EventTypeFilterID = window.CRM.EventTypeFilterID;

            type = calEvent.event.extendedProps.realType;

            if (calEvent.backgroundColor) {
                calEvent.el.style.background = calEvent.backgroundColor;
            }

            /*var str = '<div class="fc-event-main"><div class="fc-event-main-frame"><div class="fc-event-title-container"><div class="fc-event-title fc-sticky">'
                + calEvent.event.extendedProps.icon + " " + calEvent.event.title
                + '</div></div></div></div><div class="fc-event-resizer fc-event-resizer-end"></div>';

            calEvent.el.innerHTML = str;*/

            if (type !== null){
                if (type == 'event' && EventTypeFilterID == -1 && calEvent.event.extendedProps.groupID == 0 && calEvent.event.extendedProps.calType < 2) {// the personal
                    return calEvent;
                } else if (type == 'event' && EventTypeFilterID == -2 && calEvent.event.extendedProps.groupID != 0) {// the groups
                    return calEvent;
                } else if (type == 'event' && EventTypeFilterID == -3 && calEvent.event.extendedProps.calType == 2) {// Room
                    return calEvent;
                } else if (type == 'event' && EventTypeFilterID == -4 && calEvent.event.extendedProps.calType == 3) {// Computer
                    return calEvent;
                } else if (type == 'event' && EventTypeFilterID == -5 && calEvent.event.extendedProps.calType == 4) {// Video
                    return calEvent;
                } else if (type == 'event' && EventTypeFilterID == -6 && calEvent.event.extendedProps.calType == 5) {// shared
                    return calEvent;
                } else if (type == 'event'
                    && (EventTypeFilterID == 0 || (EventTypeFilterID>0 && EventTypeFilterID == calEvent.event.extendedProps.eventTypeID) ) ) {
                    return calEvent;
                } else if(type == 'event'
                    && (EventTypeFilterID>0 && EventTypeFilterID != calEvent.event.extendedProps.eventTypeID) ) {
                    calEvent.event.setProp('display', 'none');
                    return calEvent;
                } else if ((calEvent.event.extendedProps.allDay || type != 'event') && EventTypeFilterID <= 0){// we are in a allDay event
                    if (type == 'anniversary' && anniversary == true || type == 'birthday' && birthday == true){
                        return calEvent;
                    } else {
                        calEvent.event.setProp('display', 'none');
                        return calEvent;
                    }
                }
            }

            calEvent.event.setProp('display', 'none');
            return calEvent;
        },
        eventResize: function(info) {
            var event = info.event;

            if (event.extendedProps.writeable == false || event.extendedProps.realType == 'birthday' || event.extendedProps.realType == 'anniversary') {
                window.CRM.DisplayAlert(i18next.t("Error"),i18next.t("This event isn't modifiable !!!"));
                window.CRM.calendar.refetchEvents();
                return;
            }

            var fmt = 'YYYY-MM-DD HH:mm:ss';

            var dateStart = moment(event.start).format(fmt);
            var dateEnd = moment(event.end).format(fmt);
            var reccurenceID = moment(event.extendedProps.reccurenceID).format(fmt);

            if (event.extendedProps.realType == "event" && event.extendedProps.recurrent == 0) {
                bootbox.confirm({
                    title: i18next.t("Resize Event") + "?",
                    message: i18next.t("Are you sure about this change?") + "\n"+event.title + " " + i18next.t("will be resized."),
                    buttons: {
                        cancel: {
                            label: '<i class="fa fa-times"></i> ' + i18next.t("Cancel")
                        },
                        confirm: {
                            label: '<i class="fa fa-check"></i> ' + i18next.t("Confirm")
                        }
                    },
                    callback: function (result) {
                        if (result == true)// only event can be drag and drop, not anniversary or birthday
                        {
                            window.CRM.APIRequest({
                                method: 'POST',
                                path: 'events/',
                                data: JSON.stringify({"eventAction":'resizeEvent',"calendarID":event.extendedProps.calendarID,"eventID":event.extendedProps.eventID,"start":dateStart,"end":dateEnd,"allEvents":false})
                            }).done(function(data) {
                                // now we can refresh the calendar
                                window.CRM.calendar.refetchEvents();
                                window.CRM.calendar.unselect();
                            });
                        } else {
                            info.revert();
                        }
                        console.log('This was logged in the callback: ' + result);
                    }
                });
            } else {
                var box = bootbox.dialog({
                    title: i18next.t("Resize Event") + "?",
                    message: i18next.t("You're about to resize all the events. Would you like to :"),
                    buttons: {
                        cancel: {
                            label:  '<i class="fa fa-times"></i> ' + i18next.t("Cancel"),
                            className: '<i class="fa fa-check"></i> ' + 'btn btn-default',
                            callback: function () {
                                info.revert();
                            }
                        },
                        oneEvent: {
                            label:  i18next.t("Only this Event"),
                            className: 'btn btn-info',
                            callback: function () {
                                window.CRM.APIRequest({
                                    method: 'POST',
                                    path: 'events/',
                                    data: JSON.stringify({"eventAction":'resizeEvent',"calendarID":event.extendedProps.calendarID,"eventID":event.extendedProps.eventID,"start":dateStart,"end":dateEnd,"allEvents":false,"reccurenceID":reccurenceID})
                                }).done(function(data) {
                                    // now we can refresh the calendar
                                    window.CRM.calendar.refetchEvents();
                                    window.CRM.calendar.unselect();
                                });
                            }
                        },
                        allEvents: {
                            label:  i18next.t("All Events"),
                            className: 'btn btn-primary',
                            callback: function () {
                                window.CRM.APIRequest({
                                    method: 'POST',
                                    path: 'events/',
                                    data: JSON.stringify({"eventAction":'resizeEvent',"calendarID":event.extendedProps.calendarID,"eventID":event.extendedProps.eventID,"start":dateStart,"end":dateEnd,"allEvents":true,"reccurenceID":reccurenceID})
                                }).done(function(data) {
                                    // now we can refresh the calendar
                                    window.CRM.calendar.refetchEvents();
                                    window.CRM.calendar.unselect();
                                });
                            }
                        }
                    }
                });
            }
        },
        eventDrop: function(info) {
            var event = info.event;

            if (event.extendedProps.writeable == false || event.extendedProps.realType == 'birthday' || event.extendedProps.realType == 'anniversary') {
                window.CRM.DisplayAlert(i18next.t("Error"),i18next.t("This event isn't modifiable !!!"));
                window.CRM.calendar.refetchEvents();
                return false;
            }

            var fmt = 'YYYY-MM-DD HH:mm:ss';

            var dateStart = moment(event.start).format(fmt);
            var dateEnd = moment(event.end).format(fmt);

            if (event.end == null) {
                dateEnd = dateStart;
            }

            if (event.extendedProps.realType == 'event' && event.extendedProps.recurrent == 0) {
                bootbox.confirm({
                    title:  i18next.t("Move Event") + "?",
                    message: i18next.t("Are you sure about this change?") + ((event.extendedProps.recurrent != 0)?" and the Linked Events ?":"") + "<br><br>   <b>\""  + event.title + "\"</b> " + i18next.t("will be dropped."),
                    buttons: {
                        cancel: {
                            label: '<i class="fa fa-times"></i> ' + i18next.t("Cancel")
                        },
                        confirm: {
                            label: '<i class="fa fa-check"></i> ' + i18next.t("Confirm")
                        }
                    },
                    callback: function (result) {
                        if (result == true)// only event can be drag and drop, not anniversary or birthday
                        {
                            window.CRM.APIRequest({
                                method: 'POST',
                                path: 'events/',
                                data: JSON.stringify({"eventAction":'moveEvent',"calendarID":event.extendedProps.calendarID,"eventID":event.extendedProps.eventID,"start":dateStart,"end":dateEnd})
                            }).done(function(data) {
                                // now we can refresh the calendar
                                if (data.status == "failed") {
                                    window.CRM.DisplayNormalAlert(i18next.t("Error"), data.message);
                                }
                                window.CRM.calendar.refetchEvents();
                                window.CRM.calendar.unselect();
                            });
                        } else {
                            info.revert();
                        }

                        console.log('This was logged in the callback: ' + result);
                    }
                });
            } else {
                var reccurenceID = moment(event.extendedProps.reccurenceID).format(fmt);
                var origStart   = moment(event.extendedProps.origStart).format(fmt);

                if (origStart == reccurenceID) {
                    var box = bootbox.dialog({
                        title: i18next.t("Move Event") + "?",
                        message: i18next.t("You're about to move all the events. Would you like to :"),
                        buttons: {
                            cancel: {
                                label:  '<i class="fa fa-times"></i> ' + i18next.t("Cancel"),
                                className: 'btn btn-default',
                                callback: function () {
                                    info.revert();
                                }
                            },
                            oneEvent: {
                                label:  i18next.t("Only this Event"),
                                className: 'btn btn-info',
                                callback: function () {

                                    window.CRM.APIRequest({
                                        method: 'POST',
                                        path: 'events/',
                                        data: JSON.stringify({"eventAction":'moveEvent',"calendarID":event.extendedProps.calendarID,"eventID":event.extendedProps.eventID,"start":dateStart,"end":dateEnd,"allEvents":false,"reccurenceID":reccurenceID})
                                    }).done(function(data) {
                                        if (data.status == "failed") {
                                            window.CRM.DisplayNormalAlert(i18next.t("Error"), data.message);
                                        }

                                        // now we can refresh the calendar
                                        window.CRM.calendar.refetchEvents();
                                        window.CRM.calendar.unselect();
                                    });
                                }
                            },
                            allEvents: {
                                label:  i18next.t("All Events"),
                                className: 'btn btn-primary',
                                callback: function () {

                                    window.CRM.APIRequest({
                                        method: 'POST',
                                        path: 'events/',
                                        data: JSON.stringify({"eventAction":'moveEvent',"calendarID":event.extendedProps.calendarID,"eventID":event.extendedProps.eventID,"start":dateStart,"end":dateEnd,"allEvents":true,"reccurenceID":reccurenceID})
                                    }).done(function(data) {
                                        if (data.status == "failed") {
                                            window.CRM.DisplayNormalAlert(i18next.t("Error"), data.message);
                                        }

                                        // now we can refresh the calendar
                                        window.CRM.calendar.refetchEvents();
                                        window.CRM.calendar.unselect();
                                    });
                                }
                            }
                        }
                    });
                } else {// this a recurence event yet modified
                    bootbox.confirm({
                        title:  i18next.t("Move Event") + "?",
                        message: i18next.t("Are you sure about this change?") + ((event.extendedProps.recurrent != 0)?" and the Linked Events ?":"") + "<br><br>   <b>\""  + event.title + "\"</b> " + i18next.t("will be dropped."),
                        buttons: {
                            cancel: {
                                label: '<i class="fa fa-times"></i> ' + i18next.t("Cancel")
                            },
                            confirm: {
                                label: '<i class="fa fa-check"></i> ' + i18next.t("Confirm")
                            }
                        },
                        callback: function (result) {
                            if (result == true)// only event can be drag and drop, not anniversary or birthday
                            {
                                window.CRM.APIRequest({
                                    method: 'POST',
                                    path: 'events/',
                                    data: JSON.stringify({"eventAction":'moveEvent',"calendarID":event.extendedProps.calendarID,"eventID":event.extendedProps.eventID,"start":dateStart,"end":dateEnd,"allEvents":false,"reccurenceID":reccurenceID})
                                }).done(function(data) {
                                    if (data.status == "failed") {
                                        window.CRM.DisplayNormalAlert(i18next.t("Error"), data.message);
                                    }

                                    // now we can refresh the calendar
                                    window.CRM.calendar.refetchEvents();
                                    window.CRM.calendar.unselect();
                                });
                            } else {
                                info.revert();
                            }

                            console.log('This was logged in the callback: ' + result);
                        }
                    });
                }
            }
        },
        select: function(selectionInfo) {//start end
            var start = selectionInfo.start;
            var end = selectionInfo.end;

            window.CRM.APIRequest({
                method: 'POST',
                path: 'calendar/numberofcalendars',
            }).done(function(data) {
                if (data.CalendarNumber > 0){
                    // We create the dialog
                    if (window.CRM.editor != null) {
                        CKEDITOR.remove(window.CRM.editor);
                        window.CRM.editor = null;
                    }

                    var modal = createEventEditorWindow (start,end);

                    // we add the calendars and the types
                    addCalendars();
                    addCalendarEventTypes(undefined,true);

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

                    $('.date-picker').datepicker({format:window.CRM.datePickerformat, language: window.CRM.lang});

                    $('.date-picker').click('focus', function (e) {
                        e.preventDefault();
                        $(this).datepicker('show');
                    });

                    $('.date-start').hide();
                    $('.date-end').hide();
                    $('.date-recurrence').hide();
                    $(".eventNotes").hide();

                    $("#typeEventrecurrence").prop("disabled", true);
                    $("#endDateEventrecurrence").prop("disabled", true);

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
                                extraPlugins : 'uploadfile,uploadimage,filebrowser',
                                uploadUrl: window.CRM.root+'/uploader/upload.php?type=publicDocuments',
                                imageUploadUrl: window.CRM.root+'/uploader/upload.php?type=publicImages',
                                filebrowserUploadUrl: window.CRM.root+'/uploader/upload.php?type=publicDocuments',
                                filebrowserBrowseUrl: window.CRM.root+'/browser/browse.php?type=publicDocuments',
                                skin: theme
                            });
                        } else {
                            window.CRM.editor = CKEDITOR.replace('eventNotes',{
                                customConfig: window.CRM.root+'/skin/js/ckeditor/configs/calendar_event_editor_config.js',
                                language : window.CRM.lang,
                                width : '100%',
                                skin: theme
                            });
                        }

                        add_ckeditor_buttons(window.CRM.editor);
                    }

                    $(".ATTENDENCES").hide();

                    initMap();

                    modal.modal("show");

                } else {
                    window.CRM.DisplayAlert(i18next.t("Error"),i18next.t("To add an event, You have to create a calendar or activate one first."));
                }
            });
        },
    });

    //window.CRM.calendar.change('dayMaxEventRows', false);

    window.CRM.calendar.render();
});

$(document).ready(function () {
  $(document).on('hidden.bs.modal','.bootbox.modal', function (e) {
    // solve a bug a bootbox is over another box
    if($('.modal').hasClass('in')) {
    $('body').addClass('modal-open');
    }
    // end of bug resolution

    if (eventCreated) {
      if (eventAttendees) {
        var box = bootbox.dialog({
           title: i18next.t('Event added'),
           message: i18next.t("Event was added successfully. Would you like to make the Attendance or to add attendees ?"),
           size: 'large',
           buttons: {
              cancel: {
                label:  '<i class="fa fa-times"></i> ' + i18next.t('No'),
                className: 'btn btn-default'
              },
              add: {
                 label: i18next.t('Add More Attendees'),
                 className: 'btn btn-info',
                 callback: function () {
                    location.href = window.CRM.root + '/EditEventAttendees.php';
                 }
              },
              confirm: {
                 label: '<i class="fa fa-check"></i> ' + i18next.t('Make Attendance'),
                 className: 'btn btn-success',
                 callback: function () {
                    location.href = window.CRM.root + '/Checkin.php';
                 }
              }
            }
        });

        box.show();
      } else {
        var box = window.CRM.DisplayAlert(i18next.t("Event added"),i18next.t("Event was added successfully."));

        setTimeout(function() {
          // be careful not to call box.hide() here, which will invoke jQuery's hide method
          box.modal('hide');
        }, 3000);
      }

      eventAttendees = false;
      eventCreated = false;
    }
  });
});
