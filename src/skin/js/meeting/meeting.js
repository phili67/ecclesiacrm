$(document).ready(function () {
    if (navigator.userAgent.search("Safari") >= 0 && navigator.userAgent.search("Chrome") < 0)
    {
        window.CRM.DisplayAlert(i18next.t("Problem"), i18next.t("Safari isn't yet supported with Jitsi, use something else !<br/>• Your webcam<br/>• Sharing your windows<br/> won't work with meeting."))
    }

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
        bootbox.prompt(i18next.t("Set a Jitsi room name"), function(name){
            if ( name != '' && name != null) {
                window.CRM.APIRequest({
                    method: 'POST',
                    path: 'meeting/createMeetingRoom',
                    data: JSON.stringify({"roomName": name})
                }).done(function (data) {
                    location.reload();
                });
            }
        });
    });

    $('.selectRoom').click('focus', function () {
        var id = $(this).data('roomid');

        window.CRM.APIRequest({
            method: 'POST',
            path: 'meeting/selectMeetingRoom',
            data: JSON.stringify({"roomId": id})
        }).done(function (data) {
            location.reload();
        });
    });

    $('#delete-all-rooms').click('focus', function () {
        bootbox.confirm({
            title: i18next.t("Delete all Rooms?"),
            message: i18next.t("You're about to delete all of your rooms."),
            buttons: {
                cancel: {
                    label: '<i class="fa fa-times"></i> '+ i18next.t("Cancel")
                },
                confirm: {
                    label: '<i class="fa fa-check"></i> '+ i18next.t("Confirm")
                }
            },
            callback: function (result) {
                if (result) {
                    window.CRM.APIRequest({
                        method: 'DELETE',
                        path: 'meeting/deleteAllMeetingRooms'
                    }).done(function (data) {
                        location.reload();
                    });
                }
            }
        });

    });
});
