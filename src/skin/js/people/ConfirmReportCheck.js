$(document).ready(function() {
    $('#monTableau').DataTable({
        paging: true,
        pageLength: 100,
        responsive: true,
        // On trie par la première colonne (la lettre) pour que le groupement fonctionne
        order: [[0, 'asc']], 
        "language": {
            "url": window.CRM.plugin.dataTable.language.url
        },
        // On configure le groupement de lignes
        rowGroup: {
            dataSrc: 0 // Index de la colonne contenant la première lettre
        },
        
        // Optionnel : masquer la première colonne puisqu'elle sert d'intertitre
        columnDefs: [
            { targets: [0], visible: false }
        ]
    });

    $('.custom-control-input, .custom-control-label').on('change', function() {
        const isChecked = $(this).is(':checked');
    
        if (!isChecked) {
            return; 
        }

        const $thisInput = $(this); // On stocke la référence jQuery de l'input
        const currentId = $thisInput.attr('id').replace('bCustomPeople', '');
        const newValue = '1'; // Puisqu'on ne gère désormais que le passage à "coché"
        const dateSelector = '.bCustomPeopleDate' + currentId;
        const messageSelector = '.bCustomPeopleMessage' + currentId;

        $thisInput.val(newValue);        

        window.CRM.APIRequest({
            method: "POST",
            path: "people/" + window.CRM.exportType + "/updateStatus",
            data: JSON.stringify({
                "ID": currentId,
                "Status": newValue
            })
        }, function (data) {
            if (data && data.Date) {
                $(dateSelector).text(data.Date);
            }
            if (data && data.Message) {
                $(messageSelector).text(data.Message);
                $(messageSelector).removeClass('text-red');
                $(messageSelector).addClass('text-green');
            }

            
            $thisInput.prop('disabled', true);
        });
    });

    $(document).on("click", "#qrcode-call", function () {
        var qrcode = new QRCodeScanner(function(code) {
            var res = code.split('-');
            var type = res[0];
            var currentId = res[1];

            const dateSelector = '.bCustomPeopleDate' + currentId;
            const messageSelector = '.bCustomPeopleMessage' + currentId;
            const selector = '#bCustomPeople' + currentId;

            window.CRM.APIRequest({
                method: "POST",
                path: "people/" + type + "/updateStatus",
                data: JSON.stringify({
                    "ID": currentId,
                    "Status": 1
                })
            }, function (data) {
                if (data && data.status == 'failed') {
                    alert(i18next.t('Failed') + " : " + i18next.t("No event right now.") + "\n\n" + "• "
                        + i18next.t("Move one in the right range.")
                        + "\n\n" + i18next.t("Or") + "\n\n" + "• "
                        + i18next.t("Create one.") + "\n\n" + i18next.t('Group')
                        + ' : ' + data.group + "\n" + i18next.t("User") + ' : ' + data.person);

                } else {
                    if (data && data.Date) {
                        $(dateSelector).text(data.Date);
                    }
                    if (data && data.Message) {
                        $(messageSelector).text(data.Message);
                        $(messageSelector).removeClass('text-red');
                        $(messageSelector).addClass('text-green');
                    }
                    $(selector).prop('disabled', true);
                    $(selector).prop('checked', true);
                }                                
            });                        
        });
        
        qrcode.setParameters({
            "width": 470,
            "height": 570,
            "fps": 10,
            "qrbox": 250
        })
        qrcode.show();
    });
});