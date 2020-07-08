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
        if (window.CRM.editor != null) {
            CKEDITOR.remove(window.CRM.editor);
            window.CRM.editor = null;
        }

        modal = createEventEditorWindow(dateStart, dateEnd, 'createEvent', 0, '', 'EventNames.php');

        // we add the calendars and the types
        addCalendars();
        addCalendarEventTypes(typeID, false);
        addAttendees (typeID);


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

        $('.date-picker').datepicker({format: window.CRM.datePickerformat, language: window.CRM.lang});

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

        // this will ensure that image and table can be focused
        $(document).on('focusin', function (e) {
            e.stopImmediatePropagation();
        });

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
                    filebrowserBrowseUrl: window.CRM.root + '/browser/browse.php?type=publicDocuments'
                });
            } else {
                window.CRM.editor = CKEDITOR.replace('eventNotes', {
                    customConfig: window.CRM.root + '/skin/js/ckeditor/configs/calendar_event_editor_config.js',
                    language: window.CRM.lang,
                    width: '100%'
                });
            }

            add_ckeditor_buttons(window.CRM.editor);
        }


        $(".ATTENDENCES").hide();

        $('#EventCalendar option:first-child').attr("selected", "selected");

        modal.modal("show");

        initMap();
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
