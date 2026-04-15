//
//  This code is under copyright not under MIT Licence
//  copyright   : 2018 Philippe Logel all right reserved.
//                This code can't be included in another software.
//
//  Updated : 2020/06/18
//


$(function () {
    function render_container() {
        if (window.CRM.mailchimpIsActive) {
            // we first empty the container
            $("#container").html(`
                <div class="d-flex flex-column align-items-center justify-content-center" style="min-height: 40vh;">
                    <div class="spinner-border text-primary mb-4" role="status" style="width: 4rem; height: 4rem;">
                        <span class="visually-hidden">${i18next.t('Loading...')}</span>
                    </div>
                    <h2 class="text-primary fw-bold mb-2">${i18next.t('Loading data...')}</h2>
                    <p class="text-muted">${i18next.t('Please wait while we retrieve your MailChimp lists.')}</p>
                </div>
            `);

            window.CRM.APIRequest({
                method: 'GET',
                path: 'mailchimp/lists'
            }, function (data) {

                if (data.MailChimpLists == null) {
                    let emptyListHtml = `
                        <div class="row justify-content-center mt-5">
                            <div class="col-lg-8">
                                <div class="card card-outline card-warning shadow-sm rounded-4">
                                    <div class="card-body text-center py-5">
                                        <i class="fas fa-list-alt fa-3x text-warning mb-3"></i>
                                        <h2 class="headline text-warning mb-3">${i18next.t('No list are created with this account ....')}</h2>
                                        <p class="text-muted mb-0">${i18next.t('You can create a new MailChimp list using the button above.')}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    $("#container").html(emptyListHtml);
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

                    listViews += `<div class="card card-outline card-primary shadow-sm rounded-4 mb-4">
                        <div class="card-header bg-body d-flex flex-wrap justify-content-between align-items-center">
                          <div class="d-flex align-items-center flex-wrap">
                            <span class="d-flex align-items-center me-3 mb-2 mb-md-0"><i class="fas fa-list-alt text-primary me-2"></i> <span class="fw-bold h5 mb-0">${i18next.t('Email List')} :</span></span>
                            <span class="fw-bold h5 mb-0">${list.name}</span>
                          </div>
                          ${(list.marketing_permissions) ? '<span class="badge bg-info text-dark ms-2">GDPR</span>' : ''}
                        </div>
                        <div class="card-body">
                          <div class="row">
                            <div class="col-lg-4 col-lg-mailchimp mb-3">
                             <b><i class="fas fa-info-circle"></i> ${i18next.t('Details')}</b><br>
                              <table class="table table-sm table-borderless mb-0">
                                <tr><td><i class="fas fa-heading text-primary me-2"></i> ${i18next.t('Subject')}</td><td><span class="text-primary">"${list.campaign_defaults.subject}"</span></td></tr>
                                <tr><td><i class="fas fa-users text-secondary me-2"></i> ${i18next.t('Members:')}</td><td><span class="badge bg-secondary">${list.stats.member_count}</span></td></tr>
                                <tr><td><i class="fas fa-user-slash text-warning me-2"></i> ${i18next.t('Unsubscribed count:')}</td><td><span class="badge bg-warning text-dark">${list.stats.unsubscribe_count}</span></td></tr>
                                <tr><td><i class="fas fa-user-minus text-warning me-2"></i> ${i18next.t('Unsubscribed count since last send:')}</td><td><span class="badge bg-warning text-dark">${list.stats.unsubscribe_count_since_send}</span></td></tr>
                                <tr><td><i class="fas fa-broom text-danger me-2"></i> ${i18next.t('Cleaned count:')}</td><td><span class="badge bg-danger">${list.stats.cleaned_count}</span></td></tr>
                                <tr><td><i class="fas fa-broom text-danger me-2"></i> ${i18next.t('Cleaned count since last send:')}</td><td><span class="badge bg-danger">${list.stats.cleaned_count_since_send}</span></td></tr>
                              </table>
                            </div>
                            <div class="col-lg-4 col-lg-mailchimp">
                                 <b><i class="fas fa-envelope-open-text"></i> ${i18next.t('Campaigns')}</b><br>`;

                    let send_campaigns = 0;
                    var lenCampaigns = data.MailChimpCampaigns[i][send_campaigns].length;

                    listViews += '          <table class="table table-sm table-borderless mb-0">';

                    for (j = 0; j < lenCampaigns; j++) {
                        let status = data.MailChimpCampaigns[i][send_campaigns][j].status;
                        let badgeClass = (status === 'sent') ? 'success' : 'secondary';
                        listViews += `<tr>
                                                        <td class="align-middle">
                                                            <i class="fas ${status === 'sent' ? 'fa-paper-plane text-success' : 'fa-edit text-secondary'} me-2"></i>
                                                            <a href="${window.CRM.root}/v2/mailchimp/campaign/${data.MailChimpCampaigns[i][send_campaigns][j].id}" class="text-decoration-none">${data.MailChimpCampaigns[i][send_campaigns][j].settings.title}</a>
                                                        </td>
                            <td class="align-middle"><span class="badge bg-${badgeClass}">${i18next.t(status)}</span></td>
                            </tr>`;
                    }

                    let saved_campaigns = 1;
                    var lenCampaigns = data.MailChimpCampaigns[i][saved_campaigns].length;

                    for (j = 0; j < lenCampaigns; j++) {
                        let status = data.MailChimpCampaigns[i][saved_campaigns][j].status;
                        let badgeClass = (status === 'sent') ? 'success' : 'secondary';
                        listViews += `<tr>
                            <td class="align-middle">• <a href="${window.CRM.root}/v2/mailchimp/campaign/${data.MailChimpCampaigns[i][saved_campaigns][j].id}" class="text-decoration-none">${data.MailChimpCampaigns[i][saved_campaigns][j].settings.title}</a></td>
                            <td class="align-middle"><span class="badge bg-${badgeClass}">${i18next.t(status)}</span></td>
                            </tr>`;
                    }

                    listViews += '          </table>';

                    listViews += '        </div>';

                    if (data.MailChimpLists[i].tags !== undefined && data.MailChimpLists[i].tags != null) {
                        var lenTags = data.MailChimpLists[i].tags.length;

                        if (lenTags) {

                            listViews += `        <div class="col-lg-4 col-lg-mailchimp">
                                <b><i class="icon fas fa-tags"></i> ${i18next.t('Tags')}</b><br>`;

                            var tags = data.MailChimpLists[i].tags;


                            if (lenTags) {
                                listViews += '<div class="mt-2">';
                                for (k = 0; k < lenTags; k++) {
                                    listViews += `
                                        <div class="badge bg-light border text-dark d-flex align-items-center px-2 py-2 mb-2 shadow-sm w-100" style="font-size:1rem; justify-content: start;">
                                            <a class="delete-tag ms-3" data-id="${tags[k].id}" data-listid="${data.MailChimpCampaigns[i][send_campaigns].id}" title="${i18next.t('Delete')}">
                                                <i class="fas fa-times-circle text-danger ms-2" style="cursor:pointer;font-size:1.1em;"></i>
                                            </a>&nbsp;&nbsp;                                            
                                            <i class="fas fa-tag text-info me-2"></i>&nbsp;
                                            <span class="me-2">${tags[k].name}</span>&nbsp;
                                            <span class="badge bg-info text-dark ms-auto">${data.MailChimpLists[i].tags[k].member_count}</span>
                                        </div>
                                    `;
                                }
                                listViews += '</div>';
                            }

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
            let container = `
                <div class="row justify-content-center mt-5">
                    <div class="col-lg-10">
                        <div class="card card-outline card-danger shadow-sm rounded-4">
                            <div class="card-body">
                                <div class="alert alert-danger d-flex align-items-center mb-3">
                                    <i class="fas fa-ban fa-2x me-3"></i>
                                    <div>
                                        <h4 class="alert-heading mb-2">MailChimp ${i18next.t('is not configured')}</h4>
                                        <p class="mb-1">${i18next.t('Please update the')} <b>MailChimp</b> ${i18next.t('API key in Setting->')} <a href="${window.CRM.root}/v2/systemsettings/integration" class="alert-link"><i class="fas fa-cog me-1"></i>${i18next.t('Edit General Settings')}</a>.</p>
                                        <p class="mb-1">${i18next.t('Then update')} <b>sMailChimpApiKey</b>.</p>
                                        <p class="mb-0">${i18next.t('For more info see our ')}<a href="${window.CRM.getSupportURL}" class="alert-link" target="_blank"><i class="fab fa-mailchimp me-1"></i>MailChimp ${i18next.t('support docs.')}</a></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            $("#container").html(container);
        }
    }

    render_container();

    // the List Creator
    function BootboxContent() {
        var frm_str = `
                    <form id="some-form">
                        <div class="container-fluid px-0">
                            <div class="mb-3 row align-items-center">
                                <label for="ListTitle" class="col-sm-4 col-form-label text-end">
                                    <span class="text-danger">*</span> <i class="fas fa-heading text-primary me-1"></i> ${i18next.t('List Title')} :
                                </label>
                                <div class="col-sm-8 d-flex align-items-center gap-2">
                                    <input type="text" id="ListTitle" placeholder="${i18next.t('Your List Title')}" maxlength="100" class="form-control form-control-sm" required>
                                </div>
                            </div>
                            <div class="mb-3 row align-items-center">
                                <label for="Subject" class="col-sm-4 col-form-label text-end">
                                    <span class="text-danger">*</span> <i class="fas fa-envelope text-primary me-1"></i> ${i18next.t('Subject')} :
                                </label>
                                <div class="col-sm-8 d-flex align-items-center gap-2">
                                    <input type="text" id="Subject" placeholder="${i18next.t('Your Subject')}" maxlength="100" class="form-control form-control-sm" required>
                                </div>
                            </div>
                            <div class="mb-3 row align-items-center">
                                <label for="PermissionReminder" class="col-sm-4 col-form-label text-end">
                                    <span class="text-danger">*</span> <i class="fas fa-info-circle text-primary me-1"></i> ${i18next.t('Permission Reminder')} :
                                </label>
                                <div class="col-sm-8 d-flex align-items-center gap-2">
                                    <textarea id="PermissionReminder" rows="3" maxlength="100" class="form-control form-control-sm" required placeholder="${i18next.t('Permission Reminder')}"></textarea>
                                </div>
                            </div>
                            <div class="mb-3 row align-items-center">
                                <div class="offset-sm-4 col-sm-8 d-flex align-items-center gap-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="ArchiveBars">
                                        <label class="form-check-label" for="ArchiveBars"><i class="fas fa-archive text-secondary me-1"></i> ${i18next.t('Archive Bars')}</label>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3 row align-items-center">
                                <label class="col-sm-4 col-form-label text-end">
                                    <span class="text-danger">*</span> <i class="fas fa-user-shield text-primary me-1"></i> ${i18next.t('Status')}
                                </label>
                                <div class="col-sm-8 d-flex align-items-center gap-3">
                                    <div class="form-check me-3">
                                        <input class="form-check-input" type="radio" name="Status" id="StatusPrv" value="prv" checked>
                                        <label class="form-check-label" for="StatusPrv"><i class="fas fa-lock me-1"></i> ${i18next.t('Private')}</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="Status" id="StatusPub" value="pub">
                                        <label class="form-check-label" for="StatusPub"><i class="fas fa-globe me-1"></i> ${i18next.t('Public')}</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                `;

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

    // Gestionnaire suppression tag
    $(document).on("click", ".delete-tag", function (event) {
        event.preventDefault();
        var tagID = $(this).data("id");
        var listID = $(this).data("listid");

        bootbox.confirm({
            title: i18next.t("You're about to delete a tag!"),
            message: i18next.t("This will also delete the tag for all the members in this list. Are you sure ?"),
            buttons: {
                confirm: {
                    label: '<i class="fas fa-times"></i> ' + i18next.t('Yes'),
                    className: 'btn-danger'
                },
                cancel: {
                    label: '<i class="fas fa-check"></i> ' + i18next.t('No'),
                    className: 'btn-primary'
                }
            },
            callback: function (result) {
                if (result) {
                    window.CRM.dialogLoadingFunction(i18next.t("Deleting tag"), function() {
                        window.CRM.APIRequest({
                            method: 'POST',
                            path: 'mailchimp/list/removeTag',
                            data: JSON.stringify({"list_id": listID, "tag_ID": tagID})
                        }, function (data) {
                            // On recharge la vue
                            render_container();
                            window.CRM.closeDialogLoadingFunction();
                        });
                    });
                }
            }
        });
    });

});
