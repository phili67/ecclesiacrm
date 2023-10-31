$(function() {
  window.CRM.dataPropertyListTable = $("#property-listing-table-v2").DataTable({
    ajax:{
      url: window.CRM.root + "/api/properties/propertytypelists",
      type: 'POST',
      contentType: "application/json",
      dataSrc: "PropertyTypeLists"
    },
    "language": {
      "url": window.CRM.plugin.dataTable.language.url
    },
    "order": [[ 2, "asc" ]],
    columns: [
      {
        width: 'auto',
        title:i18next.t('Actions'),
        data:'PrtId',
        render: function(data, type, full, meta) {
          if (window.CRM.menuOptionEnabled == false)
            return '';

          var res = '<a href="#" data-typeid="' + full.PrtId + '" class="edit-prop"><i class="fas fa-pencil-alt" aria-hidden="true"></i></a>';
          res += '&nbsp;&nbsp;&nbsp;<a href="#" data-typeid="' + full.PrtId + '" data-warn="' + full.Properties + '" class="delete-prop"><i class="far fa-trash-alt" aria-hidden="true" style="color:red"></i></a>';
          return res;
        }
      },
      {
        width: 'auto',
        title:i18next.t('Name'),
        data:'PrtName',
        render: function(data, type, full, meta) {
          return data;
        }
      },
      {
        width: 'auto',
        title:i18next.t('Class'),
        data:'PrtClass',
        render: function(data, type, full, meta) {
          return i18next.t(data);
        }
      },
      {
        width: 'auto',
        title:i18next.t('Description'),
        data:'PrtDescription',
        render: function(data, type, full, meta) {
          return data;
        }
      }
    ],
    responsive: true,
    createdRow : function (row,data,index) {
      $(row).addClass("listRow");
    }
  });


  /* IMPORTANT : be careful
       This will work in cartToGroup code */
    function BootboxContentPropertyTypeList(type){
      var frm_str = '<div class="card-body">'
        +'<div class="row">'
        +' <div class="row">'
        +'  <div class="col-lg-3">'
        +'    <label for="depositDate">'+i18next.t("Type")+'</label>'
        +'  </div>'
        +'  <div class="col-lg-9">'
        +'    <select class= "form-control form-control-sm" id="Class" name="Class">'
        +'        <option value="p" '+ ((type=='p' || type==-1)?'selected=""':'')+'>'+i18next.t("Person")+'</option>'
        +'        <option value="f" '+ ((type=='f')?'selected=""':'')+'>'+i18next.t("Family")+'</option>'
        +'        <option value="g" '+ ((type=='g')?'selected=""':'')+'>'+i18next.t("Group")+'</option>'
        +'    </select>'
        +'  </div>'
        +'</div>'
        +'<div class="row">'
        +'  <div class="col-lg-3">'
        +'    <label for="depositDate">'+i18next.t("Name")+'</label>'
        +'  </div>'
        +'  <div class="col-lg-9">'
        +'    <input class="form-control form-control-sm" name="Name" id="Name" style="width:100%">'
        +'  </div>'
        +'</div>'
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

  $(document).on("click",".delete-prop", function(){
     var typeId  = $(this).data("typeid");
     var warn    = $(this).data("warn");
     var message = i18next.t("You're about to delete this general properties. Would you like to continue ?");

     if (warn > 0) {
       message = '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle" aria-hidden="true"></i>'+i18next.t('This general property type is still being used by') + ' ' + warn + ' ' + ((warn==1)?i18next.t('property'):i18next.t('properties')) + '.<BR>' + i18next.t('If you delete this type, you will also remove all properties using') + '<BR>' + i18next.t('it and lose any corresponding property assignments.')+'</div>';
     }

     bootbox.confirm({
      title: i18next.t("Attention"),
      message: message,
      callback: function(result){
        if (result) {
          window.CRM.APIRequest({
            method: 'POST',
            path: 'properties/propertytypelists/delete',
            data: JSON.stringify({"typeId": typeId})
          },function(data) {
            window.CRM.dataPropertyListTable.ajax.reload();
          });
        }
      }
    });
  });

  $(document).on("click",".edit-prop", function(){
     var typeId = $(this).data("typeid");

      window.CRM.APIRequest({
        method: 'POST',
        path: 'properties/propertytypelists/edit',
        data: JSON.stringify({"typeId": typeId})
      },function(data) {
        var modal = bootbox.dialog({
         message: BootboxContentPropertyTypeList(data.prtType.PrtClass),
         title: i18next.t("Property Type Editor"),
         size:"large",
         buttons: [
          {
           label: i18next.t("Save"),
           className: "btn btn-primary pull-left",
           callback: function() {
             var Name = $("#Name").val();
             var Description = $("#description").val();

             window.CRM.APIRequest({
                method: 'POST',
                path: 'properties/propertytypelists/set',
                data: JSON.stringify({"typeId": typeId,"Name": Name,"Description": Description})
             },function(data) {
                window.CRM.dataPropertyListTable.ajax.reload();
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

       $("#Name").val(data.prtType.PrtName);
       $("#description").val(data.prtType.PrtDescription);

       modal.modal("show");
      });
  });

  $(document).on("click","#add-new-prop", function(){
    var modal = bootbox.dialog({
     message: BootboxContentPropertyTypeList(-1),
     title: i18next.t("Add a New Property Type"),
     size:"large",
     buttons: [
      {
       label: i18next.t("Save"),
       className: "btn btn-primary pull-left",
       callback: function() {
         var theClass    = $("#Class").val();
         var Name        = $("#Name").val();
         var Description = $("#description").val();

         window.CRM.APIRequest({
            method: 'POST',
            path: 'properties/propertytypelists/create',
            data: JSON.stringify({"Class":theClass, "Name": Name,"Description": Description})
         },function(data) {
            window.CRM.dataPropertyListTable.ajax.reload();
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
