$(function() {
    var familiesTableConfig = {
        ajax: {
            url: window.CRM.root + "/api/families/pending-self-verify",
            dataSrc: 'families'
        },
        columns: [
            {
                title: i18next.t('Family Id'),
                data: 'FamilyId',
                searchable: false,
                render: function (data, type, full, meta) {
                    return '<a href=' + window.CRM.root + '/v2/people/family/view/' + data + '>' + data + '</a>';
                }
            },
            {
                title: i18next.t('Family'),
                data: 'FamilyName',
                searchable: true
            },
            {
                title: i18next.t('Valid Until'),
                data: 'ValidUntilDate',
                searchable: false,
                render: function (data, type, full, meta) {
                    return moment(data).format(window.CRM.datePickerformat.toUpperCase());
                }
            }
        ],
        order: [[2, "desc"]]
    }

    $.extend(familiesTableConfig,window.CRM.plugin.dataTable);

    $("#families").DataTable(familiesTableConfig);
});