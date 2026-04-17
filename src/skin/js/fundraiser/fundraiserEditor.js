$(function () {
    var locale = window.CRM.locale.replace('_', '-');
    var $countBadge = $('#donatedItemsCountBadge');

    function formatAmount(value) {
        return Number(value || 0).toLocaleString(locale);
    }

    function cleanPictureUrl(value) {
        if (!value) {
            return '';
        }

        var data = String(value).trim();
        if (data.charAt(0) === "'") {
            data = data.substring(1);
        }
        if (data.charAt(data.length - 1) === "'") {
            data = data.substring(0, data.length - 1);
        }

        return data;
    }

    function updateCountBadge() {
        if ($countBadge.length && window.CRM.donatedItemsTable) {
            $countBadge.text(window.CRM.donatedItemsTable.rows().count());
        }
    }

    $('body').on('click', '.deleteDonatedItem', function (event) {
        event.preventDefault();
        var donatedItem = $(this).data('donatedid');

        bootbox.confirm({
            message: i18next.t("You're about to delete the item !!!"),
            buttons: {
                confirm: {
                    label: '<i class="fas fa-times"></i> ' + i18next.t('Yes'),
                    className: 'btn-danger'
                },
                cancel: {
                    label: '<i class="fas fa-check"></i> ' + i18next.t('No'),
                    className: 'btn-primary'
                }
            },
            callback: function (result) {
                if (result) {
                    window.CRM.APIRequest({
                        method: 'DELETE',
                        path: 'fundraiser/donateditem',
                        data: JSON.stringify({ DonatedItemID: donatedItem, FundRaiserID: window.CRM.fundraiserID })
                    }, function (data) {
                        if (data.status === 'success') {
                            window.CRM.donatedItemsTable.ajax.reload(updateCountBadge);
                        }
                    });
                }
            }
        });
    });

    var dataTableConfig = {
        ajax: {
            url: window.CRM.root + '/api/fundraiser/' + window.CRM.fundraiserID,
            type: 'POST',
            contentType: 'application/json',
            dataSrc: 'DonatedItems',
            beforeSend: function (xhr) {
                xhr.setRequestHeader('Authorization', 'Bearer ' + window.CRM.jwtToken);
            }
        },
        language: {
            url: window.CRM.plugin.dataTable.language.url
        },
        searching: false,
        lengthChange: false,
        pageLength: 10,
        autoWidth: false,
        dom: 'rtip',
        columns: [
            {
                width: 'auto',
                title: i18next.t('Item'),
                data: 'di_Item',
                className: 'align-middle',
                render: function (data, type, full) {
                    return '<a class="font-weight-semibold" href="' + window.CRM.root + '/v2/fundraiser/donatedItemEditor/' + full.di_ID + '/' + window.CRM.fundraiserID + '"><i class="fas fa-pencil-alt mr-1" aria-hidden="true"></i>' + data + '</a>';
                }
            },
            {
                width: 'auto',
                title: i18next.t('Preview'),
                data: 'di_picture',
                className: 'align-middle text-center',
                orderable: false,
                render: function (data) {
                    var imageUrl = cleanPictureUrl(data);
                    if (imageUrl.length > 4) {
                        return '<img src="' + imageUrl + '" width="64" class="img-fluid rounded" alt="preview">';
                    }
                    return '';
                }
            },
            {
                width: 'auto',
                title: i18next.t('Multiple'),
                data: 'di_multibuy',
                className: 'align-middle text-center',
                render: function (data) {
                    return data === '1' ? '<span class="badge badge-info">x</span>' : '';
                }
            },
            {
                width: 'auto',
                title: i18next.t('Donor'),
                data: 'donorFirstName',
                className: 'align-middle',
                render: function (data, type, full) {
                    var result = '';
                    if (data != null) {
                        result += data;
                    }
                    if (full.donorLastName != null) {
                        result += (result ? ' ' : '') + full.donorLastName;
                    }
                    return result;
                }
            },
            {
                width: 'auto',
                title: i18next.t('Buyer'),
                data: 'di_multibuy',
                className: 'align-middle',
                render: function (data, type, full) {
                    if (data === '1') {
                        return i18next.t('Multiple');
                    }

                    var result = '';
                    if (full.buyerFirstName != null) {
                        result += full.buyerFirstName;
                    }
                    if (full.buyerLastName != null) {
                        result += (result ? ' ' : '') + full.buyerLastName;
                    }

                    return result;
                }
            },
            {
                width: 'auto',
                title: i18next.t('Title'),
                data: 'di_title',
                className: 'align-middle',
                render: function (data) {
                    return data;
                }
            },
            {
                width: 'auto',
                title: i18next.t('Sale Price'),
                data: 'di_sellprice',
                className: 'align-middle text-right',
                render: function (data) {
                    return formatAmount(data);
                }
            },
            {
                width: 'auto',
                title: i18next.t('Estimated value'),
                data: 'di_estprice',
                className: 'align-middle text-right',
                render: function (data) {
                    return formatAmount(data);
                }
            },
            {
                width: 'auto',
                title: i18next.t('Material Value'),
                data: 'di_materialvalue',
                className: 'align-middle text-right',
                render: function (data) {
                    return formatAmount(data);
                }
            },
            {
                width: 'auto',
                title: i18next.t('Minimum Price'),
                data: 'di_minimum',
                className: 'align-middle text-right',
                render: function (data) {
                    return formatAmount(data);
                }
            },
            {
                width: 'auto',
                title: i18next.t('Delete'),
                data: 'di_ID',
                className: 'align-middle text-center',
                orderable: false,
                render: function (data) {
                    return '<a href="#" class="btn btn-sm btn-outline-danger deleteDonatedItem" data-donatedid="' + data + '"><i class="far fa-trash-alt" aria-hidden="true"></i></a>';
                }
            }
        ],
        responsive: true,
        createdRow: function (row) {
            $(row).addClass('paymentRow');
        },
        drawCallback: function () {
            updateCountBadge();
        }
    };

    $.extend(dataTableConfig, window.CRM.plugin.dataTable);

    window.CRM.donatedItemsTable = $('#fundraiser-table').DataTable(dataTableConfig);
    updateCountBadge();
});
