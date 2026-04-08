$(function () {
    var fmt = window.CRM.datePickerformat.toUpperCase();
    var $id = $('#ID');
    var $dateStart = $('#DateStart');
    var $dateEnd = $('#DateEnd');
    var $countBadge = $('#fundraiserCountBadge');

    function buildApiUrl() {
        return window.CRM.root + '/api/fundraiser/findFundRaiser/' + window.CRM.fundraiserID + '/' + window.CRM.startDate + '/' + window.CRM.endDate;
    }

    function updateBadge() {
        if ($countBadge.length && window.CRM.findFundRaiserTable) {
            $countBadge.text(window.CRM.findFundRaiserTable.rows().count());
        }
    }

    function applyFilters() {
        if ($dateStart.val() === '' || $dateEnd.val() === '') {
            window.CRM.startDate = '-1';
            window.CRM.endDate = '-1';
        } else {
            window.CRM.startDate = moment($dateStart.val(), fmt).format('YYYY-MM-DD');
            window.CRM.endDate = moment($dateEnd.val(), fmt).format('YYYY-MM-DD');
        }

        window.CRM.fundraiserID = $id.val();
        if (window.CRM.fundraiserID === '') {
            window.CRM.fundraiserID = '0';
        }

        window.CRM.findFundRaiserTable.ajax.url(buildApiUrl()).load(updateBadge);
    }

    function clearFilters() {
        window.CRM.fundraiserID = '0';
        window.CRM.startDate = '-1';
        window.CRM.endDate = '-1';

        $dateEnd.val('');
        $dateStart.val('');
        $id.val('');

        window.CRM.findFundRaiserTable.ajax.url(buildApiUrl()).load(updateBadge);
    }

    $('#submitFilter').on('click', applyFilters);
    $('#clearFiltersSubmit').on('click', clearFilters);

    // Press Enter in filter fields to apply filters quickly.
    $id.add($dateStart).add($dateEnd).on('keydown', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            applyFilters();
        }
    });

    window.CRM.findFundRaiserTable = $('#fundraiser-listing-table').DataTable({
        ajax: {
            url: buildApiUrl(),
            type: 'POST',
            contentType: 'application/json',
            dataSrc: 'FundRaiserItems',
            beforeSend: function (xhr) {
                xhr.setRequestHeader('Authorization',
                    'Bearer ' + window.CRM.jwtToken
                );
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
                title: i18next.t('Edit'),
                data: 'Id',
                orderable: false,
                className: 'text-center align-middle',
                render: function (data, type, full) {
                    return '<a class="btn btn-xs btn-outline-primary" href="' + window.CRM.root + '/v2/fundraiser/editor/' + full.Id + '"><i class="fas fa-pencil-alt" aria-hidden="true"></i></a>';
                }
            },
            {
                width: 'auto',
                title: i18next.t('Number'),
                data: 'Id',
                className: 'align-middle font-weight-semibold',
                render: function (data) {
                    return data;
                }
            },
            {
                width: 'auto',
                title: i18next.t('Date'),
                data: 'Date',
                className: 'align-middle text-nowrap',
                render: function (data) {
                    return moment(data).format(fmt);
                }
            },
            {
                width: 'auto',
                title: i18next.t('Title'),
                data: 'Title',
                className: 'align-middle',
                render: function (data) {
                    return data;
                }
            }
        ],
        responsive: true,
        createdRow: function (row) {
            $(row).addClass('paymentRow');
        },
        drawCallback: function () {
            updateBadge();
        }
    });

    updateBadge();
});
