$(document).ready(function () {

    window.CRM.APIRequest({
      method: 'POST',
      path: 'mailchimp/testConnection'
    }).done(function(data) { 
      $("#mailTest").html(data.result);
    });

});