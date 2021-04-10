/*
 * EcclesiaCRM JavaScript Object Model Initialization Script
 */

    window.CRM.APIRequest = function(options) {
      if (!options.method)
      {
        options.method="GET"
      }
      options.url=window.CRM.root+"/api/"+options.path;
      options.dataType = 'json';
      options.contentType =  "application/json";
      return $.ajax(options);
    }

    window.CRM.DisplayErrorMessage = function(endpoint, error) {
      if (window.CRM.sLogLevel > 100)
        return;
      if (endpoint.indexOf("/api/dashboard") !== -1) {// we are in the case, we're logout
        location.reload();
        return;
      }

      message = "<p>" + i18next.t("Error making API Call to") + ": " + endpoint +
        "</p><p>" + i18next.t("Error text") + ": " + i18next.t(error.message);
      if (error.trace)
      {
        message += "</p>" + i18next.t("Stack Trace") + ": <pre>"+JSON.stringify(error.trace, undefined, 2)+"</pre>";
      }
      bootbox.alert({
        title:  i18next.t("ERROR"),
        message: message
      });
    };

    window.CRM.DisplayAlert = function(title,message,callback) {
      return bootbox.alert({
        title:  title,
        message:message
      });
    }

    window.CRM.DisplayNormalAlert = function(title,message,callback) {
      alert(title + "\n\n" + message);
    }

    window.CRM.renderMailchimpLists = function  () {

        window.CRM.APIRequest({
          method: 'GET',
          path: 'mailchimp/lists'
        }).done(function(data) {

          if (data.isActive) {
            var len = data.MailChimpLists.length;

            // now we empty the menubar lists
            $(".lists_class_menu").removeClass("hidden");

            var real_listMenu = $(".lists_class_menu").find (".nav-treeview");

            real_listMenu.html("");
            var listItems  = "";

            for (i=0;i<len;i++) {
              var list = data.MailChimpLists[i];

              listItems +=  '<li class="nav-item listName' + list.id + '"><a href="' + window.CRM.root + '/v2/mailchimp/managelist/' + list.id + '" class="nav-link "> <i class="fa fa-circle-o"></i> <p>'+ list.name + '</p></a>'
                    + '</li>';
            }

            real_listMenu.html(listItems);

            if ( data.firstLoaded == true ) {
              window.CRM.notify('fa fa-info-circle',i18next.t("Mailchimp"), i18next.t("All the lists are now loaded in Ecclesia<b>CRM</b>.<br><b>If you want to manage them, click this notification !</b>"), window.CRM.root + '/v2/mailchimp/dashboard' ,'success',"top",50000);
            }
          }

        });
    }

    window.CRM.showGlobalMessage = function (message, callOutClass) {
        $("#globalMessageText").text(message);
        $("#globalMessageCallOut").removeClass("callout-danger");
        $("#globalMessageCallOut").removeClass("callout-warning");
        $("#globalMessageCallOut").removeClass("callout-info");
        $("#globalMessageCallOut").removeClass("callout-success");
        $("#globalMessageCallOut").addClass("callout-"+callOutClass);
        $("#globalMessage").show("slow");
    }


    window.CRM.dialogLoadingFunction =  function (message) {
      window.CRM.dialogLoading = bootbox.dialog({ message: '<div class="text-center"><i class="fa fa-spin fa-spinner"></i> ' + message + '</div>' });
   }

   window.CRM.closeDialogLoadingFunction = function () {
     if (window.CRM.dialogLoading != null) {
        window.CRM.dialogLoading.modal('hide');
      }
   }

   window.CRM.notify = function(icon,title,message,link,type,place,delay,target,horizontal) {
        if (type == 'success') {
            type='bg-success';
        } else if (type == 'warning') {
            type='bg-warning';
        } else if (type == 'info') {
            type='bg-lightblue';
        } else if (type == 'error') {
            type='bg-red';
        }

        if (horizontal === undefined) {
            horizontal = 'Right';
        }

        if (link != null) {
            message = message + ' <a href="' + link + '" target="' + target + '"><i class="fa fa-arrow-circle-right"></i></a>';
        }

       $(document).Toasts('create', {
           position: place+horizontal,
           title: '<i class="'+ icon +'"></i> ' + title,
           body: message,
           delay: delay,
           type: type,
           autohide: true,
           animation:true,
           class:type
       })
    }

    window.CRM.VerifyThenLoadAPIContent = function(url) {
      var error = i18next.t("There was a problem retrieving the requested object");
      $.ajax({
        method: 'HEAD',
        url: url,
        async: false,
        statusCode: {
          200: function() {
            window.open(url);
          },
          404: function() {
            window.CRM.DisplayErrorMessage(url, {message: error});
          },
          500: function() {
            window.CRM.DisplayErrorMessage(url, {message: error});
          }
        }
      });
    }

    window.CRM.cart={
      'empty' : function (callback)
      {
        window.CRM.APIRequest({
          method: "DELETE",
          path: "cart/"
        }).done(function (data) {
          if (callback)
          {
            callback(data);
          }
          window.CRM.cart.refresh();
          // we update the cart button
          if (data.PeopleCart !== undefined && data.FamiliesCart != undefined && data.GroupsCart != undefined)
            window.CRM.cart.updatePage(data.PeopleCart,data.FamiliesCart, data.GroupsCart);
        });
      },
      'emptyCart': function () {
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
      },
      'emptyCartToEvent' : function () {
          window.CRM.cart.emptytoEvent(function(data){
              window.CRM.cart.refresh();
              location.href = window.CRM.root + '/ListEvents.php';
          });
      },
      'emptyCartToGroup' : function () {
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

          function BootboxContentCartTogroup (){
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

          var modal = bootbox.dialog({
              message: BootboxContentCartTogroup(),
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

          // we hide by default the GroupCreation
          $("#GroupCreation").hide();

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

          // we add the group and roles
          addGroups();
      },
      'deactivate' : function (callback)
      {
        bootbox.confirm({
            title: i18next.t("Do you really want to deactivate the persons?"),
            message: i18next.t("This action can be undone !!!!"),
            buttons: {
                cancel: {
                    label:  i18next.t('No'),
                    className: 'btn-primary'
                },
                confirm: {
                    label:  i18next.t("Yes : if you're sure"),
                    className: 'btn-danger'
                }
            },
            callback: function (result) {
              if (result) {
                window.CRM.APIRequest({
                  method: "POST",
                  path: "cart/deactivate"
                }).done(function (data) {
                  if (callback)
                  {
                    callback(data);
                    window.CRM.cart.refresh();
                    window.CRM.cart.updateLocalePage();// sometimes we've to reload the page or something else
                  }
                  else
                  {
                    window.CRM.cart.refresh();
                  }
                });
              } else {
                callback('nothing was done');
              }
            }
        });

      },
      'delete' : function (callback)
      {
        bootbox.confirm({
            title: i18next.t("Do you really want to delete the persons from the CRM?"),
            message: i18next.t("This action cannot be undone !!!!"),
            buttons: {
                cancel: {
                    label:  i18next.t('No'),
                    className: 'btn-primary'
                },
                confirm: {
                    label:  i18next.t("Yes : if you're sure"),
                    className: 'btn-danger'
                }
            },
            callback: function (result) {
              if (result) {
                window.CRM.APIRequest({
                  method: "POST",
                  path: "cart/delete"
                }).done(function (data) {
                  if (callback)
                  {
                    callback(data);
                    window.CRM.cart.refresh();
                    window.CRM.cart.updateLocalePage();// sometimes we've to reload the page or something else
                  }
                  else
                  {
                    window.CRM.cart.refresh();
                  }
                });
              } else {
                callback('nothing was done');
              }
            }
        });

      },
      'emptyToGroup' : function (callback)
      {
        window.CRM.groups.promptSelection({Type:window.CRM.groups.selectTypes.Group|window.CRM.groups.selectTypes.Role},function(selectedRole){
          window.CRM.APIRequest({
            method: 'POST',
            path: 'cart/emptyToGroup',
            data: JSON.stringify({"groupID":selectedRole.GroupID,"groupRoleID":selectedRole.RoleID})
          }).done(function(data) {
            window.CRM.cart.refresh();
            if(callback)
            {
                callback(data);
            }
          });
        });
      },
      'emptytoFamily' : function ()
      {

      },
      'emptytoEvent' : function (callback)
      {
        window.CRM.APIRequest({
          method: 'GET',
          path: 'events/names',
        }).done(function(eventNames) {
           var lenType = eventNames.length;
           var options = new Array();

           var boxOptions ={
             title: i18next.t('Select the event to which you would like to add your cart'),
             message: '<div class="modal-body">',
             buttons: {
               addEvent: {
                   label: i18next.t('Create First A New Event'),
                   className: 'btn-info',
                   callback: function() {
                      location.href = window.CRM.root + '/v2/calendar';
                   }
               },
               cancel: {
                   label: i18next.t('Cancel'),
                   className: 'btn-danger'
               },
               confirm: {
                   label: i18next.t('Add to Event'),
                   className: 'btn btn-primary',
                   callback: function() {
                        var e = document.getElementById("eventChosen");
                        var EventID = e.options[e.selectedIndex].value;

                        window.CRM.APIRequest({
                          method: 'POST',
                          path: 'cart/emptyToEvent',
                          data: JSON.stringify({"eventID":EventID})
                        }).done(function(data) {
                           if(callback)
                           {
                             callback(data);
                           }
                        });
                   }
               }
             }
          };

          boxOptions.message +='<center>'+i18next.t('You can add the content of the cart to the selected event below<br> - OR - <br>Create first an event and add them after.')+'</center><br>';
          boxOptions.message +='<select class="bootbox-input bootbox-input-select form-control" id="eventChosen">';
          for (i=0;i<lenType;i++) {
             boxOptions.message +='<option value="'+eventNames[i].eventTypeID+'">'+eventNames[i].name+'</option>';
           }

          boxOptions.message +='</select>\
                             </div>';

          bootbox.dialog(boxOptions).show();
        });
      },
      'addPerson' : function (Persons, callback)
      {
        window.CRM.APIRequest({
          method: 'POST',
          path: 'cart/',
          data: JSON.stringify({"Persons":Persons})
        }).done(function(data) {
          window.CRM.cart.refresh();
          if(callback)
          {
            callback(data);
          }
        });
      },
      'intersectPerson' : function (Persons, callback)
      {
        window.CRM.APIRequest({
          method: 'POST',
          path: 'cart/interectPerson',
          data: JSON.stringify({"Persons":Persons})
        }).done(function(data) {
          window.CRM.cart.refresh();
          if(callback)
          {
            callback(data);
          }
        });
      },
      'removePerson' : function (Persons, callback)
      {
         window.CRM.APIRequest({
          method: 'DELETE',
          path:'cart/',
          data: JSON.stringify({"Persons":Persons})
        }).done(function(data) {
          window.CRM.cart.refresh();
          if(callback)
          {
            callback(data);
          }
        });
      },
      'addFamily' : function (FamilyID, callback)
      {
         window.CRM.APIRequest({
          method: 'POST',
          path:'cart/',
          data: JSON.stringify({"Family":FamilyID})
        }).done(function(data) {
            window.CRM.cart.refresh();
            if(callback)
            {
              callback(data);
            }
        });
      },
      'addFamilies' : function (Families, callback)
        {
            window.CRM.APIRequest({
                method: 'POST',
                path:'cart/',
                data: JSON.stringify({"Families":Families})
            }).done(function(data) {
                window.CRM.cart.refresh();
                if(callback)
                {
                    callback(data);
                }
            });
      },
      'removeFamily' : function (FamilyID, callback)
      {
         window.CRM.APIRequest({
          method: 'POST',
          path:'cart/',
          data: JSON.stringify({"removeFamily":FamilyID})
        }).done(function(data) {
            window.CRM.cart.refresh();
            if(callback)
            {
              callback(data);
            }
        });
      },
      'removeFamilies' : function (Families, callback)
        {
            window.CRM.APIRequest({
                method: 'POST',
                path:'cart/',
                data: JSON.stringify({"removeFamilies":Families})
            }).done(function(data) {
                window.CRM.cart.refresh();
                if(callback)
                {
                    callback(data);
                }
            });
      },
      'addGroup' : function (GroupID, callback)
      {
         window.CRM.APIRequest({
          method: 'POST',
          path: 'cart/',
          data: JSON.stringify({"Group": GroupID})
        }).done(function(data) {
            window.CRM.cart.refresh();
            if(callback)
            {
              callback(data);
            }

        });
      },
      'addGroups' : function (Groups, callback)
        {
            window.CRM.APIRequest({
                method: 'POST',
                path: 'cart/',
                data: JSON.stringify({"Groups": Groups})
            }).done(function(data) {
                window.CRM.cart.refresh();
                if(callback)
                {
                    callback(data);
                }

            });
       },
      'removeGroup' : function (GroupID, callback)
      {
         window.CRM.APIRequest({
          method: 'POST',
          path: 'cart/removeGroup',
          data: JSON.stringify({"Group":GroupID})
        }).done(function(data) {
            window.CRM.cart.refresh();
            if(callback)
            {
              callback(data);
            }

        });
      },
      'removeGroups' : function (Groups, callback) {
            window.CRM.APIRequest({
                method: 'POST',
                path: 'cart/removeGroups',
                data: JSON.stringify({"Groups":Groups})
            }).done(function(data) {
                window.CRM.cart.refresh();
                if(callback)
                {
                    callback(data);
                }

            });
      },
      'addStudentGroup' : function (GroupID, callback)
      {
         window.CRM.APIRequest({
          method: 'POST',
          path: 'cart/',
          data: JSON.stringify({"studentGroup":GroupID})
        }).done(function(data) {
            window.CRM.cart.refresh();
            if(callback)
            {
              callback(data);
            }

        });
      },
      'addAllStudents' : function (callback)
        {
            window.CRM.APIRequest({
                method: 'POST',
                path: 'cart/addAllStudents',
            }).done(function(data) {
                window.CRM.cart.refresh();
                if(callback)
                {
                    callback(data);
                }

            });
      },
      'removeStudentGroup' : function (GroupID, callback)
      {
         window.CRM.APIRequest({
          method: 'POST',
          path: 'cart/removeStudentGroup',
          data: JSON.stringify({"Group":GroupID})
        }).done(function(data) {
            window.CRM.cart.refresh();
            if(callback)
            {
              callback(data);
            }

        });
      },
      'removeAllStudents' : function (callback)
      {
            window.CRM.APIRequest({
                method: 'POST',
                path: 'cart/removeAllStudents',
            }).done(function(data) {
                window.CRM.cart.refresh();
                if(callback)
                {
                    callback(data);
                }

            });
      },
      'addTeacherGroup' : function (GroupID, callback)
      {
         window.CRM.APIRequest({
          method: 'POST',
          path: 'cart/',
          data: JSON.stringify({"teacherGroup":GroupID})
        }).done(function(data) {
            window.CRM.cart.refresh();
            if(callback)
            {
              callback(data);
            }

        });
      },
      'addAllTeachers' : function (callback)
        {
            window.CRM.APIRequest({
                method: 'POST',
                path: 'cart/addAllTeachers',
            }).done(function(data) {
                window.CRM.cart.refresh();
                if(callback)
                {
                    callback(data);
                }

            });
      },
      'removeTeacherGroup' : function (GroupID, callback)
      {
         window.CRM.APIRequest({
          method: 'POST',
          path: 'cart/removeTeacherGroup',
          data: JSON.stringify({"Group":GroupID})
        }).done(function(data) {
            window.CRM.cart.refresh();
            if(callback)
            {
              callback(data);
            }

        });
      },
      'removeAllTeachers' : function (callback)
        {
            window.CRM.APIRequest({
                method: 'POST',
                path: 'cart/removeAllTeachers',
            }).done(function(data) {
                window.CRM.cart.refresh();
                if(callback)
                {
                    callback(data);
                }

            });
      },
      'updateLocalePage' : function () {
        // broadcaster
        $.event.trigger({
            type: "updateLocalePageMessage"
        });
      },
      'refresh' : function () {
          window.CRM.APIRequest({
            method: 'POST',
            path:"register/isRegisterRequired"
          }).done(function(data) {
            if (data.Register) {
               window.CRM.notify('fa  fa-info-circle',i18next.t("Register")+".",i18next.t("Register your software to EcclesiaCRM team.") + "<br><b>"  + i18next.t("Simply click this") + " <a href=\"#\" id=\"registerSoftware\"><i class=\"fa fa-arrow-circle-right\"></i></a> " + i18next.t("to register your software") +  ".</b>", null, "warning","top",10000,'_blank',"Left");
            }
          });

          window.CRM.APIRequest({
           method: 'POST',
           path:"systemupgrade/isUpdateRequired"
          }).done(function(data) {
            if (data.Upgrade) {
               window.CRM.notify('fa  fa-info-circle',i18next.t("New Release")+".",i18next.t("Installed version")+" : "+data.installedVersion+'      '+i18next.t("New One")+" : "+data.latestVersion.name+'<br><b>'+i18next.t("To upgrade simply click this Notification")+"</b>", window.CRM.root+'/UpgradeCRM.php',"info","bottom",60000,'_blank');
            }
          });

          if (window.CRM.PageName.indexOf("UserPasswordChange.php") !== -1 && window.CRM.showCart) {// the first time it's unusefull
            return;
          }

          if (window.CRM.showCart == false)// in this cas all the broadcast system is deactivated
            return;

          window.CRM.APIRequest({
            method: 'GET',
            path:"cart/"
          }).done(function(data) {
            window.CRM.cart.updatePage(data.PeopleCart,data.FamiliesCart, data.GroupsCart);
            //window.scrollTo(0, 0);
            $("#iconCount").text(data.PeopleCart.length);

            var cartDropdownMenu;
            if (data.PeopleCart.length > 0) {
              cartDropdownMenu = '\
                <div id="showWhenCartNotEmpty">\
                    <a href="' + window.CRM.root+ '/v2/cart/view" class="dropdown-item">\
                        <i class="fa fa-shopping-cart text-green"></i> ' + i18next.t("View Cart") + '\
                    </a>\
                    <div class="dropdown-divider"></div>\
                    <a href="#" class="dropdown-item emptyCart" >\
                        <i class="fa fa-eraser"></i> ' + i18next.t("Empty Cart") + ' \
                    </a>\
                    <div class="dropdown-divider"></div>\
                    <a href="#" id="emptyCartToGroup" class="dropdown-item">\
                        <i class="fa fa-tag text-info"></i> ' + i18next.t("Empty Cart to Group") + '\
                    </a>\
                    <div class="dropdown-divider"></div>\
                    <a href="' + window.CRM.root+ '/CartToFamily.php" class="dropdown-item">\
                        <i class="fa fa fa-users text-info"></i> ' + i18next.t("Empty Cart to Family") + '\
                    </a>\
                    <div class="dropdown-divider"></div>\
                    <a href="#" id="emptyCartToEvent" class="dropdown-item">\
                        <i class="fa fa fa-ticket text-info"></i> ' + i18next.t("Empty Cart to Event") + '\
                    </a>\
                    <div class="dropdown-divider"></div>\
                    <a href="' + window.CRM.root+ '/v2/map/0" class="dropdown-item">\
                        <i class="fa fa-map-marker text-info"></i> ' + i18next.t("Map Cart") + '\
                    </a>\
                    <div class="dropdown-divider"></div>\
                    <a href="#" id="deactivateCart" class="dropdown-item">\
                       <i class="fa fa-trash text-warning"></i> '+ i18next.t("Deactivate Persons From Cart")+ '\
                    </a>\
                    <div class="dropdown-divider"></div>\
                    <a href="#" id="deleteCart" class="dropdown-item">\
                       <i class="fa fa-trash text-danger"></i> '+ i18next.t("Delete Persons From the CRM")+ '\
                    </a>\
                    <!--li class="footer"><a href="#">' + i18next.t("View all") + '</a></li-->\
                </div>'
          }
            else {
              cartDropdownMenu = '\
                <span class="dropdown-item dropdown-header">' + i18next.t("Your Cart is Empty" ) + '</span>';

            }
          $("#cart-dropdown-menu").html(cartDropdownMenu);
          $("#CartBlock")
            .animate({'left':(-10)+'px'},30)
            .animate({'left':(+10)+'px'},30)
            .animate({'left':(0)+'px'},30);
          });
      },
      'updatePage' : function (cartPeople, familiesCart, groupsCart){

        // broadcaster
        $.event.trigger({
            type: "updateCartMessage",
            people:cartPeople,
            families:familiesCart,
            groups:groupsCart
        });
      }
    }

    window.CRM.register = function () {
        function BootboxContentRegister(data){
            var frm_str = '<div class="card card-warning">'
                + '  <div class="card-body">'
                + '  ' + i18next.t('If you need to make changes to registration data, go to ') + '<a href="'+ window.CRM.root + '/SystemSettings.php">'+ i18next.t('Admin->Edit General Settings') + '</a>'
                + '  </div>'
                + '</div>'
                + '<div class="card card-primary">'
                + '  <div class="card-header">'
                +    i18next.t('Please register your copy of EcclesiaCRM by checking over this information and pressing the Send button.')
                + '  '
                +    i18next.t('This information is used only to track the usage of this software.')
                + '  </div>'
                + '  <div class="card-body">'
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

            return object;
        }

        window.CRM.APIRequest({
            method: 'POST',
            path:"register/getRegistredDatas"
        }).done(function(data) {
            var modal = bootbox.dialog({
                message: BootboxContentRegister(data),
                title: i18next.t("Software Registration"),
                size: 'large',
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
    }

    window.CRM.kiosks = {
        assignmentTypes: {
            "1":"Event Attendance",
            "2":"Self Registration",
            "3":"Self Checkin",
            "4":"General Attendance"
        },
        reload: function(id)
        {
          window.CRM.APIRequest({
            "path":"kiosks/"+id+"/reloadKiosk",
            "method":"POST"
          }).done(function(data){
            //todo: tell the user the kiosk was reloaded..?  maybe nothing...
          })
        },
        enableRegistration: function() {
          return window.CRM.APIRequest({
            "path":"kiosks/allowRegistration",
            "method":"POST"
          })
        },
        accept: function (id)
        {
           window.CRM.APIRequest({
            "path":"kiosks/"+id+"/acceptKiosk",
            "method":"POST"
          }).done(function(data){
            window.CRM.kioskDataTable.ajax.reload()
          })
        },
        identify: function (id)
        {
           window.CRM.APIRequest({
            "path":"kiosks/"+id+"/identifyKiosk",
            "method":"POST"
          }).done(function(data){
              //do nothing...
               window.CRM.kioskDataTable.ajax.reload();
          })
        },
        delete: function (id)
        {
            window.CRM.APIRequest({
                "path":"kiosks/"+id,
                "method":"DELETE"
            }).done(function(data){
                //do nothing...
                window.CRM.kioskDataTable.ajax.reload();
            })
        },
        setAssignment: function (id,assignmentId)
        {
          assignmentSplit = assignmentId.split("-");
          if(assignmentSplit.length > 0)
          {
            assignmentType = assignmentSplit[0];
            eventId = assignmentSplit[1];
          }
          else
          {
            assignmentType = assignmentId;
          }

           window.CRM.APIRequest({
            "path":"kiosks/"+id+"/setAssignment",
            "method":"POST",
            "data":JSON.stringify({"assignmentType":assignmentType,"eventId":eventId})
          }).done(function(data){
               window.CRM.kioskDataTable.ajax.reload();
          })
        }
    }

    window.CRM.events = {
       getFutureEventes: function()
        {
          //this could probably be done better, as this option may present a race condition by
          //populating a window variable with future events that future elements may rely on
          window.CRM.APIRequest({
            "path":"events/notDone"
          }).done(function(data){
            window.CRM.events.futureEventsLoaded = true;
            window.CRM.events.futureEvents=data.Events;
          });
        }
    };

    window.CRM.groups = {
      'get': function() {
        return  window.CRM.APIRequest({
          path:"groups/",
          method:"GET"
        });
      },
      'defaultGroup': function (callback) {
        var res = window.CRM.APIRequest({
          path:"groups/defaultGroup",
          method:"GET"
        }).done(function(data) {
          callback(data);
        });
      },
      'getRoles': function(GroupID) {
        return window.CRM.APIRequest({
          path:"groups/"+GroupID+"/roles",
          method:"GET"
        });
      },
      'selectTypes': {
        'Group': 1,
        'Role': 2,
      },
      'promptSelection': function(selectOptions,selectionCallback) {
          var options ={
            message: '<div class="modal-body">\
                  <input type="hidden" id="targetGroupAction">',
             buttons: {
               cancel: {
                   label: '<i class="fa fa-times"></i>' + i18next.t('Cancel'),
                   className: 'btn-default'
               },
               confirm: {
                     label: '<i class="fa fa-check"></i>' + i18next.t('OK'),
                     className: 'btn-primary'
               }
             }
          };
          initFunction = function() {};

          if (selectOptions.Type & window.CRM.groups.selectTypes.Group)
          {
            options.title = i18next.t("Select Group");
            options.message +='<div class="row"><div class="col-md-12"><span style="color: red">'+i18next.t('Please select target group for members')+':</span></div></div>\
                  <div class="row"><div class="col-md-12"><select name="targetGroupSelection" id="targetGroupSelection" class="bootbox-input bootbox-input-select form-control" style="width: 100%"></select></div></div>';
            options.buttons.confirm.callback = function(){
               selectionCallback({"GroupID": $("#targetGroupSelection option:selected").val()});
            };
          }
          if (selectOptions.Type & window.CRM.groups.selectTypes.Role )
          {
            options.title = i18next.t("Select Role");
            options.message += '<div class="row"><div class="col-md-12"><span style="color: red">'+i18next.t('Please select target Role for members')+':</span></div></div>\
                  <div class="row"><div class="col-md-12"><select name="targetRoleSelection" id="targetRoleSelection" class="bootbox-input bootbox-input-select form-control"></select></div></div>';
            options.buttons.confirm.callback = function(){
              selectionCallback({"RoleID": $("#targetRoleSelection option:selected").val()});
            };
            options.buttons.cancel.callback = function(){
              if (window.CRM.DataTableGroupView) {// this part is important in the v2/groups/22/view /js select2 textfield when you cancel the action
                $(".personSearch").val(null).trigger('change');
                window.CRM.DataTableGroupView.ajax.reload();/* we reload the data no need to add the person inside the dataTable */
              }
            };
          }

          if (selectOptions.Type === window.CRM.groups.selectTypes.Role || selectOptions.Role === window.CRM.groups.selectTypes.Role)
          {
            if (!selectOptions.GroupID)
            {
              throw i18next.t("GroupID required for role selection prompt");
            }
            initFunction = function() {
              window.CRM.groups.getRoles(selectOptions.GroupID).done(function(rdata){
                 rolesList = $.map(rdata.ListOptions, function (item) {
                    var o = {
                      text: i18next.t(item.OptionName),// to translate the Teacher and Student in localize text
                      id: item.OptionId
                    };
                    return o;
                  });
                 $("#targetRoleSelection").select2({
                   data:rolesList
                 })
               })
            }
          }
          if (selectOptions.Type === (window.CRM.groups.selectTypes.Group | window.CRM.groups.selectTypes.Role) )
          {
            options.title = i18next.t("Select Group and Role");
            options.buttons.confirm.callback = function(){
              selection = {
                "RoleID": $("#targetRoleSelection option:selected").val(),
                "GroupID": $("#targetGroupSelection option:selected").val()
              };
              console.log(selection);
              selectionCallback(selection);
            }
          }
          options.message +='</div>';
          bootbox.dialog(options).init(initFunction).show();

          // this will ensure that image and table can be focused Philippe Logel
          $(document).on('focusin', function(e) {e.stopImmediatePropagation();});

          window.CRM.groups.get()
          .done(function(rdata){
            groupsList = $.map(rdata.Groups, function (item) {
              var o = {
                text: item.Name,
                id: item.Id
              };
              return o;
            });
            $groupSelect2 = $("#targetGroupSelection").select2({
              data: groupsList
            });

            $groupSelect2.on("select2:select", function (e) {
               var targetGroupId = $("#targetGroupSelection option:selected").val();
               $parent = $("#targetRoleSelection").parent();
               $("#targetRoleSelection").empty();
               window.CRM.groups.getRoles(targetGroupId).done(function(rdata){
                 rolesList = $.map(rdata.ListOptions, function (item) {
                    var o = {
                      text: i18next.t(item.OptionName),// this is for the Teacher and Student role
                      id: item.OptionId
                    };
                    return o;
                  });
                 $("#targetRoleSelection").select2({
                   data:rolesList
                 })
               })
            });
          });
      },
     'addPerson' : function(GroupID,PersonID,RoleID) {
        params = {
          method: 'POST', // define the type of HTTP verb we want to use (POST for our form)
          path:'groups/' + GroupID + '/addperson/'+PersonID
        };
        if (RoleID)
        {
          params.data = JSON.stringify({
            RoleID: RoleID
          });
        }
        return window.CRM.APIRequest(params);
      },
      'removePerson' : function(GroupID,PersonID) {
        return window.CRM.APIRequest({
          method: 'DELETE', // define the type of HTTP verb we want to use (POST for our form)
          path:'groups/' + GroupID + '/removeperson/' + PersonID,
        });
      },
      'addGroup' : function(callbackM){
        bootbox.prompt({
          title: i18next.t("Add A Group Name"),
          value: i18next.t("Default Name Group"),
          onEscape: true,
          closeButton: true,
          buttons: {
            confirm: {
              label:  i18next.t('Yes'),
                className: 'btn-primary'
            },
            cancel: {
              label:  i18next.t('No'),
              className: 'btn-danger'
            }
          },
          callback: function (result)
          {
            if (result)
            {
              var newGroup = {'groupName': result};

              $.ajax({
                method: "POST",
                url: window.CRM.root + "/api/groups/",               //call the groups api handler located at window.CRM.root
                data: JSON.stringify(newGroup),                      // stringify the object we created earlier, and add it to the data payload
                contentType: "application/json; charset=utf-8",
                dataType: "json"
              }).done(function (data) {                               //yippie, we got something good back from the server
                  window.CRM.cart.refresh();
                  if(callbackM)
                  {
                    callbackM(data);
                  }
              });
            }
           }
        });
      }
    };

    window.CRM.system = {
      'runTimerJobs' : function () {
        $.ajax({
          url: window.CRM.root + "/api/timerjobs/run",
          type: "POST"
        });
      }
    };

    window.CRM.tools = {
      'getLinkMapFromAddress' : function(address) {
         if (window.CRM.sMapExternalProvider == "AppleMaps") {
           return '<a href="http://maps.apple.com/?q=' + address + '" target="_blank">' + address + '</a>';
         } else if (window.CRM.sMapExternalProvider == "GoogleMaps") {
           return '<a href="http://maps.google.com/?q=1  ' + address + '" target="_blank">' + address + '</a>';
         } else if (window.CRM.sMapExternalProvider == "BingMaps") {
           return '<a href="https://www.bing.com/maps?where1=' + address + '&sty=c" target="_blank">' + address + '</a>';
         }
      }
    };

    window.CRM.synchronize = {
      renderers: {
          EventsCounters: function (data) {
              if (document.getElementById('BirthdateNumber') != null) {
                  document.getElementById('BirthdateNumber').innerText = data.Birthdays;
                  document.getElementById('AnniversaryNumber').innerText = data.Anniversaries;
                  document.getElementById('EventsNumber').innerText = data.Events.count;

                  var alarmLen = data.Events.alarms.length;
                  if (alarmLen > 0) {
                      for (i = 0; i < alarmLen; i++) {
                          if (data.Events.alarms[i].diplayAlarm) {
                              window.CRM.notify('fa fa-bell',i18next.t("Calendar Event")+".",data.Events.alarms[i].summary + "<br><b>", null, "error","top",10000,'_blank',"Left");
                          }
                      }
                  }
              }
          },
        FamilyCount: function (data) {
            var dashBoardFamFull = document.getElementById('familyCountDashboard');
            var dashBoardFamReal = document.getElementById('realFamilyCNT');
            var dashBoardSingleP = document.getElementById('singleCNT');

            if (dashBoardFamFull) { // we have to test if we are on the dashboard or not
                dashBoardFamFull.innerText = data.familyCount[0];
                dashBoardFamReal.innerText = data.familyCount[1];
                dashBoardSingleP.innerText = data.familyCount[2];
                latestFamiliesTable = $('#latestFamiliesDashboardItem').DataTable({
                    retrieve: true,
                    responsive: true,
                    paging: false,
                    ordering: false,
                    searching: false,
                    scrollX: false,
                    info: false,
                    'columns': [
                        {
                            data: 'Name',
                            render: function (data, type, row, meta) {
                                if (window.CRM.bThumbnailIconPresence) {
                                    return '<img src="/api/families/' + row.Id + '/thumbnail" alt="User Image" class="user-image initials-image" width="35" height="35"> <a href=' + window.CRM.root + '/FamilyView.php?FamilyID=' + row.Id + '>' + data + '</a>';
                                } else {
                                    return '<a href=' + window.CRM.root + '/FamilyView.php?FamilyID=' + row.Id + '>' + data + '</a>';
                                }
                            }
                        },
                        {
                            data: 'Address1',
                            render: function (data, type, row, meta) {
                                return data.replace(/\\(.)/mg, "$1");// we strip the slashes
                            }
                        },
                        {
                            data: 'DateEntered',
                            render: function (data, type, row, meta) {
                                if (window.CRM.timeEnglish == true) {
                                    return moment(data).format(window.CRM.datePickerformat.toUpperCase() + ' hh:mm a');
                                } else {
                                    return moment(data).format(window.CRM.datePickerformat.toUpperCase() + ' HH:mm');
                                }
                            }
                        }
                    ]
            });
            latestFamiliesTable.clear();
            latestFamiliesTable.rows.add(data.LatestFamilies);
            latestFamiliesTable.draw(true);

            updatedFamiliesTable = $('#updatedFamiliesDashboardItem').DataTable({
              retrieve: true,
              responsive: true,
              paging: false,
              ordering: false,
              searching: false,
              scrollX: false,
              info: false,
              'columns': [
                {
                  data: 'Name',
                  render: function (data, type, row, meta) {
                      if (window.CRM.bThumbnailIconPresence) {
                          return '<img src="/api/families/' + row.Id + '/thumbnail" alt="User Image" class="user-image initials-image" width="35" height="35"> <a href=' + window.CRM.root + '/FamilyView.php?FamilyID=' + row.Id + '>' + data + '</a>';
                      } else {
                          return '<a href=' + window.CRM.root + '/FamilyView.php?FamilyID=' + row.Id + '>' + data + '</a>';
                      }
                  }
                },
                {
                  data: 'Address1',
                  render: function (data, type, row, meta) {
                    return data.replace(/\\(.)/mg, "$1");// we strip the slashes
                  }
                },
                {
                  data: 'DateLastEdited',
                  render: function (data, type, row, meta) {
                    if (window.CRM.timeEnglish == true) {
                      return moment(data).format(window.CRM.datePickerformat.toUpperCase()+' hh:mm a');
                    } else {
                      return moment(data).format(window.CRM.datePickerformat.toUpperCase()+' HH:mm');
                    }
                  }
                }
              ]
            });
            updatedFamiliesTable.clear();
            updatedFamiliesTable.rows.add(data.UpdatedFamilies);
            updatedFamiliesTable.draw(true);
          }
        },
        CalendarDisplay: function (data) {
              var calendarView = document.getElementById('calendar');

              if (calendarView && window.CRM.calendar != null) {
                  if (window.CRM.calendarSignature != data) {
                      window.CRM.calendarSignature = data;
                      window.CRM.addAllCalendars();
                  }

                  window.CRM.calendar.refetchEvents()
              }
        },
        EDriveDisplay: function(data) {
            var edriveView = document.getElementById('edrive-table');

            if (edriveView) {
                window.CRM.reloadEDriveTable();
            }
        },
        GroupsDisplay: function (data) {
          var dashBoardStatsSundaySchool = document.getElementById('groupStatsSundaySchool');
          if (dashBoardStatsSundaySchool) {// We have to check if we are on the dashboard menu
            dashBoardStatsSundaySchool.innerText = data.sundaySchoolClasses;
          }

          var dashBoardStatsSundaySchoolKids = document.getElementById('groupStatsSundaySchoolKids');
          if (dashBoardStatsSundaySchoolKids) {// We have to check if we are on the dashboard menu
              dashBoardStatsSundaySchoolKids.innerText = data.sundaySchoolkids;
          }

          var dashBoardGroupsCountDashboard = document.getElementById('groupsCountDashboard');

          if (dashBoardGroupsCountDashboard) {// We have to check if we are on the dashboard menu
            dashBoardGroupsCountDashboard.innerText = data.groups;
          }
        },
        SundaySchoolDisplay  :function(data) {
            var sundaySchoolClassesDasBoard = document.getElementById('sundaySchoolClassesDasBoard');

            if (sundaySchoolClassesDasBoard) {
                sundaySchoolClassesDasBoard.innerText = data.sundaySchoolClasses;
            }

            var sundaySchoolTeachersCNTDasBoard = document.getElementById('sundaySchoolTeachersCNTDasBoard');

            if (sundaySchoolTeachersCNTDasBoard) {
                sundaySchoolTeachersCNTDasBoard.innerText = data.teachersCNT;
            }

            var sundaySchoolKidsCNTDasBoard = document.getElementById('sundaySchoolKidsCNTDasBoard');

            if (sundaySchoolKidsCNTDasBoard) {
                sundaySchoolKidsCNTDasBoard.innerText = data.kidsCNT;
            }

            var sundaySchoolFamiliesCNTDasBoard = document.getElementById('sundaySchoolFamiliesCNTDasBoard');

            if (sundaySchoolFamiliesCNTDasBoard) {
                sundaySchoolFamiliesCNTDasBoard.innerText = data.SundaySchoolFamiliesCNT;
            }

            var sundaySchoolMaleKidsCNTDasBoard = document.getElementById('sundaySchoolMaleKidsCNTDasBoard');

            if (sundaySchoolMaleKidsCNTDasBoard) {
                sundaySchoolMaleKidsCNTDasBoard.innerText = data.maleKidsCNT;
            }

            var sundaySchoolFemaleKidsCNTDasBoard = document.getElementById('sundaySchoolFemaleKidsCNTDasBoard');

            if (sundaySchoolFemaleKidsCNTDasBoard) {
                sundaySchoolFemaleKidsCNTDasBoard.innerText = data.femaleKidsCNT;
            }

            var sundaySchoolEmailLinkDasBoard = document.getElementById('sEmailLink');

            if (sundaySchoolEmailLinkDasBoard) {
                $('#sEmailLink').attr('href', 'mailto:' + data.emailLink);
            }

            var sundaySchoolEmailLinkBCCDasBoard = document.getElementById('sEmailLinkBCC');

            if (sundaySchoolEmailLinkBCCDasBoard) {
                $('#sEmailLinkBCC').attr('href', 'mailto:?bcc=' + data.emailLink);
            }

            var sundaySchoolDropDownMailDasBoard = document.getElementById('dropDownMail');

            if (sundaySchoolDropDownMailDasBoard) {
                $('#dropDownMail').html(data.dropDown.allNormal);
            }

            var sundaySchoolDropDownBCCMailDasBoard = document.getElementById('dropDownMailBCC');

            if (sundaySchoolDropDownBCCMailDasBoard) {
                $('#dropDownMailBCC').html(data.dropDown.allNormalBCC);
            }
        },
        MailchimpDisplay:function (data) {
            if (data.isActive) {
                var len = data.MailChimpLists.length;

                // now we empty the menubar lists
                $(".lists_class_menu").removeClass("hidden");
                var real_listMenu = $(".lists_class_menu").find (".nav-treeview");

                real_listMenu.html("");
                var listItems = "";

                for (i = 0; i < len; i++) {
                    var list = data.MailChimpLists[i];

                    listItems +=  '<li class="nav-item listName' + list.id + '"><a href="' + window.CRM.root + '/v2/mailchimp/managelist/' + list.id + '" class="nav-link "> <i class="fa fa-circle-o"></i> <p>'+ list.name + '</p></a>'
                        + '</li>';
                }

                real_listMenu.html(listItems);

                if (data.firstLoaded == true) {
                    window.CRM.notify('fa  fa-info-circle', i18next.t("Mailchimp"),  i18next.t("All the lists are now loaded in Ecclesia<b>CRM</b>.<br><b>If you want to manage them, click this notification !</b>"), window.CRM.root + '/v2/mailchimp/dashboard', 'success', "top",50000);
                }
            }
        },
        EventAttendeesDisplay: function (data) {
            if (window.CRM.attendeesPresences == false) {
                window.CRM.notify('fa  fa-info-circle',
                    "<b><big>" + data.EventCountAttend + "</big></b> " + i18next.t("Attendees Checked In") + ".",
                    "<br><b>" + i18next.t("More info") + ' <a href="' + window.CRM.root + '/ListEvents.php"><i class="fa fa-arrow-circle-right"></i> </a> ' + '</b>',
                    null, "warning", "bottom",
                    Math.min(window.CRM.iDashboardPageServiceIntervalTime * 1000, window.CRM.timeOut),
                    '_blank',
                    "Right");
                window.CRM.attendeesPresences = true;
            }
        },
        PersonCount: function (data) {
          var dashBoardPeopleStats = document.getElementById('peopleStatsDashboard');
          if (dashBoardPeopleStats) {
              dashBoardPeopleStats.innerText = data.personCount;

              latestPersonsTable = $('#latestPersonsDashboardItem').DataTable({
                  retrieve: true,
                  responsive: true,
                  paging: false,
                  ordering: false,
                  searching: false,
                  scrollX: false,
                  info: false,
                  'columns': [
                      {
                          data: 'LastName',
                          render: function (data, type, row, meta) {
                              if (window.CRM.bThumbnailIconPresence) {
                                  return '<img src="/api/persons/' + row.Id + '/thumbnail" alt="User Image" class="user-image initials-image" width="35" height="35"> <a href=' + window.CRM.root + '/PersonView.php?PersonID=' + row.Id + '>' + data + ' ' + row.FirstName + '</a>';
                              } else {
                                  return '<a href=' + window.CRM.root + '/PersonView.php?PersonID=' + row.Id + '>' + data + ' ' + row.FirstName + '</a>';
                              }
                          }
                      },
                      {
                          data: 'Address1',
                          render: function (data, type, row, meta) {
                              if (data === null) {
                                  return '';
                              }
                              return data.replace(/\\(.)/mg, "$1");// we strip the slashes
                          }
                      },
                      {
                          data: 'DateLastEdited',
                          render: function (data, type, row, meta) {
                              if (data === null){
                                  data = row.DateEntered;
                              }
                              if (window.CRM.timeEnglish == true) {
                                  return moment(data).format(window.CRM.datePickerformat.toUpperCase() + ' hh:mm a');
                              } else {
                                  return moment(data).format(window.CRM.datePickerformat.toUpperCase() + ' HH:mm');
                              }
                          }
                      }
                  ]
              });
              latestPersonsTable.clear();
              latestPersonsTable.rows.add(data.LatestPersons);
              latestPersonsTable.draw(true);

              updatedPersonsTable = $('#updatedPersonsDashboardItem').DataTable({
                  retrieve: true,
                  responsive: true,
                  paging: false,
                  ordering: false,
                  searching: false,
                  scrollX: false,
                  info: false,
                  'columns': [
                      {
                          data: 'LastName',
                          render: function (data, type, row, meta) {
                              if (window.CRM.bThumbnailIconPresence) {
                                  return '<img src="/api/persons/' + row.Id + '/thumbnail" alt="User Image" class="user-image initials-image" width="35" height="35"> <a href=' + window.CRM.root + '/PersonView.php?PersonID=' + row.Id + '>' + data + ' ' + row.FirstName + '</a>';
                              } else {
                                  return '<a href=' + window.CRM.root + '/PersonView.php?PersonID=' + row.Id + '>' + data + ' ' + row.FirstName + '</a>';
                              }
                          }
                      },
                      {
                          data: 'Address1',
                          render: function (data, type, row, meta) {
                              if (data === null) {
                                  return '';
                              }
                              return data.replace(/\\(.)/mg, "$1");// we strip the slashes
                          }
                      },
                      {
                          data: 'DateLastEdited',
                          render: function (data, type, row, meta) {
                              if (data === null){
                                  data = row.DateEntered;
                              }
                              if (window.CRM.timeEnglish == true) {
                                  return moment(data).format(window.CRM.datePickerformat.toUpperCase() + ' hh:mm a');
                              } else {
                                  return moment(data).format(window.CRM.datePickerformat.toUpperCase() + ' HH:mm');
                              }
                          }
                      }
                  ]
              });
              updatedPersonsTable.clear();
              updatedPersonsTable.rows.add(data.UpdatedPerson);
              updatedPersonsTable.draw(true);


          }
        }
      },
      refresh: function () {
        if (window.CRM.PageName.indexOf("UserPasswordChange.php") !== -1) {
          return;
        }
        window.CRM.APIRequest({
          method: 'GET',
          path: 'synchronize/page?currentpagename=' + window.CRM.PageName.replace(window.CRM.root,''),
        }).done(function (data) {
          if (data[0].timeOut) {
            window.location.replace(window.CRM.root+'/Login.php?session=Lock');
          } else {
            for (var key in data[1]) {
                try {
                  window["CRM"]["synchronize"]["renderers"][key](data[1][key]);
                } catch (e) {
                  console.log(e);
                }
            }
          }
        });
      }
    }

    $(document).ajaxError(function (evt, xhr, settings,errortext) {
      if(errortext !== "abort") {
        try {
            var CRMResponse = JSON.parse(xhr.responseText);
            window.CRM.DisplayErrorMessage(settings.url, CRMResponse);
        } catch(err) {
          window.CRM.DisplayErrorMessage(settings.url,{"message":errortext});
        }
      }
    });

    function LimitTextSize(theTextArea, size) {
        if (theTextArea.value.length > size) {
            theTextArea.value = theTextArea.value.substr(0, size);
        }
    }

    function popUp(URL) {
        var day = new Date();
        var id = day.getTime();
        eval("page" + id + " = window.open(URL, '" + id + "', 'toolbar=0,scrollbars=yes,location=0,statusbar=0,menubar=0,resizable=yes,width=600,height=400,left = 100,top = 50');");
    }



