$(document).ready(function () {

  window.CRM.APIRequest({
    method: "GET",
    path: "groups/" + window.CRM.currentGroup + "/roles",
  }).done(function (data) {
    window.CRM.groupRoles = data.ListOptions;
    $("#newRoleSelection").select2({
      data: $(window.CRM.groupRoles).map(function () {
        return {
          id: this.OptionId,
          text: i18next.t(this.OptionName)
        };
      })
    });
    initDataTable();
  });  
  
  window.CRM.dataPropertiesTable = $("#AssignedPropertiesTable").DataTable({
    ajax:{
      url: window.CRM.root + "/api/groups/groupproperties/"+window.CRM.currentGroup,
      type: 'POST',
      contentType: "application/json",
      dataSrc: "Record2propertyR2ps"
    },
    "language": {
      "url": window.CRM.plugin.dataTable.language.url
    },
    "searching": false,
    columns: [
      {
        width: 'auto',
        title:i18next.t('Name'),
        data:'ProName',
        render: function(data, type, full, meta) {
          return i18next.t(data);
        }
      },
      {
        width: 'auto',
        title:i18next.t('Value'),
        data:'R2pValue',
        render: function(data, type, full, meta) {
          return data;
        }
      },
      {
        width: 'auto',
        title:i18next.t('Action'),
        data:'ProId',
        render: function(data, type, full, meta) {
          var ret = '';
          if (full.ProPrompt != '') {       
            ret += '<a class="edit-property-btn" data-group_id="'+window.CRM.currentGroup+'" data-property_id="'+data+'" data-property_Name="'+full.R2pValue+'"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp;&nbsp;&nbsp;';
          }

          return ret+'<a class="remove-property-btn" data-group_id="'+window.CRM.currentGroup+'" data-property_id="'+data+'" data-property_Name="'+full.R2pValue+'"><i class="fa fa-trash-o" aria-hidden="true" style="color:red"></i></a>';
        }
      }
    ],
    responsive: true,
    createdRow : function (row,data,index) {
      $(row).addClass("paymentRow");
    }
  });
  
  $('#isGroupActive').prop('checked', window.CRM.isActive).change();
  $('#isGroupEmailExport').prop('checked', window.CRM.isIncludeInEmailExport).change();
  
  $("#deleteGroupButton").click(function() {
    console.log("click");
    bootbox.setDefaults({
    locale: window.CRM.shortLocale}),
    bootbox.confirm({
      title: i18next.t("Confirm Delete Group"),
      message: '<p style="color: red">'+
        i18next.t("Please confirm deletion of this group record")+window.CRM.groupName+"</p>"+
        "<p>"+
        i18next.t("This will also delete all Roles and Group-Specific Property data associated with this Group record.")+
        "</p><p>"+
        i18next.t("All group membership and properties will be destroyed.  The group members themselves will not be altered.")+"</p>",
      callback: function (result) {
        if (result)
        {
            window.CRM.APIRequest({
              method: "DELETE",
              path: "groups/" + window.CRM.currentGroup,
            }).done(function (data) {
              if (data.status == "success")
                window.location.href = window.CRM.root + "/GroupList.php";
            });
        }
      }
    });
  });
  
  $(".input-group-properties").select2({ 
    language: window.CRM.shortLocale
  });
  
   $('body').on('click','.assign-property-btn',function(){
     var property_id = $('.input-group-properties').val();
     var property_pro_value = $('.property-value').val();     
     
      window.CRM.APIRequest({
        method: 'POST',
        path: 'properties/groups/assign',
        data: JSON.stringify({"GroupId": window.CRM.currentGroup,"PropertyId" : property_id,"PropertyValue" : property_pro_value})
      }).done(function(data) {
        if (data && data.success) {
             window.CRM.dataPropertiesTable.ajax.reload();
             promptBox.removeClass('form-group').html('');
        }
      });
    });

  
  $('body').on('click','.remove-property-btn',function(){ 
        event.preventDefault();
        var thisLink = $(this);
        var group_id = thisLink.data('group_id');
        var property_id = thisLink.data('property_id');

        bootbox.confirm({
          buttons: {
            cancel: {
              label: i18next.t('Cancel'),
              className: 'btn btn-primary'
            },
            confirm: {
              label: i18next.t('OK'),
              className: 'btn btn-danger'
            }
          },
          title: i18next.t('Are you sure you want to unassign this property?'),
          message:i18next.t('This action can never be undone !!!!'),
          callback: function (result) {
            if (result) {
                window.CRM.APIRequest({
                  method: 'DELETE',
                  path: 'properties/groups/unassign',
                  data: JSON.stringify({"GroupId": group_id,"PropertyId" : property_id})
                  }).done(function(data) {
                    if (data && data.success) {
                        window.CRM.dataPropertiesTable.ajax.reload()
                    }
                });
            }
          }
        });
    });
    
    $('body').on('click','.edit-property-btn',function(){ 
        event.preventDefault();
        var thisLink = $(this);
        var group_id = thisLink.data('group_id');
        var property_id = thisLink.data('property_id');
        var property_name = thisLink.data('property_name');

        bootbox.prompt({
          buttons: {
            confirm: {
              label: i18next.t('OK'),
              className: 'btn btn-primary'
            },
            cancel: {
              label: i18next.t('Cancel'),
              className: 'btn btn-default'
            }
          },
          title: i18next.t('Are you sure you want to change this property?'),          
          value: property_name,
          callback: function (result) {
            if (result) {
                window.CRM.APIRequest({
                  method: 'POST',
                  path: 'properties/groups/assign',
                  data: JSON.stringify({"GroupId": group_id,"PropertyId" : property_id, "PropertyValue":result})
                  }).done(function(data) {
                    if (data && data.success) {
                        window.CRM.dataPropertiesTable.ajax.reload()
                    }
                });
            }
          }
        });
    });
    
    $(".input-group-properties").on("select2:select", function (event) {
        promptBox = $("#prompt-box");
        promptBox.removeClass('form-group').html('');
        selected = $(".input-group-properties :selected");
        pro_prompt = selected.data('pro_prompt');
        pro_value = selected.data('pro_value');
        if (pro_prompt) {
            promptBox
                .addClass('form-group')
                .append(
                    $('<label style="color:white"></label>').html(pro_prompt)
                )
                .append(
                    $('<textarea rows="3" class="form-control property-value" name="PropertyValue"></textarea>').val(pro_value)
                );
        }

    });


  $(".personSearch").select2({
    minimumInputLength: 2,
    language: window.CRM.shortLocale,
    minimumInputLength: 2,
    placeholder: " -- "+i18next.t("Person")+" -- ",
    allowClear: true, // This is for clear get the clear button if wanted 
    ajax: {
      url: function (params) {
        return window.CRM.root + "/api/persons/search/" + params.term;
      },
      dataType: 'json',
      delay: 250,
      data: function (params) {
        return {
          q: params.term, // search term
          page: params.page
        };
      },
      processResults: function (rdata, page) {
        return {results: rdata};
      },
      cache: true
    }
  });

  $(".personSearch").on("select2:select", function (e) {
      window.CRM.groups.promptSelection({Type:window.CRM.groups.selectTypes.Role,GroupID:window.CRM.currentGroup},function(selection){
        window.CRM.groups.addPerson(window.CRM.currentGroup, e.params.data.objid,selection.RoleID).done(function (data) {
          $(".personSearch").val(null).trigger('change');
          window.CRM.DataTableGroupView.ajax.reload();/* we reload the data no need to add the person inside the dataTable */
        });
      });
  });

  $("#deleteSelectedRows").click(function () {
    var deletedRows = window.CRM.DataTableGroupView.rows('.selected').data()
    bootbox.confirm({
      message: i18next.t("Are you sure you want to remove the selected group members?") + " (" + deletedRows.length + ") ",
      buttons: {
        confirm: {
          label:  i18next.t('No'),
          className: 'btn-primary'
        },
        cancel: {
          label:  i18next.t('Yes'),
          className: 'btn-danger'
        }
      },
      callback: function (result)
      {
        if (result==false)
        {
          $.each(deletedRows, function (index, value) {
            window.CRM.groups.removePerson(window.CRM.currentGroup,value.PersonId).done(
              function(){
                window.CRM.DataTableGroupView.row(function (idx, data, node) {
                  if (data.PersonId == value.PersonId) {
                    return true;
                  }
                }).remove();
                window.CRM.DataTableGroupView.rows().invalidate().draw(true);
            });
          });
        }
      }
    });

  });

  $("#addSelectedToCart").click(function () {
    if (window.CRM.DataTableGroupView.rows('.selected').length > 0)
    {
      var selectedPersons = {
        "Persons" : $.map(window.CRM.DataTableGroupView.rows('.selected').data(), function(val,i){
                      return val.PersonId;
                    })
      };
      window.CRM.cart.addPerson(selectedPersons.Persons);
    }

  });

  //copy membership
  $("#addSelectedToGroup").click(function () {
    window.CRM.groups.promptSelection({Type:window.CRM.groups.selectTypes.Group|window.CRM.groups.selectTypes.Role}, function(data){
      selectedRows = window.CRM.DataTableGroupView.rows('.selected').data()
      $.each(selectedRows, function (index, value) {
        window.CRM.groups.addPerson(data.GroupID,value.PersonId,data.RoleID);
    });
    });
  });

  $("#moveSelectedToGroup").click(function () {
    window.CRM.groups.promptSelection({Type:window.CRM.groups.selectTypes.Group|window.CRM.groups.selectTypes.Role},function(data){
      selectedRows = window.CRM.DataTableGroupView.rows('.selected').data()
      $.each(selectedRows, function (index, value) {
        console.log(data);
        window.CRM.groups.addPerson(data.GroupID,value.PersonId,data.RoleID);
        window.CRM.groups.removePerson(window.CRM.currentGroup,value.PersonId).done(
          function () {
            window.CRM.DataTableGroupView.row(function (idx, data, node) {
              if (data.PersonId == value.PersonId) {
                return true;
              }
            }).remove();
            window.CRM.DataTableGroupView.rows().invalidate().draw(true);
        });
      });
    });
  });

  $(document).on("click", ".changeMembership", function (e) {
    var PersonID = $(e.currentTarget).data("personid");
    window.CRM.groups.promptSelection({Type:window.CRM.groups.selectTypes.Role,GroupID:window.CRM.currentGroup},function(selection){
      window.CRM.groups.addPerson(window.CRM.currentGroup,PersonID,selection.RoleID).done(function(){
        window.CRM.DataTableGroupView.row(function (idx, data, node) {
        if (data.PersonId == PersonID) {
          data.RoleId = selection.RoleID;
          return true;
        }
      });
      window.CRM.DataTableGroupView.rows().invalidate().draw(true);
      });
    });
    e.stopPropagation();
  });

});

function initDataTable() {
  var DataTableOpts = {
    ajax: {
      url: window.CRM.root + "/api/groups/" + window.CRM.currentGroup + "/members",
      dataSrc: "Person2group2roleP2g2rs"
    },
    "language": {
      "url": window.CRM.plugin.dataTable.language.url
    },
      responsive: true,
      "language": {
          "url": window.CRM.plugin.dataTable.language.url
      },
      "dom": window.CRM.plugin.dataTable.dom,
      "tableTools": {
          "sSwfPath": window.CRM.plugin.dataTable.tableTools.sSwfPath
      },
    columns: [
      {
        width: 'auto',
        title: i18next.t('Name'),
        data: 'PersonId',
        render: function (data, type, full, meta) {
          return '<img src="' + window.CRM.root + '/api/persons/' + full.PersonId + '/thumbnail" class="direct-chat-img initials-image"> &nbsp <a href="PersonView.php?PersonID="' + full.PersonId + '"><a target="_top" href="PersonView.php?PersonID=' + full.PersonId + '">' + full.Person.FirstName + " " + full.Person.LastName + '</a>';
        }
      },
      {
        width: 'auto',
        title: i18next.t('Group Role'),
        data: 'RoleId',
        render: function (data, type, full, meta) {
          thisRole = $(window.CRM.groupRoles).filter(function (index, item) {
            return item.OptionId == data
          })[0];
          
          if (isShowable) {
            return i18next.t(thisRole.OptionName) + '<button class="changeMembership" data-personid=' + full.PersonId + '><i class="fa fa-pencil"></i></button>';
          } else {
            return i18next.t("Private Data");
          }
        }
      },
      {
        width: 'auto',
        title: i18next.t('Address'),
        render: function (data, type, full, meta) {          
          if (isShowable) {
            return full.Person.Address1 + " " + full.Person.Address2;
          } else {
            return i18next.t("Private Data");
          }
        }
      },
      {
        width: 'auto',
        title: i18next.t('City'),
        data: 'Person.City',
        render: function (data, type, full, meta) {          
          if (isShowable) {
            return data;
          } else {
            return i18next.t("Private Data");
          }
        }
      },
      {
        width: 'auto',
        title: i18next.t('State'),
        data: 'Person.State',
        render: function (data, type, full, meta) {          
          if (isShowable) {
            return data;
          } else {
            return i18next.t("Private Data");
          }
        }
      },
      {
        width: 'auto',
        title: i18next.t('Zip Code'),
        data: 'Person.Zip',
        render: function (data, type, full, meta) {          
          if (isShowable) {
            return data;
          } else {
            return i18next.t("Private Data");
          }
        }
      },
      {
        width: 'auto',
        title: i18next.t('Cell Phone'),
        data: 'Person.CellPhone',
        render: function (data, type, full, meta) {          
          if (isShowable) {
            return data;
          } else {
            return i18next.t("Private Data");
          }
        }
      },
      {
        width: 'auto',
        title: i18next.t('Email'),
        data: 'Person.Email',
        render: function (data, type, full, meta) {          
          if (isShowable) {
            return data;
          } else {
            return i18next.t("Private Data");
          }
        }
      }
    ],
    "fnDrawCallback": function (oSettings) {
      $("#iTotalMembers").text(oSettings.aoData.length);
    },
    "createdRow": function (row, data, index) {
      $(row).addClass("groupRow");
    }
  };
  $.extend(DataTableOpts,window.CRM.plugin.DataTable);
  window.CRM.DataTableGroupView = $("#membersTable").DataTable(DataTableOpts);

  $('#isGroupActive').change(function () {
    $.ajax({
      type: 'POST', // define the type of HTTP verb we want to use (POST for our form)
      url: window.CRM.root + '/api/groups/' + window.CRM.currentGroup + '/settings/active/' + $(this).prop('checked'),
      dataType: 'json', // what type of data do we expect back from the server
      encode: true
    });
  });

  $('#isGroupEmailExport').change(function () {
    $.ajax({
      type: 'POST', // define the type of HTTP verb we want to use (POST for our form)
      url: window.CRM.root + '/api/groups/' + window.CRM.currentGroup + '/settings/email/export/' + $(this).prop('checked'),
      dataType: 'json', // what type of data do we expect back from the server
      encode: true
    });
  });

  $(document).on('click', '.groupRow', function () {
    $(this).toggleClass('selected');
    var selectedRows = window.CRM.DataTableGroupView.rows('.selected').data().length;
    $("#deleteSelectedRows").prop('disabled', !(selectedRows));
    $("#deleteSelectedRows").text(i18next.t("Remove")+" (" + selectedRows + ") "+i18next.t("Members from group"));
    $("#buttonDropdown").prop('disabled', !(selectedRows));
    $("#addSelectedToGroup").prop('disabled', !(selectedRows));
    $("#addSelectedToGroup").html(i18next.t("Add")+"  (" + selectedRows + ") "+i18next.t("Members to another group"));
    $("#addSelectedToCart").prop('disabled', !(selectedRows));
    $("#addSelectedToCart").html(i18next.t("Add")+"  (" + selectedRows + ") "+i18next.t("Members to cart"));
    $("#moveSelectedToGroup").prop('disabled', !(selectedRows));
    $("#moveSelectedToGroup").html(i18next.t("Move")+"  (" + selectedRows + ") "+i18next.t("Members to another group"));
  });
  
    $(document).on("click",".AddToGroupCart", function(){
      clickedButton = $(this);
      window.CRM.cart.addGroup(clickedButton.data("cartgroupid"),function()
      {
        $(clickedButton).addClass("RemoveFromGroupCart");
        $(clickedButton).removeClass("AddToGroupCart");
        $('i',clickedButton).addClass("fa-remove");
        $('i',clickedButton).removeClass("fa-cart-plus");
        text = $(clickedButton).find("span.cartActionDescription");
        if(text){
          $(text).text(i18next.t("Remove from Cart"));
        }
      });
    });
    
    $(document).on("click",".RemoveFromGroupCart", function(){
      clickedButton = $(this);
      window.CRM.cart.removeGroup(clickedButton.data("cartgroupid"),function()
      {
        $(clickedButton).addClass("AddToGroupCart");
        $(clickedButton).removeClass("RemoveFromGroupCart");
        $('i',clickedButton).removeClass("fa-remove");
        $('i',clickedButton).addClass("fa-cart-plus");
        text = $(clickedButton).find("span.cartActionDescription");
        if(text){
          $(text).text(i18next.t("Add to Cart"));
        }
      });
    });
    
    
    // newMessage event subscribers : Listener CRJSOM.js
    $(document).on("emptyCartMessage", updateButtons);
    
    // newMessage event handler
    function updateButtons(e) {
      if (e.cartPeople.length == 0) {
        $("#AddToGroupCart").addClass("AddToGroupCart");
        $("#AddToGroupCart").removeClass("RemoveFromGroupCart");
        $('i',"#AddToGroupCart").removeClass("fa-remove");
        $('i',"#AddToGroupCart").addClass("fa-cart-plus");
        text = $("#AddToGroupCart").find("span.cartActionDescription")
        if(text){
          $(text).text(i18next.t("Add to Cart"));
        }
      }
    }
    
    
    // start manager
    $("#add-manager").click(function() {
      createManagerWindow(window.CRM.currentGroup);
    });
    
    $('body').on('click','.delete-person-manager', function(){ 
      var personID = $(this).data('personid');
      var groupID  = $(this).data('groupid');
      
       window.CRM.APIRequest({
         method: 'POST',
         path: 'groups/deleteManager',
         data: JSON.stringify({"groupID":groupID,"personID":personID})
      }).done(function(data) {
        if (data.status == undefined) {
          var len = data.length;
          
          var optionValues = '';
      
          for (i=0; i<len; ++i) {
            optionValues += data[i].name+'<a class="delete-person-manager" data-personid="'+data[i].personID+'" data-groupid="'+groupID+'"><i style="cursor:pointer; color:red;" class="icon fa fa-close"></i></a>, ';
          }
          
          if (optionValues != '') {
            $("#Manager-list").html(optionValues);
          } else {
            $("#Manager-list").html(i18next.t("No assigned Manager")+".");
          }
        } else {
          $("#Manager-list").html(i18next.t("No assigned Manager")+".");
        }
      });
    });
    
    
    function BootboxContentManager(){
      var frm_str = '<h3 style="margin-top:-5px">'+i18next.t("Manage Group Managers")+'</h3>'
       + '<div>'
            +'<div class="row div-title">'
              +'<div class="col-md-4">'
              + '<span style="color: red">*</span>' + i18next.t("With") + ":"                    
              +'</div>'
              +'<div class="col-md-8">'
              +'<select size="6" style="width:100%" id="select-manager-persons" multiple>'
              +'</select>'
             +'</div>'
            +'</div>'
            +'<div class="row div-title">'
              +'<div class="col-md-4"><span style="color: red">*</span>' + i18next.t("Add person") + ":</div>"
              +'<div class="col-md-8">'
                +'<select name="person-manager-Id" id="person-manager-Id" class="form-control select2"'
                    +'style="width:100%">'
                +'</select>'
              +'</div>'
            +'</div>'
          +'</div>';
          
          var object = $('<div/>').html(frm_str).contents();

        return object
    }
    
    // the add people to calendar
  
    function addManagersFromGroup(groupID)
    {
        $('#select-manager-persons').find('option').remove();
      
        window.CRM.APIRequest({
          method: 'POST',
          path: 'groups/getmanagers',
          data: JSON.stringify({"groupID": groupID})
        }).done(function(data) {    
          var elt = document.getElementById("select-manager-persons");
          var len = data.length;
          
          var optionValues = '';
      
          for (i=0; i<len; ++i) {
            var option = document.createElement("option");

            option.text = data[i].name;
            option.value = data[i].personID;
            
            optionValues += data[i].name+'<a class="delete-person-manager" data-personid="'+data[i].personID+'" data-groupid="'+groupID+'"><i style="cursor:pointer; color:red;" class="icon fa fa-close"></i></a>, ';
      
            elt.appendChild(option);
          }
          
          if (optionValues != '') {
            $("#Manager-list").html(optionValues);
          } else {
            $("#Manager-list").html(i18next.t("No assigned Manager")+".");
          }
        });  
    }
    
    function createManagerWindow (groupID)
    {
      var modal = bootbox.dialog({
         message: BootboxContentManager(),
         buttons: [
          {
           label: i18next.t("Delete"),
           className: "btn btn-warning",
           callback: function() {                        
              bootbox.confirm(i18next.t("Are you sure, you want to delete this Manager ?"), function(result){ 
                if (result) {
                  $('#select-manager-persons :selected').each(function(i, sel){ 
                    var personID = $(sel).val();
                  
                    window.CRM.APIRequest({
                       method: 'POST',
                       path: 'groups/deleteManager',
                       data: JSON.stringify({"groupID":groupID,"personID":personID})
                    }).done(function(data) {
                      $("#select-manager-persons option[value='"+personID+"']").remove();
                      
                      var opts = $('#select-manager-persons > option').map(function() { return this.text+'<a class="delete-person-manager" data-personid"'+this.value+'" data-groupid"'+groupID+'"><i style="cursor:pointer; color:red;" class="icon fa fa-close"></i></a>'; }).get();
                      
                      if (opts.length) {
                        $("#Manager-list").html(opts.join(", "));
                      } else {
                        $("#Manager-list").html(i18next.t("No assigned Manager")+".");
                      }
                    });
                  });
                }
              });
              return false;
           }
          },
          {
           label: i18next.t("Delete Managers"),
           className: "btn btn-danger",
           callback: function() {
            bootbox.confirm(i18next.t("Are you sure, you want to delete all the managers ?"), function(result){ 
              if (result) {
                window.CRM.APIRequest({
                   method: 'POST',
                   path: 'groups/deleteAllManagers',
                   data: JSON.stringify({"groupID":groupID})
                }).done(function(data) {
                  addManagersFromGroup(groupID);
                  modal.modal("hide");
                });
              }
            });
            return false;
           }
          },
          {
           label: i18next.t("Ok"),
           className: "btn btn-primary",
           callback: function() {
             modal.modal("hide");
             return true;
           }
          },
         ],
         show: false,
         onEscape: function() {
            modal.modal("hide");
         }
       });
     
       $("#person-manager-Id").select2({ 
          language: window.CRM.shortLocale,
          minimumInputLength: 2,
          placeholder: " -- "+i18next.t("Person")+" -- ",
          allowClear: true, // This is for clear get the clear button if wanted 
          ajax: {
              url: function (params){
                return window.CRM.root + "/api/people/searchonlyperson/" + params.term;
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
           
       $("#person-manager-Id").on("select2:select",function (e) { 
         if (e.params.data.personID !== undefined) {
             window.CRM.APIRequest({
                  method: 'POST',
                  path: 'groups/addManager',
                  data: JSON.stringify({"groupID":window.CRM.currentGroup,"personID": e.params.data.personID})
             }).done(function(data) { 
               addManagersFromGroup(groupID);
             });
          }
       });
     
       addManagersFromGroup(groupID);
       modal.modal('show');
     
      // this will ensure that image and table can be focused
      $(document).on('focusin', function(e) {e.stopImmediatePropagation();});  
    }

    // end manager


    // listener : when the delete member is invocated
    $(document).on("updateLocalePageMessage", updateLocaleSCPage);
    
    // newMessage event handler
    function updateLocaleSCPage(e) {
      window.CRM.DataTableGroupView.ajax.reload();
    }


}