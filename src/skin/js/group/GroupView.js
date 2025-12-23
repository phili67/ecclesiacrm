//
//  This code is under copyright not under MIT Licence
//  copyright   : 2020 Philippe Logel all right reserved not MIT licence
//                This code can't be included in another software.
//  Updated     : 2020/10/17
//
$(function() {

    window.CRM.APIRequest({
        method: "GET",
        path: "groups/" + window.CRM.currentGroup + "/roles",
    }, function (data) {
        window.CRM.groupRoles = data.ListOptions;
        $("#newRoleSelection").select2({
            data: $(window.CRM.groupRoles).map(function () {
                return {
                    id: this.OptionId,
                    text: this.OptionName
                };
            })
        });
        initDataTable();
    });

    window.CRM.dataPropertiesTable = $("#AssignedPropertiesTable").DataTable({
        ajax: {
            url: window.CRM.root + "/api/groups/groupproperties/" + window.CRM.currentGroup,
            type: 'POST',
            contentType: "application/json",
            dataSrc: "Record2propertyR2ps",
            "beforeSend": function (xhr) {
                xhr.setRequestHeader('Authorization',
                    "Bearer " +  window.CRM.jwtToken
                );
            }
        },
        select:false,
        info: false,
        "language": {
            "url": window.CRM.plugin.dataTable.language.url
        },
        "searching": false,
        columns: [
            {
                width: 'auto',
                title: i18next.t('Name'),
                data: 'ProName',
                render: function (data, type, full, meta) {
                    return i18next.t(data);
                }
            },
            {
                width: 'auto',
                title: i18next.t('Value'),
                data: 'R2pValue',
                render: function (data, type, full, meta) {
                    return data;
                }
            },
            {
                width: 'auto',
                title: i18next.t('Action'),
                data: 'ProId',
                render: function (data, type, full, meta) {
                    var ret = '';
                    if (full.ProPrompt != '') {
                        ret += '<a class="edit-property-btn" data-group_id="' + window.CRM.currentGroup + '" data-property_id="' + data + '" data-property_Name="' + full.R2pValue + '"><i class="fas fa-pencil-alt" aria-hidden="true"></i></a>&nbsp;&nbsp;&nbsp;';
                    }

                    return ret + '<a class="remove-property-btn" data-group_id="' + window.CRM.currentGroup + '" data-property_id="' + data + '" data-property_Name="' + full.R2pValue + '"><i class="far fa-trash-alt" aria-hidden="true" style="color:red"></i></a>';
                }
            }
        ],
        responsive: true,
        createdRow: function (row, data, index) {
            $(row).addClass("paymentRow");
        }
    });

    $('#isGroupActive').prop('checked', window.CRM.isActive).on('change');
    $('#isGroupEmailExport').prop('checked', window.CRM.isIncludeInEmailExport).on('change');

    $("#deleteGroupButton").on('click', function () {
        console.log("click");
        bootbox.setDefaults({
            locale: window.CRM.shortLocale
        }),
            bootbox.confirm({
                title: i18next.t("Confirm Delete Group"),
                message: '<p style="color: red">' +
                    i18next.t("Please confirm deletion of this group record") + window.CRM.groupName + "</p>" +
                    "<p>" +
                    i18next.t("This will also delete all Roles and Group-Specific Property data associated with this Group record.") +
                    "</p><p>" +
                    i18next.t("All group membership and properties will be destroyed.  The group members themselves will not be altered.") + "</p>",
                callback: function (result) {
                    if (result) {
                        window.CRM.APIRequest({
                            method: "DELETE",
                            path: "groups/" + window.CRM.currentGroup,
                        }, function (data) {
                            if (data.status == "success")
                                window.location.href = window.CRM.root + "/v2/group/list";
                        });
                    }
                }
            });
    });

    $(".input-group-properties").select2({
        language: window.CRM.shortLocale
    });

    $('body').on('click', '.assign-property-btn', function () {
        var property_id = $('.input-group-properties').val();
        var property_pro_value = $('.property-value').val();

        window.CRM.APIRequest({
            method: 'POST',
            path: 'properties/groups/assign',
            data: JSON.stringify({
                "GroupId": window.CRM.currentGroup,
                "PropertyId": property_id,
                "PropertyValue": property_pro_value
            })
        }, function (data) {
            if (data && data.success) {
                window.CRM.dataPropertiesTable.ajax.reload();
                promptBox.removeClass('form-group').html('');
            }
        });
    });


    $('body').on('click', '.remove-property-btn', function () {
        event.preventDefault();
        var thisLink = $(this);
        var group_id = thisLink.data('group_id');
        var property_id = thisLink.data('property_id');

        bootbox.confirm({
            buttons: {
                cancel: {
                    label: i18next.t('Cancel'),
                    className: 'btn btn-primary'
                },
                confirm: {
                    label: i18next.t('OK'),
                    className: 'btn btn-danger'
                }
            },
            title: i18next.t('Are you sure you want to unassign this property?'),
            message: i18next.t('This action can never be undone !!!!'),
            callback: function (result) {
                if (result) {
                    window.CRM.APIRequest({
                        method: 'DELETE',
                        path: 'properties/groups/unassign',
                        data: JSON.stringify({"GroupId": group_id, "PropertyId": property_id})
                    }, function (data) {
                        if (data && data.success) {
                            window.CRM.dataPropertiesTable.ajax.reload()
                        }
                    });
                }
            }
        });
    });

    $('body').on('click', '.edit-property-btn', function () {
        event.preventDefault();
        var thisLink = $(this);
        var group_id = thisLink.data('group_id');
        var property_id = thisLink.data('property_id');
        var property_name = thisLink.data('property_name');

        bootbox.prompt({
            buttons: {
                confirm: {
                    label: i18next.t('OK'),
                    className: 'btn btn-primary'
                },
                cancel: {
                    label: i18next.t('Cancel'),
                    className: 'btn btn-default'
                }
            },
            title: i18next.t('Are you sure you want to change this property?'),
            value: property_name,
            callback: function (result) {
                if (result) {
                    window.CRM.APIRequest({
                        method: 'POST',
                        path: 'properties/groups/assign',
                        data: JSON.stringify({"GroupId": group_id, "PropertyId": property_id, "PropertyValue": result})
                    }, function (data) {
                        if (data && data.success) {
                            window.CRM.dataPropertiesTable.ajax.reload()
                        }
                    });
                }
            }
        });
    });

    $(".input-group-properties").on("select2:select", function (event) {
        promptBox = $("#prompt-box");
        promptBox.removeClass('form-group').html('');
        selected = $(".input-group-properties :selected");
        pro_prompt = selected.data('pro_prompt');
        pro_value = selected.data('pro_value');
        if (pro_prompt) {
            promptBox
                .addClass('form-group')
                .append(
                    $('<label style="color:white"></label>').html(pro_prompt)
                )
                .append(
                    $('<textarea rows="3" class="form-control property-value" name="PropertyValue"></textarea>').val(pro_value)
                );
        }

    });


    $(".personSearch").select2({
        minimumInputLength: 2,
        language: window.CRM.shortLocale,
        minimumInputLength: 2,
        placeholder: " -- " + i18next.t("Person") + " -- ",
        allowClear: true, // This is for clear get the clear button if wanted
        ajax: {
            url: function (params) {
                return window.CRM.root + "/api/persons/search/" + params.term;
            },
            dataType: 'json',
            delay: 250,
            headers: {
                "Authorization" : "Bearer "+window.CRM.jwtToken
            },
            data: function (params) {
                return {
                    q: params.term, // search term
                    page: params.page
                };
            },
            processResults: function (rdata, page) {
                return {results: rdata};
            },
            cache: true
        }
    });

    $(".personSearch").on("select2:select", function (e) {
        window.CRM.groups.promptSelection({
            Type: window.CRM.groups.selectTypes.Role,
            GroupID: window.CRM.currentGroup
        }, function (selection) {
            window.CRM.groups.addPerson(window.CRM.currentGroup, e.params.data.objid, selection.RoleID, function (data) {
                if (data.status == "failed") {
                    window.CRM.DisplayAlert(i18next.t("Error"), i18next.t("A kid should have a family in a sunday school group !"));
                } else {
                    $(".personSearch").val(null).trigger('change');
                    window.CRM.DataTableGroupView.ajax.reload();/* we reload the data no need to add the person inside the dataTable */
                }
            });
        });
    });

    $("#addSelectedToCart").on('click', function () {
        if (window.CRM.DataTableGroupView.rows('.selected').length > 0) {
            var selectedPersons = {
                "Persons": $.map(window.CRM.DataTableGroupView.rows('.selected').data(), function (val, i) {
                    return val.PersonId;
                })
            };
            window.CRM.cart.addPerson(selectedPersons.Persons);
        }

    });

    //copy membership
    $("#addSelectedToGroup").on('click', function () {
        window.CRM.groups.promptSelection({Type: window.CRM.groups.selectTypes.Group | window.CRM.groups.selectTypes.Role}, function (data) {
            selectedRows = window.CRM.DataTableGroupView.rows('.selected').data()
            $.each(selectedRows, function (index, value) {
                window.CRM.groups.addPerson(data.GroupID, value.PersonId, data.RoleID);
            });
        });
    });

    $("#moveSelectedToGroup").on('click', function () {
        window.CRM.groups.promptSelection({Type: window.CRM.groups.selectTypes.Group | window.CRM.groups.selectTypes.Role}, function (data) {
            selectedRows = window.CRM.DataTableGroupView.rows('.selected').data()
            $.each(selectedRows, function (index, value) {
                console.log(data);
                window.CRM.groups.addPerson(data.GroupID, value.PersonId, data.RoleID);
                window.CRM.groups.removePerson(window.CRM.currentGroup, value.PersonId, function () {
                        window.CRM.DataTableGroupView.row(function (idx, data, node) {
                            if (data.PersonId == value.PersonId) {
                                return true;
                            }
                        }).remove();
                        window.CRM.DataTableGroupView.rows().invalidate().draw(true);
                    });
            });
        });
    });

    $(document).on("click", ".changeMembership", function (e) {
        var PersonID = $(e.currentTarget).data("personid");
        window.CRM.groups.promptSelection({
            Type: window.CRM.groups.selectTypes.Role,
            GroupID: window.CRM.currentGroup
        }, function (selection) {
            window.CRM.groups.addPerson(window.CRM.currentGroup, PersonID, selection.RoleID, function () {
                window.CRM.DataTableGroupView.row(function (idx, data, node) {
                    if (data.PersonId == PersonID) {
                        data.RoleId = selection.RoleID;
                        return true;
                    }
                });
                window.CRM.DataTableGroupView.rows().invalidate().draw(true);
            });
        });
        e.stopPropagation();
    });

});

function initDataTable() {
    var DataTableOpts = {
        ajax: {
            url: window.CRM.root + "/api/groups/" + window.CRM.currentGroup + "/members",
            error: function (data) { 
                window.CRM.DisplayAlert(i18next.t("Error"), i18next.t("401 : Private Datas"));
            },
            dataSrc: "Person2group2roleP2g2rs",
            "beforeSend": function (xhr) {
                xhr.setRequestHeader('Authorization',
                    "Bearer " +  window.CRM.jwtToken
                );
            }
        },
        "language": {
            "url": window.CRM.plugin.dataTable.language.url
        },
        responsive: true,
        select: true,
        columns: [
            {
                width: '220px',
                title: i18next.t('Name'),
                data: 'PersonId',
                render: function (data, type, full, meta) {
                    return full.Person.img + ' &nbsp <a href="' + window.CRM.root + '/v2/people/person/view/"' + full.PersonId + '"><a target="_top" href="' + window.CRM.root + '/v2/people/person/view/' + full.PersonId + '">' + full.Person.FirstName + " " + full.Person.LastName + '</a>';
                }
            },
            {
                width: 'auto',
                title: i18next.t('Group Role'),
                data: 'RoleId',
                render: function (data, type, full, meta) {
                    thisRole = $(window.CRM.groupRoles).filter(function (index, item) {
                        return item.OptionId == data
                    })[0];

                    if (isShowable) {
                        return  ' <a href="#" class="changeMembership btn btn-default btn-xs" data-personid=' + full.PersonId + '>'
                            +'<span class="fa-stack fa-stack-custom">'
                            +'<i class="fas fa-stack-1x fa-inverse fa-pencil-alt fas-blue"></i></a> ' + ((thisRole != undefined) ? i18next.t(thisRole.OptionName) : '');
                            +'</span>'
                    } else {
                        return i18next.t("Private Data");
                    }
                }
            },
            {
                width: 'auto',
                title: i18next.t('Address'),
                data: 'Person.Address1',
                render: function (data, type, full, meta) {
                    if (isShowable) {
                        return full.Person.Address1 + " " + full.Person.Address2;
                    } else {
                        return i18next.t("Private Data");
                    }
                }
            },
            {
                width: 'auto',
                title: i18next.t('City'),
                data: 'Person.City',
                render: function (data, type, full, meta) {
                    if (isShowable) {
                        return data;
                    } else {
                        return i18next.t("Private Data");
                    }
                }
            },
            {
                width: 'auto',
                title: i18next.t('State'),
                data: 'Person.State',
                render: function (data, type, full, meta) {
                    if (isShowable) {
                        return data;
                    } else {
                        return i18next.t("Private Data");
                    }
                }
            },
            {
                width: 'auto',
                title: i18next.t('Zip Code'),
                data: 'Person.Zip',
                render: function (data, type, full, meta) {
                    if (isShowable) {
                        return data;
                    } else {
                        return i18next.t("Private Data");
                    }
                }
            },
            {
                width: 'auto',
                title: i18next.t('Cell Phone'),
                data: 'Person.CellPhone',
                render: function (data, type, full, meta) {
                    if (isShowable) {
                        return data;
                    } else {
                        return i18next.t("Private Data");
                    }
                }
            },
            {
                width: 'auto',
                title: i18next.t('Email'),
                data: 'Person.Email',
                render: function (data, type, full, meta) {
                    if (isShowable) {
                        return data;
                    } else {
                        return i18next.t("Private Data");
                    }
                }
            }
        ],
        "fnDrawCallback": function (oSettings) {
            $("#iTotalMembers").text(oSettings.aoData.length);
        },
        "createdRow": function (row, data, index) {
            $(row).addClass("groupRow");
        }
    };

    if (window.CRM.isManageGroupsEnabled) {
        window.CRM.plugin.dataTable.buttons.push({
            text: '<i class="fas fa-trash-alt"></i> ' + i18next.t("Remove") + " (" + 0 + ") " + i18next.t("Members"),            
            attr: {
                title: 'Remove Selected Members from group',
                id: 'deleteSelectedRows'            
            },
            enabled: false,
            className: 'btn btn-danger',
            action: function (e, dt, node, config) {
                bootbox.confirm({
                    title: i18next.t("You're about to delete all the group members ?"),
                    message: i18next.t("Are you sure ? This can't be undone."),
                    buttons: {
                        cancel: {
                            label: i18next.t('No'),
                            className: 'btn-primary'
                        },
                        confirm: {
                            label: i18next.t('Yes'),
                            className: 'btn-danger'
                        }
                    },
                    callback: function (result) {
                        if (result) {
                            var selectedPersons = {
                                "Persons": $.map(window.CRM.DataTableGroupView.rows('.selected').data(), function (val, i) {
                                    return val.PersonId;
                                })
                            };
                            
                            window.CRM.APIRequest({
                                method: 'DELETE',
                                path: 'groups/removeselectedpersons',
                                data: JSON.stringify({
                                    "groupID": window.CRM.currentGroup,
                                    "Persons": selectedPersons
                                })
                            },function (data) {
                                window.CRM.DataTableGroupView.ajax.reload( () => {
                                    let selectedRows = window.CRM.DataTableGroupView.rows('.selected').data().length;
                                    if (selectedRows) {
                                        $("#deleteSelectedRows").removeClass('disabled');
                                        $("#addSelectedToGroup").removeClass('disabled');
                                    } else {
                                        $("#deleteSelectedRows").addClass('disabled');
                                        $("#addSelectedToGroup").addClass('disabled');
                                    }
                                    $("#deleteSelectedRows").html('<i class="fas fa-trash-alt"></i> ' + i18next.t("Remove") + " (" + selectedRows + ") " + i18next.t("Members"));
                                    $("#addSelectedToGroup").html('<i class="fas fa-cart-plus"></i> ' + i18next.t("Add") + "  (" + selectedRows + ") " + i18next.t("Members to cart"));
                                });                              
                            });                           
                        }
                    }
                });
            }
        });

        window.CRM.plugin.dataTable.buttons.push({
            text: '<i class="fas fa-cart-plus"></i> ' + i18next.t("Add") + "  (" + 0 + ") " + i18next.t("Members to cart"),
            extend: 'collection',
            className: 'btn btn-success',
            enabled: false,
            attr: {
                id: 'addSelectedToGroup',
                //style: "width: 300px"
            },            
            buttons: [
                {
                    text: i18next.t("Add to Cart"), 
                    action: function (e, dt, node, config) {
                        if (window.CRM.DataTableGroupView.rows('.selected').length > 0) {
                            var selectedPersons = {
                                "Persons": $.map(window.CRM.DataTableGroupView.rows('.selected').data(), function (val, i) {
                                    return val.PersonId;
                                })
                            };
                            window.CRM.cart.addPerson(selectedPersons.Persons);
                        }
                    }
                },                
                {
                    text: i18next.t("Add to Group"), 
                    action: function (e, dt, node, config) {
                        window.CRM.groups.promptSelection({Type: window.CRM.groups.selectTypes.Group | window.CRM.groups.selectTypes.Role}, function (data) {
                            selectedRows = window.CRM.DataTableGroupView.rows('.selected').data()
                            $.each(selectedRows, function (index, value) {
                                window.CRM.groups.addPerson(data.GroupID, value.PersonId, data.RoleID);
                            });
                        });
                    }
                },
                {
                    text: i18next.t("Move to Group"),
                    action: function (e, dt, node, config) {
                        window.CRM.groups.promptSelection({Type: window.CRM.groups.selectTypes.Group | window.CRM.groups.selectTypes.Role}, function (data) {
                            selectedRows = window.CRM.DataTableGroupView.rows('.selected').data()
                            $.each(selectedRows, function (index, value) {
                                console.log(data);
                                window.CRM.groups.addPerson(data.GroupID, value.PersonId, data.RoleID);
                                window.CRM.groups.removePerson(window.CRM.currentGroup, value.PersonId, function () {
                                        window.CRM.DataTableGroupView.row(function (idx, data, node) {
                                            if (data.PersonId == value.PersonId) {
                                                return true;
                                            }
                                        }).remove();
                                        window.CRM.DataTableGroupView.rows().invalidate().draw(true);
                                    });
                            });
                        });
                    }
                }
            ]            
        });

        
        window.CRM.plugin.dataTable.buttons.push({
            text: '<i class="fas fa-trash-alt"></i> '+ i18next.t("Remove all members"),
            className: 'btn btn-danger',
            action: function (e, dt, node, config) {
                bootbox.confirm({
                    title: i18next.t("You're about to delete all the group members ?"),
                    message: i18next.t("Are you sure ? This can't be undone."),
                    buttons: {
                        confirm: {
                            label: i18next.t('Yes'),
                            className: 'btn-danger'
                        },
                        cancel: {
                            label: i18next.t('No'),
                            className: 'btn-primary'
                        }                        
                    },
                    callback: function (result) {
                        if (result) {
                            window.CRM.APIRequest({
                                method: 'POST',
                                path: 'groups/emptygroup',
                                data: JSON.stringify({"groupID": groupID})
                            },function (data) {
                                window.CRM.DataTableGroupView.ajax.reload();/* we reload the data no need to add the person inside the dataTable */
                            });
                        }
                    }
                });
            }
        });        
    }

    $.extend(DataTableOpts, window.CRM.plugin.dataTable);
    
    window.CRM.DataTableGroupView = $("#membersTable").DataTable(DataTableOpts);

    $('#isGroupActive').on('change',function () {
        window.CRM.APIRequest({
            method: 'POST',
            path: 'groups/' + window.CRM.currentGroup + '/settings/active/' + $(this).prop('checked')
        }, function (selection) {
            location.reload();
        });
    });

    $('#isGroupEmailExport').on('change',function () {
        if ($(this).prop('checked')) {
            $(".sms-button").removeClass('disabled');
            $(".email-button").removeClass('disabled');
            $(".email-cci-button").removeClass('disabled');
            $(".export-vcard-button").removeClass('disabled');            

            $(".email-button-dropdown").prop('disabled', false);
            $(".email-cci-button-dropdown").prop('disabled', false);
        } else {
            $(".sms-button").addClass('disabled');
            $(".email-button").addClass('disabled');
            $(".email-cci-button").addClass('disabled');
            $(".export-vcard-button").addClass('disabled');

            $(".email-button-dropdown").prop('disabled', true);;
            $(".email-cci-button-dropdown").prop('disabled', true);;
        }

        window.CRM.APIRequest({
            method: 'POST',
            path: 'groups/' + window.CRM.currentGroup + '/settings/email/export/' + $(this).prop('checked')
        });
    });

    $(document).on('click', '.groupRow', function () {
        var selectedRows = window.CRM.DataTableGroupView.rows('.selected').data().length;
        if (selectedRows) {
            $("#deleteSelectedRows").removeClass('disabled');
            $("#addSelectedToGroup").removeClass('disabled');
        } else {
            $("#deleteSelectedRows").addClass('disabled');
            $("#addSelectedToGroup").addClass('disabled');
        }
        $("#deleteSelectedRows").html('<i class="fas fa-trash-alt"></i> ' + i18next.t("Remove") + " (" + selectedRows + ") " + i18next.t("Members"));
        $("#addSelectedToGroup").html('<i class="fas fa-cart-plus"></i> ' + i18next.t("Add") + "  (" + selectedRows + ") " + i18next.t("Members to cart"));
        
        $("#buttonDropdown").prop('disabled', !(selectedRows));        
        $("#moveSelectedToGroup").prop('disabled', !(selectedRows));
        $("#moveSelectedToGroup").html(i18next.t("Move") + "  (" + selectedRows + ") " + i18next.t("Members to another group"));
    });

    const addCartMemberAction = (clickedButton) => {
        $(clickedButton).addClass("RemoveFromGroupCart");
        $(clickedButton).removeClass("AddToGroupCart");
        $('i', clickedButton).addClass("fa-times");
        $('i', clickedButton).removeClass("fa-cart-plus");
        text = $(clickedButton).find("span.cartActionDescription");
        if (text) {
            $(text).text(i18next.t("Remove from Cart"));
        }
    }

    const removeCartMemberAction = (clickedButton) => {
        $(clickedButton).addClass("AddToGroupCart");
        $(clickedButton).removeClass("RemoveFromGroupCart");
        $('i', clickedButton).removeClass("fa-times");
        $('i', clickedButton).addClass("fa-cart-plus");
        text = $(clickedButton).find("span.cartActionDescription");
        if (text) {
            $(text).text(i18next.t("Add to Cart"));
        }
    }

    $(document).on("click", ".AddToGroupCart", function () {
        var clickedButton = $(this);
        window.CRM.cart.addGroup(clickedButton.data("cartgroupid"), function () {
            addCartMemberAction(clickedButton);
        });
    });

    $(document).on("click", ".RemoveFromGroupCart", function () {
        clickedButton = $(this);
        window.CRM.cart.removeGroup(clickedButton.data("cartgroupid"), function () {
           removeCartMemberAction (clickedButton);
        });
    });


    // newMessage event subscribers : Listener CRJSOM.js
    $(document).on("updateCartMessage", updateButtons);

    // newMessage event handler
    function updateButtons(e) {
        var clickedButton = $("#AddToGroupCart");
        if (e.people.length == 0) {
            removeCartMemberAction (clickedButton);
            
        } else {
            addCartMemberAction (clickedButton);
        }
    }


    // start manager
    $("#add-manager").on('click', function () {
        createManagerWindow(window.CRM.currentGroup);
    });

    $('body').on('click', '.delete-person-manager', function () {
        var personID = $(this).data('personid');
        var groupID = $(this).data('groupid');

        window.CRM.APIRequest({
            method: 'POST',
            path: 'groups/deleteManager',
            data: JSON.stringify({"groupID": groupID, "personID": personID})
        }, function (data) {
            if (data.status == undefined) {
                var len = data.length;

                var optionValues = '';

                for (i = 0; i < len; ++i) {
                    optionValues += '<button class="delete-person-manager btn btn-danger btn-xs" data-personid="' + data[i].personID + '" data-groupid="' + groupID + '"> <i sclass="icon far fa-trash-alt"></i> </button> '+ data[i].name + '<br/> ';
                }

                if (optionValues != '') {
                    $("#Manager-list").html(optionValues);
                } else {
                    $("#Manager-list").html(i18next.t("No assigned Manager") + ".");
                }
            } else {
                $("#Manager-list").html(i18next.t("No assigned Manager") + ".");
            }
        });
    });


    function BootboxContentManager() {
        var frm_str =  '<div>'
            + '<div class="row">'
            + '<div class="col-md-4">'
            + '<span style="color: red">*</span>' + i18next.t("With") + ":"
            + '</div>'
            + '<div class="col-md-8">'
            + '<select size="6" class="form-control GroupViewBootboxContentManager" id="select-manager-persons" multiple>'
            + '</select>'
            + '</div>'
            + '</div>'
            + '<div class="row">'
            + '<div class="col-md-4"><span style="color: red">*</span>' + i18next.t("Add users") + ":</div>"
            + '<div class="col-md-8">'
            + '<select name="person-manager-Id" id="person-manager-Id" class="form-control select2"'
            + 'style="width:100%">'
            + '</select>'
            + '</div>'
            + '</div>'
            + '</div>';

        var object = $('<div/>').html(frm_str).contents();

        return object
    }

    // the add people to calendar

    function addManagersFromGroup(groupID) {
        $('#select-manager-persons').find('option').remove();

        window.CRM.APIRequest({
            method: 'POST',
            path: 'groups/getmanagers',
            data: JSON.stringify({"groupID": groupID})
        }, function (data) {
            var elt = document.getElementById("select-manager-persons");
            var len = data.length;

            var optionValues = '';

            for (i = 0; i < len; ++i) {
                var option = document.createElement("option");

                option.text = data[i].name;
                option.value = data[i].personID;

                optionValues += '<button class="delete-person-manager btn btn-danger btn-xs" data-personid="' + data[i].personID + '" data-groupid="' + groupID + '"> <i class="icon far fa-trash-alt"></i> </button> ' + data[i].name + '<br/> ';

                elt.appendChild(option);
            }

            if (optionValues != '') {
                $("#Manager-list").html(optionValues);
            } else {
                $("#Manager-list").html(i18next.t("No assigned Manager") + ".");
            }
        });
    }

    function createManagerWindow(groupID) {
        var modal = bootbox.dialog({
            title: i18next.t("Manage Group Managers"),
            message: BootboxContentManager(),
            buttons: [
                {
                    label: i18next.t("Delete"),
                    className: "btn btn-warning",
                    callback: function () {
                        bootbox.confirm(i18next.t("Are you sure, you want to delete this Manager ?"), function (result) {
                            if (result) {
                                $('#select-manager-persons :selected').each(function (i, sel) {
                                    var personID = $(sel).val();

                                    window.CRM.APIRequest({
                                        method: 'POST',
                                        path: 'groups/deleteManager',
                                        data: JSON.stringify({"groupID": groupID, "personID": personID})
                                    }, function (data) {
                                        $("#select-manager-persons option[value='" + personID + "']").remove();

                                        var opts = $('#select-manager-persons > option').map(function () {
                                            return '<button class="delete-person-manager btn btn-danger btn-xs" data-personid"' + this.value + '" data-groupid"' + groupID + '"> <i class="icon far fa-trash-alt"></i> </button> ' + this.text;
                                        }).get();

                                        if (opts.length) {
                                            $("#Manager-list").html(opts.join(", "));
                                        } else {
                                            $("#Manager-list").html(i18next.t("No assigned Manager") + ".");
                                        }
                                    });
                                });
                            }
                        });
                        return false;
                    }
                },
                {
                    label: i18next.t("Delete Managers"),
                    className: "btn btn-danger",
                    callback: function () {
                        bootbox.confirm(i18next.t("Are you sure, you want to delete all the managers ?"), function (result) {
                            if (result) {
                                window.CRM.APIRequest({
                                    method: 'POST',
                                    path: 'groups/deleteAllManagers',
                                    data: JSON.stringify({"groupID": groupID})
                                }, function (data) {
                                    addManagersFromGroup(groupID);
                                    modal.modal("hide");
                                });
                            }
                        });
                        return false;
                    }
                },
                {
                    label: i18next.t("Ok"),
                    className: "btn btn-primary",
                    callback: function () {
                        modal.modal("hide");
                        return true;
                    }
                },
            ],
            show: false,
            onEscape: function () {
                modal.modal("hide");
            }
        });

        $("#person-manager-Id").select2({
            language: window.CRM.shortLocale,
            minimumInputLength: 2,
            placeholder: " -- " + i18next.t("User") + " -- ",
            allowClear: true, // This is for clear get the clear button if wanted
            ajax: {
                url: function (params) {
                    return window.CRM.root + "/api/people/searchonlyuser/" + params.term;
                },
                dataType: 'json',
                delay: 250,
                data: "",
                headers: {
                    "Authorization" : "Bearer "+window.CRM.jwtToken
                },
                processResults: function (data, params) {
                    return {results: data};
                },
                cache: true
            }
        });

        $("#person-manager-Id").on("select2:select", function (e) {
            if (e.params.data.personID !== undefined) {
                window.CRM.APIRequest({
                    method: 'POST',
                    path: 'groups/addManager',
                    data: JSON.stringify({"groupID": window.CRM.currentGroup, "personID": e.params.data.personID})
                }, function (data) {
                    addManagersFromGroup(groupID);
                });
            }
        });

        addManagersFromGroup(groupID);
        modal.modal('show');

        // this will ensure that image and table can be focused
        $(document).on('focusin', function (e) {
            e.stopImmediatePropagation();
        });
    }

    // end manager


    // listener : when the delete member is invocated
    $(document).on("updateLocalePageMessage", updateLocaleSCPage);

    // newMessage event handler
    function updateLocaleSCPage(e) {
        window.CRM.DataTableGroupView.ajax.reload();
    }

    /* Badge creation */
    $(document).on("click", "#groupbadge", function () {
        var groupId = $(this).data("groupid");
        window.CRM.APIRequest({
            method: "GET",
            path: "cart/"
        }, function (data) {
            if (data.PeopleCart.length > 0) {
                location.href = window.CRM.root + '/v2/group/' + groupId + '/badge/1/normal';
            } else {
                location.href = window.CRM.root + '/v2/group/' + groupId + '/badge/0/normal';
            }
        });
    });

    $('#add-event').on('click', function (e) {
        var fmt = 'YYYY-MM-DD HH:mm:ss';

        var dateStart = moment().format(fmt);
        var dateEnd = moment().format(fmt);

        addEvent(dateStart, dateEnd, i18next.t("Appointment"), sPageTitle, window.CRM.calendarID, true);
    });
}