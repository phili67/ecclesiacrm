document.addEventListener("DOMContentLoaded", function () {
  window.CRM.ElementListener('.delete-field', 'click', function (event) {
    let orderID = event.currentTarget.dataset.orderid;
    let field = event.currentTarget.dataset.field;

    bootbox.confirm({
      title: i18next.t("Attention"),
      message: i18next.t("Warning: By deleting this field, you will irrevocably lose all family data assigned for this field!"),
      callback: function (result) {
        if (result) {
          window.CRM.APIRequest({
            method: 'POST',
            path: 'families/deletefield',
            data: JSON.stringify({ "orderID": orderID, "field": field })
          }, function (data) {
            //window.CRM.dataFundTable.ajax.reload();
            window.location = window.location.href;
          });
        }
      }
    });
  });

  window.CRM.ElementListener('.up-action', 'click', function (event) {
    let orderID = event.currentTarget.dataset.orderid;
    let field = event.currentTarget.dataset.field;

    window.CRM.APIRequest({
      method: 'POST',
      path: 'families/upactionfield',
      data: JSON.stringify({ "orderID": orderID, "field": field })
    }, function (data) {
      //window.CRM.dataFundTable.ajax.reload();
      window.location = window.location.href;
    });
  });

  
  window.CRM.ElementListener('.down-action', 'click', function (event) {
    let orderID = event.currentTarget.dataset.orderid;
    let field = event.currentTarget.dataset.field;

    window.CRM.APIRequest({
      method: 'POST',
      path: 'families/downactionfield',
      data: JSON.stringify({ "orderID": orderID, "field": field })
    }, function (data) {
      //window.CRM.dataFundTable.ajax.reload();
      window.location = window.location.href;
    });
  });

  $("#custom-fields-table").DataTable({
        responsive: true,
        paging: false,
        searching: false,
        ordering: false,
        info: false,
        //dom: window.CRM.plugin.dataTable.dom,
        fnDrawCallback: function (settings) {
            $("#selector thead").remove();
        }
    });
});
