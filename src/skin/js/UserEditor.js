function addProfilesToMainDropdown()
    {
      $("#AllProfiles").empty();
      
      
      window.CRM.APIRequest({
            method: 'POST',
            path: 'userprofile/getall',
      }).done(function(data) {    
        var len = data.length;
      
        for (i=0; i<len; ++i) {
          $("#AllProfiles").append('<li> <a class="changeProfile" data-id="'+data[i].UserProfileId+'"><i class="fa fa-arrow-circle-o-down"></i>'+data[i].UserProfileName+'</a></li>');
          if (i == 0) {
            $("#mainbuttonProfile").data("id",data[i].UserProfileId);
          }          
        }           
      });  
    }

    function addProfiles()
    {
      $('#select-userprofile').find('option').remove();
      
      window.CRM.APIRequest({
            method: 'POST',
            path: 'userprofile/getall',
      }).done(function(data) {    
        var elt = document.getElementById("select-userprofile");
        var len = data.length;
      
        for (i=0; i<len; ++i) {
          var option = document.createElement("option");
          // there is a groups.type in function of the new plan of schema
          option.text = data[i].UserProfileName;
          //option.title = data[i].type;        
          option.value = data[i].UserProfileId;
        
          elt.appendChild(option);
        }           
      });  
      
      addProfilesToMainDropdown();
    }
    
    $(document).ready(function () {
        $("#personSelect").select2({ 
          language: window.CRM.shortLocale,
          minimumInputLength: 2,
          placeholder: " -- "+i18next.t("Person")+" -- "
        });
        
        $(".data-table").DataTable({
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
          var frm_str = '<h3 style="margin-top:-5px">'+i18next.t("Profile management")+'</h3>'
             + '<div>'
                  +'<div class="row div-title">'
                    +'<div class="col-md-4">'
                    + '<span style="color: red">*</span>' + i18next.t("Select your Profile") + ":"                    
                    +'</div>'
                    +'<div class="col-md-8">'
                    +'<select size="6" style="width:100%" id="select-userprofile">'
                    +'</select>'
                   +'</div>'
                  +'</div>'
                  +'<div class="row div-title">'
                    +'<div class="col-md-4"><span style="color: red">*</span>' + i18next.t("Profile Name") + ":</div>"
                    +'<div class="col-md-8">'
                      +"<input type='text' id='ProfileName' placeholder='" + i18next.t("Profile Name") + "' size='30' maxlength='100' class='form-control input-sm'  width='100%' style='width: 100%' required>"
                    +'</div>'
                  +'</div>'
                +'</div>';
                
                var object = $('<div/>').html(frm_str).contents();

              return object
        }
        
        $(document).on('change','#select-userprofile',function() {
          var profileID = $('#select-userprofile').val();
          
          window.CRM.APIRequest({
             method: 'POST',
             path: 'userprofile/get',
             data: JSON.stringify({"profileID": profileID})
          }).done(function(data) {
             $('#ProfileName').val(data.name);
          });
        });
                
        $("#manageProfile").click(function() {
          var modal = bootbox.dialog({
             message: BootboxContent(),
             buttons: [
              {
               label: i18next.t("Close"),
               className: "btn btn-success",
               callback: function() {               
               }
              },
              {
               label: i18next.t("Delete"),
               className: "btn btn-danger",
               callback: function() {
                  var profileID = $('#select-userprofile').val();
                  
                  bootbox.confirm(i18next.t("Are you sure, you want to delete this Profile ?"), function(result){ 
                    if (result) {
                      window.CRM.APIRequest({
                         method: 'POST',
                         path: 'userprofile/delete',
                         data: JSON.stringify({"profileID": profileID})
                      }).done(function(data) {
                        addProfiles();
                      });
                    }
                  });
                  return false;
               }
              },
              {
               label: i18next.t("Rename"),
               className: "btn btn-primary",
               callback: function() {
                  var profileID = $('#select-userprofile').val();
                  var name = $('#ProfileName').val();
                  
                  window.CRM.APIRequest({
                     method: 'POST',
                     path: 'userprofile/rename',
                     data: JSON.stringify({"profileID": profileID,"name":name})
                  }).done(function(data) {
                    addProfiles();
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
         
         addProfiles();
        });

 
        $('body').on('click','.changeProfile', function(){ 
          var profileID = $(this).data("id");
          
          window.CRM.APIRequest({
             method: 'POST',
             path: 'userprofile/get',
             data: JSON.stringify({"profileID": profileID})
          }).done(function(data) {
             var array = data.global.split(";");
             
             array.forEach(function(element) {
               var flag = element.split(":");
               if (flag[0] != 'Style') {
                 jQuery("input[name='"+flag[0]+"']").prop('checked', Number(flag[1]));
               } else {
                 jQuery("select[name='"+flag[0]+"']").val(flag[1]).change();
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
                 jQuery("tr[data-name='"+flag[0]+"']").children('td:eq(2)').children('select').prop('selectedIndex',Number(flag[1]));
                }
             });
          });
        });
        
        $("#addProfile").click(function() {
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
           
           bootbox.prompt(i18next.t("Choose your Profile Name"), function(result){ 
             if (result) {
                window.CRM.APIRequest({
                  method: 'POST',
                  path: 'userprofile/add',
                  data: JSON.stringify({"name": result,"global" : global_res, "userPerms":user_perm,"userValues":user_value})
                }).done(function(data) {
                    if (data && data.status=="success") {
                      addProfilesToMainDropdown();
                    } else if (data && data.status=="error") {
                      bootbox.alert({
                          title:i18next.t("Error"),
                          message: i18next.t("<center>You must set another Profile Name <br>-- or --<br> this Profile settings yet exist !!!</center>"),
                          size: "small"
                      });
                    }
                });              
             }
           });
        });
    });