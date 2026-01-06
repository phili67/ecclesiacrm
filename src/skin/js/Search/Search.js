$(function () {
    var elements = {};
    var group_elements = {};
    var group_role_elements = {}
    var available_search_type = [];
    var buildMenu = false;
    var cart = [];
    var search_Term = window.CRM.mode;
    var first_loaded = true;

    const loadAllPeople = () => {
        window.CRM.closeDialogLoadingFunction();

        window.CRM.listPeople = cart;
    }

    const loadGroupByType = (GroupType) => {
        var real_GroupType = GroupType.substr(GroupType.indexOf("-") + 1);

        window.CRM.APIRequest({
            method: 'POST',
            path: 'search/getGroupForTypeID/',
            data: JSON.stringify({ "GroupType": real_GroupType })
        }, function (data) {
            if (real_GroupType >= 0 && $('#group_search_filters').is(":visible") === false) {
                // we create the group popup menu
                group_elements = {};
                group_role_elements = {}
                $("#searchComboGroup").empty();
                $("#searchComboGroupRole").empty();
                var option = new Option(i18next.t('All Groups'), null, false, false);
                $("#searchComboGroup").append(option);
                $.each(data, function (index, value) {
                    var option = new Option(value.Name, value.Id, false, false);
                    $("#searchComboGroup").append(option);
                });
                $("#group_search_filters").show()
            }
        });
    }

    const loadGroupRole = (group) => {
        if (group == "null") {
            group_role_elements = {};
            $("#searchComboGroupRole").empty();
            return;
        }

        window.CRM.APIRequest({
            method: 'POST',
            path: 'search/getGroupRoleForGroupID/',
            data: JSON.stringify({ "Group": group })
        }, function (data) {
            // we create the group popup menu
            group_role_elements = {};
            $("#searchComboGroupRole").empty();
            var option = new Option(i18next.t('All Roles'), null, false, false);
            $("#searchComboGroupRole").append(option);
            $.each(data, function (index, value) {
                var option = new Option(i18next.t(value), index, false, false);
                $("#searchComboGroupRole").append(option);
            });
        });
    }

    const loadSearchCombo = () => {
        // first we clean all the drop down
        buildMenu = true;

        window.CRM.APIRequest({
            method: 'POST',
            path: 'search/comboElements/'
        }, function (data) {
            $.each(data, function (index, value) {
                var optgroup = $('<optgroup>');
                optgroup.attr('id', value[0]);
                optgroup.attr('label', index);

                available_search_type.push(value[0])

                $.each(value[1], function (index2, value2) {
                    var option = "";
                    if (window.CRM.gender !== -1 && index2 == "Gender-" + window.CRM.gender) {
                        option = new Option(value2, index2, true, true);
                    } else if (window.CRM.familyRole !== -1 && index2 == "FamilyRole-" + window.CRM.familyRole) {
                        option = new Option(value2, index2, true, true);
                    } else if (window.CRM.classification !== -1 && index2 == "Classification-" + window.CRM.classification) {
                        option = new Option(value2, index2, true, true);
                    } else {
                        option = new Option(value2, index2, false, false);
                    }
                    optgroup.append(option);

                });
                $("#searchCombo").append(optgroup).trigger('change');
            });

            buildMenu = false;
        });
    }

    if (search_Term != "*") {
        $(".person-filters").hide();
    }

    $("#SearchTerm").select2({
        language: window.CRM.shortLocale,
        minimumInputLength: 1,
        allowClear: true, // This is for clear get the clear button if wanted
        placeholder: i18next.t("Search terms like : name, first name, phone number, property, group name, etc ..."),
        ajax: {
            url: function (params) {
                // in all the case everything is hidden and empty
                $("#searchComboGroup").empty();
                $("#searchComboGroupRole").empty();
                $("#group_search_filters").hide();
                $('#SearchTerm').empty();

                if (params.term === "*") {
                    $(".person-filters").show();
                } else {
                    $(".person-filters").hide();
                }
                return window.CRM.root + "/api/search/getresultbyname/" + params.term;
            },
            headers: {
                "Authorization": "Bearer " + window.CRM.jwtToken
            },
            dataType: 'json',
            delay: 250,
            data: "",
            processResults: function (data, params) {
                return { results: data };
            },
            cache: true
        }
    });

    $("#SearchTerm").on("select2:select", function (e) {
        search_Term = e.params.data.text;

        cart = [];

        window.CRM.dialogLoadingFunction(i18next.t('In progress....'), function () {
            window.CRM.dataSearchTable.ajax.reload(function (json) {
                loadAllPeople();
            }, false);
        });
    });

    if (search_Term != '') {
        var data = {
            id: 1,
            text: search_Term
        };

        var newOption = new Option(data.text, data.id, true, true);
        $('#SearchTerm').append(newOption).trigger('change');

        window.CRM.dialogLoadingFunction(i18next.t('In progress....'), function () {
            window.CRM.dataSearchTable.ajax.reload(function (json) {
                loadAllPeople();
            }, false);
        });
    }


    if (window.CRM.gender !== -1) {
        elements['Gender'] = window.CRM.gender;
    }

    if (window.CRM.familyRole !== -1) {
        elements['FamilyRole'] = window.CRM.familyRole;
    }

    if (window.CRM.classification !== -1) {
        elements['Classification'] = window.CRM.classification;
    }

    $("#searchCombo").select2();
    $("#searchComboGroup").select2();
    $("#searchComboGroupRole").select2();


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
                var index = element.substr(pos + 1);
                elements[option_Type] = index;

                $('#' + option_Type).prop('disabled', true);

                if (option_Type == 'GroupType') {
                    // we've to show the group belonging to the type
                    loadGroupByType(element);
                    has_group_in_elements = true;
                }
            }
        }

        cart = [];
        window.CRM.dialogLoadingFunction(i18next.t('In progress....'), function () {
            window.CRM.dataSearchTable.ajax.reload(function (json) {
                loadAllPeople()
            }, false);

        });

        if (has_group_in_elements === false) {
            group_elements = {};
            group_role_elements = {}
            $("#searchComboGroup").empty();
            $("#searchComboGroupRole").empty();
            $("#group_search_filters").hide();
        }
    });

    $("#searchComboGroup").select2().on("change", function (e) {
        var data = $(this).select2('data');
        group_elements = {};
        group_role_elements = {}

        if (data.length > 0) {
            for (i = 0; i < data.length; i++) {// it's planned to work with more than one group
                var group_element = data[i].id;
                if (group_element != "null") {
                    group_elements['Group'] = group_element;
                }
                loadGroupRole(group_element)
            }
        } else {
            $("#searchComboGroupRole").empty();
        }

        cart = [];
        window.CRM.dialogLoadingFunction(i18next.t('In progress....'), function () {
            window.CRM.dataSearchTable.ajax.reload(function (json) {
                loadAllPeople()
            }, false);
        });
    });

    $("#searchComboGroupRole").select2().on("change", function (e) {
        var data = $(this).select2('data');
        group_role_elements = {}

        if (data.length > 0) {
            for (i = 0; i < data.length; i++) {// it's planned to work with more than one group
                var group_element_role = data[i].id;
                if (group_element_role != "null") {
                    group_role_elements['Role'] = group_element_role;
                }
            }
        }

        cart = [];
        window.CRM.dialogLoadingFunction(i18next.t('In progress....'), function () {
            window.CRM.dataSearchTable.ajax.reload(function (json) {
                loadAllPeople()
            }, false);
        });
    });

    var dataTableSearchConfig = {
        ajax: {
            url: window.CRM.root + "/api/search/getresult/",
            type: 'POST',
            contentType: "application/json",
            dataSrc: "SearchResults",
            "beforeSend": function (xhr) {
                xhr.setRequestHeader('Authorization',
                    "Bearer " + window.CRM.jwtToken
                );
            },
            data: function (json) {
                return JSON.stringify({
                    "SearchTerm": search_Term,
                    "Elements": elements,
                    "GroupElements": group_elements,
                    "GroupRoleElements": group_role_elements
                });
            }
        },
        rowGroup: {
            dataSrc: 'type'
        },
        "language": {
            "url": window.CRM.plugin.dataTable.language.url
        },
        "searching": true,
        "deferRender": true,
        orderFixed: [1, 'dsc'],
        columns: [
            {
                width: 'auto',
                title: i18next.t('id'),
                visible: false,
                data: 'id',
                render: function (data, type, full, meta) {
                    if (full.realType == 'Persons' || full.realType == 'Person Custom Field'
                        || full.realType == 'Individual Pastoral Cares' || full.realType == 'Person Properties'
                        || full.realType == 'Person Group role assignment'
                        || full.realType == 'Volunteer Opportunities') {
                        if (cart.indexOf(data) == -1) {
                            cart.push(data);
                        }
                        return data;// only persons can be added to the cart
                    } else if (full.realType == 'Families' || full.realType == 'Addresses' || full.realType == 'Family Custom Field'
                        || full.realType == 'Family Pastoral Cares' || full.realType == 'Groups') {
                        for (i = 0; i < full.members.length; i++) {
                            if (cart.indexOf(full.members[i]) == -1) {
                                cart.push(full.members[i]);
                            }
                        }
                    }
                    return null;
                }
            },
            {
                width: 'auto',
                title: i18next.t('Type'),
                visible: false,
                data: 'type',
                render: function (data, type, full, meta) {
                    return data;
                }
            },
            {
                width: 'auto',
                title: i18next.t('Photos'),
                data: 'img',
                render: function (data, type, full, meta) {
                    return data;
                }
            },
            {
                width: 'auto',
                title: i18next.t('Actions'),
                data: 'actions',
                render: function (data, type, full, meta) {
                    return '<table width="150" class="outer"><tbody><tr style="background-color: transparent !important;"><td style="border: 0px;">' + data + '</td></tr></tbody></<table>';
                }
            },
            {
                width: 'auto',
                title: i18next.t('Search result'),
                data: 'searchresult',
                render: function (data, type, full, meta) {
                    return data;
                }
            },
            {
                width: 'auto',
                title: i18next.t('Address'),
                data: 'address',
                render: function (data, type, full, meta) {
                    return data;
                }
            },
            {
                width: 'auto',
                title: i18next.t('Gender'),
                visible: false,
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
                visible: false,
                data: 'FamilyRole',
                render: function (data, type, full, meta) {
                    return data;
                }
            },
            {
                width: 'auto',
                title: i18next.t('Property Name'),
                visible: false,
                data: 'ProNames',
                render: function (data, type, full, meta) {
                    return data;
                }
            }
        ]
    };



    window.CRM.plugin.dataTable.buttons.push({
        text: i18next.t("Cart Operations"),
        extend: 'collection',
        className: 'btn btn-success',
        enabled: true,
        attr: {
            id: 'addSelectedToGroup',
            //style: "width: 300px"
        },
        buttons: [
            {
                text: '<span class="dt-button-green"><i class="fas fa-cart-plus"></i> ' + i18next.t("Add This Page") + '</span>',
                action: function (e, dt, node, config) {
                    var listPagePeople = [];
                    var listPageGroups = [];
                    var listPageFamilies = [];

                    $(".AddToPeopleCart").each(function (res) {
                        var personId = $(this).data("cartpersonid");

                        listPagePeople.push(personId);
                    });

                    $(".AddToGroupCart").each(function (res) {
                        var groupID = $(this).data("cartgroupid");

                        listPageGroups.push(groupID);
                    });

                    $(".AddToFamilyCart").each(function (res) {
                        var famID = $(this).data("cartfamilyid");

                        listPageFamilies.push(famID);
                    });


                    window.CRM.dialogLoadingFunction(i18next.t("Loading people in cart...."), function () {
                        if (listPagePeople.length > 0) {
                            window.CRM.cart.addPerson(listPagePeople, function () {
                                window.CRM.closeDialogLoadingFunction();
                            });
                        }

                        if (listPageFamilies.length > 0) {
                            window.CRM.cart.addFamilies(listPageFamilies, function () {
                                window.CRM.closeDialogLoadingFunction();
                            });
                        }

                        if (listPageGroups.length > 0) {
                            window.CRM.cart.addGroups(listPageGroups, function () {
                                window.CRM.closeDialogLoadingFunction();
                            });
                        }
                    });

                    $(".dt-button-background").trigger("click");
                }
            },
            {
                text: '<span class="dt-button-green"><i class="fas fa-cart-plus"></i> ' + i18next.t("Add All results") + '</span>',
                action: function (e, dt, node, config) {
                    loadAllPeople()

                    window.CRM.dialogLoadingFunction(i18next.t('Loading people in cart....'), function () {
                        window.CRM.cart.addPerson(window.CRM.listPeople, function () {
                            window.CRM.closeDialogLoadingFunction();
                        });
                    });

                    $(".dt-button-background").trigger("click");
                }
            },
            {
                text: '<span class="dt-button-red"><i class="fas fa-trash"></i> ' + i18next.t("Remove this page") + '</span>',
                action: function (e, dt, node, config) {
                    var listPagePeople = [];
                    var listPageGroups = [];
                    var listPageFamilies = [];

                    $(".RemoveFromPeopleCart").each(function (res) {
                        var personId = $(this).data("cartpersonid");

                        listPagePeople.push(personId);
                    });

                    $(".RemoveFromGroupCart").each(function (res) {
                        var groupID = $(this).data("cartgroupid");

                        listPageGroups.push(groupID);
                    });

                    $(".RemoveFromFamilyCart").each(function (res) {
                        var famID = $(this).data("cartfamilyid");

                        listPageFamilies.push(famID);
                    });


                    window.CRM.dialogLoadingFunction(i18next.t("Removing people in cart...."), function () {
                        if (listPagePeople.length > 0) {
                            window.CRM.cart.removePerson(listPagePeople, function () {
                                window.CRM.closeDialogLoadingFunction();
                            });
                        }

                        if (listPageFamilies.length > 0) {
                            window.CRM.cart.removeFamilies(listPageFamilies, function () {
                                window.CRM.closeDialogLoadingFunction();
                            });
                        }

                        if (listPageGroups.length > 0) {
                            window.CRM.cart.removeGroups(listPageGroups, function () {
                                window.CRM.closeDialogLoadingFunction();
                            });
                        }
                    });

                    $(".dt-button-background").trigger("click");
                }
            },
            {
                text: '<span class="dt-button-red"><i class="fas fa-trash"></i> ' + i18next.t("Remove All results") + '</span>',
                action: function (e, dt, node, config) {
                    loadAllPeople()

                    window.CRM.dialogLoadingFunction(i18next.t('Removing people in cart....'), function () {
                        window.CRM.cart.removePerson(window.CRM.listPeople, function () {
                            window.CRM.closeDialogLoadingFunction();
                        });
                    });

                    $(".dt-button-background").trigger("click");
                }
            },
            {
                text: '<span class="dt-button-orange">&cap; ' + i18next.t("Intersect result with cart") + '</span>',
                action: function (e, dt, node, config) {
                    loadAllPeople()

                    window.CRM.dialogLoadingFunction(i18next.t('Loading people in cart....'), function () {
                        window.CRM.cart.intersectPerson(window.CRM.listPeople, function () {
                            window.CRM.closeDialogLoadingFunction();
                        });
                    });

                    $(".dt-button-background").trigger("click");
                }
            }
        ]
    });

    $.extend(dataTableSearchConfig, window.CRM.plugin.dataTable);

    window.CRM.dataSearchTable = $("#DataSearchTable").DataTable(dataTableSearchConfig);

    $("#DataSearchTable").on('search.dt', function () {
        var info = window.CRM.dataSearchTable.page.info();
        $('#numberOfPersons').html(info.recordsDisplay);

        if (info.recordsDisplay > 0) {
            window.CRM.closeDialogLoadingFunction();
        }
    });

    $(document).on("click", "#search_OK", function () {
        //window.CRM.dataSearchTable.ajax.reload(null, false);
        var res = cart.length;
        cart = [];

        cart = [];
        window.CRM.dialogLoadingFunction(i18next.t('In progress....'), function () {
            window.CRM.dataSearchTable.ajax.reload(function (json) {
                loadAllPeople()
            }, false);
        })
    });


    // the main part
    $("#group_search_filters").hide()
    loadSearchCombo();


    /* Custom filtering function which will search data in column four between two values */
    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
        if (buildMenu == false) {
            return true;
        }
        return true;
    });

    // listener updateCartMessage
    // newMessage event subscribers : Listener CRJSOM.js
    $(document).on("updateCartMessage", updateButtons);

    // newMessage event handler
    function updateButtons(e) {
        if (first_loaded == false) {
            window.CRM.dataSearchTable.ajax.reload(null, false);
        }
        first_loaded = false;
    }
});
