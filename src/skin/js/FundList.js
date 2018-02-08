$(document).ready(function () {
  window.CRM.dataFundTable = $("#fundTable").DataTable({
    ajax:{
      url: window.CRM.root + "/api/donationfunds/",
      type: 'POST',
      contentType: "application/json",
      dataSrc: "DonationFunds"
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
        title:i18next.t('Active'),
        data:'Active',
        render: function(data, type, full, meta) {
          return i18next.t(data);
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
        title:i18next.t('Description'),
        data:'Description',
        render: function(data, type, full, meta) {
          return data;
        }
      },
      {
        width: 'auto',
        title:i18next.t('Edit'),
        data:'Id',
        render: function(data, type, full, meta) {        
          return '<button class="btn btn-success edit-fund" data-id="'+data+'" >'+i18next.t('Edit')+'</button>';
        }
      },
      {
        width: 'auto',
        title:i18next.t('Delete'),
        data:'Id',
        render: function(data, type, full, meta) {
          return '<button class="btn btn-danger delete-fund" data-id="'+data+'" disabled>'+i18next.t('Delete')+'</button>';
        }
      }
    ],
    responsive: true,
    createdRow : function (row,data,index) {
      $(row).addClass("paymentRow");
    }
  });
  
  
  /* IMPORTANT : be careful
       This will work in cartToGroup code */
    function BootboxContentFundList(){    
      var frm_str = '<div class="box-body">'
        +'<div class="row">'
        +'  <div class="container-fluid">'
        +'    <div class="col-lg-2">'
        +'    <label for="depositComment">'+i18next.t("Active")+'</label>'
        +'    <input id="activCheckbox" type="checkbox" name="activ" id="activ" checked="checked">'
        +'  </div>'
        +'  <div class="col-lg-1">'
        +'    <label for="depositDate">'+i18next.t("Name")+'</label>'
        +'  </div>'
        +'  <div class="col-lg-3">'
        +'    <input class="form-control input-sm" name="Name" id="Name" style="width:100%">'
        +'  </div>'
        +'  <div class="col-lg-2">'
        +'    <label for="depositDate">'+i18next.t("Description")+'</label>'
        +'  </div>'
        +'  <div class="col-lg-4">'
        +'    <input class="form-control input-sm" name="description" id="description" style="width:100%">'
        +'  </div>'
        +'</div>'
      +'</div>';

        var object = $('<div/>').html(frm_str).contents();

        return object
    }
    
  
  $(document).on("click",".edit-fund", function(){
     var fundId = $(this).data("id");
     
      window.CRM.APIRequest({
        method: 'POST',
        path: 'donationfunds/edit',
        data: JSON.stringify({"fundId": fundId})
      }).done(function(data) {
        var modal = bootbox.dialog({
         message: BootboxContentFundList,
         title: i18next.t("Fund Editor"),
         buttons: [
          {
           label: i18next.t("Save"),
           className: "btn btn-primary pull-left",
           callback: function() {
             var Activ = $("#activCheckbox").is(":checked");
             var Name = $("#Name").val();
             var Description = $("#description").val();
           
             window.CRM.APIRequest({
                method: 'POST',
                path: 'donationfunds/set',
                data: JSON.stringify({"fundId": fundId,"Activ":Activ, "Name": Name,"Description": Description})
             }).done(function(data) {
                window.CRM.dataFundTable.ajax.reload();
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
       
       $("#activCheckbox").prop('checked', data.Active);
       $("#Name").val(data.Name);
       $("#description").val(data.Description);
  
       modal.modal("show");
      });
  });
  
  $(document).on("click","#add-new-fund", function(){
    var modal = bootbox.dialog({
     message: BootboxContentFundList,
     title: i18next.t("Add Fund"),
     buttons: [
      {
       label: i18next.t("Save"),
       className: "btn btn-primary pull-left",
       callback: function() {
         var Activ = $("#activCheckbox").is(":checked");
         var Name = $("#Name").val();
         var Description = $("#description").val();
       
         window.CRM.APIRequest({
            method: 'POST',
            path: 'donationfunds/create',
            data: JSON.stringify({"Activ":Activ, "Name": Name,"Description": Description})
         }).done(function(data) {
            window.CRM.dataFundTable.ajax.reload();
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