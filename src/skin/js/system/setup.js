window.CRM.dataBaseCheck = false;

const getElement = function (id) {
    return document.getElementById(id);
};

const getFirstElementById = function (id) {
    return document.querySelector('#' + id);
};

const getDatabasePayload = function () {
    const serverName = getElement('DB_SERVER_NAME');
    const dbName = getElement('DB_NAME');
    const dbPort = getElement('DB_SERVER_PORT');
    const user = getElement('DB_USER');
    const password = getElement('DB_PASSWORD');

    return {
        serverName: serverName ? serverName.value : '',
        dbName: dbName ? dbName.value : '',
        dbPort: dbPort ? dbPort.value : '',
        user: user ? user.value : '',
        password: password ? password.value : ''
    };
};

const setAlertState = function (isSuccess) {
    const warningContainer = getElement('databaseconnection-war');
    const alertBoxes = document.querySelectorAll('.alert-db');

    if (warningContainer) {
        if (isSuccess) {
            warningContainer.textContent = 'Connection to your database successfully done. Click the "Next" button finish your installation.';
        } else {
            warningContainer.innerHTML = 'Connection to your database failed. Click the link <a href="#" class="database-check-retry"><b>here</b></a> to re-check your connection.';
        }
    }

    alertBoxes.forEach(function (alertBox) {
        alertBox.classList.remove('alert-warning', 'alert-danger', 'alert-success');
        alertBox.classList.add(isSuccess ? 'alert-success' : 'alert-danger');
    });
};

const dataBaseCheck = function () {
    fetch(window.CRM.root + '/setup/checkDatabaseConnection', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json; charset=utf-8'
        },
        body: JSON.stringify(getDatabasePayload())
    })
        .then(function (response) {
            return response.json();
        })
        .then(function (data) {
            const isSuccess = data.status !== undefined && data.status === 'success';

            window.CRM.dataBaseCheck = isSuccess;
            setAlertState(isSuccess);
        })
        .catch(function () {
            window.CRM.dataBaseCheck = false;
            setAlertState(false);
        });
};

window.dataBaseCheck = dataBaseCheck;

window.CRM.renderPrerequisite = function (name, status) {
    const rowId = name.replace(/[^A-z0-9]/g, '');
    const tableBody = getElement('prerequisites');
    const existingRow = getElement(rowId);
    const row = document.createElement('tr');
    const nameCell = document.createElement('td');
    const statusCell = document.createElement('td');

    if (!tableBody) {
        return;
    }

    row.id = rowId;
    nameCell.textContent = name;

    if (status === 'pass') {
        statusCell.className = 'text-blue';
        statusCell.innerHTML = '&check;';
    } else if (status === 'pending') {
        statusCell.className = 'text-orange';
        statusCell.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    } else {
        statusCell.className = 'text-red';
        statusCell.innerHTML = '&#x2717;';
    }

    row.appendChild(nameCell);
    row.appendChild(statusCell);
    window.CRM.prerequisites[rowId] = status;

    if (existingRow) {
        existingRow.replaceWith(row);
    } else {
        tableBody.appendChild(row);
    }
};

window.CRM.checkIntegrity = function () {
    const prerequisitesWarning = getFirstElementById('prerequisites-war');

    window.CRM.renderPrerequisite('EcclesiaCRM File Integrity Check', 'pending');

    fetch(window.CRM.root + '/setup/SystemIntegrityCheck', {
        method: 'GET'
    })
        .then(function (response) {
            return response.json();
        })
        .then(function (data) {
            if (data.status === 'success') {
                window.CRM.renderPrerequisite('EcclesiaCRM File Integrity Check', 'pass');

                if (prerequisitesWarning) {
                    prerequisitesWarning.style.display = 'none';
                }

                window.CRM.prerequisitesStatus = true;
            } else {
                window.CRM.renderPrerequisite('EcclesiaCRM File Integrity Check', 'fail');
            }
        })
        .catch(function () {
            window.CRM.renderPrerequisite('EcclesiaCRM File Integrity Check', 'fail');
        });
};

window.CRM.checkPrerequisites = function () {
    fetch(window.CRM.root + '/setup/SystemPrerequisiteCheck', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json; charset=utf-8'
        }
    })
        .then(function (response) {
            return response.json();
        })
        .then(function (data) {
            Object.keys(data).forEach(function (key) {
                window.CRM.renderPrerequisite(key, data[key] ? 'pass' : 'fail');
            });
        })
        .catch(function (error) {
            console.log(error);
        });
};

document.addEventListener('DOMContentLoaded', function () {
    const setupForm = getElement('setup-form');
    const wizardElement = getElement('wizard');
    const skipCheck = getElement('skipCheck');
    const passwordInput = getElement('DB_PASSWORD');
    const confirmPasswordInput = getElement('DB_PASSWORD2');
    const wizardState = {
        currentIndex: 0,
        stepCount: 0,
        steps: []
    };

    const styleWizardButtons = function () {
        document.querySelectorAll('a[href="#next"]').forEach(function (button) {
            button.classList.remove('me-2', 'ms-2');
            button.classList.add('btn', 'btn-primary', 'btn-lg', 'rounded-pill', 'px-4', 'mr-2', 'd-inline-flex', 'align-items-center', 'justify-content-center');
            button.setAttribute('role', 'button');
            button.setAttribute('title', 'Go to the next step');
            button.setAttribute('aria-label', 'Go to the next step of the setup wizard');
            button.innerHTML = '<span>Next</span><i class="fa fa-arrow-right ml-2" aria-hidden="true"></i>';
        });

        document.querySelectorAll('a[href="#previous"]').forEach(function (button) {
            button.classList.remove('me-2', 'ms-2');
            button.classList.add('btn', 'btn-outline-secondary', 'btn-lg', 'rounded-pill', 'px-4', 'ml-2', 'd-inline-flex', 'align-items-center', 'justify-content-center');
            button.setAttribute('role', 'button');
            button.setAttribute('title', 'Go back to the previous step');
            button.setAttribute('aria-label', 'Go back to the previous step of the setup wizard');
            button.innerHTML = '<i class="fa fa-arrow-left mr-2" aria-hidden="true"></i><span>Previous</span>';
        });

        document.querySelectorAll('a[href="#finish"]').forEach(function (button) {
            button.classList.add('btn', 'btn-success', 'btn-lg', 'rounded-pill', 'px-4', 'd-inline-flex', 'align-items-center', 'justify-content-center');
            button.setAttribute('role', 'button');
            button.setAttribute('title', 'Finish setup and create the application configuration');
            button.setAttribute('aria-label', 'Finish setup and create the application configuration');
            button.innerHTML = '<i class="fa fa-check mr-2" aria-hidden="true"></i><span>Finish Setup</span>';
        });

        document.querySelectorAll('.wizard > .actions li.disabled a').forEach(function (button) {
            button.setAttribute('aria-disabled', 'true');
            button.setAttribute('tabindex', '-1');
        });

        document.querySelectorAll('.wizard > .actions li:not(.disabled) a').forEach(function (button) {
            button.setAttribute('aria-disabled', 'false');
            button.removeAttribute('tabindex');
        });
    };

    const setConfirmPasswordValidity = function () {
        if (!passwordInput || !confirmPasswordInput) {
            return;
        }

        if (confirmPasswordInput.value !== '' && confirmPasswordInput.value !== passwordInput.value) {
            confirmPasswordInput.setCustomValidity('Passwords do not match.');
        } else {
            confirmPasswordInput.setCustomValidity('');
        }
    };

    const getStepControls = function (stepBody) {
        return Array.from(stepBody.querySelectorAll('input, select, textarea')).filter(function (field) {
            return !field.disabled && field.type !== 'hidden';
        });
    };

    const findFirstInvalidField = function (stepIndex) {
        const step = wizardState.steps[stepIndex];

        setConfirmPasswordValidity();

        if (!step) {
            return null;
        }

        return getStepControls(step.body).find(function (field) {
            return !field.checkValidity();
        }) || null;
    };

    const validateStep = function (stepIndex) {
        const invalidField = findFirstInvalidField(stepIndex);

        if (!invalidField) {
            return true;
        }

        invalidField.reportValidity();
        return false;
    };

    const validateAllSteps = function () {
        let stepIndex;

        for (stepIndex = 0; stepIndex < wizardState.stepCount; stepIndex += 1) {
            const invalidField = findFirstInvalidField(stepIndex);

            if (invalidField) {
                const previousIndex = wizardState.currentIndex;

                wizardState.currentIndex = stepIndex;
                updateWizardUi(previousIndex);
                invalidField.reportValidity();
                return false;
            }
        }

        return true;
    };

    const createActionItem = function (href, label, hidden) {
        const listItem = document.createElement('li');
        const link = document.createElement('a');

        link.href = href;
        link.setAttribute('role', 'menuitem');
        link.textContent = label;

        if (hidden) {
            listItem.style.display = 'none';
        }

        listItem.appendChild(link);
        return listItem;
    };

    const buildWizard = function () {
        const rawChildren = Array.from(wizardElement.children);
        const steps = [];
        const stepsContainer = document.createElement('div');
        const stepsList = document.createElement('ul');
        const contentContainer = document.createElement('div');
        const actionsContainer = document.createElement('div');
        const actionsList = document.createElement('ul');

        stepsContainer.className = 'steps clearfix';
        stepsList.setAttribute('role', 'tablist');
        stepsContainer.appendChild(stepsList);

        contentContainer.className = 'content clearfix';

        actionsContainer.className = 'actions clearfix';
        actionsList.setAttribute('role', 'menu');
        actionsList.setAttribute('aria-label', 'Pagination');
        actionsContainer.appendChild(actionsList);

        rawChildren.forEach(function (child, index) {
            const body = rawChildren[index + 1];

            if (child.tagName !== 'H2' || !body) {
                return;
            }

            steps.push({
                title: child.textContent.trim(),
                header: child,
                body: body
            });
        });

        wizardState.steps = steps;
        wizardState.stepCount = steps.length;

        steps.forEach(function (step, index) {
            const panelId = 'setup-step-panel-' + index;
            const tabId = 'setup-step-tab-' + index;
            const stepItem = document.createElement('li');
            const stepLink = document.createElement('a');
            const number = document.createElement('span');

            number.className = 'number';
            number.textContent = (index + 1) + '.';

            stepItem.setAttribute('role', 'tab');
            stepLink.id = tabId;
            stepLink.href = '#' + panelId;
            stepLink.setAttribute('aria-controls', panelId);
            stepLink.setAttribute('aria-selected', 'false');
            stepLink.setAttribute('data-step-index', String(index));
            stepLink.appendChild(number);
            stepLink.appendChild(document.createTextNode(' ' + step.title));
            stepItem.appendChild(stepLink);

            step.body.id = panelId;
            step.body.setAttribute('role', 'tabpanel');
            step.body.setAttribute('aria-labelledby', tabId);
            step.body.setAttribute('aria-hidden', 'true');
            step.body.classList.add('body');
            step.body.style.display = 'none';

            step.header.remove();
            stepsList.appendChild(stepItem);
            contentContainer.appendChild(step.body);

            step.item = stepItem;
            step.link = stepLink;
        });

        actionsList.appendChild(createActionItem('#previous', 'Previous', false));
        actionsList.appendChild(createActionItem('#next', 'Next', false));
        actionsList.appendChild(createActionItem('#finish', 'Finish', true));

        if (actionsList.firstElementChild) {
            actionsList.firstElementChild.classList.add('disabled');
        }

        wizardElement.innerHTML = '';
        wizardElement.classList.add('wizard', 'clearfix');
        wizardElement.appendChild(stepsContainer);
        wizardElement.appendChild(contentContainer);
        wizardElement.appendChild(actionsContainer);
    };

    const setActionState = function (selector, enabled) {
        const action = wizardElement.querySelector(selector);
        const actionItem = action ? action.parentElement : null;

        if (!actionItem || !action) {
            return;
        }

        actionItem.classList.toggle('disabled', !enabled);
        action.setAttribute('aria-disabled', enabled ? 'false' : 'true');
    };

    const setActionVisibility = function (selector, visible) {
        const action = wizardElement.querySelector(selector);
        const actionItem = action ? action.parentElement : null;

        if (!actionItem) {
            return;
        }

        actionItem.style.display = visible ? '' : 'none';
    };

    function updateWizardUi(previousIndex) {
        wizardState.steps.forEach(function (step, index) {
            const isCurrent = index === wizardState.currentIndex;
            const isDone = index < wizardState.currentIndex;
            const isDisabled = index > wizardState.currentIndex;

            step.item.classList.toggle('current', isCurrent);
            step.item.classList.toggle('done', isDone);
            step.item.classList.toggle('disabled', isDisabled);

            step.link.setAttribute('aria-selected', isCurrent ? 'true' : 'false');
            step.link.setAttribute('aria-disabled', isDisabled ? 'true' : 'false');

            step.body.classList.toggle('current', isCurrent);
            step.body.setAttribute('aria-hidden', isCurrent ? 'false' : 'true');
            step.body.style.display = isCurrent ? '' : 'none';
        });

        setActionState('a[href="#previous"]', wizardState.currentIndex > 0);
        setActionState('a[href="#next"]', wizardState.currentIndex < wizardState.stepCount - 1);
        setActionVisibility('a[href="#next"]', wizardState.currentIndex < wizardState.stepCount - 1);
        setActionVisibility('a[href="#finish"]', wizardState.currentIndex === wizardState.stepCount - 1);

        wizardElement.dispatchEvent(new CustomEvent('stepChanged', {
            detail: {
                currentIndex: wizardState.currentIndex,
                previousIndex: previousIndex
            }
        }));
    }

    const canMoveToStep = function (targetIndex) {
        if (targetIndex < 0 || targetIndex >= wizardState.stepCount) {
            return false;
        }

        if (targetIndex <= wizardState.currentIndex) {
            return true;
        }

        if (targetIndex !== wizardState.currentIndex + 1) {
            return false;
        }

        if (wizardState.currentIndex === 3 && window.CRM.dataBaseCheck === false) {
            dataBaseCheck();
            return false;
        }

        if (wizardState.currentIndex === 0) {
            return window.CRM.prerequisitesStatus;
        }

        return validateStep(wizardState.currentIndex);
    };

    const goToStep = function (targetIndex) {
        const previousIndex = wizardState.currentIndex;

        if (!canMoveToStep(targetIndex)) {
            return false;
        }

        wizardState.currentIndex = targetIndex;
        updateWizardUi(previousIndex);
        return true;
    };

    const finishSetup = function () {
        const formData = new FormData(setupForm);
        const payload = {};

        formData.forEach(function (value, key) {
            payload[key] = value;
        });

        fetch(window.CRM.root + '/setup/', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        })
            .then(function (response) {
                return response.json();
            })
            .then(function () {
                location.replace(window.CRM.root + '/');
            })
            .catch(function (error) {
                console.log(error);
            });
    };

    if (!setupForm || !wizardElement) {
        return;
    }

    buildWizard();
    updateWizardUi(0);
    setTimeout(styleWizardButtons, 300);

    wizardElement.addEventListener('stepChanged', function () {
        setTimeout(styleWizardButtons, 100);
    });

    wizardElement.addEventListener('click', function (event) {
        const stepLink = event.target.closest('.steps a');
        const actionLink = event.target.closest('.actions a');

        if (stepLink && wizardElement.contains(stepLink)) {
            const targetIndex = Number.parseInt(stepLink.getAttribute('data-step-index'), 10);

            event.preventDefault();

            if (!Number.isNaN(targetIndex)) {
                goToStep(targetIndex);
            }

            return;
        }

        if (!actionLink || !wizardElement.contains(actionLink)) {
            return;
        }

        event.preventDefault();

        if (actionLink.parentElement && actionLink.parentElement.classList.contains('disabled')) {
            return;
        }

        if (actionLink.getAttribute('href') === '#previous') {
            goToStep(wizardState.currentIndex - 1);
        } else if (actionLink.getAttribute('href') === '#next') {
            goToStep(wizardState.currentIndex + 1);
        } else if (actionLink.getAttribute('href') === '#finish' && validateAllSteps()) {
            finishSetup();
        }
    });

    if (passwordInput) {
        passwordInput.addEventListener('input', setConfirmPasswordValidity);
    }

    if (confirmPasswordInput) {
        confirmPasswordInput.addEventListener('input', setConfirmPasswordValidity);
    }

    window.CRM.checkIntegrity();
    window.CRM.checkPrerequisites();

    if (skipCheck) {
        skipCheck.addEventListener('click', function (event) {
            const prerequisitesWarning = getFirstElementById('prerequisites-war');

            event.preventDefault();

            if (prerequisitesWarning) {
                prerequisitesWarning.style.display = 'none';
            }

            window.CRM.prerequisitesStatus = true;
        });
    }

    document.addEventListener('click', function (event) {
        const retryTrigger = event.target.closest('.database-check-retry, #dataBaseCheck');

        if (!retryTrigger) {
            return;
        }

        event.preventDefault();
        dataBaseCheck();
    });
});
