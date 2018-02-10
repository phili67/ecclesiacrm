  //
  // Copyright 2018 Philippe Logel
  // All rights reserved
  //
  var anniversary = true;
  var birthday    = true;
  var withlimit   = false;
  var eventCreated= false; 
  var eventAttendees = false; 
 
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
  
  $('body').on('click','.date-title .date-range', function(){ 
      $( ".date-title").slideUp();
      $('.ATTENDENCES-title').slideDown();
      $('.date-start').slideDown();
      $('.date-end').slideDown();
      $( ".ATTENDENCES" ).slideUp();
      $( ".eventPredication").slideUp();
  });
  
  $('body').on('click','.eventPredicationTitle', function(){ 
      $( ".date-title").slideDown();
      $('.ATTENDENCES-title').slideDown();
      $('.date-start').slideUp();
      $('.date-end').slideUp();
      $( ".ATTENDENCES" ).slideUp();
      $( ".eventPredication").slideDown();
  });
  
  $('body').on('click','#EventTitle', function(){ 
      $( ".date-title").slideDown();
      $('.ATTENDENCES-title').slideDown();
      $('.date-start').slideUp();
      $('.date-end').slideUp();
      $( ".ATTENDENCES" ).slideUp();
      $( ".eventPredication").slideUp();
  });
  
  $('body').on('click','#EventDesc', function(){ 
      $( ".date-title").slideDown();
      $('.ATTENDENCES-title').slideDown();
      $('.date-start').slideUp();
      $('.date-end').slideUp();
      $( ".ATTENDENCES" ).slideUp();
      $( ".eventPredication").slideUp();
  });
  
  $('body').on('click','.ATTENDENCES-title', function(){ 
    $( ".date-title").slideDown();
    $('.ATTENDENCES-title').slideUp();
    $('.date-start').slideUp();
    $('.date-end').slideUp();
    $( ".eventPredication").slideUp();
    $( ".ATTENDENCES" ).slideDown( "slow");
  });
  
  // I have to do this because EventGroup isn't yet present when you load the page the first time
  $(document).on('change','#EventGroup',function () {
    $( ".date-title").slideDown();
    $('.ATTENDENCES-title').slideDown();
    $( ".ATTENDENCES" ).slideUp();
    $('.date-start').slideUp();
    $('.date-end').slideUp();
    $( ".eventPredication").slideUp();

     var e = document.getElementById("EventGroup");
     var _val = e.options[e.selectedIndex].value;
   
    /*if (_val == 0)
      $( ".ATTENDENCES" ).slideUp();
    else
      $( ".ATTENDENCES" ).slideDown( "slow");*/
      
    $("#addGroupAttendees").prop("disabled", (_val == 0)?true:false);
    $("#addGroupAttendees").prop('checked', (_val == 0)?false:true);
     
    localStorage.setItem("groupFilterID",groupFilterID); 
  });
  
  
  $(document).on('change','#eventType',function (val) {
    var e = document.getElementById("eventType");
    var typeID = e.options[e.selectedIndex].value;
    
    addAttendees(typeID);
  });
  
  function addAttendees(typeID,first_time=true,eventID=0)
  {
    if (first_time) {
      $('.ATTENDENCES-title').slideDown();
    }
    
    $('.date-start').slideUp();
    $('.date-end').slideUp();
    $('.eventPredication').slideUp();
      
    window.CRM.APIRequest({
          method: 'POST',
          path: 'events/attendees',
          data: JSON.stringify({"typeID":typeID,"eventID":eventID})
    }).done(function(eventTypes) {      
      var len = eventTypes.length;
    
      if (len == 0) {
        $('.ATTENDENCES-title').slideDown();
        $(".ATTENDENCES-fields" ).empty();  
        $(".ATTENDENCES").slideUp();  
        $(".ATTENDENCES-fields" ).html('<input id="countFieldsId" name="countFieldsId" type="hidden" value="0"><br>'+i18next.t('No attendees')+'<br>');
        
      } else {
        $(".ATTENDENCES-fields" ).empty();
        //$('.ATTENDENCES-title').slideUp();

        var innerHtml = '<input id="countFieldsId" name="countFieldsId" type="hidden" value="'+len+'">';
        
        innerHtml += '<table>';
        
        var notes = "";

        for (i=0; i<len; ++i) {
          innerHtml +='<tr>'
                    +"<td><label>" + eventTypes[i].countName  + ":&nbsp;</label></td>"
                    +'<td>'
                    +'<input type="text" id="field'+i+'" data-name="'+eventTypes[i].countName+'" data-countid="'+eventTypes[i].countID+'" value="'+eventTypes[i].count+'" size="8" class="form-control input-sm"  width="100%" style="width: 100%">'
                    +'</td>'
                    +'</tr>'
          notes = eventTypes[i].notes;
        }  //typeID
        
        
        innerHtml +='<tr>'
          +"<td><label>" + i18next.t('Attendance Notes: ') + " &nbsp;</label></td>"
          +'<td><input type="text" id="EventCountNotes" value="'+notes+'" class="form-control input-sm">'
          +'</td>'
        +'</tr>';
        
        innerHtml += '</table><br>';
        
        $(".ATTENDENCES-fields" ).html(innerHtml);
        
          $(".ATTENDENCES").slideDown();  
      }
    }); 
  }
  
  function addGroupEventTypes(typeId=0,bAddAttendees=false)
  {
    window.CRM.APIRequest({
          method: 'GET',
          path: 'events/types',
    }).done(function(eventTypes) {    
      var elt = document.getElementById("eventType");          
      var len = eventTypes.length;
      var passed = false;      
      
      var global_typeID = 0;
      
      for (i=0; i<len; ++i) {      
        var option = document.createElement("option");
        option.text = eventTypes[i].name;
        option.value = eventTypes[i].eventTypeID;
        
        if (typeId && typeId === eventTypes[i].eventTypeID) {
          option.setAttribute('selected','selected');
        }
        
        elt.appendChild(option);
        
        if (!passed) {       
          global_typeID = eventTypes[i].eventTypeID;
          passed = true;
        }
      }     
      
      if (bAddAttendees) {
        addAttendees(global_typeID);
      }
    });  
  }  
  function addGroupCalendars(groupID)
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
        
        if (groupID && groupID === groups[i].groupID) {
          option.setAttribute('selected','selected');
        }
        
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
    
    var frm_str = '<h3 style="margin-top:-5px">'+i18next.t("Event Creation")+'</h3><form id="some-form">'
       + '<div>'
            +'<div class="row   div-title">'
              +'<div class="col-md-3"><span style="color: red">*</span>' + i18next.t('Select your event type') + "</div>"
              +'<div class="col-md-9">'
              +'<select type="text" id="eventType" value="39"  width="100%" style="width: 100%" class="form-control input-sm">'
                   //+"<option value='0' >" + i18next.t("Personal") + "</option>"
                +'</select>'
              +'</div>'
            +'</div>'
            +'<div class="row div-title">'
              +'<div class="col-md-3"><span style="color: red">*</span>' + i18next.t('Event Title') + ":</div>"
              +'<div class="col-md-9">'
                +"<input type='text' id='EventTitle' placeholder='" + i18next.t("Calendar Title") + "' size='30' maxlength='100' class='form-control input-sm'  width='100%' style='width: 100%' required>"
              +'</div>'
            +'</div>'
            +'<div class="row div-title">'
              +'<div class="col-md-3"><span style="color: red">*</span>' + i18next.t('Event Desc') + ":</div>"
              +'<div class="col-md-9">'
                +"<textarea id='EventDesc' rows='3' maxlength='100' class='form-control input-sm'  width='100%' style='width: 100%' required placeholder='" + i18next.t("Calendar description") + "'></textarea>"
              +'</div>'
            +'</div>'          
            +'<div class="row date-title div-title">'
               +'<div class="col-md-4 date-range">'
               + i18next.t('From')+' : '+dateStart+' '+timeStart
               +'</div>'
               +'<div class="col-md-3 date-range">'
               + i18next.t('to')+' : '+dateEnd+' '+timeEnd
               +'</div>'
            +'</div>'
            +'<div class="row date-start div-block">'
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
            +'<div class="row date-end div-block">'            
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
            +'<div class="row  div-title">'
              +'<div class="col-md-3"><span style="color: red">*</span>' + i18next.t('Event Group') + ":</div>"
              +'<div class="col-md-4">'
                +'<select type="text" id="EventGroup" value="39" width="100%" style="width: 100%" class="form-control input-sm">'
                +'</select>'
              +'</div>'
              +'<div class="col-md-5">'
                 +'<div class="checkbox">'
                   +'<label>'
                    +'<input type="checkbox" id="addGroupAttendees" disabled> '+ i18next.t('Add as attendees to the event')
                  +'</label>'
                +'</div>'
              +'</div>'
            +'</div>'
            +'<div class="row  ATTENDENCES-title div-title">'
              +'<div class="col-md-3"><span style="color: red">*</span>' + i18next.t('Attendances') + ":</div>"
            +'</div>'
            +'<div class="row ATTENDENCES  div-block">'
              +'<div class="col-md-3">' + i18next.t('Attendance Counts') + "</div>"
                +'<div class="col-md-9 ATTENDENCES-fields">'                
                +'</div>'
                +'<hr/>'
              +'</div>'
            +'</div>'            
            +'<div class="row eventPredicationTitle div-title">'
              +'<div class="col-md-12">'
                +i18next.t('Event Sermon')
              +'</div>'
            +'</div>'
            +'<div class="row  eventPredication  div-block">'
              +'<div class="col-md-12" style="padding-left:0px;padding-right:2px;">'
                  +'<textarea name="EventText" cols="80" class="form-control input-sm eventPredication" id="eventPredication"  width="100%" style="margin-top:-58px;width: 100%;height: 4em;"></textarea></div>'
              +'</div>'
            +'</div>'
            +'<div class="row  div-title">'
              +'<div class="col-md-3">'
                +'<span style="color: red">*</span>'+i18next.t('Event Status')
              +'</div>'
              +'<div class="col-md-4">'
                +'<input type="radio" name="EventStatus" value="0" checked/> '+i18next.t('Active')
              +'</div>'
              +'<div class="col-md-4">'
                +'<input type="radio" name="EventStatus" value="1" /> '+i18next.t('inactive')
              +'</div>'
            +'</div>'
          +'</div>'
       + '</form>';

        var object = $('<div/>').html(frm_str).contents();

        return object
    }
    
    function createEventEditorWindow (start,end,dialogType,eventID) // dialogType : createEvent or modifyEvent, eventID is when you modify and event
    {
      if (dialogType === undefined) {
        dialogType = 'createEvent';
      }
      
      if (eventID === undefined) {
        eventID = -1;
      }
      
      if (end == null) {
        end = start;
      }
      
      
      var modal = bootbox.dialog({
         message: BootboxContent(start,end),
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
                  var addGroupAttendees = document.getElementById("addGroupAttendees").checked;
                  
                  var eventInActive = $('input[name="EventStatus"]:checked').val();
                  
                  if (addGroupAttendees) {
                    eventAttendees = true;
                  }
                              
                  var EventGroupType = e.options[e.selectedIndex].title;// we get the type of the group : personal or group for future dev
                  
                  var countFieldsId = $('form #countFieldsId').val();
                  
                  var fields = new Array();
                  
                  for (i=0;i<countFieldsId;i++) {
                    var myObj = new Object();  
                                  
                    var name = $('form #field'+i).data('name');
                    var countid = $('form #field'+i).data('countid');
                    var value = $('form #field'+i).val();

                    myObj.name = name;
                    myObj.countid = countid;
                    myObj.value = value;
                    
                    fields[i] = myObj;
                  }
                  
                  var EventCountNotes = $('form #EventCountNotes').val();
                             
                  var eventPredication = CKEDITOR.instances['eventPredication'].getData();//$('form #eventPredication').val();
              
                  var add = false;
                                                            
                  window.CRM.APIRequest({
                        method: 'POST',
                        path: 'events/',
                        data: JSON.stringify({"evntAction":dialogType,"eventID":eventID,"eventTypeID":eventTypeID,"EventGroupType":EventGroupType,"EventTitle":EventTitle,"EventDesc":EventDesc,"EventGroupID":EventGroupID,"Fields":fields,"EventCountNotes":EventCountNotes,"eventPredication":eventPredication,"start":real_start,"end":real_end,"addGroupAttendees":addGroupAttendees,"eventInActive":eventInActive})
                  }).done(function(data) {                   
                    $('#calendar').fullCalendar('unselect');              
                    add = true;              
                    modal.modal("hide");   
                    
                    $('#calendar').fullCalendar( 'refetchEvents' );
                    
                    if (dialogType == 'createEvent') {
                      eventCreated = true;
                    }
                    
                    return true;
                  });

                  return add;  
              } else {
                  window.CRM.DisplayAlert("Error","You have to set a Title for your event");
                
                  return false;
              }    
            }
          }
         ],
         show: false/*,
         onEscape: function() {
            modal.modal("hide");
         }*/
       });
       
       return modal;
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
            revertFunc();
           }
        },
        eventClick: function(calEvent, jsEvent, view) {
          /*alert('Event: ' + calEvent.title);
          alert('Coordinates: ' + jsEvent.pageX + ',' + jsEvent.pageY);
          alert('View: ' + view.name);*/
          
          
            // We create the dialog
           modal = createEventEditorWindow (calEvent.start,calEvent.end,'modifyEvent',calEvent.eventID);       
       
           $('form #EventTitle').val(calEvent.title);
           $('form #EventDesc').val(calEvent.Desc);
           $('form #eventPredication').val(calEvent.Text);
           

           // we add the calendars and the types
           addGroupCalendars(calEvent.groupID);
           addGroupEventTypes(calEvent.eventTypeID,false);
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
           $(".eventPredication").hide();
       
           // this will ensure that image and table can be focused
           $(document).on('focusin', function(e) {e.stopImmediatePropagation();});
       
           // this will create the toolbar for the textarea
           CKEDITOR.replace('eventPredication',{
            customConfig: window.CRM.root+'/skin/js/ckeditor/calendar_event_editor_config.js',
            language : window.CRM.lang,
            width : '100%'
           });
      
           $(".ATTENDENCES").hide();
       
           modal.modal("show");

           // change the border color just for fun
           $(this).css('border-color', 'red');
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
          revertFunc();
         }
      },
      selectHelper: true,        
      select: function(start, end) {
         // We create the dialog
         modal = createEventEditorWindow (start,end);       
       
         // we add the calendars and the types
         addGroupCalendars();
         addGroupEventTypes(-1,true);
       
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
         $(".eventPredication").hide();
       
         // this will ensure that image and table can be focused
         $(document).on('focusin', function(e) {e.stopImmediatePropagation();});
       
         // this will create the toolbar for the textarea
         CKEDITOR.replace('eventPredication',{
          customConfig: window.CRM.root+'/skin/js/ckeditor/calendar_event_editor_config.js',
          language : window.CRM.lang,
          width : '100%'
         });
      
         $(".ATTENDENCES").hide();
       
         modal.modal("show");
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
          var box = window.CRM.DisplayAlert("Event added","Event was added successfully.");

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