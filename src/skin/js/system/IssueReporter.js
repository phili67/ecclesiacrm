/* Copright Philippe Logel 2018 */

$(document).ready(function(){
  $("#submitDiaglogStart" ).show();
  $("#submitDiaglogFinish" ).hide();
});


$("#submitIssue").click(function () {
  if ($("input:text[name=issueTitle]").val() && $("textarea[name=issueDescription]").val()) {
    var postData = {
      "issueTitle": $("input:text[name=issueTitle]").val(),
      "issueDescription": $("textarea[name=issueDescription]").val(),
      "pageName" : $("input[name=pageName]").val(),
      "screenSize": {
          "height":screen.height,
          "width":screen.width
      },
      "windowSize":{
          "height":$(window).height(),
          "width":$(window).width()
      },
      "pageSize" : {
          "height" : $(document).height(),
          "width":$(document).width()
      }
    };
    $.ajax({
      method: "POST",
      url: window.CRM.root + "/api/issues",
      data: JSON.stringify(postData),
      contentType: "application/json; charset=utf-8",
      dataType: "json"
    }).done(function (data) {
      console.log(data);
      
      $("#issueSubmitSucces" ).text(data.number);
      $("#issueSubmitSuccesLink").prop("href", data.url);
      $("#issueSubmitSuccesLinkText" ).text(data.number);
      
      $("#submitDiaglogStart" ).hide();
      $("#submitDiaglogFinish" ).show();    
    });
  } else {
    var box = bootbox.dialog({title: "<span style='color: red;'>"+i18next.t("Error")+"</span>",message : i18next.t("You have to set a Title and a Description for your issue.")});
                
    setTimeout(function() {
        // be careful not to call box.hide() here, which will invoke jQuery's hide method
        box.modal('hide');
    }, 3000);
  
    return false;
  }
});

$("#submitIssueDone").click(function () {
    $("#IssueReportModal").modal('toggle');
    $("#submitDiaglogStart" ).show();
    $("#submitDiaglogFinish" ).hide();   
    $("input:text[name=issueTitle]").val(""); 
    $("textarea[name=issueDescription]").val(""); 
});


$("#IssueReportModal .close").click(function(){
  $("#submitDiaglogStart" ).show();
  $("#submitDiaglogFinish" ).hide();
  
  $("input:text[name=issueTitle]").val(""); 
  $("textarea[name=issueDescription]").val(""); 
})
