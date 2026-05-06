$(function () {
    function getDashboardConfig() {
        window.CRM = window.CRM || {};
        window.CRM.BirthdayAnniversaryDashboard = window.CRM.BirthdayAnniversaryDashboard || {};

        return window.CRM.BirthdayAnniversaryDashboard;
    }

    function getCurrentPastorId() {
        var config = getDashboardConfig();

        return config.currentPastorId || window.CRM.currentPastorId || null;
    }

    function ensureEditorAssets() {
        var config = getDashboardConfig();

        if (config.editorAssetsPromise) {
            return config.editorAssetsPromise;
        }

        if (window.CKEDITOR && typeof window.add_ckeditor_buttons === 'function') {
            config.editorAssetsPromise = Promise.resolve();

            return config.editorAssetsPromise;
        }

        config.editorAssetsPromise = Promise.reject(new Error('BirthdayAnniversaryDashboard editor assets are not available.'));

        return config.editorAssetsPromise;
    }

    function formatTimestamp() {
        return moment().format('YYYY-MM-DD HH:mm');
    }

    function destroyPastoralEditor() {
        if (window.CRM.editor != null) {
            CKEDITOR.remove(window.CRM.editor);
            window.CRM.editor = null;
        }
    }

    function buildPastoralBootboxContent(entityType, typeDesc, visible, canApplyToWholeFamily) {
        var noteHelpKey = entityType === 'family'
            ? 'Write the pastoral care message associated with this family.'
            : 'Write the pastoral care message associated with this person.';

        return window.CRM.buildPastoralCareBootboxContent({
            formId: 'birthday-pastoral-form',
            namespace: 'BirthdayAnniversaryDashboard',
            typeDesc: typeDesc,
            visible: visible,
            noteHelpKey: noteHelpKey,
            includeFamilyMembers: entityType === 'family',
            includeFamilyMembersLayout: 'stacked',
            showWholeFamilyCheckbox: entityType === 'person' && canApplyToWholeFamily,
            wholeFamilyCheckboxId: 'birthdayApplyToWholeFamily',
            wholeFamilyCheckboxName: 'birthdayApplyToWholeFamily',
            wholeFamilyCheckboxLabelKey: i18next.t('Record this pastoral follow-up for the whole family', {ns: 'BirthdayAnniversaryDashboard'}),
            visibilityIdPrefix: 'birthdayVisibility',
            noteTextId: 'NoteText',
            noteTextClass: 'form-control form-control-sm',
            noteTextAttributes: 'style="width:100%;height:4em;"'
        });
    }

    function markCardAsContacted($card) {
        $card.removeClass('border-info border-secondary').addClass('border-success');
        $card.find('.birthday-pastoral-status')
            .removeClass('badge-light border')
            .addClass('badge-success')
            .attr('data-state', 'recorded')
            .html('<i class="fas fa-check-circle mr-1"></i>' + i18next.t('Pastoral follow-up recorded', {ns: 'BirthdayAnniversaryDashboard'}));

        $card.find('.birthday-pastoral-date')
            .removeClass('d-none')
            .find('span')
            .text(formatTimestamp());
    }

    function markFamilyRelatedCardsAsContacted(familyId) {
        if (!familyId) {
            return;
        }

        $('.birthday-family-card[data-family-id="' + familyId + '"]').each(function () {
            markCardAsContacted($(this));
        });

        $('.birthday-person-card[data-family-id="' + familyId + '"]').each(function () {
            markCardAsContacted($(this));
        });
    }

    function submitPastoralFollowUp(options) {
        window.CRM.APIRequest({
            method: 'POST',
            path: options.path,
            data: JSON.stringify(options.payload)
        }, function (data) {
            if (data.status === 'success') {
                if (options.scope === 'family') {
                    markFamilyRelatedCardsAsContacted(options.familyId);
                } else {
                    markCardAsContacted(options.$card);
                }

                if (window.CRM.showGlobalMessage) {
                    window.CRM.showGlobalMessage(i18next.t('Pastoral follow-up recorded', {ns: 'BirthdayAnniversaryDashboard'}), 'success');
                }
            } else if (window.CRM.DisplayAlert) {
                window.CRM.DisplayAlert(i18next.t('Error', {ns: 'BirthdayAnniversaryDashboard'}), i18next.t('Unable to record pastoral follow-up.', {ns: 'BirthdayAnniversaryDashboard'}));
            }
        });
    }

    function promptActivationToggle(options) {
        var isPerson = options.entityType === 'person';
        var isActive = options.currentActive;
        var familyId = Number(options.familyId) || 0;
        var canToggleWholeFamily = isPerson && isActive && familyId > 0;
        var title = isActive ? i18next.t('Confirm Deactivation', {ns: 'BirthdayAnniversaryDashboard'}) : i18next.t('Confirm Activation', {ns: 'BirthdayAnniversaryDashboard'});
        var summary = isActive
            ? (isPerson ? i18next.t('You are about to deactivate this person:', {ns: 'BirthdayAnniversaryDashboard'}) : i18next.t('You are about to deactivate this family:', {ns: 'BirthdayAnniversaryDashboard'}))
            : (isPerson ? i18next.t('You are about to reactivate this person:', {ns: 'BirthdayAnniversaryDashboard'}) : i18next.t('You are about to reactivate this family:', {ns: 'BirthdayAnniversaryDashboard'}));
        var warning = isPerson
            ? i18next.t('For GDPR reasons, make sure you have the person\'s agreement before reactivating their record.', {ns: 'BirthdayAnniversaryDashboard'})
            : i18next.t('For GDPR reasons, make sure you have the agreement of the family members before reactivating their record.', {ns: 'BirthdayAnniversaryDashboard'});

        var messageHtml = '<div class="mb-3">'
            + '<div class="font-weight-bold mb-1">' + summary + '</div>'
            + '<div class="h5 mb-0">' + options.entityName + '</div>'
            + '</div>'
            + window.CRM.buildDialogNotice(
                'fa-exclamation-triangle text-warning',
                i18next.t('Privacy reminder', {ns: 'BirthdayAnniversaryDashboard'}),
                warning,
                'alert-light border'
            );

        if (canToggleWholeFamily) {
            messageHtml += '<div class="card card-outline card-secondary shadow-sm mt-3 mb-0">'
                + '<div class="card-body py-3">'
                + '<div class="custom-control custom-checkbox">'
                + '<input class="custom-control-input" type="checkbox" id="birthdayDeactivateWholeFamily" name="birthdayDeactivateWholeFamily">'
                + '<label class="custom-control-label" for="birthdayDeactivateWholeFamily">' + i18next.t('Also deactivate the whole family', {ns: 'BirthdayAnniversaryDashboard'}) + '</label>'
                + '</div>'
                + '<div class="small text-muted mt-2">' + i18next.t('Use this if the entire household should be marked as inactive at the same time.', {ns: 'BirthdayAnniversaryDashboard'}) + '</div>'
                + '</div>'
                + '</div>';
        }

        bootbox.dialog({
            title: '<i class="fas ' + (isActive ? 'fa-user-slash text-warning' : 'fa-user-check text-success') + ' mr-2"></i>' + title,
            message: messageHtml,
            buttons: {
                cancel: {
                    label: '<i class="fas fa-times mr-1"></i>' + i18next.t('Cancel', {ns: 'BirthdayAnniversaryDashboard'}),
                    className: 'btn btn-outline-secondary'
                },
                confirm: {
                    label: '<i class="fas ' + (isActive ? 'fa-user-slash' : 'fa-user-check') + ' mr-1"></i>' + (isActive ? i18next.t('Confirm deactivation', {ns: 'BirthdayAnniversaryDashboard'}) : i18next.t('Confirm activation', {ns: 'BirthdayAnniversaryDashboard'})),
                    className: isActive ? 'btn btn-danger' : 'btn btn-success',
                    callback: function () {
                        var useFamilyToggle = canToggleWholeFamily && $('#birthdayDeactivateWholeFamily').is(':checked');
                        var path = useFamilyToggle
                            ? 'families/' + familyId + '/activate/false'
                            : (isPerson ? 'persons/' + options.entityId : 'families/' + options.entityId) + '/activate/' + !isActive;

                        window.CRM.APIRequest({
                            method: 'POST',
                            path: path
                        }, function (data) {
                            if (data && data.success === true) {
                                window.location.reload();
                            } else if (window.CRM.DisplayAlert) {
                                window.CRM.DisplayAlert(i18next.t('Error', {ns: 'BirthdayAnniversaryDashboard'}), isPerson ? i18next.t('Unable to update this person status.', {ns: 'BirthdayAnniversaryDashboard'}) : i18next.t('Unable to update this family status.', {ns: 'BirthdayAnniversaryDashboard'}));
                            }
                        });
                    }
                }
            }
        });
    }

    function openPastoralNoteDialog(entityType, entityId, entityName, typeId, typeDesc, visible, $card) {
        ensureEditorAssets().then(function () {
            destroyPastoralEditor();
            var familyId = Number($card.data('family-id')) || Number($('.birthday-pastoral-care[data-person-id="' + entityId + '"]').first().data('family-id')) || 0;
            var canApplyToWholeFamily = entityType === 'person' && familyId > 0;

            var dialog = bootbox.dialog({
                title: '<i class="fas fa-hands-helping text-primary mr-2"></i>' + i18next.t('Pastoral Care Note Creation', {ns: 'BirthdayAnniversaryDashboard'}) + ' - ' + entityName,
                message: buildPastoralBootboxContent(entityType, typeDesc, visible, canApplyToWholeFamily),
                size: 'large',
                buttons: [
                    {
                        label: '<i class="fas fa-times"></i> ' + i18next.t('Close', {ns: 'BirthdayAnniversaryDashboard'}),
                        className: 'btn btn-outline-secondary'
                    },
                    {
                        label: '<i class="fas fa-save"></i> ' + i18next.t('Save', {ns: 'BirthdayAnniversaryDashboard'}),
                        className: 'btn btn-primary',
                        callback: function () {
                            var visibilityStatus = $('input[name="visibilityStatus"]:checked').val();
                            var noteText = CKEDITOR.instances.NoteText.getData();
                            var includeFamMembers = entityType === 'family' ? $('#includeFamilyMembers').is(':checked') : false;
                            var applyToWholeFamily = entityType === 'person' && $('#birthdayApplyToWholeFamily').is(':checked');

                            var payload = {
                                typeID: typeId,
                                currentPastorId: getCurrentPastorId(),
                                typeDesc: typeDesc,
                                visibilityStatus: visibilityStatus,
                                noteText: noteText
                            };

                            if (entityType === 'family') {
                                payload.familyID = entityId;
                                payload.includeFamMembers = includeFamMembers;
                                submitPastoralFollowUp({
                                    path: 'pastoralcare/family/add',
                                    payload: payload,
                                    scope: 'family',
                                    familyId: entityId,
                                    $card: $card
                                });
                            } else {
                                payload.personID = entityId;
                                if (applyToWholeFamily && familyId > 0) {
                                    submitPastoralFollowUp({
                                        path: 'pastoralcare/family/add',
                                        payload: {
                                            typeID: typeId,
                                            familyID: familyId,
                                            currentPastorId: getCurrentPastorId(),
                                            typeDesc: typeDesc,
                                            visibilityStatus: visibilityStatus,
                                            noteText: noteText,
                                            includeFamMembers: true
                                        },
                                        scope: 'family',
                                        familyId: familyId,
                                        $card: $card
                                    });
                                } else {
                                    submitPastoralFollowUp({
                                        path: 'pastoralcare/person/add',
                                        payload: payload,
                                        scope: 'person',
                                        $card: $card
                                    });
                                }
                            }
                        }
                    }
                ],
                show: false
            });

            $(document).on('focusin.birthdayPastoral', function (event) {
                event.stopImmediatePropagation();
            });

            dialog.on('shown.bs.modal', function () {
                var theme = 'n1theme,/skin/js/ckeditor/themes/n1theme/';
                if (window.CRM.bDarkMode) {
                    theme = 'moono-dark,/skin/js/ckeditor/themes/moono-dark/';
                }

                window.CRM.editor = CKEDITOR.replace('NoteText', {
                    customConfig: window.CRM.root + '/skin/js/ckeditor/configs/calendar_event_editor_config.js',
                    language: window.CRM.lang,
                    width: '100%',
                    skin: theme
                });

                add_ckeditor_buttons(window.CRM.editor);
            });

            dialog.on('hidden.bs.modal', function () {
                destroyPastoralEditor();
                $(document).off('focusin.birthdayPastoral');
            });

            dialog.modal('show');
        }).catch(function () {
            if (window.CRM.DisplayAlert) {
                window.CRM.DisplayAlert(i18next.t('Error', {ns: 'BirthdayAnniversaryDashboard'}), i18next.t('Unable to load the pastoral follow-up editor.', {ns: 'BirthdayAnniversaryDashboard'}));
            }
        });
    }

    $(document).on('click', '.birthday-pastoral-care', function (event) {
        event.preventDefault();
        event.stopPropagation();

        var $trigger = $(this);
        var $dropdownMenu = $trigger.closest('.dropdown-menu');
        var $dropdownToggle = $dropdownMenu.prevAll('.dropdown-toggle').first();
        var entityType = $trigger.data('entity-type') || 'person';
        var entityId = entityType === 'family' ? $trigger.data('family-id') : $trigger.data('person-id');
        var entityName = $trigger.data('person-name');
        var typeId = $trigger.data('typeid');
        var typeDesc = $trigger.data('typedesc');
        var visible = Number($trigger.data('visible')) === 1;
        var $card = $trigger.closest('.birthday-person-card, .birthday-family-card');

        $dropdownMenu.removeClass('show');
        $dropdownToggle.attr('aria-expanded', 'false');
        $trigger.closest('.btn-group').removeClass('show');

        openPastoralNoteDialog(entityType, entityId, entityName, typeId, typeDesc, visible, $card);
    });

    $(document).on('click', '.birthday-toggle-activation', function (event) {
        event.preventDefault();
        event.stopPropagation();

        var $trigger = $(this);
        var entityType = $trigger.data('entity-type');
        var entityId = entityType === 'family' ? Number($trigger.data('family-id')) : Number($trigger.data('person-id'));

        promptActivationToggle({
            entityType: entityType,
            entityId: entityId,
            familyId: Number($trigger.data('family-id')) || Number($trigger.closest('.birthday-person-card, .birthday-family-card').data('family-id')) || 0,
            entityName: $trigger.data('entity-name'),
            currentActive: Number($trigger.data('current-active')) === 1
        });
    });
});