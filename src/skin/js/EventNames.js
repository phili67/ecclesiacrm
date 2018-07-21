//
//  This code is under copyright not under MIT Licence
//  copyright   : 2018 Philippe Logel all right reserved not MIT licence
//                This code can't be incoprorated in another software without any authorizaion
//
//  Updated : 2018/05/30
//

var editor = null;

$(document).ready(function () {

  function addEvent(dateStart,dateEnd)
  {
     if (editor != null) {
       editor.destroy(false);
       editor = null;              
     }

     modal = createEventEditorWindow (dateStart,dateEnd,'createEvent',0,'','EventNames.php');
       
     // we add the calendars and the types
     addCalendars();
     addCalendarEventTypes(-1,true);

     //Timepicker
     $('.timepicker').timepicker({
       showInputs: false,
       showMeridian: (window.CRM.timeEnglish == "true")?true:false
     });

     $('.date-picker').datepicker({format:window.CRM.datePickerformat, language: window.CRM.lang});

     $('.date-picker').click('focus', function (e) {
       e.preventDefault();
       $(this).datepicker('show');
     });

     $('.date-start').hide();
     $('.date-end').hide();
     $('.date-recurrence').hide();
     $(".eventNotes").hide();

     $("#typeEventrecurrence").prop("disabled", true);
     $("#endDateEventrecurrence").prop("disabled", true);

     // this will ensure that image and table can be focused
     $(document).on('focusin', function(e) {e.stopImmediatePropagation();});

     // this will create the toolbar for the textarea
     if (editor == null) {
       editor = CKEDITOR.replace('eventNotes',{
        customConfig: window.CRM.root+'/skin/js/ckeditor/calendar_event_editor_config.js',
        language : window.CRM.lang,
        width : '100%'
       });
   
       add_ckeditor_buttons(editor);
     }
     
     
     $(".ATTENDENCES").hide();
   
     $('#EventCalendar option:first-child').attr("selected", "selected");

     modal.modal("show");

     initMap();
  }


  $(document).on('click','.add-event',function() {
    var fmt = 'YYYY-MM-DD HH:mm:ss';
  
    var dateStart = moment().format(fmt);
    var dateEnd = moment().format(fmt);
          
    addEvent(dateStart,dateEnd);
  });

  $(document).on('click','.delete-event',function() {
    var typeID = $(this).data("typeid");
    
    bootbox.confirm({
       title:  i18next.t("Delete Event Type") + "?",
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
          if (result == true)// only event can be drag and drop, not anniversary or birthday
          {
            window.CRM.APIRequest({
               method: 'POST',
               path: 'events/deleteeventtype',
               data: JSON.stringify({"typeID":typeID})
            }).done(function(data) {
               location.reload();
            });
          }
        }        
    });
  });  

});