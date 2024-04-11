const contentExists = (contentUrl, callback) => {
    fetch(contentUrl, {            
        method: "HEAD",
        contentType: false,
        processData: false
    })
    .then(data => {
        // enter you logic when the fetch is successful
        if (callback) {
            callback(true);
        }
    })
    .catch(error => {
        // enter your logic for when there is an error (ex. error toast)
        callback(false);
    });
}

$('.delete-person').on('click', function (event) {
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
                window.CRM.APIRequest({
                    method: 'DELETE',
                    path: 'persons/' + thisLink.data('person_id')
                }, function (data) {
                    if (thisLink.data('view') == 'family') {
                        location.reload();
                    } else {
                        location.replace(window.CRM.root + "/");
                    }
                });
            }
        }
    });
});


$('.saveNoteAsWordFile').on('click', function (event) {
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

$(".addGroup").on('click', function() {
    var personID = $(this).data("personid");
    window.CRM.groups.defaultGroup(function (data) {
      var theGroupID = data;
      var target = window.CRM.groups.promptSelection({Type:window.CRM.groups.selectTypes.Group | window.CRM.groups.selectTypes.Role, GroupID:theGroupID, Role:window.CRM.groups.selectTypes.Role}, function(data){
         window.CRM.groups.addPerson(data.GroupID,personID,data.RoleID, function(data){
             if (data.status == "failed") {
                 window.CRM.DisplayAlert(i18next.t("Error"), i18next.t("A kid should have a family in a sunday school group !"));
             } else {
                 window.location.href = window.CRM.root +'/v2/people/person/view/' + personID + '/Group';
             }
         });
      });
    })
});