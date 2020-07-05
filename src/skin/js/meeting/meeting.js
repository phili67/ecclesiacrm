$(document).ready(function () {
    function addEvent(dateStart, dateEnd, windowTitle, title) {
        if (window.CRM.editor != null) {
            CKEDITOR.remove(window.CRM.editor);
            window.CRM.editor = null;
        }

        modal = createEventEditorWindow(dateStart, dateEnd, 'createEvent', 0, '', 'v2/calendar', windowTitle, title);

        // we add the calendars and the types
        addCalendars();
        addCalendarEventTypes(-1, true);

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

    $('#add-event').click('focus', function (e) {
        var fmt = 'YYYY-MM-DD HH:mm:ss';

        var dateStart = moment().format(fmt);
        var dateEnd = moment().format(fmt);

        addEvent(dateStart, dateEnd, i18next.t("Appointment"), sPageTitle);
    });

    $('#newRoom').click('focus', function () {
        alert('coucou');
    })
});
