document.addEventListener("DOMContentLoaded", function () {

  window.CRM.dataPropertyListTable = new DataTable("#property-listing-table-v2", {
    ajax: {
      url: window.CRM.root + "/api/properties/propertytypelists",
      type: 'POST',
      contentType: "application/json",
      dataSrc: "PropertyTypeLists",
      "beforeSend": function (xhr) {
        xhr.setRequestHeader('Authorization',
          "Bearer " + window.CRM.jwtToken
        );
      }
    },
    initComplete: function (settings, json) {
      loadTableEvents();
    },
    "language": {
      "url": window.CRM.plugin.dataTable.language.url
    },
    "order": [[2, "asc"]],
    columns: [
      {
        width: 'auto',
        title: i18next.t('Actions'),
        data: 'PrtId',
        render: function (data, type, full, meta) {
          if (window.CRM.menuOptionEnabled == false)
            return '';

          var res = '<a href="#" data-typeid="' + full.PrtId + '" class="edit-prop"><i class="fas fa-pencil-alt" aria-hidden="true"></i></a>';
          res += '&nbsp;&nbsp;&nbsp;<a href="#" data-typeid="' + full.PrtId + '" data-warn="' + full.Properties + '" class="delete-prop"><i class="far fa-trash-alt" aria-hidden="true" style="color:red"></i></a>';
          return res;
        }
      },
      {
        width: 'auto',
        title: i18next.t('Name'),
        data: 'PrtName',
        render: function (data, type, full, meta) {
          return data;
        }
      },
      {
        width: 'auto',
        title: i18next.t('Class'),
        data: 'PrtClass',
        render: function (data, type, full, meta) {
          return i18next.t(data);
        }
      },
      {
        width: 'auto',
        title: i18next.t('Description'),
        data: 'PrtDescription',
        render: function (data, type, full, meta) {
          return data;
        }
      }
    ],
    responsive: true,
    createdRow: function (row, data, index) {
      $(row).addClass("listRow");
    }
  });


  /* IMPORTANT : be careful
       This will work in cartToGroup code */
  const BootboxContentPropertyTypeList = (type) => {
    var frm_str = '<div class="card-body">'
      + ' <div class="row">'
      + '  <div class="col-lg-1">'
      + '    <label class="pull-right" for="depositDate">' + i18next.t("Type") + '</label>'
      + '  </div>'
      + '  <div class="col-lg-2">'
      + '    <select class= "form-control form-control-sm" id="Class" name="Class">'
      + '        <option value="p" ' + ((type == 'p' || type == -1) ? 'selected=""' : '') + '>' + i18next.t("Person") + '</option>'
      + '        <option value="f" ' + ((type == 'f') ? 'selected=""' : '') + '>' + i18next.t("Family") + '</option>'
      + '        <option value="g" ' + ((type == 'g') ? 'selected=""' : '') + '>' + i18next.t("Group") + '</option>'
      + '    </select>'
      + '  </div>'
      + '  <div class="col-lg-1">'
      + '    <label class="pull-right"  for="depositDate">' + i18next.t("Name") + '</label>'
      + '  </div>'
      + '  <div class="col-lg-3">'
      + '    <input class="form-control form-control-sm" name="Name" id="Name" style="width:100%">'
      + '  </div>'
      + '  <div class="col-lg-2">'
      + '    <label class="pull-right"  for="depositDate">' + i18next.t("Description") + '</label>'
      + '  </div>'
      + '  <div class="col-lg-3">'
      + '    <input class="form-control form-control-sm" name="description" id="Description" style="width:100%">'
      + '  </div>'
      + '</div>';

    return frm_str;
  }

  const loadTableEvents = () => {
    window.CRM.ElementListener('.delete-prop', 'click', function (event) {
      let typeId = event.currentTarget.dataset.typeid;
      let warn = event.currentTarget.dataset.warn;
      let message = i18next.t("You're about to delete this general properties. Would you like to continue ?");

      if (warn > 0) {
        message = '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle" aria-hidden="true"></i>' + i18next.t('This general property type is still being used by') + ' ' + warn + ' ' + ((warn == 1) ? i18next.t('property') : i18next.t('properties')) + '.<BR>' + i18next.t('If you delete this type, you will also remove all properties using') + '<BR>' + i18next.t('it and lose any corresponding property assignments.') + '</div>';
      }

      bootbox.confirm({
        title: i18next.t("Attention"),
        message: message,
        callback: function (result) {
          if (result) {
            window.CRM.APIRequest({
              method: 'POST',
              path: 'properties/propertytypelists/delete',
              data: JSON.stringify({ "typeId": typeId })
            }, function (data) {
              window.CRM.dataPropertyListTable.ajax.reload(function () {
                loadTableEvents();
              });
            });
          }
        }
      });
    });

    window.CRM.ElementListener('.edit-prop', 'click', function (event) {
      let typeId = event.currentTarget.dataset.typeid;

      window.CRM.APIRequest({
        method: 'POST',
        path: 'properties/propertytypelists/edit',
        data: JSON.stringify({ "typeId": typeId })
      }, function (data) {
        let modal = bootbox.dialog({
          message: BootboxContentPropertyTypeList(data.prtType.PrtClass),
          title: i18next.t("Property Type Editor"),
          size: "xl",
          buttons: [
            {
              label: i18next.t("Save"),
              className: "btn btn-primary pull-left",
              callback: function () {
                let Name = document.getElementById('Name').value;
                let Description = document.getElementById('Description').value;

                window.CRM.APIRequest({
                  method: 'POST',
                  path: 'properties/propertytypelists/set',
                  data: JSON.stringify({ "typeId": typeId, "Name": Name, "Description": Description })
                }, function (data) {
                  window.CRM.dataPropertyListTable.ajax.reload(function () {
                    loadTableEvents();
                  });
                });
              }
            },
            {
              label: i18next.t("Close"),
              className: "btn btn-default pull-left",
              callback: function () {
                console.log("just do something on close");
              }
            }
          ],
          show: false,
          onEscape: function () {
            modal.modal("hide");
          }
        });

        document.getElementById('Name').value = data.prtType.PrtName;
        document.getElementById('Description').value = data.prtType.PrtDescription;

        modal.modal("show");
      });
    });
  }

  window.CRM.ElementListener('#add-new-prop', 'click', function (event) {
    var modal = bootbox.dialog({
      message: BootboxContentPropertyTypeList(-1),
      title: i18next.t("Add a New Property Type"),
      size: "large",
      buttons: [
        {
          label: i18next.t("Save"),
          className: "btn btn-primary pull-left",
          callback: function () {
            let theClass = document.getElementById('Class').value;
            let Name = document.getElementById('Name').value;
            let Description = document.getElementById('Description').value;

            window.CRM.APIRequest({
              method: 'POST',
              path: 'properties/propertytypelists/create',
              data: JSON.stringify({ "Class": theClass, "Name": Name, "Description": Description })
            }, function (data) {
              window.CRM.dataPropertyListTable.ajax.reload(function () {
                loadTableEvents();
              });
            });
          }
        },
        {
          label: i18next.t("Close"),
          className: "btn btn-default pull-left",
          callback: function () {
            console.log("just do something on close");
          }
        }
      ],
      show: false,
      onEscape: function () {
        modal.modal("hide");
      }
    });

    modal.modal("show");
  });

});
