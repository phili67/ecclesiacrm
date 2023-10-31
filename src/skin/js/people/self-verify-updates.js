$(function() {
    var familiesTableConfig = {
        ajax: {
            url: window.CRM.root + "/api/families/self-verify",
            dataSrc: 'families'
        },
        columns: [
            {
                title: i18next.t('Family Id'),
                data: 'Family.Id',
                searchable: false,
                render: function (data, type, full, meta) {
                    return '<a href=' + window.CRM.root + '/v2/people/family/view/' + data + '>' + data + '</a>';
                }
            },
            {
                title: i18next.t('Family'),
                data: 'Family.FamilyString',
                searchable: true
            },
            {
                title: i18next.t('Comments'),
                data: 'Text',
                searchable: true
            },
            {
                title: i18next.t('Date'),
                data: 'DateEntered',
                searchable: false,
                render: function (data, type, full, meta) {
                    return moment(data).format(window.CRM.datePickerformat.toUpperCase());
                }
            }
        ],
        order: [[2, "desc"]]
    };

    $.extend(familiesTableConfig,window.CRM.plugin.dataTable);

    $("#families").DataTable(familiesTableConfig);
});