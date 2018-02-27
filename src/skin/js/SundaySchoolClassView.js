$("document").ready(function(){
   $(document).on("click",".AddToStudentGroupCart", function(){
      clickedButton = $(this);
      window.CRM.cart.addStudentGroup(clickedButton.data("cartstudentgroupid"),function()
      {
        $(clickedButton).addClass("RemoveFromStudentGroupCart");
        $(clickedButton).removeClass("AddToStudentGroupCart");
        $('i',clickedButton).addClass("fa-remove");
        $('i',clickedButton).removeClass("fa-cart-plus");
        text = $(clickedButton).find("span.cartActionDescription")
        if(text){
          $(text).text(i18next.t("Remove Students from Cart"));
        }
      });
    });
    
    $(document).on("click",".RemoveFromStudentGroupCart", function(){
      clickedButton = $(this);
      window.CRM.cart.removeStudentGroup(clickedButton.data("cartstudentgroupid"),function()
      {
        $(clickedButton).addClass("AddToStudentGroupCart");
        $(clickedButton).removeClass("RemoveFromStudentGroupCart");
        $('i',clickedButton).removeClass("fa-remove");
        $('i',clickedButton).addClass("fa-cart-plus");
        text = $(clickedButton).find("span.cartActionDescription")
        if(text){
          $(text).text(i18next.t("Add Students to Cart"));
        }
      });
    });
    
    $(document).on("click",".AddToTeacherGroupCart", function(){
      clickedButton = $(this);
      window.CRM.cart.addTeacherGroup(clickedButton.data("cartteachergroupid"),function()
      {
        $(clickedButton).addClass("RemoveFromTeacherGroupCart");
        $(clickedButton).removeClass("AddToTeacherGroupCart");
        $('i',clickedButton).addClass("fa-remove");
        $('i',clickedButton).removeClass("fa-cart-plus");
        text = $(clickedButton).find("span.cartActionDescription")
        if(text){
          $(text).text(i18next.t("Remove Teachers from Cart"));
        }
      });
    });
    
    $(document).on("click",".RemoveFromTeacherGroupCart", function(){
      clickedButton = $(this);
      window.CRM.cart.removeTeacherGroup(clickedButton.data("cartteachergroupid"),function()
      {
        $(clickedButton).addClass("AddToTeacherGroupCart");
        $(clickedButton).removeClass("RemoveFromTeacherGroupCart");
        $('i',clickedButton).removeClass("fa-remove");
        $('i',clickedButton).addClass("fa-cart-plus");
        text = $(clickedButton).find("span.cartActionDescription")
        if(text){
          $(text).text(i18next.t("Add Teachers to Cart"));
        }
      });
    });
    
    $(document).on("click",".makeCheckOut", function(){
       var groupName = $(this).data("makecheckoutgroupname")
       var groupID = $(this).data("makecheckoutgroupid");
       
       window.CRM.APIRequest({
          method: 'GET',
          path: 'events/types',
        }).done(function(typeNames) {
           var lenType = typeNames.length;
           var options = new Array();
           
           var boxOptions ={
             title: i18next.t("Select the event Type you would like to use to create the Attendance")+" : "+groupName,
             message: '<div class="modal-body">',
             buttons: {
               addEvent: {  
                   label: i18next.t("Create First A New Event"),
                   className: 'btn-info',
                   callback: function() {
                      location.href = window.CRM.root + '/calendar.php';
                   }
               },
               cancel: {
                   label: i18next.t('Cancel'),
                   className: 'btn-danger'
               },
               confirm: {
                   label: i18next.t('Create Event With Students'),
                   className: 'btn btn-primary',
                   callback: function() {
                        var e = document.getElementById("typeChosen");
                        var eventTypeID = e.options[e.selectedIndex].value;
                        
                        window.CRM.APIRequest({
                          method: 'POST',
                          path: 'attendees/student',
                          data: JSON.stringify({"eventTypeID":eventTypeID,"groupID":groupID})
                        }).done(function(data) {
                           location.href = window.CRM.root+'/Checkin.php';
                        });
                   }
               }
             }
          };
          
          boxOptions.message +='<center>'+i18next.t("You can create the event automatically with the students<br> - OR - <br>Add the students to the cart and create an event to add them after.")+'</center><br>';
          boxOptions.message +='<select class="bootbox-input bootbox-input-select form-control" id="typeChosen">';
          for (i=0;i<lenType;i++) {
             boxOptions.message +='<option value="'+typeNames[i].eventTypeID+'">'+typeNames[i].name+'</option>';
           }
                      
          boxOptions.message +='</select>\
                             </div>';
          
          bootbox.dialog(boxOptions).show();
      })
    });
    
    function BootboxContentCSV(start,end) 
    {  
      var time_format;
      var fmt = window.CRM.datePickerformat.toUpperCase();
        
      var dateStart = moment(start).format(fmt);
      var dateEnd = moment(end).format(fmt);
      
    
      var frm_str = '<b><p>'+i18next.t("First, set your time range correctly to make the extraction.")+'</p></b><hr/><form id="some-form">'
          +'<div class="row">'
              +'<div class="col-md-12">'
                  +'<div class="row">'
                    +'<div class="col-md-3"><span style="color: red">*</span>'
                      + i18next.t('Start Date')+' :'
                    +'</div>'
                     +'<div class="col-md-3">'  
                       +'<div class="input-group">'
                          +'<div class="input-group-addon">'
                              +'<i class="fa fa-calendar"></i>'
                          +'</div>'
                          +'<input class="form-control date-picker input-sm" type="text" id="dateEventStart" name="dateEventStart"  value="'+dateStart+'" '
                                +'maxlength="10" id="sel1" size="11"'
                                +'placeholder="'+window.CRM.datePickerformat+'">'
                        +'</div>'
                    +'</div>'
                    +'<div class="col-md-3"><span style="color: red">*</span>'
                      + i18next.t('End Date')+' :'
                    +'</div>'
                     +'<div class="col-md-3">'  
                       +'<div class="input-group">'
                          +'<div class="input-group-addon">'
                              +'<i class="fa fa-calendar"></i>'
                          +'</div>'
                          +'<input class="form-control date-picker input-sm" type="text" id="dateEventEnd" name="dateEventEnd"  value="'+dateEnd+'" '
                                +'maxlength="10" id="sel1" size="11"'
                                +'placeholder="'+window.CRM.datePickerformat+'">'
                        +'</div>'
                    +'</div>'
                  +'</div>'
                +'</div>'
            +'</div>'
         + '</form>';
        
      var object = $('<div/>').html(frm_str).contents();

      return object
    }

    
    $(document).on("click",".exportCheckOutCSV", function(){
       var groupID = $(this).data("makecheckoutgroupid");
       
       var start=moment().subtract(1, 'years').format('YYYY-MM-DD');
       var end=moment().format('YYYY-MM-DD');
       
       var modal = bootbox.dialog({
         title: i18next.t("Set the year range to export"),
         message: BootboxContentCSV(start,end),
         buttons: [
          {
           label: i18next.t("Cancel"),
           className: "btn btn-default",
           callback: function() {
              console.log("just do something on close");
           }
          },
          {
            label: i18next.t('OK'),
            className: "btn btn-primary",
            callback: function() {
                  var dateStart = $('form #dateEventStart').val();
                  var dateEnd = $('form #dateEventEnd').val();
                  
                  var fmt = window.CRM.datePickerformat.toUpperCase();
    
                  var real_start = moment(dateStart,fmt).format('YYYY-MM-DD');
                  var real_end = moment(dateEnd,fmt).format('YYYY-MM-DD');
                  
                  window.location = window.CRM.root + "/sundayschool/SundaySchoolAttendeesExport.php?groupID="+groupID+"&start="+real_start+"&end="+real_end;
            }
          }
         ],
         show: false/*,
         onEscape: function() {
            modal.modal("hide");
         }*/
       });
       
       
       
       modal.modal("show");

       $('.date-picker').datepicker({format:window.CRM.datePickerformat, language: window.CRM.lang});
    });
    
    function BootboxContentPDF(start,end) 
    {  
      var time_format;
      var fmt = window.CRM.datePickerformat.toUpperCase();
        
      var dateStart = moment(start).format(fmt);
      var dateEnd = moment(end).format(fmt);
      
    
      var frm_str = '<b><p>'+i18next.t("First, set your time range correctly to make the extraction.")+'</p></b><hr/><form id="some-form">'
          +'<div class="row">'
              +'<div class="col-md-12">'
                  +'<div class="row">'
                    +'<div class="col-md-3"><span style="color: red">*</span>'
                      + i18next.t('Start Date')+' :'
                    +'</div>'
                     +'<div class="col-md-3">'  
                       +'<div class="input-group">'
                          +'<div class="input-group-addon">'
                              +'<i class="fa fa-calendar"></i>'
                          +'</div>'
                          +'<input class="form-control date-picker input-sm" type="text" id="dateEventStart" name="dateEventStart"  value="'+dateStart+'" '
                                +'maxlength="10" id="sel1" size="11"'
                                +'placeholder="'+window.CRM.datePickerformat+'">'
                        +'</div>'
                    +'</div>'
                    +'<div class="col-md-3"><span style="color: red">*</span>'
                      + i18next.t('End Date')+' :'
                    +'</div>'
                     +'<div class="col-md-3">'  
                       +'<div class="input-group">'
                          +'<div class="input-group-addon">'
                              +'<i class="fa fa-calendar"></i>'
                          +'</div>'
                          +'<input class="form-control date-picker input-sm" type="text" id="dateEventEnd" name="dateEventEnd"  value="'+dateEnd+'" '
                                +'maxlength="10" id="sel1" size="11"'
                                +'placeholder="'+window.CRM.datePickerformat+'">'
                        +'</div>'
                    +'</div>'
                  +'</div>'
                +'</div>'
            +'</div>'
            +'<br>'
            +'<div class="row">'
              +'<div class="col-md-3"><span style="color: red">*</span>'
                + i18next.t('Extra students')+' :'
              +'</div>'
              +'<div class="col-md-3">'
                +'<input class="form-control input-sm" type="text" id="ExtraStudents" name="ExtraStudents"  value="0" maxlength="10" id="sel1" size="11">'
              +'</div>'
              +'<div class="col-md-6">'
                +'<input id="withPictures" type="checkbox" checked> '+ i18next.t('export with photos')
              +'</div>'
              +'</div>'
            +'</div>'
         + '</form>';
        
      var object = $('<div/>').html(frm_str).contents();

      return object
    }
    
    $(document).on("click",".exportCheckOutPDF", function(){
       var groupID = $(this).data("makecheckoutgroupid");
       
       var start=moment().subtract(1, 'years').format('YYYY-MM-DD');
       var end=moment().format('YYYY-MM-DD');
       
       var modal = bootbox.dialog({
         title: i18next.t("Set the year range to export"),
         message: BootboxContentPDF(start,end),
         buttons: [
          {
           label: i18next.t("Cancel"),
           className: "btn btn-default",
           callback: function() {
              console.log("just do something on close");
           }
          },
          {
            label: i18next.t('OK'),
            className: "btn btn-primary",
            callback: function() {
              var dateStart = $('form #dateEventStart').val();
              var dateEnd = $('form #dateEventEnd').val();
              
              var fmt = window.CRM.datePickerformat.toUpperCase();

              var real_start = moment(dateStart,fmt).format('YYYY-MM-DD');
              var real_end = moment(dateEnd,fmt).format('YYYY-MM-DD');
              
              var withPictures = ($("#withPictures").is(':checked') == true)?1:0;
              var ExtraStudents = $("#ExtraStudents").val();
              
              window.location = window.CRM.root + "/Reports/ClassRealAttendance.php?groupID="+groupID+"&start="+real_start+"&end="+real_end+"&withPictures="+withPictures+"&ExtraStudents="+ExtraStudents;
            }
          }
         ],
         show: false/*,
         onEscape: function() {
            modal.modal("hide");
         }*/
       });
       
       
       
       modal.modal("show");

       $('.date-picker').datepicker({format:window.CRM.datePickerformat, language: window.CRM.lang});
    });
    
    
    
    // newMessage event subscribers  : Listener CRJSOM.js
    $(document).on("emptyCartMessage", updateButtons);
    
    // newMessage event handler
    function updateButtons(e) {
      if (e.cartSize == 0) {
        $("#AddToTeacherGroupCart").addClass("AddToTeacherGroupCart");
        $("#AddToTeacherGroupCart").removeClass("RemoveFromTeacherGroupCart");
        $('i',"#AddToTeacherGroupCart").removeClass("fa-remove");
        $('i',"#AddToTeacherGroupCart").addClass("fa-cart-plus");
        text = $("#AddToTeacherGroupCart").find("span.cartActionDescription")
        if(text){
          $(text).text(i18next.t("Add Teachers to Cart"));
        }
        
        $("#AddToStudentGroupCart").addClass("AddToStudentGroupCart");
        $("#AddToStudentGroupCart").removeClass("RemoveFromStudentGroupCart");
        $('i',"#AddToStudentGroupCart").removeClass("fa-remove");
        $('i',"#AddToStudentGroupCart").addClass("fa-cart-plus");
        text = $("#AddToStudentGroupCart").find("span.cartActionDescription")
        if(text){
          $(text).text(i18next.t("Add Students to Cart"));
        }
      }
    }
});