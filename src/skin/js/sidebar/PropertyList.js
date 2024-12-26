/* Copyright 2024 : Philippe Logel */

window.CRM.ElementListener('#add-new-prop', 'click', function (event) {
  var modal = bootbox.dialog({
    message: BootboxContentPropertyList(window.CRM.propertyTypesAll),
    size: 'large',
    title: i18next.t("Add a New Property"),
    buttons: [
      {
        label: i18next.t("Save"),
        className: "btn btn-primary pull-left",
        callback: function () {
          var theClass = window.CRM.propertyType;
          var Name = document.getElementById('Name').value;
          var Description = document.getElementById('description').value;
          var Prompt = document.getElementById('prompt').value;

          window.CRM.APIRequest({
            method: 'POST',
            path: 'properties/typelists/create',
            data: JSON.stringify({ "Class": theClass, "Name": Name, "Description": Description, "Prompt": Prompt })
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


/* IMPORTANT : be careful
  This will work in cartToGroup code */
const  BootboxContentPropertyList = (propertyTypes) => {
  let options = '<option value="">' + i18next.t('Select Property Type') + '</option>';
  let firstTime = true;

  for (let i = 0; i < propertyTypes.length; i++) {
    options += "\n" + '<option value="' + propertyTypes[i].PrtId + '" ' + ((firstTime) ? 'selected=""' : '') + '>' + propertyTypes[i].PrtName + '</option>';
    firstTime = false;
  }

  let frm_str = '<div class="card-body">'
    + '<div class="row">'
    + '  <div class="col-lg-4">'
    + '    <label for="depositDate">' + i18next.t("Type") + '</label>'
    + '  </div>'
    + '  <div class="col-lg-8">'
    + '    <select class= "form-control form-control-sm" id="Class" name="Class">'
    + options
    + '    </select>'
    + '  </div>'
    + '</div>'
    + '<div class="row">'
    + '  <div class="col-lg-4">'
    + '    <label for="depositDate">' + i18next.t("Name") + '</label>'
    + '  </div>'
    + '  <div class="col-lg-8">'
    + '    <input class="form-control form-control-sm" name="Name" id="Name" style="width:100%">'
    + '  </div>'
    + '</div>'
    + '<div class="row">'
    + '  <div class="col-lg-4">'
    + '    <label for="depositDate">' + window.CRM.propertyTypeName + ' : ' + i18next.t("with this property...") + '</label>'
    + '  </div>'
    + '  <div class="col-lg-8">'
    + '    <input class="form-control form-control-sm" name="description" id="description" style="width:100%">'
    + '  </div>'
    + '</div>'
    + '<div class="row">'
    + '  <div class="col-lg-4">'
    + '    <label for="depositDate">' + i18next.t("Prompt") + '</label>'
    + '  </div>'
    + '  <div class="col-lg-8">'
    + '    <input class="form-control form-control-sm" name="prompt" id="prompt" style="width:100%">'
    + '  </div>'
    + '</div>'
    + '</div>';

  return frm_str
}

const loadTableEvents = () => {
  window.CRM.ElementListener('.delete-prop', 'click', function (event) {
    let typeId = event.currentTarget.dataset.typeid;
    var message = i18next.t("You're about to delete this property. Would you like to continue ?");

    bootbox.confirm({
      title: i18next.t("Attention"),
      message: message,
      callback: function (result) {
        if (result) {
          window.CRM.APIRequest({
            method: 'POST',
            path: 'properties/typelists/delete',
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
      path: 'properties/typelists/edit',
      data: JSON.stringify({ "typeId": typeId })
    }, function (data) {
      var modal = bootbox.dialog({
        message: BootboxContentPropertyList(data.propertyTypes),
        size: "large",
        title: i18next.t("Property Type Editor"),
        buttons: [
          {
            label: i18next.t("Save"),
            className: "btn btn-primary pull-left",
            callback: function () {
              let Name = document.getElementById('Name').value;
              let Description = document.getElementById('description').value;
              let Prompt = document.getElementById('prompt').value;

              window.CRM.APIRequest({
                method: 'POST',
                path: 'properties/typelists/set',
                data: JSON.stringify({ "typeId": typeId, "Name": Name, "Description": Description, "Prompt": Prompt })
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

      document.getElementById('Name').value = data.proType.ProName;
      document.getElementById('description').value = data.proType.ProDescription;
      document.getElementById('prompt').value = data.proType.ProPrompt;

      modal.modal("show");
    });
  });
}


window.CRM.dataPropertyListTable = new DataTable("#property-listing-table-v2", {
  ajax: {
    url: window.CRM.root + "/api/properties/typelists/" + window.CRM.propertyType,
    type: 'POST',
    contentType: "application/json",
    dataSrc: "PropertyLists",
    "beforeSend": function (xhr) {
      xhr.setRequestHeader('Authorization',
        "Bearer " + window.CRM.jwtToken
      );
    }
  },
  drawCallback: function (settings) {
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
      data: 'ProId',
      render: function (data, type, full, meta) {
        if (window.CRM.menuOptionEnabled == false)
          return '';

        var res = '<a href="#" data-typeid="' + full.ProId + '" class="edit-prop"><i class="fas fa-pencil-alt" aria-hidden="true"></i></a>';
        res += '&nbsp;&nbsp;&nbsp;<a href="#" data-typeid="' + full.ProId + '" class="delete-prop"><i class="far fa-trash-alt" aria-hidden="true" style="color:red"></i></a>';

        return res;
      }
    },
    {
      width: 'auto',
      title: i18next.t('Name'),
      data: 'ProName',
      render: function (data, type, full, meta) {
        return data;
      }
    },
    {
      width: 'auto',
      title: window.CRM.propertyTypeName + ' : ' + i18next.t('with this Property...'),
      data: 'ProDescription',
      render: function (data, type, full, meta) {
        return data;
      }
    },
    {
      width: 'auto',
      title: i18next.t('Prompt'),
      data: 'ProPrompt',
      render: function (data, type, full, meta) {
        return data;
      }
    }
  ],
  responsive: true
});
