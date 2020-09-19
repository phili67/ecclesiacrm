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

    $("#SelectAll").click(function () {
        window.CRM.checkAll = true;

        window.CRM.paddleNumListTable.ajax.reload();
    });

    $("#SelectNone").click(function () {
        window.CRM.checkAll = false;

        window.CRM.paddleNumListTable.ajax.reload();
    });

    $("#AddBuyer").click(function () {
        location.href = window.CRM.root + '/PaddleNumEditor.php?CurrentFundraiser=' + window.CRM.fundraiserID + '&linkBack=PaddleNumList.php?FundRaiserID=' + window.CRM.fundraiserID + '&CurrentFundraiser='+window.CRM.fundraiserID;
    });

    $("#AddDonnor").click(function () {
        window.CRM.APIRequest({
            method: 'POST',
            path: 'fundraiser/add/donnors',
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
                        'name="Chk' + data + '" ' + ((window.CRM.checkAll) ? 'checked="yes"' : '') + ' </input>';
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
