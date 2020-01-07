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
            var lists_menu = $(".lists_class_menu").parent();
            var real_listMenu = $( lists_menu ).find (".treeview-menu");

            real_listMenu.html("");
            var listItems  = "";

            for (i=0;i<len;i++) {
              var list = data.MailChimpLists[i];

              listItems += '<li><a href="' + window.CRM.root + '/v2/mailchimp/managelist/' + list.id + '"><i class="fa fa-circle-o"></i>'+ list.name + '</a>';
            }

            real_listMenu.html(listItems);

            if ( data.firstLoaded == true ) {
              window.CRM.notify('glyphicon glyphicon-info-sign',i18next.t("Mailchimp"), "<br>" + i18next.t("All the lists are now loaded in Ecclesia<b>CRM</b>.<br><b>If you want to manage them, click this notification !</b>"), window.CRM.root + '/v2/mailchimp/dashboard' ,'success',"top");
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
      if (delay === undefined) {
        delay = 4000;
      }
      if (horizontal === undefined) {
        horizontal = "right";
      }
      if (target === undefined) {
        target = '_self';
      }
      $.notify({
        // options
        icon: icon,
        title: title,
        message: message,
        url: link,
        target: target
      },{
        // settings
        element: 'body',
        position: null,
        type: type,
        allow_dismiss: true,
        newest_on_top: false,
        showProgressbar: false,
        placement: {
          from: place,
          align: horizontal
        },
        offset: 20,
        spacing: 10,
        z_index: 1031,
        delay: delay,
        timer: 1000,
        url_target: target,
        mouse_over: null,
        animate: {
          enter: 'animated fadeInDown',
          exit: 'animated fadeOutUp'
        }
      });
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
            window.CRM.cart.refresh();
          }
          else
          {
            window.CRM.cart.refresh();
          }
        });
      },
      'deactivate' : function (callback)
      {
        bootbox.confirm({
            title: i18next.t("Do you really want to deactivate the persons?"),
            message: i18next.t("This action can be undone !!!!"),
            buttons: {
                cancel: {
                    label:  i18next.t('No'),
                    className: 'btn-success'
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
                    className: 'btn-success'
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
          method: 'POST',
          path:'cart/',
          data: JSON.stringify({"_METHOD":"DELETE","Persons":Persons})
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
      'addGroup' : function (GroupID, callback)
      {
         window.CRM.APIRequest({
          method: 'POST',
          path: 'cart/',
          data: JSON.stringify({"Group":GroupID})
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
               window.CRM.notify('glyphicon glyphicon-info-sign',i18next.t("Register")+".","<br>"+i18next.t("Register your software to EcclesiaCRM team.") + "<br><b>"  + i18next.t("Simply click this") + " <a href=\"#\" id=\"registerSoftware\">" + i18next.t("link") + "</a> " + i18next.t("to register your software") +  ".</b>", null, "warning","top",10000,'_blank',"left");
            }
          });

          window.CRM.APIRequest({
           method: 'POST',
           path:"systemupgrade/isUpdateRequired"
          }).done(function(data) {
            if (data.Upgrade) {
               window.CRM.notify('glyphicon glyphicon-info-sign',i18next.t("New Release")+".","<br>"+i18next.t("Installed version")+" : "+data.installedVersion+'      '+i18next.t("New One")+" : "+data.latestVersion.name+'<br><b>'+i18next.t("To upgrade simply click this Notification")+"</b>", window.CRM.root+'/UpgradeCRM.php',"info","bottom",6000,'_blank');
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
            window.CRM.cart.updatePage(data.PeopleCart);
            //window.scrollTo(0, 0);
            $("#iconCount").text(data.PeopleCart.length);

            // broadcaster
            $.event.trigger({
              type: "emptyCartMessage",
              cartPeople: data.PeopleCart
            });

            var cartDropdownMenu;
            if (data.PeopleCart.length > 0) {
              cartDropdownMenu = '\
                <li id="showWhenCartNotEmpty">\
                    <ul class="menu">\
                        <li>\
                            <a href="' + window.CRM.root+ '/v2/cart/view">\
                                <i class="fa fa-shopping-cart text-green"></i>' + i18next.t("View Cart") + '\
                            </a>\
                        </li>\
                        <li>\
                            <a href="#" class="emptyCart" >\
                                <i class="fa fa-eraser"></i>' + i18next.t("Empty Cart") + ' \
                            </a>\
                        </li>\
                        <li>\
                            <a href="#" id="emptyCartToGroup">\
                                <i class="fa fa-tag text-info"></i>' + i18next.t("Empty Cart to Group") + '\
                            </a>\
                        </li>\
                        <li>\
                            <a href="' + window.CRM.root+ '/CartToFamily.php">\
                                <i class="fa fa fa-users text-info"></i>' + i18next.t("Empty Cart to Family") + '\
                            </a>\
                        </li>\
                        <li>\
                            <a href="#" id="emptyCartToEvent">\
                                <i class="fa fa fa-ticket text-info"></i>' + i18next.t("Empty Cart to Event") + '\
                            </a>\
                        </li>\
                        <li>\
                            <a href="' + window.CRM.root+ '/v2/map/0">\
                                <i class="fa fa-map-marker text-info"></i>' + i18next.t("Map Cart") + '\
                            </a>\
                        </li>\
                        <li>\
                            <a href="#" id="deactivateCart">\
                               <i class="fa fa-trash text-warning"></i>'+ i18next.t("Deactivate Persons From Cart")+ '\
                            </a>\
                        </li>\
                        <li>\
                            <a href="#" id="deleteCart">\
                               <i class="fa fa-trash text-danger"></i>'+ i18next.t("Delete Persons From the CRM")+ '\
                            </a>\
                        </li>\
                    </ul>\
                </li>\
                          <!--li class="footer"><a href="#">' + i18next.t("View all") + '</a></li-->\
                      '
          }
            else {
              cartDropdownMenu = '\
                <li class="header">' + i18next.t("Your Cart is Empty" ) + '</li>';
            }
          $("#cart-dropdown-menu").html(cartDropdownMenu);
          $("#CartBlock")
            .animate({'left':(-10)+'px'},30)
            .animate({'left':(+10)+'px'},30)
            .animate({'left':(0)+'px'},30);
          });
      },
      'updatePage' : function (cartPeople){

        // broadcaster
        $.event.trigger({
            type: "updateCartMessage",
            people:cartPeople
        });
      }
    };

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
               confirm: {
                   label: i18next.t('OK'),
                   className: 'btn-success'
               },
               cancel: {
                   label: i18next.t('Cancel'),
                   className: 'btn-danger'
               }
             }
          };
          initFunction = function() {};

          if (selectOptions.Type & window.CRM.groups.selectTypes.Group)
          {
            options.title = i18next.t("Select Group");
            options.message +='<span style="color: red">'+i18next.t('Please select target group for members')+':</span>\
                  <select name="targetGroupSelection" id="targetGroupSelection" class="form-control" style="width: 100%"></select>';
            options.buttons.confirm.callback = function(){
               selectionCallback({"GroupID": $("#targetGroupSelection option:selected").val()});
            };
          }
          if (selectOptions.Type & window.CRM.groups.selectTypes.Role )
          {
            options.title = i18next.t("Select Role");
            options.message += '<span style="color: red">'+i18next.t('Please select target Role for members')+':</span>\
                  <select name="targetRoleSelection" id="targetRoleSelection" class="form-control"></select>';
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
                className: 'btn-success'
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

    window.CRM.dashboard = {
      renderers: {
        EventsCounters: function (data) {
          if (document.getElementById('BirthdateNumber') != null) {
            document.getElementById('BirthdateNumber').innerText = data.Birthdays;
            document.getElementById('AnniversaryNumber').innerText = data.Anniversaries;
            document.getElementById('EventsNumber').innerText = data.Events;
          }
        },
        FamilyCount: function (data) {
          var dashBoardFam = document.getElementById('familyCountDashboard');

          if (dashBoardFam) { // we have to test if we are on the dashboard or not
            dashBoardFam.innerText = data.familyCount;
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
                      return moment(data).format(window.CRM.datePickerformat.toUpperCase()+' hh:mm a');
                    } else {
                      return moment(data).format(window.CRM.datePickerformat.toUpperCase()+' HH:mm');
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
        MailchimpDisplay:function (data) {
            if (data.isActive) {
                var len = data.MailChimpLists.length;

                // now we empty the menubar lists
                $(".lists_class_menu").removeClass("hidden");
                var lists_menu = $(".lists_class_menu").parent();
                var real_listMenu = $(lists_menu).find(".treeview-menu");

                real_listMenu.html("");
                var listItems = "";

                for (i = 0; i < len; i++) {
                    var list = data.MailChimpLists[i];

                    listItems += '<li><a href="' + window.CRM.root + '/v2/mailchimp/managelist/' + list.id + '"><i class="fa fa-circle-o"></i>' + list.name + '</a>';
                }

                real_listMenu.html(listItems);

                if (data.firstLoaded == true) {
                    window.CRM.notify('glyphicon glyphicon-info-sign', i18next.t("Mailchimp"), "<br>" + i18next.t("All the lists are now loaded in Ecclesia<b>CRM</b>.<br><b>If you want to manage them, click this notification !</b>"), window.CRM.root + '/v2/mailchimp/dashboard', 'success', "top");
                }
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
          path: 'dashboard/page?currentpagename=' + window.CRM.PageName.replace(window.CRM.root,''),
        }).done(function (data) {
          if (data[0].timeOut) {
            window.location.replace(window.CRM.root+'/Login.php?session=Lock');
          } else {
            for (var key in data[1]) {
                try {
                  window["CRM"]["dashboard"]["renderers"][key](data[1][key]);
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
