//
//  This code is under copyright not under MIT Licence
//  copyright   : 2018 Philippe Logel all right reserved.
//                This code can't be included in another software.
//
//  Updated : 2020/06/18
//


$(document).ready(function () {
    window.CRM.APIRequest({
      method: 'POST',
      path: 'mailchimp/testConnection'
    }).done(function(data) {
      if (data.error == undefined) {
        $("#mailTest").html(data.result);
      } else {
        $("#mailTest").html(data.error);
      }
    });
});
