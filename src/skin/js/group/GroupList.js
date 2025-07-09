/* Copyright Philippe Logel 2018 all right reserved */

$(function() {
    window.CRM.groupsInCart = 0;
    window.CRM.APIRequest({
        method: "GET",
        path: "groups/groupsInCart"
    }, function (data) {
        window.CRM.groupsInCart = data.groupsInCart;
    });

    $("#addNewGroup").on('click',function (e) {
        var groupName = $("#groupName").val(); // get the name of the group from the textbox
        if (groupName) // ensure that the user entered a group name
        {
            window.CRM.APIRequest({
                method: 'POST',
                path: 'groups/',
                data: JSON.stringify({'groupName': groupName})
            }, function (data) {
                window.CRM.dataTableList.row.add(data);                                //add the group data to the existing window.CRM.dataTableListable
                window.CRM.dataTableList.rows().invalidate().draw(true);               //redraw the window.CRM.dataTableListable
                $("#groupName").val(null);
                window.CRM.dataTableList.ajax.reload();// PL : We should reload the table after we add a group so the button add to group is disabled
            });
        } else {

        }
    });

    window.CRM.dataTableList = $("#groupsTable").DataTable({
        "initComplete": function (settings, json) {
            if (window.groupSelect != null) {
                window.CRM.dataTableList.search(window.groupSelect).draw();
            }
        },
        "language": {
            "url": window.CRM.plugin.dataTable.language.url
        },
        responsive: true,
        ajax: {
            url: window.CRM.root + "/api/groups/",
            type: 'GET',
            dataSrc: "Groups",
            "beforeSend": function (xhr) {
                xhr.setRequestHeader('Authorization',
                    "Bearer " +  window.CRM.jwtToken
                );
            }
        },
        columns: [
            {
                width: 'auto',
                title: i18next.t('Group Name'),
                data: 'Name',
                render: function (data, type, full, meta) {
                    return  '<div class="btn-group" role="group" aria-label="Buttons">'
                        + '<a href="' + window.CRM.root + '/v2/group/' + full.Id + '/view" class="btn btn-default btn-xs">'
                        + '<span class="fa-stack fa-stack-custom">'
                        +    '<i class="fas fa-stack-1x fa-inverse fa-search-plus fas-blue"></i>'
                        + '</span>'
                        + '</a>'
                        + '<a href="' + window.CRM.root + '/v2/group/editor/' + full.Id + '"  class="btn btn-default btn-xs">'
                        +    '<span class="fa-stack fa-stack-custom">'
                        +       '<i class="fas fa-pencil-alt fa-stack-1x fa-inverse fas-blue"></i>'
                        +    '</span>'
                        +  '</a> ' + data
                        +  '</div>';
                }
            },
            {
                width: 'auto',
                title: i18next.t('Members'),
                data: 'memberCount',
                searchable: false,
                defaultContent: "0"
            },
            {
                width: 'auto',
                title: i18next.t('Group Cart Status'),
                searchable: false,
                data: 'Id',
                render: function (data, type, full, meta) {
                    // we add the memberCount, so we could disable the button Add All

                    var activLink = '';
                    if (full.memberCount == 0) {
                        activLink = ' disabled'; // PL : We disable the button Add All when there isn't any member in the group
                    }

                    if ($.inArray(full.Id, window.CRM.groupsInCart) > -1) {
                        return '<span id="groupspanid-' + full.Id + '">' + i18next.t("All members of this group are in the cart") + '</span>&nbsp;<a class="btn btn-xs btn-default" id="removeGroupFromCart" data-groupid="' + full.Id + '"><span class="fa-stack"><i class="fas fa-stack-1x fa-inverse fa-times fas-red" ></i></span></a>';
                    } else if (window.CRM.showCart) {
                        return '<span id="groupspanid-' + full.Id + '">' + i18next.t("Not all members of this group are in the cart") + '</span>&nbsp;<a id="AddGroupToCart" class="btn btn-xs btn-default ' + activLink + '" data-groupid="' + full.Id + '"><span class="fa-stack"><i class="fas fa-stack-1x fa-inverse fa-cart-plus fas-blue" ></i></span></a>';
                    } else {
                        return i18next.t("Cart isn't showable");
                    }
                }
            },
            {
                width: 'auto',
                title: i18next.t('Group Type'),
                data: 'groupType',
                defaultContent: "",
                searchable: true,
                render: function (data, type, full, meta) {
                    if (data) {
                        return data;
                    } else {
                        return i18next.t('Unassigned');
                    }
                }
            }
        ]
    });

    $("#groupsTable").on('search.dt', function () {
        var info = window.CRM.dataTableList.page.info();
        $('#numberOfGroups').html(info.recordsDisplay);
    });

    $('#table-filter').on('change', function () {
        window.CRM.dataTableList.search(this.value).draw();
        localStorage.setItem("groupSelect", this.selectedIndex);

        var info = window.CRM.dataTableList.page.info();
        $('#numberOfGroups').html(info.recordsDisplay);
    });

    $(document).on("click", "#AddGroupToCart", function (link) {
        var groupid = $(this).data("groupid");
        var parentText = $("#groupspanid-"+groupid);
        var parentLink = $(this);
        var linkSpan = $(this).find("i");
        window.CRM.cart.addGroup(groupid, function (data) {
            parentLink.attr("id", "removeGroupFromCart");
            linkSpan.removeClass('fa-cart-plus fas-blue').addClass('fa-times fas-red');
            parentText.text(i18next.t("All members of this group are in the cart"));
        });
    });

    $(document).on("click", "#removeGroupFromCart", function (link) {
        var groupid = $(this).data("groupid");
        var parentText = $("#groupspanid-"+groupid);
        var parentLink = $(this);
        var linkSpan = $(this).find("i");
        window.CRM.cart.removeGroup(groupid, function (data) {
            parentLink.attr("id", "AddGroupToCart");
            linkSpan.removeClass('fa-times fas-red').addClass('fa-cart-plus fas-blue');
            parentText.text(i18next.t("Not all members of this group are in the cart"));
        });
    });
});
