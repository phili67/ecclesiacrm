window.CRM.synchronize.renderers.PersonInfosDashboard = function (data) {
    let latestPersonsTable = document.getElementById('latestPersonsDashboardItem');
    if (latestPersonsTable != undefined) {
        latestPersonsTable = $('#latestPersonsDashboardItem').DataTable({
            retrieve: true,
            responsive: true,
            paging: false,
            ordering: false,
            searching: false,
            scrollX: false,
            info: false,
            'columns': [
                {
                    data: 'LastName',
                    render: function (data, type, row, meta) {
                        return row.img + ' <a href=' + window.CRM.root + '/v2/people/person/view/' + row.Id + '>' + data + ' ' + row.FirstName + '</a>';
                    }
                },
                {
                    data: 'Address1',
                    render: function (data, type, row, meta) {
                        if (data === null) {
                            return '';
                        }
                        return data.replace(/\\(.)/mg, "$1");// we strip the slashes
                    }
                },
                {
                    data: 'DateEntered',
                    render: function (data, type, row, meta) {
                        if (window.CRM.timeEnglish == true) {
                            return moment(data).format(window.CRM.datePickerformat.toUpperCase() + ' hh:mm a');
                        } else {
                            return moment(data).format(window.CRM.datePickerformat.toUpperCase() + ' HH:mm');
                        }
                    }
                }
            ]
        });
        latestPersonsTable.clear();
        latestPersonsTable.rows.add(data.LatestPersons);
        latestPersonsTable.draw(true);
    }

    let updatedPersonsTable = document.getElementById('updatedPersonsDashboardItem');
    if (updatedPersonsTable != undefined) {
        updatedPersonsTable = $('#updatedPersonsDashboardItem').DataTable({
            retrieve: true,
            responsive: true,
            paging: false,
            ordering: false,
            searching: false,
            scrollX: false,
            info: false,
            'columns': [
                {
                    data: 'LastName',
                    render: function (data, type, row, meta) {
                        return row.img + ' <a href=' + window.CRM.root + '/v2/people/person/view/' + row.Id + '>' + data + ' ' + row.FirstName + '</a>';
                    }
                },
                {
                    data: 'Address1',
                    render: function (data, type, row, meta) {
                        if (data === null) {
                            return '';
                        }
                        return data.replace(/\\(.)/mg, "$1");// we strip the slashes
                    }
                },
                {
                    data: 'DateLastEdited',
                    render: function (data, type, row, meta) {
                        if (data === null) {
                            data = row.DateEntered;
                        }
                        if (window.CRM.timeEnglish == true) {
                            return moment(data).format(window.CRM.datePickerformat.toUpperCase() + ' hh:mm a');
                        } else {
                            return moment(data).format(window.CRM.datePickerformat.toUpperCase() + ' HH:mm');
                        }
                    }
                }
            ]
        });
        updatedPersonsTable.clear();
        updatedPersonsTable.rows.add(data.UpdatedPerson);
        updatedPersonsTable.draw(true);
    }
};
