function doBackup(isRemote)
{
    var endpointURL = "";
    if(isRemote)
    {
        endpointURL = window.CRM.root +'/api/database/backupRemote';
    }
    else
    {
        endpointURL = window.CRM.root +'/api/database/backup';
    }
    var errorflag =0;
    if ($("input[name=encryptBackup]").is(':checked'))
    {
        if ($('input[name=pw1]').val() =="")
        {
            $("#passworderror").html(i18next.t("You must enter a password"));
            errorflag=1;
        }
        if ($('input[name=pw1]').val() != $('input[name=pw2]').val())
        {
            $("#passworderror").html(i18next.t("Passwords must match"));
            errorflag=1;
        }
    }
    if (!errorflag)
    {
        $("#passworderror").html(" ");
        // get the form data
        // there are many ways to get this data using jQuery (you can use the class or id also)
        var formData = {
            'iRemote'                   : isRemote,
            'iArchiveType'              : $('input[name=archiveType]:checked').val(),
            'bEncryptBackup'            : $("input[name=encryptBackup]").is(':checked'),
            'password'                  : $('input[name=pw1]').val()
        };
        $("#backupstatus").css("color","orange");
        $("#backupstatus").html(i18next.t("Backup Running, Please wait."));
        console.log(formData);

        //process the form
        $.ajax({
            type        : 'POST', // define the type of HTTP verb we want to use (POST for our form)
            url         : endpointURL, // the url where we want to POST
            data        : JSON.stringify(formData), // our data object
            dataType    : 'json', // what type of data do we expect back from the server
            encode      : true,
            contentType: "application/json; charset=utf-8"
        })
            .done(function(data) {
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
                    $("#backupstatus").css("color","red");
                    $("#backupstatus").html("Backup Error.");
                }
            }).fail(function(data)  {
            $("#backupstatus").css("color","red");
            $("#backupstatus").html("Backup Error.");
        });
    }
}

$('#doBackup').click(function(event) {
    event.preventDefault();
    doBackup (0);
});

$('#doRemoteBackup').click(function(event) {
    event.preventDefault();
    doBackup(1);
});

function downloadbutton(filename) {
    window.location = window.CRM.root +"/api/database/download/"+filename;
    $("#backupstatus").css("color","green");
    $("#backupstatus").html(i18next.t("Backup Downloaded, Copy on server removed"));
    $("#downloadbutton").attr("disabled","true");
}
