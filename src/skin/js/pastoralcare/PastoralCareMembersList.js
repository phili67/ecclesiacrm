$(function() {
    window.CRM.extractionType = "/api/pastoralcare/getPersonByClassification/1";

    $( ".changeType" ).on('click',function() {
        window.CRM.dataPastoralcareMembersList.search($(this).data('typeid')).draw();
    });

    window.CRM.fmt = window.CRM.datePickerformat.toUpperCase();

    $.fn.dataTable.moment( window.CRM.fmt  );

    window.CRM.neverDate = moment('1900-01-01').format( window.CRM.fmt );

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
                    res = '';
                    if (window.CRM.bThumbnailIconPresence) {
                        res += '<img src="/api/persons/' + full.Id + '/thumbnail" alt="User Image" class="user-image initials-image" width="35" height="35"> ';
                    }
                    return res + '<a href="' + window.CRM.root + "/v2/pastoralcare/person/" + full.Id + '">'+ data + '</a> ('+ i18next.t("Family Name") +' : <a href="' + window.CRM.root + "/v2/pastoralcare/family/" + full.FamilyId + '">' + full.FamilyName + "</a>)";
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
                    return data;
                }
            },
            {
                width: 'auto',
                title: i18next.t("Last visit/call"),
                data: 'PastoralCareLastDate',
                render: function (data, type, full, meta) {
                    if (data != null) {
                        var date = moment(data).format(window.CRM.fmt);
                        return date;
                    } else {
                        return window.CRM.neverDate;
                    }
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
