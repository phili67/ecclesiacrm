//
//  This code is under copyright not under MIT Licence
//  copyright   : 2018 Philippe Logel all right reserved not MIT licence
//                This code can't be incoprorated in another software without any authorizaion
//
//  Updated : 2018/05/30
//

window.CRM.editor = null;

$(function() {
    $(".filterByPastor").on('click', function () {
        var ID = $(this).data("pastorid");

        $(".all-items").hide();
        $(".item-" + ID).show();
    });

    $(".filterByPastorAll").on('click', function () {
        $(".all-items").show();
    });

    $('.user-list').DataTable({
        responsive: true
    });


    $(".modify-pastoral").on('click', function () {
        var ID = $(this).data("id");

        window.CRM.APIRequest({
            method: 'POST',
            path: 'pastoralcare/family/getinfo',
            data: JSON.stringify({"ID": ID})
        },function (data) {
            var id = data.id;
            var typeid = data.typeid;
            var typeDesc = data.typedesc;
            var visible = data.visible;
            var text = data.text;

            if (window.CRM.editor != null) {
                CKEDITOR.remove(window.CRM.editor);
                window.CRM.editor = null;
            }

            // this will create the toolbar for the textarea
            modal = createPastoralCareWindow(typeid, typeDesc, visible, id);

            $('form #NoteText').val(text);

            var theme = 'n1theme,/skin/js/ckeditor/themes/n1theme/';
            if (window.CRM.bDarkMode) {
                theme = 'moono-dark,/skin/js/ckeditor/themes/moono-dark/';
            }

            if (window.CRM.editor == null) {               
                window.CRM.editor = CKEDITOR.replace('NoteText', {
                    customConfig: window.CRM.root + '/skin/js/ckeditor/configs/calendar_event_editor_config.js',
                    language: window.CRM.lang,
                    width: '100%',
                    skin: theme
                });
                
                add_ckeditor_buttons(window.CRM.editor);
            }

            modal.modal("show");
        });
    });

    $(".delete-pastoral").on('click', function () {
        var ID = $(this).data("id");

        bootbox.confirm({
            title: '<i class="fas fa-trash-alt text-danger mr-2"></i>' + i18next.t("Delete Pastoral Care Type"),
            message: window.CRM.buildDialogNotice('fa-exclamation-triangle text-danger', i18next.t('Irreversible action'), i18next.t("This action can never be undone !!!!"), 'alert-danger'),
            buttons: window.CRM.buildDialogButtons(i18next.t('Delete'), 'btn-danger', i18next.t('Keep note'), 'btn-outline-secondary'),
            callback: function (result) {
                if (result == true)// only Pastoral care can be drag and drop, not anniversary or birthday
                {
                    window.CRM.APIRequest({
                        method: 'POST',
                        path: 'pastoralcare/family/delete',
                        data: JSON.stringify({"ID": ID})
                    },function (data) {
                        location.reload();
                        return true;
                    });
                }
            }
        });
    });

    $(".newPastorCare").on('click', function () {
        var typeid = $(this).data('typeid');
        var typeDesc = $(this).data('typedesc');
        var visible = $(this).data('visible');

        if (window.CRM.editor != null) {
            CKEDITOR.remove(window.CRM.editor);
            window.CRM.editor = null;
        }

        // this will create the toolbar for the textarea
        modal = createPastoralCareWindow(typeid, typeDesc, visible);

        var theme = 'n1theme,/skin/js/ckeditor/themes/n1theme/';
        if (window.CRM.bDarkMode) {
            theme = 'moono-dark,/skin/js/ckeditor/themes/moono-dark/';
        }

        if (window.CRM.editor == null) {            
            window.CRM.editor = CKEDITOR.replace('NoteText', {
                customConfig: window.CRM.root + '/skin/js/ckeditor/configs/calendar_event_editor_config.js',
                language: window.CRM.lang,
                width: '100%',
                skin: theme
            });

            add_ckeditor_buttons(window.CRM.editor);
        }

        modal.modal("show");
    });

    function BootboxContent(type, visible) {
        var frm_str = '<form id="some-form">'
            + window.CRM.buildDialogNotice('fa-hands-helping text-primary', i18next.t('Pastoral care note'), i18next.t('Review the note type, write the content and define who can see it.'), 'alert-light border')
            + '<div class="card card-outline card-secondary shadow-sm mt-3 mb-3">'
            + '<div class="card-body">'
            + '<div class="form-group mb-0">'
            + '<label class="font-weight-bold mb-1"><i class="fas fa-tag text-primary mr-1"></i>' + i18next.t('Type') + '</label>'
            + '<div class="form-control form-control-sm bg-light">' + type + '</div>'
            + '</div>'
            + '</div>'
            + '</div>'
            + '<div class="card card-outline card-secondary shadow-sm mb-3">'
            + '<div class="card-body">'
            + '<div class="d-flex align-items-center mb-2">'
            + '<i class="fas fa-align-left text-info mr-2"></i>'
            + '<div>'
            + '<div class="font-weight-bold">' + i18next.t('Note') + '</div>'
            + '<div class="small text-muted">' + i18next.t('Write the pastoral care message associated with this family.') + '</div>'
            + '</div>'
            + '</div>'
            + '<textarea name="NoteText" cols="80" class="form-control form-control-sm NoteText" id="NoteText" width="100%" style="width: 100%;height: 4em;"></textarea>'
            + '</div>'
            + '</div>'
            + '<div class="card card-outline card-secondary shadow-sm mb-0">'
            + '<div class="card-body">'
            + '<div class="row">'
            + '<div class="col-md-6 mb-3 mb-md-0">'
            + '<div class="font-weight-bold mb-2"><span style="color: red">*</span>' + i18next.t("For every administrator") + '</div>'
            + '<div class="custom-control custom-radio mb-2">'
            + '<input class="custom-control-input" type="radio" id="visibilityShow" name="visibilityStatus" value="1"' + ((visible) ? ' checked' : '') + '>'
            + '<label class="custom-control-label" for="visibilityShow">' + i18next.t("Show") + '</label>'
            + '</div>'
            + '<div class="custom-control custom-radio">'
            + '<input class="custom-control-input" type="radio" id="visibilityHide" name="visibilityStatus" value="0"' + ((!visible) ? ' checked' : '') + '>'
            + '<label class="custom-control-label" for="visibilityHide">' + i18next.t("Hide") + '</label>'
            + '</div>'
            + '</div>'
            + '<div class="col-md-6">'
            + '<div class="font-weight-bold mb-2"><span style="color: red">*</span>' + i18next.t("Include all the family members") + '</div>'
            + '<div class="custom-control custom-checkbox">'
            + '<input class="custom-control-input" type="checkbox" id="includeFamilyMembers" name="includeFamilyMembers">'
            + '<label class="custom-control-label" for="includeFamilyMembers">' + i18next.t("Include") + '</label>'
            + '</div>'
            + '</div>'
            + '</div>'
            + '</div>'
            + '</div>'
            + '</form>';

        var object = $('<div/>').html(frm_str).contents();

        return object
    }

    function createPastoralCareWindow(typeID, typeDesc, visible, id) // dialogType : createEvent or modifyEvent, eventID is when you modify and event
    {
        if (id === undefined) {
            id = -1;
        }

        var modal = bootbox.dialog({
            title: '<i class="fas fa-hands-helping text-primary mr-2"></i>' + i18next.t("Pastoral Care Note Creation"),
            message: BootboxContent(typeDesc, visible),
            size: 'large',
            buttons: [
                {
                    label: '<i class="fas fa-times"></i> ' + i18next.t("Close"),
                    className: "btn btn-outline-secondary",
                    callback: function () {
                        console.log("just do something on close");
                    }
                },
                {
                    label: '<i class="fas fa-save"></i> ' + i18next.t("Save"),
                    className: "btn btn-primary",
                    callback: function () {
                        var visibilityStatus = $('input[name="visibilityStatus"]:checked').val();
                        var NoteText = CKEDITOR.instances['NoteText'].getData();//$('form #NoteText').val();
                        var includeFamMembers = $('#includeFamilyMembers').is(":checked");

                        if (id == -1) {
                            window.CRM.APIRequest({
                                method: 'POST',
                                path: 'pastoralcare/family/add',
                                data: JSON.stringify({
                                    "typeID": typeID,
                                    "familyID": currentFamilyID,
                                    "currentPastorId": currentPastorId,
                                    "typeDesc": typeDesc,
                                    "visibilityStatus": visibilityStatus,
                                    "noteText": NoteText,
                                    "includeFamMembers": includeFamMembers
                                })
                            },function (data) {
                                location.reload();
                                return true;
                            });
                        } else {
                            window.CRM.APIRequest({
                                method: 'POST',
                                path: 'pastoralcare/family/modify',
                                data: JSON.stringify({
                                    "ID": id,
                                    "typeID": typeID,
                                    "familyID": currentFamilyID,
                                    "currentPastorId": currentPastorId,
                                    "typeDesc": typeDesc,
                                    "visibilityStatus": visibilityStatus,
                                    "noteText": NoteText
                                })
                            },function (data) {
                                location.reload();
                                return true;
                            })
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


    $('#add-event').on('click', function (e) {
        var fmt = 'YYYY-MM-DD HH:mm:ss';

        var dateStart = moment().format(fmt);
        var dateEnd = moment().format(fmt);

        addEvent(dateStart, dateEnd, i18next.t("Appointment"), sPageTitle);
    });
});
