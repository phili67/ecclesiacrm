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

    $(document).on("click", ".deleteDocument", function (e) {
        e.preventDefault();
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
                    var message = '<div class="alert alert-danger mb-0"><i class="fas fa-exclamation-triangle text-danger mr-2"></i>' + i18next.t('Please confirm deletion of this document') + ' : <strong>' + data.note.Title + '</strong></div>';

                    bootbox.confirm({
                        title: '<i class="fas fa-trash-alt text-danger mr-2"></i>' + i18next.t("Document Delete Confirmation"),
                        message: message,
                        size: 'large',
                        buttons: {
                          cancel: {label: i18next.t('Cancel'), className: 'btn-outline-secondary'},
                          confirm: {label: '<i class="fas fa-trash-alt mr-1"></i>' + i18next.t('Delete'), className: 'btn-danger'}
                        },
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

        var frm_str = `
            <form id="some-form">
                <div class="alert alert-light border d-flex align-items-start mb-3">
                    <i class="fas fa-file-alt text-primary mt-1 mr-2"></i>
                    <div>
                        <div class="font-weight-bold">${i18next.t("Document Editor")}</div>
                        <div class="small text-muted">${i18next.t("Create a note, audio or video entry and keep it organized with a clear title.")}</div>
                    </div>
                </div>

                <div class="card card-outline card-secondary shadow-sm mb-3">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-info-circle text-secondary mr-2"></i>
                            <div>
                                <div class="font-weight-bold">${i18next.t("Document details")}</div>
                                <div class="small text-muted">${i18next.t("Set the title and choose the type before writing the content.")}</div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="documentTitle" class="font-weight-bold mb-1">
                                <i class="fas fa-heading text-primary mr-1"></i><span style="color: red">*</span>${i18next.t('Document Title')}
                            </label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-pen"></i></span>
                                </div>
                                <input type="text" id="documentTitle" placeholder="${i18next.t("Set your Document title")}" maxlength="100" class="form-control form-control-sm" required>
                            </div>
                        </div>

                        <div class="form-group mb-0">
                            <label for="documentType" class="font-weight-bold mb-1">
                                <i class="fas fa-tags text-info mr-1"></i><span style="color: red">*</span>${i18next.t('Choose your Document Type')}
                            </label>
                            <select name="documentType" class="form-control form-control-sm" id="documentType">
                                <option value="note">${i18next.t("document")}</option>
                                <option value="video">${i18next.t("video")}</option>
                                <option value="audio">${i18next.t("audio")}</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="card card-outline card-secondary shadow-sm mb-3">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-align-left text-danger mr-2"></i>
                            <div>
                                <div class="font-weight-bold">${i18next.t("Content")}</div>
                                <div class="small text-muted">${i18next.t("Write the main content of your document below.")}</div>
                            </div>
                        </div>

                        <textarea name="documentText" cols="80" class="form-control form-control-sm" id="documentText" width="100%" style="width: 100%;height: 4em;"></textarea>
                    </div>
                </div>

                <div class="card card-outline card-secondary shadow-sm mb-0">
                    <div class="card-body py-3">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" value="1" id="private" name="private" class="custom-control-input">
                            <label for="private" class="custom-control-label font-weight-bold">${i18next.t('Private')}</label>
                        </div>
                        <div class="small text-muted mt-2">${i18next.t("Private documents are visible only to authorized users.")}</div>
                    </div>
                </div>
            </form>`;

        var object = $('<div/>').html(frm_str).contents();

        return object;
    }

    const DocumentEditorWindow = (mode, docID) => {
        var dialogTitle = mode == 'edit'
            ? '<i class="fas fa-file-signature text-primary mr-2"></i>' + i18next.t("Edit Document")
            : '<i class="fas fa-file-medical text-primary mr-2"></i>' + i18next.t("Create Document");

        var modal = bootbox.dialog({
            message: BootboxContent(),
            title: dialogTitle,
            size: 'large',
            buttons: [
                {
                    label: '<i class="fas fa-times"></i> ' + i18next.t("Close"),
                    className: "btn btn-outline-secondary",
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
                    label: '<i class="fas fa-save"></i> ' + i18next.t("Save"),
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
