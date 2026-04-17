window.CRM.ElementListener('#add-new-pastoral-care', 'click', function (event) {
  var modal = bootbox.dialog({
    message: BootboxContentPastoralCareTypeList(),
    title: i18next.t("Add Pastoral Care Type"),
    size: 'large',
    buttons: [
      {
        label: i18next.t("Save"),
        className: "btn btn-primary pull-left",
        callback: function () {
          let Visible = document.getElementById('visibleCheckbox').checked;
          let Title = document.getElementById('Title').value;
          let Description = document.getElementById('description').value;

          window.CRM.APIRequest({
            method: 'POST',
            path: 'pastoralcare/createtype',
            data: JSON.stringify({ "Visible": Visible, "Title": Title, "Description": Description })
          }, function (data) {
            window.CRM.dataPastoralCareTypeTable.ajax.reload(function() {
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
const BootboxContentPastoralCareTypeList = () => {
  return `
    <div class="card-body">
      <div class="row mb-3">
        <div class="col-lg-2">
          <label>
            <i class="fas fa-heading"></i> ${i18next.t("Title")}
          </label>
        </div>
        <div class="col-lg-10">
          <input class="form-control form-control-sm" name="Title" id="Title" style="width:100%">
        </div>
      </div>

      <div class="row mb-3">
        <div class="col-lg-2">
          <label>
            <i class="fas fa-info-circle"></i> ${i18next.t("Description")}
          </label>
        </div>
        <div class="col-lg-10">
          <input class="form-control form-control-sm" name="description" id="description" style="width:100%">
        </div>
      </div>

      <div class="row mb-3">
        <div class="col-lg-2">
        </div>
        <div class="col-lg-10">
          <input id="visibleCheckbox" type="checkbox" name="visible" checked="checked">
          <label for="visibleCheckbox">
            <i class="fas fa-eye"></i> ${i18next.t("Visible for other authorized users")}
          </label>
        </div>
      </div>
    </div>
  `;
}

const loadTableEvents = () => {
  window.CRM.ElementListener('.delete-pastoral-care', 'click', function (event) {
    let pastoralCareTypeId = event.currentTarget.dataset.id;

    bootbox.confirm({
      title: i18next.t("Attention"),
      message: i18next.t("If you delete the pastoral care type, <u><b>you'll lose all the connected datas.</b></u><br><b>Are you sure? This action can't be undone.</b>"),
      callback: function (result) {
        if (result) {
          window.CRM.APIRequest({
            method: 'POST',
            path: 'pastoralcare/deletetype',
            data: JSON.stringify({ "pastoralCareTypeId": pastoralCareTypeId })
          }, function (data) {
            window.CRM.dataPastoralCareTypeTable.ajax.reload(function() {
              loadTableEvents();
            });
          });
        }
      }
    });
  });

  window.CRM.ElementListener('.edit-pastoral-care', 'click', function (event) {
    let pastoralCareTypeId = event.currentTarget.dataset.id;

    window.CRM.APIRequest({
      method: 'POST',
      path: 'pastoralcare/edittype',
      data: JSON.stringify({ "pastoralCareTypeId": pastoralCareTypeId })
    }, function (data) {
      var modal = bootbox.dialog({
        message: BootboxContentPastoralCareTypeList,
        title: i18next.t("Pastoral Care Type Editor"),
        size: 'large',
        buttons: [
          {
            label: i18next.t("Save"),
            className: "btn btn-primary pull-left",
            callback: function () {
              let Visible = document.getElementById('visibleCheckbox').checked;
              let Title = document.getElementById('Title').value;
              let Description = document.getElementById('description').value;

              window.CRM.APIRequest({
                method: 'POST',
                path: 'pastoralcare/settype',
                data: JSON.stringify({ "pastoralCareTypeId": pastoralCareTypeId, "Visible": Visible, "Title": Title, "Description": Description })
              }, function (data) {
                window.CRM.dataPastoralCareTypeTable.ajax.reload(function() {
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

      document.getElementById('visibleCheckbox').checked = data.Visible;
      document.getElementById('Title').value = data.Title;
      document.getElementById('description').value = data.Desc;
      
      modal.modal("show");
    });
  });
}

window.CRM.dataPastoralCareTypeTable = new DataTable("#pastoral-careTable", {
  ajax: {
    url: window.CRM.root + "/api/pastoralcare/",
    type: 'POST',
    contentType: "application/json",
    dataSrc: "PastoralCareTypes",
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
  columns: [
    {
      width: 'auto',
      title: i18next.t('ID'),
      data: 'Id',
      render: function (data, type, full, meta) {
        return data;
      }
    },
    {
      width: 'auto',
      title: i18next.t('Actions'),
      data: 'Id',
      render: function (data, type, full, meta) {
        return `
          <div class="btn-group" role="group">
            <button type="button" class="btn btn-outline-primary btn-sm edit-pastoral-care" data-id="${data}" title="${i18next.t('Edit')}">
              <i class="fas fa-pen"></i>
            </button>
            <button type="button" class="btn btn-outline-danger btn-sm delete-pastoral-care" data-id="${data}" title="${i18next.t('Delete')}">
              <i class="fa fa-trash-can"></i>
            </button>
          </div>
        `;
      }
    },
    {
      width: 'auto',
      title: i18next.t('Title'),
      data: 'Title',
      render: function (data, type, full, meta) {
        return data;
      }
    },
    {
      width: 'auto',
      title: i18next.t('Description'),
      data: 'Desc',
      render: function (data, type, full, meta) {
        return data;
      }
    },
    {
      width: 'auto',
      title: i18next.t('Visible'),
      data: 'Visible',
      render: function (data, type, full, meta) {
        return (data == true) ? '<span style="color:green"><i class="fa-solid fa-check"></i></span>' : '<span style="color:red"><i class="fas fa-ban"></i></span>';
      }
    }
  ],
  responsive: true
});