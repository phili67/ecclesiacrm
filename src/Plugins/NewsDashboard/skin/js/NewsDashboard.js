$(function() {
    window.CRM.ElementListener('#add-dashboard-news-note', 'click', function(event) {
        if (window.CRM.editor) {
            CKEDITOR.remove(window.CRM.editor);
            window.CRM.editor = null;
        }

        var modal = NewsEditorWindow('create', 0);

        var theme = 'n1theme,/skin/js/ckeditor/themes/n1theme/';
        if (window.CRM.bDarkMode) {
            theme = 'moono-dark,/skin/js/ckeditor/themes/moono-dark/';
        }

        // this will create the toolbar for the textarea
        if (window.CRM.editor == null) {
            if (window.CRM.bEDrive) {
                window.CRM.editor = CKEDITOR.replace('NewsText', {
                    customConfig: window.CRM.root + '/skin/js/ckeditor/configs/note_editor_config.js',
                    language: window.CRM.lang,
                    width: '100%',
                    extraPlugins: 'uploadfile,uploadimage,filebrowser',
                    uploadUrl: window.CRM.root + '/uploader/upload.php?type=privateDocuments',
                    imageUploadUrl: window.CRM.root + '/uploader/upload.php?type=privateImages',
                    filebrowserUploadUrl: window.CRM.root + '/uploader/upload.php?type=privateDocuments',
                    filebrowserBrowseUrl: window.CRM.root + '/browser/browse.php?type=privateDocuments',
                    skin: theme
                });
            } else {
                window.CRM.editor = CKEDITOR.replace('NewsText', {
                    customConfig: window.CRM.root + '/skin/js/ckeditor/configs/note_editor_config.js',
                    language: window.CRM.lang,
                    skin: theme,
                    width: '100%'
                });
            }


            add_ckeditor_buttons(window.CRM.editor);
            add_ckeditor_buttons_merge_tag_mailchimp(window.CRM.editor);
        }

        modal.modal("show");
    });

    function addElementsToNewsDashboardList(data) {
        if (data == undefined) return;

        let cnt = data.items.length;
        var res = '';

        for (i = 0;i < cnt; i++) {
            res += '<li class="item">' +
                '       <div class="product-img">' +
                '           <img src="' + window.CRM.root + '/Plugins/NewsDashboard/core/images/' + data.items[i].Img + '" alt="Product Image" class="img-size-50">' +
                '       </div>\n' +
                '       <div class="product-info">' +
                '           <a href="javascript:void(0)" class="product-title">' + data.items[i].Title +
                '               <span class="badge badge-warning float-right">' + data.items[i].Date +'</span>' +
                '           </a>' +
                '           <span class="product-description">' + data.items[i].Text;


            if (window.CRM.newsDashboardIsAdmin) {
                res += '        <div class="row">' +
                    '                   <div class="col-md-11">' +
                    '                       <button type="button" class="btn btn-danger btn-sm float-right remove-dashboard-news-note" data-id="' + data.items[i].Id + '"><i class="fas fa-trash"></i> ' + i18next.t("Remove", {ns: 'NewsDashboard'}) + '</button>' +
                    '                       <button type="button" class="btn btn-primary btn-sm float-right edit-dashboard-news-note" data-id="' + data.items[i].Id + '"  style="margin-right: 12px"><i class="fas fa-edit"></i> ' + i18next.t("Edit", {ns: 'NewsDashboard'}) + '</button>' +
                    '                   </div>' +
                    '               </div>';
            }
            res += '           </span>' +
                '        </div>' +
                '    </li>';
        }

        return res;
    }

    function BootboxContent(sTitleText, sDocType, sText) {

        var frm_str = '<div>'
            + '<div class="row">'
            + '<div class="col-md-3"><span style="color: red">*</span>' + i18next.t('News Title', {ns: 'NewsDashboard'}) + ":</div>"
            + '<div class="col-md-9">'
            + '<input type="text" id="NewsTitle" placeholder="' + i18next.t("Set your News title", {ns: 'NewsDashboard'}) + '" size="30" maxlength="100" class="form-control form-control-sm"  width="100%" style="width: 100%" required>'
            + '</div>'
            + '</div>'
            + '<div class="row div-title">'
            + '<div class="col-md-3"><span style="color: red">*</span>' + i18next.t('Choose your News Type', {ns: 'NewsDashboard'}) + ":</div>"
            + '<div class="col-md-9">'
            + '  <select name="NewsType" class="form-control form-control-sm" id="NewsType">'
            + '     <option value="infos">' + i18next.t("Infos", {ns: 'NewsDashboard'}) + '</option>'
            + '     <option value="to_plan">' + i18next.t("To plan", {ns: 'NewsDashboard'}) + '</option>'
            + '     <option value="to_note">' + i18next.t("To note", {ns: 'NewsDashboard'}) + '</option>'
            + '     <option value="important">' + i18next.t("Important", {ns: 'NewsDashboard'}) + '</option>'
            + '     <option value="very_important">' + i18next.t("Very important", {ns: 'NewsDashboard'}) + '</option>'
            + '  </select>'
            + '</div>'
            + '</div>'
            + '<div class="row  eventNotes">'
            + '<div class="col-md-12" style="padding-left:0px;padding-right:2px;">'
            + '<textarea name="NewsText" cols="80" class="form-control form-control-sm" id="NewsText"  width="100%" style="margin-top:-58px;width: 100%;height: 4em;"></textarea></div>'
            + '</div>'
            + '</div>';

        var object = $('<div/>').html(frm_str).contents();

        return object;
    }

    function NewsEditorWindow(mode, newsID) {

        var modal = bootbox.dialog({
            title: i18next.t("News Editor", {ns: 'NewsDashboard'}),
            message: BootboxContent(),            
            size: 'large',
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
                        var NewsTitle = $('#NewsTitle').val();
                        var userID = window.CRM.userID;

                        if (NewsTitle != "") {
                            var htmlBody = CKEDITOR.instances['NewsText'].getData();
                            var Type = $("#NewsType").val();

                            if (mode == 'create') {
                                window.CRM.APIRequest({
                                    method: 'POST',
                                    path: 'newsdashboardplugin/create',
                                    data: JSON.stringify({
                                        "userID": userID,
                                        "title": NewsTitle,
                                        "type": Type,
                                        "text": htmlBody,
                                    })
                                },function (data) {
                                    if (data.status == "success") {
                                        var ul = document.getElementById('news-dashboard-list');

                                        var res =  addElementsToNewsDashboardList(data);

                                        ul.innerHTML = res;

                                        addNewsDashboarsListeners();
                                    }
                                });
                            } else if (mode == 'edit') {
                                window.CRM.APIRequest({
                                    method: 'POST',
                                    path: 'newsdashboardplugin/update',
                                    data: JSON.stringify({
                                        "userID": userID,
                                        "newsID": newsID,
                                        "type": Type,
                                        "title": NewsTitle,
                                        "text": htmlBody
                                    })
                                },function (data) {
                                    if (data.status == "success") {
                                        var ul = document.getElementById('news-dashboard-list');

                                        var res =  addElementsToNewsDashboardList(data);

                                        ul.innerHTML = res;

                                        addNewsDashboarsListeners();
                                    }
                                });
                            }
                        } else {
                            window.CRM.DisplayNormalAlert(i18next.t("Error"), i18next.t("You have to set a Title for your news", {ns: 'NewsDashboard'}));

                            return false;
                        }
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


    function addNewsDashboarsListeners () {
        window.CRM.ElementListener('.edit-dashboard-news-note', 'click', function (event) {
            var newsID = event.currentTarget.dataset.id

            window.CRM.APIRequest({
                method: 'POST',
                path: 'newsdashboardplugin/info',
                data: JSON.stringify({
                    "newsID": newsID
                })
            }, function (data) {
                if (data.status == "success") {
                    if (window.CRM.editor) {
                        CKEDITOR.remove(window.CRM.editor);
                        window.CRM.editor = null;
                    }

                    var modal = NewsEditorWindow('edit', newsID);

                    var theme = 'n1theme,/skin/js/ckeditor/themes/n1theme/';
                    if (window.CRM.bDarkMode) {
                        theme = 'moono-dark,/skin/js/ckeditor/themes/moono-dark/';
                    }

                    // this will create the toolbar for the textarea
                    if (window.CRM.editor == null) {
                        if (window.CRM.bEDrive) {
                            window.CRM.editor = CKEDITOR.replace('NewsText', {
                                customConfig: window.CRM.root + '/skin/js/ckeditor/configs/note_editor_config.js',
                                language: window.CRM.lang,
                                width: '100%',
                                extraPlugins: 'uploadfile,uploadimage,filebrowser,html5video',
                                uploadUrl: window.CRM.root + '/uploader/upload.php?type=privateDocuments',
                                imageUploadUrl: window.CRM.root + '/uploader/upload.php?type=privateImages',
                                filebrowserUploadUrl: window.CRM.root + '/uploader/upload.php?type=privateDocuments',
                                filebrowserBrowseUrl: window.CRM.root + '/browser/browse.php?type=privateDocuments',
                                skin: theme
                            });
                        } else {
                            window.CRM.editor = CKEDITOR.replace('NewsText', {
                                customConfig: window.CRM.root + '/skin/js/ckeditor/configs/note_editor_config.js',
                                language: window.CRM.lang,
                                width: '100%',
                                skin: theme
                            });
                        }

                        add_ckeditor_buttons(window.CRM.editor);
                        add_ckeditor_buttons_merge_tag_mailchimp(window.CRM.editor);
                    }

                    modal.modal("show");

                    $('#NewsTitle').val(data.note.Title);
                    $("#NewsType").val(data.note.Type);
                    CKEDITOR.instances['NewsText'].setData(data.note.Text);
                } else {
                    window.CRM.DisplayNormalAlert(i18next.t("Error"), i18next.t(data.message));
                }
            });
        });

        window.CRM.ElementListener('.remove-dashboard-news-note', 'click', function (event) {
            var newsID = event.currentTarget.dataset.id

            window.CRM.APIRequest({
                method: 'DELETE',
                path: 'newsdashboardplugin/remove',
                data: JSON.stringify({
                    "newsID": newsID
                })
            }, function (data) {
                if (data.status == "success") {
                    var ul = document.getElementById('news-dashboard-list');

                    var res =  addElementsToNewsDashboardList(data);

                    ul.innerHTML = res;

                    addNewsDashboarsListeners();
                }
            });
        });
    }

    addNewsDashboarsListeners();
});
