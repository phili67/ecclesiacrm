$(document).ready(function () {
  window.CRM.dataMenuLinkTable = $("#menulinksTable").DataTable({
    ajax:{
      url: window.CRM.root + "/api/menulinks/" + window.CRM.personId,
      type: 'POST',
      contentType: "application/json",
      dataSrc: "MenuLinks"
    },
    bSort : false,
    "language": {
      "url": window.CRM.plugin.dataTable.language.url
    },
    columns: [
      {
        width: 'auto',
        title:"",
        data:'realplace',
        render: function(data, type, full, meta) {
          return data;
        }
      },
      {
        width: 'auto',
        title:i18next.t('Place'),
        data:'Order',
        render: function(data, type, full, meta) {
          var res = "<center>";
          if (full.place == "first" || full.place == "intermediate") {
            res += '<a href="#" class="down_action" data-id="'+full.Id+'" data-order="'+full.Order+'"><img src="' + window.CRM.root + '/Images/downarrow.gif" border="0"></a>';
          }
          if (full.place == "last" || full.place == "intermediate") {
            res += '<a href="#" class="up_action" data-id="'+full.Id+'" data-order="'+full.Order+'"><img src="' + window.CRM.root + '/Images/uparrow.gif" border="0"></a>';
          }
          return res+"</center>";
        }
      },
      {
        width: 'auto',
        title:i18next.t('Actions'),
        data:'Id',
        render: function(data, type, full, meta) {
          return '<a class="edit-menu-links" data-id="'+data+'"><i class="fas fa-pencil-alt" aria-hidden="true"></i></a>&nbsp;&nbsp;&nbsp;<a class="delete-menu-links" data-id="'+data+'"><i class="far fa-trash-alt" aria-hidden="true" style="color:red"></i></a>';
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
          return '<a href="'+data+'" target="_blank">'+data+'</a>';
        }
      }
    ],
    responsive: true,
    createdRow : function (row,data,index) {
      $(row).addClass("menuLinksRow");
    }
  });


  $(document).on("click",".up_action", function(){
    var MenuPlace     = $(this).data('order');
    var MenuLinkId    = $(this).data('id');

    window.CRM.APIRequest({
      method: 'POST',
      path: 'menulinks/upaction',
      data: JSON.stringify({"PersonID":window.CRM.personId,"MenuLinkId": MenuLinkId,"MenuPlace":MenuPlace})
    },function(data) {
      reconstructMenuLinks();
    });
  });

  $(document).on("click",".down_action", function(){
    var MenuPlace     = $(this).data('order');
    var MenuLinkId    = $(this).data('id');

    window.CRM.APIRequest({
      method: 'POST',
      path: 'menulinks/downaction',
      data: JSON.stringify({"PersonID":window.CRM.personId,"MenuLinkId": MenuLinkId,"MenuPlace":MenuPlace})
    },function(data) {
      reconstructMenuLinks();
    });
  });


  /* IMPORTANT : be careful
       This will work in cartToGroup code */
    function BootboxContentMenuLinkList(){
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
        +'    <label>'+i18next.t("URI")+'</label>'
        +'  </div>'
        +'  <div class="col-lg-10">'
        +'    <input class="form-control form-control-sm" name="URI" id="URI" style="width:100%">'
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


  function reconstructMenuLinks () {
    if (window.CRM.personId == 0) {
      // global menuLinks
      window.CRM.APIRequest({
        method: 'POST',
        path: 'menulinks/' + window.CRM.personId
      },function(data) {
        var len = data.MenuLinks.length;

        if (len == 0) {
          $(".global_custom_menu").html('');
          $(".global_custom_menu").append('<a href="#"  class="nav-link active" href="' + window.CRM.root + '/MenuLinksList.php"><i class="fas fa-link"></i> <span>' + i18next.t("Global Custom Menus") + '</span></a>');
        } else {
          $(".global_custom_menu").html('');
          $(".global_custom_menu").append('<a href="#" class="nav-link active"><i class="fas fa-link"></i> <p>' + i18next.t("Global Custom Menus") + ' <i class="fas fa-angle-left right"></p></a>');
          $(".global_custom_menu").append('<ul class="nav nav-treeview" style="display: block;">');

          var list = $(".global_custom_menu").find('.nav-treeview');

          for (i=0;i<len;i++) {
            list.append('<li class="nav-item"><a class="nav-link" href="' + data.MenuLinks[i].Uri + '"><i class="far fa-circle"></i> ' + data.MenuLinks[i].Name + '</a></li>');
          }
        }

        window.CRM.dataMenuLinkTable.ajax.reload();
      });
    } else {
      // personal menu links
      var list = $(".personal_custom_menu_"+window.CRM.personId).parent().find('.nav-treeview');

      list.empty()
      list.append('<li class="nav-item"><a class="nav-link active" href="' + window.CRM.root + '/MenuLinksList.php?personId=1"><i class="far fa-circle"></i> ' + i18next.t("Dashboard") + '</a></li>');

      window.CRM.APIRequest({
        method: 'POST',
        path: 'menulinks/' + window.CRM.personId
      },function(data) {
        var len = data.MenuLinks.length;

        for (i=0;i<len;i++) {
          list.append('<li class="nav-item"><a class="nav-link" href="' + data.MenuLinks[i].Uri + '"><i class="fas fa-angle-double-right"></i> ' + data.MenuLinks[i].Name + '</a></li>');
        }

        window.CRM.dataMenuLinkTable.ajax.reload();
      });
    }
  }

  $(document).on("click",".delete-menu-links", function(){
     var MenuLinkId = $(this).data("id");

     bootbox.confirm({
      title: i18next.t("Attention"),
      message: i18next.t("If you delete the Menu Link, <u><b>you'll lose all the connected datas.</b></u><br><b>Are you sure? This action can't be undone.</b>"),
      callback: function(result){
        if (result) {
          window.CRM.APIRequest({
            method: 'POST',
            path: 'menulinks/delete',
            data: JSON.stringify({"MenuLinkId": MenuLinkId})
          },function(data) {
            reconstructMenuLinks();
          });
        }
      }
    });
  });

  $(document).on("click",".edit-menu-links", function(){
     var MenuLinkId = $(this).data("id");

      window.CRM.APIRequest({
        method: 'POST',
        path: 'menulinks/edit',
        data: JSON.stringify({"MenuLinkId": MenuLinkId})
      },function(data) {
        var modal = bootbox.dialog({
         message: BootboxContentMenuLinkList,
         title: i18next.t("Custom Menu Link Editor"),
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
                data: JSON.stringify({"MenuLinkId": MenuLinkId, "Name": Name,"URI": URI})
             },function(data) {
                reconstructMenuLinks();
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
     message: BootboxContentMenuLinkList,
     title: i18next.t("Add Custom Menu Link"),
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
         },function(data) {
            reconstructMenuLinks();
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
