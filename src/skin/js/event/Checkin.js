//
//  This code is under copyright not under MIT Licence
//  copyright   : 2018 Philippe Logel all right reserved.
//                This code can't be included in another software.
//
//  Updated : 2020/06/18
//

window.CRM.editor = null;

$(function() {

    const addEvent = (dateStart, dateEnd) => {
        window.CRM.APIRequest({
            method: 'POST',
            path: 'calendar/numberofcalendars',
        },function(data) {
            if (data.CalendarNumber > 0) {
                if (window.CRM.editor != null) {
                    CKEDITOR.remove(window.CRM.editor);
                    window.CRM.editor = null;
                }

                modal = createEventEditorWindow(dateStart, dateEnd, 'createEvent', 0, '', 'v2/calendar/events/checkin');

                // we add the calendars and the types
                addCalendars();
                addCalendarEventTypes(-1, true);

                // finish installing the window
                installAndfinishEventEditorWindow();

                $("#typeEventrecurrence").prop("disabled", true);
                $("#endDateEventrecurrence").prop("disabled", true);

                $('#EventCalendar option:first-child').attr("selected", "selected");

                modal.modal("show");

                initMap();
            } else {
                window.CRM.DisplayAlert(i18next.t("Error"),i18next.t("To add an event, You have to create a calendar or activate one first."));
            }
        });
    }

    $("#addFreeAttendees").on('click',function () {
        var counts = {};

        $(".freeAttendeesCount").each(function(element, index, set) {
            var countid = $(this).data('countid');
            var val     = $(this).val();

            counts[countid] = val;
        });

        var fieldText = $("#fieldText").val();


        window.CRM.APIRequest({
            method: 'POST',
            path: 'attendees/addFreeAttendees',
            data: JSON.stringify({"eventID": window.CRM.EventID, "fieldText": fieldText, "counts": counts})
        },function (data) {
            window.CRM.DisplayAlert(i18next.t("Free Event Attendees"), i18next.t("Successfully Added!"));
        });
    })



    $('#add-event').on('click', function (e) {
        var fmt = 'YYYY-MM-DD HH:mm:ss';

        var dateStart = moment().format(fmt);
        var dateEnd = moment().format(fmt);

        addEvent(dateStart, dateEnd);
    });


    $(document).on("click", ".PersonCheckinChangeState", function () {
        var checked = $(this).is(':checked');
        var personID = $(this).data("personid");
        var eventID = $(this).data("eventid");

        window.CRM.APIRequest({
            method: 'POST',
            path: 'attendees/checkin',
            data: JSON.stringify({"checked": checked, "personID": personID, "eventID": eventID})
        },function (data) {
            if (data.status) {
                window.CRM.dataT.ajax.reload(null, false);
            }
        });
    });

    $(document).on("click", ".PersonCheckoutChangeState", function () {
        var checked = $(this).is(':checked');
        var personID = $(this).data("personid");
        var eventID = $(this).data("eventid");

        window.CRM.APIRequest({
            method: 'POST',
            path: 'attendees/checkout',
            data: JSON.stringify({"checked": checked, "personID": personID, "eventID": eventID})
        },function (data) {
            if (data.status) {
                window.CRM.dataT.ajax.reload(null, false);
            }
        });
    });

    $(document).on("click", "#uncheckAllCheckin", function () {
        var eventID = $(this).data("id");

        window.CRM.APIRequest({
            method: 'POST',
            path: 'attendees/uncheckAll',
            data: JSON.stringify({"eventID": eventID, "type": 1})
        },function (data) {
            window.CRM.dataT.ajax.reload(null, false);
        });
    });

    $(document).on("click", "#checkAllCheckin", function () {
        var eventID = $(this).data("id");

        window.CRM.APIRequest({
            method: 'POST',
            path: 'attendees/checkAll',
            data: JSON.stringify({"eventID": eventID, "type": 1})
        },function (data) {
            window.CRM.dataT.ajax.reload(null, false);
        });
    });

    $(document).on("click", "#uncheckAllCheckout", function () {
        var eventID = $(this).data("id");

        window.CRM.APIRequest({
            method: 'POST',
            path: 'attendees/uncheckAll',
            data: JSON.stringify({"eventID": eventID, "type": 2})
        },function (data) {
            window.CRM.dataT.ajax.reload();
        });
    });

    $(document).on("click", "#checkAllCheckout", function () {
        var eventID = $(this).data("id");

        window.CRM.APIRequest({
            method: 'POST',
            path: 'attendees/checkAll',
            data: JSON.stringify({"eventID": eventID, "type": 2})
        },function (data) {
            window.CRM.dataT.ajax.reload();
        });
    });

    $(document).on("click", "#checkAllCheckout", function () {
        var eventID = $(this).data("id");

        window.CRM.APIRequest({
            method: 'POST',
            path: 'attendees/checkAll',
            data: JSON.stringify({"eventID": eventID, "type": 2})
        },function (data) {
            window.CRM.dataT.ajax.reload();
        });
    });


    $(document).on("click", "#addAndCheckIn", function () {
        var childid = $("#addAndCheckIn").data("childid");
        var adultid = $("#addAndCheckIn").data("adultid");

        window.CRM.APIRequest({
            method: 'POST',
            path: 'attendees/addPerson',
            data: JSON.stringify({"eventID": window.CRM.EventID, "iChildID": childid, "iAdultID": adultid})
        },function (data) {
            window.CRM.dataT.ajax.reload();
            SetPersonHtml($('#childDetails'), null);
            SetPersonHtml($('#adultDetails'), null);
            $("#child").val("");
            $("#adult").val("");
        });
    });

    $(document).on("click", ".DeleteBtn", function () {
        var personId = $(this).data('id');
        var eventId = $(this).data('eventid');

        bootbox.confirm({
            title: i18next.t("User Delete Confirmation"),
            message: '<p style="color: red">' +
                i18next.t("Be carefull, You are about to delete a user from the group and therefore the entire call history. This is strongly to be avoided.") + '<br><br>' +
                i18next.t("This can't be undone") + ' !!!!!!</p>',
            callback: function (result) {
                if (result) {
                    window.CRM.APIRequest({
                        method: 'POST',
                        path: 'attendees/deletePerson',
                        data: JSON.stringify({"eventID": eventId, "personID": personId})
                    },function (data) {
                        window.CRM.dataT.ajax.reload();
                    });
                }
            }
        });
    });

    $(document).on("click", "#validateAttendees", function () {
        var eventId = window.CRM.EventID;
        var noteText = CKEDITOR.instances['NoteText'].getData();//$('form #eventNotes').val();

        window.CRM.APIRequest({
            method: 'POST',
            path: 'attendees/validate',
            data: JSON.stringify({"eventID": eventId, "noteText": noteText})
        },function (data) {
            window.location = window.CRM.root + '/v2/calendar';
        });
    });

    $(document).on("click", "#resetDetails", function () {
        SetPersonHtml($('#childDetails'), null);
        SetPersonHtml($('#adultDetails'), null);
        $("#child").val("");
        $("#adult").val("");
    });


    if (window.CRM.EventID > 0) {
        var perArr;

        dataTableConfig = {
            "language": {
                "url": window.CRM.plugin.dataTable.language.url
            },
            //pageLength: 50,
            responsive: true,
            ajax: {
                url: window.CRM.root + "/api/attendees/event/" + window.CRM.EventID,
                dataSrc: "CheckinCheckoutEvents",
                "beforeSend": function (xhr) {
                    xhr.setRequestHeader('Authorization',
                        "Bearer " +  window.CRM.jwtToken
                    );
                }    
            },
            deferRender: true,
            columns: [
                {
                    title: i18next.t('Photo'),
                    data: 'Id',
                    render: function (data, type, full, meta) {
                        return full.img;
                    }
                },
                {
                    title: i18next.t('Action'),
                    data: 'Id',
                    render: function (data, type, full, meta) {
                        return '<label>\n' +
                            '       <input ' + full.isCheckinDate + ' type="checkbox"\n' +
                            '              data-personid="' + data + '"\n' +
                            '              data-eventid="' + window.CRM.EventID + '"\n' +
                            '              class="PersonCheckinChangeState"\n' +
                            '              id="PersonCheckinChangeState-' + data + '">\n' +
                            '       <span id="presenceID' + data + '" style="color:blue"> ' + i18next.t("Checkin") + '</span>\n' +
                            '       </label>\n' +
                            '       <br/>\n' +
                            '       <label>\n' +
                            '           <input ' + full.isCheckoutDate + ' type="checkbox"\n' +
                            '               data-personid="' + data + '"\n' +
                            '               data-eventid="' + window.CRM.EventID + '"\n' +
                            '               class="PersonCheckoutChangeState"\n' +
                            '               id="PersonCheckoutChangeState-' + data + '">\n' +
                            '               <span id="presenceID' + data + '" style="color:green"> ' + i18next.t("Checkout") + '</span>\n' +
                            '       </label>';
                    }
                },

                {
                    title: i18next.t('First Name'),
                    data: 'FirstName',
                    render: function (data, type, full, meta) {
                        return data;
                    }
                },
                {
                    title: i18next.t('Last Name'),
                    data: 'LastName',
                    render: function (data, type, full, meta) {
                        return data;
                    }
                },
                {
                    title: i18next.t('Gender'),
                    data: 'Gender',
                    render: function (data, type, full, meta) {
                        return data;
                    }
                },
                {
                    title: i18next.t('Checkin Date'),
                    data: 'checkinDate',
                    render: function (data, type, full, meta) {
                        return data;
                    }
                },
                {
                    title: i18next.t('Checked In By'),
                    data: 'checkinby',
                    render: function (data, type, full, meta) {
                        return data;
                    }
                },
                {
                    title: i18next.t('Checkout Date'),
                    data: 'checkoutDate',
                    render: function (data, type, full, meta) {
                        return data;
                    }
                },
                {
                    title: i18next.t('Checkout By'),
                    data: 'checkoutby',
                    render: function (data, type, full, meta) {
                        return data;
                    }
                },

                {
                    title: i18next.t('Delete'),
                    data: 'Id',
                    render: function (data, type, full, meta) {
                        return '<i class="far fa-trash-alt DeleteBtn"' + ' data-id="' + full.Id + '" + data-eventid="' + window.CRM.EventID + '" style="color:red"></i>';
                    }
                },

            ],
            order: [2, 'asc']
        }

        $.extend(dataTableConfig, window.CRM.plugin.dataTable);

        window.CRM.dataT = $("#checkedinTable").DataTable(dataTableConfig);

        $('#checkedinTable').DataTable().page.len(50).draw();

        $("#page-length-select").on('change',function(){
            $('#checkedinTable').DataTable().page.len($(this).val()).draw();
        });

        var theme = 'n1theme,/skin/js/ckeditor/themes/n1theme/';
        if (window.CRM.bDarkMode) {
            theme = 'moono-dark,/skin/js/ckeditor/themes/moono-dark/';
        }

       
        var editor = CKEDITOR.replace('NoteText', {
            customConfig: window.CRM.root + '/skin/js/ckeditor/configs/calendar_event_editor_config.js',
            language: window.CRM.lang,
            skin:theme
        });
        

        add_ckeditor_buttons(editor);

        $('.collapse').on('shown.bs.collapse', function () {
            $(this).parent().find(".fa-chevron-down").removeClass("fa-chevron-down").addClass("fa-chevron-up");
        }).on('hidden.bs.collapse', function () {
            $(this).parent().find(".fa-chevron-up").removeClass("fa-chevron-up").addClass("fa-chevron-down");
        });

        var input = $("#child, #adult, #adultout");
        input.autocomplete({
            source: function (request, response) {
                window.CRM.APIRequest({
                    method: 'GET',
                    path: 'persons/search/' + request.term
                },function (data) {
                    console.log(data);
                    response($.map(data, function (item) {
                        return {
                            label: item.text,
                            value: item.objid,
                            obj: item
                        };
                    }));
                })
            },
            minLength: 2,
            select: function (event, ui) {
                $('[id=' + event.target.id + ']').val(ui.item.obj.text);
                $('[id=' + event.target.id + '-id]').val(ui.item.obj.objid);
                SetPersonHtml($('#' + event.target.id + 'Details'), ui.item.obj);

                if (event.target.id == "adult") {
                    $("#addAndCheckIn").data("adultid", ui.item.obj.objid)
                } else {// in the case of a child
                    $("#addAndCheckIn").data("childid", ui.item.obj.objid)
                }
                return false;
            }
        });


        const SetPersonHtml = (element, perArr) => {
            if (perArr) {
                element.html(
                    '<div class="text-center">' +
                    '<a target="_top" href="v2/people/person/view/' + perArr.objid + '"><h4>' + perArr.text + '</h4></a>' +
                    '<img src="' + window.CRM.root + '/api/persons/' + perArr.objid + '/thumbnail"' +
                    'class="initials-image profile-user-img img-responsive img-circle"> </div>'
                );
                element.removeClass('hidden');
            } else {
                element.html('');
                element.addClass('hidden');
            }
        }
    }

    /* QRCode code */
    const BootboxContent = () => {

        var frm_str = '<div>'
            +'  <div class="row">'
            +'      <div class="col-md-12">'
            +'          <div id="loadingMessage">🎥 '+ i18next.t("Unable to access video stream (please make sure you have a webcam enabled)") + '</div>'
            +'      </div>'
            +'  </div>'
            +'  <div class="row">'
            +'      <div class="col-md-12">'
            +'           <canvas id="canvas" hidden></canvas></div>'
            +'           <div id="output" hidden>'
            +'           <div id="outputMessage">' + i18next.t("no detected QR Code") + '</div>'
            +'           <div hidden><b>Data:</b> <span id="outputData"></span></div>'
            +'      </div>'
            +'  </div>';

        var object = $('<div/>').html(frm_str).contents();

        return object
    }

    $(document).on("click", "#qrcode-call", function () {

        var modal = bootbox.dialog({
            title: i18next.t("QR Code : Call the register"),
            message: BootboxContent(),
            //size: 'large',
            onShown: function(e) {
                var video = document.createElement("video");
                var canvasElement = document.getElementById("canvas");
                var canvas = canvasElement.getContext("2d");
                var loadingMessage = document.getElementById("loadingMessage");
                var outputContainer = document.getElementById("output");
                var outputMessage = document.getElementById("outputMessage");
                var outputData = document.getElementById("outputData");

                function drawLine(begin, end, color) {
                    canvas.beginPath();
                    canvas.moveTo(begin.x, begin.y);
                    canvas.lineTo(end.x, end.y);
                    canvas.lineWidth = 4;
                    canvas.strokeStyle = color;
                    canvas.stroke();
                }

                // Use facingMode: environment to attempt to get the front camera on phones
                navigator.mediaDevices.getUserMedia({ video: { facingMode: "environment" } }).then(function(stream) {
                    video.srcObject = stream;
                    window.stream = stream;
                    video.setAttribute("playsinline", true); // required to tell iOS safari we don't want fullscreen
                    video.play();
                    requestAnimationFrame(tick);
                });

                var qrcode = '';

                function tick() {
                    loadingMessage.innerText = "⌛ " + i18next.t("Loading video...");
                    if (video.readyState === video.HAVE_ENOUGH_DATA) {
                        loadingMessage.hidden = true;
                        canvasElement.hidden = false;
                        outputContainer.hidden = false;

                        canvasElement.height = video.videoHeight;
                        canvasElement.width = video.videoWidth;
                        canvas.drawImage(video, 0, 0,canvasElement.width -1, canvasElement.height-1);
                        var imageData = canvas.getImageData(0, 0, canvasElement.width, canvasElement.height);
                        var code = jsQR(imageData.data, imageData.width, imageData.height, {
                            inversionAttempts: "dontInvert",
                        });
                        if (code) {
                            drawLine(code.location.topLeftCorner, code.location.topRightCorner, "#FF3B58");
                            drawLine(code.location.topRightCorner, code.location.bottomRightCorner, "#FF3B58");
                            drawLine(code.location.bottomRightCorner, code.location.bottomLeftCorner, "#FF3B58");
                            drawLine(code.location.bottomLeftCorner, code.location.topLeftCorner, "#FF3B58");
                            outputMessage.hidden = true;
                            outputData.parentElement.hidden = false;
                            outputData.innerText = code.data;


                            if (qrcode != code.data) {

                                qrcode = code.data;
                                var res = code.data.split(' ');

                                window.CRM.APIRequest({
                                    method: 'POST',
                                    path: 'attendees/qrcodeCall',
                                    data: JSON.stringify({"groupID": res[0], "personID": res[1]})
                                },function (data) {
                                    if (data.status == 'failed') {
                                        alert(i18next.t('Failed') + " : " + i18next.t("No event right now.") + "\n\n" + "• "
                                            + i18next.t("Move one in the right range.")
                                            + "\n\n" + i18next.t ("Or") + "\n\n" + "• "
                                            +  i18next.t("Create one.") + "\n\n" + i18next.t('Group')
                                            + ' : ' + data.group + "\n" + i18next.t("User") + ' : ' + data.person);
                                    } else if (data.status == 'global_failed') {
                                        alert(i18next.t("Failed") + " : " + i18next.t("No event now.") );
                                    } else {
                                        alert(i18next.t('Success') + "\n\n" + i18next.t('Group') + ' : ' + data.group + "\n" + i18next.t("User") + ' : ' + data.person);
                                        window.CRM.dataT.ajax.reload(null, false);
                                    }
                                });
                            }
                        } else {
                            outputMessage.hidden = false;
                            outputData.parentElement.hidden = true;
                        }
                    }
                    requestAnimationFrame(tick);
                }
            },
            onHide: function (e){
                window.stream.getVideoTracks()[0].stop();
            },
            buttons: [
                {
                    label: '<i class="fas fa-check"></i> ' + i18next.t("Close"),
                    className: "btn btn-primary",
                    callback: function () {

                    }
                }
            ]
        });

        modal.show();
    });


    setInterval(function(){
            window.CRM.dataT.ajax.reload(null, false);
        }, 8000
    );

    $(document).on("click", "#addVisitor", function () {
        window.location = window.CRM.root + '/v2/people/person/editor';
    });
});
