//
//  This code is under copyright not under MIT Licence
//  copyright   : 2018 Philippe Logel all right reserved.
//                This code can't be included in another software.
//
//  Updated : 2020/06/18
//
$(document).ready(function () {
    window.CRM.editor = null;

    function addTagsToMainDropdown() {
        $("#allTags").empty();
        $("#allCampaignTags").empty();
        $("#addCreateTagsDropAll").empty();

        window.CRM.APIRequest({
            method: 'POST',
            path: 'mailchimp/list/getAllTags',
            data: JSON.stringify({"list_id": window.CRM.list_ID})
        }).done(function (data) {
            $("#allTags").append('<a class="dropdown-item addTagButton" data-id="-1" data-name=""></i><i class="fa fa-plus"></i><i class="fa fa-tag"> ' + i18next.t("Add a new tag") + '</a>');
            $("#allTags").append('<div class="dropdown-divider"></div>');
            $("#allTags").append('<a class="dropdown-item deleteTagButton" data-id="-1" data-name=""><i class="fa fa-minus"></i><i class="fa fa-tag"></i> ' + i18next.t("Delete tag from subscriber(s)") + '</a>');
            $("#allTags").append('<div class="dropdown-divider"></div>');

            $("#addCreateTagsDropAll").append('<a class="dropdown-item addTagButton" data-id="-1" data-name=""></i><i class="fa fa-plus"></i><i class="fa fa-tag"> ' + i18next.t("Add a new tag") + '</a>');
            $("#addCreateTagsDropAll").append('<div class="dropdown-divider"></div>');


            if (data.result != undefined) {
                var len = data.result.length;

                for (i = 0; i < len; ++i) {
                    $("#allTags").append('<a class="dropdown-item addTagButton" data-id="' + data.result[i].id + '" data-name="' + data.result[i].name + '"><i class="fa fa-tag"></i> ' + data.result[i].name + '</a>');
                    $("#allCampaignTags").append('<a class="dropdown-item CreateCampaign" data-id="' + data.result[i].id + '" data-name="' + data.result[i].name + '"><i class="fa fa-tag"></i> ' + data.result[i].name + '</a>');

                    $("#addCreateTagsDropAll").append('<a class="dropdown-item delete-tag" data-id="' + data.result[i].id + '" data-listid="' + data.result[i].list_id + '"><i class="fa fa-minus"></i><i class="fa fa-tag"></i> ' + i18next.t("Delete tag") + ' : ' + data.result[i].name + '</a>');
                }
            }
        });
    }

    function render_container() {
        $("#check_all").prop('checked', false);
        $("#deleteMembers").prop('disabled', true);
        $(".addTagButton").prop('disabled', true);
        $(".addTagButtonDrop").prop('disabled', true);
        $(".subscribeButton").prop('disabled', true);
        $(".subscribeButtonDrop").prop('disabled', true);

        if (window.CRM.mailchimpIsActive) {
            window.CRM.APIRequest({
                method: 'GET',
                path: 'mailchimp/list/' + window.CRM.list_ID
            }).done(function (data) {
                window.CRM.closeDialogLoadingFunction();

                // we set correctly the buttons
                if (data.membersCount == 0) {
                    $("#CreateCampaign").prop("disabled", true);
                    $("#addCreateCampaignTagDrop").prop("disabled", true);
                    $("#deleteAllSubScribers").prop("disabled", true);
                } else {
                    $("#CreateCampaign").prop("disabled", false);
                    $("#addCreateCampaignTagDrop").prop("disabled", false);
                    $("#deleteAllSubScribers").prop("disabled", false);
                }
                // we empty first the container
                $("#container").html(i18next.t(i18next.t("Loading resources ...")));

                var listItems = "";

                var list = data.MailChimpList;

                var listView = '<div class="card-header with-border">'
                    + '      <h3 class="card-title"><i class="fa fa-list"></i> ' + i18next.t('Email List') + '   (' + i18next.t('Details') + ')</h3>'
                    + '      <div class="card-tools pull-right">'
                    + '          <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fa fa-plus"></i></button>'
                    + '      </div>'
                    + '    </div>'
                    + '    <div class="card-body">'
                    + '      <div class="row">'
                    + '        <div class="col-md-12">'
                    + '          <table width="100%">'
                    + '            <tr><td><b><i class="fa fa-eye"></i> ' + i18next.t('Details') + '</b> </td><td></td></tr>'
                    + '            <tr><td>' + i18next.t('Subject') + '</td><td>"' + list.campaign_defaults.subject + '"</td></tr>'
                    + '            <tr><td>' + i18next.t('Members:') + '</td><td>' + list.stats.member_count + '</td></tr>'
                    //+'            <tr><td>' + i18next.t('Campaigns:') + '</td><td>' + list.stats.campaign_count + '</td></tr>'
                    + '            <tr><td>' + i18next.t('Unsubscribed count:') + '</td><td>' + list.stats.unsubscribe_count + '</td></tr>'
                    + '            <tr><td>' + i18next.t('Unsubscribed count since last send:') + '</td><td>' + list.stats.unsubscribe_count_since_send + '</td></tr>'
                    + '            <tr><td>' + i18next.t('Cleaned count:') + '</td><td>' + list.stats.cleaned_count + '</td></tr>'
                    + '            <tr><td>' + i18next.t('Cleaned count since last send:') + '</td><td>' + list.stats.cleaned_count_since_send + '</td></tr>'
                    + '          </table>'
                    + '        </div>'
                    + '      </div><br/>'
                    + '      <div class="row">'
                    + '        <div class="col-md-12">'
                    + '           <b><i class="icon fa fa-mail-forward"></i> ' + i18next.t('Campaigns') + '</b><br>';

                var lenCampaigns = data.MailChimpCampaign.length;

                listView += '          <table width="100%">';

                var tags = '';

                for (j = 0; j < lenCampaigns; j++) {
                    if (data.membersCount == 0) {
                        listView += '<tr><td>• ' + data.MailChimpCampaign[j].settings.title + '</td></tr>';
                    } else {
                        listView += '<tr><td>• <a href="' + window.CRM.root + '/v2/mailchimp/campaign/' + data.MailChimpCampaign[j].id + '">' + data.MailChimpCampaign[j].settings.title + '</td><td>' + ' <b><span style="color:' + ((data.MailChimpCampaign[j].status == 'sent') ? 'green' : 'gray') + '">(' + i18next.t(data.MailChimpCampaign[j].status) + ')</span></b>  </td></tr>';
                    }
                }

                if (lenCampaigns == 0) {
                    listView += '<tr><td>&nbsp;&nbsp; <i class="icon fa fa-tags"></i>' + i18next.t('Campaign') + '</td></tr>';
                }

                listView += '          </table>';

                listView += '        </div>';

                listView += '   </div><br/>';

                var lenTags = data.MailChimpList.tags.length;

                if (lenTags) {
                    listView += '    <div class="row">';
                    listView += '        <div class="col-12">'
                        + '           <b><i class="icon fa fa-tags"></i> ' + i18next.t('Tags') + '</b><br>';

                    var tags = data.MailChimpList.tags;

                    var tagsButtons = '';

                    if (lenTags) {
                        for (k = 0; k < lenTags; k++) {
                            tagsButtons += '<a class="delete-tag" data-id="' + tags[k].id + '" data-listid="' + data.MailChimpList.id + '"><i style="cursor:pointer; color:red;" class="icon fa fa-close"></i></a>' + tags[k].name + '<br>';
                        }
                    }

                    listView += tagsButtons;

                    listView += '        </div>';

                }

                listView += '      </div>'
                    + '    </div>';

                listItems += '<li><a href="' + window.CRM.root + '/v2/mailchimp/managelist/' + list.id + '"><i class="fa fa-circle-o"></i>' + list.name + '</a>';

                $("#container").html(listView);
            });
        }
    }

    render_container();
    addTagsToMainDropdown();
    // render the main page

    $(document).on("click", ".delete-tag", function () {
        var tagID = $(this).data("id");
        var listID = $(this).data("listid");

        bootbox.confirm({
            title: i18next.t("You're about to delete a tag!"),
            message: i18next.t("This will also delete the tag for all the members in this list. Are you sure ?"),
            buttons: {
                confirm: {
                    label: '<i class="fa fa-times"></i> ' + i18next.t('Yes'),
                    className: 'btn-danger'
                },
                cancel: {
                    label: '<i class="fa fa-check"></i> ' + i18next.t('No'),
                    className: 'btn-primary'
                }
            },
            callback: function (result) {
                if (result) {
                    window.CRM.dialogLoadingFunction(i18next.t("Deleting tag"));

                    window.CRM.APIRequest({
                        method: 'POST',
                        path: 'mailchimp/list/removeTag',
                        data: JSON.stringify({"list_id": listID, "tag_ID": tagID})
                    }).done(function (data) {
                        render_container();
                        addTagsToMainDropdown();
                        window.CRM.dataListTable.ajax.reload(null, false);
                    });
                }
            }
        });
    });

    $(document).on("click", ".CreateCampaign", function () {
        var tagId = $(this).data("id");
        var tagName = $(this).data("name");

        if (window.CRM.editor) {
            CKEDITOR.remove(window.CRM.editor);
            window.CRM.editor = null;
        }

        var modal = createCampaignEditorWindow(tagId, tagName);

        var theme = 'n1theme,/skin/js/ckeditor/themes/n1theme/';
        if (window.CRM.bDarkMode) {
            theme = 'moono-dark,/skin/js/ckeditor/themes/moono-dark/';
        }

        // this will create the toolbar for the textarea
        if (window.CRM.editor == null) {
            if (window.CRM.bEDrive) {
                window.CRM.editor = CKEDITOR.replace('campaignNotes', {
                    customConfig: window.CRM.root + '/skin/js/ckeditor/configs/campaign_editor_config.js',
                    language: window.CRM.lang,
                    width: '100%',
                    extraPlugins: 'uploadfile,uploadimage,filebrowser',
                    uploadUrl: window.CRM.root + '/uploader/upload.php?type=publicDocuments',
                    imageUploadUrl: window.CRM.root + '/uploader/upload.php?type=publicImages',
                    filebrowserUploadUrl: window.CRM.root + '/uploader/upload.php?type=publicDocuments',
                    filebrowserBrowseUrl: window.CRM.root + '/browser/browse.php?type=publicDocuments',
                    skin: theme
                });
            } else {
                window.CRM.editor = CKEDITOR.replace('campaignNotes', {
                    customConfig: window.CRM.root + '/skin/js/ckeditor/configs/campaign_editor_config.js',
                    language: window.CRM.lang,
                    width: '100%',
                    skin: theme
                });
            }

            add_ckeditor_buttons(window.CRM.editor);
            add_ckeditor_buttons_merge_tag_mailchimp(window.CRM.editor);
        }

        modal.modal("show");
    });

    $(".person-group-Id-Share").select2({
        language: window.CRM.shortLocale,
        minimumInputLength: 1,
        placeholder: " -- " + i18next.t("A person name or Family or Group") + " -- ",
        allowClear: true, // This is for clear get the clear button if wanted
        ajax: {
            url: function (params) {
                return window.CRM.root + "/api/mailchimp/search/" + params.term;
            },
            dataType: 'json',
            delay: 50,
            data: "",
            processResults: function (data, params) {
                return {results: data};
            },
            cache: true
        }
    });


    $(".person-group-Id-Share").on("select2:select", function (e) {
        var list_id = $(this).data("listid");

        if (e.params.data.personID !== undefined) {
            window.CRM.dialogLoadingFunction(i18next.t("Loading subscriber"));

            window.CRM.APIRequest({
                method: 'POST',
                path: 'mailchimp/addperson',
                data: JSON.stringify({"list_id": list_id, "personID": e.params.data.personID})
            }).done(function (data) {
                if (data.success) {
                    window.CRM.dataListTable.ajax.reload(null, false);
                    render_container();
                } else if (data.error) {
                    window.CRM.DisplayAlert(i18next.t("Error"), i18next.t(data.error.detail));
                    window.CRM.closeDialogLoadingFunction();
                }
                $(".person-group-Id-Share").val('').trigger('change');
            });
        } else if (e.params.data.groupID !== undefined) {
            window.CRM.dialogLoadingFunction(i18next.t("Loading subscribers from Group"));

            window.CRM.APIRequest({
                method: 'POST',
                path: 'mailchimp/addgroup',
                data: JSON.stringify({"list_id": list_id, "groupID": e.params.data.groupID})
            }).done(function (data) {
                if (data.success) {
                    window.CRM.dataListTable.ajax.reload(null, false);
                    render_container();
                } else if (data.error) {
                    window.CRM.DisplayAlert(i18next.t("Error"), i18next.t(data.error.detail));
                    window.CRM.closeDialogLoadingFunction();
                }
            });
        } else if (e.params.data.familyID !== undefined) {
            window.CRM.dialogLoadingFunction(i18next.t("Loading subscribers from family"));

            window.CRM.APIRequest({
                method: 'POST',
                path: 'mailchimp/addfamily',
                data: JSON.stringify({"list_id": list_id, "familyID": e.params.data.familyID})
            }).done(function (data) {
                if (data.success) {
                    window.CRM.dataListTable.ajax.reload(null, false);
                    render_container();
                } else if (data.error) {
                    window.CRM.DisplayAlert(i18next.t("Error"), i18next.t(data.error.detail));
                    window.CRM.closeDialogLoadingFunction();
                }
            });
        } else if (e.params.data.typeId !== undefined && e.params.data.typeId == 1) {
            window.CRM.dialogLoadingFunction(i18next.t("Loading all persons from EcclesiaCRM<br>This could take a while !") + '<br>' + i18next.t("In fact, you've better to quit the CRM, wait 5 minutes and make your campaigns after.<br>To import huge datas, MailChimp API is slow."));

            window.CRM.APIRequest({
                method: 'POST',
                path: 'mailchimp/addallpersons',
                data: JSON.stringify({"list_id": list_id})
            }).done(function (data) {
                if (data.success) {
                    window.CRM.dataListTable.ajax.reload(null, false);
                    render_container();
                } else if (data.error) {
                    window.CRM.DisplayAlert(i18next.t("Error"), i18next.t(data.error.detail));
                    window.CRM.dataListTable.ajax.reload(null, false);
                    render_container();
                }
            });
        } else if (e.params.data.typeId !== undefined && e.params.data.typeId == 2) {
            window.CRM.dialogLoadingFunction(i18next.t("Loading all newsletter subscribers from EcclesiaCRM<br>This could take a while !") + '<br>' + i18next.t("In fact, you've better to quit the CRM, wait 5 minutes and make your campaigns after.<br>To import huge datas, MailChimp API is slow."));

            window.CRM.APIRequest({
                method: 'POST',
                path: 'mailchimp/addallnewsletterpersons',
                data: JSON.stringify({"list_id": list_id})
            }).done(function (data) {
                if (data.success) {
                    window.CRM.dataListTable.ajax.reload(null, false);
                    render_container();
                } else if (data.error) {
                    //window.CRM.DisplayAlert(i18next.t("Error"),i18next.t(data.error.detail));
                    window.CRM.dataListTable.ajax.reload(null, false);
                    render_container();
                }
            });
        } else if (e.params.data.typeId !== undefined && e.params.data.typeId == 3) {
            window.CRM.dialogLoadingFunction(i18next.t("Loading all families first headpeople subscribers from EcclesiaCRM<br>This could take a while !") + '<br>' + i18next.t("In fact, you've better to quit the CRM, wait 5 minutes and make your campaigns after.<br>To import huge datas, MailChimp API is slow."));

            window.CRM.APIRequest({
                method: 'POST',
                path: 'mailchimp/addAllFamilies',
                data: JSON.stringify({"list_id": list_id})
            }).done(function (data) {
                if (data.success) {
                    window.CRM.dataListTable.ajax.reload(null, false);
                    render_container();
                } else if (data.error) {
                    //window.CRM.DisplayAlert(i18next.t("Error"),i18next.t(data.error.detail));
                    window.CRM.dataListTable.ajax.reload(null, false);
                    render_container();
                }
            });
        }

    });

// the DataTable
    var columns = [
        {
            width: 'auto',
            title: "",
            data: 'id',
            render: function (data, type, full, meta) {
                return '<input type="checkbox" class="checkbox_users checkbox_user_' + full.id + '" name="AddRecords" data-id="' + full.id + '" data-email="' + full.email_address + '">';
            }
        },
        {
            width: 'auto',
            title: i18next.t('Actions'),
            data: 'id',
            render: function (data, type, full, meta) {
                return '<a class="edit-subscriber" data-id="' + full.email_address + '"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp;&nbsp;&nbsp;<a class="delete-subscriber" data-id="' + full.email_address + '"><i class="fa fa-trash-o" aria-hidden="true" style="color:red"></i></a>';
            }
        },
        {
            width: 'auto',
            title: i18next.t('Email'),
            data: 'email_address',
            render: function (data, type, full, meta) {
                if (!window.CRM.canSeePrivacyData) {
                    return i18next.t('Private Data');
                }
                return data;
            }
        },
        {
            width: 'auto',
            title: i18next.t('First Name'),
            data: 'merge_fields',
            render: function (data, type, full, meta) {
                return data.FNAME;
            }
        },
        {
            width: 'auto',
            title: i18next.t('Last Name'),
            data: 'merge_fields',
            render: function (data, type, full, meta) {
                return data.LNAME;
            }
        },
        {
            width: 'auto',
            title: i18next.t('Email Marketing'),
            data: 'status',
            render: function (data, type, full, meta) {
                var res = i18next.t(data);
                if (data == 'subscribed') {
                    res = '<p class="text-green">' + res + '</p>';
                } else if (data == 'unsubscribed') {
                    res = '<p class="text-orange">' + res + '</p>';
                } else {
                    res = '<p class="text-red">' + res + '</p>';
                }
                return res;
            }
        },
        {
            width: 'auto',
            title: i18next.t('Tags'),
            data: 'tags',
            render: function (data, type, full, meta) {
                var res = '';
                data.forEach(function (element) {
                    res += element.name + ' ';
                });
                return res;
            }
        }
    ];

    if (window.CRM.bWithAddressPhone) {
        columns.push(
            {
                width: 'auto',
                title: i18next.t('Address'),
                data: 'merge_fields',
                render: function (data, type, full, meta) {
                    return data.ADDRESS.addr1 + ' ' + data.ADDRESS.city + ' ' + data.ADDRESS.zip + ' ' + data.ADDRESS.state + ' ' + data.ADDRESS.state;
                }
            },
            {
                width: 'auto',
                title: i18next.t('Phone'),
                data: 'merge_fields',
                render: function (data, type, full, meta) {
                    return data.PHONE;
                }
            }
        );
    }


    var dataTableConfig = {
        ajax: {
            url: window.CRM.root + "/api/mailchimp/listmembers/" + window.CRM.list_ID,
            type: 'GET',
            contentType: "application/json",
            dataSrc: "MailChimpMembers"
        },
        columns: columns,
        responsive: true,
        pageLength: 50,
        createdRow: function (row, data, index) {
            $(row).addClass("duplicateRow");
        }
    }

    $.extend(dataTableConfig, window.CRM.plugin.dataTable);

    window.CRM.dataListTable = $("#memberListTable").DataTable(dataTableConfig);

    $(document).on("click", ".edit-subscriber", function () {
        var email = $(this).data("id");

        bootbox.prompt({
            title: i18next.t("Select status for : ") + email,
            inputType: 'select',
            inputOptions: [
                {
                    text: i18next.t('Subscribed'),
                    value: 'subscribed',
                },
                {
                    text: i18next.t('Unsubscribed'),
                    value: 'unsubscribed',
                }
            ],
            callback: function (status) {
                if (status) {
                    window.CRM.dialogLoadingFunction(i18next.t("Changing status ..."));

                    window.CRM.APIRequest({
                        method: 'POST',
                        path: 'mailchimp/status',
                        data: JSON.stringify({"list_id": window.CRM.list_ID, "status": status, "email": email})
                    }).done(function (data) {
                        if (data.success) {
                            window.CRM.dataListTable.ajax.reload(null, false);
                            render_container();
                        } else if (data.success == false && data.error) {
                            window.CRM.closeDialogLoadingFunction();
                            window.CRM.DisplayAlert(i18next.t("Error"), i18next.t(data.error.detail));
                        }
                    });
                }
            }
        });
    });

    $(document).on("click", ".delete-subscriber", function () {
        var email = $(this).data("id");

        bootbox.confirm({
            message: i18next.t("You're about to delete a subscriber! Are you sure ?"),
            buttons: {
                confirm: {
                    label: i18next.t('Yes'),
                    className: '<i class="fa fa-times"></i> ' + 'btn-danger'
                },
                cancel: {
                    label: '<i class="fa fa-check"></i> ' + i18next.t('No'),
                    className: 'btn-primary'
                }
            },
            callback: function (result) {
                if (result) {
                    window.CRM.dialogLoadingFunction(i18next.t('Deleting Subscriber...'));
                    window.CRM.APIRequest({
                        method: 'POST',
                        path: 'mailchimp/suppress',
                        data: JSON.stringify({"list_id": window.CRM.list_ID, "email": email})
                    }).done(function (data) {
                        if (data.success) {
                            window.CRM.dataListTable.ajax.reload(null, false);
                            render_container();
                        } else if (data.success == false && data.error) {
                            window.CRM.closeDialogLoadingFunction();
                            window.CRM.DisplayAlert(i18next.t("Error"), i18next.t(data.error.detail));
                        }
                    });
                }
            }
        });
    });

    $(document).on("click", "#deleteList", function () {
        var list_id = $(this).data("listid");

        bootbox.confirm({
            message: i18next.t("Do you really want to delete this mailing list ?"),
            buttons: {
                confirm: {
                    label: '<i class="fa fa-times"></i> ' + i18next.t('Yes'),
                    className: 'btn-danger'
                },
                cancel: {
                    label: '<i class="fa fa-check"></i> ' + i18next.t('No'),
                    className: 'btn-primary'
                }
            },
            callback: function (result) {
                if (result) {
                    window.CRM.APIRequest({
                        method: 'POST',
                        path: 'mailchimp/deletelist',
                        data: JSON.stringify({"list_id": window.CRM.list_ID})
                    }).done(function (data) {
                        if (data.success) {
                            window.location.href = window.CRM.root + "/v2/mailchimp/dashboard";
                        } else if (data.error) {
                            window.CRM.DisplayAlert(i18next.t("Error"), i18next.t(data.error.detail));
                        }
                    });
                }
            }
        });
    });

    $(document).on("click", "#deleteAllSubScribers", function () {
        var list_id = $(this).data("listid");

        bootbox.confirm({
            message: i18next.t("Are you sure you want to delete all the subscribers"),
            buttons: {
                confirm: {
                    label: '<i class="fa fa-times"></i> ' + i18next.t('Yes'),
                    className: 'btn-danger'
                },
                cancel: {
                    label: '<i class="fa fa-check"></i> ' + i18next.t('No'),
                    className: 'btn-primary'
                }
            },
            callback: function (result) {
                if (result) {
                    window.CRM.dialogLoadingFunction(i18next.t('Deleting all subscribers...') + '<br>' + i18next.t("In fact, you've better to leave the CRM, and in a quater of an hour re-open it to manage your list.<br>To delete huge datas, MailChimp API is slow."));

                    window.CRM.APIRequest({
                        method: 'POST',
                        path: 'mailchimp/deleteallsubscribers',
                        data: JSON.stringify({"list_id": window.CRM.list_ID})
                    }).done(function (data) {
                        if (data.success) {
                            window.CRM.dataListTable.ajax.reload(null, false);
                            render_container();
                        }
                    });
                }
            }
        });
    });

    function BootboxCampaignContent(nameTag) {

        var frm_str = '<h3 style="margin-top:-5px">' + i18next.t("Email Campaign Creation") + '</h3><form id="some-form">'
            + '<div>'
            + '<div class="row div-title">'
            + '<div class="col-md-3"><span style="color: red">*</span>' + i18next.t('Campaign Title') + ":</div>"
            + '<div class="col-md-9">'
            + "<input type='text' id='CampaignTitle' placeholder=\"" + i18next.t("Your Campaign Title") + "\" size='30' maxlength='100' class='form-control input-sm'  width='100%' style='width: 100%' required>"
            + '</div>'
            + '</div>'
            + '<div class="row div-title">'
            + '<div class="col-md-3"><span style="color: red">*</span>' + i18next.t('For Tag') + ":</div>"
            + '<div class="col-md-9">'
            + '<div class="col-md-3">' + nameTag + "</div>"
            + '</div>'
            + '</div>'
            + '<div class="row div-title">'
            + '<div class="col-md-3"><span style="color: red">*</span>' + i18next.t('Mail Subject') + ":</div>"
            + '<div class="col-md-9">'
            + "<input type='text' id='Subject' placeholder=\"" + i18next.t("Your Mail Subject") + "\" size='30' maxlength='100' class='form-control input-sm'  width='100%' style='width: 100%' required>"
            + '</div>'
            + '</div>'
            + '<div class="row  eventNotes">'
            + '<div class="col-md-12" style="padding-left:0px;padding-right:2px;">'
            + '<textarea name="CampaignText" cols="80" class="form-control input-sm campaignNotes" id="campaignNotes"  width="100%" style="margin-top:-58px;width: 100%;height: 4em;"></textarea></div>'
            + '</div>'
            + '</div>'
            + '</div>'
            + '</form>';

        var object = $('<div/>').html(frm_str).contents();

        return object
    }

    function createCampaignEditorWindow(tagId, tagName) {
        if (tagName == "") {
            tagName = i18next.t("All list members");
        }
        var modal = bootbox.dialog({
            message: BootboxCampaignContent(tagName),
            size: 'large',
            buttons: [
                {
                    label: '<i class="fa fa-times"></i> ' + i18next.t("Close"),
                    className: "btn btn-default",
                    callback: function () {
                        console.log("just do something on close");
                    }
                },
                {
                    label: '<i class="fa fa-check"></i> ' + i18next.t("Save"),
                    className: "btn btn-primary",
                    callback: function () {
                        var campaignTitle = $('form #CampaignTitle').val();

                        if (campaignTitle) {
                            var Subject = $('form #Subject').val();
                            var htmlBody = CKEDITOR.instances['campaignNotes'].getData();//$('form #campaignNotes').val();


                            window.CRM.dialogLoadingFunction(i18next.t("Adding Campaign ..."));

                            window.CRM.APIRequest({
                                method: 'POST',
                                path: 'mailchimp/campaign/actions/create',
                                data: JSON.stringify({
                                    "list_id": window.CRM.list_ID,
                                    "tagId": tagId,
                                    "subject": Subject,
                                    "title": campaignTitle,
                                    "htmlBody": htmlBody
                                })
                            }).done(function (data) {
                                if (data.success) {
                                    bootbox.confirm({
                                        message: i18next.t("Would like to manage directly this new campaign ?"),
                                        buttons: {
                                            confirm: {
                                                label: '<i class="fa fa-check"></i> ' + i18next.t('Yes'),
                                                className: 'btn-primary'
                                            },
                                            cancel: {
                                                label: '<i class="fa fa-times"></i> ' + i18next.t('No'),
                                                className: 'btn-default'
                                            }
                                        },
                                        callback: function (result) {
                                            render_container();
                                            if (result) {
                                                window.location.href = window.CRM.root + "/v2/mailchimp/campaign/" + data.result[0].id;
                                            }
                                        }
                                    });
                                }
                            });
                        } else {
                            window.CRM.DisplayNormalAlert(i18next.t("Error"), i18next.t("You have to set a Campaign Title for your eMail Campaign"));

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


    $(document).on("click", "#modifyList", function () {
        var name = $(this).data('name');
        var subject = $(this).data('subject');
        var permission_reminder = $(this).data('permissionreminder');

        bootbox.confirm('<form id="infos" action="#">'
            + i18next.t('List Name') + ':<input type="text" class="form-control" id="list_name" value="' + name + '"/><br/>'
            + i18next.t('Subject') + ':<input type="text" class="form-control" id="list_subject" value="' + subject + '"/><br/>'
            + i18next.t('Permission Reminder') + ':<input type="text" class="form-control" id="list_permission_reminder" value="' + permission_reminder + '"/>'
            + '</form>', function (result) {
            if (result) {
                name = $("#list_name").val();
                subject = $("#list_subject").val();
                permission_reminder = $("#list_permission_reminder").val();

                window.CRM.APIRequest({
                    method: 'POST',
                    path: 'mailchimp/modifylist',
                    data: JSON.stringify({
                        "list_id": window.CRM.list_ID,
                        "name": name,
                        "subject": subject,
                        "permission_reminder": permission_reminder
                    })
                }).done(function (data) {
                    if (data.success) {
                        $("#modifyList").data('name', name);
                        $("#modifyList").data('subject', subject);
                        $("#modifyList").data('permissionreminder', permission_reminder);
                        $("#ListTitle").text(name);

                        render_container();
                        $('.listName' + window.CRM.list_ID).html('<i class="fa fa-circle-o"></i>' + name);
                    }
                });

            }
        });
    });

    function changeState() {
        var state = false;
        $(".checkbox_users").each(function () {
            res = $(this)[0].checked;
            if (res == true) {
                state = true;
            }
        });

        $("#deleteMembers").prop('disabled', !(state));
        $(".addTagButton").prop('disabled', !(state));
        $(".addTagButtonDrop").prop('disabled', !(state));
        $(".subscribeButton").prop('disabled', !(state));
        $(".subscribeButtonDrop").prop('disabled', !(state));
    }

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

        $("#deleteMembers").prop('disabled', !(state));
        $(".addTagButton").prop('disabled', !(state));
        $(".addTagButtonDrop").prop('disabled', !(state));
        $(".subscribeButton").prop('disabled', !(state));
        $(".subscribeButtonDrop").prop('disabled', !(state));
    });

    $('#memberListTable').on('click', 'tr', function () {
        $(this).toggleClass('selected');
        var table = $('#memberListTable').DataTable();
        var data = table.row(this).data();

        if (data != undefined) {
            var userID = ".checkbox_user_" + data.id;
            var state = $(this).hasClass("selected");
            $(userID)[0].checked = state;
            changeState();
        }
    });

    $(".subscribeButton").click(function () {
        var status = $(this).data("type");

        window.CRM.dialogLoadingFunction(i18next.t('Changing subscribers state...'));

        $(".checkbox_users").each(function () {
            if (this.checked) {
                var email = $(this).data("email");

                window.CRM.APIRequest({
                    method: 'POST',
                    path: 'mailchimp/status',
                    data: JSON.stringify({"list_id": window.CRM.list_ID, "status": status, "email": email})
                }).done(function (data) {
                    if (data.success) {
                        window.CRM.dataListTable.ajax.reload(null, false);
                        render_container();
                    } else if (data.success == false && data.error) {
                        window.CRM.closeDialogLoadingFunction();
                        window.CRM.DisplayAlert(i18next.t("Error"), i18next.t(data.error.detail));
                    }
                });
            }
        });
    });


    $('body').on('click', '.deleteTagButton', function () {
        var tag = $(this).data("id");
        var name = $(this).data("name");

        var emails = [];

        $(".checkbox_users").each(function () {
            if (this.checked) {
                var email = $(this).data("email");

                emails.push(email);
            }
        });


        window.CRM.APIRequest({
            method: 'POST',
            path: 'mailchimp/list/getAllTags',
            data: JSON.stringify({"list_id": window.CRM.list_ID})
        }).done(function (data) {
            var len = data.result.length;

            var res = [{text: i18next.t("All Tags"), value: -1}];

            for (i = 0; i < len; ++i) {
                res.push({text: data.result[i].name, value: data.result[i].id});
            }

            bootbox.prompt({
                title: i18next.t("Choose the tag you want to delete :"),
                inputType: 'select',
                inputOptions: res,
                callback: function (tag) {
                    if (tag && tag != -1) {
                        console.log(tag);
                        window.CRM.dialogLoadingFunction(i18next.t('Removing tags...'));
                        window.CRM.APIRequest({
                            method: 'POST',
                            path: 'mailchimp/list/removeTagForMembers',
                            data: JSON.stringify({
                                "list_id": window.CRM.list_ID,
                                "tag": tag,
                                "name": name,
                                "emails": emails
                            })
                        }).done(function (data) {
                            if (data.success) {
                                window.CRM.dataListTable.ajax.reload(null, false);
                                render_container();
                                addTagsToMainDropdown();
                                changeState();
                            } else if (data.success == false && data.error) {
                                window.CRM.closeDialogLoadingFunction();
                                window.CRM.DisplayAlert(i18next.t("Error"), i18next.t(data.error.detail));
                            }
                        });
                    } else if (tag != null) {
                        window.CRM.dialogLoadingFunction(i18next.t('Deleting all tags for the selected members in the list...'));

                        window.CRM.APIRequest({
                            method: 'POST',
                            path: 'mailchimp/list/removeAllTagsForMembers',
                            data: JSON.stringify({"list_id": window.CRM.list_ID, "emails": emails})
                        }).done(function (data) {
                            window.CRM.dataListTable.ajax.reload(null, false);
                            render_container();
                        });
                    }
                }
            });
        });
    });


    $('body').on('click', '.addTagButton', function () {
        var tag = $(this).data("id");
        var name = $(this).data("name");

        var emails = [];

        $(".checkbox_users").each(function () {
            if (this.checked) {
                var email = $(this).data("email");

                emails.push(email);
            }
        });

        if (tag == -1) {

            bootbox.prompt(i18next.t("Add your tag name"), function (name) {
                if (name != null && name != "") {
                    window.CRM.dialogLoadingFunction(i18next.t('Adding tag...'));

                    window.CRM.APIRequest({
                        method: 'POST',
                        path: 'mailchimp/list/addTag',
                        data: JSON.stringify({
                            "list_id": window.CRM.list_ID,
                            "tag": tag,
                            "name": name,
                            "emails": emails
                        })
                    }).done(function (data) {
                        if (data.success) {
                            window.CRM.closeDialogLoadingFunction();
                            window.CRM.dataListTable.ajax.reload(null, false);
                            render_container();
                            addTagsToMainDropdown();
                            changeState();
                        } else if (data.success == false && data.error) {
                            window.CRM.closeDialogLoadingFunction();
                            window.CRM.DisplayAlert(i18next.t("Error"), i18next.t(data.error.detail));
                        }
                    });
                } else if (name != null) {
                    window.CRM.DisplayAlert(i18next.t("Error"), i18next.t("Name is empty !!"));
                }
            });
        } else {
            bootbox.confirm({
                title: i18next.t("Add tag?"),
                message: i18next.t("This will add the tag") + " \"" + name + "\" " + i18next.t("to all the current selected members in the list."),
                buttons: {
                    cancel: {
                        label: '<i class="fa fa-times"></i> ' + i18next.t("No")
                    },
                    confirm: {
                        label: '<i class="fa fa-check"></i> ' + i18next.t("Confirm")
                    }
                },
                callback: function (result) {
                    if (result) {
                        window.CRM.dialogLoadingFunction(i18next.t('Adding tag...'));
                        window.CRM.APIRequest({
                            method: 'POST',
                            path: 'mailchimp/list/addTag',
                            data: JSON.stringify({
                                "list_id": window.CRM.list_ID,
                                "tag": tag,
                                "name": name,
                                "emails": emails
                            })
                        }).done(function (data) {
                            if (data.success) {
                                window.CRM.closeDialogLoadingFunction();
                                window.CRM.dataListTable.ajax.reload(null, false);
                                render_container();
                            } else if (data.success == false && data.error) {
                                window.CRM.closeDialogLoadingFunction();
                                window.CRM.DisplayAlert(i18next.t("Error"), i18next.t(data.error.detail));
                            }
                        });
                    }
                }
            });
        }
    });


    $("#deleteMembers").click(function () {
        var emails = [];

        $(".checkbox_users").each(function () {
            if (this.checked) {
                var email = $(this).data("email");

                emails.push(email);
            }
        });

        bootbox.confirm({
            message: i18next.t("You're about to delete subscribers! Are you sure ?"),
            buttons: {
                confirm: {
                    label: i18next.t('Yes'),
                    className: '<i class="fa fa-times"></i> ' + 'btn-danger'
                },
                cancel: {
                    label: '<i class="fa fa-check"></i> ' + i18next.t('No'),
                    className: 'btn-primary'
                }
            },
            callback: function (result) {
                if (result) {
                    window.CRM.dialogLoadingFunction(i18next.t('Deleting Subscribers...'));
                    window.CRM.APIRequest({
                        method: 'POST',
                        path: 'mailchimp/suppressMembers',
                        data: JSON.stringify({"list_id": window.CRM.list_ID, "emails": emails})
                    }).done(function (data) {
                        if (data.success) {
                            window.CRM.dataListTable.ajax.reload(null, false);
                            render_container();
                        } else if (data.success == false && data.error) {
                            window.CRM.closeDialogLoadingFunction();
                            window.CRM.DisplayAlert(i18next.t("Error"), i18next.t(data.error.detail));
                        }
                    });
                }
            }
        });
    });
});
