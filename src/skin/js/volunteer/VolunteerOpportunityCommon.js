/* IMPORTANT : be careful
     This will work in cartToGroup code */
const BootboxContentVolunteerOpportunity = () => {
    var frm_str = '<div class="card-body">'
        + '<div class="row">'
        + '  <div class="col-lg-2">'
        + '    <label>' + i18next.t("Name") + '</label>'
        + '  </div>'
        + '  <div class="col-lg-10">'
        + '    <input class="form-control form-control-sm" name="Name" id="Name" style="width:100%">'
        + '  </div>'
        + '</div>'
        + '<div class="row">'
        + '  <div class="col-lg-2">'
        + '    <label>' + i18next.t("Description") + '</label>'
        + '  </div>'
        + '  <div class="col-lg-10">'
        + '    <input class="form-control form-control-sm" name="desc" id="desc" style="width:100%">'
        + '  </div>'
        + '</div>'
        + '<div class="row">'
        + '  <div class="col-lg-2">'
        + '<input type="checkbox"  id="activ" class="ibtn">'
        + '  </div>'
        + '  <div class="col-lg-10">'
        + '    <label for="depositComment">' + i18next.t("Activ") + '</label>'
        + '  </div>'
        + '</div>'
        + '</div>';

    return frm_str
}