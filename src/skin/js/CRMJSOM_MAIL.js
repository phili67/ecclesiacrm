/*
 * EcclesiaCRM JavaScript Object Model Initialization Script
 * © Philippe Logel
 */

function BootboxMail(subject, body) {
    var frm_str = '';

    if (subject != null) {
        if (subject == 0) {
            subject = "";
        }
        frm_str += '<div>'
            + '<div class="row" style="margin-left:-15px;margin-right:-15px">'
            + '<div class="col-md-12">'
            + '<input type="text" id="MailSubject" placeholder="' + i18next.t("Subject:") + '" size="30" maxlength="100" class="form-control"  value="' + subject + '" width="100%" style="width: 100%" required>'
            + '</div>'
            + '</div>';
    }

    frm_str += '<div class="row  eventNotes">'
        + '<div class="col-md-12" style="padding-left:0px;padding-right:2px;">'
        + '<textarea name="MailText" cols="80" class="form-control form-control-sm" id="MailText"  width="100%" style="margin-top:-58px;width: 100%;height: 4em;">' + body + '</textarea></div>'
        + '</div>'
        + '</div>'
        + '<div class="row  eventNotes">'
        + '</div>'
        + '</div>';

    var object = $('<div/>').html(frm_str).contents();

    return object;
}

window.CRM.mail = function (subject, body, route, extraParams, callback ) {
    /* the route can be used like :
        - arguments :
            • subject       : optional
            • body          : optional too
            • route         : is necessary
            • extraParams   : for the api
            • callBack      : for the api return
        - isMailerAvailable : false (in this case, we have to extract via the route all the email adresses (the return is emails, subject, body)
        - isMailerAvailable : true (in this case, we make the call internal (through php) via another email tool.
     */
    if ( window.CRM.isMailerAvailable == false ) {
        // we use the mailto system : the api must return (data.emails and data.subject)
        allParams = null;
        window.CRM.APIRequest({
            method: 'POST',
            path: route,
            data: JSON.stringify(extraParams)
        }, function (data) {
            if (callback) {
                window.open("mailto:?bcc=" + data.emails + "&subject=" + data.subject + "&body=" + encodeURIComponent(data.body));
            }
        });

        return null;
    } else {
        // to avoid the \n
        // we use the mailto system : subject and body are optional
        body = body. replace(/(?:\ r\n|\r|\n)/g, '<br>');
        var modal = bootbox.dialog({
            message: BootboxMail(subject, body),
            title: i18next.t("Compose New Message"),
            size: 'large',
            buttons: [
                {
                    label: '<i class="fas fa-times"></i> ' + i18next.t("Close"),
                    className: "btn btn-default",
                    callback: function () {
                        CKEDITOR.remove(window.CRM.editor);
                        window.CRM.editor = null;
                    }
                },
                {
                    label: '<i class="fas fa-paper-plane"></i> ' + i18next.t("Send"),
                    className: "btn btn-primary",
                    callback: function () {
                        var mailSubject = '';

                        if (subject != null) {
                            mailSubject = $('#MailSubject').val();
                        }
                        var htmlBody = CKEDITOR.instances['MailText'].getData();

                        var params = {
                            "mailSubject": mailSubject,
                            "htmlBody": htmlBody
                        };

                        var allParams = Object.assign({}, params, extraParams);  // Object {a: 4, b: 2, c: 110}

                        window.CRM.APIRequest({
                            method: 'POST',
                            path: route,
                            data: JSON.stringify(allParams)
                        }, function (data) {
                            if (callback) {
                                callback(data);
                                CKEDITOR.remove(window.CRM.editor);
                                window.CRM.editor = null;
                            }
                        });
                    }
                }
            ],
            show: false,
            onEscape: function () {
                CKEDITOR.remove(window.CRM.editor);
                window.CRM.editor = null;
            }
        });

        // this will ensure that image and table can be focused
        $(document).on('focusin', function (e) {
            e.stopImmediatePropagation();
        });

        var theme = 'n1theme,/skin/js/ckeditor/themes/n1theme/';
        if (window.CRM.bDarkMode) {
            theme = 'moono-dark,/skin/js/ckeditor/themes/moono-dark/';
        }

        // this will create the toolbar for the textarea
        if (window.CRM.editor == null) {
            if (window.CRM.bEDrive) {
                window.CRM.editor = CKEDITOR.replace('MailText', {
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
                window.CRM.editor = CKEDITOR.replace('MailText', {
                    customConfig: window.CRM.root + '/skin/js/ckeditor/configs/note_editor_config.js',
                    language: window.CRM.lang,
                    width: '100%',
                    skin: theme
                });
            }

            add_ckeditor_buttons(window.CRM.editor);
            add_ckeditor_buttons_merge_tag_mailchimp(window.CRM.editor);
        }

        return modal;
    }
}



