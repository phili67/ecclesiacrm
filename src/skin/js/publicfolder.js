$(document).ready(function () {
  window.CRM.APIRequest({
    method: 'POST',
    path: 'filemanager/setpathtopublicfolder'
  },function(data) {
    if (data.success == "failed") {// we changed to /public/ folder
      window.CRM.DisplayAlert(i18next.t("Attention"),i18next.t("The current images folder is now the public folder !!!"));
    }
  });
});
