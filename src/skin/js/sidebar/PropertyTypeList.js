$(document).ready(function () {
  window.CRM.dataFundTable = $("#property-listing-table-v2").DataTable({
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
        data:'Id',
        render: function(data, type, full, meta) {
          if (window.CRM.menuOptionEnabled == false)
            return '';
            
          if (full.PrtName != 'Menu') {
            var res = '<a href="#" data-typeid="' + full.PrtId + '" class="edit-prop"><i class="fa fa-pencil" aria-hidden="true"></i></a>';
            res += '&nbsp;&nbsp;&nbsp;<a href="#" data-typeid="' + full.PrtId + '" class="delete-prop"><i class="fa fa-trash-o" aria-hidden="true" style="color:red"></i></a>';
            return res;
          } else {
            return '';
          }
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
          if (full.PrtName == 'Menu') {
            return i18next.t("Sunday School Sub Menu");
          } else {
            return i18next.t(data);
          }
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
      var frm_str = '<div class="box-body">'
        +'<div class="row">'
        +'  <div class="col-lg-2">'
        +'    <select class="form-control" id="class" name="Class">'
        +'        <option value="p" '+ ((type=='p')?'selected=""':'')+'>'+i18next.t("Person")+'</option>'
        +'        <option value="f" '+ ((type=='f')?'selected=""':'')+'>'+i18next.t("Family")+'</option>'
        +'        <option value="g" '+ ((type=='g')?'selected=""':'')+'>'+i18next.t("Group")+'</option>'
        +'    </select>'
        +'  </div>'
        +'  <div class="col-lg-1">'
        +'    <label for="depositDate">'+i18next.t("Name")+'</label>'
        +'  </div>'
        +'  <div class="col-lg-9">'
        +'    <input class="form-control input-md" name="Name" id="Name" style="width:100%">'
        +'  </div>'
        +'</div>'
        +'<div class="row">'
        +'  <div class="col-lg-2">'
        +'    <label for="depositDate">'+i18next.t("Description")+'</label>'
        +'  </div>'
        +'  <div class="col-lg-10">'
        +'    <input class="form-control input-md" name="description" id="description" style="width:100%">'
        +'  </div>'
        +'</div>'
      +'</div>';

        var object = $('<div/>').html(frm_str).contents();

        return object
    }
    
  $(document).on("click",".delete-prop", function(){
     var typeId = $(this).data("typeid");
     
     bootbox.confirm({
      title: i18next.t("Attention"),
      message: i18next.t("If you delete the fund, <u><b>you'll lose all the connected datas.</b></u><br><b>Are you sure? This action can't be undone.</b>"),
      callback: function(result){
        if (result) {
          window.CRM.APIRequest({
            method: 'POST',
            path: 'donationfunds/delete',
            data: JSON.stringify({"typeId": typeId})
          }).done(function(data) {
            window.CRM.dataFundTable.ajax.reload();
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
      }).done(function(data) {
        var modal = bootbox.dialog({
         message: BootboxContentPropertyTypeList(data.prtType.PrtClass),
         title: i18next.t("Property Type Editor"),
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
       
       $("#Name").val(data.prtType.PrtName);
       $("#description").val(data.prtType.PrtDescription);
  
       modal.modal("show");
      });
  });
  
  $(document).on("click","#add-new-fund", function(){
    var modal = bootbox.dialog({
     message: BootboxContentPropertyTypeList,
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