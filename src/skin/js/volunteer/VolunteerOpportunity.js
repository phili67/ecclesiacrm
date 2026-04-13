window.CRM.volunteersInCart = [];
window.CRM.APIRequest({
    method: "GET",
    path: "volunteeropportunity/volunteersInCart"
}, function (data) {
    // On suppose que l'API retourne un tableau d'IDs des volunteer opportunities dans le panier
    window.CRM.volunteersInCart = data.volunteersInCart || [];
});


window.CRM.ElementListener('#add-new-volunteer-opportunity', 'click', function (event) {
    var modal = bootbox.dialog({
        message: BootboxContentVolunteerOpportunity,
        title: i18next.t("Add New Volunteer Opportunity"),
        size: "large",
        buttons: [
            {
                label: '<i class="fas fa-times"></i> ' + i18next.t("Close"),
                className: "btn btn-default pull-left",
                callback: function () {
                    console.log("just do something on close");
                }
            },
            {
                label: '<i class="fas fa-check"></i> ' + i18next.t("Save"),
                className: "btn btn-primary pull-left",
                callback: function () {
                    let Name = document.getElementById('Name').value;
                    let desc = document.getElementById('desc').value;
                    let state = document.getElementById('activ').checked;
                    
                    window.CRM.APIRequest({
                        method: 'POST',
                        path: 'volunteeropportunity/create',
                        data: JSON.stringify({ "Name": Name, "desc": desc, "state": state })
                    }, function (data) {
                        window.CRM.VolunteerOpportunityTable.ajax.reload(function() {
                            loadTableEvents();
                        });
                    });
                }
            }
        ],
        show: false,
        onEscape: function () {
            modal.modal("hide");
        }
    });

    modal.modal("show");
});

const loadTableEvents = () => {

    window.CRM.ElementListener('.delete-volunteer-opportunity', 'click', function (event) {
        let id = event.currentTarget.dataset.id;
        
        bootbox.confirm({
            title: i18next.t("Attention"),
            size: "large",
            message: i18next.t("If you delete the Menu Link, <u><b>you'll lose all the connected datas.</b></u><br><b>Are you sure? This action can't be undone.</b>"),
            callback: function (result) {
                if (result) {
                    window.CRM.APIRequest({
                        method: 'POST',
                        path: 'volunteeropportunity/delete',
                        data: JSON.stringify({ "id": id })
                    }, function (data) {
                        window.CRM.VolunteerOpportunityTable.ajax.reload(function() {
                            loadTableEvents();
                        });
                    });
                }
            }
        });
    });
    
    window.CRM.ElementListener('.edit-volunteer-opportunity', 'click', function (event) {
        let id = event.currentTarget.dataset.id;
    
        window.CRM.APIRequest({
            method: 'POST',
            path: 'volunteeropportunity/edit',
            data: JSON.stringify({ "id": id })
        }, function (data) {
            var modal = bootbox.dialog({
                message: BootboxContentVolunteerOpportunity,
                title: i18next.t("Custom Menu Link Editor"),
                size: "large",
                buttons: [
                    {
                        label: '<i class="fas fa-times"></i> ' + i18next.t("Close"),
                        className: "btn btn-default pull-left",
                        callback: function () {
                            console.log("just do something on close");
                        }
                    },
                    {
                        label: '<i class="fas fa-check"></i> ' + i18next.t("Save"),
                        className: "btn btn-primary pull-left",
                        callback: function () {
                            let Name = document.getElementById('Name').value;
                            let desc = document.getElementById('desc').value;
                            let state = document.getElementById('activ').checked;
                        
                            window.CRM.APIRequest({
                                method: 'POST',
                                path: 'volunteeropportunity/set',
                                data: JSON.stringify({ "id": id, "Name": Name, "desc": desc, "state": state })
                            }, function (data) {
                                window.CRM.VolunteerOpportunityTable.ajax.reload(function(){
                                    loadTableEvents();
                                });
                            });
                        }
                    }
                ],
                show: false,
                onEscape: function () {
                    modal.modal("hide");
                }
            });
    
            document.getElementById('Name').value = data.Name;
            document.getElementById('desc').value = data.Description;
            if (data.Active == "true")
                document.getElementById('activ').checked = true;
            else
                document.getElementById('activ').checked = false
    
            modal.modal("show");
        });
    });        

    const selectHierarchy = (btn) => {
        let parentId = btn.value;
        let voldId = btn.dataset.id;
    
        window.CRM.APIRequest({
            method: 'POST',
            path: 'volunteeropportunity/changeParent',
            data: JSON.stringify({ "voldId": voldId, "parentId": parentId })
        }, function (data) {
            window.CRM.VolunteerOpportunityTable.ajax.reload(function() {
                loadTableEvents();
            });
        });
    }    

    const selectColor = (btn) => {
        let colId = btn.dataset.id;
        let voldId = btn.dataset.voldId;
    
        window.CRM.APIRequest({
            method: 'POST',
            path: 'volunteeropportunity/changeColor',
            data: JSON.stringify({ "voldId": voldId, "colId": colId })
        }, function (data) {
            window.CRM.VolunteerOpportunityTable.ajax.reload(function() {
                loadTableEvents();
            });
        });
    }

    const selectIcon = (btn) => {
        let iconId = btn.dataset.id;
        let voldId = btn.dataset.voldId;
        
        window.CRM.APIRequest({
            method: 'POST',
            path: 'volunteeropportunity/changeIcon',
            data: JSON.stringify({ "voldId": voldId, "iconId": iconId })
        }, function (data) {
            window.CRM.VolunteerOpportunityTable.ajax.reload(function() {
                loadTableEvents();
            });
        });
    }


    document.querySelector('#VolunteerOpportunityTable').addEventListener('change', function (e) {
        const btn = e.target.closest('.selectHierarchy');
        if (btn !== null) {
            selectHierarchy(btn);
        }
    });

    document.querySelector('#VolunteerOpportunityTable').addEventListener('click', function (e) {
        const btn = e.target.closest('.selectColor');
        if (btn !== null) {
            selectColor(btn);
        } else {
            const btn = e.target.closest('.selectIcon');
            
            if (btn !== null) {
               selectIcon(btn);
            }            
        }
    });
}

window.CRM.VolunteerOpportunityTable = new DataTable("#VolunteerOpportunityTable", {
    ajax: {
        url: window.CRM.root + "/api/volunteeropportunity/",
        type: 'POST',
        contentType: "application/json",
        dataSrc: "VolunteerOpportunities",
        "beforeSend": function (xhr) {
            xhr.setRequestHeader('Authorization',
                "Bearer " + window.CRM.jwtToken
            );
        }
    },
    "order": [[1, "asc"]],
    "language": {
        "url": window.CRM.plugin.dataTable.language.url
    },
    initComplete: function (settings, json) {
        loadTableEvents();
    },
    columns: [
        {
            width: 'auto',
            title: i18next.t('Actions'),
            data: 'Id',
            searchable: false,
            render: function (data, type, full, meta) {
                return `<div class="btn-group btn-group-sm" role="group" aria-label="${i18next.t('Volunteer Opportunity actions')}">
                            <a href="${window.CRM.root}/v2/volunteeropportunity/${full.Id}/view" class="btn btn-outline-primary" title="${i18next.t('View')}">
                                <i class="fas fa-search"></i>
                            </a>
                            <a class="btn btn-outline-secondary edit-volunteer-opportunity" data-id="${data}" title="${i18next.t('Edit')}">
                                <i class="fas fa-pencil-alt"></i>
                            </a>
                            <a class="btn btn-outline-danger delete-volunteer-opportunity" data-id="${data}" title="${i18next.t('Delete')}">
                                <i class="far fa-trash-alt"></i>
                            </a>
                        </div>`;
            }
        },
        {
            width: 'auto',
            title: i18next.t('Name'),
            data: 'Name',
            render: function (data, type, full, meta) {
                return '<a href="' + window.CRM.root + '/v2/volunteeropportunity/' + full.Id+ '/view">' + data + '</a>';
            }
        },
        {
                width: '90px',
                title: i18next.t('Members'),
                data: 'MemberCount',
                searchable: false,
                defaultContent: "0",
                render: function (data) {
                    return `<span class="badge badge-pill badge-light border">${data || 0}</span>`;
                }
        },
        {
            width: 'auto',
            title: i18next.t('Volunteer Cart Status'),
            searchable: false,
            data: 'Id',
            render: function (data, type, full) {
                var disabled = full.memberCount == 0 ? ' disabled' : '';
                if ($.inArray(full.Id, window.CRM.volunteersInCart) > -1) {
                    return `<div class="d-flex align-items-center gap-1" id="volspanid-${full.Id}">`
                        + `<span class="badge badge-success"><i class="fas fa-check mr-1"></i>${i18next.t("In cart")}</span>`
                        + `&nbsp;<button class="btn btn-sm btn-outline-danger" id="removeVolunteerFromCart" data-volid="${full.Id}" title="${i18next.t('Remove from cart')}">`
                        +   `<i class="fas fa-times"></i>`
                        + `</button></div>`;
                } else if (window.CRM.showCart) {
                    return `<div class="d-flex align-items-center gap-1" id="volspanid-${full.Id}">`
                        + `<span class="badge badge-light border text-muted"><i class="fas fa-shopping-cart mr-1"></i>${i18next.t("Not in cart")}</span>`
                        + `&nbsp;<button class="btn btn-sm btn-outline-primary${disabled}" id="AddVolunteerToCart" data-volid="${full.Id}" title="${i18next.t('Add to cart')}">`
                        +   `<i class="fas fa-cart-plus"></i>`
                        + `</button></div>`;
                } else {
                    return `<span class="text-muted small"><i class="fas fa-ban mr-1"></i>${i18next.t("Cart isn't showable")}</span>`;
                }
            }
        },
        {
            width: 'auto',
            title: i18next.t('Description'),
            data: 'Description',
            render: function (data, type, full, meta) {
                return data;
            }
        },
        {
            width: 'auto',
            title: i18next.t('Activ'),
            data: 'Active',
            searchable: false,
            render: function (data, type, full, meta) {
                return (data == "true") ? `<span style="color:green"><i class="fa-solid fa-check"></i></span>` : `<span style="color:red"><i class="fas fa-ban"></i></span>`;                
            }
        },
        {
            width: 'auto',
            title: i18next.t('Parent (hierarchy)'),
            data: 'MenuParents',
            searchable: false,
            render: function (data, type, full, meta) {
                return data;
            }
        },
        {
            width: 'auto',
            title: i18next.t('Icon'),
            data: 'MenuIcons',
            searchable: false,
            render: function (data, type, full, meta) {
                return data;
            }
        },
        {
            width: 'auto',
            title: i18next.t('Color'),
            data: 'MenuColors',
            searchable: false,
            render: function (data, type, full, meta) {
                return data;
            }
        }
    ],
    responsive: true
});

$(document).on("click", "#AddVolunteerToCart", function () {
    var volId = $(this).data("volid");
    var $row = $("#volspanid-" + volId);
    var $btn = $(this);
    window.CRM.cart.addVolunteers(volId, function () {
        $btn.attr("id", "removeVolunteerFromCart")
            .removeClass("btn-outline-primary").addClass("btn-outline-danger")
            .attr("title", i18next.t("Remove from cart"))
            .find("i").removeClass("fa-cart-plus").addClass("fa-times");
        $row.find(".badge").removeClass("badge-light border text-muted").addClass("badge-success")
            .html('<i class="fas fa-check mr-1"></i>' + i18next.t("In cart"));
    });
});

$(document).on("click", "#removeVolunteerFromCart", function () {
    var volId = $(this).data("volid");
    var $row = $("#volspanid-" + volId);
    var $btn = $(this);
    window.CRM.cart.removeVolunteers(volId, function () {
        $btn.attr("id", "AddVolunteerToCart")
            .removeClass("btn-outline-danger").addClass("btn-outline-primary")
            .attr("title", i18next.t("Add to cart"))
            .find("i").removeClass("fa-times").addClass("fa-cart-plus");
        $row.find(".badge").removeClass("badge-success").addClass("badge-light border text-muted")
            .html('<i class="fas fa-shopping-cart mr-1"></i>' + i18next.t("Not in cart"));
    });
});