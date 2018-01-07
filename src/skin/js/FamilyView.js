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


  $("#pledge-payment-table").DataTable(window.CRM.plugin.dataTable);


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
  
  $("#AddFamilyToCart").click(function(){
    window.CRM.cart.addFamily($(this).data("familyid"));
  });
  
});
