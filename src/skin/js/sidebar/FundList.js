$(function() {
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
        title:i18next.t('Actions'),
        data:'Id',
        render: function(data, type, full, meta) {
          return '<a class="edit-fund" data-id="'+data+'"><i class="fas fa-pencil-alt" aria-hidden="true"></i></a>&nbsp;&nbsp;&nbsp;<a class="delete-fund" data-id="'+data+'"><i class="far fa-trash-alt" aria-hidden="true" style="color:red"></i></a>';
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
      var frm_str = '<div class="card-body">'
        +'<div class="row">'
        +'  <div class="col-lg-2">'
        +'    <label for="depositComment">'+i18next.t("Active")+'</label>'
        +'    <input id="activCheckbox" type="checkbox" name="activ" id="activ" checked="checked">'
        +'  </div>'
        +'  <div class="col-lg-1">'
        +'    <label for="depositDate">'+i18next.t("Name")+'</label>'
        +'  </div>'
        +'  <div class="col-lg-9">'
        +'    <input class="form-control form-control-sm" name="Name" id="Name" style="width:100%">'
        +'  </div>'
        +'</div>'
        +'<br/>'
        +'<div class="row">'
        +'  <div class="col-lg-3">'
        +'    <label for="depositDate">'+i18next.t("Description")+'</label>'
        +'  </div>'
        +'  <div class="col-lg-9">'
        +'    <input class="form-control form-control-sm" name="description" id="description" style="width:100%">'
        +'  </div>'
        +'</div>'
      +'</div>';

        var object = $('<div/>').html(frm_str).contents();

        return object
    }

  $(document).on("click",".delete-fund", function(){
     var fundId = $(this).data("id");

     bootbox.confirm({
      title: i18next.t("Attention"),
      message: i18next.t("If you delete the fund, <u><b>you'll lose all the connected datas.</b></u><br><b>Are you sure? This action can't be undone.</b>"),
      callback: function(result){
        if (result) {
          window.CRM.APIRequest({
            method: 'POST',
            path: 'donationfunds/delete',
            data: JSON.stringify({"fundId": fundId})
          },function(data) {
            window.CRM.dataFundTable.ajax.reload();
          });
        }
      }
    });
  });

  $(document).on("click",".edit-fund", function(){
     var fundId = $(this).data("id");

      window.CRM.APIRequest({
        method: 'POST',
        path: 'donationfunds/edit',
        data: JSON.stringify({"fundId": fundId})
      },function(data) {
        var modal = bootbox.dialog({
         message: BootboxContentFundList,
         title: i18next.t("Fund Editor"),
         size: 'large',
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
             },function(data) {
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
     size: 'large',
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
         },function(data) {
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
