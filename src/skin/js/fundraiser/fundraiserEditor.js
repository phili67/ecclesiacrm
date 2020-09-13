$(document).ready(function () {
    $('body').on('click',".deleteDonatedItem", function () {
        var donatedItem = $(this).data('donatedid');

        window.CRM.APIRequest({
            method: "DELETE",
            path: "fundraiser/donateditem",
            data: JSON.stringify({"DonatedItemID":donatedItem,"FundRaiserID": window.CRM.fundraiserID})
        }).done(function (data) {
            if (data.status == "success") {
                window.CRM.donatedItemsTable.ajax.reload();
            }
        });
    });

    window.CRM.donatedItemsTable = $("#fundraiser-table").DataTable({
        ajax: {
            url: window.CRM.root + "/api/fundraiser/" + window.CRM.fundraiserID,
            type: 'POST',
            contentType: "application/json",
            dataSrc: "DonatedItems"
        },
        "language": {
            "url": window.CRM.plugin.dataTable.language.url
        },
        "searching": false,
        columns: [
            {
                width: 'auto',
                title: i18next.t('Item'),
                data: 'di_Item',
                render: function (data, type, full, meta) {
                    return '<a href="' + window.CRM.root + '/v2/fundraiser/donatedItemEditor/' + full.di_ID + '/' +  window.CRM.fundraiserID +'"><i class="fa fa-pencil" aria-hidden="true"></i>&nbsp;' + data + '</a>';
                }
            },
            {
                width: 'auto',
                title: i18next.t('Multiple'),
                data: 'di_multibuy',
                render: function (data, type, full, meta) {
                    return (data == "1")?'x':'';
                }
            },
            {
                width: 'auto',
                title: i18next.t('Donor'),
                data: 'donorFirstName',
                render: function (data, type, full, meta) {
                    return data + ' ' + full.donorLastName;
                }
            },
            {
                width: 'auto',
                title: i18next.t('Buyer'),
                data: 'di_multibuy',
                render: function (data, type, full, meta) {
                    if (data == "1") {
                        return i18next.t('Multiple');
                    }

                    res = '';
                    if (full.buyerFirstName != null)
                        res += full.buyerFirstName;
                    if (full.buyerLastName != null)
                        res += ' ' + full.buyerLastName;

                    return res;
                }
            },
            {
                width: 'auto',
                title: i18next.t('Title'),
                data: 'di_title',
                render: function (data, type, full, meta) {
                    return data;
                }
            },
            {
                width: 'auto',
                title: i18next.t('Title'),
                data: 'di_title',
                render: function (data, type, full, meta) {
                    return data;
                }
            },
            {
                width: 'auto',
                title: i18next.t('Sale Price'),
                data: 'di_sellprice',
                render: function (data, type, full, meta) {
                    return Number(data).toLocaleString(window.CRM.locale.replace("_","-"));
                }
            },
            {
                width: 'auto',
                title: i18next.t('Estimated value'),
                data: 'di_estprice',
                render: function (data, type, full, meta) {
                    return Number(data).toLocaleString(window.CRM.locale.replace("_","-"));
                }
            },
            {
                width: 'auto',
                title: i18next.t('Material Value'),
                data: 'di_materialvalue',
                render: function (data, type, full, meta) {
                    return Number(data).toLocaleString(window.CRM.locale.replace("_","-"));
                }
            },
            {
                width: 'auto',
                title: i18next.t('Minimum Price'),
                data: 'di_minimum',
                render: function (data, type, full, meta) {
                    return Number(data).toLocaleString(window.CRM.locale.replace("_","-"));
                }
            },
            {
                width: 'auto',
                title: i18next.t('Delete'),
                data: 'di_ID',
                render: function (data, type, full, meta) {
                    return '<a href="#" class="deleteDonatedItem" data-donatedid="' + data + '"><i class="fa fa-trash-o deleteDonatedItem" aria-hidden="true" style="color:red" data-donatedid="' + data + '"></i>';
                }
            }
        ],
        responsive: true,
        createdRow: function (row, data, index) {
            $(row).addClass("paymentRow");
        }
    });
});
