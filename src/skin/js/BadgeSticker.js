$(function() {
    $(".my-colorpicker-back").colorpicker({
      color:window.CRM.back,
      inline:false,
      horizontal:true,
      right:true
    });

    $(".my-colorpicker-title").colorpicker({
      color:window.CRM.title,
      inline:false,
      horizontal:true,
      right:true
    });

    $(".delete-file").on('click', function () {
      var name = $(this).data("name");

      bootbox.confirm(i18next.t("Are you sure, you want to delete this image ?"), function(result){
        if (result) {
          window.CRM.APIRequest({
            method: 'POST',
            path: 'system/deletefile',
            data: JSON.stringify({"name": name, "path" : '/Images/background/'})
          },function(data) {
            location.reload();
          });
        }
      });
    });

    $(".add-file").on('click', function () {
      var name = $(this).data("name");

      $("#image").val(name);

      window.CRM.image = name;

      window.CRM.reloadLabel();
    });
});
