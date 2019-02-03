//
//  This code is under copyright not under MIT Licence
//  copyright   : 2018 Philippe Logel all right reserved not MIT licence
//  Updated     : 2018/07/23
//
  
  
$(document).ready(function () {
  $('.remove-property-btn').click(function(e) {
    var personId = $(this).data('person_id');
    
    bootbox.confirm({
      message: i18next.t("Are you sure you want to remove this person from the CRM") + "?",
      buttons: {
        confirm: {
          label: i18next.t('Yes'),
            className: 'btn-success'
        },
        cancel: {
          label: i18next.t('No'),
          className: 'btn-danger'
        }
      },
      callback: function (result)
      {
        if (result)
        {
          window.CRM.APIRequest({
            method: 'POST',
            path: 'gdrp/removeperson',
            data: JSON.stringify({"personId":personId})
          }).done(function(data) {
            location.reload();
          }); 
        }
      }
    });
  });
  
  $('#remove-all').click(function(e) {      
    bootbox.confirm({
      message: i18next.t("Are you sure you want to remove all persons from the CRM") + "?",
      buttons: {
        confirm: {
          label: i18next.t('Yes'),
            className: 'btn-success'
        },
        cancel: {
          label: i18next.t('No'),
          className: 'btn-danger'
        }
      },
      callback: function (result)
      {
        if (result)
        {
          window.CRM.APIRequest({
            method: 'POST',
            path: 'gdrp/removeallpersons'
          }).done(function(data) {
            if (data.status == "failed") {
              bootbox.alert(i18next.t("Not all the persons were DELETED : Some of them have records of donations and may NOT be deleted until these donations are associated with another family."), function(){ 
                 location.reload();
              }); 
            } else {
              location.reload();
            }
          }); 
        }
      }
    });
  });

});