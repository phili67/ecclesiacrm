i18nextOpt = {
  lng:window.CRM.shortLocale,
  nsSeparator: false,
  keySeparator: false,
  pluralSeparator:false,
  contextSeparator:false,
  fallbackLng: false,
  resources: { }
};

i18nextOpt.resources[window.CRM.shortLocale] = {
  translation: window.CRM.i18keys
};
i18next.init(i18nextOpt);

$("document").ready(function(){
    $(".multiSearch").select2({
        language: window.CRM.shortLocale,
        minimumInputLength: 2,
        ajax: {
            url: function (params){
              return window.CRM.root + "/api/search/" + params.term;
            },
            dataType: 'json',
            delay: 250,
            data: "",
            processResults: function (data, params) {
              return {results: data};
            },
            cache: true
        }
    });
    $(".multiSearch").on("select2:select",function (e) { window.location.href= e.params.data.uri;});

    window.CRM.system.runTimerJobs();
       
    $(".date-picker").datepicker({format:window.CRM.datePickerformat, language: window.CRM.lang});

    $(".maxUploadSize").text(window.CRM.maxUploadSize);
  
  
    /* IMPORTANT : be careful
       You have to be careful with this part of code !!!!!
       this part of code will work in two different js code : PersonView.js and GroupList.js */
    $(document).on("click", ".emptyCart", function (e) {
      window.CRM.cart.empty(function(data){
        window.CRM.cart.refresh();
        
        if (window.CRM.dataTableList) {
            window.CRM.dataTableList.ajax.reload();
            window.CRM.dataTableList.ajax.reload();
        } else if (data.cartPeople) {// this part should be written like this, the code will crash at this point without this test and crash the js code
          console.log(data.cartPeople);
          $(data.cartPeople).each(function(index,data){
            personButton = $("a[data-cartpersonid='" + data + "']");
            $(personButton).addClass("AddToPeopleCart");
            $(personButton).removeClass("RemoveFromPeopleCart");
            $('span i:nth-child(2)',personButton).removeClass("fa-remove");
            $('span i:nth-child(2)',personButton).addClass("fa-cart-plus");
          });
        }
      });
    });
    
    /* IMPORTANT : be careful
       This will work in cartToGroup code */
    function BootboxContentCartTogroup(){    
      var frm_str = '<form id="some-form">'
        +'<table border=0 cellpadding=2 width="100%">'
        +'<tr>'
        +'<td>'+i18next.t('Select the method to add to a group')+'   </td>'
        +'<td><select id="GroupSelector" class="form-control">'
        +'<option>'+i18next.t('Select an existing Group')+'</option>'
        +'<option>'+i18next.t('or Create a new Group from the Cart')+'</option>'
        +'</select>'
        +'</td>'
        +'</tr>'
        +'</table>'
        +'<hr/>'
        +'<div id="GroupSelect">'
        +'    <p align="center">'+i18next.t('Select the group to which you would like to add your cart')+':</p>'
        +'      <table align="center">'
        +'        <tr>'
        +'          <td class="LabelColumn">'+i18next.t('Select Group')+':</td>'
        +'          <td class="TextColumn">'
        +'            <select id="PopupGroupID" name="PopupGroupID" style="width:100%" class="form-control">'
        +'            </select>'
        +'          </td>'
        +'        </tr>'
        +'        <tr><td colspan="2">&nbsp;</td></tr>'
        +'        <tr>'
        +'          <td class="LabelColumn">'+i18next.t('Select Role')+':</td>'
        +'          <td class="TextColumn">'
        +'            <select name="GroupRole" id="GroupRole" style="width:100%" class="form-control">'
        +'                <option>'+i18next.t('None')+'</option>'
        +'            </select>'
        +'          </td>'
        +'        </tr>'
        +'      </table>'
        +'      <br>'
        +'</div>'
        +'<div id="GroupCreation">'
        +'      <p align="center">'
        +'        <table border=0 cellpadding=2 width="100%">'
        +'        <tr>'
        +'           <td>'+ i18next.t('Group Name') + ':</td>'
        +'           <td><input type="text" id="GroupName" value="" size="30" maxlength="100" class="form-control"  width="100%" style="width: 100%" placeholder="'+i18next.t("Default Name Group")+'" required></td>'
        +'        </tr>'        
        +'        </table>'
        +'      </p>'
        +'</div>';

        var object = $('<div/>').html(frm_str).contents();

        return object
    }
    
    function addGroups()
    {
        window.CRM.APIRequest({
            path:"groups/",
            method:"GET"
        }).done(function(data) {
            var Groups = data.Groups;                 
            var elt = document.getElementById("PopupGroupID");
            if (elt != null) {
              var len = Groups.length;

              // We add the none option
              var option = document.createElement("option");
              option.text = i18next.t("None");
              option.value = 0;
              option.title = ""; 
              elt.appendChild(option);
      
              for (i=0; i<len; ++i) {
                var option = document.createElement("option");
                // there is a groups.type in function of the new plan of schema
                option.text = Groups[i].Name;
                option.title = Groups[i].RoleListId;        
                option.value = Groups[i].Id;
                elt.appendChild(option);
              }
            }       
      });  
    }
    
    // I have to do this because EventGroup isn't yet present when you load the page the first time
    $(document).on('change','#PopupGroupID',function () {
     var e = document.getElementById("PopupGroupID");
     
     if (e.selectedIndex > 0) {
         var option = e.options[e.selectedIndex];
         var GroupID = option.value;
   
          window.CRM.APIRequest({
              path:"groups/"+GroupID+"/roles",
              method:"GET"
          }).done(function(data) {
              var ListOptions = data.ListOptions;                 
              $("#GroupRole").empty();        
              var elt = document.getElementById("GroupRole");
              if (elt != null) {
                var len = ListOptions.length;

                // We add the none option
                var option = document.createElement("option");
                option.text = i18next.t("None");
                option.value = 0;
                option.title = ""; 
                elt.appendChild(option);
    
                for (i=0; i<len; ++i) {
                  var option = document.createElement("option");
                  // there is a groups.type in function of the new plan of schema
                  option.text = i18next.t(ListOptions[i].OptionName);
                  option.value = ListOptions[i].OptionId;
                  elt.appendChild(option);
                }
              }
          });
      } 
    });
  
    // I have to do this because EventGroup isn't yet present when you load the page the first time
    $(document).on('change','#GroupSelector',function () {
       var e = document.getElementById("GroupSelector");
       if (e.selectedIndex == 0) {
           $("#GroupCreation").hide();
           $("#GroupSelect").show();
       } else {
           $("#GroupSelect").hide();
           $("#GroupCreation").show();           
       }
    });
    
    
    $(document).on("click", "#emptyCartToEvent", function (e) {
      window.CRM.cart.emptytoEvent(function(data){
        window.CRM.cart.refresh();
        location.href = window.CRM.root + '/ListEvents.php';
      });
    });

    
    $(document).on("click", "#emptyCartToGroup", function (e) {
      var modal = bootbox.dialog({
         message: BootboxContentCartTogroup,
         title: i18next.t("Add Cart to Group"),
         buttons: [
          {
           label: i18next.t("Save"),
           className: "btn btn-primary pull-left",
           callback: function() {
             var e = document.getElementById("GroupSelector");
             if (e.selectedIndex == 0) {
                 var e = document.getElementById("PopupGroupID");
                 
                 if (e.selectedIndex > 0) {
                     var option = e.options[e.selectedIndex];
                     var GroupID = option.value;             

                     var e = document.getElementById("GroupRole");
                     var option = e.options[e.selectedIndex];
                     var RoleID = option.value;
                     
                     window.CRM.APIRequest({
                        method: 'POST',
                        path: 'cart/emptyToGroup',
                        data: JSON.stringify({"groupID":GroupID,"groupRoleID":RoleID})
                     }).done(function(data) {
                          window.CRM.cart.refresh();
                          location.href = window.CRM.root + '/v2/group/' + GroupID + '/view';
                     });
                      
                      return true
                } else {
                    var box = bootbox.dialog({title: "<span style='color: red;'>"+i18next.t("Error")+"</span>",message : i18next.t("You have to select one group and a group role if you want")});
                
                    setTimeout(function() {
                        // be careful not to call box.hide() here, which will invoke jQuery's hide method
                        box.modal('hide');
                    }, 3000);
                    
                    return false;
                }                    
              } else {
          
                  var newGroupName = document.getElementById("GroupName").value;
                  
                  if (newGroupName) {
                      window.CRM.APIRequest({
                        method: 'POST',
                        path: 'cart/emptyToNewGroup',               //call the groups api handler located at window.CRM.root
                        data: JSON.stringify({'groupName':newGroupName}),                      // stringify the object we created earlier, and add it to the data payload
                      }).done(function (data) {                               //yippie, we got something good back from the server
                          window.CRM.cart.refresh();
                          location.href = window.CRM.root + '/v2/group/'+data.Id+'/view';
                      });
                      
                      return true;
                  } else {
                    var box = bootbox.dialog({title: "<span style='color: red;'>"+i18next.t("Error")+"</span>",message : i18next.t("You have to set a Group Name")});
                
                    setTimeout(function() {
                        // be careful not to call box.hide() here, which will invoke jQuery's hide method
                        box.modal('hide');
                    }, 3000);
                    
                    return false;
                  }
              }
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
       
       // we hide by default the GroupCreation
       $("#GroupCreation").hide();
       
       // we add the group and roles
       addGroups();
    });
    
    window.CRM.cart.refresh();
    window.CRM.dashboard.refresh();
    DashboardRefreshTimer=setInterval(window.CRM.dashboard.refresh, window.CRM.iDasbhoardServiceIntervalTime * 1000);
    
    // all bootbox are now localized
    bootbox.setDefaults({locale: window.CRM.lang});
});

$(document).on("click", "#deleteCart", function (e) {
  window.CRM.cart.delete(function(data) {
    var path = location.href;
    path = path.substring(path.lastIndexOf("/") + 1);
    path = path.split("?")[0].split("#")[0]; 

    if (data.status == "failure")
    {
      var box = window.CRM.DisplayAlert(i18next.t("Error text"),data.message);
      
      setTimeout(function() {
        // be careful not to call box.hide() here, which will invoke jQuery's hide method
        box.modal('hide');
        
        if ((path == "PersonView.php" || path == "CartView.php") && data != 'nothing was done') {
          location.reload();
        }
      }, 7000);
    } else {
      if (path == "PersonView.php" && data != 'nothing was done') {
          location.reload();
      }
    }    
  });
});

$(document).on("click", "#deactivateCart", function (e) {
  window.CRM.cart.deactivate(function(data) {
    var path = location.href;
    path = path.substring(path.lastIndexOf("/") + 1);
    path = path.split("?")[0].split("#")[0]; 

    if (data.status == "failure")
    {
      var box = window.CRM.DisplayAlert(i18next.t("Error text"),data.message);
      
      setTimeout(function() {
        // be careful not to call box.hide() here, which will invoke jQuery's hide method
        box.modal('hide');
        
        if (path == "PersonView.php" && data != 'nothing was done') {
          location.reload();
        }
      }, 7000);
    } else {
      if ((path == "PersonView.php" || path == "CartView.php") && data != 'nothing was done') {
          location.reload();
      }
    }    
  });
});

function suspendSession(){
  $.ajax({
    method: 'HEAD',
    url: window.CRM.root + "/api/session/lock",
    statusCode: {
      200: function() {
        window.open(window.CRM.root + "/Login.php");
      },
      404: function() {
        window.CRM.DisplayErrorMessage(url, {message: error});
      },
      500: function() {
        window.CRM.DisplayErrorMessage(url, {message: error});
      }
    }
  });     
};

function BootboxContentRegister(data){
  var frm_str = '<div class="box box-warning">'
    + '  <div class="box-body">'
    + '  ' + i18next.t('If you need to make changes to registration data, go to ') + '<a href="'+ window.CRM.root + '/SystemSettings.php">'+ i18next.t('Admin->Edit General Settings') + '</a>'
    + '  </div>'
    + '</div>'
    + '<div class="box box-primary">'
    + '  <div class="box-header">'
    +    i18next.t('Please register your copy of EcclesiaCRM by checking over this information and pressing the Send button.')
    + '  '
    +    i18next.t('This information is used only to track the usage of this software.')
    + '  </div>'
    + '  <div class="box-body">'
    +      i18next.t('Church Name') + ':' + data.ChurchName + '<br>'
    +      i18next.t('Version') + ':' + data.InstalledVersion + '<br>'
    +      i18next.t('Address') + ':' + data.ChurchAddress + '<br>'
    +      i18next.t('City') + ':' + data.ChurchCity + '<br>'
    +      i18next.t('State') + ':' + data.ChurchState + '<br>'
    +      i18next.t('Zip') + ':' + data.ChurchZip + '<br>'
    +      i18next.t('Country') + ':' + data.ChurchCountry + '<br>'
    +      i18next.t('Church Email') + ':' + data.ChurchEmail + '<br>'
    +      'EcclesiaCRM ' + i18next.t('Base URL') + ':' + data.EcclesiaCRMURL + '<br>'
    +      '<br>' + i18next.t('Message')
    +      '<textarea class="form-control" id="registeremailmessage" name="emailmessage" rows="10" cols="72">' + data.EmailMessage + '</textarea>'
    +      '<input type="hidden" name="EcclesiaCRMURL" value="' + data.EcclesiaCRMURL + '"/>'
    + '  </div>'
    + '</div>';

    var object = $('<div/>').html(frm_str).contents();

    return object
}

$(document).on("click", "#registerSoftware", function (e) {
    window.CRM.APIRequest({
      method: 'POST',
      path:"register/getRegistredDatas"
    }).done(function(data) {
        var modal = bootbox.dialog({
         message: BootboxContentRegister(data),
         title: i18next.t("Software Registration"),
         buttons: [
          {
             label: i18next.t("Send"),
             className: "btn btn-primary pull-left",
             callback: function() {
                $.ajax({
                  type: "POST",
                  url: window.CRM.root + "/api/register",
                  data: {
                    emailmessage: $("#registeremailmessage").val(),
                    EcclesiaCRMURL: $("input[name=EcclesiaCRMURL]").val()
                  },
                  success: function (data) {
                    alert(i18next.t('Your software is now registered. Thank you !'));
                    location.reload();
                  }
                });
            }
          },
          {
             label: i18next.t("Cancel"),
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

