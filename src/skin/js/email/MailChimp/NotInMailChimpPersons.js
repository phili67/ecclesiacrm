//
//  This code is under copyright not under MIT Licence
//  copyright   : 2018 Philippe Logel all right reserved.
//                This code can't be included in another software.
//
//  Updated : 2020/06/18
//


$(function() {
  window.CRM.dataFundTable = $("#personsWithoutEmailTable").DataTable({
    ajax:{
      url: window.CRM.root + "/api/persons/NotInMailChimp/emails/persons",
      type: 'GET',
      contentType: "application/json",
      dataSrc: "emails",
      "beforeSend": function (xhr) {
        xhr.setRequestHeader('Authorization',
            "Bearer " +  window.CRM.jwtToken
        );
      }
    },
    "language": {
      "url": window.CRM.plugin.dataTable.language.url
    },
    columns: [
      {
        width: 'auto',
        title:i18next.t('Person'),
        data:'id',
        render: function(data, type, full, meta) {
          return full.img;
        }
      },
      {
        width: 'auto',
        title:i18next.t('Name'),
        data:'url',
        render: function(data, type, full, meta) {
          return data;
        }
      },
      {
        width: 'auto',
        title:i18next.t('Email'),
        data:'email',
        render: function(data, type, full, meta) {
          return data;
        }
      }
    ],
    responsive: true,
    createdRow : function (row,data,index) {
      $(row).addClass("duplicateRow");
    }
  });
});
