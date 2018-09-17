$("document").ready(function(){
   // search for the dates
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
  
  
  // the chart donut code 
    var dataTable = $('.data-table').DataTable({
         "language": {
           "url": window.CRM.plugin.dataTable.language.url
         },
         pageLength: 100,
         responsive: true,
         order: [[ 0, "asc" ]]
       });
    
    // turn the element to select2 select style
    $('.email-recepients-kids').select2({
      placeholder: 'Enter recepients',
      tags:KidsEmails
    });
    $('.email-recepients-teachers').select2({
      placeholder: 'Enter recepients',
      tags: TeachersEmails
    });
    $('.email-recepients-parents').select2({
      placeholder: 'Enter recepients',
      tags: ParentsEmails
    });

    var birthDateColumn = dataTable.column(':contains('+birthDateColumnText+')');

    var hideBirthDayFilter = function() {
      plot.unhighlight();
      birthDateColumn
        .search('')
        .draw();

      birthDayFilter.hide();
    };
    

    var birthDayFilter = $('.birthday-filter');
    var birthDayMonth = birthDayFilter.find('.month');
    birthDayFilter.find('i.fa-close')
      .bind('click', hideBirthDayFilter);

    $("#bar-chart").bind("plotclick", function (event, pos, item) {
      plot.unhighlight();

      if (!item) {
        hideBirthDayFilter();
        return;
      }

      var month = bar_data.data[item.dataIndex][0];

      birthDateColumn
        .search(month.substr(0, 7))
        .draw();

      birthDayMonth.text(month);
      birthDayFilter.show();

      plot.highlight(item.series, item.datapoint);
    });


  /*
   * BAR CHART
   * ---------
   */

  var bar_data = {
    data: birthDayMonthChartJSON,
    color: "#3c8dbc"
  };

 var plot = $.plot("#bar-chart", [bar_data], {
    grid: {
      borderWidth: 1,
      borderColor: "#f3f3f3",
      tickColor: "#f3f3f3",
      hoverable:true,
      clickable:true
    },
    series: {
      bars: {
        show: true,
        barWidth: 0.5,
        align: "center"
      }
    },
    xaxis: {
      mode: "categories",
      tickLength: 0
    },
    yaxis: {
      tickSize: 1
    }
  });

  /* END BAR CHART */

  /*
   * DONUT CHART
   * -----------
   */
     
  var genderColumn = dataTable.column(':contains('+genderColumnText+')');

  var hideGenderFilter = function() {
      plot.unhighlight();
      genderColumn
        .search('')
        .draw();

      genderFilter.hide();
  };


  var genderFilter = $('.gender-filter');
  var genderType = genderFilter.find('.type');;
  
  genderFilter.find('i.fa-close')
      .bind('click', hideGenderFilter);


  var donutData = genderChartJSON;
  var placeholder = $("#donut-chart");

  $.plot("#donut-chart", donutData, {
    series: {
      pie: {
        show: true,
        radius: 1,
        innerRadius: 0.5,
        label: {
          show: true,
          radius: 2 / 3,
          formatter: labelFormatter,
          threshold: 0.1
        }

      }
    },
    grid: {
        hoverable: true,
        clickable: true
    },
    legend: {
      show: false
    }
  });
  
  placeholder.bind("plotclick", function(event, pos, obj) {

    if (!obj) {
      return;
    }

    percent = parseFloat(obj.series.percent).toFixed(2);
      
    //alert(""  + obj.series.label + ": cpicpi" + percent + "%");
      
    var gender = obj.series.label;
    
    var searchGender = gender.substr(0, gender.length-1);
    
    genderColumn
        .search(searchGender)
        .draw();
    
    genderType.text(gender);
    genderFilter.show();
  });
  
  /*
   * END DONUT CHART
   */
  /*
   * Custom Label formatter
   * ----------------------
   */
  function labelFormatter(label, series) {
    return "<div style='font-size:13px; text-align:center; padding:2px; color: #fff; font-weight: 600;'>"
      + label
      + "<br/>"
      + Math.round(series.percent) + "%</div>";
  }  
      
  /* the Cart management */
  // the little buttons
  $(document).on("click",".AddOneStudentToCart", function(){
      clickedButton = $(this);
      window.CRM.cart.addPerson([clickedButton.data("cartpersonid")],function()
      {
        $(clickedButton).addClass("RemoveOneStudentFromCart");
        $(clickedButton).removeClass("AddOneStudentToCart");
        $('span i:nth-child(2)',clickedButton).addClass("fa-remove");
        $('span i:nth-child(2)',clickedButton).removeClass("fa-cart-plus");
      });
      
      var studentsButton = $('#AddStudentsToGroupCart');
      $(studentsButton).addClass("RemoveStudentsFromGroupCart");
      $(studentsButton).removeClass("AddStudentsToGroupCart");
      $('i',studentsButton).addClass("fa-remove");
      $('i',studentsButton).removeClass("fa-cart-plus");
      text = $(studentsButton).find("span.cartActionDescription")
      if(text){
        $(text).text(i18next.t("Remove Students from Cart"));
      }
  });
  
  $(document).on("click",".RemoveOneStudentFromCart", function(){
      var clickedButton = $(this);
      window.CRM.cart.removePerson([clickedButton.data("cartpersonid")],function()
      {
        $(clickedButton).addClass("AddOneStudentToCart");
        $(clickedButton).removeClass("RemoveOneStudentFromCart");
        $('span i:nth-child(2)',clickedButton).removeClass("fa-remove");
        $('span i:nth-child(2)',clickedButton).addClass("fa-cart-plus");
      });
  });

  $(document).on("click",".AddStudentsToGroupCart", function(){
      var clickedButton = $(this);
      window.CRM.cart.addStudentGroup(clickedButton.data("cartstudentgroupid"),function()
      {
        $(clickedButton).addClass("RemoveStudentsFromGroupCart");
        $(clickedButton).removeClass("AddStudentsToGroupCart");
        $('i',clickedButton).addClass("fa-remove");
        $('i',clickedButton).removeClass("fa-cart-plus");
        text = $(clickedButton).find("span.cartActionDescription")
        if(text){
          $(text).text(i18next.t("Remove Students from Cart"));
        }
        
        // everything is done in the listener 
        /*$('.AddOneStudentToCart').addClass("RemoveOneStudentFromCart");
        $('.RemoveOneStudentFromCart').removeClass("AddOneStudentToCart");
        $(".fa-inverse").removeClass("fa-cart-plus");
        $(".fa-inverse").addClass("fa-remove");*/
      });
    });
    
    $(document).on("click",".RemoveStudentsFromGroupCart", function(){
      clickedButton = $(this);
      window.CRM.cart.removeStudentGroup(clickedButton.data("cartstudentgroupid"),function()
      {
        $(clickedButton).addClass("AddStudentsToGroupCart");
        $(clickedButton).removeClass("RemoveStudentsFromGroupCart");
        $('i',clickedButton).removeClass("fa-remove");
        $('i',clickedButton).addClass("fa-cart-plus");
        text = $(clickedButton).find("span.cartActionDescription")
        if(text){
          $(text).text(i18next.t("Add Students to Cart"));
        }
        
        // everything is done in the listener 
        /*$('.RemoveOneStudentFromCart').addClass("AddOneStudentToCart");
        $('.AddOneStudentToCart').removeClass("RemoveOneStudentFromCart");
        $(".fa-inverse").removeClass("fa-remove");
        $(".fa-inverse").addClass("fa-cart-plus");*/
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
    
    
    
    
    // newMessage event subscribers  : Listener CRJSOM.js
    $(document).on("updateCartMessage", updateButtons);
    
    // newMessage event handler
    function updateButtons(e) {
      var cartPeople = e.people;
      
      if (cartPeople != null) {
        personButtons = $("a[data-cartpersonid]");
        $(personButtons).each(function(index,personButton){
          personID = $(personButton).data("cartpersonid");
          if (cartPeople.includes(personID) || cartPeople.includes(personID.toString())) {
            personPresent = true;
            $(personButton).addClass("RemoveOneStudentFromCart");
            $(personButton).removeClass("AddOneStudentToCart");
            fa = $(personButton).find("i.fa.fa-inverse");
            $(fa).addClass("fa-remove");
            $(fa).removeClass("fa-cart-plus");
            text = $(personButton).find("span.cartActionDescription")
            if(text){
              $(text).text(i18next.t("Remove from Cart"));
            }
          } else {
            $(personButton).addClass("AddOneStudentToCart");
            $(personButton).removeClass("RemoveOneStudentFromCart");
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
    }
    
    // newMessage event subscribers  : Listener CRJSOM.js
    $(document).on("emptyCartMessage", emptyButtons);
    
    // newMessage event handler
    function emptyButtons(e) {
      if (e.cartSize == 0) {
        $("#AddToTeacherGroupCart").addClass("AddToTeacherGroupCart");
        $("#AddToTeacherGroupCart").removeClass("RemoveFromTeacherGroupCart");
        $('i',"#AddToTeacherGroupCart").removeClass("fa-remove");
        $('i',"#AddToTeacherGroupCart").addClass("fa-cart-plus");
        text = $("#AddToTeacherGroupCart").find("span.cartActionDescription")
        if(text){
          $(text).text(i18next.t("Add Teachers to Cart"));
        }
        
        $("#AddStudentsToGroupCart").addClass("AddStudentsToGroupCart");
        $("#AddStudentsToGroupCart").removeClass("RemoveStudentsFromGroupCart");
        $('i',"#AddStudentsToGroupCart").removeClass("fa-remove");
        $('i',"#AddStudentsToGroupCart").addClass("fa-cart-plus");
        text = $("#AddStudentsToGroupCart").find("span.cartActionDescription")
        if(text){
          $(text).text(i18next.t("Add Students to Cart"));
        }
        
        var clickedButton = $('.RemoveOneStudentFromCart');

        $(clickedButton).addClass("AddOneStudentToCart");
        $(clickedButton).removeClass("RemoveOneStudentFromCart");
        $('span i:nth-child(2)',clickedButton).removeClass("fa-remove");
        $('span i:nth-child(2)',clickedButton).addClass("fa-cart-plus");
      }
    }

    // end of cart management
    
    
    // checkout the student    
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
                      location.href = window.CRM.root + '/Calendar.php';
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
         title: i18next.t("Set year range to export"),
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
         title: i18next.t("Set year range to export"),
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
});
