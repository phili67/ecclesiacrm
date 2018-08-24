//
//  This code is under copyright not under MIT Licence
//  copyright   : 2018 Philippe Logel all right reserved not MIT licence
//                This code can't be incoprorated in another software without any authorizaion
//
//  Updated : 2018/05/30
//

var editor = null;

$(document).ready(function () {
  $( ".filterByPastor" ).click(function() {
    var ID = $(this).data("personid");
    
    $(".all-items").hide();
    $(".item-"+ID).show();
  });
  
  $( ".filterByPastorAll" ).click(function() {
    $(".all-items").show();
  });
  
  
  
  $( ".modify-pastoral" ).click(function() {
    var ID = $(this).data("id");
    
    window.CRM.APIRequest({
        method: 'POST',
        path: 'pastoralcare/getinfo',
        data: JSON.stringify({"ID":ID})
    }).done(function(data) {
        var id       = data.id;
        var typeid   = data.typeid;
        var typeDesc = data.typedesc;
        var visible  = data.visible;
        var text     = data.text;
    
        if (editor != null) {
          editor.destroy(false);
          editor = null;              
        }

        // this will create the toolbar for the textarea
        modal = createPastoralCareWindow (typeid,typeDesc,visible,id);
    
        $('form #NoteText').val(text);
    
        if (editor == null) {
         editor = CKEDITOR.replace('NoteText',{
           customConfig: window.CRM.root+'/skin/js/ckeditor/calendar_event_editor_config.js',
           language : window.CRM.lang,
           width : '100%'
         });
   
         add_ckeditor_buttons(editor);
       }
   
       modal.modal("show");
    });
  });
    
  $( ".delete-pastoral" ).click(function() {
    var ID = $(this).data("id");
    
    bootbox.confirm({
       title:  i18next.t("Delete Pastoral Care Type") + "?",
        message: i18next.t("This action can never be undone !!!!"),
        buttons: {
          cancel: {
            label: '<i class="fa fa-times"></i> ' + i18next.t("Cancel")
          },
          confirm: {
            label: '<i class="fa fa-check"></i> ' + i18next.t("Confirm")
          }
        },
        callback: function (result) {
          if (result == true)// only Pastoral care can be drag and drop, not anniversary or birthday
          {
             window.CRM.APIRequest({
                method: 'POST',
                path: 'pastoralcare/delete',
                data: JSON.stringify({"ID":ID})
            }).done(function(data) {
               location.reload();
               return true;
            });
          } 
        }        
    });
  });
  
  $( ".newPastorCare" ).click(function() {
    var typeid   = $(this).data('typeid');
    var typeDesc = $(this).data('typedesc');
    var visible  = $(this).data('visible');
    
    if (editor != null) {
        editor.destroy(false);
        editor = null;              
    }

    // this will create the toolbar for the textarea
    modal = createPastoralCareWindow (typeid,typeDesc,visible);
    
    /*var text = "coucou";
    $('form #NoteText').val(text);*/
    
    if (editor == null) {
     editor = CKEDITOR.replace('NoteText',{
       customConfig: window.CRM.root+'/skin/js/ckeditor/calendar_event_editor_config.js',
       language : window.CRM.lang,
       width : '100%'
     });
   
     add_ckeditor_buttons(editor);
   }
   
   modal.modal("show");
  });
  
  function BootboxContent(type,visible){      
    var frm_str = '<h3 style="margin-top:-5px">'+i18next.t("Pastoral Care Note Creation")+'</h3><form id="some-form">'
      +'<div class="row div-title">'
        +'<div class="col-md-3">' + i18next.t('Type') + ":</div>"
        +'<div class="col-md-9"><b>'
          +type
        +'</b></div>'
      +'</div>'
      + '<div>'
          +'<div class="row">'
            +'<div class="col-md-12" style="padding-left:0px;padding-right:2px;">'
                +'<textarea name="NoteText" cols="80" class="form-control input-sm NoteText" id="NoteText"  width="100%" style="width: 100%;height: 4em;"></textarea></div>'
            +'</div>'
          +'</div>'
        +'</div>'
        +'<div class="row  div-title">'
          +'<div class="col-md-6">'
            +'<span style="color: red">*</span>'+i18next.t("For every administrator")
          +'</div>'
          +'<div class="col-md-3">'
            +'<input type="radio" name="visibilityStatus" value="1"'+((visible)?' checked':'')+'/> '+i18next.t("Show")
          +'</div>'
          +'<div class="col-md-3">'
            +'<input type="radio" name="visibilityStatus" value="0" '+((!visible)?' checked':'')+'/> '+i18next.t("Hide")
          +'</div>'
        +'</div>'
     +'</form>';

    var object = $('<div/>').html(frm_str).contents();

    return object
  }
    
  function createPastoralCareWindow (typeID,typeDesc,visible,id) // dialogType : createEvent or modifyEvent, eventID is when you modify and event
  {
    if (id === undefined) {
      id = -1;
    } 

    var modal = bootbox.dialog({
       message: BootboxContent(typeDesc,visible),
       buttons: [
        {
         label: i18next.t("Close"),
         className: "btn btn-default",
         callback: function() {
            console.log("just do something on close");
         }
        },
        {
         label: i18next.t("Save"),
         className: "btn btn-primary",
         callback: function() {
            var visibilityStatus  = $('input[name="visibilityStatus"]:checked').val();
            var NoteText          = CKEDITOR.instances['NoteText'].getData();//$('form #NoteText').val();
            
            if (id == -1) {
              window.CRM.APIRequest({
                  method: 'POST',
                  path: 'pastoralcare/add',
                  data: JSON.stringify({"typeID":typeID,"personID":currentPersonID,"currentPastorId":currentPastorId,"typeDesc":typeDesc,"visibilityStatus":visibilityStatus,"noteText":NoteText})
              }).done(function(data) {
                 location.reload();
                 return true;
              });
            } else {
              window.CRM.APIRequest({
                  method: 'POST',
                  path: 'pastoralcare/modify',
                  data: JSON.stringify({"ID":id,"typeID":typeID,"personID":currentPersonID,"currentPastorId":currentPastorId,"typeDesc":typeDesc,"visibilityStatus":visibilityStatus,"noteText":NoteText})
              }).done(function(data) {
                 location.reload();
                 return true;
              })
            }

          }
        }
       ],
       show: false/*,
       onEscape: function() {
          modal.modal("hide");
       }*/
     });
     
             
    // this will ensure that image and table can be focused
    $(document).on('focusin', function(e) {e.stopImmediatePropagation();});
      
    return modal;
  }
  

});