$(document).ready(function () {
    var elements = {};
    var group_elements = [];
    var available_search_type = [];
    var buildMenu = false;

    $("#searchCombo").select2();
    $("#searchComboGroup").select2();

    $("#searchCombo").select2().on("change", function (e) {
        if (buildMenu == true) {
            return;
        }
        var data = $(this).select2('data');

        elements = {};
        var has_group_in_elements = false;

        $.each(available_search_type, function (index, val) {
            $('#' + val).prop('disabled', false);
        });

        if (data.length > 0) {
            for (i = 0; i < data.length; i++) {
                var element = data[i].id;
                var pos = element.indexOf("-");
                var option_Type = element.substr(0, pos);
                var index = element.substr(pos+1);
                elements[option_Type]=index;

                $('#' + option_Type).prop('disabled', true);

                if (option_Type == 'GroupType') {
                    // we've to show the group belonging to the type
                    loadGroupByType(element);
                    has_group_in_elements = true;
                }
            }

            window.CRM.dataSearchTable.ajax.reload(null, false);
        }

        if (has_group_in_elements === false) {
            group_elements.length = 0;
            $("#searchComboGroup").empty();
            $("#group_search_filters").hide();
        }
    });

    $("#searchComboGroup").select2().on("change", function (e) {
        var data = $(this).select2('data');
        group_elements.length = 0;

        if (data.length > 0) {
            for (i = 0; i < data.length; i++) {
                var group_element = data[i].id;
                var pos = group_element.indexOf("-");
                var option_Type = group_element.substr(0, pos);
                group_elements.push(group_element);
            }
        }
    });

    function loadGroupByType(GroupType) {
        var real_GroupType = GroupType.substr(GroupType.indexOf("-") + 1);

        window.CRM.APIRequest({
            method: 'POST',
            path: 'search/getGroupForTypeID/',
            data: JSON.stringify({"GroupType": real_GroupType})
        }).done(function (data) {
            if ( real_GroupType >= 0 && $('#group_search_filters').is(":visible") === false ) {
                // we create the group popup menu
                group_elements.length = 0;
                $("#searchComboGroup").empty();
                $.each(data, function (index, value) {
                    var option = new Option(value.Name, value.Id, false, false);
                    $("#searchComboGroup").append(option);
                });
                $("#group_search_filters").show()
            }
        });
    }

    function loadSearchCombo() {
        // first we clean all the drop down
        buildMenu = true;

        window.CRM.APIRequest({
            method: 'POST',
            path: 'search/comboElements/'
        }).done(function (data) {
            $.each(data, function (index, value) {
                var optgroup = $('<optgroup>');
                optgroup.attr('id', value[0]);
                optgroup.attr('label', index);

                available_search_type.push(value[0])

                $.each(value[1], function (index2, value2) {
                    var option = new Option(value2, index2, false, false);
                    optgroup.append(option);

                });
                $("#searchCombo").append(optgroup).trigger('change');
            });

            buildMenu = false;
        });
    }

    window.CRM.dataSearchTable = $("#DataSearchTable").DataTable({
        ajax: {
            url: window.CRM.root + "/api/search/getresult/",
            type: 'POST',
            contentType: "application/json",
            dataSrc: "SearchResults",
            data: function (json) {
                var search_Term = $("#SearchTerm").val();

                return JSON.stringify({
                    "SearchTerm": search_Term,
                    "Elements" : elements,
                    "GroupElements": group_elements});
            }
        },
        rowGroup: {
            dataSrc: 'type'
        },
        "language": {
            "url": window.CRM.plugin.dataTable.language.url
        },
        "searching": true,
        columns: [
            {
                width: 'auto',
                title: i18next.t('Search result'),
                data: 'text',
                render: function (data, type, full, meta) {
                    return i18next.t(data);
                }
            },
            {
                width: 'auto',
                title: i18next.t('Type'),
                visible: false,
                data: 'type',
                render: function (data, type, full, meta) {
                    return i18next.t(data);
                }
            },
            {
                width: 'auto',
                title: i18next.t('Gender'),
                visible: true,
                data: 'Gender',
                render: function (data, type, full, meta) {
                    return i18next.t(data);
                }
            },
            {
                width: 'auto',
                title: i18next.t('Classification'),
                visible: true,
                data: 'Classification',
                render: function (data, type, full, meta) {
                    return data;
                }
            },
            {
                width: 'auto',
                title: i18next.t('Family Role'),
                visible: true,
                data: 'FamilyRole',
                render: function (data, type, full, meta) {
                    return data;
                }
            },
            {
                width: 'auto',
                title: i18next.t('Property Name'),
                visible: true,
                data: 'ProNames',
                render: function (data, type, full, meta) {
                    return data;
                }
            }
        ],
        responsive: true
    });

    $(document).on("click","#search_OK", function() {
        window.CRM.dataSearchTable.ajax.reload(null, false);
    });

    $("#group_search_filters").hide()
    loadSearchCombo();


    /* Custom filtering function which will search data in column four between two values */
    $.fn.dataTable.ext.search.push(function( settings, data, dataIndex ) {
        if (buildMenu == false) {
            return true;
        }
        return true;
    });
});
