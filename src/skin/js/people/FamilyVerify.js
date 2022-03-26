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
                            "BirthDayDate": BirthDayDate
                        }, function (data) {
                            // TODO : update in live the view !!! or reload the view via api
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

    $(".modifyPerson").click(function () {
        var personId = $(this).data("id");

        $.post(window.CRM.root + '/ident/my-profile/getPersonInfo/', {"token": window.CRM.token, "personId": personId}, function (data) {
            var modal = PersonWindow(data.html, personId);
            modal.modal("show");

            $('.date-picker').datepicker({format: window.CRM.datePickerformat, language: window.CRM.lang});
        });
    });

    $(".deletePerson").click(function () {
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

    $(".modifyFamily").click(function () {
        var familyId = $(this).data("id");

        $.post(window.CRM.root + '/ident/my-profile/getFamilyInfo/', {"token": window.CRM.token, "familyId": familyId}, function (data) {

            var modal = FamilyWindow(data.html);
            modal.modal("show");

            $('.date-picker').datepicker({format: window.CRM.datePickerformat, language: window.CRM.lang});
        });
    });

    $(".deleteFamily").click(function () {
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

    $(".exitSession").click(function (){
        $.post(window.CRM.root + '/ident/my-profile/exitSession/', {"token": window.CRM.token}, function (data) {
            window.location = window.location.href;
        });
    });
});
