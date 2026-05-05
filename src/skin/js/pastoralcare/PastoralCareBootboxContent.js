(function () {
    window.CRM = window.CRM || {};

    window.CRM.buildPastoralCareBootboxContent = function (options) {
        var settings = $.extend({
            formId: 'some-form',
            namespace: null,
            typeDesc: '',
            visible: false,
            noteHelpKey: '',
            includeFamilyMembers: false,
            includeFamilyMembersLayout: 'stacked',
            showWholeFamilyCheckbox: false,
            wholeFamilyCheckboxId: 'applyToWholeFamily',
            wholeFamilyCheckboxName: 'applyToWholeFamily',
            wholeFamilyCheckboxLabelKey: i18next.t('Record this pastoral follow-up for the whole family'),
            visibilityIdPrefix: 'visibility',
            noteTextId: 'NoteText',
            noteTextClass: 'form-control form-control-sm',
            noteTextAttributes: 'style="width:100%;height:4em;"'
        }, options || {});
        var visibilityShowId = settings.visibilityIdPrefix + 'Show';
        var visibilityHideId = settings.visibilityIdPrefix + 'Hide';
        var noteHelp = settings.noteHelpKey ? settings.noteHelpKey : '';
        var visibilitySection = '<div class="font-weight-bold mb-2"><span style="color: red">*</span>' + i18next.t('For every administrator') + '</div>'
            + '<div class="custom-control custom-radio mb-2">'
            + '<input class="custom-control-input" type="radio" id="' + visibilityShowId + '" name="visibilityStatus" value="1"' + (settings.visible ? ' checked' : '') + '>'
            + '<label class="custom-control-label" for="' + visibilityShowId + '">' + i18next.t('Show') + '</label>'
            + '</div>'
            + '<div class="custom-control custom-radio">'
            + '<input class="custom-control-input" type="radio" id="' + visibilityHideId + '" name="visibilityStatus" value="0"' + (!settings.visible ? ' checked' : '') + '>'
            + '<label class="custom-control-label" for="' + visibilityHideId + '">' + i18next.t('Hide') + '</label>'
            + '</div>';
        var familyMembersSection = '<div class="font-weight-bold mb-2"><span style="color: red">*</span>' + i18next.t('Include all the family members') + '</div>'
            + '<div class="custom-control custom-checkbox">'
            + '<input class="custom-control-input" type="checkbox" id="includeFamilyMembers" name="includeFamilyMembers">'
            + '<label class="custom-control-label" for="includeFamilyMembers">' + i18next.t('Include') + '</label>'
            + '</div>';
        var wholeFamilySection = '<hr>'
            + '<div class="custom-control custom-checkbox">'
            + '<input class="custom-control-input" type="checkbox" id="' + settings.wholeFamilyCheckboxId + '" name="' + settings.wholeFamilyCheckboxName + '">'
            + '<label class="custom-control-label" for="' + settings.wholeFamilyCheckboxId + '">' + settings.wholeFamilyCheckboxLabelKey + '</label>'
            + '</div>';
        var optionsSection = visibilitySection;

        if (settings.includeFamilyMembers) {
            if (settings.includeFamilyMembersLayout === 'split') {
                optionsSection = '<div class="row">'
                    + '<div class="col-md-6 mb-3 mb-md-0">' + visibilitySection + '</div>'
                    + '<div class="col-md-6">' + familyMembersSection + '</div>'
                    + '</div>';
            } else {
                optionsSection += '<hr>' + familyMembersSection;
            }
        }

        if (settings.showWholeFamilyCheckbox) {
            optionsSection += wholeFamilySection;
        }

        var formContent = '<form id="' + settings.formId + '">'
            + window.CRM.buildDialogNotice('fa-hands-helping text-primary', i18next.t('Pastoral care note'), i18next.t('Review the note type, write the content and define who can see it.'), 'alert-light border')
            + '<div class="card card-outline card-secondary shadow-sm mt-3 mb-3">'
            + '<div class="card-body">'
            + '<div class="form-group mb-0">'
            + '<label class="font-weight-bold mb-1"><i class="fas fa-tag text-primary mr-1"></i>' + i18next.t('Type') + '</label>'
            + '<div class="form-control form-control-sm bg-light">' + settings.typeDesc + '</div>'
            + '</div>'
            + '</div>'
            + '</div>'
            + '<div class="card card-outline card-secondary shadow-sm mb-3">'
            + '<div class="card-body">'
            + '<div class="d-flex align-items-center mb-2">'
            + '<i class="fas fa-align-left text-info mr-2"></i>'
            + '<div>'
            + '<div class="font-weight-bold">' + i18next.t('Note') + '</div>'
            + '<div class="small text-muted">' + noteHelp + '</div>'
            + '</div>'
            + '</div>'
            + '<textarea name="NoteText" cols="80" class="' + settings.noteTextClass + '" id="' + settings.noteTextId + '" ' + settings.noteTextAttributes + '></textarea>'
            + '</div>'
            + '</div>'
            + '<div class="card card-outline card-secondary shadow-sm mb-0">'
            + '<div class="card-body">'
            + optionsSection
            + '</div>'
            + '</div>'
            + '</form>';

        return $('<div/>').html(formContent).contents();
    };
}());