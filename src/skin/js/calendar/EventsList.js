$(document).ready(function () {
    var start_period = '2022-01-01';
    var end_period   = '2022-12-31';

    moment.locale(window.CRM.shortLocale);

    var timeFormat = "";

    if (window.CRM.timeEnglish == true) {
        timeFormat = window.CRM.datePickerformat.toUpperCase() + ' hh:mm a';
    } else {
        timeFormat = window.CRM.datePickerformat.toUpperCase() + ' HH:mm';
    }

    var DataEventsListTable = {
        ajax: {
            url: window.CRM.root + "/api/calendar/getalleventsForEventsList",
            type: 'POST',
            contentType: "application/json",
            dataSrc: "EventsListResults",
            data: function (json) {
                var search_Term = $("#SearchTerm").val();

                if (search_Term !='') {
                    $('.in-progress').css("color", "red");
                    $('.in-progress').html("  "+ i18next.t("In progress...."));
                }

                return JSON.stringify({
                    "start": start_period,
                    "end" : end_period,
                    "isBirthdayActive": false,
                    "isAnniversaryActive": false});
            }
        },
        rowGroup: {
            dataSrc: 'month',
            startRender: function(rows, group) {
                var oneDate = moment('02-' + group + '-2021', 'DD-MM-YYYY');
                return i18next.t('Events for') + ' : ' + oneDate.format('MMMM');
            }
        },
        "pageLength": 20000,
        "language": {
            "url": window.CRM.plugin.dataTable.language.url
        },
        "searching": true,
        "deferRender": true,
        orderFixed: [3, 'asc'],
        columns: [
            {
                width: 'auto',
                title: i18next.t('Actions'),
                visible: true,
                data: 'icon',
                render: function (data, type, full, meta) {
                    return data; // + full.backgroundColor
                }
            },
            {
                width: 'auto',
                title: i18next.t('Date'),
                visible: true,
                data: 'start',
                render: function (data, type, full, meta) {
                    return moment(data).format(timeFormat);
                }
            },
            {
                width: 'auto',
                title: i18next.t('Title'),
                data: 'title',
                render: function (data, type, full, meta) {
                    return data;
                }
            },
            {
                width: 'auto',
                title: i18next.t('Month'),
                data: 'month',
                visible: false,
                render: function (data, type, full, meta) {
                    return data;
                }
            },
            {
                width: 'auto',
                title: i18next.t('Month'),
                data: 'eventID',
                render: function (data, type, full, meta) {
                    var oneDate = moment('02-' + full.month + '-2021', 'DD-MM-YYYY');
                    return oneDate.format('MMMM');
                }
            }
        ]
    };

    $.extend(DataEventsListTable,window.CRM.plugin.dataTable);

    window.CRM.DataEventsListTable = $("#DataEventsListTable").DataTable(DataEventsListTable);

    /* Custom filtering function which will search data in column four between two values */
    /*$.fn.dataTable.ext.search.push(function( settings, data, dataIndex ) {
        if (buildMenu == false) {
            return true;
        }
        return true;
    });*/
});
