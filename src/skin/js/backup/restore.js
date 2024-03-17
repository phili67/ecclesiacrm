$('#restoredatabase').submit(function (event) {
    event.preventDefault();

    var formData = new FormData($(this)[0]);
    if (window.FileReader) { // if the browser supports FileReader, validate the flie locally before uploading.
        var file = document.getElementById('restoreFile').files[0];
        if (file.size > window.CRM.maxUploadSizeBytes) {
            window.CRM.DisplayErrorMessage("/api/database/restore", {message: i18next.t('The selected file exceeds this servers maximum upload size of') + " : " + window.CRM.maxUploadSize});
            return false;
        }
    }

    $("#restorestatus").css("color", "orange");
    $("#restorestatus").html(i18next.t('Restore Running, Please wait.'));
    $.ajax({
        url: window.CRM.root + '/api/database/restore',
        type: 'POST',
        data: formData,
        cache: false,
        contentType: false,
        enctype: 'multipart/form-data',
        processData: false,
        dataType: 'json'
    }).done(function (data) {
        if (data.Messages !== undefined && data.Messages.length > 0) {
            $.each(data.Messages, function (index, value) {
                var inhtml = '<h4><i class="icon fas fa-ban"></i> Alert!</h4>' + value;
                $("<div>").addClass("alert alert-danger").html(inhtml).appendTo("#restoreMessages");
            });
        }
        $("#restorestatus").css("color", "green");
        $("#restorestatus").html(i18next.t('Restore Complete'));
        $("#restoreNextStep").html('<a href="' + window.CRM.root + '/session/logout" class="btn btn-primary">'+i18next.t("Login to restored Database")+'</a>');
    }).fail(function () {
        $("#restorestatus").css("color", "red");
        $("#restorestatus").html(i18next.t('Restore Error.'));
    });
    return false;
});
