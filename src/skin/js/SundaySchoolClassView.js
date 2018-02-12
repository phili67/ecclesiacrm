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
    
    $(document).on("click",".exportCheckOut", function(){
       var groupID = $(this).data("makecheckoutgroupid");
       
       bootbox.prompt({
        size: "small",
        title: i18next.t("Set the Year you want."),
        value: moment().year()-1+'-'+moment().year(),
        buttons: {
          cancel: {
            label: i18next.t('Cancel'),
            className: 'btn-default',
            callback: function () {
            }
          },
          confirm: {
            label: i18next.t('OK'),
            className: 'btn-primary',
            callback: function () {
            }
          }
        },
        callback: function(result){ 
          if (result) {
            window.location = window.CRM.root + "/sundayschool/SundaySchoolAttendeesExport.php?groupID="+groupID+"&year="+result;
          }
       }
      });
       
       
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