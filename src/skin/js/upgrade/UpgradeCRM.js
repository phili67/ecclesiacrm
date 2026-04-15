$(function () {
    const stepStatusLabels = {
        active: i18next.t('Current step'),
        complete: i18next.t('Completed'),
        locked: i18next.t('Locked')
    };

    const setStepState = (step, state) => {
        const card = $('[data-step-card="' + step + '"]');
        const timelineStep = $('[data-step="' + step + '"]');
        const statusLabel = $('[data-step-status="' + step + '"] span:last');

        card.removeClass('is-active is-complete is-locked').addClass('is-' + state);
        timelineStep.removeClass('is-active is-complete is-locked').addClass('is-' + state);
        statusLabel.text(stepStatusLabels[state]);
    };

    const activateStep = (step) => {
        const previousStep = step - 1;

        if (previousStep >= 1) {
            setStepState(previousStep, 'complete');
        }

        setStepState(step, 'active');
    };

    const initializeStepStates = () => {
        setStepState(1, 'active');
        setStepState(2, 'locked');
        setStepState(3, 'locked');
        setStepState(4, 'locked');
    };

    const startProgressWindow = () => {
        setStepState(1, 'active');
        $("#status1").html('<i class="fas fa-spin fa-spinner"></i>');
        window.CRM.html("#status-text", i18next.t("Backup in progress, don't close the window !"));
        window.CRM.css("#status-text", 'color: orange');

        window.CRM.dialogLoadingFunction(i18next.t("Backup in progress, don't close the window !"), function () {
            fetch(window.CRM.root + '/api/database/backup', {
                method: 'POST',
                headers: {
                    'Content-Type': "application/json; charset=utf-8",
                    'Authorization': 'Bearer ' + window.CRM.jwtToken,
                },
                body: JSON.stringify({
                    'iArchiveType': 3
                })
            }).then(res => res.json())
                .then(data => {
                    window.CRM.bakupTimer = setInterval(function () {
                        // Invoke function every 10 minutes
                        resultFunction();
                    }, 1000 * 10);
                }).catch(error => {
                    $("#backupStatus").css("color", "red");
                    $("#backupStatus").html(i18next.t('Backup Error.'));
                });
        });
    }

    const resultFunction = () => {
        window.CRM.APIRequest({
            method: 'GET',
            path: 'database/backup/result',
        }, function (data) {
            if (data.BackupDone) {
                backupDoneFunction(data.Backup_Result_Datas);
            } else {
                window.CRM.css("#status-text", 'color: orange');
                $("#status-text").html(i18next.t("Backup in progress, don't close the window !"));
            }
            window.CRM.html("#status-text", data.message);
        });
    }

    const backupDoneFunction = (Backup_Result_Datas) => {
        window.CRM.closeDialogLoadingFunction();
        setStepState(1, 'active');

        var downloadButton = '<button class="btn btn-primary" id="downloadbutton" role="button" data-file="' + Backup_Result_Datas.filename + '"><i class="fas fa-download"></i>  ' + Backup_Result_Datas.filename + "</button>";

        $("#backupStatus").css("color", "green");
        $("#status-text").html(i18next.t("Backup Complete, Ready for Download."));
        $("#resultFiles").html(downloadButton);
        $("#status1").html('<i class="fas fa-check" style="color:orange"></i>');
        $("#doBackup").attr("disabled", "true");  

        if (window.CRM.bakupTimer !== null) {
            clearInterval(window.CRM.bakupTimer);
        }
    }    

    initializeStepStates();

    if (window.CRM.isInProgress) {
        startProgressWindow();
    }

    if (window.CRM.BackupDone) {
        backupDoneFunction(window.CRM.BackupDatas);
    }

    $(document).on('click', '#downloadbutton', function () {
        let filename = $(this).data('file');
        
        window.location = window.CRM.root + "/api/database/download/" + filename;
        $("#backupStatus").css("color", "green");
        $("#backupStatus").html(i18next.t('Backup Downloaded, Copy on server removed'));
        $("#downloadbutton").attr("disabled", "true");
        $("#doBackup").attr("disabled", "true");
        $("#fetchPhase").show("slow");
        $("#backupPhase").slideUp();
        $("#status1").html('<i class="fas fa-check" style="color:green"></i>');
        activateStep(2);
    });

    $("#doBackup").on('click', function () {
        startProgressWindow();
    });

    $("#fetchUpdate").on('click', function () {
        $("#status2").html('<i class="fas fa-spin fa-spinner"></i>');

        fetch(window.CRM.root + '/api/systemupgrade/downloadlatestrelease', {
            method: 'GET',
            headers: {
                'Content-Type': "application/json; charset=utf-8",
                'Authorization': 'Bearer ' + window.CRM.jwtToken,
            }
        }).then(res => res.json())
            .then(data => {
                $("#status2").html('<i class="fas fa-check" style="color:green"></i>');
                window.CRM.updateFile = data;
                $("#updateFileName").text(data.fileName);
                $("#updateFullPath").text(data.fullPath);
                $("#releaseNotes").text(data.releaseNotes);
                $("#updateSHA1").text(data.sha1);
                $("#fetchPhase").slideUp();
                $("#updatePhase").show("slow");
                activateStep(3);
            });
    });

    $("#applyUpdate").on('click', function () {
        $("#status3").html('<i class="fas fa-spin fa-spinner"></i>');
        fetch(window.CRM.root + '/api/systemupgrade/doupgrade', {
            method: 'POST',
            headers: {
                'Content-Type': "application/json; charset=utf-8",
                'Authorization': 'Bearer ' + window.CRM.jwtToken,
            },
            body: JSON.stringify({
                fullPath: window.CRM.updateFile.fullPath,
                sha1: window.CRM.updateFile.sha1
            })
        }).then(res => res.json())
            .then(data => {
                $("#status3").html('<i class="fas fa-check" style="color:green"></i>');
                $("#updatePhase").slideUp();
                $("#finalPhase").show("slow");
                activateStep(4);
            });
    });
});