//
//  This code is under copyright not under MIT Licence
//  copyright   : 2018 Philippe Logel all right reserved not MIT licence
//  Updated     : 2018/06/28
//

$('.RemoveClassification').click('focus', function (e) {
  var mode = $(this).data('mode');
  var order = $(this).data('order');
  var listID = $(this).data('listid');
  var ID = $(this).data('id');
  var name = $(this).data('name');
  
  
  bootbox.setDefaults({
    locale: window.CRM.shortLocale}),
    bootbox.confirm({
      title: i18next.t("Delete Classification"),
      message: '<p style="color: red">'+
        i18next.t("Please confirm deletion of this classification")+" : \""+name+"\" ?</p>"+
        "<p style='color: red'><b>"+
        i18next.t("This will also delete this Classification for all the associated persons.")+
        "</b><br></p><p style='color: red'><b>"+
        i18next.t("This can't be undone !!!!")+"</b></p>",
      callback: function (result) {
        if (result)
        {
            window.location.href = window.CRM.root+"/OptionManagerRowOps.php?mode="+mode+"&Order="+order+"&ListID="+listID+"&ID="+ID+"&Action=delete";
        }
      }
    });
});