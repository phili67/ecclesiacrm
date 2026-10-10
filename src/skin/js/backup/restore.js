window.CRM.restoreTimer = null;

const restoreFileLabel = document.querySelector('.custom-file-label[for="restoreFile"]');
const defaultRestoreFileLabel = restoreFileLabel.textContent;
const restoreSubmitButton = document.getElementById('restoreSubmitButton');

window.CRM.ElementListener('#restoreFile', 'change', function (event) {
    const file = event.target.files[0];
    restoreFileLabel.textContent = file ? file.name : defaultRestoreFileLabel;
    restoreSubmitButton.disabled = !file;
});

const restoreSteps = [
    'uploading_file',
    'extracting_archive',
    'importing_database',
    'restoring_images',
    'restoring_private_webdav',
    'restoring_public_webdav',
    'restoring_webdav_shares',
    'upgrading_database',
];

const getRestoreWorkflow = (fileName) => {
    const normalizedFileName = String(fileName || '').toLowerCase().replace(/\.gpg$/, '');
    if (normalizedFileName.endsWith('.tar.gz')) {
        return restoreSteps;
    }
    if (normalizedFileName.endsWith('.zip')) {
        return ['uploading_file', 'extracting_archive', 'importing_database', 'upgrading_database'];
    }
    return ['uploading_file', 'importing_database', 'upgrading_database'];
};

const renderRestoreProgress = (data) => {
    const container = document.getElementById('restoreProgress');
    const progress = data.Restore_Result_Datas || {};
    if (!progress.status && !data.Restore_In_Progress && !data.RestoreDone) {
        container.classList.add('d-none');
        return '';
    }

    const fileInput = document.getElementById('restoreFile');
    const fileName = progress.fileName || fileInput.files[0]?.name || '';
    const workflow = getRestoreWorkflow(fileName);
    let currentStage = progress.status === 'failed' ? progress.failedStage : progress.status;
    if (currentStage === 'preparing') {
        currentStage = workflow[1] || workflow[0];
    }
    const currentIndex = workflow.indexOf(currentStage);
    const isComplete = data.success === true;
    const uploadPercent = document.getElementById('restoreUploadPercent');

    if (progress.status !== 'uploading_file') {
        uploadPercent.textContent = isComplete || currentIndex > 0 ? '100%' : '';
    }

    container.classList.remove('d-none');
    document.querySelectorAll('[data-restore-step]').forEach((item) => {
        const step = item.dataset.restoreStep;
        const index = workflow.indexOf(step);
        item.classList.toggle('d-none', index === -1);
        if (index === -1) {
            return;
        }

        const icon = item.querySelector('i');
        item.classList.remove('list-group-item-success', 'list-group-item-warning', 'list-group-item-danger');
        item.removeAttribute('aria-current');

        if (isComplete || (currentIndex >= 0 && index < currentIndex)) {
            item.classList.add('list-group-item-success');
            icon.className = 'fas fa-check-circle text-success mr-2';
        } else if (index === currentIndex && progress.status === 'failed') {
            item.classList.add('list-group-item-danger');
            icon.className = 'fas fa-times-circle text-danger mr-2';
            item.setAttribute('aria-current', 'step');
        } else if (index === currentIndex) {
            item.classList.add('list-group-item-warning');
            icon.className = 'fas fa-spinner fa-spin text-warning mr-2';
            item.setAttribute('aria-current', 'step');
        } else {
            icon.className = 'fas fa-circle text-muted mr-2';
        }
    });

    if (currentIndex >= 0) {
        const activeStep = Array.from(document.querySelectorAll('[data-restore-step]'))
            .find((item) => item.dataset.restoreStep === currentStage)
            ?.querySelector('.restore-step-label');
        return activeStep ? activeStep.textContent.trim() : '';
    }
    return '';
};

const showRestoreResult = (data) => {
    const activeStep = renderRestoreProgress(data);
    if (!data.RestoreDone) {
        if (data.MaintenanceMode) {
            $('#restorestatus').css('color', 'orange');
            $('#restorestatus').text(activeStep || (i18next.t('Maintenance mode') + ': ' + i18next.t('Restore Running, Please wait.')));
        }
        return;
    }

    if (window.CRM.restoreTimer !== null) {
        clearInterval(window.CRM.restoreTimer);
        window.CRM.restoreTimer = null;
    }

    const result = data.Restore_Result_Datas || {};
    if (data.success === true) {
        $('#restorestatus').css('color', 'green');
        if (Array.isArray(result.Messages) && result.Messages.length > 0) {
            result.Messages.forEach((message) => {
                const alert = document.createElement('div');
                alert.className = 'alert alert-danger';
                alert.textContent = message;
                document.getElementById('restoreMessages').appendChild(alert);
            });
        }

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

const resumeRestoreStatus = () => {
    fetch(window.CRM.root + '/api/database/restore/result', {
        method: 'GET',
        headers: {
            'Authorization': 'Bearer ' + window.CRM.jwtToken,
        },
    })
        .then((response) => response.json())
        .then((data) => {
            if (data.RestoreDone || data.Restore_In_Progress || data.MaintenanceMode) {
                showRestoreResult(data);
            }
            if (!data.RestoreDone && (data.Restore_In_Progress || data.MaintenanceMode) && window.CRM.restoreTimer === null) {
                window.CRM.restoreTimer = setInterval(resultFunction, 1000 * 10);
            }
        })
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

    renderRestoreProgress({
        RestoreDone: false,
        Restore_In_Progress: true,
        Restore_Result_Datas: {
            status: 'uploading_file',
            fileName: file.name,
        },
    });
    document.getElementById('restoreUploadPercent').textContent = '0%';
    $('#restorestatus').css('color', 'orange').text(i18next.t('Uploading backup file'));

    const request = new XMLHttpRequest();
    request.open('POST', window.CRM.root + '/api/database/restore');
    request.setRequestHeader('Authorization', 'Bearer ' + window.CRM.jwtToken);
    request.upload.addEventListener('progress', (uploadEvent) => {
        if (!uploadEvent.lengthComputable) {
            return;
        }
        const percent = Math.round((uploadEvent.loaded / uploadEvent.total) * 100);
        document.getElementById('restoreUploadPercent').textContent = percent + '%';
    });
    request.upload.addEventListener('load', () => {
        document.getElementById('restoreUploadPercent').textContent = '100%';
    });
    request.addEventListener('load', () => {
        let data;
        try {
            data = JSON.parse(request.responseText);
        } catch (error) {
            $('#restorestatus').css('color', 'red').text(i18next.t('Restore Error.'));
            console.log(error.name + ' ' + error.message);
            return;
        }

        if (request.status < 200 || request.status >= 300 || data.result !== true) {
            renderRestoreProgress({
                RestoreDone: true,
                success: false,
                Restore_Result_Datas: {
                    status: 'failed',
                    failedStage: 'uploading_file',
                    fileName: file.name,
                },
            });
            $('#restorestatus').css('color', 'red').text(data.message || i18next.t('Restore Error.'));
            return;
        }

        checkIfFinished();
    });
    request.addEventListener('error', () => {
        renderRestoreProgress({
            RestoreDone: true,
            success: false,
            Restore_Result_Datas: {
                status: 'failed',
                failedStage: 'uploading_file',
                fileName: file.name,
            },
        });
        $('#restorestatus').css('color', 'red').text(i18next.t('Restore Error.'));
    });
    request.send(formData);

    return false;
});

resumeRestoreStatus();
