// Copyright 2018 Philippe Logel all right reserved

$(document).ready(function () {

  $(document).on("click",".PersonChangeState", function(){  
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
           $('#presenceID'+personID).text(i18next.t("Present"));
           message = "Attendees validated successfully.";
         } else {
           $('#checkoutDatePersonID'+personID).text("");
           $('#presenceID'+personID).text(i18next.t("Absent"));
           message = "Attendees unvalidated successfully.";
         }
         
         /*var box = window.CRM.DisplayAlert("Attendance",message);

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
      data: JSON.stringify({"eventID":eventID})
    }).done(function(data) {   
      location.reload();
    });
  });

  $(document).on("click","#checkAll", function(){  
    var eventID  = $(this).data("id");
    
     window.CRM.APIRequest({
      method: 'POST',
      path: 'attendees/checkAll',
      data: JSON.stringify({"eventID":eventID})
    }).done(function(data) {   
      location.reload();
    });
  });
  

});