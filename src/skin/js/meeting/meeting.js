$(document).ready(function () {
    if (navigator.userAgent.search("Safari") >= 0 && navigator.userAgent.search("Chrome") < 0)
    {
        window.CRM.DisplayAlert(i18next.t("Problem"), i18next.t("Safari isn't yet supported with Jitsi, use something else !<br/>• Your webcam<br/>• Sharing your windows<br/> won't work with meeting."))
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
