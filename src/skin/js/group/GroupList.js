/* Copyright Philippe Logel 2018 all right reserved */

$(function() {
    window.CRM.groupsInCart = 0;
    window.CRM.APIRequest({
        method: "GET",
        path: "groups/groupsInCart"
    }, function (data) {
        window.CRM.groupsInCart = data.groupsInCart;
    });

    $("#addNewGroup").on('click', function () {
        bootbox.dialog({
            title: '<i class="fas fa-users mr-2"></i>' + i18next.t("Add New Group"),
            message: `<div class="form-group mb-0">
                        <label for="bootboxGroupName" class="font-weight-bold">
                            <i class="fas fa-tag mr-1 text-muted"></i>${i18next.t("Group Name")}
                        </label>
                        <input id="bootboxGroupName" type="text" class="form-control"
                               placeholder="${i18next.t("Enter group name")}" autofocus />
                      </div>`,
            buttons: {
                cancel: {
                    label: '<i class="fas fa-times mr-1"></i>' + i18next.t("Cancel"),
                    className: 'btn-outline-secondary'
                },
                confirm: {
                    label: '<i class="fas fa-check mr-1"></i>' + i18next.t("Save"),
                    className: 'btn-success',
                    callback: function () {
                        var groupName = document.getElementById('bootboxGroupName').value.trim();
                        if (!groupName) {
                            bootbox.alert(i18next.t('Please enter a group name.'));
                            return false;
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
            var info = window.CRM.dataTableList.page.info();
            $('#numberOfGroups').html(info.recordsDisplay);
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
                xhr.setRequestHeader('Authorization', "Bearer " + window.CRM.jwtToken);
            }
        },
        columns: [
            {
                width: 'auto',
                title: i18next.t('Group Name'),
                data: 'Name',
                render: function (data, type, full) {
                    return '<div class="d-flex align-items-center">'
                        + '<div class="btn-group btn-group-sm mr-2" role="group" aria-label="' + i18next.t('Group actions') + '">'
                        +   '<a href="' + window.CRM.root + '/v2/group/' + full.Id + '/view" class="btn btn-outline-primary" title="' + i18next.t('View') + '">'
                        +     '<i class="fas fa-search"></i>'
                        +   '</a>'
                        +   '<a href="' + window.CRM.root + '/v2/group/editor/' + full.Id + '" class="btn btn-outline-secondary" title="' + i18next.t('Edit') + '">'
                        +     '<i class="fas fa-pencil-alt"></i>'
                        +   '</a>'
                        + '</div>'
                        + '<span class="font-weight-bold">' + data + '</span>'
                        + '</div>';
                }
            },            
            {
                width: '90px',
                title: i18next.t('Members'),
                data: 'memberCount',
                searchable: false,
                defaultContent: "0",
                render: function (data) {
                    return '<span class="badge badge-pill badge-light border">' + (data || 0) + '</span>';
                }
            },
            {
                width: 'auto',
                title: i18next.t('Group Cart Status'),
                searchable: false,
                data: 'Id',
                render: function (data, type, full) {
                    var disabled = full.memberCount == 0 ? ' disabled' : '';
                    if ($.inArray(full.Id, window.CRM.groupsInCart) > -1) {
                        return '<div class="d-flex align-items-center gap-1" id="groupspanid-' + full.Id + '">'
                            + '<span class="badge badge-success"><i class="fas fa-check mr-1"></i>' + i18next.t("In cart") + '</span>'
                            + '&nbsp;<button class="btn btn-sm btn-outline-danger" id="removeGroupFromCart" data-groupid="' + full.Id + '" title="' + i18next.t('Remove from cart') + '">'
                            +   '<i class="fas fa-times"></i>'
                            + '</button></div>';
                    } else if (window.CRM.showCart) {
                        return '<div class="d-flex align-items-center gap-1" id="groupspanid-' + full.Id + '">'
                            + '<span class="badge badge-light border text-muted"><i class="fas fa-shopping-cart mr-1"></i>' + i18next.t("Not in cart") + '</span>'
                            + '&nbsp;<button class="btn btn-sm btn-outline-primary' + disabled + '" id="AddGroupToCart" data-groupid="' + full.Id + '" title="' + i18next.t('Add to cart') + '">'
                            +   '<i class="fas fa-cart-plus"></i>'
                            + '</button></div>';
                    } else {
                        return '<span class="text-muted small"><i class="fas fa-ban mr-1"></i>' + i18next.t("Cart isn't showable") + '</span>';
                    }
                }
            },
            {
                width: 'auto',
                title: i18next.t('Group Type'),
                data: 'groupType',
                defaultContent: "",
                searchable: true,
                render: function (data) {
                    if (data) {
                        return '<span class="badge badge-pill badge-secondary">' + data + '</span>';
                    }
                    return '<span class="text-muted small">' + i18next.t('Unassigned') + '</span>';
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

    $(document).on("click", "#AddGroupToCart", function () {
        var groupid = $(this).data("groupid");
        var $row = $("#groupspanid-" + groupid);
        var $btn = $(this);
        window.CRM.cart.addGroup(groupid, function () {
            $btn.attr("id", "removeGroupFromCart")
                .removeClass("btn-outline-primary").addClass("btn-outline-danger")
                .attr("title", i18next.t("Remove from cart"))
                .find("i").removeClass("fa-cart-plus").addClass("fa-times");
            $row.find(".badge").removeClass("badge-light border text-muted").addClass("badge-success")
                .html('<i class="fas fa-check mr-1"></i>' + i18next.t("In cart"));
        });
    });

    $(document).on("click", "#removeGroupFromCart", function () {
        var groupid = $(this).data("groupid");
        var $row = $("#groupspanid-" + groupid);
        var $btn = $(this);
        window.CRM.cart.removeGroup(groupid, function () {
            $btn.attr("id", "AddGroupToCart")
                .removeClass("btn-outline-danger").addClass("btn-outline-primary")
                .attr("title", i18next.t("Add to cart"))
                .find("i").removeClass("fa-times").addClass("fa-cart-plus");
            $row.find(".badge").removeClass("badge-success").addClass("badge-light border text-muted")
                .html('<i class="fas fa-shopping-cart mr-1"></i>' + i18next.t("Not in cart"));
        });
    });
});
