$(document).ready(function () {
    
    $(".input-family-properties").on("select2:select", function (event) {
        promptBox = $("#prompt-box");
        promptBox.removeClass('form-group').html('');
        selected = $(".input-family-properties :selected");
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
    
    $('.remove-property-btn').click(function (event) {
        event.preventDefault();
        var thisLink = $(this);
        var family_id = thisLink.data('family_id');
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
                path: 'properties/families/unassign',
                data: JSON.stringify({"FamilyId": family_id,"PropertyId" : property_id})
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
      var family_id = thisLink.data('family_id');
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
              path: 'properties/families/assign',
              data: JSON.stringify({"FamilyId": family_id,"PropertyId" : property_id, "PropertyValue":result})
            }).done(function(data) {
              if (data && data.success) {
                location.reload();
              }
            });
          }
        }
      });
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


  $(".data-table").DataTable({
    "language": {
      "url": window.CRM.plugin.dataTable.language.url
    },
    responsive: true});
    
  
   $(".data-person").DataTable({"language": {
      "url": window.CRM.plugin.dataTable.language.url
    },
    searching: false,
    responsive: true,
    paging: false});  
  
  
  // this part allows to render the dataTable responsive in Tab  
  $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        $($.fn.dataTable.tables(true)).DataTable()
           .columns.adjust()
           .responsive.recalc();
  });
  
  $("#onlineVerify").click(function () {
    $.ajax({
      type: 'POST',
      url: window.CRM.root + '/api/families/' + window.CRM.currentFamily + '/verify'
    })
      .done(function(data, textStatus, xhr) {
        $('#confirm-verify').modal('hide');
        if (xhr.status == 200) {
          showGlobalMessage(i18next.t("Verification email sent"), "success")
        } else {
          showGlobalMessage(i18next.t("Failed to send verification email"), "danger")
        }
      });
  });

  $("#verifyNow").click(function () {
    $.ajax({
      type: 'POST',
      url: window.CRM.root + '/api/families/verify/' + window.CRM.currentFamily + '/now'
    })
      .done(function(data, textStatus, xhr) {
        $('#confirm-verify').modal('hide');
        if (xhr.status == 200) {
          location.reload();
        } else {
          showGlobalMessage(i18next.t("Failed to add verification"), "danger")
        }
      });
  });


  $("#verifyDownloadPDF").click(function () {
    window.open(window.CRM.root + '/Reports/ConfirmReport.php?familyId=' + window.CRM.currentFamily, '_blank');
    $('#confirm-verify').modal('hide');
  });
  
     
    
    $(document).on("click",".AddToFamilyCart", function(){
      clickedButton = $(this);
      window.CRM.cart.addFamily(clickedButton.data("cartfamilyid"),function()
      {
        $(clickedButton).addClass("RemoveFromFamilyCart");
        $(clickedButton).removeClass("AddToFamilyCart");
        $('i',clickedButton).addClass("fa-remove");
        $('i',clickedButton).removeClass("fa-cart-plus");
        text = $(clickedButton).find("span.cartActionDescription");
        if(text){
          $(text).text(i18next.t("Remove from Cart"));
        }
      });
    });
    
    $(document).on("click",".RemoveFromFamilyCart", function(){
      clickedButton = $(this);
      window.CRM.cart.removeFamily(clickedButton.data("cartfamilyid"),function()
      {
        $(clickedButton).addClass("AddToFamilyCart");
        $(clickedButton).removeClass("RemoveFromFamilyCart");
        $('i',clickedButton).removeClass("fa-remove");
        $('i',clickedButton).addClass("fa-cart-plus");
        text = $(clickedButton).find("span.cartActionDescription");
        if(text){
          $(text).text(i18next.t("Add to Cart"));
        }
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

    // newMessage event subscribers : Listener CRJSOM.js
    $(document).on("emptyCartMessage", updateButtons);
    
    // newMessage event handler
    function updateButtons(e) {
      if (e.cartSize == 0) {
        $("#AddToFamilyCart").addClass("AddToFamilyCart");
        $("#AddToFamilyCart").removeClass("RemoveFromFamilyCart");
        $('i',"#AddToFamilyCart").removeClass("fa-remove");
        $('i',"#AddToFamilyCart").addClass("fa-cart-plus");
        text = $("#AddToFamilyCart").find("span.cartActionDescription")
        if(text){
          $(text).text(i18next.t("Add to Cart"));
        }
      } else {
        $("#AddToFamilyCart").addClass("RemoveFromFamilyCart");
        $("#AddToFamilyCart").removeClass("AddToFamilyCart");
        $('i',"#AddToFamilyCart").removeClass("fa-cart-plus");
        $('i',"#AddToFamilyCart").addClass("fa-remove");
        text = $("#AddToFamilyCart").find("span.cartActionDescription")
        if(text){
          $(text).text(i18next.t("Remove from Cart"));
        }
      }
    }  
    
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
          return i18next.t(data);
        }
      },
      {
        width: 'auto',
        title:i18next.t('Edit'),
        data:'Id',
        render: function(data, type, full, meta) {        
          return '<a class="btn btn-success" href="AutoPaymentEditor.php?AutID='+data+'&FamilyID='+full.Familyid+'&linkBack=FamilyView.php?FamilyID='+full.Familyid+'">'+i18next.t('Edit')+'</a>';
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
          return '<a class="btn btn-success" href="PledgeEditor.php?GroupKey='+full.Groupkey+'&amp;linkBack=FamilyView.php?FamilyID='+full.FamId+'">'+i18next.t("Edit")+'</a>';
        }
      },
      {
        width: 'auto',
        title:i18next.t('Delete'),
        data:'Id',
        render: function(data, type, full, meta) {
          return '<button class="btn btn-danger delete-pledge" data-id="'+data+'">'+i18next.t('Delete')+'</button>';
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
/*
      onSelect: function() {
        pledgePaymentTable.search( $(this).val() ).draw();
*/    

/* Custom filtering function which will search data in column four between two values */
$.fn.dataTable.ext.search.push(
    function( settings, data, dataIndex ) {
        if (settings.nTable.id == "automaticPaymentsTable") {
          return true;
        }
        
        var min = parseInt( $('#Min').val(), 10 );
        var max = parseInt( $('#Max').val(), 10 );
        var age = parseFloat( data[2] ) || 0; // use data for the fiscal year
 
        if ( ( isNaN( min ) && isNaN( max ) ) ||
             ( isNaN( min ) && age <= max ) ||
             ( min <= age   && isNaN( max ) ) ||
             ( min <= age   && age <= max ) )
        {
            return true;
        }
        return false;
    }
);

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
