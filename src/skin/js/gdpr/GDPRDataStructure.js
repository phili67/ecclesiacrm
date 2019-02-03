  $(document).ready(function () {
      $("#gdpr-data-structure-table").DataTable({
       "language": {
         "url": window.CRM.plugin.dataTable.language.url
       },
       responsive: true,
       pageLength: 100,
      });
      
      $('input').keydown( function(e) {
        var key  = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;
        var val  = $(this).val();
        var id   = $(this).data("id");
        var type = $(this).data("type");
        
        if (key == 9 || key == 13) {
          window.CRM.APIRequest({
            method: 'POST',
            path: 'gdrp/setComment',
            data: JSON.stringify({"custom_id": id,"comment" : val,"type" : type})
          }).done(function(data) {
            if (key == 13) {
              var dialog = bootbox.dialog({
                message  : i18next.t("Your operation completed successfully."),
              });
            
              setTimeout(function(){ 
                  dialog.modal('hide');
              }, 1000);
            }
          });
        }
      });
  });
