//
//  This code is under copyright not under MIT Licence
//  copyright   : 2018 Philippe Logel all right reserved.
//                This code can't be included in another software.
//
//  Updated : 2020/06/18
//

var tagButtonsLoaded = false;

$(function() {
    window.CRM.editor = null;

    function addTagsToMainDropdown() {
        $("#allTags").empty();
        $("#allCampaignTags").empty();
        $("#addCreateTagsDropAll").empty();

        window.CRM.APIRequest({
            method: 'POST',
            path: 'mailchimp/list/getAllTags',
            data: JSON.stringify({"list_id": window.CRM.list_ID})
        }, function (data) {
            $("#allTags").append('<a class="dropdown-item addTagButton" data-id="-1" data-name=""></i><i class="fas fa-plus"></i> <i class="fas fa-tag"></i> ' + i18next.t("Add a new tag") + '</a>');
            $("#allTags").append('<div class="dropdown-divider"></div>');
            $("#allTags").append('<a class="dropdown-item deleteTagButton" data-id="-1" data-name=""><i class="fas fa-minus"></i> <i class="fas fa-tag"></i> ' + i18next.t("Delete tag from subscriber(s)") + '</a>');
            $("#allTags").append('<div class="dropdown-divider"></div>');

            $("#addCreateTagsDropAll").append('<a class="dropdown-item addTagButton" data-id="-1" data-name=""></i><i class="fas fa-plus"></i> <i class="fas fa-tag"></i> ' + i18next.t("Add a new tag") + '</a>');
            $("#addCreateTagsDropAll").append('<div class="dropdown-divider"></div>');


            if (data.result != undefined) {
                var len = data.result.length;

                for (i = 0; i < len; ++i) {
                    $("#allTags").append('<a class="dropdown-item addTagButton" data-id="' + data.result[i].id + '" data-name="' + data.result[i].name + '" id="dropdown-item-add-' + data.result[i].id + '"><i class="fas fa-tag"></i> ' + data.result[i].name + '</a>');
                    $("#allCampaignTags").append('<a class="dropdown-item CreateCampaign" data-id="' + data.result[i].id + '" data-name="' + data.result[i].name + '"><i class="fas fa-tag"></i> ' + data.result[i].name + '</a>');

                    $("#addCreateTagsDropAll").append('<a class="dropdown-item delete-tag" data-id="' + data.result[i].id + '" data-listid="' + data.result[i].list_id + '"><i class="fas fa-minus"></i> <i class="fas fa-tag"></i> ' + i18next.t("Delete tag") + ' : ' + data.result[i].name + '</a>');
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
            }, function (data) {
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

                var listView = '<div class="card-header border-1">'
                    + '      <h3 class="card-title"><i class="fas fa-list"></i> ' + i18next.t('Email List') + '   (' + i18next.t('Details') + ')</h3>'
                    + '      <div class="card-tools pull-right">'
                    + '          <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>'
                    + '      </div>'
                    + '    </div>'
                    + '    <div class="card-body">'
                    + '      <div class="row">'
                    + '        <div class="col-md-12">'
                    + '          <table width="100%">'
                    + '            <tr><td><b><i class="far fa-eye"></i> ' + i18next.t('Details') + '</b> </td><td></td></tr>'
                    + '            <tr><td>&bullet; ' + i18next.t('Subject') + '</td><td>:</td><td><b>' + list.campaign_defaults.subject + '</b></td></tr>'
                    + '            <tr><td>&bullet; ' + i18next.t('Members:') + '</td><td>:</td><td><b>' + list.stats.member_count + '</b></td></tr>'
                    //+'            <tr><td>&bullet; ' + i18next.t('Campaigns:') + '</td><td>:</td><td><b> ' + list.stats.campaign_count + '</b></td></tr>'
                    + '            <tr><td>&bullet; ' + i18next.t('Unsubscribed count:') + '</td><td>:</td><td><b>' + list.stats.unsubscribe_count + '</b></td></tr>'
                    + '            <tr><td>&bullet; ' + i18next.t('Unsubscribed count since last send:') + '</td><td>:</td><td><b>' + list.stats.unsubscribe_count_since_send + '</b></td></tr>'
                    + '            <tr><td>&bullet; ' + i18next.t('Cleaned count:') + '</td><td>:</td><td><b>' + list.stats.cleaned_count + '</b></td></tr>'
                    + '            <tr><td>&bullet; ' + i18next.t('Cleaned count since last send:') + '</td><td>:</td><td><b>' + list.stats.cleaned_count_since_send + '</b></td></tr>'
                    + '          </table>'
                    + '        </div>'
                    + '      </div><hr class="hr-mailchimp"/>'
                    + '      <div class="row">'
                    + '        <div class="col-md-12">'
                    + '           <b><i class="fas fa-envelope-open-text"></i> ' + i18next.t('Campaigns') + '</b><br>';

                // saved campaigns
                listView += '          <table width="100%">';

                let save_campaigns = 1;
                var lenCampaigns = data.MailChimpCampaign[save_campaigns].length;

                var tags = '';

                for (j = 0; j < lenCampaigns; j++) {
                    if (data.membersCount == 0) {
                        listView += '<tr><td>• ' + data.MailChimpCampaign[save_campaigns][j].settings.title + '</td></tr>';
                    } else {
                        listView += '<tr><td>&bullet; ' + data.MailChimpCampaign[save_campaigns][j].settings.title + '</td><td>' + ' <b><span style="color:' + ((data.MailChimpCampaign[save_campaigns][j].status == 'sent') ? 'green' : 'gray') + '">(' + i18next.t(data.MailChimpCampaign[save_campaigns][j].status) + ')</span></b>  </td><td><a href="' + window.CRM.root + '/v2/mailchimp/campaign/' + data.MailChimpCampaign[save_campaigns][j].id + '" class="btn btn btn-primary btn-xs""><i class="fas fa-edit"></i> </a></td></tr>';
                    }
                }

                // sent campaigns
                if (lenCampaigns > 0) {
                    listView += '<tr><td>&nbsp;</td><td></td><td></td></tr>';
                }

                let send_campaigns = 0;
                var lenCampaigns = data.MailChimpCampaign[send_campaigns].length;

                var tags = '';

                for (j = 0; j < lenCampaigns; j++) {
                    if (data.membersCount == 0) {
                        listView += '<tr><td>• ' + data.MailChimpCampaign[send_campaigns][j].settings.title + '</td></tr>';
                    } else {
                        listView += '<tr><td>&bullet; ' + data.MailChimpCampaign[send_campaigns][j].settings.title + '</td><td>' + ' <b><span style="color:' + ((data.MailChimpCampaign[send_campaigns][j].status == 'sent') ? 'green' : 'gray') + '">(' + i18next.t(data.MailChimpCampaign[send_campaigns][j].status) + ')</span></b>  </td><td><a href="' + window.CRM.root + '/v2/mailchimp/campaign/' + data.MailChimpCampaign[send_campaigns][j].id + '" class="btn btn btn-primary btn-xs""><i class="fas fa-edit"></i> </a></td></tr>';
                    }
                }

                if (lenCampaigns == 0) {
                    listView += '<tr><td>&nbsp;&nbsp; <i class="icon fas fa-tags"></i>' + i18next.t('Campaign') + '</td></tr>';
                }

                listView += '          </table>';

                listView += '        </div>';

                listView += '   </div><hr class="hr-mailchimp"/>';

                var lenTags = data.MailChimpList.tags.length;

                if (lenTags) {
                    listView += '    <div class="row">';
                    listView += '        <div class="col-12">'
                        + '           <b><i class="icon fas fa-tags"></i> ' + i18next.t('Tags') + '</b><br>';

                    var tags = data.MailChimpList.tags;

                    var tagsButtons = '';

                    if (lenTags) {
                        tagsButtons += '<table width="100%" id="allTagsRightView">';
                        for (k = 0; k < lenTags; k++) {
                            tagsButtons += '<tr id="delete-tag-tr-' + tags[k].id + '">';
                            tagsButtons += '<td>&bullet; ' + tags[k].name + ' </td><td><a class="delete-tag btn btn btn-danger btn-xs" data-id="' + tags[k].id + '" data-listid="' + data.MailChimpList.id + '"><i style="cursor:pointer;" class="icon far fa-trash-alt"></i> </a></td>';
                            tagsButtons += '</tr>';
                        }
                        tagsButtons += '</table>'
                    }

                    listView += tagsButtons;

                    listView += '        </div>';

                }

                listView += '      </div>'
                    + '    </div>';

                listItems += '<li><a href="' + window.CRM.root + '/v2/mailchimp/managelist/' + list.id + '"><i class="far fa-circle"></i>' + list.name + '</a>';

                $("#container").html(listView);
            });
        }
    }

    function loadTableMembers() {
        // the DataTable
        var columns = [
            {
                width: '20px',
                orderable: false,
                title: '<input type="checkbox" class="check_all" id="check_all" data-toggle="tooltip" data-placement="bottom" title="" data-original-title="' + i18next.t("Check all boxes") + '">',
                data: 'checkoxColumn',
                render: function (data, type, full, meta) {
                    return data;
                }
            },
            {
                width: '80px',
                className: "text-center",
                orderable: false,
                title: '<small>' + i18next.t("Actions") + ' </small><br/>'
                    + '<div class="btn-group">' +
                    '       <button type="button" id="deleteMembers" class="btn btn-danger btn-sm"'
                    + 'disabled><i class="far fa-trash-alt"></i> </button> ' +
                    '       <button type="button" class="subscribeButton btn btn-primary btn-sm" data-type="subscribed"' +
                    '                                        disabled><i class="fas fa-user"></i></button>' +
                    '                                <button type="button" class="subscribeButtonDrop btn btn-primary dropdown-toggle btn-sm"' +
                    '                                        data-toggle="dropdown" aria-expanded="false" disabled>' +
                    '                                    <span class="caret"></span>' +
                    '                                    <span class="sr-only">Toggle Dropdown</span>' +
                    '                                </button>' +
                    '                                <div class="dropdown-menu" role="menu">' +
                    '                                    <a class="dropdown-item subscribeButton" data-type="subscribed"><i' +
                    '                                            class="fas fa-user"></i><i class="fas fa-check"></i> ' + i18next.t("Subscribed") +
                    '                                    </a>\n' +
                    '                                    <a class="dropdown-item subscribeButton" data-type="unsubscribed"><i\n' +
                    '                                            class="fas fa-user"></i><i class="fas fa-times"></i> ' + i18next.t("Unsubscribed") +
                    '                                    </a>\n' +
                    '                                </div>' +
                    '   </div>',
                data: 'actionColumn',
                render: function (data, type, full, meta) {
                    return data;
                }
            },
            {
                width: 'auto',
                className: "text-center",
                orderable: false,
                title: '<small>' + i18next.t('Tags') + '</small><br/><div class="btn-group">\n' +
                    '                                <button type="button" class="addTagButton btn btn-primary btn-sm" data-id="-1" ' +
                    '                                        disabled><i class="fas fa-tag"></i></button>' +
                    '                                <button type="button" class="addTagButtonDrop btn btn-primary dropdown-toggle btn-sm"' +
                    '                                        data-toggle="dropdown" aria-expanded="false" disabled>' +
                    '                                    <span class="caret"></span>' +
                    '                                    <span class="sr-only">Toggle Dropdown</span>' +
                    '                                </button>\n' +
                    '                                <div class="dropdown-menu" role="menu" id="allTags"></div>' +
                    '                            </div>',
                data: 'tagsColumn',
                render: function (data, type, full, meta) {
                    return data;
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
                title: i18next.t('First Name'),
                data: 'merge_fields',
                render: function (data, type, full, meta) {
                    return data.FNAME;
                }
            },
            {
                width: 'auto',
                title: i18next.t('Email'),
                data: 'email_address_column',
                render: function (data, type, full, meta) {
                    return data;
                }
            },
            {
                width: 'auto',
                title: i18next.t('Email Marketing'),
                data: 'statusColumn',
                render: function (data, type, full, meta) {
                    return data;
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
                dataSrc: "MailChimpMembers",
                "beforeSend": function (xhr) {
                    xhr.setRequestHeader('Authorization',
                        "Bearer " +  window.CRM.jwtToken
                    );
                }
            },
            deferRender: true,
            columns: columns,
            responsive: true,
            pageLength: 25,
            rowId: 'email_address_column',
            order: [[3, "asc"]],
            createdRow: function (row, data, index) {
                $(row).addClass("duplicateRow");
            },
            initComplete: function (settings, json) {
                $("body").tooltip({selector: '[data-toggle=tooltip]'});
                if (tagButtonsLoaded === false) {
                    addDataTableButtons();
                    tagButtonsLoaded = true;
                }
            }
        }


        /*$.fn.dataTable.ext.buttons.test1 = {
            text: 'My button 1',
            action: function (e, dt, node, config) {
                alert('Button activated 1');
            }
        };

        $.fn.dataTable.ext.buttons.test2 = {
            text: 'My button 2',
            action: function (e, dt, node, config) {
                alert('Button activated 2');
            }
        };


        $.fn.dataTable.ext.buttons.menu = {
            text: 'My Menu',
            action: function (e, dt, node, config) {
                alert('Button activated 2');
            }
        };


        $.fn.dataTable.ext.buttons.testSplitPlus = {
            extend: 'collection',
            className: 'custom-html-collection',
            buttons: [
                'test1',
                'test2'
            ]
        };


        window.CRM.plugin.dataTable.buttons.push('testSplitPlus');*/

        $.extend(dataTableConfig, window.CRM.plugin.dataTable);

        window.CRM.dataListTable = $("#memberListTable").DataTable(dataTableConfig);
    }

    render_container();
    addTagsToMainDropdown();
    loadTableMembers();

    function addDataTableButtons() {
        $(document).on("click", ".addTagButton" , function() {
            $(".addTagButtonDrop").dropdown('toggle');
            var ev = event;

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
                        }, function (data) {
                            var result = data.result[0];

                            if (data.success) {
                                window.CRM.dataListTable.ajax.reload(function (json) {
                                    window.CRM.closeDialogLoadingFunction();
                                    $("#allTags").append('<a class="dropdown-item addTagButton" data-id="' + result.id + '" data-name="' + result.name + '" id="dropdown-item-add-' + result.id + '"><i class="fas fa-tag"></i> ' + result.name + '</a>');

                                    tagsButtons = '<tr id="delete-tag-tr-' + result.id + '">';
                                    tagsButtons += '<td>&bullet; ' + result.name + ' </td><td><a class="delete-tag btn btn btn-danger btn-xs" data-id="' + result.id + '" data-listid="' + result.list_id + '"><i style="cursor:pointer;" class="icon far fa-trash-alt"></i> </a></td>';
                                    tagsButtons += '</tr>';

                                    $("#allTagsRightView").append(tagsButtons);
                                    window.CRM.closeDialogLoadingFunction();
                                }, false);
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
                            label: '<i class="fas fa-times"></i> ' + i18next.t("No")
                        },
                        confirm: {
                            label: '<i class="fas fa-check"></i> ' + i18next.t("Confirm")
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
                            }, function (data) {
                                if (data.success) {
                                    window.CRM.dataListTable.ajax.reload(function (json) {
                                        render_container();
                                        window.CRM.closeDialogLoadingFunction();
                                    }, false);
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

        $('.deleteTagButton').on('click',function (event) {
            $(".addTagButtonDrop").dropdown('toggle');
            var ev = event;

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
            }, function (data) {
                var len = data.result.length;

                var res = [{text: i18next.t("Select One"), value: ''}];

                res.push({text: i18next.t("All Tags"), value: -1});

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
                            }, function (data) {
                                if (data.success) {
                                    window.CRM.dataListTable.ajax.reload(function (json) {
                                        window.CRM.closeDialogLoadingFunction();
                                    }, false);
                                    //addTagsToMainDropdown();
                                    //changeState();
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
                            }, function (data) {
                                window.CRM.dataListTable.ajax.reload(function (json) {
                                    window.CRM.closeDialogLoadingFunction();
                                }, false);
                            });
                        }
                    }
                });
            });
        });
    }

    // render the main page

    $(document).on("click", ".delete-tag", function (event) {
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
                    window.CRM.dialogLoadingFunction(i18next.t("Deleting tag"));

                    window.CRM.APIRequest({
                        method: 'POST',
                        path: 'mailchimp/list/removeTag',
                        data: JSON.stringify({"list_id": listID, "tag_ID": tagID})
                    }, function (data) {
                        window.CRM.dataListTable.ajax.reload(function () {
                            window.CRM.closeDialogLoadingFunction();
                            $("#dropdown-item-add-"+tagID).remove();
                            $("#delete-tag-tr-"+tagID).remove();
                        }, false);
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
                    extraPlugins: 'uploadfile,uploadimage,filebrowser,html5video',
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
            headers: {
                "Authorization" : "Bearer "+window.CRM.jwtToken
            },
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
            }, function (data) {
                if (data.success) {
                    window.CRM.dataListTable.ajax.reload(function (json) {
                        render_container();
                    }, false);
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
            }, function (data) {
                if (data.success) {
                    window.CRM.dataListTable.ajax.reload(function (json) {
                        render_container();
                    }, false);
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
            }, function (data) {
                if (data.success) {
                    window.CRM.dataListTable.ajax.reload(function (json) {
                        render_container();
                    }, false);
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
            }, function (data) {
                if (data.success) {
                    window.CRM.dataListTable.ajax.reload(function (json) {
                        render_container();
                    }, false);
                } else if (data.error) {
                    window.CRM.DisplayAlert(i18next.t("Error"), i18next.t(data.error.detail));
                    window.CRM.dataListTable.ajax.reload(function (json) {
                        render_container();
                    }, false);
                }
            });
        } else if (e.params.data.typeId !== undefined && e.params.data.typeId == 2) {
            window.CRM.dialogLoadingFunction(i18next.t("Loading all newsletter subscribers from EcclesiaCRM<br>This could take a while !") + '<br>' + i18next.t("In fact, you've better to quit the CRM, wait 5 minutes and make your campaigns after.<br>To import huge datas, MailChimp API is slow."));

            window.CRM.APIRequest({
                method: 'POST',
                path: 'mailchimp/addallnewsletterpersons',
                data: JSON.stringify({"list_id": list_id})
            }, function (data) {
                if (data.success) {
                    window.CRM.dataListTable.ajax.reload(function (json) {
                        render_container();
                    }, false);
                } else if (data.error) {
                    window.CRM.DisplayAlert(i18next.t("Error"), i18next.t(data.error.detail));
                    window.CRM.dataListTable.ajax.reload(function (json) {
                        render_container();
                    }, false);
                }
            });
        } else if (e.params.data.typeId !== undefined && e.params.data.typeId == 3) {
            window.CRM.dialogLoadingFunction(i18next.t("Loading all families first headpeople subscribers from EcclesiaCRM<br>This could take a while !") + '<br>' + i18next.t("In fact, you've better to quit the CRM, wait 5 minutes and make your campaigns after.<br>To import huge datas, MailChimp API is slow."));

            window.CRM.APIRequest({
                method: 'POST',
                path: 'mailchimp/addAllFamilies',
                data: JSON.stringify({"list_id": list_id})
            }, function (data) {
                if (data.success) {
                    window.CRM.dataListTable.ajax.reload(function (json) {
                        render_container();
                    }, false);
                } else if (data.error) {
                    window.CRM.DisplayAlert(i18next.t("Error"), i18next.t(data.error.detail));
                    window.CRM.dataListTable.ajax.reload(function (json) {
                        render_container();
                    }, false);
                }
            });
        }

    });


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
                    }, function (data) {
                        if (data.success) {
                            window.CRM.dataListTable.ajax.reload(function (json) {
                                render_container();
                            }, false);
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
                    className: '<i class="fas fa-times"></i> ' + 'btn-danger'
                },
                cancel: {
                    label: '<i class="fas fa-check"></i> ' + i18next.t('No'),
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
                    }, function (data) {
                        if (data.success) {
                            window.CRM.dataListTable.ajax.reload(function (json) {
                                render_container();
                            }, false);
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
                    window.CRM.APIRequest({
                        method: 'POST',
                        path: 'mailchimp/deletelist',
                        data: JSON.stringify({"list_id": window.CRM.list_ID})
                    }, function (data) {
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
                    window.CRM.dialogLoadingFunction(i18next.t('Deleting all subscribers...') + '<br>' + i18next.t("In fact, you've better to leave the CRM, and in a quater of an hour re-open it to manage your list.<br>To delete huge datas, MailChimp API is slow."));

                    window.CRM.APIRequest({
                        method: 'POST',
                        path: 'mailchimp/deleteallsubscribers',
                        data: JSON.stringify({"list_id": window.CRM.list_ID})
                    }, function (data) {
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
            + "<input type='text' id='CampaignTitle' placeholder=\"" + i18next.t("Your Campaign Title") + "\" size='30' maxlength='100' class='form-control form-control-sm'  width='100%' style='width: 100%' required>"
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
            + "<input type='text' id='Subject' placeholder=\"" + i18next.t("Your Mail Subject") + "\" size='30' maxlength='100' class='form-control form-control-sm'  width='100%' style='width: 100%' required>"
            + '</div>'
            + '</div>'
            + '<div class="row  eventNotes">'
            + '<div class="col-md-12" style="padding-left:0px;padding-right:2px;">'
            + '<textarea name="CampaignText" cols="80" class="form-control form-control-sm campaignNotes" id="campaignNotes"  width="100%" style="margin-top:-58px;width: 100%;height: 4em;"></textarea></div>'
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
            size: 'extra-large',
            buttons: [
                {
                    label: '<i class="fas fa-times"></i> ' + i18next.t("Close"),
                    className: "btn btn-default",
                    callback: function () {
                        console.log("just do something on close");
                    }
                },
                {
                    label: '<i class="fas fa-check"></i> ' + i18next.t("Save"),
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
                            }, function (data) {
                                if (data.success) {
                                    bootbox.confirm({
                                        message: i18next.t("Would like to manage directly this new campaign ?"),
                                        buttons: {
                                            confirm: {
                                                label: '<i class="fas fa-check"></i> ' + i18next.t('Yes'),
                                                className: 'btn-primary'
                                            },
                                            cancel: {
                                                label: '<i class="fas fa-times"></i> ' + i18next.t('No'),
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
            + i18next.t('List Name') + ':<input type="text" class= "form-control form-control-sm" id="list_name" value="' + name + '"/><br/>'
            + i18next.t('Subject') + ':<input type="text" class= "form-control form-control-sm" id="list_subject" value="' + subject + '"/><br/>'
            + i18next.t('Permission Reminder') + ':<input type="text" class= "form-control form-control-sm" id="list_permission_reminder" value="' + permission_reminder + '"/>'
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
                }, function (data) {
                    if (data.success) {
                        $("#modifyList").data('name', name);
                        $("#modifyList").data('subject', subject);
                        $("#modifyList").data('permissionreminder', permission_reminder);
                        $("#ListTitle").text(name);

                        render_container();
                        $('.listName' + window.CRM.list_ID).html('<i class="far fa-circle"></i>' + name);
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

    $(document).on("click", ".check_all", function () {
        var res = 0;

        var state = this.checked;

        /*window.CRM.dataListTable.rows().every(function (rowIdx, tableLoop, rowLoop, data) {
            //console.log(`For index ${rowIdx}, data value is ${data}`);
            var data = this.data();
            data.checkStatus = state;
            window.CRM.dataListTable.cell({row: rowIdx, column: 0}).data("toto").draw();
            res++;
        });*/

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

    $(document).on("click", ".subscribeButton", function () {
        var status = $(this).data("type");

        window.CRM.dialogLoadingFunction(i18next.t('Changing subscribers state...'));

        $(".checkbox_users").each(function () {
            if (this.checked) {
                var email = $(this).data("email");

                window.CRM.APIRequest({
                    method: 'POST',
                    path: 'mailchimp/status',
                    data: JSON.stringify({"list_id": window.CRM.list_ID, "status": status, "email": email})
                }, function (data) {
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

    $(document).on("click", "#deleteMembers", function () {
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
                    className: '<i class="fas fa-times"></i> ' + 'btn-danger'
                },
                cancel: {
                    label: '<i class="fas fa-check"></i> ' + i18next.t('No'),
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
                    }, function (data) {
                        if (data.success) {
                            window.CRM.dataListTable.ajax.reload(function () {
                                render_container();
                            }, false);
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
