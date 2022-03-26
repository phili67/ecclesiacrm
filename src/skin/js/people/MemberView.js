function contentExists(contentUrl, callback) {
    $.ajax({
        method :"HEAD",
        url: contentUrl,
        processData: false,
        global:false,
        success: function(data, textStatus, jqXHR){
            callback(true, data, textStatus, jqXHR);
        },
        error: function(jqXHR, textStatus, errorThrown) {
            callback(false, jqXHR, textStatus, errorThrown);
        }
    });
}

$('.delete-person').click(function (event) {
    event.preventDefault();
    var thisLink = $(this);
    bootbox.confirm({
        title:i18next.t("Delete this person?"),
        message: i18next.t("Do you want to delete this person? This cannot be undone.") + " <b>" + thisLink.data('person_name')+'</b>',
        buttons: {
            cancel: {
                className: 'btn-primary',
                label: '<i class="fas fa-times"></i>' + i18next.t("Cancel")
            },
            confirm: {
                className: 'btn-danger',
                label: '<i class="far fa-trash-alt"></i>' + i18next.t("Delete")
            }
        },
        callback: function (result) {
            if(result) {
                $.ajax({
                    type: 'DELETE',
                    url: window.CRM.root + '/api/persons/' + thisLink.data('person_id'),
                    dataType: 'json',
                    success: function (data, status, xmlHttpReq) {
                        if (thisLink.data('view') == 'family') {
                            location.reload();
                        } else {
                            location.replace(window.CRM.root + "/");
                        }
                    }
                });
            }
        }
    });
});


$('.saveNoteAsWordFile').click(function (event) {
    var noteId = $(this).data("id");
    bootbox.confirm({
        title:i18next.t("Save your note"),
        message: i18next.t("Do you want to save your note as a Word File in your EDrive?"),
        buttons: {
            cancel: {
                className: 'btn-default',
                label: '<i class="fas fa-times"></i> ' + i18next.t("Cancel")
            },
            confirm: {
                className: 'btn-primary',
                label: '<i class="far fa-save"></i> ' + i18next.t("Save")
            }
        },
        callback: function (result) {
            if(result) {
                window.CRM.APIRequest({
                  method: 'POST',
                  path: 'persons/saveNoteAsWordFile',
                  data: JSON.stringify({"personId":window.CRM.iPersonId,"noteId":noteId})
                },function(data) {
                  // reload toolbar
                  if (window.CRM.dataEDriveTable != undefined) {
                     window.CRM.reloadEDriveTable();
                     alert (i18next.t('Your note is saved in your EDrive')+' : '+data.title+'.docx');
                  }
                });
            }
        }
    });
});

$(".addGroup").click(function() {
    var personID = $(this).data("personid");
    window.CRM.groups.defaultGroup(function (data) {
      var theGroupID = data;
      var target = window.CRM.groups.promptSelection({Type:window.CRM.groups.selectTypes.Group | window.CRM.groups.selectTypes.Role, GroupID:theGroupID, Role:window.CRM.groups.selectTypes.Role}, function(data){
         window.CRM.groups.addPerson(data.GroupID,personID,data.RoleID, function(){
           window.location.href = window.CRM.root +'/PersonView.php?PersonID=' + personID + '&group=true';
         });
      });
    })
});

$("#verifyURL").click(function () {
    window.CRM.APIRequest({
        method: 'POST',
        path: 'families/verify/url',
        data: JSON.stringify({"famId": window.CRM.currentFamily})
    },function(data) {
        $('#confirm-verify').modal('hide');
        bootbox.alert({
            title: i18next.t("Verification URL"),
            message: "<a href='" + window.CRM.root + "/" + data.url+"'>" + window.CRM.root + "/" + data.url+"</a>"
        });
    });
});

$("#verifyNow").click(function () {
    $.ajax({
        type: 'POST',
        url: window.CRM.root + '/api/families/verify/' + window.CRM.currentFamily + '/now'
    })
        .done(function(data, textStatus, xhr) {
            $('#confirm-verify').modal('hide');
            if (xhr.status == 200) {
                location.reload();
            } else {
                window.CRM.showGlobalMessage(i18next.t("Failed to add verification"), "danger")
            }
        });
});

$("#verifyDownloadPDF").click(function () {
    location.href = window.CRM.root + '/Reports/ConfirmReport.php?familyId=' + window.CRM.currentFamily;
    $('#confirm-verify').modal('hide');
});

$("#onlineVerify").click(function () {
    $.ajax({
        type: 'POST',
        url: window.CRM.root + '/api/families/' + window.CRM.currentFamily + '/verify'
    })
        .done(function(data, textStatus, xhr) {
            $('#confirm-verify').modal('hide');
            if (xhr.status == 200) {
                window.CRM.showGlobalMessage(i18next.t("Verification email sent"), "success")
            } else {
                window.CRM.showGlobalMessage(i18next.t("Failed to send verification email"), "danger")
            }
        });
});

$("#onlineVerifyPDF").click(function () {
    $.ajax({
        type: 'POST',
        url: window.CRM.root + '/api/families/' + window.CRM.currentFamily + '/verifyPDF'
    })
        .done(function(data, textStatus, xhr) {
            $('#confirm-verify').modal('hide');
            if (xhr.status == 200) {
                window.CRM.showGlobalMessage(i18next.t("Verification email sent") + ' (PDF)', "success")
            } else {
                window.CRM.showGlobalMessage(i18next.t("Failed to send verification email") + ' (PDF)', "danger")
            }
        });
});


