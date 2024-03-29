//
//  This code is under copyright not under MIT Licence
//  copyright   : 2024 Philippe Logel all right reserved not MIT licence
//  Updated     : 2024/03/23
//

// usage : see OptionManager.js


  // this variables must be global for persistent datas
  var selectedName = '';
  var callbackRes = null;

  export class ImagePickerWindow {
    constructor(options, callbackRes, callBackIcons) {
      this.options = options;
      this.callbackRes = callbackRes;
      this.callBackIcons = callBackIcons;
      this.selectedName = '';
      this.diag = null;
    }

    BootboxContent(firstLabel, label, message) {
      var frm_str = '<div>'
        + '<div class="col-md-5"><label>' + firstLabel + ' : </label></div><div class="col-md-7">"' + label + '"</div></div>'
        + '</div>'
        + '<div class="row div-title">'
        + '<div class="col-md-12">' + message + '</div>'
        + '</div>'
        + '<div class="row">'
        + '<div class="col-md-12"><div id="here_table"></div></div>'
        + '</div>'
        + '</div>'

      return frm_str;
    }

    build() {
      // we have to get back the callback
      callbackRes = this.callbackRes;
      selectedName = this.selectedName;

      this.diag = bootbox.dialog({
        message: this.BootboxContent(this.options.firstLabel, this.options.label, this.options.message, this.options.directory),
        title: this.options.title,
        buttons: [
          {
            label: '<i class="fas fa-times"></i> ' + i18next.t("Cancel"),
            className: "btn btn-default",
          },
          {
            label: '<i class="fas fa-check"></i> ' + i18next.t("Validate"),
            className: "btn btn-primary",
            callback: function () {
              if (callbackRes) {
                return callbackRes(selectedName);
              }
            }
          }
        ],
        onEscape: function () {
        }
      });

      if (this.callBackIcons) {
        this.callBackIcons(this.options.directory);
      }

      this.diag.on('shown.bs.modal', function (base) {
        window.CRM.ElementListener('.imgCollection', 'click', function (event) {
          selectedName = event.currentTarget.dataset.name;

          document.querySelectorAll('.imgCollection').forEach(function (element, index) {
            element.style.border = "solid 1px white";
          });

          event.currentTarget.style.border = "solid 1px blue";
        });
      });
    }

    getModal() {
      return this.diag;
    }

    show() {
      this.diag.modal("show");
    }
  }