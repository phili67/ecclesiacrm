$(document).ready(function () {

    $('body').on('click', ".pnDelete", function () {
        var pnID = $(this).data("pnid");

        window.CRM.APIRequest({
            method: "DELETE",
            path: "fundraiser/paddlenum",
            data: JSON.stringify({"fundraiserID": window.CRM.fundraiserID, "pnID": pnID})
        }).done(function (data) {
            if (data.status == "success") {
                window.CRM.paddleNumListTable.ajax.reload();
            }
        });
    });

    function addPersonsToSelectList(iPerID) {

        if (iPerID === undefined) {
            iPerID = false;
        }

        window.CRM.APIRequest({
            method: 'GET',
            path: 'fundraiser/paddlenum/persons/all/' + window.CRM.fundraiserID,
        }).done(function (data) {
            var elt = document.getElementById("Buyers");
            var persons = data.persons;
            var len = persons.length;

            $("#Number").val(data.Number);

            var option = document.createElement("option");
            option.text = i18next.t ('Unassigned');
            option.value = 0;
            elt.appendChild(option);

            for (i = 0; i < len; ++i) {
                var option = document.createElement("option");
                option.text = persons[i].LastName + ", " + persons[i].FirstName + " - " + persons[i].Address1;
                option.value = persons[i].Id;

                /*if (iPerID && iPerID === persons[i].Id) {
                    option.setAttribute('selected', 'selected');
                }*/

                elt.appendChild(option);
            }
            $("#Buyers").select2();

            window.CRM.addbuyerModal.modal("show");
        });
    }

    function BootboxContent( windowtitle, number ) {
        var frm_str = '<h3 style="margin-top:-5px">' + i18next.t ('Buyer Number Editor') + '</h3><div id="some-form">'
            + '<div>'
            + '  <div class="row  div-title BuyerTitle">'
            + '      <div class="col-md-3"><label>' + i18next.t('Add buyer') + "</label></div>"
            + '  </div>'
            + '  <div class="row NumberTitle">'
            + '      <div class="col-md-3">' + i18next.t('Number') + ":</div>"
            + '      <div class="col-md-9">'
            + "         <input type='text' id='Number' placeholder='" + i18next.t("Buyer Number") + "' size='30' maxlength='100' class='form-control input-sm'  width='100%' style='width: 100%' required " + ((windowtitle != undefined) ? ("value='" + number + "'") : "") + ">"
            + '      </div>'
            + '  </div><br>'
            + '  <div class="row  buyer-list-title">'
            + '      <div class="col-md-3">' + i18next.t('Buyer') + ":</div>"
            + '      <div class="col-md-9">'
            + '          <select name="PerID" class="form-control select2  input-sm" id="Buyers" style="width: 100%">></select>'
            + '      </div>'
            + '  </div>'
            + '</div>';

        var object = $('<div/>').html(frm_str).contents();

        return object
    }


    function createBuyerEditorWindow(number, windowtitle, iPerdId) // dialogType : createEvent or modifyEvent, eventID is when you modify and event
    {
        windowtitle = i18next.t ('Buyer Number Editor');

        window.CRM.addbuyerModal = bootbox.dialog({
            message: BootboxContent(windowtitle, number),
            size: 'large',
            buttons: [
                {
                    label: '<i class="fa fa-times"></i> ' + i18next.t("Close"),
                    className: "btn btn-default",
                    callback: function () {
                        console.log("just do something on close");
                    }
                },
                {
                    label: '<i class="fa fa-check"></i> ' + i18next.t("Ok"),
                    className: "btn btn-primary",
                    callback: function () {

                    }
                }
            ],
            show: false/*,
         onEscape: function() {
            modal.modal("hide");
         }*/
        });

        addPersonsToSelectList(iPerdId);
    }

    $("#SelectAll").click(function () {
        window.CRM.checkAll = true;

        window.CRM.paddleNumListTable.ajax.reload();
    });

    $("#SelectNone").click(function () {
        window.CRM.checkAll = false;

        window.CRM.paddleNumListTable.ajax.reload();
    });

    $("#AddBuyer").click(function () {
        createBuyerEditorWindow('1', i18next.t('Buyer Number Editor'));

        //location.href = window.CRM.root + '/PaddleNumEditor.php?CurrentFundraiser=' + window.CRM.fundraiserID + '&linkBack=PaddleNumList.php?FundRaiserID=' + window.CRM.fundraiserID + '&CurrentFundraiser='+window.CRM.fundraiserID;
    });

    $("#AddDonnor").click(function () {
        window.CRM.APIRequest({
            method: 'POST',
            path: 'fundraiser/paddlenum/add/donnors',
            data: JSON.stringify({"fundraiserID": window.CRM.fundraiserID})
        }).done(function(data) {
            if (data.status == "success") {
                window.CRM.paddleNumListTable.ajax.reload();
            }
        });
    });

    window.CRM.paddleNumListTable = $("#buyer-listing-table").DataTable({
        ajax: {
            url: window.CRM.root + "/api/fundraiser/paddlenum/list/" + window.CRM.fundraiserID,
            type: 'POST',
            contentType: "application/json",
            dataSrc: "PaddleNumItems"
        },
        "language": {
            "url": window.CRM.plugin.dataTable.language.url
        },
        "searching": false,
        columns: [
            {
                width: 'auto',
                title: i18next.t('Select'),
                data: 'Id',
                render: function (data, type, full, meta) {
                    return '<input type="checkbox"' +
                        'name="Chk' + data + '" ' + ((window.CRM.checkAll) ? 'checked="yes"' : '') + ' ></input>';
                }
            },
            {
                width: 'auto',
                title: i18next.t('Number'),
                data: 'Num',
                render: function (data, type, full, meta) {
                    return '<a href="' + window.CRM.root + '/PaddleNumEditor.php?PaddleNumID=' + full.Id + '&linkBack=PaddleNumList.php"> ' + full.Num + '</a>';
                }
            },
            {
                width: 'auto',
                title: i18next.t('Buyer'),
                data: 'BuyerFirstName',
                render: function (data, type, full, meta) {
                    return full.BuyerFirstName + ' ' + full.BuyerLastName;
                }
            },
            {
                width: 'auto',
                title: i18next.t('Delete'),
                data: 'Id',
                render: function (data, type, full, meta) {
                    return '<a href="#" data-pnid="' + data + '" class="pnDelete">\n' +
                        '<i class="fa fa-trash-o" aria-hidden="true" style="color:#ff0000"></i></a>'
                }
            }
        ],
        responsive: true,
        createdRow: function (row, data, index) {
            $(row).addClass("paymentRow");
        }
    });

});
