/*******************************************************************************
 *
 *  filename    : FamilyVerify.js
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2022 Philippe Logel
 *
 ******************************************************************************/

$(function() {
    window.CRM.APIRequest = (options, callback) => {
        if (!options.method) {
            options.method = "GET"
        }

        fetch(window.CRM.root + "/ident/" + options.path, {            
            method: options.method,
            headers: {
                'Content-Type': "application/json; charset=utf-8",                
            },
            body: options.data
        })
            .then(res => res.json())
            .then(data => {
                // enter you logic when the fetch is successful
                if (callback) {
                    callback(data);
                }
            })
            .catch(error => {
                // enter your logic for when there is an error (ex. error toast)
                console.log(error)
            });
    }

    $('#onlineVerifySiteBtn').hide();
    $("#confirm-modal-done").hide();
    $("#confirm-modal-error").hide();

    $("#onlineVerifyBtn").on('click', function () {

        window.CRM.APIRequest({
            method: 'POST',
            path: 'my-profile/onlineVerificationFinished/',
            data: JSON.stringify({
                "token": window.CRM.token,
                "message" : $("#confirm-info-data").val()
            })
        }, function (data) {
            $('#confirm-modal-collect').hide();
            $("#onlineVerifyCancelBtn").hide();
            $("#onlineVerifyBtn").hide();
            $("#onlineVerifySiteBtn").show();
            if (data.Status == "success") {
                $("#confirm-modal-done").show();
            } else {
                $("#confirm-modal-error").show();
            }
        });
    });

    const BootboxContent = (data, custom) => {
        var frm_str = '<form id="some-form">';

        frm_str += data
            + '<br>'
            + custom
            + '</form>';

        var object = $('<div/>').html(frm_str).contents();

        return object;
    }

    const PersonWindow = (data, personId) => {
        var modal = bootbox.dialog({
            message: BootboxContent(data),
            buttons: [
                {
                    label: '<i class="fas fa-times"></i> ' + i18next.t("Close"),
                    className: "btn btn-default",
                    callback: function () {
                    }
                },
                {
                    label: '<i class="fas fa-check"></i> ' + i18next.t("Save"),
                    className: "btn btn-primary",
                    callback: function () {
                        var FirstName = $('form #FirstName').val();
                        var MiddleName = $('form #MiddleName').val();
                        var LastName = $('form #LastName').val();
                        var FamilyRole = $('form #FamilyRole').val();
                        var homePhone = $('form #homePhone').val();
                        var workPhone = $('form #workPhone').val();
                        var cellPhone = $('form #cellPhone').val();
                        var email = $('form #email').val();
                        var workemail = $('form #workemail').val();
                        var BirthDayDate = $('form #BirthDayDate').val();

                        var fmt = window.CRM.datePickerformat.toUpperCase();;

                        var real_dateTime = moment(BirthDayDate,fmt).format('YYYY-MM-DD');

                        fetch(window.CRM.root + '/ident/my-profile/modifyPersonInfo/', {
                            method: 'POST',
                            headers: {
                                'Content-Type': "application/json; charset=utf-8",
                            },
                            body: JSON.stringify({
                                "token": window.CRM.token,
                                "personId": personId,
                                "FirstName": FirstName,
                                "MiddleName": MiddleName,
                                "LastName": LastName,
                                "FamilyRole": FamilyRole,
                                "homePhone": homePhone,
                                "workPhone": workPhone,
                                "cellPhone": cellPhone,
                                "email": email,
                                "workemail": workemail,
                                "BirthDayDate": real_dateTime,
                                "type": "family"
                            })
                        })
                        .then(res => res.json())
                        .then(data => {
                            $(".person-container-" + personId).html(data.content);
                        });
                    }
                }
            ],
            show: false,
            onEscape: function () {
            }
        });

        // this will ensure that image and table can be focused
        $(document).on('focusin', function (e) {
            e.stopImmediatePropagation();
        });

        return modal;
    }

    $(document).on("click", ".modifyPerson", function () {
        var personId = $(this).data("id");

        window.CRM.APIRequest({
            method: 'POST',
            path: 'my-profile/getPersonInfo/',
            data: JSON.stringify({
                "token": window.CRM.token,
                "personId": personId
            })
        }, function (data) {
            var modal = PersonWindow(data.html, personId);
            modal.modal("show");

            $('.date-picker').datepicker({format: window.CRM.datePickerformat, language: window.CRM.lang});
        });
    });

    $(document).on("click", ".deletePerson", function () {
        var personId = $(this).data("id");

        bootbox.confirm(i18next.t("Confirm Delete"), function(confirmed) {
            if (confirmed) {
                window.CRM.APIRequest({
                    method: 'POST',
                    path: 'my-profile/deletePerson/',
                    data: JSON.stringify({
                        "token": window.CRM.token,
                        "personId": personId
                    })
                }, function (data) {
                    $(".person-container-" + personId).html('');
                });
            }
        });
    });

    const FamilyWindow = (data, custom, fields, familyId) => {
        var _fields = fields;

        var modal = bootbox.dialog({
            message: BootboxContent(data, custom),
            size: "large",
            buttons: [
                {
                    label: '<i class="fas fa-times"></i> ' + i18next.t("Close"),
                    className: "btn btn-default",
                    callback: function () {
                    }
                },
                {
                    label: '<i class="fas fa-check"></i> ' + i18next.t("Save"),
                    className: "btn btn-primary",
                    callback: function () {
                        var fields = _fields;

                        var FamilyName = $('form #FamilyName').val();
                        var Address1 = $('form #Address1').val();
                        var Address2 = $('form #Address2').val();
                        var City = $('form #City').val();
                        var Zip = $('form #Zip').val();
                        var State = $('form #State').val();
                        var Country = $('form #Country').val();
                        var homePhone = $('form #homePhone').val();
                        var workPhone = $('form #workPhone').val();
                        var cellPhone = $('form #cellPhone').val();
                        var email = $('form #email').val();
                        var WeddingDate = $('form #WeddingDate').val();
                        var SendNewsLetter = $('form #SendNewsLetter').is(':checked');

                        var res_fields = new Object();
                        for (let i=0;i<fields.length;i++) {
                            let elt = $( "form ." + fields[i] );
                            let t = elt.attr('type');
                            if (t == 'radio') {
                                val = $('input[name="'+ fields[i] + '"]:checked').val();                                    
                            } else {
                                val = $( "." + fields[i] ).val();
                            }
                            res_fields[fields[i]] = val;                                            
                        }

                        var fmt = window.CRM.datePickerformat.toUpperCase();;

                        var real_dateTime = moment(WeddingDate,fmt).format('YYYY-MM-DD');

                        fetch(window.CRM.root + '/ident/my-profile/modifyFamilyInfo/', {
                            method: 'POST',
                            headers: {
                                'Content-Type': "application/json; charset=utf-8",
                            },
                            body: JSON.stringify({
                                "token": window.CRM.token,
                                "familyId": familyId,
                                "FamilyName": FamilyName,
                                "Address1": Address1,
                                "Address2": Address2,
                                "City": City,
                                "Zip": Zip,
                                "Country": Country,
                                "State": State,
                                "homePhone": homePhone,
                                "workPhone": workPhone,
                                "cellPhone": cellPhone,
                                "email": email,
                                "WeddingDate": real_dateTime,
                                "SendNewsLetter": SendNewsLetter,
                                "familyFields": res_fields,
                            })
                        })
                        .then(res => res.json())
                        .then(data => {
                            $("#FamName").html(data.FamName);
                            $("#family-info").html(data.content);
                            $("#family-custom-info").html(data.customContent);

                            let iconurl = window.CRM.root+"/skin/icons/event.png";
                            
                            let icon = L.icon({
                                iconUrl: iconurl,
                                iconSize:     [32, 32], // size of the icon
                                iconAnchor:   [16, 32], // point of the icon which will correspond to marker's location
                                popupAnchor:  [0, -32] // point from which the popup should open relative to the iconAnchor
                            });

                            let Address = '';
                            contentString = "<p>" + Address + "</p>";

                            let centerCard = {
                                lat: Number(data.position.lat),
                                lng: Number(data.position.lng)
                            };

                            //Add marker and infowindow
                            addMarkerWithInfowindow(window.CRM.map, centerCard, icon, '', contentString);
                        });
                    }
                }
            ],
            show: false,
            onEscape: function () {
            }
        });

        // this will ensure that image and table can be focused
        $(document).on('focusin', function (e) {
            e.stopImmediatePropagation();
        });

        return modal;
    }

    $(document).on("click", ".modifyFamily", function () {
        var familyId = $(this).data("id");

        window.CRM.APIRequest({
            method: 'POST',
            path: 'my-profile/getFamilyInfo/',
            data: JSON.stringify({
                "token": window.CRM.token,
                "familyId": familyId
            })
        }, function (data) {

            var modal = FamilyWindow(data.html, data.custom, data.fields, familyId);
            modal.modal("show");

            $('.date-picker').datepicker({format: window.CRM.datePickerformat, language: window.CRM.lang});
        });
    });

    $(document).on("click", ".deleteFamily", function () {
        var familyId = $(this).data("id");

        bootbox.confirm(i18next.t("Confirm Delete"), function(confirmed) {
            if (confirmed) {
                window.CRM.APIRequest({
                    method: 'POST',
                    path: 'my-profile/deleteFamily/',
                    data: JSON.stringify({
                        "token": window.CRM.token,
                        "familyId": familyId
                    })
                }, function (data) {
                    location.reload();
                });
            }
        });
    });

    $(document).on("click", ".exitSession", function () {
        window.CRM.APIRequest({
            method: 'POST',
            path: 'my-profile/exitSession/',
            data: JSON.stringify({
                "token": window.CRM.token
            })
        }, function (data) {
            window.location = window.location.href;
        });
    });
});
