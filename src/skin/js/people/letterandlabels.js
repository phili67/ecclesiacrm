$(function() {
    var type = 'family';
    var users = '';

    $("#classList").select2();

    $( "#letterandlabelsnamingmethod" ).on( "change", function() {
        type = $('#letterandlabelsnamingmethod option:selected').val();        

        $('input#familiesId').val("");
        $('input#personsId').val("");
    });


    // select2 part
    $(".person-family-search").select2({
        language: window.CRM.shortLocale,
        minimumInputLength: 1,
        placeholder: " -- " + i18next.t("A person name or Family") + " -- ",
        allowClear: true, // This is for clear get the clear button if wanted
        ajax: {
            url: function (params) {
                return window.CRM.root + "/api/people/search/" + params.term + '/' + type;
            },
            headers: {
                "Authorization" : "Bearer "+window.CRM.jwtToken
            },
            dataType: 'json',
            delay: 50,
            data: JSON.stringify({"type": type}),
            processResults: function (data, params) {
                return {results: data};
            },
            cache: true
        }
    });


    $(".person-family-search").on("select2:select", function (e) {
        if (e.params.data.personID !== undefined) {            
            if (type == 'person') {
                var val = $('input#personsId').val();
                
                if (users == '') {
                    val = e.params.data.personID;
                    users = e.params.data.text;
                } else {
                    users = users + ',' + e.params.data.text;
                    val = val + ',' + e.params.data.personID;
                }
                $("#users").html(users);
                $('input#personsId').val(val);
            }
            
        } else if (e.params.data.familyID !== undefined) {
            if (type == 'family') {
                var val = $('input#familiesId').val();                
                if (users == '') {
                    users = e.params.data.name;
                    val = e.params.data.familyID;
                } else {
                    users = users + ',' + e.params.data.name;
                    val = val + ',' + e.params.data.familyID;
                }
                $("#users").html(users);
                $('input#familiesId').val(val);
            }
        }
    });

    $("#letterandlabelsnamingmethod").on ('change', function() {
        if ($(this).val() == 'person') {
            window.CRM.DisplayAlert(i18next.t("Modification"), i18next.t("By persons"));
        } else if ($(this).val() == 'family') {
            window.CRM.DisplayAlert(i18next.t("Modification"), i18next.t("By Adresses"));
        }
        $("#users").html(i18next.t("None"));
        users = '';
    });

    $("#remove-users").on ('click', function() {
        $("#users").html(i18next.t("None"));
        users = '';
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
        
        if (name == 'SubmitConfirmReportEmail' && users == '') {
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
                'background-color': '#f55', 
                'font-weight' : 'bold', 
                'color': '#000', 
                'font-size': '1em', 
                'font-weight' : 'bold'
            });
        } else if (name == 'SubmitConfirmReportEmail' && users != '') {
            bootbox.confirm({
                    title: i18next.t("Warning !!!!"),
                    size: "large",
                    message:i18next.t("You're about to send a e-mail to") + " " +  users + " "  + i18next.t("members")+ ".",
                    animate:true,
                    callback: function(result) {
                        if (result) {
                            currentForm.submit();
                        }
                    }
            }).find('.modal-content').css({
                'background-color': '#f55', 
                'font-weight' : 'bold', 
                'color': '#000', 
                'font-size': '1em', 
                'font-weight' : 'bold'
            });
        } else {
            currentForm.submit();
        }
    });

    $("#delete-pending-persons").on ('click', function() {
        bootbox.confirm({
            title: i18next.t("Warning !!!!"),
            size: "large",
            message:i18next.t("You're about to delete all pending confirmation for all the persons."),
            callback: function(result) {
                if (result) {
                    window.CRM.APIRequest({
                        method: 'POST',
                        path: 'persons/reset/pending'
                      },function(data) {
                        // reset count to 0.
                        $("#pending-count-persons").html("0");
                      });
                }
            }
            }).find('.modal-content').css({
                'background-color': '#f55', 
                'font-weight' : 'bold', 
                'color': '#000', 
                'font-size': '1em', 
                'font-weight' : 'bold'
            });
    });

    $("#delete-done-confirmation-persons").on ('click', function() {
        bootbox.confirm({
            title: i18next.t("Warning !!!!"),
            size: "large",
            message:i18next.t("You're about to delete all done confirmation for all the persons."),
            callback: function(result) {
                if (result) {
                    window.CRM.APIRequest({
                        method: 'POST',
                        path: 'persons/reset/done'
                      },function(data) {
                        $("#done-count-persons").html("0");
                      });
                }
            }
        }).find('.modal-content').css({
                'background-color': '#f55', 
                'font-weight' : 'bold', 
                'color': '#000', 
                'font-size': '1em', 
                'font-weight' : 'bold'
        });
    });

    $("#delete-pending-families").on ('click', function() {
        bootbox.confirm({
            title: i18next.t("Warning !!!!"),
            size: "large",
            message:i18next.t("You're about to delete all pending confirmation for all the families."),
            callback: function(result) {
                if (result) {
                    window.CRM.APIRequest({
                        method: 'POST',
                        path: 'families/reset/pending'
                      },function(data) {
                        // reset count to 0.
                        $("#pending-count-families").html("0");
                      });
                }
            }
            }).find('.modal-content').css({
                'background-color': '#f55', 
                'font-weight' : 'bold', 
                'color': '#000', 
                'font-size': '1em', 
                'font-weight' : 'bold'
            });
    });

    $("#delete-done-confirmation-families").on ('click', function() {
        bootbox.confirm({
            title: i18next.t("Warning !!!!"),
            size: "large",
            message:i18next.t("You're about to delete all done confirmation for all the families."),
            callback: function(result) {
                if (result) {
                    window.CRM.APIRequest({
                        method: 'POST',
                        path: 'families/reset/done'
                      },function(data) {
                        $("#done-count-familie").html("0");
                      });
                }
            }
        }).find('.modal-content').css({
                'background-color': '#f55', 
                'font-weight' : 'bold', 
                'color': '#000', 
                'font-size': '1em', 
                'font-weight' : 'bold'
        });
    });

});