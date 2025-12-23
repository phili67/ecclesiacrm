$(function () {

    const initDataTable = () => {
        var DataTableOpts = {
            ajax: {
                url: window.CRM.root + "/api/volunteeropportunity/" + window.CRM.volID + '/members',
                error: function (data) {
                    window.CRM.DisplayAlert(i18next.t("Error"), i18next.t("401 : Private Datas"));
                },
                dataSrc: "PersonVolunteers",
                "beforeSend": function (xhr) {
                    xhr.setRequestHeader('Authorization',
                        "Bearer " + window.CRM.jwtToken
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
                        return full.Person.img + ' &nbsp <a href="' + window.CRM.root + '/v2/people/person/view/"' + full.PersonId + '"><a target="_top" href="' + window.CRM.root + '/v2/people/person/view/' + full.PersonId + '">' + full.FirstName + " " + full.LastName + '</a>';
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
                                "Persons": $.map(window.CRM.DataTableVolunteersView.rows('.selected').data(), function (val, i) {
                                    return val.PersonId;
                                })
                            };

                            window.CRM.APIRequest({
                                method: 'DELETE',
                                path: 'volunteeropportunity/removePersons',
                                data: JSON.stringify({
                                    "volID": window.CRM.volID,
                                    "Persons": selectedPersons.Persons
                                })
                            }, function (data) {
                                window.CRM.DataTableVolunteersView.ajax.reload(() => {
                                    let selectedRows = window.CRM.DataTableVolunteersView.rows('.selected').data().length;
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
                        if (window.CRM.DataTableVolunteersView.rows('.selected').length > 0) {
                            var selectedPersons = {
                                "Persons": $.map(window.CRM.DataTableVolunteersView.rows('.selected').data(), function (val, i) {
                                    return val.PersonId;
                                })
                            };
                            window.CRM.cart.addPerson(selectedPersons.Persons);
                        }
                    }
                },
                {
                    text: i18next.t("Add to volunteer opportunities"),
                    action: function (e, dt, node, config) {
                        window.CRM.volunteers.promptSelection({ Type: window.CRM.volunteers.selectTypes.Volunteer }, function (data) {
                            let selectedRows = window.CRM.DataTableVolunteersView.rows('.selected').data()
                            $.each(selectedRows, function (index, value) {
                                window.CRM.volunteers.addPerson(data.VolID, value.PersonId);
                            });
                        });
                    }
                },
                {
                    text: i18next.t("Move to volunteer opportunities"),
                    action: function (e, dt, node, config) {
                        window.CRM.volunteers.promptSelection(
                            { Type: window.CRM.volunteers.selectTypes.Volunteer }, function (data) {
                            let selectedRows = window.CRM.DataTableVolunteersView.rows('.selected').data()
                            $.each(selectedRows, function (index, value) {
                                console.log(data);
                                window.CRM.volunteers.addPerson(data.VolID, value.PersonId);
                                window.CRM.volunteers.removePerson(data.volID, value.PersonId, function () {
                                    window.CRM.DataTableVolunteersView.row(function (idx, data, node) {
                                        if (data.PersonId == value.PersonId) {
                                            return true;
                                        }
                                    }).remove();
                                    window.CRM.DataTableVolunteersView.rows().invalidate().draw(true);
                                });
                            });
                        });
                    }
                }
            ]
        });


        window.CRM.plugin.dataTable.buttons.push({
            text: '<i class="fas fa-trash-alt"></i> ' + i18next.t("Remove all members"),
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
                                path: 'volunteeropportunity/removeAllMembers',
                                data: JSON.stringify({ "volId": window.CRM.volID })
                            }, function (data) {
                                window.CRM.DataTableVolunteersView.ajax.reload();/* we reload the data no need to add the person inside the dataTable */
                            });
                        }
                    }
                });
            }
        });


        $.extend(DataTableOpts, window.CRM.plugin.dataTable);

        window.CRM.DataTableVolunteersView = new DataTable("#VolunteerOpportunityTableMembers", DataTableOpts);

        $('#isVolunteersActive').on('change', function () {
            window.CRM.APIRequest({
                method: 'POST',
                path: 'volunteeropportunity/' + window.CRM.volID + '/settings/active/' + $(this).prop('checked')
            }, function (selection) {
                location.reload();
            });
        });

        $('#isVolunteersEmailExport').on('change', function () {
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
                path: 'volunteeropportunity/' + window.CRM.volID + '/settings/email/export/' + $(this).prop('checked')
            });
        });

        $(document).on('click', '.groupRow', function () {
            var selectedRows = window.CRM.DataTableVolunteersView.rows('.selected').data().length;
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
            var text = $(clickedButton).find("span.cartActionDescription");
            if (text) {
                $(text).text(i18next.t("Remove from Cart"));
            }
        }

        const removeCartMemberAction = (clickedButton) => {
            $(clickedButton).addClass("AddToGroupCart");
            $(clickedButton).removeClass("RemoveFromGroupCart");
            $('i', clickedButton).removeClass("fa-times");
            $('i', clickedButton).addClass("fa-cart-plus");
            var text = $(clickedButton).find("span.cartActionDescription");
            if (text) {
                $(text).text(i18next.t("Add to Cart"));
            }
        }

        $(document).on("click", ".AddToGroupCart", function () {
            var clickedButton = $(this);
            window.CRM.cart.addVolunteers(clickedButton.data("cartvolunterid"), function () {
                addCartMemberAction(clickedButton);
            });
        });

        $(document).on("click", ".RemoveFromGroupCart", function () {
            var clickedButton = $(this);
            window.CRM.cart.removeVolunteers(clickedButton.data("cartvolunterid"), function () {
                removeCartMemberAction(clickedButton);
            });
        });


        // newMessage event subscribers : Listener CRJSOM.js
        $(document).on("updateCartMessage", updateButtons);

        // newMessage event handler
        function updateButtons(e) {
            var clickedButton = $("#AddToGroupCart");
            if (e.people.length == 0) {
                removeCartMemberAction(clickedButton);

            } else {
                addCartMemberAction(clickedButton);
            }
        }        

        // listener : when the delete member is invocated
        $(document).on("updateLocalePageMessage", updateLocaleSCPage);

        // newMessage event handler
        function updateLocaleSCPage(e) {
            window.CRM.DataTableVolunteersView.ajax.reload();
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

    initDataTable();

    $('#isVolunteersActive').prop('checked', window.CRM.isActive).on('change');
    $('#isVolunteersEmailExport').prop('checked', window.CRM.isIncludeInEmailExport).on('change');

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
        window.CRM.volunteers.addPerson(window.CRM.volID, e.params.data.objid, function (data) {
            if (data.status == "failed") {
                window.CRM.DisplayAlert(i18next.t("Error"), i18next.t("A kid should have a family in a sunday school group !"));
            } else {
                $(".personSearch").val(null).trigger('change');
                window.CRM.DataTableVolunteersView.ajax.reload();
            }
        });        
    });
});
