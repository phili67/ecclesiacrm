$(document).ready(function () {
// mailChimp management
  if (window.CRM.normalMail != undefined) {
    window.CRM.APIRequest({
      method: 'POST',
      path: 'persons/isMailChimpActive',
      data: JSON.stringify({"personId": window.CRM.currentPersonID,"email" : window.CRM.normalMail})
    }).done(function(data) {
      if (data.success) {
        if (data.isIncludedInMailing) {
          $("#NewsLetterSend").css('color','green');
          $("#NewsLetterSend").html('<i class="fa fa-check"></i>');
          $("#mailChimpUserNormal").text(data.mailingList);
        } else {
          $("#NewsLetterSend").css('color','red');
          $("#NewsLetterSend").html('<i class="fa fa-times"></i>');
          $("#mailChimpUserNormal").text(i18next.t("None"));
        }
      } else {
        $("#NewsLetterSend").css('color','red');
        $("#NewsLetterSend").html('<i class="fa fa-times"></i>');
      }
    });
  }
  
  if (window.CRM.workMail != undefined) {
    window.CRM.APIRequest({
      method: 'POST',
      path: 'persons/isMailChimpActive',
      data: JSON.stringify({"personId": window.CRM.currentPersonID,"email" : window.CRM.workMail})
    }).done(function(data) {
      if (data.success) {
        if (data.isIncludedInMailing) {
          $("#NewsLetterSend").css('color','green');
          $("#NewsLetterSend").html('<i class="fa fa-check"></i>');
          $("#mailChimpUserWork").text(data.mailingList);
        } else {
          $("#NewsLetterSend").css('color','red');
          $("#NewsLetterSend").html('<i class="fa fa-times"></i>');
          $("#mailChimpUserWork").text(i18next.t("None"));
        }
      } else {
        $("#NewsLetterSend").css('color','red');
        $("#NewsLetterSend").html('<i class="fa fa-times"></i>');
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

      bootbox.confirm({
          title: popupTitle,
          message: '<p style="color: red">' + popupMessage + '</p>',
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
            ret += '<a href="" class="edit-property-btn" data-person_id="'+window.CRM.currentPersonID+'" data-property_id="'+data+'" data-property_Name="'+full.R2pValue+'"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp;&nbsp;&nbsp;';
          } else {
            ret += '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
          }
            
          ret += '<a href="" class="remove-property-btn" data-person_id="'+window.CRM.currentPersonID+'" data-property_id="'+data+'" data-property_Name="'+full.R2pValue+'"><i class="fa fa-trash-o" aria-hidden="true" style="color:red"></a>';
          
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
    }).done(function(data) {
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
  });
  
// notes management
  function addPersonsFromNotes(noteId)
  {
      $('#select-share-persons').find('option').remove();
      
      window.CRM.APIRequest({
            method: 'POST',
            path: 'sharedocument/getallperson',
            data: JSON.stringify({"noteId": noteId})
      }).done(function(data) {    
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
  
  
  function BootboxContentShare(){
    var frm_str = '<h3 style="margin-top:-5px">'+i18next.t("Share your Document")+'</h3>'
       + '<div>'
            +'<div class="row div-title">'
              +'<div class="col-md-4">'
              + '<span style="color: red">*</span>' + i18next.t("With") + ":"                    
              +'</div>'
              +'<div class="col-md-8">'
              +'<select size="6" style="width:100%" id="select-share-persons" multiple>'
              +'</select>'
             +'</div>'
            +'</div>'
            +'<div class="row div-title">'
              +'<div class="col-md-4"><span style="color: red">*</span>' + i18next.t("Set Rights") + ":</div>"
              +'<div class="col-md-8">'
                +'<select name="person-group-Id" id="person-group-rights" class="form-control input-sm"'
                    +'style="width:100%" data-placeholder="text to place">'
                    +'<option value="0">'+i18next.t("Select your rights")+" [👀  ]"+i18next.t("or")+"[👀 ✐]"+' -- </option>'
                    +'<option value="1">'+i18next.t("[👀  ]")+' -- '+i18next.t("[R ]")+'</option>'
                    +'<option value="2">'+i18next.t("[👀 ✐]")+' -- '+i18next.t("[RW]")+'</option>'
                +'</select>'
              +'</div>'
            +'</div>'
            +'<div class="row div-title">'
              +'<div class="col-md-4"><span style="color: red">*</span>' + i18next.t("Send email notification") + ":</div>"
              +'<div class="col-md-8">'
                +'<input id="sendEmail" type="checkbox">'
              +'</div>'
            +'</div>'            
            +'<div class="row div-title">'
              +'<div class="col-md-4"><span style="color: red">*</span>' + i18next.t("Add persons/Family/groups") + ":</div>"
              +'<div class="col-md-8">'
                +'<select name="person-group-Id" id="person-group-Id" class="form-control select2"'
                    +'style="width:100%">'
                +'</select>'
              +'</div>'
            +'</div>'
          +'</div>';
          
          var object = $('<div/>').html(frm_str).contents();

        return object
  }
  
  $(".shareNote").click(function(event){
    var noteId = event.currentTarget.dataset.id;
    var isShared = event.currentTarget.dataset.shared;
    
    var button = $(this); //Assuming first tab is selected by default
        
    var modal = bootbox.dialog({
       message: BootboxContentShare(),
       buttons: [
        {
         label: i18next.t("Delete"),
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
                  }).done(function(data) {
                    $("#select-share-persons option[value='"+personID+"']").remove(); 
                    
                    if (data.count == 0) {
                      $(button).addClass("btn-default");
                      $(button).removeClass("btn-success");
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
         label: i18next.t("Stop sharing"),
         className: "btn btn-danger",
         callback: function() {
          bootbox.confirm(i18next.t("Are you sure ? You are about to stop sharing your document ?"), function(result){ 
            if (result) {
              window.CRM.APIRequest({
                 method: 'POST',
                 path: 'sharedocument/cleardocument',
                 data: JSON.stringify({"noteId":noteId})
              }).done(function(data) {
                addPersonsFromNotes(noteId);
                $(button).addClass("btn-default");
                $(button).removeClass("btn-success");
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
     
     $("#person-group-Id").select2({ 
        language: window.CRM.shortLocale,
        minimumInputLength: 2,
        placeholder: " -- "+i18next.t("Person or Family or Group")+" -- ",
        allowClear: true, // This is for clear get the clear button if wanted 
        ajax: {
            url: function (params){
              return window.CRM.root + "/api/people/search/" + params.term;
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
      
     $("#person-group-rights").change(function() {
       var rightAccess = $(this).val();
       var deferreds = [];
       var i = 0;
       
       $('#select-share-persons :selected').each(function(i, sel){ 
          var personID = $(sel).val();
          var str = $(sel).text();
          
          deferreds.push(          
            window.CRM.APIRequest({
               method: 'POST',
               path: 'sharedocument/setrights',
               data: JSON.stringify({"noteId":noteId,"personID": personID,"rightAccess":rightAccess})
            }).done(function(data) {
              if (rightAccess == 1) {
                res = str.replace(i18next.t("[👀 ✐]"), i18next.t("[👀  ]"));
              } else {
                res = str.replace(i18next.t("[👀  ]"), i18next.t("[👀 ✐]"));
              }
            
              var elt = [personID,res];
              deferreds[i++] = elt;
            })
          );
          
        });
        
        $.when.apply($, deferreds).done(function(data) {
         // all images are now prefetched
         //addPersonsFromNotes(noteId);
         
         deferreds.forEach(function(element) {
           $('#select-share-persons option[value="'+element[0]+'"]').text(element[1]);
         }); 
         
         $("#person-group-rights option:first").attr('selected','selected');
        });
     });
     
     $("#select-share-persons").change(function() {
       $("#person-group-rights").val(0);
     });
          
      
     $("#person-group-Id").on("select2:select",function (e) { 
       var notification = ($("#sendEmail").is(':checked'))?1:0;
       
       if (e.params.data.personID !== undefined) {
           window.CRM.APIRequest({
                method: 'POST',
                path: 'sharedocument/addperson',
                data: JSON.stringify({"noteId":noteId,"currentPersonID":window.CRM.currentPersonID,"personID": e.params.data.personID,"notification":notification})
           }).done(function(data) { 
             addPersonsFromNotes(noteId);
             $(button).addClass("btn-success");
             $(button).removeClass("btn-default");
           });
        } else if (e.params.data.groupID !== undefined) {
           window.CRM.APIRequest({
                method: 'POST',
                path: 'sharedocument/addgroup',
                data: JSON.stringify({"noteId":noteId,"currentPersonID":window.CRM.currentPersonID,"groupID": e.params.data.groupID,"notification":notification})
           }).done(function(data) { 
             addPersonsFromNotes(noteId);
             $(button).addClass("btn-success");
             $(button).removeClass("btn-default");
           });
        } else if (e.params.data.familyID !== undefined) {
           window.CRM.APIRequest({
                method: 'POST',
                path: 'sharedocument/addfamily',
                data: JSON.stringify({"noteId":noteId,"currentPersonID":window.CRM.currentPersonID,"familyID": e.params.data.familyID,"notification":notification})
           }).done(function(data) { 
             addPersonsFromNotes(noteId);
             $(button).addClass("btn-success");
             $(button).removeClass("btn-default");
           });
        }
     });
     
     addPersonsFromNotes(noteId);
     modal.modal('show');
     
    // this will ensure that image and table can be focused
    $(document).on('focusin', function(e) {e.stopImmediatePropagation();});
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
                  }).done(function(data) {
                    if (data && data.success) {
                       window.CRM.dataPropertiesTable.ajax.reload();
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
      if (e.cartPeople.length == 0) {
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
    columns: [
      {
        width: 'auto',
        title:i18next.t('Actions'),
        data:'Id',
        render: function(data, type, full, meta) {
          return '<a href="#" class="delete-volunteerOpportunityId" data-volunteerOpportunityId="'+full.Id+'"><i class="fa fa-trash-o" aria-hidden="true" style="color:red"></a>';
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
          var fmt = window.CRM.datePickerformat.toUpperCase();
          
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
          }).done(function(data) {
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
            }).done(function(data) {
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
          return '<a href="' + window.CRM.root +'/AutoPaymentEditor.php?AutID='+data+'&FamilyID='+full.Familyid+'&linkBack=PersonView.php?PersonID='+window.CRM.currentPersonID+'"><i class="fa fa-pencil" aria-hidden="true"></i></a>'
                +'&nbsp;&nbsp;&nbsp;<a class="delete-payment" data-id="'+data+'"><i class="fa fa-trash-o" aria-hidden="true" style="color:red"></i></a>';
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
          var ret = '<a class="" href="' + window.CRM.root + '/PledgeEditor.php?GroupKey='+full.Groupkey+'&amp;linkBack=PersonView.php?PersonID='+window.CRM.currentPersonID+'"><i class="fa fa-pencil" aria-hidden="true"></i></a>';
          
          if (full.Closed != "1") {
            ret += '&nbsp;&nbsp;&nbsp;<a class="delete-pledge" data-id="'+data+'"><i class="fa fa-trash-o" aria-hidden="true" style="color:red"></i></a>';
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
        if (settings.nTable.id == "automaticPaymentsTable" || settings.nTable.id == "assigned-properties-table" 
          || settings.nTable.id == "assigned-volunteer-opps-table" || settings.nTable.id == "edrive-table") {
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
      }
    }
    
    applyFilter();
});


