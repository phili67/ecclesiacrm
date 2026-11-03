window.CRM.synchronize.renderers.FamilyInfosDashboard = function (data) {
    function updateFamiliesTable(id, rows, dateField) {
        if (document.getElementById(id) === null) {
            return;
        }

        const table = $('#' + id).DataTable({
            retrieve: true,
            responsive: true,
            paging: false,
            ordering: false,
            searching: false,
            scrollX: false,
            info: false,
            columns: [
                {
                    data: 'Name',
                    render: function (name, type, row) {
                        return row.img + ' <a href=' + window.CRM.root + '/v2/people/family/view/' + row.Id + '>' + name + '</a>';
                    }
                },
                {
                    data: 'Address1',
                    render: function (address) {
                        return address === null ? '' : address.replace(/\\(.)/mg, '$1');
                    }
                },
                {
                    data: dateField,
                    render: function (date) {
                        const timeFormat = window.CRM.timeEnglish == true ? ' hh:mm a' : ' HH:mm';
                        return moment(date).format(window.CRM.datePickerformat.toUpperCase() + timeFormat);
                    }
                }
            ]
        });
        table.clear();
        table.rows.add(rows);
        table.draw(true);
    }

    updateFamiliesTable('latestFamiliesDashboardItem', data.LatestFamilies, 'DateEntered');
    updateFamiliesTable('updatedFamiliesDashboardItem', data.UpdatedFamilies, 'DateLastEdited');
};
