$(document).ready(function () {
  $('#onlineVerifySiteBtn').hide();
  $("#confirm-modal-done").hide();
  $("#confirm-modal-error").hide();

  $("#onlineVerifyBtn").click(function () {
    $.post(window.CRM.root + '/external/verify/' + token,
      {
        message: $("#confirm-info-data").val()
      },
      function (data, status) {
        $('#confirm-modal-collect').hide();
        $("#onlineVerifyCancelBtn").hide();
        $("#onlineVerifyBtn").hide();
        $("#onlineVerifySiteBtn").show();
        if (status == "success") {
          $("#confirm-modal-done").show();
        } else {
          $("#confirm-modal-error").show();
        }
      });
  });
});
