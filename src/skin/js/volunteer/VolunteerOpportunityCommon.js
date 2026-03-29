/* IMPORTANT : be careful
     This will work in cartToGroup code */
const BootboxContentVolunteerOpportunity = () => {
    var frm_str = `<div class="card-body">
      <div class="row mb-3">
        <div class="col-lg-2">
          <label><i class="fas fa-tag mr-1"></i>${i18next.t("Name")}</label>
        </div>
        <div class="col-lg-10">
          <input class="form-control form-control-sm" name="Name" id="Name" style="width:100%" placeholder="${i18next.t('Enter volunteer opportunity name')}">
        </div>
      </div>
      <div class="row mb-3">
        <div class="col-lg-2">
          <label><i class="fas fa-file-alt mr-1"></i>${i18next.t("Description")}</label>
        </div>
        <div class="col-lg-10">
          <input class="form-control form-control-sm" name="desc" id="desc" style="width:100%" placeholder="${i18next.t('Enter a brief description')}">
        </div>
      </div>
      <div class="row">
        <div class="col-lg-2">
          <input type="checkbox" id="activ" class="ibtn">
        </div>
        <div class="col-lg-10">
          <label for="activ"><i class="fas fa-check mr-1"></i>${i18next.t("Activ")}</label>
        </div>
      </div>
    </div>`;

    return frm_str
}