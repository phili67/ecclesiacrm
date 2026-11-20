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
            window.CRM.destroyEditor(window.CRM.editor);
            window.CRM.editor = null;
        }

        var modal = DocumentEditorWindow('create', 0);

        var theme = 'default';
        if (window.CRM.bDarkMode) {
            theme = 'default';
        }

        // this will create the toolbar for the textarea
        if (window.CRM.editor == null) {
            if (window.CRM.bEDrive) {
                window.CRM.editor = window.CRM.createEditor('documentText', {
                    preset: 'notes',
                    language: window.CRM.lang,
                    width: '100%',
                    uploadType: 'privateImages',
                });
            } else {
                window.CRM.editor = window.CRM.createEditor('documentText', {
                    preset: 'notes',
                    language: window.CRM.lang,
                    width: '100%'
                });
            }


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
                if (!data.canEdit) {
                    window.CRM.DisplayNormalAlert(i18next.t("Error"), i18next.t("Read only"));
                    return;
                }
                if (window.CRM.editor) {
                    window.CRM.destroyEditor(window.CRM.editor);
                    window.CRM.editor = null;
                }

                var modal = DocumentEditorWindow('edit', docID, data.version);

                var theme = 'default';
                if (window.CRM.bDarkMode) {
                    theme = 'default';
                }

                // this will create the toolbar for the textarea
                if (window.CRM.editor == null) {
                    if (window.CRM.bEDrive) {
                        window.CRM.editor = window.CRM.createEditor('documentText', {
                            preset: 'notes',
                            language: window.CRM.lang,
                            width: '100%',
                            uploadType: 'privateImages',
                        });
                    } else {
                        window.CRM.editor = window.CRM.createEditor('documentText', {
                            preset: 'notes',
                            language: window.CRM.lang,
                            width: '100%',
                        });
                    }

                }

                modal.modal("show");

                $('#documentTitle').val(data.note.Title);
                $("#documentType").val(data.note.Type);
                $("#private").prop("checked", data.note.Private);
                $("#private").prop("disabled", !data.canChangePrivacy);
                window.CRM.editor.then(function (editor) {
                    editor.setData(data.note.Text);
                });
            } else {
                window.CRM.DisplayNormalAlert(i18next.t("Error"), i18next.t(data.message));
            }
        }, function (error) {
            window.CRM.DisplayNormalAlert(i18next.t("Error"), error.message);
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
                                }, function (error) {
                                    window.CRM.DisplayNormalAlert(i18next.t("Error"), error.message);
                                });
                            }
                        }
                    });
            } else {
                window.CRM.DisplayNormalAlert(i18next.t("Error"), i18next.t(data.message));
            }
        }, function (error) {
            window.CRM.DisplayNormalAlert(i18next.t("Error"), error.message);
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

                <div class="alert alert-warning d-none" id="documentConflict">
                    <p>${i18next.t("This document has changed since you opened it. Your edits are still in the editor. Compare them with the current version and merge them manually.")}</p>
                    <div class="font-weight-bold" id="documentConflictTitle"></div>
                    <div id="documentConflictType"></div>
                    <textarea id="documentConflictText" class="form-control my-2" rows="8" readonly></textarea>
                    <button type="button" class="btn btn-warning btn-sm" id="documentUseCurrentVersion">${i18next.t("Use current version as the base for my edits")}</button>
                </div>
                <div class="card card-outline card-secondary shadow-sm mb-0">
                    <div class="card-body py-3">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" value="1" id="private" name="private" class="custom-control-input">
                            <label for="private" class="custom-control-label font-weight-bold">${i18next.t('Private')}</label>
                        </div>
                        <div class="small text-muted mt-2">${i18next.t("Private documents are visible only to their creator and selected recipients. Sharing a document makes it private.")}</div>
                    </div>
                </div>
            </form>`;

        var object = $('<div/>').html(frm_str).contents();

        return object;
    }

    const DocumentEditorWindow = (mode, docID, initialVersion) => {
        var version = initialVersion;
        var saving = false;
        var dialogTitle = mode == 'edit'
            ? '<i class="fas fa-file-signature text-primary mr-2"></i>' + i18next.t("Edit Document")
            : '<i class="fas fa-file-medical text-primary mr-2"></i>' + i18next.t("Create Document");

        var modal = bootbox.dialog({
            message: BootboxContent(),
            title: dialogTitle,            
            size: 'extra-large',
            buttons: [
                {
                    label: '<i class="fas fa-times"></i> ' + i18next.t("Close"),
                    className: "btn btn-outline-secondary",
                    callback: function () {
                        return !saving;
                    }
                },
                {
                    label: '<i class="fas fa-save"></i> ' + i18next.t("Save"),
                    className: "btn btn-primary",
                    callback: function () {
                        if (saving) {
                            return false;
                        }
                        var DocumentTitle = $('#documentTitle').val();
                        var perId = window.CRM.currentPersonID;
                        var famId = window.CRM.currentFamily;

                        if (window.CRM.docType == 'person') {
                            famId = 0;
                        } else if (window.CRM.docType == 'family') {
                            perId = 0;
                        }

                        if (DocumentTitle != "") {
                            saving = true;
                            var Type = $("#documentType").val();
                            var Private = $('#private').is(':checked');
                            var htmlBody = window.CRM.getEditor('documentText').getData();

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
                                    } else {
                                        saving = false;
                                        window.CRM.DisplayNormalAlert(i18next.t("Error"), i18next.t("Invalid document"));
                                    }
                                }, function (error) {
                                    saving = false;
                                    window.CRM.DisplayNormalAlert(i18next.t("Error"), error.message);
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
                                        "bPrivate": Private,
                                        "version": version
                                    })
                                },function (data) {
                                    if (data.success) {
                                        if (window.CRM.docType == 'person') {
                                            location.href = window.CRM.root + '/v2/people/person/view/' + window.CRM.currentPersonID + '/Documents';
                                        } else if (window.CRM.docType == 'family') {
                                            location.href = window.CRM.root + '/v2/people/family/view/' + window.CRM.currentFamily + '/Documents';
                                        }
                                    } else {
                                        saving = false;
                                        window.CRM.DisplayNormalAlert(i18next.t("Error"), i18next.t("Invalid document"));
                                    }
                                }, function (error) {
                                    saving = false;
                                    if (error.status === 409 && error.response && error.response.note) {
                                        var current = error.response;
                                        modal.find('#documentConflictTitle').text(current.note.Title);
                                        modal.find('#documentConflictType').text(current.note.Type);
                                        modal.find('#documentConflictText').val(current.note.Text);
                                        modal.find('#documentConflict').removeClass('d-none');
                                        modal.find('#documentUseCurrentVersion').off('click').on('click', function () {
                                            version = current.version;
                                            if (!current.canChangePrivacy) {
                                                modal.find('#private').prop('checked', !!Number(current.note.Private));
                                            }
                                            modal.find('#private').prop('disabled', !current.canChangePrivacy);
                                            modal.find('#documentConflict').addClass('d-none');
                                        });
                                    } else {
                                        window.CRM.DisplayNormalAlert(i18next.t("Error"), error.message);
                                    }
                                });
                            }
                            return false;
                        } else {
                            window.CRM.DisplayNormalAlert(i18next.t("Error"), i18next.t("You have to set a Title for your document"));

                            return false;
                        }
                    }
                }
            ],
            show: false,
            onEscape: function () {
                return !saving;
            }
        });

        // this will ensure that image and table can be focused
        $(document).on('focusin', function (e) {
            e.stopImmediatePropagation();
        });

        return modal;
    }

    $(document).on('click', '.noteHistory', function () {
        var docID = $(this).data('id');
        window.CRM.APIRequest({
            method: 'POST',
            path: 'document/history',
            data: JSON.stringify({docID: docID})
        }, function (data) {
            var content = $('<div>');
            var list = $('<select class="form-control mb-2" size="6">').appendTo(content);
            var details = $('<div class="mb-2">').appendTo(content);
            var preview = $('<iframe class="w-100 border rounded" sandbox="">')
                .attr('title', i18next.t('Preview'))
                .css({height: '400px', background: '#fff'}).appendTo(content);
            if (!data.history.length) {
                $('<p>').text(i18next.t('No versions have been saved yet.')).prependTo(content);
            }
            data.history.forEach(function (revision) {
                $('<option>').val(revision.id).text(revision.created_at + ' — ' + revision.author + ' — ' + revision.title).appendTo(list);
            });
            function selectedRevision() {
                return data.history.find(function (revision) { return String(revision.id) === list.val(); });
            }
            list.on('change', function () {
                var revision = selectedRevision();
                details.text(revision ? revision.title + ' (' + revision.type + ')' : '');
                preview.prop('srcdoc', '<!doctype html><html><head><meta charset="utf-8">' +
                    '<meta http-equiv="Content-Security-Policy" content="default-src \'none\'; img-src http: https: data: blob:; media-src http: https: data: blob:; style-src \'unsafe-inline\'; form-action \'none\'; base-uri \'none\'">' +
                    '<style>body{font-family:Arial,sans-serif;line-height:1.5;margin:1rem;overflow-wrap:anywhere}' +
                    'img,video{max-width:100%;height:auto}figure{margin:1em 0}table{border-collapse:collapse;max-width:100%}' +
                    'td,th{border:1px solid #ccc;padding:.4em}</style></head><body>' +
                    (revision ? revision.content || '' : '') + '</body></html>');
            });
            if (data.history.length) {
                list.val(String(data.history[0].id)).trigger('change');
            }
            var buttons = {
                close: {label: i18next.t('Close'), className: 'btn-outline-secondary'}
            };
            if (data.canEdit && data.history.length) {
                buttons.restore = {
                    label: i18next.t('Restore this version'),
                    className: 'btn-warning',
                    callback: function () {
                        var revision = selectedRevision();
                        if (!revision) {
                            return false;
                        }
                        bootbox.confirm(i18next.t('Restore this version? Your current note will remain in the history.'), function (confirmed) {
                            if (!confirmed) {
                                return;
                            }
                            window.CRM.APIRequest({
                                method: 'POST',
                                path: 'document/restore',
                                data: JSON.stringify({docID: docID, revisionId: revision.id, version: data.version})
                            }, function () {
                                location.reload();
                            }, function (error) {
                                window.CRM.DisplayNormalAlert(i18next.t("Error"), error.message);
                            });
                        });
                        return false;
                    }
                };
            }
            bootbox.dialog({title: i18next.t('Version history'), message: content, size: 'large', buttons: buttons});
        }, function (error) {
            window.CRM.DisplayNormalAlert(i18next.t("Error"), error.message);
        });
    });
});
