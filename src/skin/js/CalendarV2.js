//
//  This code is under copyright not under MIT Licence
//  copyright   : 2018 Philippe Logel all right reserved not MIT licence
//                This code can't be incoprorated in another software without any authorizaion
//
//  Updated : 2018/05/30
//

var editor = null;
  
$(document).ready(function () {
  //
  // initialize calendar
  // -----------------------------------------------------------------
  $('#calendar').fullCalendar({
    customButtons: {
      actualizeButton: {
        text: i18next.t('Actualize'),
        click: function() {
          $('#calendar').fullCalendar( 'refetchEvents' );
        }
      }
    },
    header: {
        left: 'prev,next today',
        center: 'title',
        right: 'month,agendaWeek,agendaDay,listMonth,actualizeButton',//listYear
    },
    height: parent,
    selectable: window.CRM.isModifiable,
    editable:window.CRM.isModifiable,
    defaultView: wAgendaName,
    viewRender: function(view, element){
      localStorage.setItem("wAgendaName",view.name);
    },
    eventDrop: function(event, delta, revertFunc) {
      if (event.type == 'birthday' || event.type == 'anniversary') {
        window.CRM.DisplayAlert(i18next.t("Error"),i18next.t("This event isn't modifiable !!!"));
        $('#calendar').fullCalendar( 'refetchEvents' );
        return false;
      }
      
      if (event.writeable == false) {
        window.CRM.DisplayAlert(i18next.t("Error"),i18next.t("This event isn't modifiable !!!"));
        $('#calendar').fullCalendar( 'refetchEvents' );
        return;
      }
      
      var fmt = 'YYYY-MM-DD HH:mm:ss';

      var dateStart = moment(event.start).format(fmt);
      var dateEnd = moment(event.end).format(fmt);
      
      if (event.end == null) {
         dateEnd = dateStart;
      }
      

      if (event.type == 'event' && event.recurrent == 0) {
        bootbox.confirm({
         title:  i18next.t("Move Event") + "?",
          message: i18next.t("Are you sure about this change?") + ((event.recurrent != 0)?" and the Linked Events ?":"") + "<br><br>   <b>\""  + event.title + "\"</b> " + i18next.t("will be dropped."),
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
                 path: 'events/',
                 data: JSON.stringify({"evntAction":'moveEvent',"calendarID":event.calendarID,"eventID":event.eventID,"start":dateStart,"end":dateEnd})
              }).done(function(data) {
                // now we can refresh the calendar
                $('#calendar').fullCalendar('refetchEvents');
                $('#calendar').fullCalendar('unselect'); 
              });
            } else {
              revertFunc();
            }
            
            console.log('This was logged in the callback: ' + result);
          }        
      });
     } else {
      var reccurenceID = moment(event.reccurenceID).format(fmt);
      var origStart   = moment(event.origStart).format(fmt);
      
      if (origStart == reccurenceID) {
         var box = bootbox.dialog({
           title: i18next.t("Move Event") + "?",
           message: i18next.t("You're about to move all the events. Would you like to :"),
           buttons: {
            cancel: {
              label:  i18next.t("Cancel"),
              className: 'btn btn-default',
                callback: function () {
                  revertFunc();
                }
              },
            oneEvent: {
              label:  i18next.t("Only this Event"),
              className: 'btn btn-info',
                callback: function () {

                  window.CRM.APIRequest({
                     method: 'POST',
                     path: 'events/',
                     data: JSON.stringify({"evntAction":'moveEvent',"calendarID":event.calendarID,"eventID":event.eventID,"start":dateStart,"end":dateEnd,"allEvents":false,"reccurenceID":reccurenceID})
                  }).done(function(data) {
                    // now we can refresh the calendar
                    $('#calendar').fullCalendar('refetchEvents');
                    $('#calendar').fullCalendar('unselect'); 
                  }); 
                }
              },
            allEvents: {
              label:  i18next.t("All Events"),
              className: 'btn btn-primary',
                callback: function () {

                  window.CRM.APIRequest({
                     method: 'POST',
                     path: 'events/',
                     data: JSON.stringify({"evntAction":'moveEvent',"calendarID":event.calendarID,"eventID":event.eventID,"start":dateStart,"end":dateEnd,"allEvents":true,"reccurenceID":reccurenceID})
                  }).done(function(data) {
                    // now we can refresh the calendar
                    $('#calendar').fullCalendar('refetchEvents');
                    $('#calendar').fullCalendar('unselect'); 
                  }); 
                }                    
              }
            }
        });
      } else {// this a recurence event yet modified
        bootbox.confirm({
         title:  i18next.t("Move Event") + "?",
          message: i18next.t("Are you sure about this change?") + ((event.recurrent != 0)?" and the Linked Events ?":"") + "<br><br>   <b>\""  + event.title + "\"</b> " + i18next.t("will be dropped."),
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
                 path: 'events/',
                 data: JSON.stringify({"evntAction":'moveEvent',"calendarID":event.calendarID,"eventID":event.eventID,"start":dateStart,"end":dateEnd,"allEvents":false,"reccurenceID":reccurenceID})
              }).done(function(data) {
                // now we can refresh the calendar
                $('#calendar').fullCalendar('refetchEvents');
                $('#calendar').fullCalendar('unselect'); 
              });
            } else {
              revertFunc();
            }
            
            console.log('This was logged in the callback: ' + result);
          }        
        });
      }
     }
  },
  eventClick: function(calEvent, jsEvent, view) {
    if (calEvent.writeable == false) {
        window.CRM.DisplayAlert(i18next.t("Error"),i18next.t("This event isn't modifiable !!!"));
        
        return;
    }
  
    var fmt = 'YYYY-MM-DD HH:mm:ss';

    var dateStart = moment(calEvent.start).format(fmt);
    var dateEnd = moment(calEvent.end).format(fmt);
    
    if (calEvent.type == "event" && window.CRM.isModifiable) {
       // only with group event We create the dialog,
       if (calEvent.type == "event") {
         var box = bootbox.dialog({
           title: i18next.t("Modify Event"),
           message: i18next.t("What would you like to do ? Be careful with the deletion, it's impossible to revert !!!"),
           buttons: {
              cancel: {
                label:  i18next.t("Delete Event"),
                className: 'btn btn-danger',
                 callback: function () {
                   if (calEvent.type == "event" && calEvent.recurrent == 0) {
                     bootbox.confirm(i18next.t("Are you sure to delete this event?"), function(confirmed) {
                      if (confirmed) {
                        window.CRM.APIRequest({
                           method: 'POST',
                           path: 'events/',
                           data: JSON.stringify({"calendarID":calEvent.calendarID,"evntAction":'suppress',"eventID":calEvent.eventID})
                        }).done(function(data) {
                           $('#calendar').fullCalendar( 'refetchEvents' );
                           $('#calendar').fullCalendar('unselect'); 
                        });
                       }
                      });
                   } else if (calEvent.type == "event" && calEvent.recurrent == 1) {
                     var reccurenceID = moment(calEvent.reccurenceID).format(fmt);
                     
                     var box = bootbox.dialog({
                       title: i18next.t("Delete all repeated Events"),
                       message: i18next.t("You are about to delete all the repeated Events linked to this event. Are you sure? This can't be undone."),
                       buttons: {
                          cancel: {
                            label:  i18next.t('No'),
                            className: 'btn btn-success'
                          },     
                          add: {
                             label: i18next.t('Only this event'),
                             className: 'btn btn-info',
                             callback: function () {
                               window.CRM.APIRequest({
                                 method: 'POST',
                                 path: 'events/',
                                 data: JSON.stringify({"calendarID":calEvent.calendarID,"evntAction":'suppress',"eventID":calEvent.eventID,"dateStart":dateStart,"reccurenceID":reccurenceID})
                              }).done(function(data) {
                                 $('#calendar').fullCalendar( 'refetchEvents' );
                                 $('#calendar').fullCalendar('unselect'); 
                              });
                             }
                          },
                          confirm: {
                             label: i18next.t('Every Events linked to this Event'),
                             className: 'btn btn-danger',
                             callback: function () {
                                window.CRM.APIRequest({
                                   method: 'POST',
                                   path: 'events/',
                                   data: JSON.stringify({"calendarID":calEvent.calendarID,"evntAction":'suppress',"eventID":calEvent.eventID})
                                }).done(function(data) {
                                   $('#calendar').fullCalendar( 'refetchEvents' );
                                   $('#calendar').fullCalendar('unselect'); 
                                });
                             }
                          }
                        }
                    });

                    box.show();
                  } else {
                    // the other event type
                  }
                }
              },     
              add: {
                 label: i18next.t('Add More Attendees'),
                 className: 'btn btn-info',
                 callback: function () {
                    window.CRM.APIRequest({
                     method: 'POST',
                     path: 'events/',
                     data: JSON.stringify({"evntAction":'attendeesCheckinEvent',"eventID":calEvent.eventID})
                    }).done(function(data) {
                       location.href = window.CRM.root + 'EditEventAttendees.php';
                    });
                 }
              },
              attendance: {
                 label: i18next.t('Make Attendance'),
                 className: 'btn btn-primary',
                 callback: function () {
                    window.CRM.APIRequest({
                     method: 'POST',
                     path: 'events/',
                     data: JSON.stringify({"evntAction":'attendeesCheckinEvent',"eventID":calEvent.eventID})
                    }).done(function(data) {
                       location.href = window.CRM.root + 'Checkin.php';
                    });
                  
                 }
              },
              Edit: {
                 label: i18next.t('Edit'),
                 className: 'btn btn-success',
                 callback: function () {
                   if (editor != null) {
                      editor.destroy(false);
                      editor = null;              
                   }
                   
                   modal = createEventEditorWindow (calEvent.start,calEvent.end,'modifyEvent',calEvent.eventID,calEvent.reccurenceID);
 
                   $('form #EventTitle').val(calEvent.title);
                   $('form #EventDesc').val(calEvent.Desc);
                   $('form #eventNotes').val(calEvent.Text);
                   $('form #EventLocation').val(calEvent.location);
     
                   // we add the calendars and the types
                   addCalendars(calEvent.calendarID);
                   addCalendarEventTypes(calEvent.eventTypeID,false);
                   addAttendees(calEvent.eventTypeID,true,calEvent.eventID);
     
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
 
                   initMap(calEvent.longitude,calEvent.latitude,calEvent.title+'('+calEvent.Desc+')',calEvent.location,calEvent.title+'('+calEvent.Desc+')',calEvent.Text);

                   modal.modal("show");                   
                }
              }
            }
        });

        box.show();
      } else {
        // we are with other event type
      }
        
      // change the border color just for fun
      $(this).css('border-color', 'red');

    }
  },
  eventResize: function(event, delta, revertFunc) {
    if (event.writeable == false || event.type == 'birthday' || event.type == 'anniversary') {
      window.CRM.DisplayAlert(i18next.t("Error"),i18next.t("This event isn't modifiable !!!"));
      $('#calendar').fullCalendar( 'refetchEvents' );
      return;
    }
      
    var fmt = 'YYYY-MM-DD HH:mm:ss';

    var dateStart = moment(event.start).format(fmt);
    var dateEnd = moment(event.end).format(fmt);
    var reccurenceID = moment(event.reccurenceID).format(fmt);

    if (event.type == "event" && event.recurrent == 0) {
      bootbox.confirm({
       title: i18next.t("Resize Event") + "?",
        message: i18next.t("Are you sure about this change?") + "\n"+event.title + " " + i18next.t("will be dropped."),
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
               path: 'events/',
               data: JSON.stringify({"evntAction":'resizeEvent',"calendarID":event.calendarID,"eventID":event.eventID,"start":dateStart,"end":dateEnd,"allEvents":false})
            }).done(function(data) {
               // now we can refresh the calendar
               $('#calendar').fullCalendar( 'refetchEvents' );
               $('#calendar').fullCalendar('unselect'); 
            });                  
         } else {
          revertFunc();
         }
         console.log('This was logged in the callback: ' + result);
        }        
      });
    } else {
      var box = bootbox.dialog({
         title: i18next.t("Resize Event") + "?",
         message: i18next.t("You're about to resize all the events. Would you like to :"),
         buttons: {
          cancel: {
            label:  i18next.t("Cancel"),
            className: 'btn btn-default',
              callback: function () {
                revertFunc();
              }
            },
          oneEvent: {
            label:  i18next.t("Only this Event"),
            className: 'btn btn-info',
              callback: function () {
                window.CRM.APIRequest({
                   method: 'POST',
                   path: 'events/',
                   data: JSON.stringify({"evntAction":'resizeEvent',"calendarID":event.calendarID,"eventID":event.eventID,"start":dateStart,"end":dateEnd,"allEvents":false,"reccurenceID":reccurenceID})
                }).done(function(data) {
                   // now we can refresh the calendar
                   $('#calendar').fullCalendar( 'refetchEvents' );
                   $('#calendar').fullCalendar('unselect'); 
                });  
              }
            },
          allEvents: {
            label:  i18next.t("All Events"),
            className: 'btn btn-primary',
              callback: function () {
                window.CRM.APIRequest({
                 method: 'POST',
                 path: 'events/',
                 data: JSON.stringify({"evntAction":'resizeEvent',"calendarID":event.calendarID,"eventID":event.eventID,"start":dateStart,"end":dateEnd,"allEvents":true,"reccurenceID":reccurenceID})
                }).done(function(data) {
                   // now we can refresh the calendar
                   $('#calendar').fullCalendar( 'refetchEvents' );
                   $('#calendar').fullCalendar('unselect'); 
                });
              }                    
            }
          }
      });
   }         
},
selectHelper: true,        
select: function(start, end) {
  window.CRM.APIRequest({
      method: 'POST',
      path: 'calendar/numberofcalendar',
  }).done(function(data) {         
    if (data.CalendarNumber > 0){
       // We create the dialog
       if (editor != null) {
          editor.destroy(false);
          editor = null;              
       }
      
       modal = createEventEditorWindow (start,end);
 
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
 
       initMap();

       modal.modal("show");
       
    } else {
       window.CRM.DisplayAlert(i18next.t("Error"),i18next.t("To add an event, You have to create a calendar or activate one first."));
    }
  });
},
eventLimit: withlimit, // allow "more" link when too many events
locale: window.CRM.lang,
eventRender: function (event, element, view) {
  calendarFilterID = window.calendarFilterID;
  EventTypeFilterID = window.EventTypeFilterID;
  
  element.find('.fc-title').html(event.icon+event.title);

  if (event.hasOwnProperty('type')){
    if (event.type == 'event'  
      && (EventTypeFilterID == 0 || (EventTypeFilterID>0 && EventTypeFilterID == event.eventTypeID) ) ) {
      return true;
    } else if(event.type == 'event' 
      && (EventTypeFilterID>0 && EventTypeFilterID != event.eventTypeID) ) {
      return false;
    } else if ((event.allDay || event.type != 'event')){// we are in a allDay event          
     if (event.type == 'anniversary' && anniversary == true || event.type == 'birthday' && birthday == true){
      var evStart = moment(view.intervalStart).subtract(1, 'days');
      var evEnd = moment(view.intervalEnd).subtract(1, 'days');
      if (!event.start.isAfter(evStart) || event.start.isAfter(evEnd)) {
        return false;
      }
     } else {
      return false;
     }
    }
   }
},
events: function(start, end, timezone, callback) {
  var real_start = moment.unix(start.unix()).format('YYYY-MM-DD HH:mm:ss');
  var real_end = moment.unix(end.unix()).format('YYYY-MM-DD HH:mm:ss');
  
  window.CRM.APIRequest({
    method: 'POST',
    path: 'calendar/getallevents',
    data: JSON.stringify({"start":real_start,"end":real_end})
  }).done(function(events) {
    callback(events);
  });
}
});
  
  $(document).on('hidden.bs.modal','.bootbox.modal', function (e) {
    if (eventCreated) {   
      if (eventAttendees) {
        var box = bootbox.dialog({
           title: i18next.t('Event added'),
           message: i18next.t("Event was added successfully. Would you like to make the Attendance or to add attendees ?"),
           buttons: {
              cancel: {
                label:  i18next.t('No'),
                className: 'btn btn-default'
              },     
              add: {
                 label: i18next.t('Add More Attendees'),
                 className: 'btn btn-info',
                 callback: function () {
                    location.href = window.CRM.root + 'EditEventAttendees.php';
                 }
              },
              confirm: {
                 label: i18next.t('Make Attendance'),
                 className: 'btn btn-success',
                 callback: function () {
                    location.href = window.CRM.root + 'Checkin.php';
                 }
              }
            }
        });

        box.show();
      } else {
        var box = window.CRM.DisplayAlert(i18next.t("Event added"),i18next.t("Event was added successfully."));

        setTimeout(function() {
          // be careful not to call box.hide() here, which will invoke jQuery's hide method
          box.modal('hide');
        }, 3000);
      }                
        
      eventAttendees = false;
      eventCreated = false;
    }
  });
});
