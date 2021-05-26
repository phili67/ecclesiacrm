var title = "";

window.CRM.kiosk = {
    APIRequest: function (options) {
        if (!options.method) {
            options.method = "GET"
        }
        options.url = window.CRM.root + "/kiosk/" + options.path;
        options.dataType = 'json';
        options.contentType = "application/json";
        return $.ajax(options);
    },

    renderClassMember: function (classMember) {
        existingDiv = $("#personId-" + classMember.personId);
        if (existingDiv.length > 0) {

        } else {
            var globaldiv = $("<div>").addClass("row");
            var outerDiv = $("<div>", {id: "personId-" + classMember.personId}).addClass("col-sm-12");
            var innerDiv = $("<div>").addClass("card card-widget widget-user-2");
            var userHeaderDiv = $("<div>", {class: "widget-user-header bg-primary"}).attr("data-personid", classMember.personId);
            var imageDiv = $("<div>", {class: "widget-user-image"})
                .append($("<img>", {
                        class: "initials-image profile-user-img img-responsive img-circle no-border",
                        src: window.CRM.root + "/kiosk/activeClassMember/" + classMember.personId + "/photo",
                        name: classMember.displayName
                    })
                );
            userHeaderDiv.append(imageDiv);
            userHeaderDiv.append($("<h3>", {
                class: "widget-user-username",
                text: classMember.displayName
            })).append($("<h3>", {
                class: "widget-user-desc",
                style: "clear:both",
                text: i18next.t(classMember.classRole)
            }));
            innerDiv.append(userHeaderDiv);
            innerDiv.append($("<div>", {class: "card-footer no-padding"})
                .append($("<div>", {class: "row"})
                    .append($("<div>", {class: "col-md-4"})
                        .append($("<label>")
                            .append($('<input type="checkbox" data-personid="' + classMember.personId + '" class="checkinButton" id="checkin-' + classMember.personId + '" ' + ((classMember.checkedIn == "1") ? 'checked' : '') + '>' +
                                '<span> ' + i18next.t("Checkin") + '</span>'))
                        )
                    )
                    .append($("<div>", {class: "col-md-4"})
                        .append($("<label>")
                            .append($('<input type="checkbox" data-personid="' + classMember.personId + '" class="checkoutButton"  id="checkout-' + classMember.personId + '" ' + ((classMember.checkedOut == "1") ? 'checked' : '') + ' >' +
                                '<span> ' + i18next.t("Checkout") + '</span>'))
                        )
                    )
                    .append($("<div>", {class: "col-md-4"})
                        .append($("<button>", {
                                class: "btn btn-danger parentAlertButton",
                                style: "width:100%",
                                text: " " + i18next.t("Trigger Parent Alert"),
                                "data-personid": classMember.personId
                            })
                                .prepend($("<i>", {class: "fa fa-envelope-o", 'aria-hidden': "true"}))
                        )
                    )
                ));
            outerDiv.append(innerDiv);
            globaldiv.append(outerDiv)
            $("#classMemberContainer").append(globaldiv);
        }


        if (classMember.checkedOut == 1) {
            window.CRM.kiosk.setCheckedOut(classMember.personId);
        } else if (classMember.checkedIn == 1) {
            window.CRM.kiosk.setCheckedIn(classMember.personId);
        } else {
            window.CRM.kiosk.setNotCheckInOut(classMember.personId);
        }

        $("#checkin-" + classMember.personId).prop("checked", (classMember.checkedIn == 1));
        $("#checkout-" + classMember.personId).prop("checked", (classMember.checkedOut == 1));
    },

    updateActiveClassMembers: function () {
        window.CRM.kiosk.APIRequest({
            path: "activeClassMembers"
        })
            .done(function (data) {
                $(data.EventAttends).each(function (i, d) {
                    window.CRM.kiosk.renderClassMember({
                        displayName: d.Person.FirstName + " " + d.Person.LastName,
                        classRole: d.RoleName,
                        personId: d.Person.Id,
                        status: d.status,
                        checkedIn: d.checkedIn,
                        checkedOut: d.checkedOut
                    })
                });
            })
    },

    heartbeat: function () {
        window.CRM.kiosk.APIRequest({
            path: "heartbeat"
        }).done(function (data) {
            thisAssignment = data.Assignment;
            if (window.CRM.kioskAssignmentId === undefined) {
                window.CRM.kioskAssignmentId = thisAssignment;
            } else if (thisAssignment && window.CRM.kioskAssignmentId != null && (thisAssignment.EventId !== window.CRM.kioskAssignmentId.EventId || thisAssignment.Event.GroupId !== window.CRM.kioskAssignmentId.Event.GroupId)) {
                location.reload();
            }

            if (data.Commands === "Reload") {
                location.reload();
            }

            if (data.Commands === "Identify") {
                clearInterval(window.CRM.kioskEventLoop);
                $("#event").hide();
                $("#noEvent").show();
                $("#noEvent").html("Kiosk Name: " + data.Name);
                setTimeout(function () {
                    location.reload()
                }, 2000);
                return;
            }

            if (data.Accepted) {
                Assignment = data.Assignment;
                if (Assignment && Assignment.AssignmentType == 1) {
                    window.CRM.kiosk.updateActiveClassMembers();
                    $("#noEvent").hide();
                    $("#event").show();

                    $("#eventKiosk").text("( " + i18next.t("Kiosk") + " : " + data.Name + ')');
                    $("#eventTitle").text(Assignment.Event.Title);
                    $("#startTime").text(moment(Assignment.Event.Start.date).format('MMMM Do YYYY, h:mm:ss a'));
                    /* TO DO : date: "2020-06-04 22:00:00.000000"
                      timezone: "Europe/Paris"
                      timezone_type: 3*/

                    $("#endTime").text(moment(Assignment.Event.End.date).format('MMMM Do YYYY, h:mm:ss a'));

                    if (Assignment.Event.Title != title) {
                        $("#classMemberContainer").html("");
                    }

                    title = Assignment.Event.Title;
                } else {
                    $("#noEvent").show();
                    $("#noEvent").html('No assignments for kiosk : ' + data.Name);
                    $("#event").hide();
                }
            } else {
                $("#noEvent").show();
                $("#noEvent").html("This kiosk has not been accepted.<br/>Kiosk Name: " + data.Name);
                $("#event").hide();
            }

        })
    },

    checkInPerson: function (personId) {
        var checked = $("#checkin-" + personId).is(':checked');

        if (checked) {
            window.CRM.kiosk.APIRequest({
                path: "checkin",
                method: "POST",
                data: JSON.stringify({"PersonId": personId})
            }).done(function (data) {
                window.CRM.kiosk.setCheckedIn(personId);
            });
        } else {
            $("#checkout-" + personId).prop("checked", false);
            window.CRM.kiosk.APIRequest({
                path: "uncheckin",
                method: "POST",
                data: JSON.stringify({"PersonId": personId})
            }).done(function (data) {
                window.CRM.kiosk.setCheckedIn(personId);
            });
        }
    },

    checkOutPerson: function (personId) {
        var checked = $("#checkout-" + personId).is(':checked');

        if (checked) {
            window.CRM.kiosk.APIRequest({
                path: "checkout",
                method: "POST",
                data: JSON.stringify({"PersonId": personId})
            }).done(function (data) {
                window.CRM.kiosk.setCheckedOut(personId);
            });
        } else {
            window.CRM.kiosk.APIRequest({
                path: "uncheckout",
                method: "POST",
                data: JSON.stringify({"PersonId": personId})
            }).done(function (data) {
                window.CRM.kiosk.setCheckedOut(personId);
            });
        }
    },

    setNotCheckInOut: function (personId) {
        $personDiv = $("#personId-" + personId)
        $personDivButton = $("#personId-" + personId + " .checkoutButton")
        $personDiv.find(".widget-user-header").addClass("bg-gray");
        $personDiv.find(".widget-user-header").removeClass("bg-primary");
        $personDiv.find(".widget-user-header").removeClass("bg-green");
    },


    setCheckedOut: function (personId) {
        $personDiv = $("#personId-" + personId)
        $personDivButton = $("#personId-" + personId + " .checkoutButton")
        /*$personDivButton.addClass("checkinButton");
        $personDivButton.removeClass("checkoutButton");
        $personDivButton.text("Checkin");*/
        $personDiv.find(".widget-user-header").addClass("bg-green");
        $personDiv.find(".widget-user-header").removeClass("bg-primary");
        $personDiv.find(".widget-user-header").removeClass("bg-gray");
    },

    setCheckedIn: function (personId) {
        $personDiv = $("#personId-" + personId)

        $personDivButton = $("#personId-" + personId + " .checkinButton")
        /*$personDivButton.removeClass("checkinButton");
        $personDivButton.addClass("checkoutButton");
        $personDivButton.text(i18next.t("Checkout"));*/

        $personDiv.find(".widget-user-header").addClass("bg-primary");
        $personDiv.find(".widget-user-header").removeClass("bg-green");
        $personDiv.find(".widget-user-header").removeClass("bg-gray");
    },

    triggerNotification: function (personId) {
        //window.CRM.kiosk.stopEventLoop();
        window.CRM.kiosk.APIRequest({
            path: "triggerNotification",
            method: "POST",
            data: JSON.stringify({"PersonId": personId})
        }).done(function (data) {
            //window.CRM.kiosk.startEventLoop();
            //TODO:  Signal to the kiosk user that the notification was sent
        });

    },

    enterFullScreen: function () {
        if (document.documentElement.requestFullscreen) {
            document.documentElement.requestFullscreen();
        } else if (document.documentElement.mozRequestFullScreen) {
            document.documentElement.mozRequestFullScreen();
        } else if (document.documentElement.webkitRequestFullscreen) {
            document.documentElement.webkitRequestFullscreen();
        } else if (document.documentElement.msRequestFullscreen) {
            document.documentElement.msRequestFullscreen();
        }
    },

    exitFullScreen: function () {
        if (document.exitFullscreen) {
            document.exitFullscreen();
        } else if (document.mozCancelFullScreen) {
            document.mozCancelFullScreen();
        } else if (document.webkitExitFullscreen) {
            document.webkitExitFullscreen();
        }
    },

    displayPersonInfo: function (personId) {
        //TODO: Display information (allergies, etc) about the person selected.
    },

    startEventLoop: function () {
        window.CRM.kiosk.kioskEventLoop = setInterval(window.CRM.kiosk.heartbeat, 2000);
    },

    stopEventLoop: function () {
        clearInterval(window.CRM.kiosk.kioskEventLoop);
    }

}
