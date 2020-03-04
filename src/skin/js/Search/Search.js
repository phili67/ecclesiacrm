$(document).ready(function () {
    var elements = {};
    var group_elements = {};
    var group_role_elements = {}
    var available_search_type = [];
    var buildMenu = false;

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
                var index = element.substr(pos+1);
                elements[option_Type]=index;

                $('#' + option_Type).prop('disabled', true);

                if (option_Type == 'GroupType') {
                    // we've to show the group belonging to the type
                    loadGroupByType(element);
                    has_group_in_elements = true;
                }
            }

            $('.progress').css("color", "red");
            $('.progress').html("  "+ i18next.t("In progress...."));
            window.CRM.dataSearchTable.ajax.reload(function ( json ) {
                $('.progress').css("color", "green");
                $('.progress').html("  "+i18next.t("Done !"));
                loadAllPeople()
            }, false);
        }

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
                group_elements['Group'] = group_element;
                loadGroupRole (group_element)
            }
            $('.progress').css("color", "red");
            $('.progress').html("  "+ i18next.t("In progress...."));
            window.CRM.dataSearchTable.ajax.reload(function ( json ) {
                $('.progress').css("color", "green");
                $('.progress').html("  "+i18next.t("Done !"));
                loadAllPeople()
            }, false);
        }
    });
    
    $("#searchComboGroupRole").select2().on("change", function (e) {
        var data = $(this).select2('data');
        group_role_elements = {}

        if (data.length > 0) {
            for (i = 0; i < data.length; i++) {// it's planned to work with more than one group
                var group_element_role = data[i].id;
                group_role_elements['Role'] = group_element_role;
            }
            $('.progress').css("color", "red");
            $('.progress').html("  "+ i18next.t("In progress...."));
            window.CRM.dataSearchTable.ajax.reload(function ( json ) {
                $('.progress').css("color", "green");
                $('.progress').html("  "+i18next.t("Done !"));
                loadAllPeople()
            }, false);
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
                group_elements = {};
                group_role_elements = {}
                $("#searchComboGroup").empty();
                $("#searchComboGroupRole").empty();
                $.each(data, function (index, value) {
                    var option = new Option(value.Name, value.Id, false, false);
                    $("#searchComboGroup").append(option);
                });
                $("#group_search_filters").show()
            }
        });
    }
    
    function loadGroupRole (group) {
        window.CRM.APIRequest({
            method: 'POST',
            path: 'search/getGroupRoleForGroupID/',
            data: JSON.stringify({"Group": group})
        }).done(function (data) {
            // we create the group popup menu
            group_role_elements = {};
            $("#searchComboGroupRole").empty();
            $.each(data, function (index, value) {
                var option = new Option(i18next.t(value), index, false, false);
                $("#searchComboGroupRole").append(option);
            });
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
                    "GroupElements": group_elements,
                    "GroupRoleElements": group_role_elements});
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
                    if (full.realType == 'Persons') {
                        return data;// only persons can be added to the cart
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
                    return i18next.t(data);
                }
            },
            {
                width: 'auto',
                title: i18next.t('Photos'),
                data: 'img',
                render: function (data, type, full, meta) {
                    if (full.realType == 'Persons') {
                        return '<img src="/api/persons/' + full.id + '/thumbnail" class="initials-image direct-chat-img " width="10px" height="10px">';
                    } else if (full.realType == 'Addresses') {
                        return '<img src="/api/families/' + full.id + '/thumbnail" class="initials-image direct-chat-img " width="10px" height="10px">';
                    } else {
                        return null;
                    }
                }
            },
            {
                width: 'auto',
                title: i18next.t('Actions'),
                data: 'text',
                render: function (data, type, full, meta) {
                    var res = ''

                    if (full.realType == 'Persons') {
                        res += '<a href="' + window.CRM.root + '/PersonEditor.php?PersonID=' + full.id + '" data-toggle="tooltip" data-placement="top" data-original-title="' + i18next.t('Edit') + '">'
                            + '<span class="fa-stack">'
                            + '<i class="fa fa-square fa-stack-2x"></i>'
                            + '<i class="fa fa-pencil fa-stack-1x fa-inverse"></i>'
                            + '</span>'
                            + '</a>&nbsp;';

                        if (full.inCart === false) {
                            res += "<a class=\"AddToPeopleCart\" data-cartpersonid=\"" + full.id + "\">\n" +
                                "\n" +
                                "                <span class=\"fa-stack\">\n" +
                                "                <i class=\"fa fa-square fa-stack-2x\"></i>\n" +
                                "                <i class=\"fa fa-stack-1x fa-inverse fa-cart-plus\"></i>\n" +
                                "                </span>\n" +
                                "                </a>  ";
                        } else {
                            res += "<a class=\"AddToPeopleCart\" data-cartpersonid=\"" + full.id + "\">\n" +
                                "\n" +
                                "                <span class=\"fa-stack\">\n" +
                                "                <i class=\"fa fa-square fa-stack-2x\"></i>\n" +
                                "                <i class=\"fa fa-remove fa-stack-1x fa-inverse\"></i>\n" +
                                "                </span>\n" +
                                "                </a>  ";
                        }

                        res += '&nbsp;<a href="' + window.CRM.root +'/PrintView.php?PersonID=' + full.id + '"  data-toggle="tooltip" data-placement="top" data-original-title="' + i18next.t('Print') + '">'
                            + '<span class="fa-stack">'
                            + '<i class="fa fa-square fa-stack-2x"></i>'
                            + '<i class="fa fa-print fa-stack-1x fa-inverse"></i>'
                            + '</span>'
                            + '</a>';

                    } else if (full.realType == 'Addresses') {
                        res += '<a href="' + window.CRM.root + '/FamilyEditor.php?FamilyID=' + full.id + '" data-toggle="tooltip" data-placement="top" data-original-title="' + i18next.t('Edit') + '">'
                            + '<span class="fa-stack">'
                            + '<i class="fa fa-square fa-stack-2x"></i>'
                            + '<i class="fa fa-pencil fa-stack-1x fa-inverse"></i>'
                            + '</span>'
                            + '</a>&nbsp;';

                        res += '<a href="' + window.CRM.root + '/FamilyView.php?FamilyID=' + full.id + '" data-toggle="tooltip" data-placement="top" data-original-title="' + i18next.t('Edit') + '">'
                            + '<span class="fa-stack">'
                            + '<i class="fa fa-square fa-stack-2x"></i>'
                            + '<i class="fa fa-search-plus fa-stack-1x fa-inverse"></i>'
                            + '</span>'
                            + '</a>&nbsp;';
                    } else {
                        return null;
                    }


                    return res;
                }
            },
            {
                width: 'auto',
                title: i18next.t('Search result'),
                data: 'text',
                render: function (data, type, full, meta) {
                    if (full.realType == "Persons") {
                        return '<a href="' + window.CRM.root + '/PersonView.php?PersonID=' + full.id + '" data-toggle="tooltip" data-placement="top" data-original-title="' + i18next.t('Edit') + '">' + data + '</a>'
                    } else if (full.realType == 'Addresses') {
                        return '<a href="' + window.CRM.root + '/FamilyView.php?FamilyID=' + full.id + '" data-toggle="tooltip" data-placement="top" data-original-title="' + i18next.t('Edit') + '">' + data + '</a>'
                    }
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
        //window.CRM.dataSearchTable.ajax.reload(null, false);
        window.CRM.dataSearchTable.ajax.reload(function ( json ) {
            loadAllPeople()
        }, false);
    });

    function loadAllPeople()
    {
        window.CRM.listPeople = window.CRM.dataSearchTable
            .column( 0 )
            .data()
            .toArray();
    }

    $("#AddAllToCart").click(function(){
        loadAllPeople()
        window.CRM.cart.addPerson(window.CRM.listPeople);

        /*$('.progress').css("color", "red");
        $('.progress').html("  "+ i18next.t("Loading people in cart...."));
        window.CRM.dataSearchTable.ajax.reload(function ( json ) {
            $('.progress').css("color", "green");
            $('.progress').html("  "+ i18next.t("Loading finished...."));
        }, false);*/
    });

    $("#AddAllPageToCart").click(function(){
        var listPagePeople  = [];
        $(".AddToPeopleCart").each(function(res) {
            var personId= $(this).data("cartpersonid");

            listPagePeople.push(personId);
        });

        if (listPagePeople.length > 0) {
            window.CRM.cart.addPerson(listPagePeople);
        } else {
            window.CRM.DisplayAlert(i18next.t("Add People"), i18next.t("This page is still in the cart."));
        }
    });


    $("#RemoveAllFromCart").click(function(){
        loadAllPeople()
        window.CRM.cart.removePerson(window.CRM.listPeople);

        /*$('.progress').css("color", "red");
        $('.progress').html("  "+ i18next.t("Loading people in cart...."));
        window.CRM.dataSearchTable.ajax.reload(function ( json ) {
            $('.progress').css("color", "green");
            $('.progress').html("  "+ i18next.t("Loading finished...."));
        }, false);*/
    });

    $("#RemoveAllPageFromCart").click(function(){
        var listPagePeople  = [];
        $(".RemoveFromPeopleCart").each(function(res) {
            var personId= $(this).data("cartpersonid");

            listPagePeople.push(personId);
        });

        window.CRM.cart.removePerson(listPagePeople);
    });

    // the main part
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
