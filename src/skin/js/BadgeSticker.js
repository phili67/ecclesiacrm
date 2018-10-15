$(document).ready(function () {
    $(".my-colorpicker-back").colorpicker({
      color:back,
      inline:false,
      horizontal:true,
      right:true
    });
    
    $(".my-colorpicker-title").colorpicker({
      color:title,
      inline:false,
      horizontal:true,
      right:true
    });
    
    $(".delete-file").click(function () {
      var name = $(this).data("name");
      
      bootbox.confirm(i18next.t("Are you sure, you want to delete this image ?"), function(result){
        if (result) {
          window.CRM.APIRequest({
            method: 'POST',
            path: 'system/deletefile',
            data: JSON.stringify({"name": name, "path" : '/Images/background/'})
          }).done(function(data) {
            location.reload();
          });
        }
      });
    });

    $(".add-file").click(function () {
      var name = $(this).data("name");
      
      $("#image").val(name);
    }); 
});
