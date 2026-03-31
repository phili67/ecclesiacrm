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
        bootbox.dialog({
            title: i18next.t("Add New Group"),
            message: `<div class="form-group">
                        <label for="bootboxGroupName"><i class="fas fa-users"></i> ${i18next.t("Group Name")}</label>
                        <input id="bootboxGroupName" type="text" class="form-control" placeholder="${i18next.t("Enter group name")}" />
                      </div>`,
            buttons: {
                cancel: {
                    label: i18next.t("Cancel"),
                    className: 'btn-default'
                },
                confirm: {
                    label: i18next.t("Save"),
                    className: 'btn-primary',
                    callback: function () {
                        var groupName = document.getElementById('bootboxGroupName').value.trim();
                        if (!groupName) {
                            bootbox.alert(i18next.t('Please enter a group name.'));
                            return false; // keep modal open
                        }

                        window.CRM.APIRequest({
                            method: 'POST',
                            path: 'groups/',
                            data: JSON.stringify({'groupName': groupName})
                        }, function (data) {
                            window.CRM.dataTableList.row.add(data);
                            window.CRM.dataTableList.rows().invalidate().draw(true);
                            window.CRM.dataTableList.ajax.reload();
                        });
                    }
                }
            }
        });
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
