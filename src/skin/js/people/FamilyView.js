$(document).ready(function () {

// mailchimp management
  if (window.CRM.familyMail != undefined) {
    window.CRM.APIRequest({
      method: 'POST',
      path: 'families/isMailChimpActive',
      data: JSON.stringify({"familyId": window.CRM.currentFamily,"email" : window.CRM.familyMail})
    },function(data) {
      if (data.success) {
        if (data.isIncludedInMailing) {
          $("#NewsLetterSend").css('color','green');
          $("#NewsLetterSend").html('<i class="fas fa-check"></i>');
          if (data.mailChimpActiv) {
            $("#mailChimpUserNormal").text(data.mailingList);
          }
        } else {
          $("#NewsLetterSend").css('color','red');
          $("#NewsLetterSend").html('<i class="fas fa-times"></i>');
          $("#mailChimpUserNormal").text(i18next.t("None"));
        }
      } else {
        $("#NewsLetterSend").css('color','red');
        $("#NewsLetterSend").html('<i class="fas fa-times"></i>');
      }
    });
  }

// end of mailchimp management

  $("#activateDeactivate").click(function () {
    console.log("click activateDeactivate");
    popupTitle = (window.CRM.currentActive == true ? i18next.t("Confirm Deactivation") : i18next.t("Confirm Activation") );
    if (window.CRM.currentActive == true) {
      popupMessage = i18next.t("Please confirm deactivation of family") + ': ' + window.CRM.fam_Name;
    } else {
      popupMessage = i18next.t("Please confirm activation of family") + ': ' + window.CRM.fam_Name + "<br>";
    }

    popupWarning = i18next.t("Be carefull with the GDPR, when a family is de-activated, you have to ask all the persons of the family first, if you want to re-activate the account !<br><br>WITHOUT ANY AUTHORIZATION, THE GDPR MAKE YOUR USE OF THIS FAMILY ILLEGAL !!!!!");

    bootbox.confirm({
      title: popupTitle,
      message: '<p style="color: red">' + popupWarning + '</p><br><p>' + popupMessage + '</p>',
      callback: function (result) {
        if (result) {
          $.ajax({
            method: "POST",
            url: window.CRM.root + "/api/families/" + window.CRM.currentFamily + "/activate/" + !window.CRM.currentActive,
            dataType: "json",
            encode: true
          }).done(function (data) {
            if (data.success == true)
              window.location.href = window.CRM.root + "/v2/people/family/view/" + window.CRM.currentFamily;
            });
          }
        }
      });
    });

    $("#deletePhoto").click(function () {
      $.ajax({
        type: "POST",
        url: window.CRM.root + "/api/families/" + window.CRM.currentFamily + "/photo",
        encode: true,
        dataType: 'json',
        data: {
          "_METHOD": "DELETE"
        }
      }).done(function (data) {
        location.reload();
      });
    });

    window.CRM.photoUploader = $("#photoUploader").PhotoUploader({
      url: window.CRM.root + "/api/families/" + window.CRM.currentFamily + "/photo",
      maxPhotoSize: window.CRM.maxUploadSize,
      photoHeight: window.CRM.iPhotoHeight,
      photoWidth: window.CRM.iPhotoWidth,
      done: function (e) {
        location.reload();
      }
    });

    contentExists(window.CRM.root + "/api/families/" + window.CRM.currentFamily + "/photo", function (success) {
      if (success) {
        $("#view-larger-image-btn").removeClass('hide');

        $("#view-larger-image-btn").click(function () {
          bootbox.alert({
            title: i18next.t("Family Photo"),
            message: '<img class="img-rounded img-responsive center-block" src="' + window.CRM.root + '/api/families/' + window.CRM.currentFamily + '/photo" />',
            backdrop: true
          });
        });
      }
    });

    $(".input-family-properties").select2({
        language: window.CRM.shortLocale
    });


    window.CRM.dataPropertiesTable = $("#assigned-properties-table").DataTable({
      ajax:{
        url: window.CRM.root + "/api/families/familyproperties/"+window.CRM.currentFamily,
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
          title:i18next.t('Edit'),
          data:'ProId',
          render: function(data, type, full, meta) {
            if (full.ProPrompt != '') {
              return '<a href="#" class="edit-property-btn" data-family_id="'+window.CRM.currentFamily+'" data-property_id="'+data+'" data-property_Name="'+full.R2pValue+'"><i class="fas fa-pencil-alt" aria-hidden="true"></a>';
            }

            return "";
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
            return '<a href="#" class="remove-property-btn" data-family_id="'+window.CRM.currentFamily+'" data-property_id="'+data+'" data-property_Name="'+full.R2pValue+'"><i class="far fa-trash-alt" aria-hidden="true" style="color:red"></a>';
          }
        },
        {
          width: 'auto',
          title:i18next.t('Name'),
          data:'ProName',
          render: function(data, type, full, meta) {
            return i18next.t(data);
          }
        }
      ],
      responsive: true,
      createdRow : function (row,data,index) {
        $(row).addClass("paymentRow");
      }
    });


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
              $('<label style="color:white"></label>').html(pro_prompt)
            )
            .append(
              $('<textarea rows="3" class="form-control property-value" name="PropertyValue"></textarea>').val(pro_value)
            );
        }
    });

    $('body').on('click','.remove-property-btn',function(){
        event.preventDefault();
        var thisLink = $(this);
        var family_id = thisLink.data('family_id');
        var property_id = thisLink.data('property_id');

        bootbox.confirm({
          buttons: {
            confirm: {
              label: i18next.t('OK'),
              className: 'btn btn-default'
            },
            cancel: {
              label: i18next.t('Cancel'),
              className: 'btn btn-primary'
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
              },function(data) {
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
      var family_id = thisLink.data('family_id');
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
              path: 'properties/families/assign',
              data: JSON.stringify({"FamilyId": family_id,"PropertyId" : property_id, "PropertyValue":result})
            },function(data) {
              if (data && data.success) {
                window.CRM.dataPropertiesTable.ajax.reload()
              }
            });
          }
        }
      });
    });

  $('body').on('click','.assign-property-btn',function(){
   var property_id = $('.input-family-properties').val();
   var property_pro_value = $('.property-value').val();

    window.CRM.APIRequest({
      method: 'POST',
      path: 'properties/families/assign',
      data: JSON.stringify({"FamilyId": window.CRM.currentFamily,"PropertyId" : property_id,"PropertyValue" : property_pro_value})
    },function(data) {
      if (data && data.success) {
           window.CRM.dataPropertiesTable.ajax.reload();
           promptBox.removeClass('form-group').html('');
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

    $(document).on("click",".AddToFamilyCart", function(){
      clickedButton = $(this);
      window.CRM.cart.addFamily(clickedButton.data("cartfamilyid"),function()
      {
        $(clickedButton).addClass("RemoveFromFamilyCart");
        $(clickedButton).removeClass("AddToFamilyCart");
        $('i',clickedButton).addClass("fa-times");
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
        $('i',clickedButton).removeClass("fa-times");
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
        $('span i:nth-child(2)',clickedButton).removeClass("fa-times");
        $('span i:nth-child(2)',clickedButton).addClass("fa-cart-plus");
      });
    });

    $(document).on("click",".AddToPeopleCart", function(){
      clickedButton = $(this);
      window.CRM.cart.addPerson([clickedButton.data("cartpersonid")],function()
      {
        $(clickedButton).addClass("RemoveFromPeopleCart");
        $(clickedButton).removeClass("AddToPeopleCart");
        $('span i:nth-child(2)',clickedButton).addClass("fa-times");
        $('span i:nth-child(2)',clickedButton).removeClass("fa-cart-plus");
      });
    });

    // newMessage event subscribers : Listener CRJSOM.js
    $(document).on("updateCartMessage", updateLittleButtons);

    function updateLittleButtons(e) {
        var cartPeople = e.people;

        personButtons = $("a[data-cartpersonid]");
        $(personButtons).each(function(index,personButton){
          personID = $(personButton).data("cartpersonid")
          if (cartPeople.length > 0 && cartPeople.includes(personID)) {
            personPresent = true;
            $(personButton).addClass("RemoveFromPeopleCart");
            $(personButton).removeClass("AddToPeopleCart");
            fa = $(personButton).find("i.fas.fa-inverse");
            $(fa).addClass("fa-times");
            $(fa).removeClass("fa-cart-plus");
            text = $(personButton).find("span.cartActionDescription")
            if(text){
              $(text).text(i18next.t("Remove from Cart"));
            }
          } else {
            $(personButton).addClass("AddToPeopleCart");
            $(personButton).removeClass("RemoveFromPeopleCart");
            fa = $(personButton).find("i.fas.fa-inverse");

            $(fa).removeClass("fa-times");
            $(fa).addClass("fa-cart-plus");
            text = $(personButton).find("span.cartActionDescription")
            if(text){
              $(text).text(i18next.t("Add to Cart"));
            }
          }
        });

        if (cartPeople.length == 0) {
            $("#AddToFamilyCart").addClass("AddToFamilyCart");
            $("#AddToFamilyCart").removeClass("RemoveFromFamilyCart");
            $('i',"#AddToFamilyCart").removeClass("fa-times");
            $('i',"#AddToFamilyCart").addClass("fa-cart-plus");
            text = $("#AddToFamilyCart").find("span.cartActionDescription")
            if(text){
                $(text).text(i18next.t("Add to Cart"));
            }
        } else {
            var peopleInCart = false;
            var personButtons = $("a[data-cartpersonid]");
            $(personButtons).each(function(index,personButton){
                personID = $(personButton).data("cartpersonid")
                if (cartPeople.includes(personID)) {
                    peopleInCart = true;
                }
            });

            if (peopleInCart) {
                $("#AddToFamilyCart").addClass("RemoveFromFamilyCart");
                $("#AddToFamilyCart").removeClass("AddToFamilyCart");
                $('i',"#AddToFamilyCart").removeClass("fa-cart-plus");
                $('i',"#AddToFamilyCart").addClass("fa-times");
                text = $("#AddToFamilyCart").find("span.cartActionDescription")
                if(text){
                    $(text).text(i18next.t("Remove from Cart"));
                }
            } else {
                $("#AddToFamilyCart").addClass("AddToFamilyCart");
                $("#AddToFamilyCart").removeClass("RemoveFromFamilyCart");
                $('i',"#AddToFamilyCart").removeClass("fa-times");
                $('i',"#AddToFamilyCart").addClass("fa-cart-plus");
                text = $("#AddToFamilyCart").find("span.cartActionDescription")
                if(text){
                    $(text).text(i18next.t("Add to Cart"));
                }
            }
        }
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
        render: function (data, type, full, meta) {
          return Number(data).toLocaleString(window.CRM.lang);
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
        title:i18next.t('Action'),
        data:'Id',
        render: function(data, type, full, meta) {
          return '<a class="" href="' + window.CRM.root + '/AutoPaymentEditor.php?AutID='+data+'&FamilyID='+full.Familyid+'&linkBack=v2/people/family/view/'+full.Familyid+'"><i class="fas fa-pencil-alt" aria-hidden="true"></i></a>'
                +'&nbsp;&nbsp;&nbsp;<a class="delete-payment" data-id="'+data+'"><i class="far fa-trash-alt" aria-hidden="true" style="color:red"></i></a>';
        }
      },
      {
        width: 'auto',
        title:i18next.t('Updated By'),
        data:'DateLastEdited',
        render: function(data, type, full, meta) {
          var fmt = window.CRM.datePickerformat.toUpperCase();

          if (window.CRM.timeEnglish == true) {
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
          },function(data) {
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
          return Number(data).toLocaleString(window.CRM.lang);
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
        title:i18next.t('Action'),
        data:'Id',
        render: function(data, type, full, meta) {
          var ret = '<a class="" href="' + window.CRM.root + '/PledgeEditor.php?GroupKey='+full.Groupkey+'&amp;linkBack=v2/people/family/view/'+full.FamId+'"><i class="fas fa-pencil-alt" aria-hidden="true"></i></a>';
          if (full.Closed != "1") {
            ret += '&nbsp;&nbsp;&nbsp;<a class="delete-pledge" data-id="'+data+'"><i class="far fa-trash-alt" aria-hidden="true" style="color:red"></i></a>';
          }

          return ret;
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
          },function(data) {
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
   $.fn.dataTable.ext.search.push(function( settings, data, dataIndex ) {
        if (settings.nTable.id == "automaticPaymentsTable") {
          return true;
        }

        var fmt = window.CRM.datePickerformat.toUpperCase();

        var min = moment($('#Min').val(),fmt);
        var max = moment($('#Max').val(),fmt);
        var age = moment(data[4],fmt);

        if ( ( isNaN( min ) && isNaN( max ) ) ||
             ( isNaN( min ) && age <= max ) ||
             ( min <= age   && isNaN( max ) ) ||
             ( min <= age   && age <= max ) )
        {
            return true;
        }
        return false;
    });

    $("#Min").on('change', function(){
      pledgePaymentTable.draw();
      var fmt = window.CRM.datePickerformat.toUpperCase();

      var min = moment($(this).val(),fmt).format('YYYY-MM-DD');

      window.CRM.APIRequest({
        method: 'POST',
        path: 'users/showsince',
        data: JSON.stringify({"date": min})
      },function(data) {
      });
    });

    $("#Max").on('change', function(){
      pledgePaymentTable.draw();
      var fmt = window.CRM.datePickerformat.toUpperCase();

      var max = moment($(this).val(),fmt).format('YYYY-MM-DD');

      window.CRM.APIRequest({
        method: 'POST',
        path: 'users/showto',
        data: JSON.stringify({"date": max})
      },function(data) {
      });
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
