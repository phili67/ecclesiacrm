$(document).ready(function () {
  window.CRM.dataPastoralCareTypeTable = $("#GDRP-Table").DataTable({
    ajax:{
      url: window.CRM.root + "/api/gdrp/",
      type: 'POST',
      contentType: "application/json",
      dataSrc: "Notes"
    },
    "language": {
      "url": window.CRM.plugin.dataTable.language.url
    },
    columns: [
      {
        width: 'auto',
        title:i18next.t('Full Name'),
        data:'fullNamePerson',
        render: function(data, type, full, meta) {
          return "<a href=\""+window.CRM.root+"/PersonView.php?PersonID="+full.personId+"\">"+data+"</a>";
        }
      },
      {
        width: 'auto',
        title:i18next.t('Title'),
        data:'Title',
        render: function(data, type, full, meta) {
          return data;
        }
      },
      {
        width: 'auto',
        title:i18next.t('Text'),
        data:'Text',
        render: function(data, type, full, meta) {
          return i18next.t(data);
        }
      },
      {
        width: 'auto',
        title:i18next.t('Date Entered'),
        data:'DateEntered',
        render: function(data, type, full, meta) {
          return data;
        }
      },
      {
        width: 'auto',
        title:i18next.t('Date Last Edited'),
        data:'DateLastEdited',
        render: function(data, type, full, meta) {
          return data;
        }
      },      
      {
        width: 'auto',
        title:i18next.t('Type'),
        data:'Type',
        render: function(data, type, full, meta) {        
          return i18next.t(data);
        }
      },
      {
        width: 'auto',
        title:i18next.t('Edited By Name'),
        data:'editedByFullName',
        render: function(data, type, full, meta) {        
          return data;
        }
      },      
      {
        width: 'auto',
        title:i18next.t('Deactivated'),
        data:'Deactivated',
        render: function(data, type, full, meta) {        
          return data;
        }
      },       /*{
        width: 'auto',
        title:i18next.t('Delete'),
        data:'Id',
        render: function(data, type, full, meta) {
          return '<button class="btn btn-danger delete-pastoral-care" data-id="'+data+'" >'+i18next.t('Delete')+'</button>';
        }
      }*/
    ],
    responsive: true,
    createdRow : function (row,data,index) {
      $(row).addClass("noteRow");
    }
  });
});