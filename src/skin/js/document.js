//
//  This code is under copyright not under MIT Licence
//  copyright   : 2019 Philippe Logel all right reserved not MIT licence
//                This code can't be incoprorated in another software without authorizaion
//
//  Updated : 2019/04/19
//

$(function() {
    window.CRM.editor = null;

    $(document).on("click", "#createDocument", function () {
        if (window.CRM.editor) {
            CKEDITOR.remove(window.CRM.editor);
            window.CRM.editor = null;
        }

        var modal = DocumentEditorWindow('create', 0);

        var theme = 'n1theme,/skin/js/ckeditor/themes/n1theme/';
        if (window.CRM.bDarkMode) {
            theme = 'moono-dark,/skin/js/ckeditor/themes/moono-dark/';
        }

        // this will create the toolbar for the textarea
        if (window.CRM.editor == null) {
            if (window.CRM.bEDrive) {
                window.CRM.editor = CKEDITOR.replace('documentText', {
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
                window.CRM.editor = CKEDITOR.replace('documentText', {
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

    $(document).on("click", ".editDocument", function () {
        var docID = $(this).data('id');
        var perID = $(this).data('perid');
        var famID = $(this).data('famid');

        window.CRM.APIRequest({
            method: 'POST',
            path: 'document/get',
            data: JSON.stringify({"docID": docID, "personID": perID, "famID": famID})
        },function (data) {
            if (data.success) {
                if (window.CRM.editor) {
                    CKEDITOR.remove(window.CRM.editor);
                    window.CRM.editor = null;
                }

                var modal = DocumentEditorWindow('edit', docID);

                var theme = 'n1theme,/skin/js/ckeditor/themes/n1theme/';
                if (window.CRM.bDarkMode) {
                    theme = 'moono-dark,/skin/js/ckeditor/themes/moono-dark/';
                }

                // this will create the toolbar for the textarea
                if (window.CRM.editor == null) {
                    if (window.CRM.bEDrive) {
                        window.CRM.editor = CKEDITOR.replace('documentText', {
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
                        window.CRM.editor = CKEDITOR.replace('documentText', {
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

                $('#documentTitle').val(data.note.Title);
                $("#documentType").val(data.note.Type);
                $("#private").prop("checked", data.note.Private);
                CKEDITOR.instances['documentText'].setData(data.note.Text);
            } else {
                window.CRM.DisplayNormalAlert(i18next.t("Error"), i18next.t(data.message));
            }
        });
    });

    $(document).on("click", ".deleteDocument", function () {
        var docID = $(this).data('id');
        var perID = $(this).data('perid');
        var famID = $(this).data('famid');

        window.CRM.APIRequest({
            method: 'POST',
            path: 'document/get',
            data: JSON.stringify({"docID": docID, "personID": perID, "famID": famID})
        },function (data) {
            if (data.success) {
                window.CRM.APIRequest({
                    method: 'POST',
                    path: 'document/get',
                    data: JSON.stringify({
                        "docID": docID,
                        "personID": window.CRM.currentPersonID,
                        "famID": window.CRM.currentFamily
                    })
                },function (data) {
                    message = '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle" aria-hidden="true"></i>' + i18next.t('Please confirm deletion of this document') + ' : ' + data.note.Title + '</div><br>' + data.note.Text;

                    bootbox.confirm({
                        title: i18next.t("Document Delete Confirmation"),
                        message: message,
                        size: 'large',
                        callback: function (result) {
                            if (result) {
                                window.CRM.APIRequest({
                                    method: 'POST',
                                    path: 'document/delete',
                                    data: JSON.stringify({"docID": docID})
                                },function (data) {
                                    if (window.CRM.docType == 'person') {
                                        location.href = window.CRM.root + '/v2/people/person/view/' + window.CRM.currentPersonID + '/Documents';
                                    } else if (window.CRM.docType == 'family') {
                                        location.href = window.CRM.root + '/v2/people/family/view/' + window.CRM.currentFamily + '/Documents';
                                    }
                                });
                            }
                        }
                    });
                });
            } else {
                window.CRM.DisplayNormalAlert(i18next.t("Error"), i18next.t(data.message));
            }
        });
    });


    const BootboxContent = (sTitleText, sDocType, sText) => {

        var frm_str = '<form id="some-form">'
            + '<div>'
            + '<div class="row">'
            + '<div class="col-md-3"><span style="color: red">*</span>' + i18next.t('Document Title') + ":</div>"
            + '<div class="col-md-9">'
            + '<input type="text" id="documentTitle" placeholder="' + i18next.t("Set your Document title") + '" size="30" maxlength="100" class="form-control form-control-sm"  width="100%" style="width: 100%" required>'
            + '</div>'
            + '</div>'
            + '<br>'
            + '<div class="row div-title">'
            + '<div class="col-md-3"><span style="color: red">*</span>' + i18next.t('Choose your Document Type') + ":</div>"
            + '<div class="col-md-9">'
            + '  <select name="documentType" class="form-control form-control-sm" id="documentType">'
            + '     <option value="note">' + i18next.t("document") + '</option>'
            + '     <option value="video">' + i18next.t("video") + '</option>'
            + '     <option value="audio">' + i18next.t("audio") + '</option>'
            + '  </select>'
            + '</div>'
            + '</div>'
            + '<div class="row  eventNotes">'
            + '<div class="col-md-12" style="padding-left:0px;padding-right:2px;">'
            + '<textarea name="documentText" cols="80" class="form-control form-control-sm" id="documentText"  width="100%" style="margin-top:-58px;width: 100%;height: 4em;"></textarea></div>'
            + '</div>'
            + '</div>'
            + '<div class="row  eventNotes">'
            + '<div class="col-md-12">'
            + '   <center><input type="checkbox" value="1" id="private" name="private" echo "checked">&nbsp;<label for="private">' + i18next.t('Private') + '</label></center>'
            + '</div>'
            + '</div>'
            + '</form>';

        var object = $('<div/>').html(frm_str).contents();

        return object;
    }

    const DocumentEditorWindow = (mode, docID) => {

        var modal = bootbox.dialog({
            message: BootboxContent(),
            title: i18next.t("Document Editor"),
            size: 'large',
            buttons: [
                {
                    label: '<i class="fas fa-times"></i> ' + i18next.t("Close"),
                    className: "btn btn-default",
                    callback: function () {
                        window.CRM.APIRequest({
                            method: 'POST',
                            path: 'document/leave',
                            data: JSON.stringify({"docID": docID})
                        },function (data) {
                            console.log("we just close the doc ! ");
                        });
                    }
                },
                {
                    label: '<i class="fas fa-check"></i> ' + i18next.t("Save"),
                    className: "btn btn-primary",
                    callback: function () {
                        var DocumentTitle = $('#documentTitle').val();
                        var perId = window.CRM.currentPersonID;
                        var famId = window.CRM.currentFamily;

                        if (window.CRM.docType == 'person') {
                            famId = 0;
                        } else if (window.CRM.docType == 'family') {
                            perId = 0;
                        }

                        if (DocumentTitle != "") {
                            var Type = $("#documentType").val();
                            var Private = $('#private').is(':checked');
                            var htmlBody = CKEDITOR.instances['documentText'].getData();

                            if (mode == 'create') {
                                window.CRM.APIRequest({
                                    method: 'POST',
                                    path: 'document/create',
                                    data: JSON.stringify({
                                        "personID": perId,
                                        "famID": famId,
                                        "title": DocumentTitle,
                                        "type": Type,
                                        "text": htmlBody,
                                        "bPrivate": Private
                                    })
                                },function (data) {
                                    if (data.success) {
                                        if (window.CRM.docType == 'person') {
                                            location.href = window.CRM.root + '/v2/people/person/view/' + window.CRM.currentPersonID + '/Documents';
                                        } else if (window.CRM.docType == 'family') {
                                            location.href = window.CRM.root + '/v2/people/family/view/' + window.CRM.currentFamily + '/Documents';
                                        }
                                    }
                                });
                            } else if (mode == 'edit') {
                                window.CRM.APIRequest({
                                    method: 'POST',
                                    path: 'document/update',
                                    data: JSON.stringify({
                                        "docID": docID,
                                        "title": DocumentTitle,
                                        "type": Type,
                                        "text": htmlBody,
                                        "bPrivate": Private
                                    })
                                },function (data) {
                                    if (data.success) {
                                        if (window.CRM.docType == 'person') {
                                            location.href = window.CRM.root + '/v2/people/person/view/' + window.CRM.currentPersonID + '/Documents';
                                        } else if (window.CRM.docType == 'family') {
                                            location.href = window.CRM.root + '/v2/people/family/view/' + window.CRM.currentFamily + '/Documents';
                                        }
                                    }
                                });
                            }
                        } else {
                            window.CRM.DisplayNormalAlert(i18next.t("Error"), i18next.t("You have to set a Title for your document"));

                            return false;
                        }
                    }
                }
            ],
            show: false,
            onEscape: function () {
                window.CRM.APIRequest({
                    method: 'POST',
                    path: 'document/leave',
                    data: JSON.stringify({"docID": docID})
                },function (data) {
                    console.log("we just close the doc ! ");
                    modal.modal("hide");
                });
            }
        });

        // this will ensure that image and table can be focused
        $(document).on('focusin', function (e) {
            e.stopImmediatePropagation();
        });

        return modal;
    }
});
