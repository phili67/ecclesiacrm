$(document).ready(function () {
    $('#onlineVerifySiteBtn').hide();
    $("#confirm-modal-done").hide();
    $("#confirm-modal-error").hide();

    $("#onlineVerifyBtn").click(function () {
        $.post(window.CRM.root + '/ident/my-profile/' + window.CRM.token,
            {
                message: $("#confirm-info-data").val()
            },
            function (data, status) {
                $('#confirm-modal-collect').hide();
                $("#onlineVerifyCancelBtn").hide();
                $("#onlineVerifyBtn").hide();
                $("#onlineVerifySiteBtn").show();
                if (status == "success") {
                    $("#confirm-modal-done").show();
                } else {
                    $("#confirm-modal-error").show();
                }
            });
    });

    function BootboxContent(data) {

        var frm_str = '<form id="some-form">';

        frm_str += data
            + '</form>';

        var object = $('<div/>').html(frm_str).contents();

        return object;
    }

    function PersonWindow(data, personId) {

        var modal = bootbox.dialog({
            message: BootboxContent(data),
            buttons: [
                {
                    label: '<i class="fas fa-times"></i> ' + i18next.t("Close"),
                    className: "btn btn-default",
                    callback: function () {
                    }
                },
                {
                    label: '<i class="fas fa-check"></i> ' + i18next.t("Save"),
                    className: "btn btn-primary",
                    callback: function () {
                        var FirstName = $('form #FirstName').val();
                        var MiddleName = $('form #MiddleName').val();
                        var LastName = $('form #LastName').val();
                        var FamilyRole = $('form #FamilyRole').val();
                        var homePhone = $('form #homePhone').val();
                        var workPhone = $('form #workPhone').val();
                        var cellPhone = $('form #cellPhone').val();
                        var email = $('form #email').val();
                        var workemail = $('form #workemail').val();
                        var BirthDayDate = $('form #BirthDayDate').val();

                        var fmt = window.CRM.datePickerformat.toUpperCase();;

                        var real_dateTime = moment(BirthDayDate,fmt).format('YYYY-MM-DD');


                        $.post(window.CRM.root + '/ident/my-profile/modifyPersonInfo/', {
                            "token": window.CRM.token,
                            "personId": personId,
                            "FirstName": FirstName,
                            "MiddleName": MiddleName,
                            "LastName": LastName,
                            "FamilyRole": FamilyRole,
                            "homePhone": homePhone,
                            "workPhone": workPhone,
                            "cellPhone": cellPhone,
                            "email": email,
                            "workemail": workemail,
                            "BirthDayDate": real_dateTime
                        }, function (data) {
                            $(".person-container-" + personId).html(data.content);
                        });
                    }
                }
            ],
            show: false,
            onEscape: function () {
            }
        });

        // this will ensure that image and table can be focused
        $(document).on('focusin', function (e) {
            e.stopImmediatePropagation();
        });

        return modal;
    }

    $(document).on("click", ".modifyPerson", function () {
        var personId = $(this).data("id");

        $.post(window.CRM.root + '/ident/my-profile/getPersonInfo/', {"token": window.CRM.token, "personId": personId}, function (data) {
            var modal = PersonWindow(data.html, personId);
            modal.modal("show");

            $('.date-picker').datepicker({format: window.CRM.datePickerformat, language: window.CRM.lang});
        });
    });

    $(document).on("click", ".deletePerson", function () {
        var personId = $(this).data("id");

        bootbox.confirm(i18next.t("Confirm Delete"), function(confirmed) {
            if (confirmed) {
                $.post(window.CRM.root + '/ident/my-profile/deletePerson/', {
                    "token": window.CRM.token,
                    "personId": personId
                }, function (data) {
                    $(".person-container-" + personId).html('');
                });
            }
        });
    });

    function FamilyWindow(data) {

        var modal = bootbox.dialog({
            message: BootboxContent(data),
            size: "large",
            buttons: [
                {
                    label: '<i class="fas fa-times"></i> ' + i18next.t("Close"),
                    className: "btn btn-default",
                    callback: function () {
                    }
                },
                {
                    label: '<i class="fas fa-check"></i> ' + i18next.t("Save"),
                    className: "btn btn-primary",
                    callback: function () {

                    }
                }
            ],
            show: false,
            onEscape: function () {
            }
        });

        // this will ensure that image and table can be focused
        $(document).on('focusin', function (e) {
            e.stopImmediatePropagation();
        });

        return modal;
    }

    $(document).on("click", ".modifyFamily", function () {
        var familyId = $(this).data("id");

        $.post(window.CRM.root + '/ident/my-profile/getFamilyInfo/', {"token": window.CRM.token, "familyId": familyId}, function (data) {

            var modal = FamilyWindow(data.html);
            modal.modal("show");

            $('.date-picker').datepicker({format: window.CRM.datePickerformat, language: window.CRM.lang});
        });
    });

    $(document).on("click", ".deleteFamily", function () {
        var familyId = $(this).data("id");

        bootbox.confirm(i18next.t("Confirm Delete"), function(confirmed) {
            if (confirmed) {
                $.post(window.CRM.root + '/ident/my-profile/deleteFamily/', {
                    "token": window.CRM.token,
                    "familyId": familyId
                }, function (data) {
                    location.reload();
                });
            }
        });
    });

    $(document).on("click", ".exitSession", function () {
        $.post(window.CRM.root + '/ident/my-profile/exitSession/', {"token": window.CRM.token}, function (data) {
            window.location = window.location.href;
        });
    });
});
