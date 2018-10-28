$(document).ready(function () {
  window.CRM.dataPastoralCareTypeTable = $("#pastoral-careTable").DataTable({
    ajax:{
      url: window.CRM.root + "/api/pastoralcare/",
      type: 'POST',
      contentType: "application/json",
      dataSrc: "PastoralCareTypes"
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
          return '<a class="edit-pastoral-care" data-id="'+data+'"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp;&nbsp;&nbsp;<a class="delete-pastoral-care" data-id="'+data+'"><i class="fa fa-trash-o" aria-hidden="true" style="color:red"></i></a>';
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
        title:i18next.t('Description'),
        data:'Desc',
        render: function(data, type, full, meta) {
          return data;
        }
      },
      {
        width: 'auto',
        title:i18next.t('Visible'),
        data:'Visible',
        render: function(data, type, full, meta) {
          return (data==true)?i18next.t("Yes"):i18next.t("No");
        }
      }
    ],
    responsive: true,
    createdRow : function (row,data,index) {
      $(row).addClass("pastoralCareRow");
    }
  });
  
  
  /* IMPORTANT : be careful
       This will work in cartToGroup code */
    function BootboxContentPastoralCareTypeList(){    
      var frm_str = '<div class="box-body">'
        +'<div class="row">'
        +'  <div class="col-lg-2">'
        +'    <label>'+i18next.t("Title")+'</label>'
        +'  </div>'
        +'  <div class="col-lg-10">'
        +'    <input class="form-control input-md" name="Title" id="Title" style="width:100%">'
        +'  </div>'
        +'</div>'
        +'<div class="row">'
        +'  <div class="col-lg-2">'
        +'    <label>'+i18next.t("Description")+'</label>'
        +'  </div>'
        +'  <div class="col-lg-10">'
        +'    <input class="form-control input-md" name="description" id="description" style="width:100%">'
        +'  </div>'
        +'</div>'
        +'<div class="row">'
        +'  <div class="col-lg-2">'
        +'  </div>'
        +'  <div class="col-lg-10">'
        +'    <br>'
        +'    <input id="visibleCheckbox" type="checkbox" name="visible" id="visible" checked="checked">'
        +'    <label for="depositComment">'+i18next.t("Visible for other authorized users")+'</label>'
        +'  </div>'
        +'</div>'
      +'</div>';

        var object = $('<div/>').html(frm_str).contents();

        return object
    }
    
  $(document).on("click",".delete-pastoral-care", function(){
     var pastoralCareTypeId = $(this).data("id");
     
     bootbox.confirm({
      title: i18next.t("Attention"),
      message: i18next.t("If you delete the pastoral care type, <u><b>you'll lose all the connected datas.</b></u><br><b>Are you sure? This action can't be undone.</b>"),
      callback: function(result){
        if (result) {
          window.CRM.APIRequest({
            method: 'POST',
            path: 'pastoralcare/deletetype',
            data: JSON.stringify({"pastoralCareTypeId": pastoralCareTypeId})
          }).done(function(data) {
            window.CRM.dataPastoralCareTypeTable.ajax.reload();
          });
        }
      }
    });
  });  
  
  $(document).on("click",".edit-pastoral-care", function(){
     var pastoralCareTypeId = $(this).data("id");
     
      window.CRM.APIRequest({
        method: 'POST',
        path: 'pastoralcare/edittype',
        data: JSON.stringify({"pastoralCareTypeId": pastoralCareTypeId})
      }).done(function(data) {
        var modal = bootbox.dialog({
         message: BootboxContentPastoralCareTypeList,
         title: i18next.t("Pastoral Care Type Editor"),
         buttons: [
          {
           label: i18next.t("Save"),
           className: "btn btn-primary pull-left",
           callback: function() {
             var Visible = $("#visibleCheckbox").is(":checked");
             var Title = $("#Title").val();
             var Description = $("#description").val();
           
             window.CRM.APIRequest({
                method: 'POST',
                path: 'pastoralcare/settype',
                data: JSON.stringify({"pastoralCareTypeId": pastoralCareTypeId,"Visible":Visible, "Title": Title,"Description": Description})
             }).done(function(data) {
                window.CRM.dataPastoralCareTypeTable.ajax.reload();
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
       
       $("#visibleCheckbox").prop('checked', data.Visible);
       $("#Title").val(data.Title);
       $("#description").val(data.Desc);
  
       modal.modal("show");
      });
  });
  
  $(document).on("click","#add-new-pastoral-care", function(){
    var modal = bootbox.dialog({
     message: BootboxContentPastoralCareTypeList,
     title: i18next.t("Add Pastoral Care Type"),
     buttons: [
      {
       label: i18next.t("Save"),
       className: "btn btn-primary pull-left",
       callback: function() {
         var Visible = $("#visibleCheckbox").is(":checked");
         var Title = $("#Title").val();
         var Description = $("#description").val();
       
         window.CRM.APIRequest({
            method: 'POST',
            path: 'pastoralcare/createtype',
            data: JSON.stringify({"Visible":Visible, "Title": Title,"Description": Description})
         }).done(function(data) {
            window.CRM.dataPastoralCareTypeTable.ajax.reload();
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