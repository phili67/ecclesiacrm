/* Copyright 2024 : Philippe Logel */
document.addEventListener("DOMContentLoaded", function() {
    
    window.CRM.ElementListener('.allPhonesCommaD', 'click', function(event) {
        prompt(i18next.t("Press CTRL + C to copy all group members\' phone numbers"), window.CRM.sPhoneLink)
    });
    
    window.CRM.ElementListener('.codename', 'click', function(event) {
        if (document.labelform.bulkmailpresort.checked) {
            document.labelform.bulkmailquiet.disabled = false;
        } else {
            document.labelform.bulkmailquiet.disabled = true;
            document.labelform.bulkmailquiet.checked = false;
        }
    });
    
    function loadTableEvents () 
    {
        window.CRM.ElementListener('.RemoveFromPeopleCart', 'click', function(event) {
            let personid = event.currentTarget.dataset.personid;
            event.stopPropagation();
            window.CRM.cart.removePerson([personid], function (data) {
                window.CRM.dataTableListing.ajax.reload(function () {
                    if (window.CRM.dataTableListing.data().count() == 0) {
                        bootbox.alert(i18next.t("You have no more items in your cart."), function () {
                            window.location.href = window.CRM.root + "/v2/dashboard"
                        });                        
                    }
                    loadTableEvents();
                });
    
                // we have to update the links
                document.getElementById("emailLink").setAttribute('href', "mailto:" + data.sEmailLink);
                document.getElementById("emailCCIlink").setAttribute('href', "mailto:?bcc=" + data.sEmailLink);
                document.getElementById("sPhoneLinkSMS").setAttribute('href', "sms:" + data.sPhoneLink);

                if (data.sEmailLink == "") {
                    document.getElementById("emailLink").style.display = 'none';
                    document.getElementById("emailCCIlink").style.display = 'none';
                }
    
                if (data.sPhoneLink == "") {
                    document.getElementById("sPhoneLinkSMS").style.display = 'none';
                }
    
                window.CRM.sEmailLink = data.sEmailLink;
                window.CRM.sPhoneLink = data.sPhoneLink;
            });
        });
    }

    window.CRM.dataTableListing = new DataTable("#cart-listing-table", {
        "language": {
            "url": window.CRM.plugin.dataTable.language.url
        },
        initComplete: function (settings, json) {
            loadTableEvents();
        },
        responsive: true,
        deferLoading: 10,
        ajax: {
            url: window.CRM.root + "/api/persons/cart/view",
            type: 'GET',
            dataSrc: "CartPersons",
            "beforeSend": function (xhr) {
                xhr.setRequestHeader('Authorization',
                    "Bearer " +  window.CRM.jwtToken
                );
            }
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

    let test = new DataTable("#cart-label-table", {
        responsive: true,
        paging: false,
        searching: false,
        ordering: false,
        info: false,
        //dom: window.CRM.plugin.dataTable.dom,
        fnDrawCallback: function (settings) {
            $("#selector thead").remove();
        }
    })

    window.CRM.ElementListener('.emptyCart', 'click', function(event) {
        window.CRM.cart.empty(function () {
            document.location.reload();
        });
    });
});

