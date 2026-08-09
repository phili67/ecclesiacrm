//
//  This code is under copyright not under MIT Licence
//  copyright   : 2018 Philippe Logel all right reserved.
//                This code can't be included in another software.
//
//  Updated : 2020/06/18
//


$(function() {
    $("#systemInfosCollapse").on("shown.bs.collapse", function() {
      const mailTest = $("#mailTest");
      mailTest.removeClass("alert-success alert-danger").addClass("alert-light");
      mailTest.text(mailTest.data("loading-message"));

      window.CRM.APIRequest({
        method: 'POST',
        path: 'system/testEmailConnection'
      }, function(data) {
        if (data.success == true) {
          mailTest.removeClass("alert-light alert-danger").addClass("alert-success").html(data.result);
        } else {
          mailTest.removeClass("alert-light alert-success").addClass("alert-danger").html(data.error);
        }
      });
    });
});
