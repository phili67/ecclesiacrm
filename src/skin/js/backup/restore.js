window.CRM.ElementListener('#restoredatabase', 'submit', function (event) {
    event.preventDefault();

    const file = document.getElementById('restoreFile').files[0];
        
    if (window.FileReader) { // if the browser supports FileReader, validate the flie locally before uploading.
        if (file.size > window.CRM.maxUploadSizeBytes) {
            window.CRM.DisplayErrorMessage("/api/database/restore", {message: i18next.t('The selected file exceeds this servers maximum upload size of') + " : " + window.CRM.maxUploadSize});
            return false;
        }
    }

    $("#restorestatus").css("color", "orange");
    $("#restorestatus").html(i18next.t('Restore Running, Please wait.'));

    const formData = new FormData();
    formData.append('restoreFile', file);

    window.CRM.dialogLoadingFunction(i18next.t("Restore backup, don't close the window !"), function () {
        fetch(window.CRM.root + '/api/database/restore', {            
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + window.CRM.jwtToken,
            },
            body: formData, // our data object
        })
            .then(res => res.json())
            .then(data => {
                if (data.Messages !== undefined && data.Messages.length > 0) {
                    $.each(data.Messages, function (index, value) {
                        var inhtml = '<h4><i class="icon fas fa-ban"></i> Alert!</h4>' + value;
                        $("<div>").addClass("alert alert-danger").html(inhtml).appendTo("#restoreMessages");
                    });
                }
                $("#restorestatus").css("color", "green");
                $("#restorestatus").html(i18next.t('Restore Complete'));
                $("#restoreNextStep").html('<a href="' + window.CRM.root + '/session/logout" class="btn btn-primary">'+i18next.t("Login to restored Database")+'</a>');

                window.CRM.closeDialogLoadingFunction();
            })
            .catch(error => {
                $("#restorestatus").css("color", "red");
                $("#restorestatus").html(i18next.t('Restore Error.'));

                window.CRM.closeDialogLoadingFunction();
            });
    });

    return false;
});
