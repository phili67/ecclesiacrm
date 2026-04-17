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
      const safeFirstLabel = firstLabel || '';
      const safeLabel = label || '';
      const safeMessage = message || '';

      return `
        <div class="container-fluid px-0 icon-picker-content">
          <div class="row align-items-center mb-3">
            <div class="col-md-4 font-weight-bold text-muted">
              <i class="fas fa-tag text-primary mr-2"></i>${safeFirstLabel}
            </div>
            <div class="col-md-8">
              <div class="p-2 rounded border bg-light">
                <i class="fas fa-icons text-warning mr-2"></i>
                <span class="text-dark">${safeLabel}</span>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-12">
              <div class="border rounded bg-white p-2 d-flex justify-content-center text-center" id="icon_table" aria-live="polite"></div>
              <div class="text-muted small text-center mt-2">
                  <i class="fas fa-images mr-2"></i>${safeMessage}
              </div>
            </div>
          </div>
        </div>`;
    }

    build() {
      // we have to get back the callback
      callbackRes = this.callbackRes;
      selectedName = this.selectedName;

      this.diag = bootbox.dialog({
        message: this.BootboxContent(this.options.firstLabel, this.options.label, this.options.message, this.options.directory),
        title: '<i class="fas fa-map-marker-alt text-danger mr-2"></i> ' + this.options.title,
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