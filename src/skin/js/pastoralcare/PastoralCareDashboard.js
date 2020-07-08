$(document).ready(function () {

    var fmt = window.CRM.datePickerformat.toUpperCase();

    if (window.CRM.timeEnglish == true) {
        time_format = 'h:mm A';
    } else {
        time_format = 'H:mm';
    }

    fmt += ' ' + time_format;

    window.CRM.dataPastoralcareMembers = $("#pastoralcareMembers").DataTable({
        ajax:{
            url: window.CRM.root + "/api/pastoralcare/members",
            type: 'POST',
            contentType: "application/json",
            dataSrc: "Pastors"
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
                    return '<a href="' + window.CRM.root + "/PersonView.php?PersonID=" + full.PersonID + '">'+ data + '</a>';
                }
            },
            {
                width: 'auto',
                title:i18next.t("First Name"),
                data:'FirstName',
                render: function(data, type, full, meta) {
                    return '<a href="' + window.CRM.root + "/PersonView.php?PersonID=" + full.PersonID + '">'+ data + '</a>';
                }
            }
        ],
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
            dataSrc: "PersonNeverBeenContacted"
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
                    return '<a href="' + window.CRM.root + "/v2/pastoralcare/person/" + full.Id + '">'+ data + "</a>";
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
                        return i18next.t("Never");
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
            dataSrc: "FamilyNeverBeenContacted"
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
                    return '<a href="' + window.CRM.root + "/v2/pastoralcare/family/" + full.Id + '">'+ data + "</a>";
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
                        return i18next.t("Never");
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
            dataSrc: "RetiredNeverBeenContacted"
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
                    return '<a href="' + window.CRM.root + "/v2/pastoralcare/person/" + full.Id + '">'+ data + "</a>";
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
                        return i18next.t("Never");
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
            dataSrc: "YoungNeverBeenContacted"
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
                    return '<a href="' + window.CRM.root + "/v2/pastoralcare/person/" + full.Id + '">'+ data + "</a>";
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
                        return i18next.t("Never");
                    }
                }
            }
        ],
        responsive: true,
        createdRow : function (row,data,index) {
            $(row).addClass("menuLinksRow");
        }
    });

    function addEvent(dateStart,dateEnd,windowTitle,title)
    {
        if (window.CRM.editor != null) {
            CKEDITOR.remove(window.CRM.editor);
            window.CRM.editor = null;
        }

        modal = createEventEditorWindow (dateStart,dateEnd,'createEvent',0,'','v2/calendar',windowTitle,title);

        // we add the calendars and the types
        addCalendars();
        addCalendarEventTypes(-1,true);

        //Timepicker
        $('.timepicker').datetimepicker({
            format: 'LT',
            locale: window.CRM.lang,
            icons:
                {
                    up: 'fa fa-angle-up',
                    down: 'fa fa-angle-down'
                }
        });

        $('.date-picker').datepicker({format:window.CRM.datePickerformat, language: window.CRM.lang});

        $('.date-picker').click('focus', function (e) {
            e.preventDefault();
            $(this).datepicker('show');
        });

        $('.date-start').hide();
        $('.date-end').hide();
        $('.date-recurrence').hide();
        $(".eventNotes").hide();

        $("#typeEventrecurrence").prop("disabled", true);
        $("#endDateEventrecurrence").prop("disabled", true);

        // this will create the toolbar for the textarea
        if (window.CRM.editor == null) {
            if (window.CRM.bEDrive) {
                window.CRM.editor = CKEDITOR.replace('eventNotes',{
                    customConfig: window.CRM.root+'/skin/js/ckeditor/configs/calendar_event_editor_config.js',
                    language : window.CRM.lang,
                    width : '100%',
                    extraPlugins : 'uploadfile,uploadimage,filebrowser',
                    uploadUrl: window.CRM.root+'/uploader/upload.php?type=publicDocuments',
                    imageUploadUrl: window.CRM.root+'/uploader/upload.php?type=publicImages',
                    filebrowserUploadUrl: window.CRM.root+'/uploader/upload.php?type=publicDocuments',
                    filebrowserBrowseUrl: window.CRM.root+'/browser/browse.php?type=publicDocuments'
                });
            } else {
                window.CRM.editor = CKEDITOR.replace('eventNotes',{
                    customConfig: window.CRM.root+'/skin/js/ckeditor/configs/calendar_event_editor_config.js',
                    language : window.CRM.lang,
                    width : '100%'
                });
            }

            add_ckeditor_buttons(window.CRM.editor);
        }


        $(".ATTENDENCES").hide();

        $('#EventCalendar option:first-child').attr("selected", "selected");

        modal.modal("show");

        initMap();
    }

    $('#add-event').click('focus', function (e) {
        var fmt = 'YYYY-MM-DD HH:mm:ss';

        var dateStart = moment().format(fmt);
        var dateEnd = moment().format(fmt);

        addEvent(dateStart,dateEnd,i18next.t("Appointment"),sPageTitle);
    });

    $( ".newPastorCare" ).click(function() {
        var typeID   = $(this).data('typeid');

        window.CRM.APIRequest({
            method: 'POST',
            path: 'pastoralcare/createRandomly',
            data: JSON.stringify({"typeID":typeID})
        }).done(function(data) {
            if (data.status == "success") {
                switch (typeID) {
                    case 1:case 3:case 4:// person, old person or Young
                        location.href = window.CRM.root + '/v2/pastoralcare/person/' + data.personID;
                        break;
                    default:// a family
                        location.href = window.CRM.root + '/v2/pastoralcare/family/' + data.familyID;
                }

            }
        });
    });
});
