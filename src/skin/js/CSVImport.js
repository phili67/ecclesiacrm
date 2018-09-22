
$('#clear-people').click(function (event) {
    event.preventDefault();
    var thisLink = $(this);
    bootbox.confirm({
        title:i18next.t( "Clear Persons and Families"),
        message: i18next.t("Warning! Do not select this option if you plan to add to an existing database.<br/>") + " <b>" + i18next.t("Use only if unsatisfied with initial import. All person and member data will be destroyed!")+'<br><br><span style="color:black">'+i18next.t("I Understand")+'&nbsp;<input type="checkbox" name="chkClear" id="chkClear"></span>',
        buttons: {
            cancel: {
                label: '<i class="fa fa-times"></i> ' + i18next.t("Cancel")
            },
            confirm: {
                label: '<i class="fa fa-trash"></i> ' + i18next.t("Clear Persons and Families"),
                className: 'btn-danger'
            }
        },
        callback: function (result) {
           var chkClear = $('input[name=chkClear]').prop('checked');
            if(result && chkClear==true) {
                window.CRM.APIRequest({
                    method: 'DELETE',
                    path: 'database/people/clear',
                }).done(function (data) {
                    window.CRM.DisplayAlert(i18next.t("Data Cleared Successfully!"), "success");
                    $('.import-users').hide();
                    $('#import-success').html('<br><br>&nbsp;&nbsp;&nbsp;'+i18next.t("Data Cleared Successfully!"));
                });
            }
        }
    });
});

$(document.body).on("change",".columns",function(){
  var theSelect2 = $(this);
  var name       = theSelect2.find('option:selected').text();
  var numCols    = theSelect2.data("numcol");
  var col        = theSelect2.data("col");
  var val        = theSelect2.val();
  
  for (i=0;i<numCols;i++) {
    if (i == col) continue;
    
    var theColVal = $('#col'+i).val();
    
    if (val == theColVal) {
      window.CRM.DisplayAlert(i18next.t('Duplicate values'),i18next.t("You've selected two times the same name") +" <b>\"" + name + "\"</b> " + i18next.t("for this field. Chose another one."));
      theSelect2.val(null).trigger('change');
    }
  }
});