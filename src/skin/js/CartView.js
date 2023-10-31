/* Copyright 2019 : Philippe Logel */

function allPhonesCommaD() {
    prompt(i18next.t("Press CTRL + C to copy all group members\' phone numbers"), window.CRM.sPhoneLink)
}

function codename() {
    if (document.labelform.bulkmailpresort.checked) {
        document.labelform.bulkmailquiet.disabled = false;
    } else {
        document.labelform.bulkmailquiet.disabled = true;
        document.labelform.bulkmailquiet.checked = false;
    }
}

$(function() {
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
                title: i18next.t('Name'),
                data: 'personID',
                render: function (data, type, full, meta) {
                    return '<img src="' + full.thumbnail + '" class="direct-chat-img initials-image">&nbsp'
                        + '<a href="' + window.CRM.root + '/v2/people/person/view/' + full.personID + '">'
                        + full.fullName
                        + '</a>';
                }
            },
            {
                width: 'auto',
                title: i18next.t('Address'),
                data: 'sValidAddy',
                render: function (data, type, full, meta) {
                    return data;
                }
            },
            {
                width: 'auto',
                title: i18next.t('Email'),
                data: 'sValidEmail',
                render: function (data, type, full, meta) {
                    return data;
                }
            },
            {
                width: 'auto',
                title: i18next.t('Remove'),
                data: 'personID',
                render: function (data, type, full, meta) {
                    return '<a class="RemoveFromPeopleCart" data-personid="' + data + '"><i class="far fa-trash-alt" aria-hidden="true" style="color:red"></i></a>';
                }
            },
            {
                width: 'auto',
                title: i18next.t('Classification'),
                data: 'ClassificationName',
                render: function (data, type, full, meta) {
                    return data;
                }
            },
            {
                width: 'auto',
                title: i18next.t('Family Role'),
                data: 'FamilyRoleName',
                render: function (data, type, full, meta) {
                    return data;
                }
            }
        ]
    });

    $("#cart-label-table").DataTable({
        responsive: true,
        paging: false,
        searching: false,
        ordering: false,
        info: false,
        //dom: window.CRM.plugin.dataTable.dom,
        fnDrawCallback: function (settings) {
            $("#selector thead").remove();
        }
    });

    $(document).on("click", ".emptyCart", function (e) {
        window.CRM.cart.empty(function () {
            document.location.reload();
        });
    });

    $(document).on("click", ".RemoveFromPeopleCart", function (e) {
        clickedButton = $(this);
        e.stopPropagation();
        window.CRM.cart.removePerson([clickedButton.data("personid")], function (data) {
            window.CRM.dataTableListing.ajax.reload(function () {
                if (window.CRM.dataTableListing.data().count() == 0) {
                    bootbox.alert(i18next.t("You have no more items in your cart."), function () {
                        window.location.href = window.CRM.root + "/v2/dashboard"
                    });
                }
            });

            // we have to update the links
            $('#emailLink').attr("href", "mailto:" + data.sEmailLink);
            $('#emailCCIlink').attr("href", "mailto:?bcc=" + data.sEmailLink);

            $('.sPhoneLinkSMS').attr("href", "sms:" + data.sPhoneLink);

            if (data.sEmailLink == "") {
                $('#emailLink').hide();
                $('#emailCCIlink').hide();
            }

            if (data.sPhoneLink == "") {
                $('#globalSMSLink').hide();
            }

            window.CRM.sEmailLink = data.sEmailLink;
            window.CRM.sPhoneLink = data.sPhoneLink;
        });
    });
});

