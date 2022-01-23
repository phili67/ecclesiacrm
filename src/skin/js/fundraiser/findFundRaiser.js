$(document).ready(function () {
    var fmt = window.CRM.datePickerformat.toUpperCase();

    $('body').on('click',"#submitFilter", function () {
        if ($("#DateStart").val() == '' || $("#DateEnd").val() == '') {
            window.CRM.startDate = '-1';
            window.CRM.endDate = '-1';
        } else {
            window.CRM.startDate = moment($("#DateStart").val(), fmt).format('YYYY-MM-DD');
            window.CRM.endDate = moment($("#DateEnd").val(), fmt).format('YYYY-MM-DD');
        }

        window.CRM.fundraiserID = $("#ID").val()

        if (window.CRM.fundraiserID == '') {
            window.CRM.fundraiserID = '0';
        }

        var api = window.CRM.root + "/api/fundraiser/findFundRaiser/" + window.CRM.fundraiserID + '/' + window.CRM.startDate + '/' + window.CRM.endDate;

        window.CRM.findFundRaiserTable.ajax.url( api ).load();
    });

    $('body').on('click',"#clearFiltersSubmit", function () {
        window.CRM.fundraiserID = '0';
        window.CRM.startDate = '-1';
        window.CRM.endDate = '-1';

        $("#DateEnd").val('');
        $("#DateStart").val('');
        $("#ID").val('');

        var api = window.CRM.root + "/api/fundraiser/findFundRaiser/" + window.CRM.fundraiserID + '/' + window.CRM.startDate + '/' + window.CRM.endDate;

        window.CRM.findFundRaiserTable.ajax.url( api ).load();
    });

    window.CRM.findFundRaiserTable = $("#fundraiser-listing-table").DataTable({
        ajax: {
            url: window.CRM.root + "/api/fundraiser/findFundRaiser/" + window.CRM.fundraiserID + '/' + window.CRM.startDate + '/' + window.CRM.endDate,
            type: 'POST',
            contentType: "application/json",
            dataSrc: "FundRaiserItems"
        },
        "language": {
            "url": window.CRM.plugin.dataTable.language.url
        },
        "searching": false,
        columns: [
            {
                width: 'auto',
                title: i18next.t('Edit'),
                data: 'Id',
                render: function (data, type, full, meta) {
                    return '<a href="' + window.CRM.root + '/FundRaiserEditor.php?FundRaiserID=' + full.Id +'"><i class="fas fa-pencil-alt" aria-hidden="true"></i></a>';
                }
            },
            {
                width: 'auto',
                title: i18next.t('Number'),
                data: 'Id',
                render: function (data, type, full, meta) {
                    return data;
                }
            },
            {
                width: 'auto',
                title: i18next.t('Date'),
                data: 'Date',
                render: function (data, type, full, meta) {
                    return  moment(data).format(fmt);
                }
            },
            {
                width: 'auto',
                title: i18next.t('Title'),
                data: 'Title',
                render: function (data, type, full, meta) {
                   return data
                }
            }
        ],
        responsive: true,
        createdRow: function (row, data, index) {
            $(row).addClass("paymentRow");
        }
    });
});
