$(function() {

    var fmt = window.CRM.datePickerformat.toUpperCase();

    window.CRM.fmt = window.CRM.datePickerformat.toUpperCase();

    $.fn.dataTable.moment( fmt );

    window.CRM.neverDate = moment('1900-01-01 00:00').format( window.CRM.fmt );

    function toPhoneLink(value) {
        if (value === null || value === undefined) {
            return '';
        }

        var display = String(value).trim();
        if (display === '') {
            return '';
        }

        var telValue = display.replace(/\s+/g, '');
        return '<a href="tel:' + telValue + '" class="d-inline-flex align-items-center">'
            + '<span class="badge badge-light border mr-2"><i class="fas fa-phone-alt text-success"></i></span>'
            + '<span>' + display + '</span>'
            + '</a>';
    }

    function buildIdentityLink(options) {
        var image = options.image;
        var href = options.href;
        var primary = options.primary || '';
        var secondary = options.secondary || '';
        var fallbackIcon = options.fallbackIcon || 'fa-user';

        return '<div class="d-flex align-items-center">'
            + image
            + '<div class="ml-2">'
            + '<div class="font-weight-bold"><a href="' + href + '">' + primary + '</a></div>'
            + (secondary !== '' ? '<div class="small text-muted"><i class="fas ' + fallbackIcon + ' mr-1"></i>' + secondary + '</div>' : '')
            + '</div>'
            + '</div>';
    }

    function buildThumbnail(full, id, type) {
        if (window.CRM.bThumbnailIconPresence) {
            return '<img src="' + window.CRM.root + '/api/' + type + '/' + id + '/thumbnail" alt="User Image" class="user-image initials-image-24">';
        }

        var fallback = type === 'families' ? 'Family.png' : 'Person.png';
        return '<img src="' + window.CRM.root + '/Images/' + fallback + '" class="initials-image direct-chat-img-24">';
    }

    function getBestPhone(full) {
        return full.CellPhone || full.cellPhone || full.HomePhone || full.homePhone || full.WorkPhone || full.workPhone || '';
    }

    function buildAddress(full) {
        var explicit = full.Address || full.address || '';
        if (explicit && String(explicit).trim() !== '') {
            return String(explicit).trim();
        }

        var parts = [
            full.Address1 || full.address1 || '',
            full.Address2 || full.address2 || '',
            full.City || full.city || '',
            full.State || full.state || '',
            full.Zip || full.zip || '',
            full.Country || full.country || ''
        ];

        return parts.join(' ').replace(/\s+/g, ' ').trim();
    }

    function toAddressLink(full) {
        var address = buildAddress(full);
        if (address === '') {
            return '';
        }
        return '<div class="d-inline-flex align-items-start">'
            + '<span class="badge badge-light border mr-2"><i class="fas fa-map-marker-alt text-danger"></i></span>'
            + '<span>' + window.CRM.tools.getLinkMapFromAddress(address) + '</span>'
            + '</div>';
    }

    function formatCareDate(data) {
        if (data === null || data === undefined) {
            return '<span class="text-muted"><i class="fas fa-ban mr-1"></i>' + i18next.t("Never contacted") + '</span>';
        }
        var date = moment(data).format(fmt);
        if (date === window.CRM.neverDate) {
            return '<span class="badge badge-light border text-muted"><i class="fas fa-ban mr-1"></i>' + i18next.t("Never contacted") + '</span>';
        }
        return '<span class="badge badge-light border text-dark"><i class="far fa-calendar-check mr-1 text-success"></i>' + date + '</span>';
    }

    columnsPastoralCareMembers = [
        {
            width: 'auto',
            title:i18next.t("Name"),
            data:'LastName',
            render: function(data, type, full, meta) {
                    return buildIdentityLink({
                        image: buildThumbnail(full, full.PersonID, 'persons'),
                        href: window.CRM.root + "/v2/people/person/view/" + full.PersonID,
                        primary: data,
                        secondary: i18next.t('Pastoral care member'),
                        fallbackIcon: 'fa-user'
                    });
            }
        },
        {
            width: 'auto',
            title:i18next.t("First Name"),
            data:'FirstName',
            render: function(data, type, full, meta) {
                return '<a href="' + window.CRM.root + "/v2/people/person/view/" + full.PersonID + '">'+ data + '</a>';
            }
        }
    ];

    if (window.CRM.bPastoralcareStats) {
        columnsPastoralCareMembers.push(
            {
                width: 'auto',
                title:i18next.t("Visits/calls"),
                data:'Visits',
                render: function(data, type, full, meta) {
                    return '<a href="' + window.CRM.root + "/v2/pastoralcare/listforuser/" + full.UserID + '">'+ data + '</a>';
                }
            }
        )
    }

    window.CRM.dataPastoralcareMembers = $("#pastoralcareMembers").DataTable({
        ajax:{
            url: window.CRM.root + "/api/pastoralcare/members",
            type: 'POST',
            contentType: "application/json",
            dataSrc: "Pastors",
            "beforeSend": function (xhr) {
                xhr.setRequestHeader('Authorization',
                    "Bearer " +  window.CRM.jwtToken
                );
            }
        },
        bSort : true,
        pageLength: 4,
        dom: 'rtip',
        "language": {
            "url": window.CRM.plugin.dataTable.language.url
        },
        columns: columnsPastoralCareMembers,
        responsive: true,
        createdRow : function (row,data,index) {
            $(row).addClass("menuLinksRow");
        }
    });

    window.CRM.personNeverBeenContacted = $("#personNeverBeenContacted").DataTable({
        ajax:{
            url: window.CRM.root + "/api/pastoralcare/personNeverBeenContacted",
            type: 'POST',
            contentType: "application/json",
            dataSrc: "PersonNeverBeenContacted",
            "beforeSend": function (xhr) {
                xhr.setRequestHeader('Authorization',
                    "Bearer " +  window.CRM.jwtToken
                );
            }
        },
        bSort : true,
        pageLength: 5,
        dom: 'rtip',
        "language": {
            "url": window.CRM.plugin.dataTable.language.url
        },
        columns: [
            {
                width: 'auto',
                title:i18next.t("Name"),
                data:'LastName',
                render: function(data, type, full, meta) {
                    return buildIdentityLink({
                        image: buildThumbnail(full, full.Id, 'persons'),
                        href: window.CRM.root + "/v2/pastoralcare/person/" + full.Id,
                        primary: data,
                        secondary: i18next.t('Person'),
                        fallbackIcon: 'fa-user'
                    });
                }
            },
            {
                width: 'auto',
                title:i18next.t("First Name"),
                data:'FirstName',
                render: function(data, type, full, meta) {
                    return '<a href="' + window.CRM.root + "/v2/pastoralcare/person/" + full.Id + '">'+ data + "</a>";
                }
            },
            {
                width: 'auto',
                title:i18next.t("Phone"),
                data:null,
                render: function(data, type, full, meta) {
                    return toPhoneLink(getBestPhone(full));
                }
            },
            {
                width: 'auto',
                title:i18next.t("Address"),
                data:null,
                render: function(data, type, full, meta) {
                    return toAddressLink(full);
                }
            },
            {
                width: 'auto',
                title: i18next.t("Last visit/call"),
                data: 'PastoralCareLastDate',
                render: function (data, type, full, meta) {
                    return formatCareDate(data);
                }
            }
        ],
        responsive: true,
        createdRow : function (row,data,index) {
            $(row).addClass("menuLinksRow");
        }
    });

    window.CRM.familyNeverBeenContacted = $("#familyNeverBeenContacted").DataTable({
        ajax:{
            url: window.CRM.root + "/api/pastoralcare/familyNeverBeenContacted",
            type: 'POST',
            contentType: "application/json",
            dataSrc: "FamilyNeverBeenContacted",
            "beforeSend": function (xhr) {
                xhr.setRequestHeader('Authorization',
                    "Bearer " +  window.CRM.jwtToken
                );
            }
        },
        bSort : true,
        pageLength: 5,
        dom: 'rtip',
        "language": {
            "url": window.CRM.plugin.dataTable.language.url
        },
        columns: [
            {
                width: 'auto',
                title:i18next.t("Name"),
                data:'Name',
                render: function(data, type, full, meta) {
                    return buildIdentityLink({
                        image: buildThumbnail(full, full.Id, 'families'),
                        href: window.CRM.root + "/v2/pastoralcare/family/" + full.Id,
                        primary: data,
                        secondary: i18next.t('Family'),
                        fallbackIcon: 'fa-home'
                    });
                }
            },
            {
                width: 'auto',
                title:i18next.t("Phone"),
                data:null,
                render: function(data, type, full, meta) {
                    return toPhoneLink(getBestPhone(full));
                }
            },
            {
                width: 'auto',
                title:i18next.t("Address"),
                data:null,
                render: function(data, type, full, meta) {
                    return toAddressLink(full);
                }
            },
            {
                width: 'auto',
                title: i18next.t("Last visit/call"),
                data: 'PastoralCareLastDate',
                render: function (data, type, full, meta) {
                    return formatCareDate(data);
                }
            }
        ],
        responsive: true,
        createdRow : function (row,data,index) {
            $(row).addClass("menuLinksRow");
        }
    });

    window.CRM.singleNeverBeenContacted = $("#singleNeverBeenContacted").DataTable({
        ajax:{
            url: window.CRM.root + "/api/pastoralcare/singleNeverBeenContacted",
            type: 'POST',
            contentType: "application/json",
            dataSrc: "SingleNeverBeenContacted",
            "beforeSend": function (xhr) {
                xhr.setRequestHeader('Authorization',
                    "Bearer " +  window.CRM.jwtToken
                );
            }
        },
        bSort : true,
        pageLength: 5,
        dom: 'rtip',
        "language": {
            "url": window.CRM.plugin.dataTable.language.url
        },
        columns: [
            {
                width: 'auto',
                title:i18next.t("Name"),
                data:'Name',
                render: function(data, type, full, meta) {
                    let res = '';
                    if (window.CRM.bThumbnailIconPresence) {
                        res += '<img src="' + window.CRM.root + '/api/persons/' + full.PersonID + '/thumbnail" alt="User Image" class="user-image initials-image-24"> ';
                    } else {
                        res += '<img src="' + window.CRM.root + '/Images/Person.png" class="initials-image direct-chat-img-24"> ';
                    }
                    return res + '<a href="' + window.CRM.root + "/v2/pastoralcare/person/" + full.PersonID + '">'+ data + "</a>";
                }
            },
            {
                width: 'auto',
                title:i18next.t("First Name"),
                data:'FirstName',
                render: function(data, type, full, meta) {
                    return '<a href="' + window.CRM.root + "/v2/pastoralcare/person/" + full.PersonID + '">'+ data + "</a>";
                }
            },
            {
                width: 'auto',
                title:i18next.t("Phone"),
                data:null,
                render: function(data, type, full, meta) {
                    return toPhoneLink(getBestPhone(full));
                }
            },
            {
                width: 'auto',
                title:i18next.t("Address"),
                data:null,
                render: function(data, type, full, meta) {
                    return toAddressLink(full);
                }
            },
            {
                width: 'auto',
                title: i18next.t("Last visit/call"),
                data: 'PastoralCareLastDate',
                render: function (data, type, full, meta) {
                    return formatCareDate(data);
                }
            }
        ],
        responsive: true,
        createdRow : function (row,data,index) {
            $(row).addClass("menuLinksRow");
        }
    });

    window.CRM.retiredNeverBeenContacted = $("#retiredNeverBeenContacted").DataTable({
        ajax:{
            url: window.CRM.root + "/api/pastoralcare/retiredNeverBeenContacted",
            type: 'POST',
            contentType: "application/json",
            dataSrc: "RetiredNeverBeenContacted",
            "beforeSend": function (xhr) {
                xhr.setRequestHeader('Authorization',
                    "Bearer " +  window.CRM.jwtToken
                );
            }
        },
        bSort : true,
        pageLength: 5,
        dom: 'rtip',
        "language": {
            "url": window.CRM.plugin.dataTable.language.url
        },
        columns: [
            {
                width: 'auto',
                title:i18next.t("Name"),
                data:'LastName',
                render: function(data, type, full, meta) {
                    let res = '';
                    if (window.CRM.bThumbnailIconPresence) {
                        res += '<img src="' + window.CRM.root + '/api/persons/' + full.Id + '/thumbnail" alt="User Image" class="user-image initials-image-24"> ';
                    } else {
                        res += '<img src="' + window.CRM.root + '/Images/Person.png" class="initials-image direct-chat-img-24"> ';
                    }
                    return res + '<a href="' + window.CRM.root + "/v2/pastoralcare/person/" + full.Id + '">'+ data + "</a>";
                }
            },
            {
                width: 'auto',
                title:i18next.t("First Name"),
                data:'FirstName',
                render: function(data, type, full, meta) {
                    return '<a href="' + window.CRM.root + "/v2/pastoralcare/person/" + full.Id + '">'+ data + "</a>";
                }
            },
            {
                width: 'auto',
                title:i18next.t("Phone"),
                data:null,
                render: function(data, type, full, meta) {
                    return toPhoneLink(getBestPhone(full));
                }
            },
            {
                width: 'auto',
                title:i18next.t("Address"),
                data:null,
                render: function(data, type, full, meta) {
                    return toAddressLink(full);
                }
            },
            {
                width: 'auto',
                title: i18next.t("Last visit/call"),
                data: 'PastoralCareLastDate',
                render: function (data, type, full, meta) {
                    return formatCareDate(data);
                }
            }
        ],
        responsive: true,
        createdRow : function (row,data,index) {
            $(row).addClass("menuLinksRow");
        }
    });

    window.CRM.youngNeverBeenContacted = $("#youngNeverBeenContacted").DataTable({
        ajax:{
            url: window.CRM.root + "/api/pastoralcare/youngNeverBeenContacted",
            type: 'POST',
            contentType: "application/json",
            dataSrc: "YoungNeverBeenContacted",
            "beforeSend": function (xhr) {
                xhr.setRequestHeader('Authorization',
                    "Bearer " +  window.CRM.jwtToken
                );
            }
        },
        bSort : true,
        pageLength: 5,
        dom: 'rtip',
        "language": {
            "url": window.CRM.plugin.dataTable.language.url
        },
        columns: [
            {
                width: 'auto',
                title:i18next.t("Name"),
                data:'LastName',
                render: function(data, type, full, meta) {
                    return buildIdentityLink({
                        image: buildThumbnail(full, full.Id, 'persons'),
                        href: window.CRM.root + "/v2/pastoralcare/person/" + full.Id,
                        primary: data,
                        secondary: i18next.t('Person'),
                        fallbackIcon: 'fa-user'
                    });
                }
            },
            {
                width: 'auto',
                title:i18next.t("First Name"),
                data:'FirstName',
                render: function(data, type, full, meta) {
                    return '<a href="' + window.CRM.root + "/v2/pastoralcare/person/" + full.Id + '">'+ data + "</a>";
                }
            },
            {
                width: 'auto',
                title:i18next.t("Phone"),
                data:null,
                render: function(data, type, full, meta) {
                    return toPhoneLink(getBestPhone(full));
                }
            },
            {
                width: 'auto',
                title:i18next.t("Address"),
                data:null,
                render: function(data, type, full, meta) {
                    return toAddressLink(full);
                }
            },
            {
                width: 'auto',
                title: i18next.t("Last visit/call"),
                data: 'PastoralCareLastDate',
                render: function (data, type, full, meta) {
                    return formatCareDate(data);
                }
            }
        ],
        responsive: true,
        createdRow : function (row,data,index) {
            $(row).addClass("menuLinksRow");
        }
    });

    $('#add-event').on('click', function (e) {
        var fmt = 'YYYY-MM-DD HH:mm:ss';

        var dateStart = moment().format(fmt);
        var dateEnd = moment().format(fmt);

        addEvent(dateStart,dateEnd,i18next.t("Appointment"),sPageTitle);
    });

    $( ".newPastorCare" ).on('click',function() {
        var typeID   = $(this).data('typeid');

        window.CRM.APIRequest({
            method: 'POST',
            path: 'pastoralcare/createRandomly',
            data: JSON.stringify({"typeID":typeID})
        },function(data) {
            if (data.status == "success") {
                switch (typeID) {
                    case 1:case 3:case 4:case 5:// person, old person or Young or single
                        location.href = window.CRM.root + '/v2/pastoralcare/person/' + data.personID;
                        break;
                    default:// a family
                        location.href = window.CRM.root + '/v2/pastoralcare/family/' + data.familyID;
                }

            }
        });
    });
});
