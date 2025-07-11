//
//  This code is under copyright not under MIT Licence
//  copyright   : 2018 Philippe Logel all right reserved.
//                This code can't be included in another software.
//
//  Updated : 2020/06/18
//


$(function() {
    function render_container() {
        if (window.CRM.mailchimpIsActive) {
            // we first empty the container
            $("#container").html('<br><br><br><h2 class="headline text-primary text-center"><i class="fas fa-spin fa-spinner"></i> ' + i18next.t("Loading datas ...") + "</h2>");

            window.CRM.APIRequest({
                method: 'GET',
                path: 'mailchimp/lists'
            }, function (data) {

                if (data.MailChimpLists == null) {
                    $("#container").html('<h2 class="headline text-primary">' + i18next.t("No list are created with this account ...."));

                    return;
                }

                $(".mailchimp-message-is-activated").css("display", "block");
                $(".mailchimp-dashboard-list-visibility").css("display", "block");

                var len = data.MailChimpLists.length;

                // now we empty the menubar lists
                $(".lists_class_menu").removeClass("hidden");
                var lists_menu = $(".lists_class_menu").parent();
                var real_listMenu = $(lists_menu).find(".treeview-menu");

                real_listMenu.html("");

                var listViews = "";
                var listItems = "";

                for (i = 0; i < len; i++) {
                    var list = data.MailChimpLists[i];

                    listViews += '<div class="card">'
                        + '    <div class="card-header   border-1">'
                        + '      <h3 class="card-title"><i class="fas fa-list"></i> ' + i18next.t('Email List') + ' : ' + list.name + '</h3> <span style="float:right"> (' + ((list.marketing_permissions) ? i18next.t('GDPR') : '') + ')'
                        + '    </div>'
                        + '    <div class="card-body">'
                        + '      <div class="row" style="100%">'
                        + '        <div class="col-lg-4 col-lg-mailchimp">'
                        + '          <table width="350px">'
                        + '            <tr><td><b><i class="far fa-eye"></i> ' + i18next.t('Details') + '</b> </td><td></td></tr>'
                        + '            <tr><td>' + i18next.t('Subject') + '</td><td>"' + list.campaign_defaults.subject + '"</td></tr>'
                        + '            <tr><td>' + i18next.t('Members:') + '</td><td>' + list.stats.member_count + '</td></tr>'
                        //+'            <tr><td>' + i18next.t('Campaigns:') + '</td><td>' + list.stats.campaign_count + '</td></tr>'
                        + '            <tr><td>' + i18next.t('Unsubscribed count:') + '</td><td>' + list.stats.unsubscribe_count + '</td></tr>'
                        + '            <tr><td>' + i18next.t('Unsubscribed count since last send:') + '</td><td>' + list.stats.unsubscribe_count_since_send + '</td></tr>'
                        + '            <tr><td>' + i18next.t('Cleaned count:') + '</td><td>' + list.stats.cleaned_count + '</td></tr>'
                        + '            <tr><td>' + i18next.t('Cleaned count since last send:') + '</td><td>' + list.stats.cleaned_count_since_send + '</td></tr>'
                        + '          </table>'
                        + '        </div>'
                        + '        <div class="col-lg-4 col-lg-mailchimp">'
                        + '           <b><i class="fas fa-envelope-open-text"></i> ' + i18next.t('Campaigns') + '</b><br>';

                    let send_campaigns = 0;
                    var lenCampaigns = data.MailChimpCampaigns[i][send_campaigns].length;

                    listViews += '          <table width="300px">';

                    for (j = 0; j < lenCampaigns; j++) {
                        listViews += '<tr><td>• <a href="' + window.CRM.root + '/v2/mailchimp/campaign/' + data.MailChimpCampaigns[i][send_campaigns][j].id + '">' + data.MailChimpCampaigns[i][send_campaigns][j].settings.title + '</td><td>' + ' <b><span class="badge bg-' + ((data.MailChimpCampaigns[i][send_campaigns][j].status == 'sent') ? 'green' : 'gray') + '">' + i18next.t(data.MailChimpCampaigns[i][send_campaigns][j].status) + '</span></b>  </td></tr>';
                    }

                    let saved_campaigns = 1;
                    var lenCampaigns = data.MailChimpCampaigns[i][saved_campaigns].length;

                    for (j = 0; j < lenCampaigns; j++) {
                        listViews += '<tr><td>• <a href="' + window.CRM.root + '/v2/mailchimp/campaign/' + data.MailChimpCampaigns[i][saved_campaigns][j].id + '">' + data.MailChimpCampaigns[i][saved_campaigns][j].settings.title + '</td><td>' + ' <b><span class="badge bg-' + ((data.MailChimpCampaigns[i][saved_campaigns][j].status == 'sent') ? 'green' : 'gray') + '">' + i18next.t(data.MailChimpCampaigns[i][saved_campaigns][j].status) + '</span></b>  </td></tr>';
                    }

                    listViews += '          </table>';

                    listViews += '        </div>';

                    if (data.MailChimpLists[i].tags !== undefined && data.MailChimpLists[i].tags != null) {
                        var lenTags = data.MailChimpLists[i].tags.length;

                        if (lenTags) {

                            listViews += '        <div class="col-lg-4 col-lg-mailchimp">'
                                + '           <b><i class="icon fas fa-tags"></i> ' + i18next.t('Tags') + '</b><br>';

                            var tags = data.MailChimpLists[i].tags;

                            var tagsButtons = '';

                            if (lenTags) {
                                for (k = 0; k < lenTags; k++) {
                                    tagsButtons += '<a class="delete-tag" data-id="' + tags[k].id + '" data-listid="'
                                        + data.MailChimpCampaigns[i][send_campaigns].id + '"><i style="cursor:pointer; color:red;" class="icon far fa-trash-alt"></i></a> <b>'
                                        + data.MailChimpLists[i].tags[k].member_count + '</b> ' + tags[k].name + ' <br>';
                                }
                            }

                            listViews += tagsButtons;

                            listViews += '        </div>';

                        }
                    }

                    listViews += '      </div>' +
                        '</div>'
                        + '<div class="card-footer">'
                        + '<a class="btn btn btn-primary" href="' + window.CRM.root + '/v2/mailchimp/managelist/' + list.id + '" style="float:right"> <i class="fas fa-pencil-alt"></i> ' + i18next.t('Modify') + '</a>'
                        + '</div>'
                        + '    </div>';

                    listItems += '<li><a href="' + window.CRM.root + '/v2/mailchimp/managelist/' + list.id + '"><i class="far fa-circle"></i>' + list.name + '</a>';

                }

                $("#container").html(listViews);
                real_listMenu.html(listItems);
            });
        } else {
            var container = '<div class="row">'
                + '<div class="col-lg-12">'
                + '  <div class="card card-body">'
                + '    <div class="alert alert-danger alert-dismissible">'
                + '      <h4><i class="fas fa-ban"></i> MailChimp ' + i18next.t('is not configured') + '</h4>'
                + '      ' + i18next.t('Please update the') + ' MailChimp ' + i18next.t('API key in Setting->') + '<a href="' + window.CRM.root + '/v2/systemsettings/Integration">' + i18next.t('Edit General Settings') + '</a>,'
                + '      ' + i18next.t('then update') + ' sMailChimpApiKey. ' + i18next.t('For more info see our ') + '<a href="' + window.CRM.getSupportURL + '"> MailChimp +' + i18next.t('support docs.') + '</a>'
                + '    </div>'
                + '  </div>'
                + '</div>'
                + '</div>';

            $("#container").html(container);
        }
    }

    render_container();

    // the List Creator
    function BootboxContent() {
        var frm_str = '<form id="some-form">'
            + '<div>'
            + '<div class="row">'
            + '<div class="col-md-3"><span style="color: red">*</span>' + i18next.t('List Title') + ":</div>"
            + '<div class="col-md-9">'
            + "<input type='text' id='ListTitle' placeholder=\"" + i18next.t("Your List Title") + "\" size='30' maxlength='100' class='form-control form-control-sm'  width='100%' style='width: 100%' required>"
            + '</div>'
            + '</div>'
            + '<div class="row div-title">'
            + '<div class="col-md-3"><span style="color: red">*</span>' + i18next.t('Subject') + ":</div>"
            + '<div class="col-md-9">'
            + "<input type='text' id='Subject' placeholder=\"" + i18next.t("Your Subject") + "\" size='30' maxlength='100' class='form-control form-control-sm'  width='100%' style='width: 100%' required>"
            + '</div>'
            + '</div>'
            + '<div class="row div-title">'
            + '<div class="col-md-3"><span style="color: red">*</span>' + i18next.t('Permission Reminder') + ":</div>"
            + '<div class="col-md-9">'
            + "<textarea id='PermissionReminder' rows='3' maxlength='100' class='form-control form-control-sm'  width='100%' style='width: 100%' required placeholder=\"" + i18next.t("Permission Reminder") + "\"></textarea>"
            + '</div>'
            + '</div>'
            + '<div class="row div-title">'
            + '<div class="col-md-5">'
            + '<div class="checkbox">'
            + '<label>'
            + '<input type="checkbox" id="ArchiveBars"> ' + i18next.t('Archive Bars')
            + '</label>'
            + '</div>'
            + '</div>'
            + '</div>'
            + '<div class="row  div-title">'
            + '<div class="status-event-title">'
            + '<span style="color: red">*</span>' + i18next.t('Status')
            + '</div>'
            + '<div class="status-event">'
            + '<input type="radio" name="Status" value="prv" checked/> ' + i18next.t('Private')
            + '</div>'
            + '<div class="status-event">'
            + '<input type="radio" name="Status" value="pub" /> ' + i18next.t('Public')
            + '</div>'
            + '</div>'
            + '</div>'
            + '</form>';

        var object = $('<div/>').html(frm_str).contents();

        return object
    }

    function createListEditorWindow() {

        var modal = bootbox.dialog({
            message: BootboxContent(),
            title: i18next.t("List Creation"),
            buttons: [
                {
                    label: '<i class="fas fa-check"></i> ' + i18next.t("Close"),
                    className: "btn btn-default",
                    callback: function () {
                        console.log("just do something on close");
                    }
                },
                {
                    label: '<i class="fas fa-check"></i> ' + i18next.t("Save"),
                    className: "btn btn-primary",
                    callback: function () {
                        var ListTitle = $('form #ListTitle').val();

                        if (ListTitle) {
                            var Subject = $('form #Subject').val();
                            var PermReminder = $('form #PermissionReminder').val();
                            var ArchiveBars = $('#ArchiveBars').is(":checked");
                            var Status = $('input[name="Status"]:checked').val();

                            window.CRM.APIRequest({
                                method: 'POST',
                                path: 'mailchimp/createlist',
                                data: JSON.stringify({
                                    "ListTitle": ListTitle,
                                    "Subject": Subject,
                                    "PermissionReminder": PermReminder,
                                    "ArchiveBars": ArchiveBars,
                                    "Status": Status
                                })
                            }, function (data) {
                                if (data.success) {
                                    render_container();
                                    modal.modal("hide");
                                } else if (data.error) {
                                    window.CRM.DisplayAlert(i18next.t("Error"), i18next.t(data.error.detail));
                                }
                            });
                        } else {
                            window.CRM.DisplayAlert("Error", "You have to set a List Title for your eMail List");

                            return false;
                        }
                    }
                }
            ],
            show: false/*,
           onEscape: function() {
              modal.modal("hide");
           }*/
        });

        // this will ensure that image and table can be focused
        $(document).on('focusin', function (e) {
            e.stopImmediatePropagation();
        });

        return modal;
    }


    $(document).on("click", "#CreateList", function () {
        var modal = createListEditorWindow();

        modal.modal("show");
    });
});
