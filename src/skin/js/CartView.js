/* Copyright 2019 : Philippe Logel */

$(document).ready(function () {
    window.CRM.dataTableListing = $("#cart-listing-table").DataTable({
        "language": {
            "url": window.CRM.plugin.dataTable.language.url
        },
        responsive: true,
        deferLoading: 10,
        ajax: {
            url: window.CRM.root + "/api/persons/cart/view",
            type: 'GET',
            dataSrc: "CartPersons"
        },
        columns: [
            {
                width: 'auto',
                title:i18next.t('Name'),
                data: 'personID',
                render: function (data, type, full, meta) {
                    return '<img src="' + full.thumbnail+ '" class="direct-chat-img initials-image">&nbsp'
                        +'<a href="' + window.CRM.root + '/PersonView.php?PersonID=' + full.personID + '">'
                        + full.fullName
                        + '</a>';
                }
            },
            {
                width: 'auto',
                title:i18next.t('Address'),
                data: 'sValidAddy',
                render: function (data, type, full, meta) {
                    return data;
                }
            },
            {
                width: 'auto',
                title:i18next.t('Email'),
                data: 'sValidEmail',
                render: function (data, type, full, meta) {
                    return data;
                }
            },
            {
                width: 'auto',
                title:i18next.t('Remove'),
                data: 'personID',
                render: function (data, type, full, meta) {
                    return '<a class="RemoveFromPeopleCart btn btn-danger" data-personid="' + data + '">' + i18next.t('Remove') + '</a>';
                }
            },
            {
                width: 'auto',
                title:i18next.t('Classification'),
                data: 'ClassificationName',
                render: function (data, type, full, meta) {
                    return data;
                }
            },
            {
                width: 'auto',
                title:i18next.t('Family Role'),
                data: 'FamilyRoleName',
                render: function (data, type, full, meta) {
                    return data;
                }
            }
        ]
    });

    $("#cart-label-table").DataTable({
        responsive:true,
        paging: false,
        searching: false,
        ordering: false,
        info:     false,
        //dom: window.CRM.plugin.dataTable.dom,
        fnDrawCallback: function( settings ) {
            $("#selector thead").remove();
        }
    });

    $(document).on("click", ".emptyCart", function (e) {
        window.CRM.cart.empty(function(){
            document.location.reload();
        });
    });

    $(document).on("click", ".RemoveFromPeopleCart", function (e) {
        clickedButton = $(this);
        e.stopPropagation();
        window.CRM.cart.removePerson([clickedButton.data("personid")],function() {
            window.CRM.dataTableListing.ajax.reload();// PL : We should reload the table after we add a group so the button add to group is disabled
        });
    });

});