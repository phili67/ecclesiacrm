document.addEventListener('DOMContentLoaded', () => {
    const settingsPage = document.getElementById('MailChimpSettingsPage');
    const settingsForm = document.getElementById('MailChimpSettingsForm');
    const apiKeyInput = document.getElementById('apiKey');
    const toggleApiKeyButton = document.getElementById('ToggleApiKey');
    const saveState = document.getElementById('MailChimpSaveState');
    const saveButton = document.getElementById('SaveSettings');
    const autoResizeFields = Array.from(document.querySelectorAll('[data-autoresize="true"]'));

    if (!settingsPage || !settingsForm) {
        return;
    }

    let isDirty = false;

    const getPayload = () => ({
        apiKey: document.getElementById('apiKey')?.value ?? '',
        requestTimeOut: document.getElementById('requestTimeOut')?.value ?? 30,
        externalCssFont: document.getElementById('externalCssFont')?.value ?? '',
        bWithAddressPhone: document.getElementById('bWithAddressPhone')?.checked ? 1 : 0,
        sMailChimpEmailSender: document.getElementById('sMailChimpEmailSender')?.value ?? '',
        sMailChimpExtraFont: document.getElementById('sMailChimpExtraFont')?.value ?? ''
    });

    const resizeTextarea = (field) => {
        field.style.height = 'auto';
        field.style.height = `${field.scrollHeight}px`;
    };

    const updateDirtyState = (dirty) => {
        isDirty = dirty;

        if (!saveState || !saveButton) {
            return;
        }

        saveState.dataset.dirty = dirty ? 'true' : 'false';
        saveState.innerHTML = dirty
            ? '<i class="fas fa-exclamation-circle mr-1"></i>' + i18next.t('Unsaved changes are ready to be stored.', {ns: 'MailChimp'})
            : '<i class="fas fa-database mr-1"></i>' + i18next.t('No local changes yet.', {ns: 'MailChimp'});

        saveButton.classList.toggle('btn-warning', dirty);
        saveButton.classList.toggle('btn-dark', !dirty);
    };

    const showSavedState = () => {
        if (!saveState || !saveButton) {
            return;
        }

        saveState.dataset.dirty = 'false';
        saveState.innerHTML = '<i class="fas fa-check-circle mr-1"></i>' + i18next.t('Settings saved.', {ns: 'MailChimp'});
        saveButton.classList.remove('btn-warning');
        saveButton.classList.add('btn-dark');
        isDirty = false;
    };

    autoResizeFields.forEach((field) => {
        resizeTextarea(field);
        field.addEventListener('input', () => resizeTextarea(field));
    });

    settingsForm.querySelectorAll('input, textarea, select').forEach((field) => {
        field.addEventListener('input', () => updateDirtyState(true));
        field.addEventListener('change', () => updateDirtyState(true));
    });

    if (toggleApiKeyButton && apiKeyInput) {
        toggleApiKeyButton.addEventListener('click', () => {
            const isPassword = apiKeyInput.getAttribute('type') === 'password';
            const icon = toggleApiKeyButton.querySelector('i');

            apiKeyInput.setAttribute('type', isPassword ? 'text' : 'password');

            if (icon) {
                icon.classList.toggle('fa-eye');
                icon.classList.toggle('fa-eye-slash');
            }
        });
    }

    settingsForm.addEventListener('submit', (event) => {
        event.preventDefault();

        window.CRM.dialogLoadingFunction('Save MailChimp settings...', function () {
            window.CRM.APIRequest({
                method: 'POST',
                path: 'mailchimp/settings',
                data: JSON.stringify(getPayload())
            }, function (data) {
                window.CRM.closeDialogLoadingFunction();

                if (data.success) {
                    showSavedState();
                    window.CRM.DisplayAlert('MailChimp', i18next.t('Saved', {ns: 'MailChimp'}));
                    return;
                }

                updateDirtyState(true);
                window.CRM.DisplayAlert('MailChimp', (data.error && data.error.detail) ? data.error.detail : i18next.t('Unable to save settings', {ns: 'MailChimp'}));
            });
        });
    });

    window.addEventListener('beforeunload', (event) => {
        if (!isDirty) {
            return;
        }

        event.preventDefault();
        event.returnValue = '';
    });
});