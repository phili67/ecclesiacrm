$(document).ready(function () {
  
  $('.changeRole').click(function(event) {
    var GroupID = $(this).data("groupid");
    window.CRM.groups.promptSelection({Type:window.CRM.groups.selectTypes.Role,GroupID:GroupID},function(selection){
      window.CRM.groups.addPerson(GroupID,window.CRM.currentPersonID,selection.RoleID).done(function(){
        location.reload();
      })
      
    });
  });

  $(".groupRemove").click(function(event){
    var targetGroupID = event.currentTarget.dataset.groupid;
    var targetGroupName = event.currentTarget.dataset.groupname;
    
    bootbox.confirm({
      message: i18next.t("Are you sure you want to remove this person's membership from") + " " + targetGroupName + "?",
      buttons: {
        confirm: {
          label: i18next.t('Yes'),
            className: 'btn-success'
        },
        cancel: {
          label: i18next.t('No'),
          className: 'btn-danger'
        }
      },
      callback: function (result)
      {
        if (result)
        {
          window.CRM.groups.removePerson(targetGroupID,window.CRM.currentPersonID).done(
            function(){
              location.reload()
            }
          ); 
        }
      }
    });
  })

  $("#addGroup").click(function() {
    var target = window.CRM.groups.promptSelection({Type:window.CRM.groups.selectTypes.Group | window.CRM.groups.selectTypes.Role}, function(data){
      window.CRM.groups.addPerson(data.GroupID,window.CRM.currentPersonID,data.RoleID).done(function(){
          location.reload()
        }
      );
    });
  });
  
    $("#input-person-properties").on("select2:select", function (event) {
        promptBox = $("#prompt-box");
        promptBox.removeClass('form-group').html('');
        selected = $("#input-person-properties :selected");
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
        var dataToSend = {
            PersonId: thisLink.data('person_id'),
            PropertyId: thisLink.data('property_id')
        };
        var url = window.CRM.root + '/api/properties/persons/unassign';

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
                $.ajax({
                    type: 'DELETE',
                    url: url,
                    data: dataToSend,
                    dataType: 'json',
                    success: function (data, status, xmlHttpReq) {
                        if (data && data.success) {
                            location.reload();
                        }
                    }
                });
            }
          }
        });
    });
    
    $('.edit-property-btn').click(function (event) {
        event.preventDefault();
        var thisLink = $(this);
        var person_id = thisLink.data('person_id');
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
                  path: 'properties/persons/assign',
                  data: JSON.stringify({"PersonId": person_id,"PropertyId" : property_id, "PropertyValue":result})
                  }).done(function(data) {
                    if (data && data.success) {
                            location.reload();
                    }
                });
            }
          }
        });
    });
    
    $('#edit-role-btn').click(function (event) {
        event.preventDefault();
        var thisLink = $(this);
        var personId = thisLink.data('person_id');
        var familyRoleId = thisLink.data('family_role_id');
        var familyRole = thisLink.data('family_role');
        
        $.ajax({
            type: 'GET',
            dataType: 'json',
            url: window.CRM.root + '/api/roles/all',
            success: function (data, status, xmlHttpReq) {
                if (data.length) {
                    roles = [{text: familyRole, value: ''}];
                    for (var i=0; i < data.length; i++) {
                      if (data[i].OptionId == familyRoleId) {
                          continue;
                      }
                      
                      roles[roles.length] = {
                          text: data[i].OptionName,
                          value: data[i].OptionId
                      };
                    }
                    
                    bootbox.prompt({
                      title:i18next.t( 'Change role'),
                      inputType: 'select',
                      inputOptions: roles,
                      callback: function (result) {
                        if (result) {
                          $.ajax({
                              type: 'POST',
                              data: { personId: personId, roleId: result },
                              dataType: 'json',
                              url: window.CRM.root + '/api/roles/persons/assign',
                              success: function (data, status, xmlHttpReq) {
                                  if (data.success) {
                                      location.reload();
                                  }
                              }
                          });
                        }
                          
                      }
                    });
                    
                }
            }
        });
        
    });
    
    $(document).on("click",".AddOneToPeopleCart", function(){
      clickedButton = $(this);
      window.CRM.cart.addPerson([clickedButton.data("onecartpersonid")],function()
      {
        $(clickedButton).addClass("RemoveOneFromPeopleCart");
        $(clickedButton).removeClass("AddOneToPeopleCart");
        $('i',clickedButton).addClass("fa-remove");
        $('i',clickedButton).removeClass("fa-cart-plus");
        text = $(clickedButton).find("span.cartActionDescription");
        if(text){
          $(text).text(i18next.t("Remove from Cart"));
        }
      });
    });
    
    $(document).on("click",".RemoveOneFromPeopleCart", function(){
      clickedButton = $(this);
      window.CRM.cart.removePerson([clickedButton.data("onecartpersonid")],function()
      {
        $(clickedButton).addClass("AddOneToPeopleCart");
        $(clickedButton).removeClass("RemoveOneFromPeopleCart");
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
      if (e.cartSize == 0) {
        $("#AddPersonToCart").addClass("AddOneToPeopleCart");
        $("#AddPersonToCart").removeClass("RemoveOneFromPeopleCart");
        $('i',"#AddPersonToCart").removeClass("fa-remove");
        $('i',"#AddPersonToCart").addClass("fa-cart-plus");
        text = $("#AddPersonToCart").find("span.cartActionDescription")
        if(text){
          $(text).text(i18next.t("Add to Cart"));
        }
      }
    }
    
    // end of newMessage event subscribers : Listener CRJSOM.js
    
    // the family buton update
    
     $(document).on("click",".AddToPeopleCart", function(){
      clickedButton = $(this);
      window.CRM.cart.addPerson([clickedButton.data("cartpersonid")],function()
      {
        $(clickedButton).addClass("RemoveFromPeopleCart");
        $(clickedButton).removeClass("AddToPeopleCart");
        $('span i:nth-child(2)',clickedButton).addClass("fa-remove");
        $('span i:nth-child(2)',clickedButton).removeClass("fa-cart-plus");
      });
  });
  
  $(document).on("click",".RemoveFromPeopleCart", function(){
      clickedButton = $(this);
      window.CRM.cart.removePerson([clickedButton.data("cartpersonid")],function()
      {
        $(clickedButton).addClass("AddToPeopleCart");
        $(clickedButton).removeClass("RemoveFromPeopleCart");
        $('span i:nth-child(2)',clickedButton).removeClass("fa-remove");
        $('span i:nth-child(2)',clickedButton).addClass("fa-cart-plus");
      });
    });
    
    // newMessage event subscribers : Listener CRJSOM.js
    $(document).on("updateCartMessage", updateLittleButtons);
    
    function updateLittleButtons(e) {
        var cartPeople = e.people;
        
        personButtons = $("a[data-cartpersonid]");
        $(personButtons).each(function(index,personButton){
          personID = $(personButton).data("cartpersonid")
          if (cartPeople.includes(personID)) {
            personPresent = true;
            $(personButton).addClass("RemoveFromPeopleCart");
            $(personButton).removeClass("AddToPeopleCart");
            fa = $(personButton).find("i.fa.fa-inverse");
            $(fa).addClass("fa-remove");
            $(fa).removeClass("fa-cart-plus");
            text = $(personButton).find("span.cartActionDescription")
            if(text){
              $(text).text(i18next.t("Remove from Cart"));
            }
          } else {
            $(personButton).addClass("AddToPeopleCart");
            $(personButton).removeClass("RemoveFromPeopleCart");
            fa = $(personButton).find("i.fa.fa-inverse");
            
            $(fa).removeClass("fa-remove");
            $(fa).addClass("fa-cart-plus");
            text = $(personButton).find("span.cartActionDescription")
            if(text){
              $(text).text(i18next.t("Add to Cart"));
            }
          }
        });
    }
    
  // this part allows to render the dataTable responsive in Tab  
  $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        $($.fn.dataTable.tables(true)).DataTable()
           .columns.adjust()
           .responsive.recalc();
  });

    
  automaticPaymentsTable = $("#automaticPaymentsTable").DataTable({
    ajax:{
      url: window.CRM.root + "/api/payments/family",
      type: 'POST',
      contentType: "application/json",
      data: function ( d ) {
        return JSON.stringify({"famId" : window.CRM.currentFamily});
      },
      dataSrc: "AutoPayments"
    },
    "language": {
      "url": window.CRM.plugin.dataTable.language.url
    },
    columns: [
      {
        width: 'auto',
        title:i18next.t('Type'),
        data:'EnableBankDraft',
        render: function(data, type, full, meta) {
          if (full.EnableBankDraft) {
            return i18next.t('Bank Draft');
          } else if (full.EnableCreditCard) {
            return i18next.t('Credit Card');
          } else {
            return i18next.t('Disabled');
          }
        }
      },
      {
        width: 'auto',
        title:i18next.t('Next payment date'),
        data:'NextPayDate',
        render: function(data, type, full, meta) {
          var fmt = window.CRM.datePickerformat.toUpperCase();
          
          return moment(data).format(fmt);
        }
      },
      {
        width: 'auto',
        title:i18next.t('Amount'),
        data:'Amount',
        render: function(data, type, full, meta) {
          return data;
        }
      },
      {
        width: 'auto',
        title:i18next.t('Interval (months)'),
        data:'Interval',
        render: function(data, type, full, meta) {
          return data;
        }
      },
      {
        width: 'auto',
        title:i18next.t('Fund'),
        data:'fundName',
        render: function(data, type, full, meta) {
          return data;
        }
      },
      {
        width: 'auto',
        title:i18next.t('Edit'),
        data:'Id',
        render: function(data, type, full, meta) {        
          return '<a class="btn btn-success" href="AutoPaymentEditor.php?AutID='+data+'&FamilyID='+full.Familyid+'&linkBack=PersonView.php?PersonID='+window.CRM.currentPersonID+'">'+i18next.t('Edit')+'</a>';
        }
      },
      {
        width: 'auto',
        title:i18next.t('Delete'),
        data:'Id',
        render: function(data, type, full, meta) {
          return '<button class="btn btn-danger delete-payment" data-id="'+data+'">'+i18next.t('Delete')+'</button>';
        }
      },
      {
        width: 'auto',
        title:i18next.t('Updated By'),
        data:'DateLastEdited',
        render: function(data, type, full, meta) {
          var fmt = window.CRM.datePickerformat.toUpperCase();
    
          if (window.CRM.timeEnglish == 'true') {
            time_format = 'h:mm A';
          } else {
            time_format = 'H:mm';
          }
    
          return moment(data).format(fmt+' '+time_format);;
        }
      },
      {
        width: 'auto',
        title:i18next.t('Updated By'),
        data:'EnteredFirstName',
        render: function(data, type, full, meta) {
          return data;
        }
      }
    ],
    responsive: true,
    createdRow : function (row,data,index) {
      $(row).addClass("paymentRow");
    }
  });
  
  
  $(document).on("click",".delete-payment", function(){
     clickedButton = $(this);         
     var autoPaymentId = clickedButton.data("id");
    
     bootbox.confirm(i18next.t("Confirm Delete Automatic payment"), function(confirmed) {
        if (confirmed) {
          window.CRM.APIRequest({
            method: 'POST',
            path: 'payments/delete',
            data: JSON.stringify({"famId": window.CRM.currentFamily,"paymentId" : autoPaymentId})
          }).done(function(data) {
            automaticPaymentsTable.ajax.reload();
          });
        }
     });
  });
  
  
  pledgePaymentTable = $("#pledgePaymentTable").DataTable({
    ajax:{
      url: window.CRM.root + "/api/pledges/family",
      type: 'POST',
      contentType: "application/json",
      data: function ( d ) {
        return JSON.stringify({"famId" : window.CRM.currentFamily});
      },
      dataSrc: "Pledges"
    },
    "language": {
      "url": window.CRM.plugin.dataTable.language.url
    },
    columns: [
      {
        width: 'auto',
        title:i18next.t('Pledge or Payment'),
        data:'Pledgeorpayment',
        render: function(data, type, full, meta) {
          return i18next.t(data);
        }
      },
      {
        width: 'auto',
        title:i18next.t('Dep ID')+" "+i18next.t('Closed'),
        data:'Depid',
        render: function(data, type, full, meta) {
          return data+"&nbsp;&nbsp;&nbsp;"+(full.Closed == 1 ? '<div style="color:red;display: inline-block;">'+i18next.t('Yes')+'</div>' : '<div style="color:green;display: inline-block;">'+i18next.t('No')+'</div>');
        }
      },
      {
        width: 'auto',
        title:i18next.t('Fund'),
        data:'fundName',
        render: function(data, type, full, meta) {
          return i18next.t(data);
        }
      },
      {
        width: 'auto',
        title:i18next.t('Fiscal Year'),
        data:'Fyid',
        render: function(data, type, full, meta) {
          return data+1996;//MakeFYString dans Include
        }
      },
      {
        width: 'auto',
        title:i18next.t('Date'),
        data:'Date',
        render: function(data, type, full, meta) {
          var fmt = window.CRM.datePickerformat.toUpperCase();
          
          return moment(data).format(fmt);
        }
      },
      {
        width: 'auto',
        title:i18next.t('Amount'),
        data:'Amount',
        render: function(data, type, full, meta) {
          return data;
        }
      },
      {
        width: 'auto',
        title:i18next.t('NonDeductible'),
        data:'Nondeductible',
        render: function(data, type, full, meta) {
          return data;
        }
      },
      {
        width: 'auto',
        title:i18next.t('Schedule'),
        data:'Schedule',
        render: function(data, type, full, meta) {
          return i18next.t(data);
        }
      },
      {
        width: 'auto',
        title:i18next.t('Method'),
        data:'Method',
        render: function(data, type, full, meta) {
          return i18next.t(data);
        }
      },
      {
        width: 'auto',
        title:i18next.t('CHECK')+" #",
        data:'Checkno',
        render: function(data, type, full, meta) {
          if (full.Method == "CHECK")
            return i18next.t(data);
          else 
            return i18next.t('None');
        }
      },      
      {
        width: 'auto',
        title:i18next.t('Comment'),
        data:'Comment',
        render: function(data, type, full, meta) {
          return i18next.t(data);
        }
      },
      {
        width: 'auto',
        title:i18next.t('Edit'),
        data:'Id',
        render: function(data, type, full, meta) {
          return '<a class="btn btn-success" href="PledgeEditor.php?GroupKey='+full.Groupkey+'&amp;linkBack=PersonView.php?PersonID='+window.CRM.currentPersonID+'">'+i18next.t("Edit")+'</a>';
        }
      },      
      {
        width: 'auto',
        title:i18next.t('Delete'),
        data:'Id',
        render: function(data, type, full, meta) {
          if (full.Closed == "1") {
            return '<button class="btn btn-danger" data-id="'+data+'" disabled>'+i18next.t('Delete')+'</button>';
          } else {
            return '<button class="btn btn-danger delete-pledge" data-id="'+data+'">'+i18next.t('Delete')+'</button>';
          }
        }
      },
      {
        width: 'auto',
        title:i18next.t('Date Updated'),
        data:'Datelastedited',
        render: function(data, type, full, meta) {
          var fmt = window.CRM.datePickerformat.toUpperCase();          
          return moment(data).format(fmt);
        }
      },
      {
        width: 'auto',
        title:i18next.t('Updated By'),
        data:'EnteredFirstName',
        render: function(data, type, full, meta) {
          return data+" "+full.EnteredLastName;
        }
      },
    ],
    responsive: true,
    createdRow : function (row,data,index) {
      $(row).addClass("paymentRow");
    }
  });
  
  $(document).on("click",".delete-pledge", function(){
     clickedButton = $(this);         
     var paymentId = clickedButton.data("id");
    
     bootbox.confirm(i18next.t("Confirm Delete"), function(confirmed) {
        if (confirmed) {
          window.CRM.APIRequest({
            method: 'POST',
            path: 'pledges/delete',
            data: JSON.stringify({"famId": window.CRM.currentFamily,"paymentId" : paymentId})
          }).done(function(data) {
            pledgePaymentTable.ajax.reload();
          });
        }
     });
  });
  
   $('#ShowPledges').change(function() {
      applyFilter();      
    });
    
   $('#ShowPayments').change(function() {
       applyFilter();
    });
    
    $("#date-picker-period").change(function () {
      alert($('#date-picker-period').val());
    });


  /* Custom filtering function which will search data in column four between two values */
   $.fn.dataTable.ext.search.push(
    function( settings, data, dataIndex ) {
        if (settings.nTable.id == "automaticPaymentsTable") {
          return true;
        }
        
        var min = parseInt( $('#Min').val(), 10 );
        var max = parseInt( $('#Max').val(), 10 );
        var age = parseFloat( data[3] ) || 0; // use data for the fiscal year
 
        if ( ( isNaN( min ) && isNaN( max ) ) ||
             ( isNaN( min ) && age <= max ) ||
             ( min <= age   && isNaN( max ) ) ||
             ( min <= age   && age <= max ) )
        {
            return true;
        }
        return false;
    });

    $('#Min, #Max').keyup( function() {
        pledgePaymentTable.draw();
    });


    function applyFilter()
    {
      var showPledges = $('#ShowPledges').prop('checked');
      var showPayments = $('#ShowPayments').prop('checked');
      
      if (showPledges && showPayments) {
        pledgePaymentTable.column(0).search(i18next.t("Pledge")+"|"+i18next.t("Payment"), true, false).draw();
      } else if (showPledges) {
        pledgePaymentTable.column(0).search(i18next.t("Pledge")).draw();
      } else if (showPayments) {
        pledgePaymentTable.column(0).search(i18next.t("Payment")).draw();
      } else {
        pledgePaymentTable.column(0).search("toto").draw();
      }
    }
    
    applyFilter();
});
