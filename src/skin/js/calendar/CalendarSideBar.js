//
//  This code is under copyright not under MIT Licence
//  copyright   : 2018 Philippe Logel all right reserved not MIT licence
//                This code can't be included in another software.
//
//  Updated     : 2020/05/13
//

var maxHeight = 230;
$(window).resize(function () {
    //(document.body.clientHeight); n'a pas l'air top
    var hscreen = $(window).height(),
        height = hscreen > maxHeight ? maxHeight : hscreen;
    $('#mon_rectangle').height(height);
});

// for the calendar
$('body').on('click', '.check-calendar', function () {
    var calIDs = $(this).data("id");
    var isChecked = ($(this).is(':checked')) ? 1 : 0;

    window.CRM.APIRequest({
        method: 'POST',
        path: 'calendar/setckecked',
        data: JSON.stringify({"calIDs": calIDs, "isChecked": isChecked})
    }, function (data) {
        // we reload all the events
        window.CRM.calendar.refetchEvents();
    });
});


$("#add-calendar").on('click', function (e) {
    bootbox.prompt({
        title: i18next.t("Set Calendar Name"),
        buttons: {
            cancel: {
                label: '<i class="fas fa-times"></i> ' + i18next.t("Cancel")
            },
            confirm: {
                label: '<i class="fas fa-check"></i> ' + i18next.t("Confirm"),
                className: 'btn-primary'
            }
        },
        inputType: 'text',
        callback: function (title) {
            if (title !== null && title !== '') {
                window.CRM.APIRequest({
                    method: 'POST',
                    path: 'calendar/new',
                    data: JSON.stringify({"title": title})
                }, function (data) {
                    addPersonalCalendars();
                });
            }
        }
    });
});

$("#add-reservation-calendar").on('click', function (e) {
    bootbox.confirm({
        title: i18next.t("Set Resource Name"),
        message: '<table width=100%>'
            + '<tr>'
            + '<td>'
            + i18next.t("Name") + ':'
            + '</td>'
            + '<td>'
            + ' <input class="bootbox-input bootbox-input-text form-control form-control-sm" type="text" id="textCalendar"><br>'
            + '</td>'
            + '</tr>'
            + '<tr>'
            + '<td>'
            + i18next.t("Select a resource type") + ':'
            + '</td>'
            + '<td>'
            + '<select class="form-control form-control-sm" id="typeCalendar" name="typeCalendar">'
            + '<option value="2">' + i18next.t("Room") + '</option>'
            + '<option value="3">' + i18next.t("Computer") + '</option>'
            + '<option value="4">' + i18next.t("Video") + '</option>'
            + '</select>'
            + '</td>'
            + '</tr>'
            + '<tr>'
            + '<td>'
            + i18next.t("Description") + ':'
            + '</td>'
            + '<td>'
            + ' <input class="bootbox-input bootbox-input-text form-control form-control-sm" type="text" id="descCalendar"><br>'
            + '</td>'
            + '</tr>'
            + '</table>',
        buttons: {
            cancel: {
                label: '<i class="fas fa-times"></i> ' + i18next.t("Cancel")
            },
            confirm: {
                label: '<i class="fas fa-check"></i> ' + i18next.t("OK"),
                className: 'btn-primary'
            }
        },
        callback: function (result) {
            var title = $("#textCalendar").val();
            var type = $("#typeCalendar").val();
            var desc = $("#descCalendar").val();

            if (result) {
                window.CRM.APIRequest({
                    method: 'POST',
                    path: 'calendar/newReservation',
                    data: JSON.stringify({"title": title, "type": type, "desc": desc})
                }, function (data) {
                    addReservationCalendars();
                });
            }
        }
    });
});

// to add PresenceShare

function addShareCalendarPresence(type) {
    $('#select-calendar-presence').find('option').remove();

    window.CRM.APIRequest({
        method: 'POST',
        path: 'calendar/getallforuser',
        data: JSON.stringify({"type": type, "onlyvisible": false, "allCalendars": true})
    }, function (res) {
        var data = res.calendars;
        var elt = document.getElementById("select-calendar-presence");
        var len = data.length;

        for (i = 0; i < len; ++i) {
            if (data[i].present == true) {
                var option = document.createElement("option");

                var hello = "✐👀✖❌  ✔✕✖✅";

                option.text = '✅' + " " + data[i].calendarName;
                option.value = data[i].calendarID;

                elt.appendChild(option);
            } else {
                var option = document.createElement("option");

                option.text = '❌' + " " + data[i].calendarName;
                option.value = data[i].calendarID;

                elt.appendChild(option);
            }

        }
    });
}

function BootboxContentCalendarPresence() {
    var frm_str ='<div>'
        + '  <div class="row">'
        + '      <div class="col-md-4">'
        + '          <span style="color: red">*</span>' + i18next.t("Calendars") + ":"
        + '      </div>'
        + '      <div class="col-md-8">'
        + '          <select size="6" class="form-control BootboxContentCalendarPresence" id="select-calendar-presence" multiple></select>'
        + '      </div>'
        + '  </div>'
        + '  <br/>'
        + '  <div class="row">'
        + '      <div class="col-md-4"><span style="color: red">*</span>' + i18next.t("Set Status") + ":</div>"
        + '      <div class="col-md-8">'
        + '          <select name="calendar-show-hide" id="calendar-show-hide" class="form-control form-control-sm"'
        + '              style="width:100%" data-placeholder="text to place">'
        + '              <option value="0">' + i18next.t("Select [Exclude] or [Include]") + ' -- </option>'
        + '              <option value="2">' + '✅' + ' -- ' + i18next.t('[INCLUDE]') + '</option>'
        + '              <option value="1">' + '❌' + ' -- ' + i18next.t('[EXCLUDE]') + '</option>'
        + '          </select>'
        + '      </div>'
        + '  </div>'
        + '</div>';

    var object = $('<div/>').html(frm_str).contents();

    return object
}

function CreateCalendarPresenceWindow(type) {
    var modal = bootbox.dialog({
        message: BootboxContentCalendarPresence(),
        title: i18next.t("Include/Exclude your Calendars in the SideBar"),
        buttons: [
            {
                label: '<i class="fas fa-check"></i> ' + i18next.t("Ok"),
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

    addShareCalendarPresence(type);

    return modal;
}

function createPresenceManager(type) {
    var modal = CreateCalendarPresenceWindow(type);

    $("#calendar-show-hide").on('change',function () {
        var isPresent = $(this).val();
        var deferredsSH = [];
        var i = 0;

        $('#select-calendar-presence :selected').each(function (i, sel) {
            var calIDs = $(sel).val();
            var str = $(sel).text();

            deferredsSH.push(
                window.CRM.APIRequest({
                    method: 'POST',
                    path: 'calendar/showhidecalendars',
                    data: JSON.stringify({"calIDs": calIDs, "isPresent": (isPresent == 1) ? false : true})
                }, function (data) {
                    if (isPresent == 1) {
                        res = str.replace('✅', '❌');
                    } else {
                        res = str.replace('❌', '✅');
                    }

                    var elt = [calIDs, res];
                    deferredsSH[i++] = elt;
                })
            );

        });

        $.when.apply($, deferredsSH).done(function (data) {
            //addShareCalendarPresence(type);

            deferredsSH.forEach(function (element) {
                $('#select-calendar-presence option[value="' + element[0] + '"]').text(element[1]);
            });

            // we update the sidebar and the calendar too
            switch (type) {
                case 'personal':
                    addPersonalCalendars();
                    break;
                case 'reservation':
                    addReservationCalendars();
                    break;
                case 'group':
                    addGroupCalendars();
                    break;
                case 'share':
                    addShareCalendars();
                    break;
            }
            window.CRM.calendar.refetchEvents();
            $("#calendar-show-hide option:first").attr('selected', 'selected');
        });
    });

    modal.modal("show");
}

$("#manage-all-calendars").on('click', function (e) {
    createPresenceManager('personal');
});

$("#manage-all-groups").on('click', function (e) {
    createPresenceManager('group');
});

$("#manage-all-reservation").on('click', function (e) {
    createPresenceManager('reservation');
});

$("#manage-all-shared").on('click', function (e) {
    createPresenceManager('share');
});


// the add people to calendar

function addPersonsFromCalendar(calendarId) {
    $('#select-share-persons').find('option').remove();

    window.CRM.APIRequest({
        method: 'POST',
        path: 'calendar/getinvites',
        data: JSON.stringify({"calIDs": calendarId})
    }, function (data) {
        var elt = document.getElementById("select-share-persons");
        var len = data.length;

        for (i = 0; i < len; ++i) {
            if (data[i].access == 2) {
                var option = document.createElement("option");

                option.text = i18next.t("[👀  ]") + " " + data[i].principal.replace("principals/", "");
                option.value = data[i].principal;

                elt.appendChild(option);
            } else if (data[i].access == 3) {
                var option = document.createElement("option");

                option.text = i18next.t("[👀 ✐]") + " " + data[i].principal.replace("principals/", "");
                option.value = data[i].principal;

                elt.appendChild(option);
            }

        }
    });
}

function BootboxContentShare() {
    var frm_str = '<div>'
        + '<div class="row">'
        + '<div class="col-md-4">'
        + '<span style="color: red">*</span>' + i18next.t("With") + ":"
        + '</div>'
        + '<div class="col-md-8">'
        + '<select size="6" class="form-control BootboxContentShare" id="select-share-persons" multiple>'
        + '</select>'
        + '</div>'
        + '<br/>'
        + '<br/>'
        + '</div>'
        + '<div class="row">'
        + '<div class="col-md-4"><span style="color: red">*</span>' + i18next.t("Set Rights") + ":</div>"
        + '<div class="col-md-8">'
        + '<select name="person-group-Id-Share" id="person-group-rights" class="form-control form-control-sm"'
        + 'style="width:100%;margin-bottom:15px" data-placeholder="text to place">'
        + '<option value="0">' + i18next.t("Select your rights") + " [👀  ]" + i18next.t("or") + " [👀 ✐]" + ' -- </option>'
        + '<option value="1">' + i18next.t("[👀  ]") + ' -- ' + i18next.t("[R ]") + '</option>'
        + '<option value="2">' + i18next.t("[👀 ✐]") + ' -- ' + i18next.t("[RW]") + '</option>'
        + '</select>'
        + '</div>'
        + '</div>'
        + '<div class="row div-title">'
        + '<div class="col-md-4"><span style="color: red">*</span>' + i18next.t("Send email notification") + ":</div>"
        + '<div class="col-md-8">'
        + '<input id="sendEmail" type="checkbox">'
        + '</div>'
        + '</div>'
        + '<div class="row div-title">'
        + '<div class="col-md-4"><span style="color: red">*</span>' + i18next.t("Add persons/Family/groups") + ":</div>"
        + '<div class="col-md-8">'
        + '<select name="person-group-Id-Share" id="person-group-Id-Share" class="form-control select2"'
        + 'style="width:100%">'
        + '</select>'
        + '</div>'
        + '</div>'
        + '</div>';

    var object = $('<div/>').html(frm_str).contents();

    return object
}

function createShareWindow(calIDs) {
    var modal = bootbox.dialog({
        message: BootboxContentShare(),
        title: i18next.t("Share your Calendar"),
        size: "large",
        buttons: [
            {
                label: '<i class="fas fa-times"></i> ' + i18next.t("Delete"),
                className: "btn btn-warning",
                callback: function () {
                    bootbox.confirm(i18next.t("Are you sure, you want to delete this Share ?"), function (result) {
                        if (result) {
                            $('#select-share-persons :selected').each(function (i, sel) {
                                var principal = $(sel).val();

                                window.CRM.APIRequest({
                                    method: 'POST',
                                    path: 'calendar/sharedelete',
                                    data: JSON.stringify({"calIDs": calIDs, "principal": principal})
                                }, function (data) {
                                    $("#select-share-persons option[value='" + principal + "']").remove();
                                    window.CRM.calendar.refetchEvents();
                                });
                            });
                        }
                    });
                    return false;
                }
            },
            {
                label: i18next.t("Stop sharing"),
                className: "btn btn-danger",
                callback: function () {
                    bootbox.confirm(i18next.t("Are you sure, you want to stop sharing your document ?"), function (result) {
                        if (result) {
                            window.CRM.APIRequest({
                                method: 'POST',
                                path: 'calendar/sharestop',
                                data: JSON.stringify({"calIDs": calIDs})
                            }, function (data) {
                                addPersonsFromCalendar(calIDs);
                                modal.modal("hide");
                            });
                        }
                    });
                    return false;
                }
            },
            {
                label: '<i class="fas fa-check"></i> ' + i18next.t("Ok"),
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

    $("#person-group-Id-Share").select2({
        language: window.CRM.shortLocale,
        minimumInputLength: 2,
        placeholder: " -- " + i18next.t("Person or Family or Group") + " -- ",
        allowClear: true, // This is for clear get the clear button if wanted
        ajax: {
            url: function (params) {
                return window.CRM.root + "/api/people/search/" + params.term;
            },
            dataType: 'json',
            delay: 250,
            data: "",
            processResults: function (data, params) {
                return {results: data};
            },
            headers: {
                "Authorization" : "Bearer "+window.CRM.jwtToken
            },
            cache: true
        }
    });

    $("#person-group-rights").on('change',function () {
        var rightAccess = $(this).val();
        var i = 0;

        $('#select-share-persons :selected').each(function (i, sel) {
            var principal = $(sel).val();
            var str = $(sel).text();

            window.CRM.APIRequest({
                method: 'POST',
                path: 'calendar/setrights',
                data: JSON.stringify({"calIDs": calIDs, "principal": principal, "rightAccess": rightAccess})
            }, function (data) {
                if (rightAccess == 1) {
                    res = str.replace(i18next.t("[👀 ✐]"), i18next.t("[👀  ]"));
                } else {
                    res = str.replace(i18next.t("[👀  ]"), i18next.t("[👀 ✐]"));
                }

                $('#select-share-persons option[value="' + principal + '"]').text(res);
            });
        });

        $("#person-group-rights option:first").attr('selected', 'selected');
    });

    $("#select-share-persons").on('change',function () {
        $("#person-group-rights").val(0);
    });

    $("#person-group-Id-Share").on("select2:select", function (e) {
        var notification = ($("#sendEmail").is(':checked')) ? 1 : 0;

        if (e.params.data.personID !== undefined) {
            window.CRM.APIRequest({
                method: 'POST',
                path: 'calendar/shareperson',
                data: JSON.stringify({
                    "calIDs": calIDs,
                    "personID": e.params.data.personID,
                    "notification": notification
                })
            }, function (data) {
                addPersonsFromCalendar(calIDs);
            });
        } else if (e.params.data.groupID !== undefined) {
            window.CRM.APIRequest({
                method: 'POST',
                path: 'calendar/sharegroup',
                data: JSON.stringify({"calIDs": calIDs, "groupID": e.params.data.groupID, "notification": notification})
            }, function (data) {
                addPersonsFromCalendar(calIDs);
            });
        } else if (e.params.data.familyID !== undefined) {
            window.CRM.APIRequest({
                method: 'POST',
                path: 'calendar/sharefamily',
                data: JSON.stringify({
                    "calIDs": calIDs,
                    "familyID": e.params.data.familyID,
                    "notification": notification
                })
            }, function (data) {
                addPersonsFromCalendar(calIDs);
            });
        }
    });

    addPersonsFromCalendar(calIDs);
    modal.modal('show');

    // this will ensure that image and table can be focused
    $(document).on('focusin', function (e) {
        e.stopImmediatePropagation();
    });
}

$('body').on('click', '#manage-cal-group', function () {
    var calIDs = $(this).data("id");
    var type = $(this).data("type");

    window.CRM.APIRequest({
        method: 'POST',
        path: 'calendar/info',
        data: JSON.stringify({"calIDs": calIDs, "type": type})
    }, function (data) {
        var allButtons = {};

        var buttonDelete = {
            delete: {
                label: i18next.t("Delete"),
                className: 'btn-danger',
                callback: function () {

                    bootbox.confirm({
                        title: i18next.t("Are you sure?"),
                        message: i18next.t("You'll lose the calendar, the events and all the share calendars too. This cannot be undone."),
                        callback: function (result) {
                            if (result) {
                                window.CRM.APIRequest({
                                    method: 'POST',
                                    path: 'calendar/delete',
                                    data: JSON.stringify({"calIDs": calIDs})
                                }, function (data) {
                                    if (type == "personal") {
                                        addPersonalCalendars();
                                    } else if (type == "reservation") {
                                        addReservationCalendars();
                                    }
                                    window.CRM.calendar.refetchEvents();
                                });
                            }
                        }
                    });
                }
            }
        };

        var buttonManage = {
            manage: {
                label: i18next.t("Manage"),
                className: 'btn-info',
                callback: function () {
                    createShareWindow(calIDs);
                    return true;
                }
            }
        };

        var buttonOk = {
            Ok: {
                label: '<i class="fas fa-check"></i> ' + i18next.t("Ok"),
                className: 'btn-primary',
                callback: function () {
                    return true;
                }
            }
        };

        if (type == "personal" || type == "reservation" && data.isAdmin == true) {
            allButtons = $.extend(allButtons, buttonDelete, buttonManage, buttonOk);
        } else if (type == "group" && data.isAdmin == true) {
            allButtons = $.extend(allButtons, buttonManage, buttonOk);
        } else if (type == "group" && data.isAdmin == false) {
            allButtons = $.extend(allButtons, buttonOk);
        } else if (type == "shared") {
            allButtons = $.extend(allButtons, buttonOk);
        }


        var dialog = bootbox.dialog({
            title: i18next.t("Calendar Management for") + " : " + data.title,
            message: i18next.t(data.message),
            size: "large",
            buttons: allButtons
        });
    });

});

//
// end off add people to calendar
//


$('body').on('click', '.editCalendarName', function () {
    var calIDs = $(this).data("id");
    var name = $(this).data("name");

    bootbox.prompt({
        title: i18next.t("Modify Calendar Name"),
        inputType: 'text',
        value: name,
        callback: function (title) {
            if (title !== null && title != '') {
                window.CRM.APIRequest({
                    method: 'POST',
                    path: 'calendar/modifyname',
                    data: JSON.stringify({"title": title, "calIDs": calIDs})
                }, function (data) {
                    addPersonalCalendars();
                });
            }
        }
    });
});

$('body').on('click', '.editGroupName', function () {
    var calIDs = $(this).data("id");
    var name = $(this).data("name");

    bootbox.prompt({
        title: i18next.t("Modify Group Name"),
        inputType: 'text',
        value: name,
        callback: function (title) {
            if (title !== null && title != '') {
                window.CRM.APIRequest({
                    method: 'POST',
                    path: 'calendar/modifyname',
                    data: JSON.stringify({"title": title, "calIDs": calIDs})
                }, function (data) {
                    addGroupCalendars();
                });
            }
        }
    });
});

$('body').on('click', '.editReservationName', function () {
    var calIDs = $(this).data("id");
    var name = $(this).data("name");

    bootbox.prompt({
        title: i18next.t("Modify Resource Name"),
        inputType: 'text',
        value: name,
        callback: function (title) {
            if (title !== null && title != '') {
                window.CRM.APIRequest({
                    method: 'POST',
                    path: 'calendar/modifyname',
                    data: JSON.stringify({"calIDs": calIDs, "title": title})
                }, function (data) {
                    addReservationCalendars();
                });
            }
        }
    });
});

$('body').on('click', '.editShareName', function () {
    var calIDs = $(this).data("id");
    var name = $(this).data("name");

    bootbox.prompt({
        title: i18next.t("Modify Share Name"),
        inputType: 'text',
        value: name,
        callback: function (title) {
            if (title !== null && title != '') {
                window.CRM.APIRequest({
                    method: 'POST',
                    path: 'calendar/modifyname',
                    data: JSON.stringify({"title": title, "calIDs": calIDs})
                }, function (data) {
                    addShareCalendars();
                });
            }
        }
    });
});

$('body').on('click', '#reservation-info', function () {
    var title = $(this).data("title");
    var content = $(this).data("content");
    var id = $(this).data("id");
    var calType = $(this).data("caltype");

    bootbox.confirm({
        title: i18next.t("Resource Info for") + ' : ' + title,
        message: '<table width=100%>'
            + '<tr>'
            + '<td>'
            + i18next.t("Select a resource type") + ':'
            + '</td>'
            + '<td>'
            + '<select class="form-control form-control-sm" id="typeCalendar" name="typeCalendar">'
            + '<option value="2"' + ((calType == 2) ? ' selected' : '') + '>' + i18next.t("Room") + '</option>'
            + '<option value="3"' + ((calType == 3) ? ' selected' : '') + '>' + i18next.t("Computer") + '</option>'
            + '<option value="4"' + ((calType == 4) ? ' selected' : '') + '>' + i18next.t("Video") + '</option>'
            + '</select>'
            + '<br>'
            + '</td>'
            + '</tr>'
            + '<tr>'
            + '<td>'
            + i18next.t("Description") + ':'
            + '</td>'
            + '<td>'
            + ' <input class="bootbox-input bootbox-input-text form-control form-control-sm" type="text" id="descCalendar" value="' + content + '"><br>'
            + '</td>'
            + '</tr>'
            + '</table>',
        buttons: {
            cancel: {
                label: '<i class="fas fa-times"></i> ' + i18next.t("Cancel")
            },
            confirm: {
                label: '<i class="fas fa-check"></i> ' + i18next.t("OK"),
                className: 'btn-primary'
            }
        },
        callback: function (result) {
            var desc = $("#descCalendar").val();
            var type = Number($("#typeCalendar").val());

            if (result) {
                window.CRM.APIRequest({
                    method: 'POST',
                    path: 'calendar/setDescriptionType',
                    data: JSON.stringify({"calIDs": id, "desc": desc, "type": type})
                }, function (data) {
                    if (data.status == "success") {
                        addReservationCalendars();
                    } else {
                        window.CRM.DisplayAlert(i18next.t("Error"), i18next.t("Only administrators have the right to change the Resource description"));
                    }
                });
            }
        }
    });
});

function addPersonalCalendars() {
    $('#cal-list').empty();

    window.CRM.APIRequest({
        method: 'POST',
        path: 'calendar/getallforuser',
        data: JSON.stringify({"type": "personal", "onlyvisible": false, "allCalendars": false})
    }, function (res) {
        var data = res.calendars;
        var len = data.length;

        var visibles = 0;

        for (i = 0; i < len; ++i) {
            if (data[i].visible) {
                visibles += 1;
            }

            $('#cal-list').append('<li class="list-group-item" style="cursor: pointer;">' +
                '<div class="row">' +
                '   <div class="col-1">' +
                '       <input id="personal-checkbox-'+ data[i].calendarID +'" type="checkbox" class="check-calendar calendar-sidebar-checkbox" data-id="' + data[i].calendarID + '"' + ((data[i].visible) ? "checked" : "") + '>' +
                '   </div>' +
                '   <div class="col-10">' +
                '       <div class="input-group my-colorpicker-global my-colorpicker1' + i + ' colorpicker-element" data-id="' + data[i].calendarID + '">' +
                '           <div class="editCalendarName text-center-calendar"  data-id="' + data[i].calendarID + '" data-name="' + data[i].calendarName + '">' + data[i].icon + ' ' + data[i].calendarName.substring(0, 22) + '</div>' +
                '           <div class="input-group-addon">' +
                '               <i style="background-color:' + data[i].calendarColor + ';"></i>' +
                '           </div>' +
                '       </div>' +
                '   </div>' +
                '   <div class="col-1">' +
                '           <div class="calendar-sidebar-info-button">' +
                '              <i class="fas fa-info-circle" id="manage-cal-group" data-type="personal" data-id="' + data[i].calendarID + '"></i>' +
                '           </div>' +
                '   </div>' +
                '</div>' +
                '</li>');
            $(".my-colorpicker1" + i).colorpicker({
                color: data[i].calendarColor,
                inline: false,
                horizontal: true,
                right: true
            });

            $(".my-colorpicker1" + i).on('changeColor', function (e) {
                var calIDs = $(this).data("id");
                var color = $(this).data('colorpicker').color.toHex();//.toString('hex');

                window.CRM.APIRequest({
                    method: 'POST',
                    path: 'calendar/setcolor',
                    data: JSON.stringify({"calIDs": calIDs, "color": color})
                }, function (data) {
                    // we reload all the events
                    window.CRM.calendar.refetchEvents();
                });
            });
        }

        var elt = document.getElementById("check-uncheck-personal-calendar");
        if (visibles == 0) {
            elt.classList.add("fa-square");
            elt.classList.remove("fa-check-square");
        } else {
            elt.classList.add("fa-check-square");
            elt.classList.remove("fa-square");
        }
    });
}

function addGroupCalendars() {
    $('#group-list').empty();

    window.CRM.APIRequest({
        method: 'POST',
        path: 'calendar/getallforuser',
        data: JSON.stringify({"type": "group", "onlyvisible": false, "allCalendars": false})
    }, function (res) {
        var data = res.calendars;
        var len = data.length;

        var visibles = 0;

        for (i = 0; i < len; ++i) {

            if (data[i].visible) {
                visibles += 1;
            }

            $('#group-list').append('<li class="list-group-item" style="cursor: pointer;">' +
                '<div class="row">' +
                '   <div class="col-1">' +
                '       <input id="Group-checkbox-'+ data[i].calendarID +'" type="checkbox" class="check-calendar calendar-sidebar-checkbox" data-id="' + data[i].calendarID + '"' + ((data[i].visible) ? "checked" : "") + '>' +
                '   </div>' +
                '   <div class="col-10">' +
                '       <div class="input-group my-colorpicker-global my-colorpicker1' + i + ' colorpicker-element" data-id="' + data[i].calendarID + '">' +
                '           <div class="editGroupName text-center-calendar"  data-id="' + data[i].calendarID + '" data-name="' + data[i].calendarName + '">' +
                data[i].icon + '<i class="fas fa-users"></i> ' +
                data[i].calendarName.substring(0, 22) +
                '</div>' +
                '           <div class="input-group-addon">' +
                '               <i style="background-color:' + data[i].calendarColor + ';"></i>' +
                '           </div>' +
                '       </div>' +
                '   </div>' +
                '   <div class="col-1">' +
                '           <div class="calendar-sidebar-info-button">' +
                '              <i class="fas fa-info-circle" id="manage-cal-group" data-type="group" data-id="' + data[i].calendarID + '"></i>' +
                '           </div>' +
                '   </div>' +
                '</div>' +
                '</li>');

            // '<li class="list-group-item" style="cursor: pointer;"><div class="input-group my-colorpicker-global my-colorpicker1'+i+' colorpicker-element" data-id="'+data[i].calendarID+'"><input id="checkBox" type="checkbox" class="check-calendar" data-id="'+data[i].calendarID+'"'+((data[i].visible)?"checked":"")+'>'+data[i].icon+'<i class="fa pull-right fa-info-circle"  style="font-size: 1em" style="color:gray;padding-right:10px;" id="manage-cal-group" data-type="group" data-id="'+data[i].calendarID+'"></i> <span class="editGroupName"  data-id="'+data[i].calendarID+'">'+data[i].calendarName+'</span><div class="input-group-addon" style="border-left: 1"><i style="background-color:'+data[i].calendarColor+';"></i></li>');

            $(".my-colorpicker1" + i).colorpicker({
                color: data[i].calendarColor,
                inline: false,
                horizontal: true,
                right: true
            });

            $(".my-colorpicker1" + i).on('changeColor', function (e) {
                var calIDs = $(this).data("id");
                var color = $(this).data('colorpicker').color.toHex();//.toString('hex');

                window.CRM.APIRequest({
                    method: 'POST',
                    path: 'calendar/setcolor',
                    data: JSON.stringify({"calIDs": calIDs, "color": color})
                }, function (data) {
                    // we reload all the events
                    window.CRM.calendar.refetchEvents();
                });
            });
        }

        var elt = document.getElementById("check-uncheck-all-group-calendar");
        if (visibles == 0) {
            elt.classList.add("fa-square");
            elt.classList.remove("fa-check-square");
        } else {
            elt.classList.add("fa-check-square");
            elt.classList.remove("fa-square");
        }
    });
}

function addReservationCalendars() {
    $('#reservation-list').empty();

    window.CRM.APIRequest({
        method: 'POST',
        path: 'calendar/getallforuser',
        data: JSON.stringify({"type": "reservation", "onlyvisible": false, "allCalendars": false})
    }, function (res) {
        var data = res.calendars;
        var len = data.length;
        var visibles = 0;

        for (i = 0; i < len; ++i) {
            var icon = '';

            if (data[i].calType == 2) {
                icon = '<i class="fas fa-building"></i>';
            } else if (data[i].calType == 3) {
                icon = '<i class="fab fa-windows"></i>';
            } else if (data[i].calType == 4) {
                icon = '<i class="fas fa-video"></i>';
            }

            if (data[i].visible) {
                visibles += 1;
            }

            var infoLine = '<li class="list-group-item" style="cursor: pointer;">' +
                '<div class="row row-calendar-resource">' +
                '   <div class="col-1">' +
                '       <input id="Reservation-checkbox-'+ data[i].calendarID +'" type="checkbox" class="check-calendar calendar-sidebar-checkbox" data-id="' + data[i].calendarID + '"' + ((data[i].visible) ? "checked" : "") + '>' +
                '   </div>' +
                '   <div class="col-9">' +
                '       <div class="input-group my-colorpicker-global my-colorpicker1' + i + ' colorpicker-element" data-id="' + data[i].calendarID + '">' +
                '           <div class="editReservationName text-center-calendar"  data-id="' + data[i].calendarID + '" data-name="' + data[i].calendarName + '">' +
                data[i].icon + icon + ' ' +
                data[i].calendarName.substring(0, 22) +
                '</div>' +
                '           <div class="input-group-addon">' +
                '               <i style="background-color:' + data[i].calendarColor + ';"></i>' +
                '           </div>' +
                '       </div>' +
                '   </div>' +
                '   <div class="col-2">' +
                '           <div class="calendar-sidebar-info-button" style="width:40px">';

            if (data[i].isAdmin) {
                infoLine += '<i class="fas fa-cog calendar-sidebar-info-button" data-title="' + data[i].calendarName + '" data-caltype="' + data[i].calType + '" data-content="' + data[i].desc + '" style="font-size: 1em" style="color:gray;padding-right:10px;" id="reservation-info" data-type="reservation" data-id="' + data[i].calendarID + '"></i>';
            }

            infoLine += '              <i class="fas fa-info-circle calendar-sidebar-info-button" id="manage-cal-group" data-type="reservation" data-id="' + data[i].calendarID + '"></i>' +
                '           </div>' +
                '   </div>' +
                '</div>' +
                '</li>';


            $('#reservation-list').append(infoLine);

            $(".my-colorpicker1" + i).colorpicker({
                color: data[i].calendarColor,
                inline: false,
                horizontal: true,
                right: true
            });

            $(".my-colorpicker1" + i).on('changeColor', function (e) {
                var calIDs = $(this).data("id");
                var color = $(this).data('colorpicker').color.toHex();//.toString('hex');

                window.CRM.APIRequest({
                    method: 'POST',
                    path: 'calendar/setcolor',
                    data: JSON.stringify({"calIDs": calIDs, "color": color})
                }, function (data) {
                    // we reload all the events
                    window.CRM.calendar.refetchEvents();
                });
            });
        }

        var elt = document.getElementById("check-uncheck-all-reservation-calendar");
        if (visibles == 0) {
            elt.classList.add("fa-square");
            elt.classList.remove("fa-check-square");
        } else {
            elt.classList.add("fa-check-square");
            elt.classList.remove("fa-square");
        }
    });
}

function addShareCalendars() {
    $('#share-list').empty();

    window.CRM.APIRequest({
        method: 'POST',
        path: 'calendar/getallforuser',
        data: JSON.stringify({"type": "share", "onlyvisible": false, "allCalendars": false})
    }, function (res) {
        var data = res.calendars;
        var len = data.length;

        var visibles = 0;

        for (i = 0; i < len; ++i) {
            var icon = '';

            if (data[i].calType == 2) {
                icon = '&nbsp<i class="fas fa-building"></i>&nbsp';
            } else if (data[i].calType == 3) {
                icon = '&nbsp<i class="fab fa-windows"></i>&nbsp;';
            } else if (data[i].calType == 4) {
                icon = '&nbsp<i class="fas fa-video"></i>&nbsp;';
            }

            if (data[i].visible) {
                visibles += 1;
            }

            $('#share-list').append('<li class="list-group-item" style="cursor: pointer;">' +
                '<div class="row">' +
                '   <div class="col-1">' +
                '       <input id="Share-checkbox-'+ data[i].calendarID +'" type="checkbox" class="check-calendar calendar-sidebar-checkbox" data-id="' + data[i].calendarID + '"' + ((data[i].visible) ? "checked" : "") + '>' +
                '   </div>' +
                '   <div class="col-10">' +
                '       <div class="input-group my-colorpicker-global my-colorpicker1' + i + ' colorpicker-element" data-id="' + data[i].calendarID + '">' +
                '           <div class="editShareName text-center-calendar"  data-id="' + data[i].calendarID + '" data-name="' + data[i].calendarName + '">' +
                data[i].icon + icon + ' ' +
                data[i].calendarName.substring(0, 22) +
                '</div>' +
                '           <div class="input-group-addon">' +
                '               <i style="background-color:' + data[i].calendarColor + ';"></i>' +
                '           </div>' +
                '       </div>' +
                '   </div>' +
                '   <div class="col-1">' +
                '           <div class="calendar-sidebar-info-button">' +
                '              <i class="fas fa-info-circle" id="manage-cal-group" data-type="shared" data-id="' + data[i].calendarID + '"></i>' +
                '           </div>' +
                '   </div>' +
                '</div>' +
                '</li>');

            $(".my-colorpicker1" + i).colorpicker({
                color: data[i].calendarColor,
                inline: false,
                horizontal: true,
                right: true
            });

            $(".my-colorpicker1" + i).on('changeColor', function (e) {
                var calIDs = $(this).data("id");
                var color = $(this).data('colorpicker').color.toHex();//.toString('hex');

                window.CRM.APIRequest({
                    method: 'POST',
                    path: 'calendar/setcolor',
                    data: JSON.stringify({"calIDs": calIDs, "color": color})
                }, function (data) {
                    // we reload all the events
                    window.CRM.calendar.refetchEvents();
                });
            });
        }

        var elt = document.getElementById("check-uncheck-all-shared-calendar");
        if (visibles == 0) {
            elt.classList.add("fa-square");
            elt.classList.remove("fa-check-square");
        } else {
            elt.classList.add("fa-check-square");
            elt.classList.remove("fa-square");
        }

    });
}

var addAllCalendarsCountimes = 1;

window.CRM.addAllCalendars = function f() {
    if (addAllCalendarsCountimes != 2) {
        // Add all the calendars
        addPersonalCalendars();
        addGroupCalendars();
        addReservationCalendars();
        addShareCalendars();
    }
    addAllCalendarsCountimes++;
}

window.CRM.ElementListener('#check-uncheck-personal-calendar', 'click', function(event) {
    window.CRM.APIRequest({
        method: 'POST',
        path: 'calendar/getallforuser',
        data: JSON.stringify({"type": "personal", "onlyvisible": false, "allCalendars": false})
    }, function (res) {
        var data = res.calendars;
        var len = data.length;
        var isChecked = (res.visibles > 0)?true:false;

        var allCalendars = [];
        for (i = 0; i < len; ++i) {
            var elt = document.getElementById('personal-checkbox-'+ data[i].calendarID);
            elt.checked = !isChecked;
            allCalendars.push(data[i].calendarID);
        }

        window.CRM.APIRequest({
            method: 'POST',
            path: 'calendar/setckeckedselected',
            data: JSON.stringify({"allCalIDs": allCalendars, "isChecked": !isChecked})
        }, function (data) {
            // we reload all the events
            window.CRM.calendar.refetchEvents();
        });

        var globalCheckbox = document.getElementById("check-uncheck-personal-calendar");
        if (isChecked == true) {
            globalCheckbox.classList.add("fa-square");
            globalCheckbox.classList.remove("fa-check-square");
        } else {
            globalCheckbox.classList.add("fa-check-square");
            globalCheckbox.classList.remove("fa-square");
        }
    });
});

window.CRM.ElementListener('#check-uncheck-all-group-calendar', 'click', function(event) {
    window.CRM.APIRequest({
        method: 'POST',
        path: 'calendar/getallforuser',
        data: JSON.stringify({"type": "group", "onlyvisible": false, "allCalendars": false})
    }, function (res) {
        var data = res.calendars;
        var len = data.length;
        var isChecked = (res.visibles > 0)?true:false;

        var allCalendars = [];
        for (i = 0; i < len; ++i) {
            var elt = document.getElementById('Group-checkbox-'+ data[i].calendarID);
            elt.checked = !isChecked;
            allCalendars.push(data[i].calendarID);
        }

        window.CRM.APIRequest({
            method: 'POST',
            path: 'calendar/setckeckedselected',
            data: JSON.stringify({"allCalIDs": allCalendars, "isChecked": !isChecked})
        }, function (data) {
            // we reload all the events
            window.CRM.calendar.refetchEvents();
        });

        var globalCheckbox = document.getElementById("check-uncheck-all-group-calendar");
        if (isChecked == true) {
            globalCheckbox.classList.add("fa-square");
            globalCheckbox.classList.remove("fa-check-square");
        } else {
            globalCheckbox.classList.add("fa-check-square");
            globalCheckbox.classList.remove("fa-square");
        }
    });
});

window.CRM.ElementListener('#check-uncheck-all-reservation-calendar', 'click', function(event) {
    window.CRM.APIRequest({
        method: 'POST',
        path: 'calendar/getallforuser',
        data: JSON.stringify({"type": "reservation", "onlyvisible": false, "allCalendars": false})
    }, function (res) {
        var data = res.calendars;
        var len = data.length;
        var isChecked = (res.visibles > 0)?true:false;

        var allCalendars = [];
        for (i = 0; i < len; ++i) {
            var elt = document.getElementById('Reservation-checkbox-'+ data[i].calendarID);
            elt.checked = !isChecked;
            allCalendars.push(data[i].calendarID);
        }

        window.CRM.APIRequest({
            method: 'POST',
            path: 'calendar/setckeckedselected',
            data: JSON.stringify({"allCalIDs": allCalendars, "isChecked": !isChecked})
        }, function (data) {
            // we reload all the events
            window.CRM.calendar.refetchEvents();
        });

        var globalCheckbox = document.getElementById("check-uncheck-all-reservation-calendar");
        if (isChecked == true) {
            globalCheckbox.classList.add("fa-square");
            globalCheckbox.classList.remove("fa-check-square");
        } else {
            globalCheckbox.classList.add("fa-check-square");
            globalCheckbox.classList.remove("fa-square");
        }
    });
});

window.CRM.ElementListener('#check-uncheck-all-shared-calendar', 'click', function(event) {
    window.CRM.APIRequest({
        method: 'POST',
        path: 'calendar/getallforuser',
        data: JSON.stringify({"type": "share", "onlyvisible": false, "allCalendars": false})
    }, function (res) {
        var data = res.calendars;
        var len = data.length;
        var isChecked = (res.visibles > 0)?true:false;

        var allCalendars = [];
        for (i = 0; i < len; ++i) {
            var elt = document.getElementById('Share-checkbox-'+ data[i].calendarID);
            elt.checked = !isChecked;
            allCalendars.push(data[i].calendarID);
        }

        window.CRM.APIRequest({
            method: 'POST',
            path: 'calendar/setckeckedselected',
            data: JSON.stringify({"allCalIDs": allCalendars, "isChecked": !isChecked})
        }, function (data) {
            // we reload all the events
            window.CRM.calendar.refetchEvents();
        });

        var globalCheckbox = document.getElementById("check-uncheck-all-shared-calendar");
        if (isChecked == true) {
            globalCheckbox.classList.add("fa-square");
            globalCheckbox.classList.remove("fa-check-square");
        } else {
            globalCheckbox.classList.add("fa-check-square");
            globalCheckbox.classList.remove("fa-square");
        }
    });
});

window.CRM.addAllCalendars();


