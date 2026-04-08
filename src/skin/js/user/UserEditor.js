function addRolesToMainDropdown()
    {
      $("#AllRoles").empty();


      window.CRM.APIRequest({
            method: 'POST',
            path: 'userrole/getall',
      },function(data) {
        var len = data.length;

        for (i=0; i<len; ++i) {
          $("#AllRoles").append('<a href="#" class="dropdown-item changeRole" data-id="'+data[i].Id+'"><i class="fas fa-tag mr-1"></i>'+data[i].Name+'</a>');
          if (i == 0) {
            $("#mainbuttonRole").data("id",data[i].Id);
          }
        }
      });
    }

    function addRoles()
    {
      $('#select-userrole').find('option').remove();

      window.CRM.APIRequest({
            method: 'POST',
            path: 'userrole/getall',
      },function(data) {
        var elt = document.getElementById("select-userrole");
        var len = data.length;

        for (i=0; i<len; ++i) {
          var option = document.createElement("option");
          // there is a groups.type in function of the new plan of schema
          option.text = data[i].Name;
          //option.title = data[i].type;
          option.value = data[i].Id;

          elt.appendChild(option);
        }
      });

      addRolesToMainDropdown();
    }

    $(function() {
        $("#personSelect").select2({
          language: window.CRM.shortLocale,
          minimumInputLength: 2,
          placeholder: " -- "+i18next.t("Person")+" -- "
        });

        $("#personSelect").on("select2:select", function (event) {
          if ($(this).find(':selected').data('email') == '') {
            window.CRM.DisplayAlert(i18next.t("Error"),i18next.t("The user must have an email address."));
            $("#personSelect").val('').trigger("change");
          }
        });

        $(".data-table1").DataTable({
          "language": {
            "url": window.CRM.plugin.dataTable.language.url
          },
          pageLength: 100,
          info: false,
          bSort : false,
          searching: false, paging: false,
          responsive: true
        });

        $(".data-table2").DataTable({
          "language": {
            "url": window.CRM.plugin.dataTable.language.url
          },
          pageLength: 100,
          info: false,
          bSort : false,
          searching: false, paging: false,
          responsive: true
        });

        function BootboxContent(){
          var frm_str = '<div class="container-fluid px-0">'
                  +'<div class="alert alert-light border mb-3">'
                    +'<i class="fas fa-user-tag text-primary mr-2"></i>'
                    + i18next.t("Select your Role") + ' ' + i18next.t("and update its name if needed.")
                  +'</div>'
                  +'<div class="form-group mb-3">'
                    +'<label for="select-userrole" class="text-muted small font-weight-bold text-uppercase mb-2"><span class="text-danger">*</span> ' + i18next.t("Select your Role") + '</label>'
                    +'<select size="6" id="select-userrole" class="form-control">'
                    +'</select>'
                  +'</div>'
                  +'<div class="form-group mb-0 div-title">'
                    +'<label for="RoleName" class="text-muted small font-weight-bold text-uppercase mb-2"><span class="text-danger">*</span> ' + i18next.t("Role Name") + '</label>'
                    +"<input type='text' id='RoleName' placeholder='" + i18next.t("Role Name") + "' size='30' maxlength='100' class='form-control form-control-sm' required>"
                  +'</div>'
                +'</div>';

                var object = $('<div/>').html(frm_str).contents();

              return object
        }

        $(document).on('change','#select-userrole',function() {
          var roleID = $('#select-userrole').val();

          window.CRM.APIRequest({
             method: 'POST',
             path: 'userrole/get',
             data: JSON.stringify({"roleID": roleID})
          },function(data) {
             $('#RoleName').val(data.name);
          });
        });

        $("#manageRole").on('click',function() {
          var modal = bootbox.dialog({
             title: '<i class="fas fa-user-shield mr-2"></i>' + i18next.t("Role management"),
             message: BootboxContent(),
             buttons: [
              {
               label: '<i class="fa fa-times"></i> ' + i18next.t("Close"),
               className: "btn btn-sm btn-outline-secondary",
               callback: function() {
               }
              },
              {
               label: '<i class="fas fa-trash-alt"></i> ' + i18next.t("Delete"),
               className: "btn btn-sm btn-danger",
               callback: function() {
                  var roleID = $('#select-userrole').val();

                  bootbox.confirm({
                    title: '<i class="fas fa-exclamation-triangle text-danger mr-2"></i>' + i18next.t("Delete"),
                    message: '<div class="alert alert-danger mb-0">' + i18next.t("Are you sure? You're about to delete this Role.") + '</div>',
                    buttons: {
                      cancel: {
                        label: '<i class="fas fa-times"></i> ' + i18next.t("Cancel"),
                        className: 'btn btn-sm btn-outline-secondary'
                      },
                      confirm: {
                        label: '<i class="fas fa-trash-alt"></i> ' + i18next.t("Delete"),
                        className: 'btn btn-sm btn-danger'
                      }
                    },
                    callback: function(result){
                      if (result) {
                        window.CRM.APIRequest({
                           method: 'POST',
                           path: 'userrole/delete',
                           data: JSON.stringify({"roleID": roleID})
                        },function(data) {
                          addRoles();
                        });
                      }
                    }
                  });
                  return false;
               }
              },
              {
               label: '<i class="fas fa-pencil-alt"></i> ' + i18next.t("Rename"),
               className: "btn btn-sm btn-primary",
               callback: function() {
                  var roleID = $('#select-userrole').val();
                  var name = $('#RoleName').val();

                  window.CRM.APIRequest({
                     method: 'POST',
                     path: 'userrole/rename',
                     data: JSON.stringify({"roleID": roleID,"name":name})
                  },function(data) {
                    addRoles();
                  });
                  return false;
               }
              }
             ],
             show: false,
             onEscape: function() {
                modal.modal("hide");
             }
         });

         modal.modal("show");

         addRoles();
        });


        $('body').on('click','.changeRole', function(){
          var roleID = $(this).data("id");

          var test = $('input[name="roleID"]:hidden').val(roleID);

          window.CRM.APIRequest({
             method: 'POST',
             path: 'userrole/get',
             data: JSON.stringify({"roleID": roleID})
          },function(data) {
             var array = data.global.split(";");

             array.forEach(function(element) {
               var flag = element.split(":");
               if (flag[0] != 'Style') {
                 jQuery("input[name='"+flag[0]+"']").prop('checked', Number(flag[1]));
               } else {
                 jQuery("select[name='"+flag[0]+"']").val(flag[1]).on('change');
               }
             });

             array = data.usrPerms.split(";");

             array.forEach(function(element) {
               var flag = element.split(":");
               jQuery("tr[data-name='"+flag[0]+"']").children('td:eq(0)').children('select').prop('selectedIndex',((flag[1] == 'TRUE')?1:0));
             });

             array = data.userValues.split(";");

             array.forEach(function(element) {
               var flag = element.split(":");

               if (flag[1] == 'semi_colon') {
                 flag[1] = ';';
               }

               var td2 = jQuery("tr[data-name='"+flag[0]+"']").children('td:eq(2)');
               var select2 = jQuery("tr[data-name='"+flag[0]+"']").children('td:eq(2)').children('select');

               if (select2.length === 0) {
                select2 = td2.children('input');

                select2.val(flag[1]);
               } else {
                 if ( isNaN(Number(flag[1])) ){
                   jQuery("tr[data-name='"+flag[0]+"']").children('td:eq(2)').children('select').val(flag[1]);
                 } else {
                   jQuery("tr[data-name='"+flag[0]+"']").children('td:eq(2)').children('select').prop('selectedIndex',Number(flag[1]));
                 }
               }
             });
          });
        });

        $("#addRole").on('click',function() {
           var global_res = '';
           $(".global_settings").each(function() {
              var _val;

              if ($(this).is('select')) {
                _val = $(this).val();
              } else {
                _val = $(this).is(':checked') ? '1' : '0';
              }

              var _name = $(this).attr("name");

              global_res += _name+':'+_val+';'
           });

           var user_perm = '';
           var user_value = '';

           $(".user_settings").each(function() {
              var _name = $(this).data("name");

              var td0 = $(this).children('td:eq(0)');
              var select0 = td0.children('select');

              var _val0 = select0.val();

              var td2 = $(this).children('td:eq(2)');
              var select2 = td2.children('select');

              if (select2.length === 0) {
                select2 = td2.children('input');
              }

              var _val2 = select2.val();

              if (_val2 == ';') {
                _val2 = 'semi_colon';
              }

              user_perm += _name+':'+_val0+';'
              user_value += _name+':'+_val2+';'
           });

           global_res = global_res.slice(0, -1);
           user_perm = user_perm.slice(0, -1);
           user_value = user_value.slice(0, -1);

           bootbox.prompt({
             title: '<i class="fas fa-user-plus mr-2"></i>' + i18next.t("Choose a Role Name"),
             inputType: 'text',
             buttons: {
               cancel: {
                 label: '<i class="fas fa-times"></i> ' + i18next.t("Cancel"),
                 className: 'btn btn-sm btn-outline-secondary'
               },
               confirm: {
                 label: '<i class="fas fa-save"></i> ' + i18next.t("Save"),
                 className: 'btn btn-sm btn-primary'
               }
             },
             callback: function(result){
             if (result) {
                window.CRM.APIRequest({
                  method: 'POST',
                  path: 'userrole/add',
                  data: JSON.stringify({"name": result,"global" : global_res, "userPerms":user_perm,"userValues":user_value})
                },function(data) {
                    if (data && data.status=="success") {
                      addRolesToMainDropdown();
                    } else if (data && data.status=="error") {
                      bootbox.alert({
                          title: '<i class="fas fa-exclamation-circle text-danger mr-2"></i>' + i18next.t("Error"),
                          message: '<div class="alert alert-danger mb-0 text-center">' + i18next.t("You must set another Role Name") + '<br>-- ' + i18next.t("or") + ' --<br>' + i18next.t("this Role Name already exist !!!") + '</div>',
                          size: "small"
                      });
                    }
                });
             }
           }});
        });
    });
