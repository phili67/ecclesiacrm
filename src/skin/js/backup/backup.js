function doBackup(isRemote) {
    var endpointURL = "";
    if (isRemote) {
        endpointURL = window.CRM.root + '/api/database/backupRemote';
    }
    else {
        endpointURL = window.CRM.root + '/api/database/backup';
    }
    var errorflag = 0;
    if ($("input[name=encryptBackup]").is(':checked')) {
        if ($('input[name=pw1]').val() == "") {
            $("#passworderror").html(i18next.t("You must enter a password"));
            errorflag = 1;
        }
        if ($('input[name=pw1]').val() != $('input[name=pw2]').val()) {
            $("#passworderror").html(i18next.t("Passwords must match"));
            errorflag = 1;
        }
    }
    if (!errorflag) {
        $("#passworderror").html(" ");
        // get the form data
        // there are many ways to get this data using jQuery (you can use the class or id also)
        var formData = {
            'iRemote': isRemote,
            'iArchiveType': $('input[name=archiveType]:checked').val(),
            'bEncryptBackup': $("input[name=encryptBackup]").is(':checked'),
            'password': $('input[name=pw1]').val()
        };

        $("#backupstatus").css("color", "orange");
        $("#backupstatus").html(i18next.t("Backup Running, Please wait."));

        // abort in 1 second
        window.CRM.dialogLoadingFunction(i18next.t("Backup in progress, don't close the window !"), function () {
            fetch(endpointURL, {
                method: 'POST',
                headers: {
                    'Content-Type': "application/json; charset=utf-8",
                    'Authorization': 'Bearer ' + window.CRM.jwtToken,
                },
                body: JSON.stringify(formData), // our data object
            }).then(res => res.json())
                .then(data => {
                    console.log(data);
                    if (data.result === true) {
                        var downloadButton = "<button class=\"btn btn-primary\" id=\"downloadbutton\" role=\"button\" onclick=\"javascript:downloadbutton('" + data.filename + "')\"><i class='fas fa-download'></i>  " + data.filename + "</button>";
                        $("#backupstatus").css("color", "green");
                        if (isRemote) {
                            $("#backupstatus").html(i18next.t("Backup Generated and copied to remote server"));
                        } else {
                            $("#backupstatus").html(i18next.t("Backup Complete, Ready for Download."));
                            $("#resultFiles").html(downloadButton);
                        }
                    } else {
                        $("#backupstatus").css("color", "red");
                        $("#backupstatus").html("Backup Error.");
                    }

                    window.CRM.closeDialogLoadingFunction();
                }).catch(error => {
                    // enter your logic for when there is an error (ex. error toast)
                    window.CRM.closeDialogLoadingFunction();

                    $("#backupstatus").css("color", "red");
                    $("#backupstatus").html("Backup Error.");

                    console.log(error.name + " " + error.message);
                });
        });
    }
}

$('#doBackup').on('click', function (event) {
    event.preventDefault();
    doBackup(0);
});

$('#doRemoteBackup').on('click', function (event) {
    event.preventDefault();
    doBackup(1);
});

function downloadbutton(filename) {
    window.location = window.CRM.root + "/api/database/download/" + filename;
    $("#backupstatus").css("color", "green");
    $("#backupstatus").html(i18next.t("Backup Downloaded, Copy on server removed"));
    $("#downloadbutton").attr("disabled", "true");
}
