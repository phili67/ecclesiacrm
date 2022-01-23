function setGroupsIdsAttendees(selectControl)
{
  var res = '';

  $('#GroupID :selected').each(function(){
     //selected[$(this).val()]=$(this).text();
     res = $(selectControl).val()+',';
  });

  res = res.slice(0, -1);

  $('#exportCheckOutPDF').attr( 'data-makecheckoutgroupid', res )
}

$( "#GroupID" ).click(function() {
  setGroupsIdsAttendees(this);
});

$( "#GroupID" ).change(function() {
  setGroupsIdsAttendees(this);
});

    function BootboxContentPDF(start,end)
    {
      var time_format;
      var fmt = window.CRM.datePickerformat.toUpperCase();

      var dateStart = moment(start).format(fmt);
      var dateEnd = moment(end).format(fmt);


      var frm_str = '<b><p>'+i18next.t("First, set your time range correctly to make the extraction.")+'</p></b><hr/><form id="some-form">'
          +'<div class="row">'
              +'<div class="col-md-12">'
                  +'<div class="row">'
                    +'<div class="col-md-3"><span style="color: red">*</span>'
                       + i18next.t('Start Date')+' :'
                    +'</div>'
                    +'<div class="input-group col-md-3">'
                    +'  <div class="input-group-prepend">'
                    +'     <span class="input-group-text"><i class="fas fa-calendar"></i></span>'
                    +'  </div>'
                    +'  <input class="form-control date-picker input-sm" type="text" id="dateEventStart" name="dateEventStart"  value="'+dateStart+'" '
                            +'maxlength="10" id="sel1" size="11"'
                            +'placeholder="'+window.CRM.datePickerformat+'">'
                    +'</div>'
                    +'<div class="col-md-3"><span style="color: red">*</span>'
                      + i18next.t('End Date')+' :'
                    +'</div>'
                      +'<div class="input-group col-md-3">'
                      +'  <div class="input-group-prepend">'
                      +'     <span class="input-group-text"><i class="fas fa-calendar"></i></span>'
                      +'  </div>'
                      +'<input class="form-control date-picker input-sm" type="text" id="dateEventEnd" name="dateEventEnd"  value="'+dateEnd+'" '
                      +'maxlength="10" id="sel1" size="11"'
                      +'placeholder="'+window.CRM.datePickerformat+'">'
                     +'</div>'
                  +'</div>'
                +'</div>'
            +'</div>'
            +'<br>'
            +'<div class="row">'
              +'<div class="col-md-3"><span style="color: red">*</span>'
                + i18next.t('Extra students')+' :'
              +'</div>'
              +'<div class="col-md-3">'
                +'<input id="ExtraStudents"  class="ExtraStudents form-control input-sm" type="text" name="ExtraStudents" value="0" maxlength="10" size="11">'
              +'</div>'
              +'<div class="col-md-6">'
                +'<input id="withPictures" type="checkbox" checked> '+ i18next.t('export with photos')
              +'</div>'
              +'</div>'
            +'</div>'
            +'<div class="row">'
            +'  <div class="col-md-6">'
            +'     <input type="radio" id="pdf" name="exporttype" value="pdf" checked>'
            +'     <label for="pdf">PDF</label>'
            +'  </div>'
            +'  <div class="col-md-6">'
            +'     <input type="radio" id="huey" name="exporttype" value="csv">'
            +'     <label for="csv">CSV</label>'
            +'  </div>'
            +'</div>'
         + '</form>';

      var object = $('<div/>').html(frm_str).contents();

      return object
    }


    $(document).on("click",".exportCheckOutPDF", function(){
        $("#GroupID").each(function () {
            var groupID = $(this).val();
            if (groupID.length == 0) {
                window.CRM.DisplayAlert(i18next.t('Attention'), i18next.t('At least one group must be selected to make class lists or attendance sheets.'));
            } else {
                var start=moment().subtract(1, 'years').format('YYYY-MM-DD');
                var end=moment().format('YYYY-MM-DD');

                var modal = bootbox.dialog({
                    title: i18next.t("Set year range to export"),
                    message: BootboxContentPDF(start,end),
                    size: "extra-large",
                    buttons: [
                        {
                            label: '<i class="fas fa-times"> ' + i18next.t("Cancel"),
                            className: "btn btn-default",
                            callback: function() {
                                console.log("just do something on close");
                            }
                        },
                        {
                            label: '<i class="fas fa-check"> ' + i18next.t('OK'),
                            className: "btn btn-primary",
                            callback: function() {
                                var dateStart = $('form #dateEventStart').val();
                                var dateEnd = $('form #dateEventEnd').val();

                                var fmt = window.CRM.datePickerformat.toUpperCase();

                                var real_start = moment(dateStart,fmt).format('YYYY-MM-DD');
                                var real_end = moment(dateEnd,fmt).format('YYYY-MM-DD');

                                var withPictures = ($("form #withPictures").is(':checked') == true)?1:0;
                                var ExtraStudents = $("form .ExtraStudents").val();

                                var exportTypePDF   = $("form #pdf").is(":checked");

                                $("#GroupID").each(function(){
                                    var groupID = $(this).val();
                                    window.location = window.CRM.root + "/Reports/ClassRealAttendance.php?groupID="+groupID+"&start="+real_start+"&end="+real_end+"&withPictures="+withPictures+"&ExtraStudents="+ExtraStudents+"&exportTypePDF="+((exportTypePDF)?1:0)
                                });
                            }
                        }
                    ],
                    show: false/*,
         onEscape: function() {
            modal.modal("hide");
         }*/
                });



                modal.modal("show");

                $('.date-picker').datepicker({format:window.CRM.datePickerformat, language: window.CRM.lang});
            }
        });
    });
