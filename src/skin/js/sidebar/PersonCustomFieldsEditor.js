$(document).ready(function () {
  $(document).on("click",".delete-field", function(){
     var orderID = $(this).data("orderid");
     var field = $(this).data("field");
     
     bootbox.confirm({
      title: i18next.t("Attention"),
      message: i18next.t("Warning:  By deleting this field, you will irrevocably lose all family data assigned for this field!"),
      callback: function(result){
        if (result) {
          window.CRM.APIRequest({
            method: 'POST',
            path: 'persons/deletefield',
            data: JSON.stringify({"orderID": orderID,"field":field})
          }).done(function(data) {
            //window.CRM.dataFundTable.ajax.reload();
            location.reload();
          });
        }
      }
    });
  });
  
  $(document).on("click",".up-action", function(){
    var orderID = $(this).data("orderid");
    var field   = $(this).data("field");
     
    window.CRM.APIRequest({
      method: 'POST',
      path: 'persons/upactionfield',
      data: JSON.stringify({"orderID": orderID,"field":field})
    }).done(function(data) {
      //window.CRM.dataFundTable.ajax.reload();
      location.reload();
    });
  }); 
  
  $(document).on("click",".down-action", function(){
    var orderID = $(this).data("orderid");
    var field   = $(this).data("field");
     
    window.CRM.APIRequest({
      method: 'POST',
      path: 'persons/downactionfield',
      data: JSON.stringify({"orderID": orderID,"field":field})
    }).done(function(data) {
      //window.CRM.dataFundTable.ajax.reload();
      location.reload();
    });
  });
});