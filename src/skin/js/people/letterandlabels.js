$(function() {
    var type = 'person';

    $("#classList").select2();

    $( "#letterandlabelsnamingmethod" ).on( "change", function() {
        type = $('#letterandlabelsnamingmethod option:selected').val();        

        $('input#familiesId').val("");
        $('input#personsId').val("");
    });


    // select2 part
    $(".person-group-Id-Share").select2({
        language: window.CRM.shortLocale,
        minimumInputLength: 1,
        placeholder: " -- " + i18next.t("A person name or Family") + " -- ",
        allowClear: true, // This is for clear get the clear button if wanted
        ajax: {
            url: function (params) {
                return window.CRM.root + "/api/people/search/" + params.term;
            },
            dataType: 'json',
            delay: 50,
            data: "",
            processResults: function (data, params) {
                return {results: data};
            },
            cache: true
        }
    });


    $(".person-group-Id-Share").on("select2:select", function (e) {
        if (e.params.data.personID !== undefined) {            
            if (type == 'person') {
                var val = $('input#personsId').val();
                val = val + ',' + e.params.data.personID;
                $('input#personsId').val(val);
            }
            
        } else if (e.params.data.familyID !== undefined) {
            if (type == 'family') {
                var val = $('input#familiesId').val();
                val = val + ',' + e.params.data.familyID;
                $('input#familiesId').val(val);
            }
        }
    });

    var which;
    $("input").on('click', function () {
        which = $(this).attr("id");
    });

    $("#Myform").on('submit', function(e) {
        var name = e.originalEvent.submitter.name;
        $('input#realAction').val(name);        
        var currentForm = this;
        e.preventDefault();
        
        if (name == 'SubmitConfirmReportEmail') {
            bootbox.confirm({
                    title: i18next.t("Warning !!!!"),
                    size: "large",
                    message:i18next.t("You're about to send a massive e-mail to all EcclesiaCRM members :<br>- prefer a test with a few people or families<br>- make sure you select all of them, either by address or by person."),
                    animate:true,
                    callback: function(result) {
                        if (result) {
                            currentForm.submit();
                        }
                    }
            }).find('.modal-content').css({
                'background-color': '#f00', 
                'font-weight' : 'bold', 
                'color': '#000', 
                'font-size': '1em', 
                'font-weight' : 'bold'
            });
        } else {
            currentForm.submit();
        }
    });
});