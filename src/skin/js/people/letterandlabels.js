$(function() {
    var type = 'family';
    var users = '';

    $("#classList").select2();

    $( "#letterandlabelsnamingmethod" ).on( "change", function() {
        type = $('#letterandlabelsnamingmethod option:selected').val();        

        $(".person-family-search").val(null).trigger('change');

        $('input#familiesId').val("");
        $('input#personsId').val("");
    });

    document.getElementById('LettersAndLabelsForm').addEventListener('submit', function(e) {
        e.preventDefault();

        var formData = new FormData(this, e.submitter); // Permet de récupérer les données du formulaire, y compris le bouton qui a déclenché la soumission

        const dataObject = Object.fromEntries(formData.entries());
        
        if (dataObject.realAction === 'SubmitConfirmReportCheck') {
            var postForm = document.createElement('form');
            postForm.method = 'POST';
            postForm.action = window.CRM.root + "/v2/people/confirmReportCheck";

            formData.forEach(function(value, key) {
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = value;
                postForm.appendChild(input);
            });

            document.body.appendChild(postForm);
            postForm.submit();
            return;
        } else if (dataObject.realAction === 'SubmitConfirmReportEmail' && users == '') {
            var postForm = document.createElement('form');
            postForm.method = 'POST';
            postForm.action = window.CRM.root + "/Reports/ConfirmReportEmail.php";

            formData.forEach(function(value, key) {
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = value;
                postForm.appendChild(input);
            });          
            
            document.body.appendChild(postForm);

            bootbox.confirm({
                title: i18next.t("Warning !!!!"),
                size: "large",
                message:i18next.t("You're about to send a massive e-mail to all EcclesiaCRM members :<br>- prefer a test with a few people or families<br>- make sure you select all of them, either by address or by person."),
                animate:true,
                callback: function(result) {
                    if (result) {
                        postForm.submit();
                    }
                }
            }).find('.modal-content').css({
                'background-color': '#f55', 
                'font-weight' : 'bold', 
                'color': '#000', 
                'font-size': '1em', 
                'font-weight' : 'bold'
            });
            return;
        } else if (dataObject.realAction === 'SubmitConfirmReportEmail' && users != '') {
            var postForm = document.createElement('form');
            postForm.method = 'POST';
            postForm.action = window.CRM.root + "/Reports/ConfirmReportEmail.php";

            formData.forEach(function(value, key) {
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = value;
                postForm.appendChild(input);
            });

            document.body.appendChild(postForm);

            bootbox.confirm({
                title: i18next.t("Warning !!!!"),
                size: "large",
                message:i18next.t("You're about to send a e-mail to") + " " +  users + " "  + i18next.t("members")+ ".",
                animate:true,
                callback: function(result) {
                    if (result) {
                        postForm.submit();
                    }
                }
            }).find('.modal-content').css({
                'background-color': '#f55', 
                'font-weight' : 'bold', 
                'color': '#000', 
                'font-size': '1em', 
                'font-weight' : 'bold'
            });
            return;
        }
        

        var donneesFormulaire = new URLSearchParams(formData);

        window.CRM.dialogLoadingFunction(i18next.t("Please wait while the PDF is generated and downloaded."), function () {
            var urlCible = window.CRM.root + "/v2/people/LettersAndLabels";

            fetch(urlCible, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'Authorization': 'Bearer ' + window.CRM.jwtToken,
                },
                body: donneesFormulaire
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error("Erreur lors de la génération : " + response.status);
                }
                // L'ASTUCE : On demande à Fetch de traiter la réponse comme un fichier binaire (Blob)
                return response.blob(); 
            })
            .then(blob => {
                // 4. On ferme la Bootbox puisque le fichier est entièrement reçu !
                
                // 5. Création d'un lien invisible pour déclencher le téléchargement du PDF
                var urlPdf = window.URL.createObjectURL(blob);
                var lienInvisible = document.createElement('a');
                lienInvisible.href = urlPdf;
                
                // Vous pouvez nommer le fichier par défaut si le PHP n'impose pas le sien en mode 'D'
                lienInvisible.download = "document.pdf"; 
                
                document.body.appendChild(lienInvisible);
                lienInvisible.click(); // Simule le clic de téléchargement

                window.CRM.closeDialogLoadingFunction();

                
                // Nettoyage de la mémoire
                document.body.removeChild(lienInvisible);
                window.URL.revokeObjectURL(urlPdf);
            })
            .catch(error => {
                // En cas de problème, on ferme la Bootbox et on prévient
                window.CRM.closeDialogLoadingFunction();

                bootbox.alert("Impossible de générer le PDF.");
                console.error(error);
            }); 
        });            
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
        $('input#familiesId').val("");
        $('input#personsId').val("");

        
        $(".person-family-search").val(null).trigger('change');

        if ($(this).val() == 'person') {
            window.CRM.DisplayAlert(i18next.t("Modification"), i18next.t("By persons"));
        } else if ($(this).val() == 'family') {
            window.CRM.DisplayAlert(i18next.t("Modification"), i18next.t("By Adresses"));
        }
        $("#users").html(i18next.t("None"));
        users = '';
    });

    $("#remove-users").on ('click', function() {
        $(".person-family-search").val(null).trigger('change');
        $("#users").html(i18next.t("None"));
        users = '';
    });

    var which;
    $("input").on('click', function () {
        which = $(this).attr("id");
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

    $('input[name="options"]').on('change', function() {
        // Récupère l'ID de l'élément coché
        let selectedId = $(this).attr('id'); 
        switch (selectedId) {
            case 'Labels':
                $('#reports-options-labels').removeClass('hide');
                $('#reports-options-more').addClass('hide');
                break;
            case 'ConfirmDataLetter':
                $('#reports-options-labels').addClass('hide');
                $('#reports-options-more').removeClass('hide');
                $('#qrCodeOption').removeClass('hide');                
                $('#SubmitConfirmReport').removeClass('hide');
                $('#SubmitConfirmReportCheck').addClass('hide');
                $('#SubmitConfirmReportEmail').addClass('hide');
                $('#reports-options-more-right').addClass('hide');

                $('#reports-options-more-left').removeClass('col-lg-8');                
                $('#reports-options-more-left').addClass('col-lg-12');
                break;
            case 'ConfirmDataInPerson':
                $('#reports-options-labels').addClass('hide');
                $('#reports-options-more').removeClass('hide');
                $('#qrCodeOption').addClass('hide');
                $('#SubmitConfirmReport').addClass('hide');
                $('#SubmitConfirmReportCheck').removeClass('hide');
                $('#SubmitConfirmReportEmail').addClass('hide');
                $('#reports-options-more-right').removeClass('hide');

                $('#reports-options-more-left').removeClass('col-lg-12');
                $('#reports-options-more-left').addClass('col-lg-8');
                break;
            case 'ConfirmDataEmail':
                $('#reports-options-labels').addClass('hide');
                $('#reports-options-more').removeClass('hide');
                $('#qrCodeOption').addClass('hide');
                $('#SubmitConfirmReport').addClass('hide');
                $('#SubmitConfirmReportCheck').addClass('hide');
                $('#SubmitConfirmReportEmail').removeClass('hide');
                $('#reports-options-more-right').removeClass('hide');

                $('#reports-options-more-left').removeClass('col-lg-12');
                $('#reports-options-more-left').addClass('col-lg-8');
                break;
            default:
                window.CRM.DisplayAlert(i18next.t("Modification"), i18next.t("None"));
        }       
    });

});