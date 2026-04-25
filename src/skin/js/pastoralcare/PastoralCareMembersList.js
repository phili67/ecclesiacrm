$(function() {
    window.CRM.extractionType = "/api/pastoralcare/getPersonByClassification/1";

    $( ".changeType" ).on('click',function() {
        window.CRM.dataPastoralcareMembersList.search($(this).data('typeid')).draw();
    });

    window.CRM.fmt = window.CRM.datePickerformat.toUpperCase();

    $.fn.dataTable.moment( window.CRM.fmt  );

    window.CRM.neverDate = moment('1900-01-01').format( window.CRM.fmt );

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

        return '<div class="d-flex align-items-center">'
            + image
            + '<div class="ml-2">'
            + '<div class="font-weight-bold"><a href="' + href + '">' + primary + '</a></div>'
            + (secondary !== '' ? '<div class="small text-muted">' + secondary + '</div>' : '')
            + '</div>'
            + '</div>';
    }

    function buildThumbnail(id) {
        if (window.CRM.bThumbnailIconPresence) {
            return '<img src="/api/persons/' + id + '/thumbnail" alt="User Image" class="user-image initials-image" width="35" height="35">';
        }

        return '<img src="' + window.CRM.root + '/Images/Person.png" class="initials-image direct-chat-img" width="50" height="50">';
    }

    function getBestPhone(full) {
        return full.CellPhone || full.cellPhone || full.per_CellPhone || full.fam_CellPhone || full.HomePhone || full.homePhone || full.per_HomePhone || full.fam_HomePhone || full.WorkPhone || full.workPhone || full.per_WorkPhone || full.fam_WorkPhone || '';
    }

    function buildAddress(full) {
        var explicit = full.Address || full.address || '';
        if (explicit && String(explicit).trim() !== '') {
            return String(explicit).trim();
        }

        var parts = [
            full.Address1 || full.address1 || full.per_Address1 || full.fam_Address1 || '',
            full.Address2 || full.address2 || full.per_Address2 || full.fam_Address2 || '',
            full.City || full.city || full.per_City || full.fam_City || '',
            full.State || full.state || full.per_State || full.fam_State || '',
            full.Zip || full.zip || full.per_Zip || full.fam_Zip || '',
            full.Country || full.country || full.per_Country || full.fam_Country || ''
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
        var date = moment(data).format(window.CRM.fmt);
        if (date === window.CRM.neverDate) {
            return '<span class="badge badge-light border text-muted"><i class="fas fa-ban mr-1"></i>' + i18next.t("Never contacted") + '</span>';
        }
        return '<span class="badge badge-light border text-dark"><i class="far fa-calendar-check mr-1 text-success"></i>' + date + '</span>';
    }

    window.CRM.dataPastoralcareMembersList = $("#pastoralCareMembersList").DataTable({
        ajax: {
            url: window.CRM.root + window.CRM.extractionType,
            type: 'POST',
            contentType: "application/json",
            dataSrc: "MembersClassicationsList",
            "beforeSend": function (xhr) {
                xhr.setRequestHeader('Authorization',
                    "Bearer " +  window.CRM.jwtToken
                );
            }
        },
        bSort: true,
        "language": {
            "url": window.CRM.plugin.dataTable.language.url
        },
        columns: [
            {
                width: 'auto',
                title: i18next.t("Last Name (Family Name)"),
                data: 'LastName',
                render: function (data, type, full, meta) {
                    return buildIdentityLink({
                        image: buildThumbnail(full.Id),
                        href: window.CRM.root + "/v2/pastoralcare/person/" + full.Id,
                        primary: data,
                        secondary: i18next.t("Family Name") + ' : <a href="' + window.CRM.root + "/v2/pastoralcare/family/" + full.FamilyId + '">' + full.FamilyName + '</a>'
                    });
                }
            },
            {
                width: 'auto',
                title: i18next.t("First Name"),
                data: 'FirstName',
                render: function (data, type, full, meta) {
                    return '<a href="' + window.CRM.root + "/v2/pastoralcare/person/" + full.Id + '">'+ data + "</a>";
                }
            },
            {
                width: 'auto',
                title: i18next.t("Classification"),
                data: 'ClassName',
                render: function (data, type, full, meta) {
                    return '<span class="badge badge-light border text-dark">' + data + '</span>';
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
        createdRow: function (row, data, index) {
            $(row).addClass("menuLinksRow");
        }
    });

    $('#add-event').on('click', function (e) {
        var fmt = 'YYYY-MM-DD HH:mm:ss';

        var dateStart = moment().format(fmt);
        var dateEnd = moment().format(fmt);

        addEvent(dateStart,dateEnd,i18next.t("Appointment"),sPageTitle);
    });

    $('.typeSort').on('click', function (e) {
        window.CRM.extractionType = "/api/pastoralcare/getPersonByClassification/" + $(this).val();

        window.CRM.dataPastoralcareMembersList.ajax.url( window.CRM.extractionType ).load();
    });
});
