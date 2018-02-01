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
});
