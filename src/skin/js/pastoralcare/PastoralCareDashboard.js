$(function() {

    var fmt = window.CRM.datePickerformat.toUpperCase();

    window.CRM.fmt = window.CRM.datePickerformat.toUpperCase();

    $.fn.dataTable.moment( fmt );

    window.CRM.neverDate = moment('1900-01-01 00:00').format( window.CRM.fmt );

    columnsPastoralCareMembers = [
        {
            width: 'auto',
            title:i18next.t("Name"),
            data:'LastName',
            render: function(data, type, full, meta) {
                res = '';
                if (window.CRM.bThumbnailIconPresence) {
                    res += '<img src="' + window.CRM.root + '/api/persons/' + full.PersonID + '/thumbnail" alt="User Image" class="user-image initials-image" width="35" height="35"> ';
                }
                return res + '<img src="' + window.CRM.root + '/Images/Person.png" class="initials-image direct-chat-img " width="50" height="50"> <a href="' + window.CRM.root + "/v2/people/person/view/" + full.PersonID + '">'+ data + '</a>';
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
        "language": {
            "url": window.CRM.plugin.dataTable.language.url
        },
        columns: [
            {
                width: 'auto',
                title:i18next.t("Name"),
                data:'LastName',
                render: function(data, type, full, meta) {
                    res = '';
                    if (window.CRM.bThumbnailIconPresence) {
                        res += '<img src="' + window.CRM.root + ' /api/persons/' + full.Id + '/thumbnail" alt="User Image" class="user-image initials-image" width="35" height="35"> ';
                    }
                    return res + '<img src="' + window.CRM.root + '/Images/Person.png" class="initials-image direct-chat-img " width="50" height="50"> <a href="' + window.CRM.root + "/v2/pastoralcare/person/" + full.Id + '">'+ data + "</a>";
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
                title: i18next.t("Last visit/call"),
                data: 'PastoralCareLastDate',
                render: function (data, type, full, meta) {
                    if (data != null) {
                        var date = moment(data).format(fmt);
                        return date;
                    } else {
                        return window.CRM.neverDate;
                    }
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
        "language": {
            "url": window.CRM.plugin.dataTable.language.url
        },
        columns: [
            {
                width: 'auto',
                title:i18next.t("Name"),
                data:'Name',
                render: function(data, type, full, meta) {
                    res = '';
                    if (window.CRM.bThumbnailIconPresence) {
                        res += '<img src="' + window.CRM.root + '/api/families/' + full.Id + '/thumbnail" alt="User Image" class="user-image initials-image" width="35" height="35"> ';
                    }
                    return res + '<img src="' + window.CRM.root + '/Images/Family.png" class="initials-image direct-chat-img " width="50" height="50"> <a href="' + window.CRM.root + "/v2/pastoralcare/family/" + full.Id + '">'+ data + "</a>";
                }
            },
            {
                width: 'auto',
                title: i18next.t("Last visit/call"),
                data: 'PastoralCareLastDate',
                render: function (data, type, full, meta) {
                    if (data != null) {
                        var date = moment(data).format(fmt);
                        return date;
                    } else {
                        return window.CRM.neverDate;
                    }
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
        "language": {
            "url": window.CRM.plugin.dataTable.language.url
        },
        columns: [
            {
                width: 'auto',
                title:i18next.t("Name"),
                data:'Name',
                render: function(data, type, full, meta) {
                    res = '';
                    if (window.CRM.bThumbnailIconPresence) {
                        res += '<img src="' + window.CRM.root + '/api/persons/' + full.PersonID + '/thumbnail" alt="User Image" class="user-image initials-image" width="35" height="35"> ';
                    }
                    return res + '<img src="' + window.CRM.root + '/Images/Person.png" class="initials-image direct-chat-img " width="50" height="50"> <a href="' + window.CRM.root + "/v2/pastoralcare/person/" + full.PersonID + '">'+ data + "</a>";
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
                title: i18next.t("Last visit/call"),
                data: 'PastoralCareLastDate',
                render: function (data, type, full, meta) {
                    if (data != null) {
                        var date = moment(data).format(fmt);
                        return date;
                    } else {
                        return window.CRM.neverDate;
                    }
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
        "language": {
            "url": window.CRM.plugin.dataTable.language.url
        },
        columns: [
            {
                width: 'auto',
                title:i18next.t("Name"),
                data:'LastName',
                render: function(data, type, full, meta) {
                    res = '';
                    if (window.CRM.bThumbnailIconPresence) {
                        res += '<img src="' + window.CRM.root + '/api/persons/' + full.Id + '/thumbnail" alt="User Image" class="user-image initials-image" width="35" height="35"> ';
                    }
                    return res + '<img src="' + window.CRM.root + '/Images/Person.png" class="initials-image direct-chat-img " width="50" height="50"> <a href="' + window.CRM.root + "/v2/pastoralcare/person/" + full.Id + '">'+ data + "</a>";
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
                title: i18next.t("Last visit/call"),
                data: 'PastoralCareLastDate',
                render: function (data, type, full, meta) {
                    if (data != null) {
                        var date = moment(data).format(fmt);
                        return date;
                    } else {
                        return window.CRM.neverDate;
                    }
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
        "language": {
            "url": window.CRM.plugin.dataTable.language.url
        },
        columns: [
            {
                width: 'auto',
                title:i18next.t("Name"),
                data:'LastName',
                render: function(data, type, full, meta) {
                    res = '';
                    if (window.CRM.bThumbnailIconPresence) {
                        res += '<img src="' + window.CRM.root + '/api/persons/' + full.Id + '/thumbnail" alt="User Image" class="user-image initials-image" width="35" height="35"> ';
                    }
                    return res + '<img src="' + window.CRM.root + '/Images/Person.png" class="initials-image direct-chat-img " width="50" height="50"> <a href="' + window.CRM.root + "/v2/pastoralcare/person/" + full.Id + '">'+ data + "</a>";
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
                title: i18next.t("Last visit/call"),
                data: 'PastoralCareLastDate',
                render: function (data, type, full, meta) {
                    if (data != null) {
                        var date = moment(data).format(fmt);
                        return date;
                    } else {
                        return window.CRM.neverDate;
                    }
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
