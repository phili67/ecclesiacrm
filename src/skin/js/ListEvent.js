//
//  This code is under copyright not under MIT Licence
//  copyright   : 2018 Philippe Logel all right reserved not MIT licence
//                This code can't be incoprorated in another software without any authorizaion
//
//  Updated : 2018/05/30
//

var editor = null;

function addEvent(dateStart,dateEnd)
{
   if (editor != null) {
      editor.destroy(false);
      editor = null;              
   }
       
   modal = createEventEditorWindow (dateStart,dateEnd,'createEvent',0,'','ListEvent.php');
       
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

   modal.modal("show");
   
   initMap();
}


$('#add-event').click('focus', function (e) {
  var fmt = 'YYYY-MM-DD HH:mm:ss';
  
  var dateStart = moment().format(fmt);
  var dateEnd = moment().format(fmt);
          
  addEvent(dateStart,dateEnd);
});

//Added by @saulowulhynek to translation of datatable nav terms
  $(document).ready(function () {
    $.fn.dataTable.moment = function ( format, locale ) {
      var types = $.fn.dataTable.ext.type;

      // Add type detection
      types.detect.unshift( function ( d ) {
          // Removed true as the last parameter of the following moment
          return moment( d, format, locale ).isValid() ?
              'moment-'+format :
          null;
      } );

      // Add sorting method - use an integer for the sorting
      types.order[ 'moment-'+format+'-pre' ] = function ( d ) {
         console.log("d");
          return moment ( d, format, locale, true ).unix();
      };
    };
    

    $.fn.dataTable.moment(window.CRM.datePickerformat.toUpperCase(),window.CRM.shortLocale);

    
    $(".eventsTable").DataTable({
       "language": {
         "url": window.CRM.plugin.dataTable.language.url
       },
       responsive: true
    });
    
    $('.listEvents').DataTable({"language": {
      "url": window.CRM.plugin.dataTable.language.url
    }});
    
    $('.DeleteEvent').submit(function(e) {
        var currentForm = this;
        e.preventDefault();
        bootbox.confirm({
        title:  i18next.t("Deleting an event will also delete all attendance counts for that event."),
        message:i18next.t("Are you sure you want to DELETE the event ?"),
        buttons: {
          confirm: {
              label: i18next.t('Yes'),
              className: 'btn-danger'
          },
          cancel: {
              label: i18next.t('No'),
              className: 'btn-success'
          }
        },
        callback: function(result) {
            if (result) {
                currentForm.submit();
            }
        }});
    });
    
    $(".EditEvent").click('focus', function (e) {
       var eventID    = $(this).data("id");
       
       window.CRM.APIRequest({
          method: 'POST',
          path: 'events/info',
          data: JSON.stringify({"eventID":eventID})
      }).done(function(calEvent) {
         if (editor != null) {
           editor.destroy(false);
           editor = null;              
         }
         
         modal = createEventEditorWindow (calEvent.start,calEvent.end,'modifyEvent',eventID,'','ListEvent.php');
       
         $('form #EventTitle').val(calEvent.Title);
         $('form #EventDesc').val(calEvent.Desc);
         $('form #eventNotes').val(calEvent.Text);
         $('form #EventLocation').val(calEvent.location);

         // we add the calendars and the types
         addCalendars(calEvent.calendarID);
         addCalendarEventTypes(calEvent.eventTypeID,false);
         addAttendees(calEvent.eventTypeID,true,calEvent.eventID);
         setActiveState(calEvent.inActive);

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

         modal.modal("show");
         
         initMap(calEvent.longitude,calEvent.latitude,calEvent.title+'('+calEvent.Desc+')',calEvent.location,calEvent.title+'('+calEvent.Desc+')',calEvent.Text);                   
      });
    });
    
  });
