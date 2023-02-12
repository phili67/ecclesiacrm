$(document).ready(function () {

    $(".check_all").click(function () {
        var state = this.checked;
        $(".checkbox_users").each(function () {
            $(this)[0].checked = state;
            var tr = $(this).closest("tr");
            if (state) {
                $(tr).addClass('selected');
            } else {
                $(tr).removeClass('selected');
            }
        });
    });


    $('#user-listing-table').on('click', 'tr', function () {
        $(this).toggleClass('selected');

        var table = $('#user-listing-table').DataTable();
        var data = table.row(this).data();

        if (data != undefined) {
            click_tr = true;
            var userID = $(data[0]).data("id");
            var state = $(this).hasClass("selected");
            $('.checkbox_user' + userID).prop('checked', state);
            click_tr = false;
        }
    });

    $(".changeRole").click(function () {
        var roleID = $(this).data("id");
        var roleName = this.innerText;
        var userID = -1;

        $(".checkbox_users").each(function () {
            if (this.checked) {
                userID = $(this).data("id");
                _val = $(this).val();

                window.CRM.APIRequest({
                    method: 'POST',
                    path: 'users/applyrole',
                    data: JSON.stringify({"userID": userID, "roleID": roleID})
                },function (data) {
                    if (data.success == true) {
                        // à terminer !!!
                        $('.role' + data.userID).html(data.roleName);
                    }
                });
            }
        });

        if (userID == -1) {
            window.CRM.DisplayAlert(i18next.t("Error"), i18next.t("You've to check at least one user."));
        }
    });


    $("#user-listing-table tbody").on('click', '.webdavkey', function () {
        var userID = $(this).data("userid");

        window.CRM.APIRequest({
            method: 'POST',
            path: 'users/webdavKey',
            data: JSON.stringify({"userID": userID})
        },function (data) {
            if (data.status == 'success') {
                var message = i18next.t("The WebDav Key is") + " : ";
                if (data.token != null) {
                    message += data.token;
                } else {
                    message += i18next.t("None");
                }

                message += "<br>" + i18next.t("The public WebDav Key is") + " : ";

                if (data.token2 != null) {
                    message += data.token2;
                } else {
                    message += i18next.t("None");
                }
                window.CRM.DisplayAlert(i18next.t("WebDav key"), message);
            }
        });
    });

    $("#user-listing-table tbody").on('click', '.lock-unlock', function () {
        var userID = $(this).data("userid");
        var userName = $(this).data("username");
        var button = $(this)
        var content = $(this).find('i');
        var lock = content.hasClass('fa-lock');

        window.CRM.APIRequest({
            method: 'POST',
            path: 'users/lockunlock',
            data: JSON.stringify({"userID": userID})
        },function (data) {
            if (data.success == true) {
                if (lock == false) {
                    content.removeClass('fa-unlock');
                    content.addClass('fa-lock');
                    button.css('color', 'red');
                    window.CRM.showGlobalMessage(i18next.t("User") + ' ' + userName + ' ' + i18next.t("is now locked"), "warning");
                } else {
                    content.removeClass('fa-lock');
                    content.addClass('fa-unlock');
                    button.css('color', 'green');
                    window.CRM.showGlobalMessage(i18next.t("User") + ' ' + userName + ' ' + i18next.t("is now unlocked"), "success");
                }
            }
        });
    });

    window.CRM.fmt = "";

    if (window.CRM.timeEnglish == true) {
        window.CRM.fmt = window.CRM.datePickerformat.toUpperCase() + ' hh:mm a';
    } else {
        window.CRM.fmt = window.CRM.datePickerformat.toUpperCase() + ' HH:mm';
    }

    $.fn.dataTable.moment(window.CRM.fmt);

    $("#user-listing-table").DataTable({
        "language": {
            "url": window.CRM.plugin.dataTable.language.url
        },
        responsive: true
    });

    $("#user-listing-table tbody").on('click', '.deleteUser', function () {
        var userId = $(this).data('id');
        var userName = $(this).data('name');

        bootbox.confirm({
            title: i18next.t("User Delete Confirmation"),
            message: '<p style="color: red">' +
                i18next.t("Please confirm removal of user status from:") + '<b>' + userName + '</b><br><br>' +
                i18next.t("Be carefull, You are about to lose the home folder and the associated files, the Calendars, the Share calendars and all the events too, for") + ':<b> ' + userName + '</b><br><br>' +
                i18next.t("This can't be undone") + '</p>',
            callback: function (result) {
                if (result) {
                    window.CRM.APIRequest({
                        method: "DELETE",
                        path: "users/" + userId
                    },function (data) {
                        if (data.status == "success")
                            $("#row-" + userId).remove();
                    });
                }
            }
        });
    });

    $("#user-listing-table tbody").on('click', '.restUserLoginCount', function () {
        var userId = $(this).data('id');
        var userName = $(this).data('name');
        var parentTd = $(this).parent();

        bootbox.confirm({
            title: i18next.t("Action Confirmation"),
            message: '<p style="color: red">' +
                i18next.t("Please confirm reset failed login count") + ": <b>" + userName + "</b></p>",
            callback: function (result) {
                if (result) {
                    $.ajax({
                        method: "POST",
                        url: window.CRM.root + "/api/users/" + userId + "/login/reset",
                        dataType: "json",
                        encode: true,
                    }).done(function (data) {
                        if (data.status == "success")
                            parentTd.html('0');
                        window.CRM.showGlobalMessage(i18next.t("Reset failed login count for") + ' ' + userName + ' ' + i18next.t('done.'), "info");
                    });
                }
            }
        });
    });

    $("#user-listing-table tbody").on('click', '.resetUserPassword', function () {
        var userId = $(this).data('id');
        var userName = $(this).data('name');

        bootbox.confirm({
            title: i18next.t("Action Confirmation"),
            message: '<p style="color: red">' +
                i18next.t("Please confirm the password reset of this user") + ": <b>" + userName + "</b></p>",
            callback: function (result) {
                if (result) {
                    $.ajax({
                        method: "POST",
                        url: window.CRM.root + "/api/users/" + userId + "/password/reset",
                        dataType: "json",
                        encode: true,
                    }).done(function (data) {
                        if (data.status == "success")
                            window.CRM.showGlobalMessage(i18next.t("Password reset for") + userName, "info");
                    });
                }
            }
        });
    });

    $('#user-listing-table tbody').on('click', '.control-account', function() {
        var userId = $(this).data("userid");
        window.CRM.APIRequest({
            method: 'POST',
            path: 'users/controlAccount',
            data: JSON.stringify({"userID": userId})
        },function (data) {
            if (data.success) {
                window.location = window.CRM.root;
            }
        });
    });

    $("#user-listing-table tbody").on('click', '.two-fa-manage', function () {
        var userID = $(this).data('userid');

        var modal = bootbox.dialog({
            title: i18next.t("Two factors authentications"),
            message: '<p><ul>' +
                '<li>' +
                    i18next.t("Delete") + " : " + i18next.t("to remove two-factor authentication") +
                '</li>' +
                '<li>' +
                    i18next.t("Pending") + " : " + i18next.t("Gives the user 60 seconds to log in with their recovery codes. The user will then have to delete or simply rescan the QR-code in the OTP Management application.") +
                '</li>' +
                '</ul>' +
                '</p>',
            buttons: [
                {
                    label: '<i class="fas fa-times"></i> ' + i18next.t("Close"),
                    className: "btn btn-secondary",
                    callback: function() {
                    }
                },
                {
                    label: '<i class="fas fa-trash-alt"></i> ' + i18next.t("Delete"),
                    className: "btn btn-danger",
                    callback: function() {
                        window.CRM.APIRequest({
                            method: 'POST',
                            path: 'users/2fa/remove',
                            data: JSON.stringify({"userID": userID})
                        },function (data) {
                            location.reload();
                        });
                    }
                },
                {
                    label: '<i class="fas fa-clock"></i> ' + i18next.t("Pending"),
                    className: "btn btn-primary",
                    callback: function() {
                        window.CRM.APIRequest({
                            method: 'POST',
                            path: 'users/2fa/pending',
                            data: JSON.stringify({"userID": userID})
                        },function (data) {
                            i18next.t("The user has 60 seconds to use his recovery codes.");
                        });
                    }
                }
            ],
            show: false,
            onEscape: function() {
                modal.modal("hide");
            }
        });

        modal.modal("show");
    });
});

