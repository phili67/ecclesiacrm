var title = "";

window.CRM.kiosk = {
    normalizeSearchText: function (value) {
        return (value || "")
            .toString()
            .toLowerCase()
            .normalize("NFD")
            .replace(/[\u0300-\u036f]/g, "")
            .trim();
    },

    updateMemberSearchSummary: function () {
        var totalCount = $("#classMemberContainer [id^='personId-']").length;
        var visibleCount = $("#classMemberContainer [id^='personId-']:visible").length;

        $("#memberSearchCount").text(visibleCount + " / " + totalCount);
    },

    applyMemberSearchFilter: function () {
        var query = window.CRM.kiosk.normalizeSearchText($("#memberSearchInput").val());

        $("#classMemberContainer [id^='personId-']").each(function () {
            var memberCard = $(this);
            var isMatch = query === "" || memberCard.attr("data-search").indexOf(query) !== -1;

            memberCard.closest(".row").toggle(isMatch);
        });

        window.CRM.kiosk.updateMemberSearchSummary();
    },

    ensureMemberSearchTools: function () {
        if ($("#memberSearchTools").length > 0) {
            return;
        }

        var tools = $("<div>", {
            id: "memberSearchTools",
            class: "mb-3 pt-2 sticky-top",
            style: "top: 0.75rem; z-index: 1030;"
        })
            .append(
                $("<div>", {
                    class: "card shadow border-0 mb-0 mx-auto bg-light",
                    style: "width: 100%; max-width: 500px; box-shadow: 0 0.95rem 2rem rgba(0, 0, 0, 0.18);"
                })
                    .append($("<div>", {class: "card-body py-2 px-3"})
                        .append($("<div>", {class: "d-flex align-items-center justify-content-between mb-2"})
                            .append(
                                $("<div>")
                                    .append($("<div>", {class: "small text-uppercase text-secondary font-weight-bold"}).text(i18next.t("Find member")))
                                    .append($("<div>", {class: "small text-muted"}).text(i18next.t("Search by name, role, or ID.")))
                            )
                            .append($("<span>", {id: "memberSearchCount", class: "badge badge-secondary"}).text("0 / 0"))
                        )
                        .append($("<div>", {
                            class: "input-group input-group-sm mx-auto",
                            style: "max-width: 100%;"
                        })
                            .append($("<div>", {class: "input-group-prepend"})
                                .append($("<span>", {class: "input-group-text bg-light text-secondary border-right-0"})
                                    .append($("<i>", {class: "fas fa-search", 'aria-hidden': "true"}))
                                )
                            )
                            .append($("<input>", {
                                id: "memberSearchInput",
                                class: "form-control",
                                type: "search",
                                placeholder: i18next.t("Search a member"),
                                autocomplete: "off",
                                style: "min-width: 0; flex: 1 1 auto;"
                            }))
                            .append($("<div>", {class: "input-group-append"})
                                .append($("<button>", {
                                    id: "memberSearchClear",
                                    class: "btn btn-outline-secondary",
                                    type: "button",
                                    text: i18next.t("Clear")
                                }))
                            )
                        )
                    )
            );

        $("#classMemberContainer").before(tools);

        $("#memberSearchInput").on("input", function () {
            window.CRM.kiosk.applyMemberSearchFilter();
        });

        $("#memberSearchClear").on("click", function () {
            $("#memberSearchInput").val("").trigger("input").focus();
        });

        window.CRM.kiosk.updateMemberSearchSummary();
    },

    APIRequest: function (options, callback) {
        if (!options.method) {
            options.method = "GET"
        }

        fetch(window.CRM.root + "/kiosk/" + options.path, {            
            method: options.method,
            dataType: 'json',
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
    },

    renderClassMember: function (classMember) {
        window.CRM.kiosk.ensureMemberSearchTools();

        existingDiv = $("#personId-" + classMember.personId);
        if (existingDiv.length > 0) {

        } else {
            var globaldiv = $("<div>").addClass("row");
            var outerDiv = $("<div>", {id: "personId-" + classMember.personId})
                .addClass("col-12 mb-3")
                .attr("data-search", window.CRM.kiosk.normalizeSearchText(classMember.displayName + " " + classMember.classRole + " " + classMember.personId));
            var innerDiv = $("<div>").addClass("card card-widget widget-user-2 shadow-sm border-0 mb-0");
            var userHeaderDiv = $("<div>", {class: "widget-user-header bg-primary"}).attr("data-personid", classMember.personId);
            var imageDiv = $("<div>", {class: "widget-user-image"})
                .append($("<img>", {
                        class: "initials-image profile-user-img img-responsive img-circle no-border",
                        src: window.CRM.root + "/kiosk/activeClassMember/" + classMember.personId + "/photo",
                        name: classMember.displayName
                    })
                );
            var userTextDiv = $("<div>", {class: "ml-5 pl-4 pr-2"});
            userHeaderDiv.append(imageDiv);
            userTextDiv
                .append($("<h3>", {
                    class: "widget-user-username-kiosk mb-1",
                    text: classMember.displayName
                }))
                .append(
                    $("<div>", {class: "d-flex flex-wrap align-items-center justify-content-between mt-2"})
                        .append(
                            $("<div>", {class: "small mb-0 mr-2"})
                                .append($("<i>", {class: "fas fa-user-tag mr-1", 'aria-hidden': "true"}))
                                .append(document.createTextNode(i18next.t(classMember.classRole)))
                        )
                        .append($("<span>", {class: "badge badge-light text-secondary border"}).text("#" + classMember.personId))
                );
                        userHeaderDiv.append(userTextDiv);
            innerDiv.append(userHeaderDiv);
            innerDiv
                .append(
                    $("<div>", {class: "card-body py-3 px-3"})
                        .append($("<div>", {class: "row align-items-center"})
                            .append($("<div>", {class: "col-6 col-md-3 mb-3 mb-md-0"})
                                .append($("<div>", {class: "small text-uppercase text-secondary font-weight-bold mb-1"}).text(i18next.t("In")))
                                .append($("<div>", {class: "custom-control custom-switch"})
                                    .append($("<input>", {
                                        type: "checkbox",
                                        class: "custom-control-input checkinButton",
                                        id: "checkin-" + classMember.personId,
                                        "data-personid": classMember.personId,
                                        checked: (classMember.checkedIn == "1")
                                    }))
                                    .append($("<label>", {
                                        class: "custom-control-label font-weight-bold small",
                                        for: "checkin-" + classMember.personId
                                    })
                                        .append($("<i>", {class: "fas fa-sign-in-alt text-info mr-1", 'aria-hidden': "true"}))
                                        .append(document.createTextNode(i18next.t("Checkin")))
                                    )
                                )
                            )
                            .append($("<div>", {class: "col-6 col-md-3 mb-3 mb-md-0"})
                                .append($("<div>", {class: "small text-uppercase text-secondary font-weight-bold mb-1"}).text(i18next.t("Out")))
                                .append($("<div>", {class: "custom-control custom-switch"})
                                    .append($("<input>", {
                                        type: "checkbox",
                                        class: "custom-control-input checkoutButton",
                                        id: "checkout-" + classMember.personId,
                                        "data-personid": classMember.personId,
                                        checked: (classMember.checkedOut == "1")
                                    }))
                                    .append($("<label>", {
                                        class: "custom-control-label font-weight-bold small",
                                        for: "checkout-" + classMember.personId
                                    })
                                        .append($("<i>", {class: "fas fa-sign-out-alt text-secondary mr-1", 'aria-hidden': "true"}))
                                        .append(document.createTextNode(i18next.t("Checkout")))
                                    )
                                )
                            )
                            .append($("<div>", {class: "col-12 col-md-6"})
                                .append($("<div>", {class: "small text-uppercase text-warning font-weight-bold mb-2"}).text(i18next.t("Notify")))
                                .append($("<button>", {
                                    class: "btn btn-outline-warning btn-sm btn-block parentAlertButton",
                                        text: " " + i18next.t("Trigger Parent Alert"),
                                        "data-personid": classMember.personId,
                                        type: "button"
                                    })
                                        .prepend($("<i>", {class: "fas fa-envelope mr-1", 'aria-hidden': "true"}))
                                )
                            )
                        )
                );
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
        window.CRM.kiosk.applyMemberSearchFilter();
    },

    updateActiveClassMembers: function () {
        window.CRM.kiosk.APIRequest({
            path: "activeClassMembers"
        },function (data) {
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
        });
    },

    heartbeat: function () {
        window.CRM.kiosk.APIRequest({
            path: "heartbeat"
        }, function (data) {
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

                    $("#eventKiosk").html(
                        `<div class="bg-light shadow-sm rounded border px-3 py-2 h-100 d-flex flex-column justify-content-between">
                            <div class="small text-uppercase font-weight-bold mb-1 text-secondary">
                                ${i18next.t("Kiosk")}
                            </div>
                            <div class="d-flex flex-wrap align-items-center justify-content-between">
                                <div class="font-weight-bold mb-2 mb-sm-0 mr-sm-3">
                                    <i class="fas fa-desktop text-info mr-2" aria-hidden="true"></i>
                                    ${data.Name}
                                </div>
                                <span class="badge badge-light text-secondary border px-3 py-2">
                                    <i class="fas fa-broadcast-tower text-info mr-1" aria-hidden="true"></i>
                                        ${i18next.t("Attendance")}
                                </span>
                            </div>
                        </div>`
                    );
                    $("#eventTitle").html(
                        `<div class="bg-light shadow-sm rounded border px-3 py-2 h-100">
                            <div class="small text-uppercase font-weight-bold mb-1 text-secondary">
                                ${i18next.t("Current event")}
                            </div>
                            <div class="h4 mb-0 font-weight-bold">
                                <i class="fas fa-calendar-check text-primary mr-2" aria-hidden="true"></i>
                                    ${Assignment.Event.Title}
                            </div>
                        </div>`
                    );
                    $("#startTime").html(
                        `<div class="bg-light shadow-sm rounded border px-3 py-2 h-100">
                            <span class="small text-uppercase font-weight-bold d-block mb-1 text-secondary">
                                ${i18next.t("In")}
                            </span>
                            <span class="font-weight-bold d-block">
                                <i class="fas fa-play-circle text-primary mr-2" aria-hidden="true"></i>
                                ${moment(Assignment.Event.Start.date).format('MMMM Do YYYY, h:mm:ss a')}
                            </span>
                        </div>`
                    );
                    /* TO DO : date: "2020-06-04 22:00:00.000000"
                      timezone: "Europe/Paris"
                      timezone_type: 3*/

                    $("#endTime").html(
                        `<div class="bg-light shadow-sm rounded border px-3 py-2 h-100">
                            <span class="small text-uppercase font-weight-bold d-block mb-1 text-secondary">
                                ${i18next.t("Out")}
                            </span>
                            <span class="font-weight-bold d-block">
                                <i class="fas fa-flag-checkered text-success mr-2" aria-hidden="true"></i>
                                ${moment(Assignment.Event.End.date).format('MMMM Do YYYY, h:mm:ss a')}
                            </span>
                        </div>`
                    );

                    if (Assignment.Event.Title != title) {
                        $("#classMemberContainer").html("");
                    }

                    title = Assignment.Event.Title;
                } else {
                    $("#noEvent").show();
                    $("#noEvent").html(i18next.t('No assignments for kiosk') + ' : ' + data.Name);
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
            },function (data) {
                window.CRM.kiosk.setCheckedIn(personId);
            });
        } else {
            $("#checkout-" + personId).prop("checked", false);
            window.CRM.kiosk.APIRequest({
                path: "uncheckin",
                method: "POST",
                data: JSON.stringify({"PersonId": personId})
            },function (data) {
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
            },function (data) {
                window.CRM.kiosk.setCheckedOut(personId);
            });
        } else {
            window.CRM.kiosk.APIRequest({
                path: "uncheckout",
                method: "POST",
                data: JSON.stringify({"PersonId": personId})
            },function (data) {
                window.CRM.kiosk.setCheckedOut(personId);
            });
        }
    },

    setNotCheckInOut: function (personId) {
        $personDiv = $("#personId-" + personId)
        $personDivButton = $("#personId-" + personId + " .checkoutButton")
        $personDiv.find(".widget-user-header").addClass("bg-light");
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
        $personDiv.find(".widget-user-header").removeClass("bg-light");
    },

    setCheckedIn: function (personId) {
        $personDiv = $("#personId-" + personId)

        $personDivButton = $("#personId-" + personId + " .checkinButton")
        /*$personDivButton.removeClass("checkinButton");
        $personDivButton.addClass("checkoutButton");
        $personDivButton.text(i18next.t("Checkout"));*/

        $personDiv.find(".widget-user-header").addClass("bg-primary");
        $personDiv.find(".widget-user-header").removeClass("bg-green");
        $personDiv.find(".widget-user-header").removeClass("bg-light");
    },

    triggerNotification: function (personId) {
        //window.CRM.kiosk.stopEventLoop();
        window.CRM.kiosk.APIRequest({
            path: "triggerNotification",
            method: "POST",
            data: JSON.stringify({"PersonId": personId})
        },function (data) {
            if (data.status) {
                alert('Message Sent');
            } else {
                alert('error');
            }
            //window.CRM.kiosk.startEventLoop();                        
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
