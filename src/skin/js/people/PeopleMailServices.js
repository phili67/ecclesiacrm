$(function () {
    // mail services management for persons
    if (window.CRM.normalMail != undefined) {
        window.CRM.APIRequest({
            method: 'POST',
            path: 'persons/isMailServiceActive',
            data: JSON.stringify({ "personId": window.CRM.currentPersonID, "email": window.CRM.normalMail })
        }, function (data) {
            if (data.success) {
                if (data.isIncludedInMailing) {
                    $("#NewsLetterSend").css('color', 'green');
                    $("#NewsLetterSend").html('<i class="fas fa-check"></i>');
                    if (data.mailServiceActive) {
                        $("#mailServiceUserNormal").html(data.mailingList);
                    }
                } else {
                    $("#NewsLetterSend").css('color', 'red');
                    $("#NewsLetterSend").html('<i class="fas fa-times"></i>');
                    $("#mailServiceUserNormal").html(i18next.t("None"));
                }
            } else {
                $("#NewsLetterSend").css('color', 'red');
                $("#NewsLetterSend").html('<i class="fas fa-times"></i>');
            }
        });
    }

    if (window.CRM.workMail != undefined) {
        window.CRM.APIRequest({
            method: 'POST',
            path: 'persons/isMailServiceActive',
            data: JSON.stringify({ "personId": window.CRM.currentPersonID, "email": window.CRM.workMail })
        }, function (data) {
            if (data.success) {
                if (data.isIncludedInMailing) {
                    $("#NewsLetterSend").css('color', 'green');
                    $("#NewsLetterSend").html('<i class="fas fa-check"></i>');
                    $("#mailServiceUserWork").html(data.mailingList);
                } else {
                    $("#NewsLetterSend").css('color', 'red');
                    $("#NewsLetterSend").html('<i class="fas fa-times"></i>');
                    $("#mailServiceUserWork").html(i18next.t("None"));
                }
            } else {
                $("#NewsLetterSend").css('color', 'red');
                $("#NewsLetterSend").html('<i class="fas fa-times"></i>');
            }
        });
    }
    // end mail services management

    // mailchimp management for families
    if (window.CRM.familyMail != undefined) {
        window.CRM.APIRequest({
            method: 'POST',
            path: 'families/isMailServiceActive',
            data: JSON.stringify({ "familyId": window.CRM.currentFamily, "email": window.CRM.familyMail })
        }, function (data) {
            if (data.success) {
                if (data.isIncludedInMailing) {
                    $("#NewsLetterSend").css('color', 'green');
                    $("#NewsLetterSend").html('<i class="fas fa-check"></i>');
                    if (data.mailServiceActive) {
                        $("#mailServiceUserNormal").html(data.mailingList);
                    }
                } else {
                    $("#NewsLetterSend").css('color', 'red');
                    $("#NewsLetterSend").html('<i class="fas fa-times"></i>');
                    $("#mailServiceUserNormal").text(i18next.t("None"));
                }
            } else {
                $("#NewsLetterSend").css('color', 'red');
                $("#NewsLetterSend").html('<i class="fas fa-times"></i>');
            }
        });
    }

    // end of mailchimp management
});