window.CRM.restoreTimer = null;

const showRestoreResult = (data) => {
    if (!data.RestoreDone) {
        if (data.MaintenanceMode) {
            $('#restorestatus').css('color', 'orange');
            $('#restorestatus').html(i18next.t('Maintenance mode') + ': ' + i18next.t('Restore Running, Please wait.'));
        }
        return;
    }

    if (window.CRM.restoreTimer !== null) {
        clearInterval(window.CRM.restoreTimer);
        window.CRM.restoreTimer = null;
    }

    window.CRM.closeDialogLoadingFunction();

    const result = data.Restore_Result_Datas || {};
    if (data.success === true) {
        if (Array.isArray(result.Messages) && result.Messages.length > 0) {
            result.Messages.forEach((message) => {
                const alert = document.createElement('div');
                alert.className = 'alert alert-danger';
                alert.textContent = message;
                document.getElementById('restoreMessages').appendChild(alert);
            });
        }

        $('#restorestatus').css('color', 'green');
        $('#restorestatus').html(i18next.t('Restore Complete'));
        $('#restoreNextStep').html('<a href="' + window.CRM.root + '/session/logout" class="btn btn-primary">' + i18next.t('Login to restored Database') + '</a>');
    } else {
        $('#restorestatus').css('color', 'red');
        $('#restorestatus').html(data.message || i18next.t('Restore Error.'));
    }
};

const resultFunction = () => {
    fetch(window.CRM.root + '/api/database/restore/result', {
        method: 'GET',
        headers: {
            'Authorization': 'Bearer ' + window.CRM.jwtToken,
        },
    })
        .then((response) => response.json())
        .then(showRestoreResult)
        .catch((error) => console.log(error.name + ' ' + error.message));
};

const checkIfFinished = () => {
    if (window.CRM.restoreTimer === null) {
        window.CRM.restoreTimer = setInterval(resultFunction, 1000 * 10);
    }
    resultFunction();
};

window.CRM.ElementListener('#restoredatabase', 'submit', function (event) {
    event.preventDefault();

    const fileInput = document.getElementById('restoreFile');
    const file = fileInput.files[0];
    if (!file) {
        window.CRM.DisplayErrorMessage('/api/database/restore', { message: i18next.t('Please select a backup file.') });
        return false;
    }

    if (window.FileReader && file.size > window.CRM.maxUploadSizeBytes) {
        window.CRM.DisplayErrorMessage('/api/database/restore', { message: i18next.t('The selected file exceeds this servers maximum upload size of') + ' : ' + window.CRM.maxUploadSize });
        return false;
    }

    $('#restorestatus').css('color', 'orange');
    $('#restorestatus').html(i18next.t('Maintenance mode') + ': ' + i18next.t('Restore Running, Please wait.'));

    const formData = new FormData();
    formData.append('restoreFile', file);
    const passwordInput = document.getElementById('restorePassword');
    formData.append('restorePassword', passwordInput ? passwordInput.value : '');

    window.CRM.dialogLoadingFunction(i18next.t("Restore backup, don't close the window !"), function () {
        fetch(window.CRM.root + '/api/database/restore', {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + window.CRM.jwtToken,
            },
            body: formData,
        }).then((response) => response.json())
            .then((data) => {
                if (data.result !== true) {
                    throw new Error(data.message || i18next.t('Restore Error.'));
                }

                checkIfFinished();
            }).catch((error) => {
                window.CRM.closeDialogLoadingFunction();

                $('#restorestatus').css('color', 'red');
                $('#restorestatus').html(i18next.t('Restore Error.'));

                console.log(error.name + ' ' + error.message);
            });
    });

    return false;
});
