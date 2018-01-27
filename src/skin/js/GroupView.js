$(document).ready(function () {

  $.ajax({
    method: "GET",
    url: window.CRM.root + "/api/groups/" + window.CRM.currentGroup + "/roles",
    dataType: "json"
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
    //echo '<option value="' . $role['lst_OptionID'] . '">' . $role['lst_OptionName'] . '</option>';
  });
  
  
   $('#assign-property-form').submit(function (event) {
        event.preventDefault();
        var thisForm = $(this);
        var url = thisForm.attr('action');
        var dataToSend = thisForm.serialize();

        $.ajax({
            type: 'POST',
            url: url,
            data: dataToSend,
            dataType: 'json',
            success: function (data, status, xmlHttpReq) {
                if (data && data.success) {
                    location.reload();
                }
            }
        });
    });

  
  $('.remove-property-btn').click(function (event) {
        event.preventDefault();
        var thisLink = $(this);
        var group_id = thisLink.data('group_id');
        var property_id = thisLink.data('property_id');

        bootbox.confirm({
          buttons: {
            confirm: {
              label: i18next.t('OK'),
              className: 'confirm-button-class'
            },
            cancel: {
              label: i18next.t('Cancel'),
              className: 'cancel-button-class'
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
                            location.reload();
                    }
                });
            }
          }
        });
    });
    
    $('.edit-property-btn').click(function (event) {
        event.preventDefault();
        var thisLink = $(this);
        var group_id = thisLink.data('group_id');
        var property_id = thisLink.data('property_id');
        var property_name = thisLink.data('property_name');

        bootbox.prompt({
          buttons: {
            confirm: {
              label: i18next.t('OK'),
              className: 'confirm-button-class'
            },
            cancel: {
              label: i18next.t('Cancel'),
              className: 'cancel-button-class'
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
                            location.reload();
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
                    $('<label></label>').html(pro_prompt)
                )
                .append(
                    $('<textarea rows="3" class="form-control" name="PropertyValue"></textarea>').val(pro_value)
                );
        }

    });


  $(".personSearch").select2({
    minimumInputLength: 2,
    language: window.CRM.shortLocale,
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
          return i18next.t(thisRole.OptionName) + '<button class="changeMembership" data-personid=' + full.PersonId + '><i class="fa fa-pencil"></i></button>';
        }
      },
      {
        width: 'auto',
        title: i18next.t('Address'),
        render: function (data, type, full, meta) {
          return full.Person.Address1 + " " + full.Person.Address2;
        }
      },
      {
        width: 'auto',
        title: i18next.t('City'),
        data: 'Person.City'
      },
      {
        width: 'auto',
        title: i18next.t('State'),
        data: 'Person.State'
      },
      {
        width: 'auto',
        title: i18next.t('Zip Code'),
        data: 'Person.Zip'
      },
      {
        width: 'auto',
        title: i18next.t('Cell Phone'),
        data: 'Person.CellPhone'
      },
      {
        width: 'auto',
        title: i18next.t('Email'),
        data: 'Person.Email'
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
}
