$(function() {
    $(".groupSpecificProperties").on('click',function (e) {
        var groupPropertyAction = e.currentTarget.id;
        if (groupPropertyAction == "enableGroupProps") {
            $("#groupSpecificPropertiesModal").modal("show");
            $("#gsproperties-label").text(i18next.t('Confirm Enable Group Specific Properties'));
            $("#groupSpecificPropertiesModal .modal-body span").text(i18next.t('This will create a group-specific properties table for this group. You should then add needed properties with the Group-Specific Properties Form Editor.'));
            $("#setgroupSpecificProperties").text(i18next.t('Enable Group Specific Properties'));
            $("#setgroupSpecificProperties").data("action", 1);
        } else {
            $("#groupSpecificPropertiesModal").modal("show");
            $("#gsproperties-label").text(i18next.t('Confirm Disable Group Specific Properties'));
            $("#groupSpecificPropertiesModal .modal-body span").text(i18next.t('Are you sure you want to remove the group-specific person properties? All group member properties data will be lost!'));
            $("#setgroupSpecificProperties").text(i18next.t('Disable Group Specific Properties'));
            $("#setgroupSpecificProperties").data("action", 0);
        }
    });

    $("#setgroupSpecificProperties").on('click',function (e) {
        var action = $("#setgroupSpecificProperties").data("action");

        window.CRM.APIRequest({
            method: 'POST',
            path: 'groups/' + window.CRM.groupID + "/setGroupSpecificPropertyStatus",
            data: JSON.stringify({"GroupSpecificPropertyStatus": action})
        }, function (data) {
            location.reload(); // this shouldn't be necessary
        });
    });


    $("#selectGroupIDDiv").hide();

    $("#cloneGroupRole").on('click',function (e) {
        if (e.target.checked)
            $("#selectGroupIDDiv").show();
        else {
            $("#selectGroupIDDiv").hide();
            $("#seedGroupID").prop('selectedIndex', 0);
        }
    });

    $("#groupEditForm").on('submit',function (e) {
        e.preventDefault();

        var formData = {
            "groupName": $("input[name='Name']").val(),
            "description": $("textarea[name='Description']").val(),
            "groupType": $("select[name='GroupType'] option:selected").val()
        };

        window.CRM.APIRequest({
            method: "POST",
            path: "groups/" + window.CRM.groupID,
            data: JSON.stringify(formData),
        },function (data) {
            if (data.Type == 4) {
                window.location.href = window.CRM.root + "/v2/sundayschool/" + window.CRM.groupID + "/view";
            } else {
                window.location.href = window.CRM.root + "/v2/group/" + window.CRM.groupID + "/view";
            }
        });

    });

    $("#addNewRole").on('click',function (e) {
        var newRoleName = $("#newRole").val();

        window.CRM.APIRequest({
            method: "POST",
            path: "groups/" + window.CRM.groupID + "/roles",
            data: JSON.stringify({"roleName": newRoleName }),
        },function (data) {
            window.CRM.dataT.ajax.reload();
            $("#newRole").val('');
            window.CRM.roleCount++;
            //location.reload(); // this shouldn't be necessary
        });

    });

    $(document).on('click', '.deleteRole', function (e) {
        var roleID = e.currentTarget.id.split("-")[1];

        var numberOfRows = window.CRM.dataT.data().count();

        if (numberOfRows > 1) {
            bootbox.confirm({
                title: i18next.t("Confirm Delete Role"),
                message: '<p style="color: red">' +
                    i18next.t("This will also delete all persons membership associated with this role.") +
                    "</p>",
                callback: function (result) {
                    if (result) {
                        window.CRM.APIRequest({
                            method: "DELETE",
                            path: "groups/" + window.CRM.groupID + "/roles/" + roleID,
                        },function (data) {
                            window.CRM.dataT.ajax.reload();
                            window.CRM.roleCount--;
                            if (roleID == defaultRoleID)        // if we delete the default group role, set the default group role to 1 before we tell the table to re-render so that the buttons work correctly
                                defaultRoleID = 1;
                        });
                    }
                }
            });
        } else {
            bootbox.alert(i18next.t("A group should have at least one role!"));
        }

    });

    $(document).on('click', '.rollOrder', function (e) {

        var roleID = e.currentTarget.id.split("-")[1]; // get the ID of the role that we're manipulating
        var roleSequenceAction = e.currentTarget.id.split("-")[0];  //determine whether we're increasing or decreasing this role's sequence number
        var newRoleSequence = 0;      //create a variable at the function scope to store the new role's sequence
        var currentRoleSequence = window.CRM.dataT.cell(function (idx, data, node) {
            if (data.OptionId == roleID) {
                return true;
            }
        }, 2).data(); //get the sequence number of the selected role
        if (roleSequenceAction == "roleUp") {
            newRoleSequence = Number(currentRoleSequence) - 1;  //decrease the role's sequence number
        } else if (roleSequenceAction == "roleDown") {
            newRoleSequence = Number(currentRoleSequence) + 1; // increase the role's sequenc number
        }

        replaceRow = window.CRM.dataT.row(function (idx, data, node) {
            if (data.OptionSequence == newRoleSequence) {
                return true;
            }
        });
        var d = replaceRow.data();
        d.OptionSequence = currentRoleSequence;
        setGroupRoleOrder(window.CRM.groupID, d.OptionId, d.OptionSequence);
        replaceRow.data(d);

        window.CRM.dataT.cell(function (idx, data, node) {
            if (data.OptionId == roleID) {
                return true;
            }
        }, 2).data(newRoleSequence); // set our role to the new sequence number
        setGroupRoleOrder(window.CRM.groupID, roleID, newRoleSequence);
        window.CRM.dataT.rows().invalidate().draw(true);
        window.CRM.dataT.order([[2, "asc"]]).draw();

    });

    $(document).on('change', '.roleName', function (e) {

        var groupRoleName = e.target.value;
        var roleID = e.target.id.split("-")[1];
        window.CRM.APIRequest({
            method: "POST",
            path: "groups/" + window.CRM.groupID + "/roles/" + roleID,
            data: JSON.stringify({"groupRoleName": groupRoleName })
        },function (data) {
            window.CRM.DisplayAlert(i18next.t("Role Name"), i18next.t("The role name is now modified."));
        });

    });

    $(document).on('click', '.defaultRole', function (e) {
        var roleID = e.target.id.split("-")[1];
        window.CRM.APIRequest({
            method: "POST",
            path: "groups/" + window.CRM.groupID + "/defaultRole",
            data: JSON.stringify({"roleID": roleID })
        },function (data) {
            defaultRoleID = roleID; //update the local variable representing the default role id
            window.CRM.dataT.rows().invalidate().draw(true);
            // re-register the JQuery handlers since we changed the DOM, and new buttons will not have an action bound.
        });
    });

    window.CRM.dataT = $("#groupRoleTable").DataTable({
        "language": {
            "url": window.CRM.plugin.dataTable.language.url
        },
        ajax: {
            url: window.CRM.root + "/api/groups/" + window.CRM.groupID + "/roles",
            type: 'GET',
            contentType: "application/json",
            dataSrc: "ListOptions",
            "beforeSend": function (xhr) {
                xhr.setRequestHeader('Authorization',
                    "Bearer " +  window.CRM.jwtToken
                );
            }
        },
        columns: [
            {
                width: 'auto',
                title: i18next.t("Role Name"),
                data: 'OptionName',
                render: function (data, type, full, meta) {
                    if (type === 'display') {
                        if (data === 'Student' || data === 'Teacher')
                            return '<input type="text" class="form-control form-control-sm" id="roleName-' + full.OptionId + '" value="' + i18next.t(data) + '" readonly>';
                        else
                            return '<input type="text" class="form-control form-control-sm roleName" id="roleName-' + full.OptionId + '" value="' + data + '">';
                    } else
                        return data;
                }
            },
            {
                width: 'auto',
                title: i18next.t("Make Default"),
                data: 'Id',
                render: function (data, type, full, meta) {
                    if (full.OptionId == defaultRoleID) {
                        return "<strong><i class=\"fas fa-check\"></i>" + i18next.t("Default") + "</strong>";
                    } else {
                        return '<button type="button" id="defaultRole-' + full.OptionId + '" class="btn btn-sm btn-success defaultRole">' + i18next.t("Default") + '</button>';
                    }
                }
            },
            {
                width: '200px',
                title: i18next.t("Sequence"),
                data: 'OptionSequence',
                className: "dt-body-center",
                render: function (data, type, full, meta) {
                    if (type === 'display') {
                        var sequenceCell = "";
                        if (data > 1) {
                            sequenceCell += '<button type="button" id="roleUp-' + full.OptionId + '" class="btn btn-sm rollOrder"> <i class="fas fa-arrow-up"></i></button>&nbsp;';
                        }
                        sequenceCell += data;
                        if (data != window.CRM.roleCount) {
                            sequenceCell += '&nbsp;<button type="button" id="roleDown-' + full.OptionId + '" class="btn  btn-sm rollOrder"> <i class="fas fa-arrow-down"></i></button>';
                        }
                        return sequenceCell;
                    } else {
                        return data;
                    }
                }
            },
            {
                width: 'auto',
                title: i18next.t("Delete"),
                data: 'Id',
                render: function (data, type, full, meta) {
                    if (full.OptionName === 'Student' || full.OptionName === 'Teacher')
                        return '<button type="button" id="roleDelete-' + full.OptionId + '" class="btn  btn-sm btn-danger deleteRole" disabled><i class="far fa-trash-alt" aria-hidden="true"></i></button>';
                    else
                        return '<button type="button" id="roleDelete-' + full.OptionId + '" class="btn  btn-sm btn-danger deleteRole"><i class="far fa-trash-alt" aria-hidden="true"></i></button>';

                }
            },
        ],
        "order": [[2, "asc"]]
    });

    // initialize the event handlers when the document is ready.  Don't do it here, since we need to be able to initialize these handlers on the fly in response to user action.
});


function setGroupRoleOrder(groupID, roleID, groupRoleOrder) {
    window.CRM.APIRequest({
        method: "POST",
        path: "groups/" + groupID + "/roles/" + roleID,
        data: JSON.stringify({"groupRoleOrder": groupRoleOrder })
    },function (data) {
    });
}
