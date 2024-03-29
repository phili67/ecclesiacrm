//
//  This code is under copyright not under MIT Licence
//  copyright   : 2019 Philippe Logel all right reserved not MIT licence
//  Updated     : 2019/07/03
//

  import {ImagePickerWindow} from './IconPicker.js';

  window.CRM.ElementListener('.checkOnlyPersonView', 'click', function (event) {
    let ID = event.currentTarget.dataset.id;
    let optionID = event.currentTarget.dataset.optionid;
    let isChecked = event.currentTarget.checked;

    window.CRM.APIRequest({
      method: 'POST',
      path: 'mapicons/checkOnlyPersonView',
      data: JSON.stringify({ "lstID": ID, "lstOptionID": optionID, "onlyPersonView": isChecked })
    }, function (data) {
      //window.location = window.location.href;
    });
  });

  window.CRM.ElementListener('.row-action', 'click', function (event) {
    let mode = event.currentTarget.dataset.mode;
    let Order = event.currentTarget.dataset.order;
    let ListID = event.currentTarget.dataset.listid;
    let ID = event.currentTarget.dataset.id;
    let Action = event.currentTarget.dataset.action;

    window.CRM.APIRequest({
      method: "POST",
      path: 'generalrole/action',               //call the groups api handler located at window.CRM.root
      data: JSON.stringify({ "mode": mode, "Order": Order, "ListID": ListID, "ID": ID, "Action": Action })                      // stringify the object we created earlier, and add it to the data payload
    }, function (data) {                               //yippie, we got something good back from the server
      window.location = window.location.href;
    });
  });

  window.CRM.ElementListener('.RemoveClassification', 'click', function (event) {
    let mode = event.currentTarget.dataset.mode;
    let Order = event.currentTarget.dataset.order;
    let ListID = event.currentTarget.dataset.listid;
    let ID = event.currentTarget.dataset.id;
    let name = event.currentTarget.dataset.name;

    bootbox.setDefaults({
      locale: window.CRM.shortLocale
    }),
      bootbox.confirm({
        title: i18next.t("Delete Classification"),
        message: '<p style="color: red">' +
          i18next.t("Please confirm deletion of this classification") + " : \"" + name + "\" ?</p>" +
          "<p style='color: red'><b>" +
          i18next.t("This will also delete this Classification for all the associated persons.") +
          "</b><br></p><p style='color: red'><b>" +
          i18next.t("This can't be undone !!!!") + "</b></p>",
        callback: function (result) {
          if (result) {
            window.CRM.APIRequest({
              method: "POST",
              path: 'generalrole/action',               //call the groups api handler located at window.CRM.root
              data: JSON.stringify({ "mode": mode, "Order": Order, "ListID": ListID, "ID": ID, "Action": "delete" })                      // stringify the object we created earlier, and add it to the data payload
            }, function (data) {                               //yippie, we got something good back from the server
              window.location = window.location.href;
            });
          }
        }
      });
  });


  window.CRM.ElementListener('.RemoveImage', 'click', function (event) {
    let lstID = event.currentTarget.dataset.id;
    let lstOptionID = event.currentTarget.dataset.optionid;

    window.CRM.APIRequest({
      method: 'POST',
      path: 'mapicons/removeIcon',
      data: JSON.stringify({ "lstID": lstID, "lstOptionID": lstOptionID })
    }, function (data) {
      window.location = window.location.href;
    });
  });


  window.CRM.ElementListener('.AddImage', 'click', function (event) {
    let lstID = event.currentTarget.dataset.id;
    let lstOptionID = event.currentTarget.dataset.optionid;
    let name = event.currentTarget.dataset.name;

    let diag = new ImagePickerWindow({
      title: i18next.t("Map Icon GoogleMap"),
      firstLabel: i18next.t("Classification"),
      label: name,
      message: i18next.t("Select your classification icon"),
      directory: window.CRM.root + '/skin/icons/markers/'
    },
      function (name) {
        window.CRM.APIRequest({
          method: 'POST',
          path: 'mapicons/setIconName',
          data: JSON.stringify({ "name": name, "lstID": lstID, "lstOptionID": lstOptionID })
        }, function (data) {
          window.location = window.location.href;
        });
      },
      function (directory) {
        window.CRM.APIRequest({
          method: 'POST',
          path: 'mapicons/getall',
        }, function (data) {
          let len = data.length;
          let table = document.getElementById('here_table');

          let res = '<table width=100%>';
          let buff = '';

          for (let i = 0; i < len; i++) {
            if (i % 8 == 0) {
              if (i == 0) {
                buff = '<tr>';
              } else {
                res += buff + '</tr>';
                buff = '<tr>';
              }
            }
            buff += '<td><img src="' + directory + data[i] + '" class="imgCollection" data-name="' + data[i] + '" style="border:solid 1px white"></td>';
          }

          if (buff != '') {
            len = len % 8;
            for (let i = 0; i < len; i++) {
              buff += '<td></td>';
            }
            res += buff + '</tr>';
          }

          table.innerHTML = res;
        })
      });

    diag.build();
    diag.show();
  });