//
//  This code is under copyright not under MIT Licence
//  copyright   : 2018 Philippe Logel all right reserved.
//                This code can't be included in another software.
//
//  Updated : 2020/06/18
//


$(function() {
    var editor = null;

    // this will create the toolbar for the textarea
    var theme = 'default';
    if (window.CRM.bDarkMode) {
        theme = 'default';
    }

    if (editor == null) {
        if (window.CRM.bEDrive) {
            editor = window.CRM.createEditor('campaignContent', {
                preset: 'campaign',
                language: window.CRM.lang,
                width: '100%',
                uploadType: 'publicImages',
            });
        } else {
            editor = window.CRM.createEditor('campaignContent', {
                preset: 'campaign',
                language: window.CRM.lang,
                width: '100%',
            });
        }


    }

    window.CRM.APIRequest({
        method: 'GET',
        path: 'mailchimp/campaign/' + window.CRM.campaign_Id + '/content'
    },function (data) {
        if (data.success) {
            editor.then(function (instance) {
                instance.setData(data.content);
            });
        } else if (data.error) {
            window.CRM.DisplayAlert(i18next.t("Error"), i18next.t(data.error.detail));
        }
    });

    // I have to do this because EventCalendar isn't yet present when you load the page the first time
    $(document).on('change', '#checkboxaCampaignSchedule', function (value) {
        if (window.CRM.isCampaignSent) return;

        var _val = $('#checkboxaCampaignSchedule').is(":checked");

        $("#dateCampaign").prop("disabled", (_val == 0) ? true : false);
        $("#timeCampaign").prop("disabled", (_val == 0) ? true : false);

        if (_val == 0 && (window.CRM.status == "paused" || window.CRM.status == "save")) {
            $("#sendCampaign").show();
        } else {
            $("#sendCampaign").hide();
        }

        var fmt = window.CRM.datePickerformat.toUpperCase();

        var date = moment().format(fmt);
        $("#dateCampaign").val(date);

        if (window.CRM.timeEnglish == true) {
            time_format = 'h:00 A';
        } else {
            time_format = 'H:00';
        }

        var time = moment().format(time_format);
        $("#timeCampaign").val(time);
    });

    $(document).on("click", "#saveCampaign", function () {
        var subject = $("#CampaignSubject").val();
        var content = window.CRM.getEditor('campaignContent').getData();
        var isSchedule = $('#checkboxaCampaignSchedule').is(":checked");
        var realScheduleDate = '';

        if (isSchedule) {
            var dateStart = $('#dateCampaign').val();
            var timeStart = $('#timeCampaign').val();

            var fmt = window.CRM.datePickerformat.toUpperCase();

            if (window.CRM.timeEnglish == 'true') {
                time_format = 'h:mm A';
            } else {
                time_format = 'H:mm';
            }

            fmt = fmt + ' ' + time_format;

            realScheduleDate = moment(dateStart + ' ' + timeStart, fmt).utc().format();
        }

        window.CRM.dialogLoadingFunction(i18next.t("Saving Campaign ..."), function() {
            window.CRM.APIRequest({
                method: 'POST',
                path: 'mailchimp/campaign/actions/save',
                data: JSON.stringify({
                    "campaign_id": window.CRM.campaign_Id,
                    "subject": subject,
                    "content": content,
                    "realScheduleDate": realScheduleDate,
                    "isSchedule": isSchedule,
                    "oldStatus": window.CRM.status
                })
            },function (data) {
                window.CRM.closeDialogLoadingFunction();

                if (data.success == true) {
                    window.CRM.DisplayAlert(i18next.t("Campaign"), i18next.t("saved successfully"));
                } else if (data.success == false && data.error1.detail) {
                    window.CRM.DisplayAlert(i18next.t("Error"), data.error1.detail);
                } else if (data.success == false && data.error2.detail) {
                    window.CRM.DisplayAlert(i18next.t("Error"), data.error2.detail);
                } else if (data.success == false && data.error3.detail) {
                    window.CRM.DisplayAlert(i18next.t("Error"), data.error3.detail);
                }

                $('.status').html("(" + i18next.t(data.status) + ")");

                window.CRM.status = data.status;

                if (data.status == "paused") {
                    $("#sendCampaign").show();
                }
            });
        });
    });

    $(document).on("click", "#sendCampaign", function () {

        bootbox.confirm({
            message: i18next.t("You're about to send your campaign! Are you sure ?"),
            buttons: {
                confirm: {
                    label: '<i class="fas fa-check"></i> ' + i18next.t('Yes', {ns: 'MailChimp'}),
                    className: 'btn-success'
                },
                cancel: {
                    label: '<i class="fas fa-times"></i> ' + i18next.t('No', {ns: 'MailChimp'}),
                    className: 'btn-default'
                }
            },
            callback: function (result) {
                if (result) {
                    window.CRM.APIRequest({
                        method: 'POST',
                        path: 'mailchimp/campaign/actions/send',
                        data: JSON.stringify({"campaign_id": window.CRM.campaign_Id})
                    },function (data) {
                        if (data.success) {
                            window.location.href = window.CRM.root + "/v2/mailchimp/managelist/" + window.CRM.list_Id;
                        } else if (data.success == false && data.error) {
                            window.CRM.DisplayAlert(i18next.t("Error", {ns: 'MailChimp'}), i18next.t(data.error.detail, {ns: 'MailChimp'}));
                        }
                    });
                }
            }
        });
    });

    $(document).on("click", "#deleteCampaign", function () {

        bootbox.confirm({
            message: i18next.t("You're about to delete your campaign! Are you sure ?"),
            buttons: {
                confirm: {
                    label: '<i class="fas fa-times"></i> ' + i18next.t('Yes', {ns: 'MailChimp'}),
                    className: 'btn-danger'
                },
                cancel: {
                    label: '<i class="fas fa-check"></i> ' + i18next.t('No', {ns: 'MailChimp'}),
                    className: 'btn-primary'
                }
            },
            callback: function (result) {
                if (result) {
                    window.CRM.APIRequest({
                        method: 'POST',
                        path: 'mailchimp/campaign/actions/delete',
                        data: JSON.stringify({"campaign_id": window.CRM.campaign_Id})
                    },function (data) {
                        if (data.success) {
                            window.location.href = window.CRM.root + "/v2/mailchimp/managelist/" + window.CRM.list_Id;
                        } else if (data.success == false && data.error) {
                            window.CRM.DisplayAlert(i18next.t("Error", {ns: 'MailChimp'}), i18next.t(data.error.detail, {ns: 'MailChimp'}));
                        }
                    });
                }
            }
        });
    });
});
