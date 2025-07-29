document.addEventListener("DOMContentLoaded", function() {

    window.CRM.bakupTimer = null;

    const loadFunctions = () => {
        window.CRM.ElementListener('#downloadbutton', 'click', function(event) {
            let filename = event.currentTarget.dataset.filename;

            window.CRM.css("#backupstatus", 'color: green');  
            window.CRM.html("#backupstatus", i18next.t("Backup Downloaded, Copy on server removed"));   
            window.CRM.disabled("#downloadbutton", true);            
            
            window.location = window.CRM.root + "/api/database/download/" + filename;

            // after we reload page
            setTimeout(function() {
                window.location.reload();    
            }, 10000);                    
        });
    }  
    
    if (window.CRM.BackupDone) {
        loadFunctions();
    }

    const resultFunction = () => {
        window.CRM.APIRequest({
            method: 'GET',
            path: 'database/backup/result',
        }, function (data) {
            if (data.BackupDone) {
                window.CRM.closeDialogLoadingFunction();   

                let button = document.createElement('button');
                button.setAttribute('class','btn btn-primary');
                button.setAttribute('data-filename',data.Backup_Result_Datas.filename);
                button.setAttribute('role', "button");
                button.setAttribute('id','downloadbutton');

                let icon = document.createElement('i');
                icon.setAttribute('class','fas fa-download');

                button.appendChild(icon);

                let span = document.createElement('span');
                span.innerText = ' ' + data.Backup_Result_Datas.filename;

                button.appendChild(span);                         
                                                
                // add button to the page
                let resultFiles = document.getElementById('resultFiles');
                resultFiles.appendChild(button);
                
                window.CRM.css("#backupstatus", 'color: green');            
                
                if (window.CRM.bakupTimer !== null) {
                    clearInterval(window.CRM.bakupTimer);
                }

                loadFunctions();
            } else {
                window.CRM.css("#backupstatus", 'color: orange');                  
            }
            window.CRM.html("#backupstatus", data.message);  
        });
    }

    const checkIfFinished = () => {
        window.CRM.bakupTimer = setInterval(function () {
            // Invoke function every 10 minutes
            resultFunction();
        }, 1000 * 10);
    }

    const doBackup = (isRemote) => {
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
                window.CRM.html("#passworderror", i18next.t("You must enter a password")); 
                errorflag = 1;
            }
            if ($('input[name=pw1]').val() != $('input[name=pw2]').val()) {
                window.CRM.html("#passworderror", i18next.t("Passwords must match"));                 
                errorflag = 1;
            }
        }
        if (!errorflag) {
            window.CRM.html("#passworderror", "");                 
            // get the form data
            // there are many ways to get this data using jQuery (you can use the class or id also)
            var formData = {
                'iRemote': isRemote,
                'iArchiveType': $('input[name=archiveType]:checked').val(),
                'bEncryptBackup': $("input[name=encryptBackup]").is(':checked'),
                'password': $('input[name=pw1]').val()
            };

            window.CRM.css("#backupstatus", 'color: orange'); 
            window.CRM.html("#backupstatus", i18next.t("Backup Running, Please wait.")); 
            
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
                            window.CRM.css("#backupstatus", 'color: orange'); 
                            if (isRemote) {
                                window.CRM.html("#backupstatus", i18next.t("Background backup in progress to remote server"));                                 
                            } else {
                                window.CRM.html("#backupstatus", i18next.t("Background backup in progress"));                                 
                            }

                            window.CRM.disabled("#doBackup", true);      
                            window.CRM.disabled("#doRemoteBackup", true);      
                        } else {
                            window.CRM.css("#backupstatus", 'color: red'); 
                            window.CRM.html("#backupstatus", i18next.t("Backup Error."));                                                 
                        }

                        checkIfFinished();
                    }).catch(error => {
                        // enter your logic for when there is an error (ex. error toast)
                        window.CRM.closeDialogLoadingFunction();

                        window.CRM.css("#backupstatus", 'color: red'); 
                        window.CRM.html("#backupstatus", i18next.t("Backup Error."));                                                 

                        console.log(error.name + " " + error.message);
                    });
            });
        }
    }

    if (window.CRM.isInProgress) {
        window.CRM.dialogLoadingFunction(i18next.t("Backup in progress, don't close the window !"), function () {
            checkIfFinished();
        });
    }

    window.CRM.ElementListener('#doBackup', 'click', function(event) {
        doBackup(0);
    });

    window.CRM.ElementListener('#doRemoteBackup', 'click', function(event) {
        doBackup(1);
    });
});