
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

/* IMPORTANT : be careful
     This will work in cartToGroup code */
const BootboxContentVolunteerOpportunity = () => {
    var frm_str = '<div class="card-body">'
        + '<div class="row">'
        + '  <div class="col-lg-2">'
        + '    <label>' + i18next.t("Name") + '</label>'
        + '  </div>'
        + '  <div class="col-lg-10">'
        + '    <input class="form-control form-control-sm" name="Name" id="Name" style="width:100%">'
        + '  </div>'
        + '</div>'
        + '<div class="row">'
        + '  <div class="col-lg-2">'
        + '    <label>' + i18next.t("Description") + '</label>'
        + '  </div>'
        + '  <div class="col-lg-10">'
        + '    <input class="form-control form-control-sm" name="desc" id="desc" style="width:100%">'
        + '  </div>'
        + '</div>'
        + '<div class="row">'
        + '  <div class="col-lg-2">'
        + '<input type="checkbox"  id="activ" class="ibtn">'
        + '  </div>'
        + '  <div class="col-lg-10">'
        + '    <label for="depositComment">' + i18next.t("Activ") + '</label>'
        + '  </div>'
        + '</div>'
        + '</div>';

    return frm_str
}

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
    
    window.CRM.ElementListener('.selectHierarchy', 'change', function (event) {
        let parentId = event.currentTarget.value;
        let voldId = event.currentTarget.dataset.id
        
        window.CRM.APIRequest({
            method: 'POST',
            path: 'volunteeropportunity/changeParent',
            data: JSON.stringify({ "voldId": voldId, "parentId": parentId })
        }, function (data) {
            window.CRM.VolunteerOpportunityTable.ajax.reload(function() {
                loadTableEvents();
            });
        });
    });
    
    window.CRM.ElementListener('.selectIcon', 'change', function (event) {
        let iconId = event.currentTarget.value;
        let voldId = event.currentTarget.dataset.id
        
        window.CRM.APIRequest({
            method: 'POST',
            path: 'volunteeropportunity/changeIcon',
            data: JSON.stringify({ "voldId": voldId, "iconId": iconId })
        }, function (data) {
            window.CRM.VolunteerOpportunityTable.ajax.reload(function() {
                loadTableEvents();
            });
        });
    });
    
    window.CRM.ElementListener('.selectColor', 'change', function (event) {
        let colId = event.currentTarget.value;
        let voldId = event.currentTarget.dataset.id
    
        window.CRM.APIRequest({
            method: 'POST',
            path: 'volunteeropportunity/changeColor',
            data: JSON.stringify({ "voldId": voldId, "colId": colId })
        }, function (data) {
            window.CRM.VolunteerOpportunityTable.ajax.reload(function() {
                loadTableEvents();
            });
        });
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
    drawCallback: function (settings) {
        loadTableEvents();
    },
    columns: [
        {
            width: 'auto',
            title: i18next.t('Actions'),
            data: 'Id',
            searchable: false,
            render: function (data, type, full, meta) {
                return '<a class="edit-volunteer-opportunity" data-id="' + data + '"><i class="fas fa-pencil-alt" aria-hidden="true"></i></a>&nbsp;&nbsp;&nbsp;<a class="delete-volunteer-opportunity" data-id="' + data + '"><i class="far fa-trash-alt" aria-hidden="true" style="color:red"></i></a>';
            }
        },
        {
            width: 'auto',
            title: i18next.t('Name'),
            data: 'Name',
            render: function (data, type, full, meta) {
                return data;
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
                return (data == "true") ? '<span style="color:green"><i class="fa-solid fa-check"></i></span>' : '<span style="color:red"><i class="fas fa-ban"></i></span>';                
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