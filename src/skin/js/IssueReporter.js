$("#submitIssue").click(function () {
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
    $("#submitDiaglogStart" ).hide();
    $("#submitDiaglogFinish" ).show();
    
    //console.log(data);
    
    $("#issueSubmitSucces" ).text(data.number);
    $("#issueSubmitSuccesLink").prop("href", data.url);
    $("#issueSubmitSuccesLinkText" ).text(data.number);
  });
});

$("#submitIssueDone").click(function () {
    $("#IssueReportModal").modal('toggle');;
    $("#submitDiaglogStart" ).show();
    $("#submitDiaglogFinish" ).hide();   
    $("input:text[name=issueTitle]").val(""); 
    $("textarea[name=issueDescription]").val(""); 
});


$(document).ready(function(){
  $("#submitDiaglogStart" ).show();
  $("#submitDiaglogFinish" ).hide();
});
