$(document).ready(function () {
  window.CRM.dataMenuLinksTable = $("#menulinksTable").DataTable({
    ajax:{
      url: window.CRM.root + "/api/menulinks/" + window.CRM.personId,
      type: 'POST',
      contentType: "application/json",
      dataSrc: "MenuLinkss"
    },
    "language": {
      "url": window.CRM.plugin.dataTable.language.url
    },
    columns: [
      {
        width: 'auto',
        title:i18next.t('ID'),
        data:'Id',
        render: function(data, type, full, meta) {
          return data;
        }
      },
      {
        width: 'auto',
        title:i18next.t('Name'),
        data:'Name',
        render: function(data, type, full, meta) {
          return data;
        }
      },
      {
        width: 'auto',
        title:i18next.t('Uri'),
        data:'Uri',
        render: function(data, type, full, meta) {
          return data;
        }
      },
      {
        width: 'auto',
        title:i18next.t('Edit'),
        data:'Id',
        render: function(data, type, full, meta) {        
          return '<button class="btn btn-success edit-menu-links" data-id="'+data+'" >'+i18next.t('Edit')+'</button>';
        }
      },
      {
        width: 'auto',
        title:i18next.t('Delete'),
        data:'Id',
        render: function(data, type, full, meta) {
          return '<button class="btn btn-danger delete-menu-links" data-id="'+data+'" >'+i18next.t('Delete')+'</button>';
        }
      }
    ],
    responsive: true,
    createdRow : function (row,data,index) {
      $(row).addClass("menuLinksRow");
    }
  });
  
  
  /* IMPORTANT : be careful
       This will work in cartToGroup code */
    function BootboxContentMenuLinksList(){    
      var frm_str = '<div class="box-body">'
        +'<div class="row">'
        +'  <div class="col-lg-2">'
        +'    <label>'+i18next.t("Name")+'</label>'
        +'  </div>'
        +'  <div class="col-lg-10">'
        +'    <input class="form-control input-md" name="Name" id="Name" style="width:100%">'
        +'  </div>'
        +'</div>'
        +'<div class="row">'
        +'  <div class="col-lg-2">'
        +'    <label>'+i18next.t("URI")+'</label>'
        +'  </div>'
        +'  <div class="col-lg-10">'
        +'    <input class="form-control input-md" name="URI" id="URI" style="width:100%">'
        +'  </div>'
        +'</div>'
        +'<div class="row">'
        +'  <div class="col-lg-2">'
        +'  </div>'
        +'  <div class="col-lg-10">'
        +'    <br>'
        +'    <label for="depositComment">'+i18next.t("This link should begin with : http://.... or https://....")+'</label>'
        +'  </div>'
        +'</div>'
      +'</div>';

        var object = $('<div/>').html(frm_str).contents();

        return object
    }
    
  $(document).on("click",".delete-menu-links", function(){
     var MenuLinksId = $(this).data("id");
     
     bootbox.confirm({
      title: i18next.t("Attention"),
      message: i18next.t("If you delete the Menu Link, <u><b>you'll lose all the connected datas.</b></u><br><b>Are you sure? This action can't be undone.</b>"),
      callback: function(result){
        if (result) {
          window.CRM.APIRequest({
            method: 'POST',
            path: 'menulinks/delete',
            data: JSON.stringify({"MenuLinksId": MenuLinksId})
          }).done(function(data) {
            window.CRM.dataMenuLinksTable.ajax.reload();
          });
        }
      }
    });
  });  
  
  $(document).on("click",".edit-menu-links", function(){
     var MenuLinksId = $(this).data("id");
     
      window.CRM.APIRequest({
        method: 'POST',
        path: 'menulinks/edit',
        data: JSON.stringify({"MenuLinksId": MenuLinksId})
      }).done(function(data) {
        var modal = bootbox.dialog({
         message: BootboxContentMenuLinksList,
         title: i18next.t("Menu Link Editor"),
         buttons: [
          {
           label: i18next.t("Save"),
           className: "btn btn-primary pull-left",
           callback: function() {
             var Name = $("#Name").val();
             var URI = $("#URI").val();
           
             window.CRM.APIRequest({
                method: 'POST',
                path: 'menulinks/set',
                data: JSON.stringify({"MenuLinksId": MenuLinksId, "Name": Name,"URI": URI})
             }).done(function(data) {
                window.CRM.dataMenuLinksTable.ajax.reload();
                location.reload();
             });
            }
          },
          {
           label: i18next.t("Close"),
           className: "btn btn-default pull-left",
           callback: function() {
              console.log("just do something on close");
           }
          }
         ],
         show: false,
         onEscape: function() {
            modal.modal("hide");
         }
       });
       
       $("#Name").val(data.Name);
       $("#URI").val(data.Uri);
  
       modal.modal("show");
      });
  });
  
  $(document).on("click","#add-new-menu-links", function(){
    var modal = bootbox.dialog({
     message: BootboxContentMenuLinksList,
     title: i18next.t("Add Menu Link"),
     buttons: [
      {
       label: i18next.t("Save"),
       className: "btn btn-primary pull-left",
       callback: function() {
         var Name = $("#Name").val();
         var URI = $("#URI").val();
       
         window.CRM.APIRequest({
            method: 'POST',
            path: 'menulinks/create',
            data: JSON.stringify({"PersonID":window.CRM.personId, "Name": Name,"URI": URI})
         }).done(function(data) {
            //window.CRM.dataMenuLinksTable.ajax.reload();
            location.reload();
         });
        }
      },
      {
       label: i18next.t("Close"),
       className: "btn btn-default pull-left",
       callback: function() {
          console.log("just do something on close");
       }
      }
     ],
     show: false,
     onEscape: function() {
        modal.modal("hide");
     }
   });
   
   modal.modal("show");
  });

});