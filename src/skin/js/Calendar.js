  //
  // Copyright 2018 Philippe Logel
  //
  var anniversary = true;
  var birthday    = true;
  var withlimit   = false;
  var eventCreated= false;
  
 
  var birthD = localStorage.getItem("birthday");
  if (birthD != null)
  {
    if (birthD == 'checked'){ 
      birthday=true;
    } else {
      birthday=false;
    }
      
    $('#isBirthdateActive').prop('checked', birthday);
  }

  var ann = localStorage.getItem("anniversary");
  if (ann != null)
  {
    if (ann == 'checked'){
      anniversary=true;
    } else {
      anniversary=false;
    }
    
    $('#isAnniversaryActive').prop('checked', anniversary);
  }
  
  var wLimit = localStorage.getItem("withlimit");
  if (wLimit != null)
  {
    if (wLimit == 'checked'){
      withlimit=true;
    } else {
      withlimit=false;
    }
    
    $('#isWithLimit').prop('checked', withlimit);
  }  
  
  
  $("#isBirthdateActive").on('change',function () {
     var _val = $(this).is(':checked') ? 'checked' : 'unchecked';
     
     if (_val == 'checked'){
       birthday = true;
     } else { 
      birthday = false;
     }
     $('#calendar').fullCalendar( 'refetchEvents' );
     
     localStorage.setItem("birthday",_val);     
  });
  
  $("#isAnniversaryActive").on('change',function () {
     var _val = $(this).is(':checked') ? 'checked' : 'unchecked';
     if (_val == 'checked'){
      anniversary = true;
     } else { 
      anniversary = false;
     }

     $('#calendar').fullCalendar( 'refetchEvents' );
     
     localStorage.setItem("anniversary",_val); 
  });
  
  $("#isWithLimit").on('change',function () {
     var _val = $(this).is(':checked') ? 'checked' : 'unchecked';
     if (_val == 'checked'){
        withlimit = true;
     } else { 
        withlimit = false;
     }
   
     var options = $('#calendar').fullCalendar('getView').options;
     options.eventLimit = withlimit;
     $('#calendar').fullCalendar('destroy');
     $('#calendar').fullCalendar(options);
     
     localStorage.setItem("withlimit",_val); 
  });
  
  window.groupFilterID     = 0;
  window.EventTypeFilterID = 0;
  
  localStorage.setItem("groupFilterID",groupFilterID);
  localStorage.setItem("EventTypeFilterID",EventTypeFilterID);  
  
  $("#EventGroupFilter").on('change',function () {
     var e = document.getElementById("EventGroupFilter");
     window.groupFilterID = e.options[e.selectedIndex].value;
   
    $('#calendar').fullCalendar( 'refetchEvents' );
    
    if (window.groupFilterID == 0)
      $("#ATTENDENCES").parents("tr").hide();
     
     localStorage.setItem("groupFilterID",groupFilterID); 
  });
  
  
  $("#EventTypeFilter").on('change',function () {
     var e = document.getElementById("EventTypeFilter");
     window.EventTypeFilterID = e.options[e.selectedIndex].value;
      
     $('#calendar').fullCalendar( 'refetchEvents' );
     
     localStorage.setItem("EventTypeFilterID",EventTypeFilterID); 
  });
  
  $('body').on('click','.date-range', function(){ 
      $( ".date-title").hide();
      $('.date-start').fadeIn();
      $('.date-end').fadeIn();
      $( ".ATTENDENCES" ).hide();
      $( ".eventPredication").hide();
  });
  
  $('body').on('click','.eventPredicationGlobal', function(){ 
      $( ".date-title").fadeIn();
      $('.date-start').hide();
      $('.date-end').hide();
      $( ".ATTENDENCES" ).hide();
      $( ".eventPredication").fadeIn();
  });
  
  $('body').on('click','#EventTitle', function(){ 
      $( ".date-title").fadeIn();
      $('.date-start').hide();
      $('.date-end').hide();
      $( ".ATTENDENCES" ).hide();
      $( ".eventPredication").hide();
  });
  
  $('body').on('click','#EventDesc', function(){ 
      $( ".date-title").fadeIn();
      $('.date-start').hide();
      $('.date-end').hide();
      $( ".ATTENDENCES" ).hide();
      $( ".eventPredication").hide();
  });
  
  // I have to do this because EventGroup isn't yet present when you load the page the first time
  $(document).on('change','#EventGroup',function () {
    $( ".date-title").fadeIn();
    $('.date-start').hide();
    $('.date-end').hide();
    $( ".eventPredication").hide();

     var e = document.getElementById("EventGroup");
     var _val = e.options[e.selectedIndex].value;
   
    if (_val == 0)
      $( ".ATTENDENCES" ).hide();
    else
      $( ".ATTENDENCES" ).fadeIn( "slow");
     
     localStorage.setItem("groupFilterID",groupFilterID); 
  });
  
  function addEventTypes()
  {
    window.CRM.APIRequest({
          method: 'GET',
          path: 'events/calendars',
    }).done(function(eventTypes) {    
      var elt = document.getElementById("eventType");          
      var len = eventTypes.length;
      
      for (i=0; i<len; ++i) {
        var option = document.createElement("option");
        option.text = eventTypes[i].name;
        option.value = eventTypes[i].eventTypeID;
        elt.appendChild(option);
      }       
      
    });  
  }
  
  function addCalendars()
  {
    window.CRM.APIRequest({
          method: 'GET',
          path: 'groups/calendars',
    }).done(function(groups) {    
      var elt = document.getElementById("EventGroup");          
      var len = groups.length;

      // We add the none option
      var option = document.createElement("option");
      option.text = i18next.t("None");
      option.value = 0;
      option.title = ""; 
      elt.appendChild(option);
      
      for (i=0; i<len; ++i) {
        var option = document.createElement("option");
        // there is a groups.type in function of the new plan of schema
        option.text = groups[i].name;
        option.title = groups[i].type;        
        option.value = groups[i].groupID;
        elt.appendChild(option);
      }       
      
    });  
  }
  
  function BootboxContent(start,end){  
    var time_format;
    var fmt = window.CRM.datePickerformat.toUpperCase();
    
    if (window.CRM.timeEnglish == 'true') {
      time_format = 'h:mm A';
    } else {
      time_format = 'H:mm';
    }
    
    var dateStart = moment(start).format(fmt);
    var timeStart = moment(start).format(time_format);
    var dateEnd = moment(end).format(fmt);
    var timeEnd = moment(end).format(time_format);
    
    var frm_str = '<h3>'+i18next.t("Event Creation")+'</h3><form id="some-form">'
       + '<div>'
            +'<div class="row">'
              +'<div class="col-md-3"><span style="color: red">*</span>' + i18next.t('Select your event type') + "</div>"
              +'<div class="col-md-9">'
              +'<select type="text" id="eventType" value="39"  width="100%" style="width: 100%" class="form-control input-sm">'
                   //+"<option value='0' >" + i18next.t("Personal") + "</option>"
                +'</select>'
              +'</div>'
            +'</div>'
            +'<div class="row">'
              +'<div class="col-md-3"><span style="color: red">*</span>' + i18next.t('Event Title') + ":</div>"
              +'<div class="col-md-9">'
                +"<input type='text' id='EventTitle' placeholder='" + i18next.t("Calendar Title") + "' size='30' maxlength='100' class='form-control input-sm'  width='100%' style='width: 100%' required>"
              +'</div>'
            +'</div>'
            +'<hr/>'
            +'<div class="row">'
              +'<div class="col-md-3"><span style="color: red">*</span>' + i18next.t('Event Desc') + ":</div>"
              +'<div class="col-md-9">'
                +"<textarea id='EventDesc' rows='3' maxlength='100' class='form-control input-sm'  width='100%' style='width: 100%' required placeholder='" + i18next.t("Calendar description") + "'></textarea>"
              +'</div>'
            +'</div>'          
            +'<hr/>'
            +'<div class="row">'
              +'<div class="col-md-3"><span style="color: red">*</span>' + i18next.t('Event Group') + ":</div>"
              +'<div class="col-md-9">'
                +'<select type="text" id="EventGroup" value="39" width="100%" style="width: 100%" class="form-control input-sm">'
                +'</select>'
              +'</div>'
            +'</div>'
            +'<div class="row ATTENDENCES">'
              +'<div class="col-md-3">' + i18next.t('Attendance Counts') + "</div>"
                +'<div class="col-md-9">'
                +'<table>'
                  +'<tr>'
                      +"<td><label>" + i18next.t("Total") + ":&nbsp;</label></td>"
                    +'<td>'
                    +'<input type="text" id="Total" value="0" size="8" class="form-control input-sm"  width="100%" style="width: 100%">'
                    +'</td>'
                    +'</tr>'
                  +'<tr>'
                      +"<td><label>" + i18next.t("Members") + ":&nbsp;</label></td>"
                    +'<td>'
                    +'<input type="text" id="Members" value="0" size="8" class="form-control input-sm"  width="100%" style="width: 100%">'
                   +' </td>'
                    +'</tr>'
                 +' <tr>'
                      +"<td><label>" + i18next.t("Visitors") + ":&nbsp;</label></td>"
                    +'<td>'
                    +'<input type="text" id="Visitors" value="0" size="8" class="form-control input-sm"  width="100%" style="width: 100%">'
                    +'</td>'
                    +'</tr>'
                        +'<tr>'
                  +"<td><label>" + i18next.t('Attendance Notes: ') + " &nbsp;</label></td>"
                    +'<td><input type="text" id="EventCountNotes" value="" class="form-control input-sm">'
                    +'</td>'
                    +'</tr>'
                +'</table>'
                +'</div>'
                +'<hr/>'
              +'</div>'
            +'</div>'
            +'<hr/>'
            +'<div class="row date-title">'
               +'<div class="col-md-4 date-range">'
               + i18next.t('From')+' : '+dateStart+' '+timeStart
               +'</div>'
               +'<div class="col-md-3 date-range">'
               + i18next.t('to')+' : '+dateEnd+' '+timeEnd
               +'</div>'
            +'</div>'
            +'<div class="row date-start">'
                +'<div class="col-md-12">'
                  +'<div class="row">'
                    +'<div class="col-md-3"><span style="color: red">*</span>'
                      + i18next.t('Start Date')+' :'
                    +'</div>'
                     +'<div class="col-md-4">'  
                       +'<div class="input-group">'
                          +'<div class="input-group-addon">'
                              +'<i class="fa fa-calendar"></i>'
                          +'</div>'
                          +'<input class="form-control date-picker input-sm" type="text" id="dateEventStart" name="dateEventStart"  value="'+dateStart+'" '
                                +'maxlength="10" id="sel1" size="11"'
                                +'placeholder="'+window.CRM.datePickerformat+'">'
                        +'</div>'
                    +'</div>'
                    +'<div class="col-md-4">'
                         +'<div class="bootstrap-timepicker">'
                           +'<div class="form-group">'
                              +'<div class="input-group">'
                                 +'<div class="input-group-addon">'
                                    +'<i class="fa fa-clock-o"></i>'
                                 +'</div>'
                                 +'<input type="text" class="form-control timepicker input-sm" id="timeEventStart" name="timeEventStart" value="'+timeStart+'">'
                              +'</div>'
                            +'</div> '           
                         +'</div>'
                     +'</div>'
                  +'</div>'
                +'</div>'
            +'</div>'
            +'<div class="row date-end">'            
                +'<div class="col-md-12">'
                  +'<div class="row">'
                    +'<div class="col-md-3"><span style="color: red">*</span>'
                      +i18next.t('End Date')+' :'
                    +'</div>'
                    +'<div class="col-md-4"> '   
                       +'<div class="input-group">'
                          +'<div class="input-group-addon">'
                              +'<i class="fa fa-calendar"></i>'
                          +'</div>'
                          +'<input class="form-control date-picker  input-sm" type="text" id="dateEventEnd" name="dateEventEnd"  value="'+dateEnd+'" '
                                +'maxlength="10" id="sel1" size="11"'
                                +'placeholder="'+window.CRM.datePickerformat+'">'
                        +'</div>'
                    +'</div>'
                    +'<div class="col-md-4">'
                         +'<div class="bootstrap-timepicker">'
                           +'<div class="form-group">'
                              +'<div class="input-group">'
                                 +'<div class="input-group-addon">'
                                    +'<i class="fa fa-clock-o"></i>'
                                 +'</div>'
                                 +'<input type="text" class="form-control timepicker input-sm" id="timeEventEnd" name="timeEventEnd" value="'+timeEnd+'">'
                              +'</div>'
                            +'</div>'
                         +'</div>'
                     +'</div>'
                  +'</div>'
                +'</div>'
            +'</div>'
            +'<hr/>'
            +'<div class="row eventPredicationGlobal">'
              +'<div class="col-md-12">'+i18next.t('Event Sermon')
                +'<div class="eventPredication" style="margin-top:-60px;">'
                  +'<textarea name="EventText" rows="4" cols="80" class="form-control input-sm eventPredication" id="eventPredication"  width="100%" style="width: 100%"></textarea></div>'
                +'</div>'
            +'</div>'
            //+'<tr>'
              //+'<td class="LabelColumn"><span style="color: red">*</span>Statut de l&#39;événement:</td>'
              //+'<td colspan="3" class="TextColumn">'
                //+'<input type="radio" name="EventStatus" value="0" checked/> Actif      <input type="radio" name="EventStatus" value="1" /> Inactif    </td>'
            //+'</tr>'
          +'</div>'
       + '</form>';

        var object = $('<div/>').html(frm_str).contents();

        return object
    }

  
    $(document).ready(function () {
        //
        // initialize the calendar
        // -----------------------------------------------------------------
        $('#calendar').fullCalendar({
          header: {
              left: 'prev,next today',
              center: 'title',
              right: 'month,agendaWeek,agendaDay,listMonth'
          },
          height: 500,
          selectable: isModifiable,
          editable:isModifiable,
          eventDrop: function(event, delta, revertFunc) {
            if (event.type == 'event'){
              bootbox.confirm({
               title:  i18next.t("Move Event") + "?",
                message: i18next.t("Are you sure about this change?") + "<br><br>   <b>\""  + event.title + "\"</b> " + i18next.t("will be dropped."),
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
                   data: JSON.stringify({"evntAction":'moveEvent',"eventID":event.eventID,"start":event.start.format()})
                  }).done(function(data) {
                     // now we can create the event
                     $('#calendar').fullCalendar('removeEvents',event._id);// delete old one
                     $('#calendar').fullCalendar('renderEvent', data, true); // add the new one
                     $('#calendar').fullCalendar('unselect'); 
                  });
                 } else {
                  revertFunc();
                 }
                 console.log('This was logged in the callback: ' + result);
                }        
            });
           } else {
            revertFunc();
           }
        },
        eventResize: function(event, delta, revertFunc) {
          if (event.type == 'event'){
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
                 data: JSON.stringify({"evntAction":'resizeEvent',"eventID":event.eventID,"end":event.end.format()})
                }).done(function(data) {
                   // now we can create the event
                   $('#calendar').fullCalendar('removeEvents',event._id);// delete old one
                   $('#calendar').fullCalendar('renderEvent', data, true); // add the new one
                   $('#calendar').fullCalendar('unselect'); 
                });
               } else {
                revertFunc();
               }
               console.log('This was logged in the callback: ' + result);
              }        
          });
         } else {
          revertFunc();
         }
      },
      selectHelper: true,        
      select: function(start, end) {
       var modal = bootbox.dialog({
         message: BootboxContent(start,end),
         buttons: [
          {
           label: i18next.t("Save"),
           className: "btn btn-primary pull-left",
           callback: function() {
              var EventTitle =  $('form #EventTitle').val();
              
              if (EventTitle) {
                  var e = document.getElementById("eventType");
                  var eventTypeID = e.options[e.selectedIndex].value;
                                                       
                  var EventDesc =  $('form #EventDesc').val();
                  
                  var dateStart = $('form #dateEventStart').val();
                  var timeStart = $('form #timeEventStart').val();
                  var dateEnd = $('form #dateEventEnd').val();
                  var timeEnd = $('form #timeEventEnd').val();
                  
                  var fmt = window.CRM.datePickerformat.toUpperCase();
    
                  if (window.CRM.timeEnglish == 'true') {
                    time_format = 'h:mm A';
                  } else {
                    time_format = 'H:mm';
                  }
                  
                  fmt = fmt+' '+time_format;
                                    
                  var real_start = moment(dateStart+' '+timeStart,fmt).format('YYYY-MM-DD H:mm');
                  var real_end = moment(dateEnd+' '+timeEnd,fmt).format('YYYY-MM-DD H:mm');
                             
                  var e = document.getElementById("EventGroup");
                  var EventGroupID = e.options[e.selectedIndex].value;
                  var EventGroupType = e.options[e.selectedIndex].title;// we get the type of the group : personal or group for future dev
                             
                  var Total =  $('form #Total').val();
                  var Members = $('form #Members').val();
                  var Visitors = $('form #Visitors').val();
                  var EventCountNotes = $('form #EventCountNotes').val();
                             
                  var eventPredication = CKEDITOR.instances['eventPredication'].getData();//$('form #eventPredication').val();
              
                  var add = false;
                                                            
                  window.CRM.APIRequest({
                        method: 'POST',
                        path: 'events/',
                        data: JSON.stringify({"evntAction":'createEvent',"eventTypeID":eventTypeID,"EventGroupType":EventGroupType,"EventTitle":EventTitle,"EventDesc":EventDesc,"EventGroupID":EventGroupID,"Total":Total,"Members":Members,"Visitors":Visitors,"EventCountNotes":EventCountNotes,"eventPredication":eventPredication,"start":real_start,"end":real_end})
                  }).done(function(data) {                   
                    $('#calendar').fullCalendar('renderEvent', data, true); // stick? = true             
                    $('#calendar').fullCalendar('unselect');              
                    add = true;              
                    modal.modal("hide");   
                    
                    eventCreated = true;                    
                    
                    return true;
                  });

                  return add;  
              } else {
                  window.CRM.DisplayAlert("Error","You have to set a Title for your event");
                
                  return false;
              }    
            }
          },
          {
           label: i18next.t("Close"),
           className: "btn btn-default pull-left",
           callback: function() {
              console.log("just do something on close");
           }
          }
         ],
         show: false/*,
         onEscape: function() {
            modal.modal("hide");
         }*/
       });
  
       modal.modal("show");
       
       // we add the calendars
       addCalendars();
       addEventTypes();      
       
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
       $( ".eventPredication").hide();
       
       // this will ensure that image and table can be focused
       $(document).on('focusin', function(e) {e.stopImmediatePropagation();});
       
       // this will create the toolbar for the textarea
       CKEDITOR.replace('eventPredication',{
        customConfig: window.CRM.root+'/skin/js/ckeditor/calendar_event_editor_config.js',
        language : window.CRM.lang,
        width : '100%'
       });
      
       $(".ATTENDENCES").hide();
      },
      eventLimit: withlimit, // allow "more" link when too many events
      locale: window.CRM.lang,
      events: window.CRM.root + '/api/calendar/events',
      eventRender: function (event, element, view) {
        groupFilterID = window.groupFilterID;
        EventTypeFilterID = window.EventTypeFilterID;
        
        if (event.hasOwnProperty('type')){
          if (event.type == 'event' 
            && (groupFilterID == 0 || (groupFilterID>0 && groupFilterID == event.groupID)) 
            && (EventTypeFilterID == 0 || (EventTypeFilterID>0 && EventTypeFilterID == event.eventTypeID))){
            return true;
          } else if(event.type == 'event' 
            && ((groupFilterID>0 && groupFilterID != event.groupID)
                || (EventTypeFilterID>0 && EventTypeFilterID != event.eventTypeID))){
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
      }
    });
    
    $(document).on('hidden.bs.modal','.bootbox.modal', function (e) {
      if (eventCreated) {                    
          var box = window.CRM.DisplayAlert("Event added","Event was added successfully.");

          setTimeout(function() {
            // be careful not to call box.hide() here, which will invoke jQuery's hide method
            box.modal('hide');
          }, 3000);
          
          eventCreated = false;           
      }
    });
  });