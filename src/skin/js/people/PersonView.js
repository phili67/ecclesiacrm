$(document).ready(function () {
// mailChimp management
  if (window.CRM.normalMail != undefined) {
    window.CRM.APIRequest({
      method: 'POST',
      path: 'persons/isMailChimpActive',
      data: JSON.stringify({"personId": window.CRM.currentPersonID,"email" : window.CRM.normalMail})
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

  if (window.CRM.workMail != undefined) {
    window.CRM.APIRequest({
      method: 'POST',
      path: 'persons/isMailChimpActive',
      data: JSON.stringify({"personId": window.CRM.currentPersonID,"email" : window.CRM.workMail})
    },function(data) {
      if (data.success) {
        if (data.isIncludedInMailing) {
          $("#NewsLetterSend").css('color','green');
          $("#NewsLetterSend").html('<i class="fas fa-check"></i>');
          $("#mailChimpUserWork").text(data.mailingList);
        } else {
          $("#NewsLetterSend").css('color','red');
          $("#NewsLetterSend").html('<i class="fas fa-times"></i>');
          $("#mailChimpUserWork").text(i18next.t("None"));
        }
      } else {
        $("#NewsLetterSend").css('color','red');
        $("#NewsLetterSend").html('<i class="fas fa-times"></i>');
      }
    });
  }
  // end mailChimp management

  $("#activateDeactivate").click(function () {
      console.log("click activateDeactivate");
      popupTitle = (window.CRM.currentActive == true ? i18next.t("Confirm Deactivation") : i18next.t('Confirm Activation'));
      if (window.CRM.currentActive == true) {
          popupMessage = i18next.t("Please confirm deactivation of person") + ': ' + window.CRM.personFullName;
      }
      else {
          popupMessage = i18next.t("Please confirm activation of person") + ': ' + window.CRM.personFullName;
      }

      popupWarning = i18next.t("Be carefull with the GDPR, when a person is de-activated, you have to ask the person first, if you want to reactivate the account !<br><br>WITHOUT ANY AUTHORIZATION, THE GDPR MAKE YOUR USE OF THIS PERSON ILLEGAL !!!!!");

      bootbox.confirm({
          title: popupTitle,
          message: '<p style="color: red">' + popupWarning + '</p><br><p>' + popupMessage + '</p>',
          buttons: {
              cancel: {
                  className: 'btn-primary',
                  label: '<i class="fas fa-times"></i>' + i18next.t("Cancel")
              },
              confirm: {
                  className: 'btn-danger',
                  label: '<i class="far fa-trash-alt"></i>' + ((window.CRM.currentActive == true)?i18next.t("Deactivate"):i18next.t("Activate"))
              }
          },
          callback: function (result) {
              if (result) {
                  $.ajax({
                      method: "POST",
                      url: window.CRM.root + "/api/persons/" + window.CRM.currentPersonID + "/activate/" + !window.CRM.currentActive,
                      dataType: "json",
                      encode: true
                  }).done(function (data) {
                    if (data.success == true) {
                        window.location.href = window.CRM.root + "/PersonView.php?PersonID=" + window.CRM.currentPersonID;
                    }
                  });
              }
          }
      });
  });

  $("#deletePhoto").click (function () {
    $.ajax({
    type: "POST",
    url: window.CRM.root + "/api/persons/"+window.CRM.currentPersonID+"/photo",
    encode: true,
    dataType: 'json',
    data: {
      "_METHOD": "DELETE"
    }
    }).done(function(data) {
      location.reload();
    });
  });

  window.CRM.photoUploader =  $("#photoUploader").PhotoUploader({
    url: window.CRM.root + "/api/persons/"+window.CRM.currentPersonID+"/photo",
    maxPhotoSize: window.CRM.maxUploadSize,
    photoHeight: window.CRM.iPhotoHeight,
    photoWidth: window.CRM.iPhotoWidth,
    done: function(e) {
      window.location.reload();
    }
  });

  $("#uploadImageButton").click(function(){
    window.CRM.photoUploader.show();
  });


  $(document).ready(function() {

      $("#input-volunteer-opportunities").select2({
        language: window.CRM.shortLocale
      });
      $("#input-person-properties").select2({
        language: window.CRM.shortLocale
      });

      contentExists(window.CRM.root + "/api/persons/" + window.CRM.currentPersonID + "/photo", function(success) {
          if (success) {
              $("#view-larger-image-btn").removeClass('hide');

              $("#view-larger-image-btn").click(function() {
                  bootbox.alert({
                      title: i18next.t("Photo"),
                      message: '<img class="img-rounded img-responsive center-block" src="'+window.CRM.root+'/api/persons/' + window.CRM.currentPersonID + '/photo" />',
                      backdrop: true
                  });
              });
          }
      });

  });

  // the assigned properties
  window.CRM.dataPropertiesTable = $("#assigned-properties-table").DataTable({
    ajax:{
      url: window.CRM.root + "/api/persons/personproperties/"+window.CRM.currentPersonID,
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
        title:i18next.t('Actions'),
        data:'ProId',
        render: function(data, type, full, meta) {
          var ret = '';
          if (full.ProPrompt != '') {
            ret += '<a href="" class="edit-property-btn" data-person_id="'+window.CRM.currentPersonID+'" data-property_id="'+data+'" data-property_Name="'+full.R2pValue+'"><i class="fas fa-pencil-alt" aria-hidden="true"></i></a>&nbsp;&nbsp;&nbsp;';
          } else {
            ret += '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
          }

          ret += '<a href="" class="remove-property-btn" data-person_id="'+window.CRM.currentPersonID+'" data-property_id="'+data+'" data-property_Name="'+full.R2pValue+'"><i class="far fa-trash-alt" aria-hidden="true" style="color:red"></a>';

          return ret;
        }
      },
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
      }
    ],
    responsive: true,
    createdRow : function (row,data,index) {
      $(row).addClass("paymentRow");
    }
  });

  $('body').on('click','.assign-property-btn',function(){
   var property_id = $('.input-person-properties').val();
   var property_pro_value = $('.property-value').val();

    window.CRM.APIRequest({
      method: 'POST',
      path: 'properties/persons/assign',
      data: JSON.stringify({"PersonId": window.CRM.currentPersonID,"PropertyId" : property_id,"PropertyValue" : property_pro_value})
    },function(data) {
      if (data && data.success) {
         window.CRM.dataPropertiesTable.ajax.reload();
         promptBox.removeClass('form-group').html('');

         if (data.count > 0) {
            $("#properties-warning").hide();
            $("#properties-table").show();
         }
      }
    });
  });


  $('.changeRole').click(function(event) {
    var GroupID = $(this).data("groupid");
    window.CRM.groups.promptSelection({Type:window.CRM.groups.selectTypes.Role,GroupID:GroupID},function(selection){
      window.CRM.groups.addPerson(GroupID,window.CRM.currentPersonID,selection.RoleID, function(){
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
            className: 'btn-danger'
        },
        cancel: {
          label: i18next.t('No'),
          className: 'btn-primary'
        }
      },
      callback: function (result)
      {
        if (result)
        {
          window.CRM.groups.removePerson(targetGroupID,window.CRM.currentPersonID, function(){
              window.location.href = window.CRM.root + '/PersonView.php?PersonID=' + window.CRM.currentPersonID + '&group=true';
            }
          );
        }
      }
    });
  });

// notes management
  function addPersonsFromNotes(noteId)
  {
      $('#select-share-persons').find('option').remove();

      window.CRM.APIRequest({
            method: 'POST',
            path: 'sharedocument/getallperson',
            data: JSON.stringify({"noteId": noteId})
      },function(data) {
        var elt = document.getElementById("select-share-persons");
        var len = data.length;

        for (i=0; i<len; ++i) {
          var option = document.createElement("option");
          // there is a groups.type in function of the new plan of schema
          option.text = data[i].name;
          //option.title = data[i].type;
          option.value = data[i].id;

          elt.appendChild(option);
        }
      });

      //addProfilesToMainDropdown();
  }


  $(".shareNote").click(function(event){
    var noteId = event.currentTarget.dataset.id;
    var isShared = event.currentTarget.dataset.shared;

    var button = $(this); //Assuming first tab is selected by default
    var state  = button.find('.fa-stack-2x');

    var modal = bootbox.dialog({
       message: window.CRM.BootboxContentShareFiles(),
       size: "large",
       buttons: [
        {
         label: '<i class="fas fa-times"></i> ' + i18next.t("Delete"),
         className: "btn btn-warning",
         callback: function() {
            bootbox.confirm(i18next.t("Are you sure ? You're about to delete this Person ?"), function(result){
              if (result) {
                $('#select-share-persons :selected').each(function(i, sel){
                  var personID = $(sel).val();

                  window.CRM.APIRequest({
                     method: 'POST',
                     path: 'sharedocument/deleteperson',
                     data: JSON.stringify({"noteId":noteId,"personID": personID})
                  },function(data) {
                    $("#select-share-persons option[value='"+personID+"']").remove();

                    if (data.count == 0) {
                      $(state).css('color', '#777');
                      $(button).data('shared',0);
                    }

                    $("#person-group-Id").val("").trigger("change");
                  });
                });
              }
            });
            return false;
         }
        },
        {
         label: '<i class="far fa-stop-circle"></i> ' + i18next.t("Stop sharing"),
         className: "btn btn-danger",
         callback: function() {
          bootbox.confirm(i18next.t("Are you sure ? You are about to stop sharing your document ?"), function(result){
            if (result) {
              window.CRM.APIRequest({
                 method: 'POST',
                 path: 'sharedocument/cleardocument',
                 data: JSON.stringify({"noteId":noteId})
              },function(data) {
                addPersonsFromNotes(noteId);
                $(state).css('color', '#777');
                $(button).data('shared',0);
                modal.modal("hide");
              });
            }
          });
          return false;
         }
        },
        {
         label: '<i class="fas fa-check"></i> ' + i18next.t("Ok"),
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

    window.CRM.addSharedButtonsActions(noteId,isShared,button,state,modal);
  });

  $(".filter-timeline").change(function() {
       switch ($(this).val()) {
         case 'shared':
           $(".type-file").hide();
           $(".icon-file").hide();
           $(".type-note").hide();
           $(".icon-note").hide();
           $(".type-video").hide();
           $(".icon-video").hide();
           $(".type-audio").hide();
           $(".icon-audio").hide();
           $(".type-shared").show();
           $(".icon-shared").show();
           break;
         case 'file':
           $(".type-file").show();
           $(".icon-file").show();
           $(".type-shared").hide();
           $(".icon-shared").hide();
           $(".type-note").hide();
           $(".icon-note").hide();
           $(".type-video").hide();
           $(".icon-video").hide();
           $(".type-audio").hide();
           $(".icon-audio").hide();
           break;
         case 'note':
           $(".type-shared").hide();
           $(".icon-shared").hide();
           $(".type-file").hide();
           $(".icon-file").hide();
           $(".type-note").show();
           $(".icon-note").show();
           $(".type-video").hide();
           $(".icon-video").hide();
           $(".type-audio").hide();
           $(".icon-audio").hide();
           break;
         case 'audio':
           $(".type-shared").hide();
           $(".icon-shared").hide();
           $(".type-file").hide();
           $(".icon-file").hide();
           $(".type-note").hide();
           $(".icon-note").hide();
           $(".type-audio").show();
           $(".icon-audio").show();
           $(".type-video").hide();
           $(".icon-video").hide();
           break;
         case 'video':
           $(".type-shared").hide();
           $(".icon-shared").hide();
           $(".type-file").hide();
           $(".icon-file").hide();
           $(".type-note").hide();
           $(".icon-note").hide();
           $(".type-audio").hide();
           $(".icon-audio").hide();
           $(".type-video").show();
           $(".icon-video").show();
           break;
         case 'all':
           $(".type-shared").hide();
           $(".icon-shared").hide();
           $(".type-file").show();
           $(".icon-file").show();
           $(".type-note").show();
           $(".icon-note").show();
           $(".type-video").show();
           $(".icon-video").show();
           $(".type-audio").show();
           $(".icon-audio").show();
           break;
       }

  });

// end of note management

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
                    $('<label style="color:white"></label>').html(pro_prompt)
                )
                .append(
                    $('<textarea rows="3" class="form-control property-value" name="PropertyValue"></textarea>').val(pro_value)
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

    $('body').on('click','.remove-property-btn',function(){
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
              className: 'btn btn-danger'
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
                $.ajax({
                    type: 'DELETE',
                    url: url,
                    data: dataToSend,
                    dataType: 'json',
                    success: function (data, status, xmlHttpReq) {
                        if (data && data.success) {
                          window.CRM.dataPropertiesTable.ajax.reload();

                          if (data.count == 0) {
                              $("#properties-warning").show();
                              $("#properties-table").hide();
                          }
                        }
                    }
                });
            }
          }
        });
    });

    $('body').on('click','.edit-property-btn',function(){
        event.preventDefault();
        var thisLink = $(this);
        var person_id = thisLink.data('person_id');
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
                  path: 'properties/persons/assign',
                  data: JSON.stringify({"PersonId": person_id,"PropertyId" : property_id, "PropertyValue":result})
                  },function(data) {
                    if (data && data.success) {
                       window.CRM.dataPropertiesTable.ajax.reload();
                    }
                });
            }
          }
        });
    });

    $('#edit-classification-btn').click(function (event) {
        event.preventDefault();
        var thisLink = $(this);
        var personId = thisLink.data('person_id');
        var classificationId = thisLink.data('classification_id');
        var classificationRole = thisLink.data('classification_role');

        $.ajax({
            type: 'GET',
            dataType: 'json',
            url: window.CRM.root + '/api/people/classifications/all',
            success: function (data, status, xmlHttpReq) {
                if (data.Classifications.length) {
                    classifications = [{text: classificationRole, value: ''}];
                    for (var i=0; i < data.Classifications.length; i++) {
                        if (data.Classifications[i].OptionId == classificationId) {
                            continue;
                        }

                        classifications[classifications.length] = {
                            text: data.Classifications[i].OptionName,
                            value: data.Classifications[i].OptionId
                        };
                    }

                    bootbox.prompt({
                        title:i18next.t('Change classification'),
                        inputType: 'select',
                        inputOptions: classifications,
                        callback: function (result) {
                            if (result) {
                                $.ajax({
                                    type: 'POST',
                                    data: { personId: personId, classId: result },
                                    dataType: 'json',
                                    url: window.CRM.root + '/api/people/person/classification/assign',
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
                      title:i18next.t('Change role'),
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
        $('i',clickedButton).addClass("fa-times");
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
        $('i',clickedButton).removeClass("fa-times");
        $('i',clickedButton).addClass("fa-cart-plus");
        text = $(clickedButton).find("span.cartActionDescription");
        if(text){
          $(text).text(i18next.t("Add to Cart"));
        }
      });
    });

    // newMessage event subscribers : Listener CRJSOM.js
    $(document).on("updateCartMessage", updateButtons);

    // newMessage event handler
    function updateButtons(e) {
      if (e.people.length == 0) {
        $("#AddPersonToCart").addClass("AddOneToPeopleCart");
        $("#AddPersonToCart").removeClass("RemoveOneFromPeopleCart");
        $('i',"#AddPersonToCart").removeClass("fa-times");
        $('i',"#AddPersonToCart").addClass("fa-cart-plus");
        text = $("#AddPersonToCart").find("span.cartActionDescription")
        if(text){
          $(text).text(i18next.t("Add to Cart"));
        }
      }
    }

    // end of newMessage event subscribers : Listener CRJSOM.js


  // this part allows to render the dataTable responsive in Tab
  $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        $($.fn.dataTable.tables(true)).DataTable()
           .columns.adjust()
           .responsive.recalc();
  });


  assignedVolunteerTable = $("#assigned-volunteer-opps-table").DataTable({
    ajax:{
      url: window.CRM.root + "/api/persons/volunteers/"+window.CRM.currentPersonID,
      type: 'POST',
      contentType: "application/json",
      dataSrc: "VolunteerOpportunities"
    },
    "language": {
      "url": window.CRM.plugin.dataTable.language.url
    },
    "searching": false,
    columns: [
      {
        width: 'auto',
        title:i18next.t('Actions'),
        data:'Id',
        render: function(data, type, full, meta) {
          return '<a href="#" class="delete-volunteerOpportunityId" data-volunteerOpportunityId="'+full.Id+'"><i class="far fa-trash-alt" aria-hidden="true" style="color:red"></a>';
        }
      },
      {
        width: 'auto',
        title:i18next.t('Name'),
        data:'Name',
        render: function(data, type, full, meta) {
          return data;
        }
      },
      {
        width: 'auto',
        title:i18next.t('Description'),
        data:'Description',
        render: function(data, type, full, meta) {
          return data;
        }
      },
    ],
    responsive: true,
    createdRow : function (row,data,index) {
      $(row).addClass("assignedVolunteerRow");
    }
  });

  $(document).on("click",".delete-volunteerOpportunityId", function(){
     var volunteerOpportunityId = $(this).data("volunteeropportunityid");

     bootbox.confirm(i18next.t("Confirm Delete volunteer Opportunity"), function(confirmed) {
        if (confirmed) {
          window.CRM.APIRequest({
            method: 'POST',
            path: 'persons/volunteers/delete',
            data: JSON.stringify({"personId": window.CRM.currentPersonID,"volunteerOpportunityId" : volunteerOpportunityId})
          },function(data) {
            assignedVolunteerTable.ajax.reload();

            if (data && data.success) {
              assignedVolunteerTable.ajax.reload();
              if (data.count == 0) {
                 $("#volunter-warning").show();
                 $("#volunter-table").hide();
              }
            }
          });
        }
     });
  });

  $(document).on("click",".VolunteerOpportunityAssign", function(){
     $('#input-volunteer-opportunities').each(function(i, sel){
        var volIDs = $(sel).val();

        if (volIDs != null) {
          volIDs.forEach(function(volID) {
            window.CRM.APIRequest({
              method: 'POST',
              path: 'persons/volunteers/add',
              data: JSON.stringify({"personId": window.CRM.currentPersonID,"volID" : volID})
            },function(data) {
              assignedVolunteerTable.ajax.reload();

              if (data && data.success) {
                $("#input-volunteer-opportunities").val('').trigger('change')
                if (data.count > 0) {
                  $("#volunter-warning").hide();
                  $("#volunter-table").show();
                }
              }
            });
          });
        }
     });
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
          return '<a href="' + window.CRM.root +'/AutoPaymentEditor.php?AutID='+data+'&FamilyID='+full.Familyid+'&linkBack=PersonView.php?PersonID='+window.CRM.currentPersonID+'"><i class="fas fa-pencil-alt" aria-hidden="true"></i></a>'
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
          var ret = '<a class="" href="' + window.CRM.root + '/PledgeEditor.php?GroupKey='+full.Groupkey+'&amp;linkBack=PersonView.php?PersonID='+window.CRM.currentPersonID+'"><i class="fas fa-pencil-alt" aria-hidden="true"></i></a>';

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
        if (settings.nTable.id == "automaticPaymentsTable" || settings.nTable.id  == "edrive-table" ) {
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
      }
    }

    applyFilter();

    window.CRM.AutomaticDarkModeFunction = function (darkMode)
    {
        if (darkMode) {
            $('.btn-box-tool').addClass('dark-mode');
        } else {
            $('.btn-box-tool').removeClass('dark-mode');
        }
    }

    <!-- for the theme before jquery load is finished -->
    if (window.CRM.sLightDarkMode == "automatic") {
        let matched = window.matchMedia('(prefers-color-scheme: dark)').matches;

        if(matched) {// we're on dark mode
            $('.btn-box-tool').addClass('dark-mode');
        } else {// we're in light mode
            $('.btn-box-tool').removeClass('dark-mode');
        }
    }
});


