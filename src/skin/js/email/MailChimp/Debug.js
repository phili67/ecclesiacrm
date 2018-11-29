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