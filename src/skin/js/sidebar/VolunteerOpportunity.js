$(document).ready(function () {
  window.CRM.VolunteerOpportunityTable = $("#VolunteerOpportunityTable").DataTable({
    ajax:{
      url: window.CRM.root + "/api/volunteeropportunity/",
      type: 'POST',
      contentType: "application/json",
      dataSrc: "VolunteerOpportunities"
    },
    "order": [[ 1, "asc" ]],
    "language": {
      "url": window.CRM.plugin.dataTable.language.url
    },
    columns: [
      {
        width: 'auto',
        title:i18next.t('Actions'),
        data:'Id',
        searchable: false,
        render: function(data, type, full, meta) {
          return '<a class="edit-volunteer-opportunity" data-id="'+data+'"><i class="fas fa-pencil-alt" aria-hidden="true"></i></a>&nbsp;&nbsp;&nbsp;<a class="delete-volunteer-opportunity" data-id="'+data+'"><i class="far fa-trash-alt" aria-hidden="true" style="color:red"></i></a>';
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
        title:i18next.t('Activ'),
        data:'Active',
        searchable: false,
        render: function(data, type, full, meta) {
          return (data == "true")?i18next.t('Yes'):i18next.t('No');
        }
      },
      {
        width: 'auto',
        title:i18next.t('Parent (hierarchy)'),
        data:'Menu',
        searchable: false,
        render: function(data, type, full, meta) {
            return data;
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
    function BootboxContentVolunteerOpportunity(){
      var frm_str = '<div class="card-body">'
        +'<div class="row">'
        +'  <div class="col-lg-2">'
        +'    <label>'+i18next.t("Name")+'</label>'
        +'  </div>'
        +'  <div class="col-lg-10">'
        +'    <input class="form-control form-control-sm" name="Name" id="Name" style="width:100%">'
        +'  </div>'
        +'</div>'
        +'<div class="row">'
        +'  <div class="col-lg-2">'
        +'    <label>'+i18next.t("Description")+'</label>'
        +'  </div>'
        +'  <div class="col-lg-10">'
        +'    <input class="form-control form-control-sm" name="desc" id="desc" style="width:100%">'
        +'  </div>'
        +'</div>'
        +'<div class="row">'
        +'  <div class="col-lg-2">'
        +'<input type="checkbox"  id="activ" class="ibtn">'
        +'  </div>'
        +'  <div class="col-lg-10">'
        +'    <label for="depositComment">'+i18next.t("Activ")+'</label>'
        +'  </div>'
        +'</div>'
      +'</div>';

        var object = $('<div/>').html(frm_str).contents();

        return object
    }

  $(document).on("click",".delete-volunteer-opportunity", function(){
     var id = $(this).data("id");

     bootbox.confirm({
      title: i18next.t("Attention"),
      size: "large",
      message: i18next.t("If you delete the Menu Link, <u><b>you'll lose all the connected datas.</b></u><br><b>Are you sure? This action can't be undone.</b>"),
      callback: function(result){
        if (result) {
          window.CRM.APIRequest({
            method: 'POST',
            path: 'volunteeropportunity/delete',
            data: JSON.stringify({"id": id})
          },function(data) {
            window.CRM.VolunteerOpportunityTable.ajax.reload();
          });
        }
      }
    });
  });

  $(document).on("click",".edit-volunteer-opportunity", function(){
     var id = $(this).data("id");

      window.CRM.APIRequest({
        method: 'POST',
        path: 'volunteeropportunity/edit',
        data: JSON.stringify({"id": id})
      },function(data) {
        var modal = bootbox.dialog({
         message: BootboxContentVolunteerOpportunity,
         title: i18next.t("Custom Menu Link Editor"),
         size: "large",
         buttons: [
          {
             label: '<i class="fas fa-times"></i> ' + i18next.t("Close"),
             className: "btn btn-default pull-left",
             callback: function() {
                 console.log("just do something on close");
             }
          },
          {
           label: '<i class="fas fa-check"></i> ' + i18next.t("Save"),
           className: "btn btn-primary pull-left",
           callback: function() {
             var Name         = $("#Name").val();
             var desc         = $("#desc").val();
             var state        = $("#activ").is(':checked');

             window.CRM.APIRequest({
                method: 'POST',
                path: 'volunteeropportunity/set',
                data: JSON.stringify({"id": id, "Name": Name,"desc": desc,"state":state})
             },function(data) {
                window.CRM.VolunteerOpportunityTable.ajax.reload();
             });
            }
          }
         ],
         show: false,
         onEscape: function() {
            modal.modal("hide");
         }
       });

       $("#Name").val(data.Name);
       $("#desc").val(data.Description);
       if (data.Active == "true")
         $("#activ").attr("checked","checked");
       else
         $("#activ").removeAttr("checked");

       modal.modal("show");
      });
  });

  $(document).on("click","#add-new-volunteer-opportunity", function(){
    var modal = bootbox.dialog({
     message: BootboxContentVolunteerOpportunity,
     title: i18next.t("Add New Volunteer Opportunity"),
     size: "large",
     buttons: [
      {
       label: '<i class="fas fa-times"></i> ' + i18next.t("Close"),
       className: "btn btn-default pull-left",
       callback: function() {
          console.log("just do something on close");
       }
      },
      {
         label: '<i class="fas fa-check"></i> ' + i18next.t("Save"),
         className: "btn btn-primary pull-left",
         callback: function() {
             var Name         = $("#Name").val();
             var desc         = $("#desc").val();
             var state        = $("#activ").is(':checked');

             window.CRM.APIRequest({
                 method: 'POST',
                 path: 'volunteeropportunity/create',
                 data: JSON.stringify({"Name": Name,"desc": desc,"state":state})
             },function(data) {
                 window.CRM.VolunteerOpportunityTable.ajax.reload();
             });
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

    $(document).on("change",".selectHierarchy",function() {
        var parentId = this.value;
        var voldId = $(this).data('id');

        window.CRM.APIRequest({
            method: 'POST',
            path: 'volunteeropportunity/changeParent',
            data: JSON.stringify({"voldId": voldId, "parentId": parentId})
        },function(data) {
            window.CRM.VolunteerOpportunityTable.ajax.reload();
        });
    });
});
