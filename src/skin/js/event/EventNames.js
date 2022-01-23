//
//  This code is under copyright not under MIT Licence
//  copyright   : 2018 Philippe Logel all right reserved not MIT licence
//                This code can't be incoprorated in another software without authorizaion
//
//  Updated : 2019/04/19
//

window.CRM.editor = null;

$(document).ready(function () {

    function addEvent(dateStart, dateEnd, typeID) {
        window.CRM.APIRequest({
            method: 'POST',
            path: 'calendar/numberofcalendars',
        }).done(function(data) {
            if (data.CalendarNumber > 0) {
                if (window.CRM.editor != null) {
                    CKEDITOR.remove(window.CRM.editor);
                    window.CRM.editor = null;
                }

                modal = createEventEditorWindow(dateStart, dateEnd, 'createEvent', 0, '', 'EventNames.php');

                // we add the calendars and the types
                addCalendars();
                addCalendarEventTypes(typeID, false);
                addAttendees(typeID);

                // finish installing the window
                installAndfinishEventEditorWindow();

                $("#typeEventrecurrence").prop("disabled", true);
                $("#endDateEventrecurrence").prop("disabled", true);

                // this will ensure that image and table can be focused
                $(document).on('focusin', function (e) {
                    e.stopImmediatePropagation();
                });

                $('#EventCalendar option:first-child').attr("selected", "selected");

                modal.modal("show");

                initMap();
            } else {
                window.CRM.DisplayAlert(i18next.t("Error"),i18next.t("To add an event, You have to create a calendar or activate one first."));
            }
        });
    }


    $(document).on('click', '.add-event', function () {
        var typeID = $(this).data("typeid");
        var fmt = 'YYYY-MM-DD HH:mm:ss';

        var dateStart = moment().format(fmt);
        var dateEnd = moment().format(fmt);

        addEvent(dateStart, dateEnd, typeID);
    });

    $(document).on('click', '.delete-event', function () {
        var typeID = $(this).data("typeid");

        bootbox.confirm({
            title: i18next.t("Delete Event Type") + "?",
            message: i18next.t("This action can never be undone !!!!"),
            buttons: {
                cancel: {
                    label: '<i class="fas fa-times"></i> ' + i18next.t("Cancel")
                },
                confirm: {
                    label: '<i class="fas fa-check"></i> ' + i18next.t("Confirm")
                }
            },
            callback: function (result) {
                if (result == true)// only event can be drag and drop, not anniversary or birthday
                {
                    window.CRM.APIRequest({
                        method: 'POST',
                        path: 'events/deleteeventtype',
                        data: JSON.stringify({"typeID": typeID})
                    }).done(function (data) {
                        location.reload();
                    });
                }
            }
        });
    });
});
