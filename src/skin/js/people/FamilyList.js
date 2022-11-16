//
//  This code is under copyright not under MIT Licence
//  copyright   : 2018 Philippe Logel all right reserved not MIT licence
//  Updated     : 2018/07/23
//


$(document).ready(function () {
  $('.remove-property-btn').click(function(e) {
    var familyId = $(this).data('family_id');
    var row = window.CRM.familiesListTable.row( $(this).parents('tr') );
    var rowNode = row.node();

    bootbox.confirm({
      message: i18next.t("Are you sure you want to remove this family from the CRM") + "?",
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
            path: 'gdrp/removefamily',
            data: JSON.stringify({"familyId":familyId})
          },function(data) {
              row.remove().draw();
          });
        }
      }
    });
  });

  $('#remove-all').click(function(e) {
    bootbox.confirm({
      message: i18next.t("Are you sure you want to remove all families from the CRM") + "?",
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
            path: 'gdrp/removeallfamilies'
          },function(data) {
            if (data.status == "failed") {
              bootbox.alert(i18next.t("Not all the families were DELETED : Some of them have records of donations and may NOT be deleted until these donations are associated with another family."), function(){
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
