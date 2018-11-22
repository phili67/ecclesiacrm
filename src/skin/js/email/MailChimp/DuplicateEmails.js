$(document).ready(function () {
  window.CRM.dataFundTable = $("#duplicateTable").DataTable({
    ajax:{
      url: window.CRM.root + "/api/persons/duplicate/emails",
      type: 'GET',
      contentType: "application/json",
      dataSrc: "emails"
    },
    "language": {
      "url": window.CRM.plugin.dataTable.language.url
    },
    columns: [
      {
        width: 'auto',
        title:i18next.t('Email'),
        data:'email',
        render: function(data, type, full, meta) {
          return data;
        }
      },      
      {
        width: 'auto',
        title:i18next.t('People'),
        data:'people',
        render: function(data, type, full, meta) {
          var render ="<ul>";
          $.each( data, function( key, value ) {
              render += "<li><a href='"+ window.CRM.root + "/PersonView.php?PersonID=" +value.id + "' target='user' />"+ value.name + "</a></li>";
          });
          render += "</ul>"
          return render;
        }
      },
      {
        width: 'auto',
        title:i18next.t('Families'),
        data:'families',
        render: function(data, type, full, meta) {
          var render ="<ul>";
          $.each( data, function( key, value ) {
              render += "<li><a href='"+ window.CRM.root + "/FamilyView.php?FamilyID=" +value.id + "' target='family' />"+ value.name + "</a></li>";
          });
          render += "</ul>"
          return render;
        }
      },
    ],
    responsive: true,
    createdRow : function (row,data,index) {
      $(row).addClass("duplicateRow");
    }
  });
});