$(document).ready(function () {
  $('#onlineVerifySiteBtn').hide();
  $("#confirm-modal-done").hide();
  $("#confirm-modal-error").hide();

  $("#onlineVerifyBtn").click(function () {
    $.post(window.CRM.root + '/ident/my-profile/' + token,
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

    function BootboxContent(data) {

        var frm_str = '<h3 style="margin-top:-5px">' + i18next.t("Editor") + '</h3>'
            + '<form id="some-form">';

        frm_str += data
            + '</form>';

        var object = $('<div/>').html(frm_str).contents();

        return object;
    }

    function PersonWindow(data) {

        var modal = bootbox.dialog({
            message: BootboxContent(data),
            buttons: [
                {
                    label: '<i class="fas fa-times"></i> ' + i18next.t("Close"),
                    className: "btn btn-default",
                    callback: function () {
                    }
                },
                {
                    label: '<i class="fas fa-check"></i> ' + i18next.t("Save"),
                    className: "btn btn-primary",
                    callback: function () {

                    }
                }
            ],
            show: false,
            onEscape: function () {
            }
        });

        // this will ensure that image and table can be focused
        $(document).on('focusin', function (e) {
            e.stopImmediatePropagation();
        });

        return modal;
    }

  $(".modifyPerson").click(function () {
      var personId = $(this).data("id");

      $.post(window.CRM.root + '/ident/my-profile/getPersonInfo/', {"personId":personId}, function(data){

          var modal = PersonWindow(data.html);
          modal.modal("show");

          $('.date-picker').datepicker({format: window.CRM.datePickerformat, language: window.CRM.lang});
      });
  });

  $(".deletePerson").click(function (){
      var personId = $(this).data("id");

      alert('delete in progress');
  });
});
