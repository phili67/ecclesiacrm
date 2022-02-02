$(document).ready(function () {
  window.CRM.dataPropertyListTable = $("#property-listing-table-v2").DataTable({
    ajax:{
      url: window.CRM.root + "/api/properties/typelists/"+window.CRM.propertyType,
      type: 'POST',
      contentType: "application/json",
      dataSrc: "PropertyLists"
    },
    "language": {
      "url": window.CRM.plugin.dataTable.language.url
    },
    "order": [[ 2, "asc" ]],
    columns: [
      {
        width: 'auto',
        title:i18next.t('Actions'),
        data:'ProId',
        render: function(data, type, full, meta) {
          if (window.CRM.menuOptionEnabled == false)
            return '';

          var res = '<a href="#" data-typeid="' + full.ProId + '" class="edit-prop"><i class="fas fa-pencil-alt" aria-hidden="true"></i></a>';
          res += '&nbsp;&nbsp;&nbsp;<a href="#" data-typeid="' + full.ProId + '" class="delete-prop"><i class="far fa-trash-alt" aria-hidden="true" style="color:red"></i></a>';

          return res;
        }
      },
      {
        width: 'auto',
        title:i18next.t('Name'),
        data:'ProName',
        render: function(data, type, full, meta) {
          return data;
        }
      },
      {
        width: 'auto',
        title: window.CRM.propertyTypeName + ' : ' + i18next.t('with this Property...'),
        data:'ProDescription',
        render: function(data, type, full, meta) {
          return data;
        }
      },
      {
        width: 'auto',
        title:i18next.t('Prompt'),
        data:'ProPrompt',
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
    function BootboxContentPropertyList(propertyTypes){
      var options   = '<option value="">'+ i18next.t('Select Property Type') + '</option>';
      var firstTime = true;

      for (i=0;i < propertyTypes.length;i++){
          options += "\n"+'<option value="'+propertyTypes[i].PrtId+'" '+ ((firstTime)?'selected=""':'')+'>'+propertyTypes[i].PrtName+'</option>';
          firstTime=false;
      }

      var frm_str = '<div class="card-body">'
        +'<div class="row">'
        +'  <div class="col-lg-4">'
        +'    <label for="depositDate">'+i18next.t("Type")+'</label>'
        +'  </div>'
        +'  <div class="col-lg-8">'
        +'    <select class="form-control" id="Class" name="Class">'
        +       options
        +'    </select>'
        +'  </div>'
        +'</div>'
        +'<div class="row">'
        +'  <div class="col-lg-4">'
        +'    <label for="depositDate">'+i18next.t("Name")+'</label>'
        +'  </div>'
        +'  <div class="col-lg-8">'
        +'    <input class="form-control input-md" name="Name" id="Name" style="width:100%">'
        +'  </div>'
        +'</div>'
        +'<div class="row">'
        +'  <div class="col-lg-4">'
        +'    <label for="depositDate">'+ window.CRM.propertyTypeName + ' : ' + i18next.t("with this property...")+'</label>'
        +'  </div>'
        +'  <div class="col-lg-8">'
        +'    <input class="form-control input-md" name="description" id="description" style="width:100%">'
        +'  </div>'
        +'</div>'
        +'<div class="row">'
        +'  <div class="col-lg-4">'
        +'    <label for="depositDate">'+i18next.t("Prompt")+'</label>'
        +'  </div>'
        +'  <div class="col-lg-8">'
        +'    <input class="form-control input-md" name="prompt" id="prompt" style="width:100%">'
        +'  </div>'
        +'</div>'
      +'</div>';

        var object = $('<div/>').html(frm_str).contents();

        return object
    }

  $(document).on("click",".delete-prop", function(){
     var typeId  = $(this).data("typeid");
     var message = i18next.t("You're about to delete this property. Would you like to continue ?");

     bootbox.confirm({
      title: i18next.t("Attention"),
      message: message,
      callback: function(result){
        if (result) {
          window.CRM.APIRequest({
            method: 'POST',
            path: 'properties/typelists/delete',
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
        path: 'properties/typelists/edit',
        data: JSON.stringify({"typeId": typeId})
      },function(data) {
        var modal = bootbox.dialog({
         message: BootboxContentPropertyList(data.propertyTypes),
         size: "large",
         title: i18next.t("Property Type Editor"),
         buttons: [
          {
           label: i18next.t("Save"),
           className: "btn btn-primary pull-left",
           callback: function() {
             var Name        = $("#Name").val();
             var Description = $("#description").val();
             var Prompt      = $("#prompt").val();

             window.CRM.APIRequest({
                method: 'POST',
                path: 'properties/typelists/set',
                data: JSON.stringify({"typeId": typeId,"Name": Name,"Description": Description,"Prompt": Prompt})
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

       $("#Name").val(data.proType.ProName);
       $("#description").val(data.proType.ProDescription);
       $("#prompt").val(data.proType.ProPrompt);

       modal.modal("show");
      });
  });

  $(document).on("click","#add-new-prop", function(){
    var modal = bootbox.dialog({
     message: BootboxContentPropertyList(window.CRM.propertyTypesAll),
     size: 'large',
     title: i18next.t("Add a New Property"),
     buttons: [
      {
       label: i18next.t("Save"),
       className: "btn btn-primary pull-left",
       callback: function() {
         var theClass    = window.CRM.propertyType;
         var Name        = $("#Name").val();
         var Description = $("#description").val();
         var Prompt      = $("#prompt").val();

         window.CRM.APIRequest({
            method: 'POST',
            path: 'properties/typelists/create',
            data: JSON.stringify({"Class":theClass, "Name": Name,"Description": Description, "Prompt": Prompt})
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
