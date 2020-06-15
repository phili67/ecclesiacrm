//
//  This code is under copyright not under MIT Licence
//  copyright   : 2018 Philippe Logel all right reserved not MIT licence
//                This code can't be incoprorated in another software without authorizaion
//
//  Updated : 2019/04/19
//

window.CRM.editor = null;

$(document).ready(function () {

  function addEvent(dateStart,dateEnd)
  {
     if (window.CRM.editor != null) {
       CKEDITOR.remove(window.CRM.editor);
       window.CRM.editor = null;
     }

     modal = createEventEditorWindow (dateStart,dateEnd,'createEvent',0,'','Checkin.php');

     // we add the calendars and the types
     addCalendars();
     addCalendarEventTypes(-1,true);

     //Timepicker
     $('.timepicker').datetimepicker({
         format: 'LT',
         locale: window.CRM.lang,
         icons:
             {
                 up: 'fa fa-angle-up',
                 down: 'fa fa-angle-down'
             }
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
     if (window.CRM.editor == null) {
       if (window.CRM.bEDrive) {
         window.CRM.editor = CKEDITOR.replace('eventNotes',{
          customConfig: window.CRM.root+'/skin/js/ckeditor/configs/calendar_event_editor_config.js',
          language : window.CRM.lang,
          width : '100%',
          extraPlugins : 'uploadfile,uploadimage,filebrowser',
          uploadUrl: window.CRM.root+'/uploader/upload.php?type=publicDocuments',
          imageUploadUrl: window.CRM.root+'/uploader/upload.php?type=publicImages',
          filebrowserUploadUrl: window.CRM.root+'/uploader/upload.php?type=publicDocuments',
          filebrowserBrowseUrl: window.CRM.root+'/browser/browse.php?type=publicDocuments'
         });
       } else {
         window.CRM.editor = CKEDITOR.replace('eventNotes',{
          customConfig: window.CRM.root+'/skin/js/ckeditor/configs/calendar_event_editor_config.js',
          language : window.CRM.lang,
          width : '100%'
         });
       }

       add_ckeditor_buttons(window.CRM.editor);
     }


     $(".ATTENDENCES").hide();

     $('#EventCalendar option:first-child').attr("selected", "selected");

     modal.modal("show");

     initMap();
  }


  $('#add-event').click('focus', function (e) {
    var fmt = 'YYYY-MM-DD HH:mm:ss';

    var dateStart = moment().format(fmt);
    var dateEnd = moment().format(fmt);

    addEvent(dateStart,dateEnd);
  });


    $(document).on("click",".PersonCheckinChangeState", function(){
      var checked  = $(this).is(':checked');
      var personID = $(this).data("personid");
      var eventID  = $(this).data("eventid");

      window.CRM.APIRequest({
        method: 'POST',
        path: 'attendees/checkinstudent',
        data: JSON.stringify({"checked":checked,"personID":personID,"eventID":eventID})
      }).done(function(data) {
        if (data.status) {
          $('#checkoutPersonID'+personID).text(data.name);

          var message;

          if (checked) {
             $('#checkinDatePersonID'+personID).text(data.date);
             message = "Attendees validated successfully.";
           } else {
             $('#checkinDatePersonID'+personID).text("");
             $('#checkoutDatePersonID'+personID).text("");
             $("#PersonCheckoutChangeState-"+personID). prop("checked", false);
             message = "Attendees unvalidated successfully.";
           }

           /*var box = window.CRM.DisplayAlert(i18next.t("Attendance"),message);

           setTimeout(function() {
            // be careful not to call box.hide() here, which will invoke jQuery's hide method
            box.modal('hide');
           }, 1000);*/
         }
      });
    });

    $(document).on("click",".PersonCheckoutChangeState", function(){
        var checked  = $(this).is(':checked');
        var personID = $(this).data("personid");
        var eventID  = $(this).data("eventid");

        window.CRM.APIRequest({
            method: 'POST',
            path: 'attendees/checkoutstudent',
            data: JSON.stringify({"checked":checked,"personID":personID,"eventID":eventID})
        }).done(function(data) {
            if (data.status) {
                $('#checkoutPersonID'+personID).text(data.name);

                var message;

                if (checked) {
                    $('#checkoutDatePersonID'+personID).text(data.date);
                    message = "Attendees validated successfully.";
                } else {
                    $('#checkoutDatePersonID'+personID).text("");
                    message = "Attendees unvalidated successfully.";
                }

                /*var box = window.CRM.DisplayAlert(i18next.t("Attendance"),message);

                setTimeout(function() {
                 // be careful not to call box.hide() here, which will invoke jQuery's hide method
                 box.modal('hide');
                }, 1000);*/
            }
        });
    });

    $(document).on("click","#uncheckAll", function(){
      var eventID  = $(this).data("id");

       window.CRM.APIRequest({
        method: 'POST',
        path: 'attendees/uncheckAll',
        data: JSON.stringify({"eventID":eventID, "type": 1})
      }).done(function(data) {
        location.reload();
      });
    });

    $(document).on("click","#checkAll", function(){
      var eventID  = $(this).data("id");

       window.CRM.APIRequest({
        method: 'POST',
        path: 'attendees/checkAll',
        data: JSON.stringify({"eventID":eventID, "type": 1})
      }).done(function(data) {
        location.reload();
      });
    });

});
