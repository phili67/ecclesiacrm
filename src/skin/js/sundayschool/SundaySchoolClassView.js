$("document").ready(function () {

    $("#deleteClassButton").click(function () {
        console.log("click");
        bootbox.setDefaults({
            locale: window.CRM.shortLocale
        }),
            bootbox.confirm({
                title: i18next.t("Confirm Delete Group"),
                message: '<p style="color: red">' +
                    i18next.t("Please confirm deletion of this group record") + " " + sundayGroupName + "</p>" +
                    "<p>" +
                    i18next.t("This will also delete all Roles and Group-Specific Property data associated with this Group record.") +
                    "</p><p>" +
                    i18next.t("All group membership and properties will be destroyed.  The group members themselves will not be altered.") + "</p>",
                callback: function (result) {
                    if (result) {
                        window.CRM.APIRequest({
                            method: "DELETE",
                            path: "groups/" + sundayGroupId,
                        },function (data) {
                            if (data.status == "success")
                                window.location.href = window.CRM.root + "/v2/sundayschool/dashboard";
                        });
                    }
                }
            });
    });

    function edition_mode() {
        $(".edition-mode").fadeIn(300);
        $('.quick-status').hide();
        $('.teachers').hide();
        $('.info').hide();

        var oSettings = dataTable.page.len(10).draw();
        window.scrollTo(0, 0);
    }

    function load_SundaySchool() {
        window.CRM.APIRequest({
            method: "GET",
            path: "groups/" + sundayGroupId + "/sundayschool"
        },function (data) {
            var len_teachers = data.teachers.length;
            var res = '<div class="row">';

            var cnt = 0;

            for (i = 0; i < len_teachers; i++) {
                if (cnt % 4 == 0 && cnt != 0) {
                    res += '</div><div class="row">';
                }
                res += '<div class="col-md-3">' +
                    '        <!-- Begin user profile -->' +
                    '        <div class="card text-center user-profile-2">' +
                    '            <div class="card-header border-0">' +
                    '               <h4 class="card-title-sundayschool-view" style="float:center !important">' + data.teachers[i]['per_FirstName'] + ' ' + data.teachers[i]['per_LastName'] + '</h4>' +
                    '            </div>' +
                    '            <div class="card-body">';

                res += '               <img src="' + window.CRM.root + '/api/persons/' + data.teachers[i]['per_ID'] + '/thumbnail"' +
                    '        alt="User Image" class="user-image initials-image" width="85" height="85" />';

                if (data.teachersProps[i][data.teachers[i]['per_ID']] != false) {
                    res += '   <p>' + data.teachersProps[i][data.teachers[i]['per_ID']] + '</p>';
                } else {
                    res += '<p></p>';
                }
                res += '            <a href="mailto:' + data.teachers[i]['per_Email'] + '" class="btn btn-success btn-sm btn-block"><i' +
                    '      class="far fa-envelope"></i> ' + i18next.t('Send Message') + '</a>' +
                    '        <a href="' + window.CRM.root + '/PersonView.php?PersonID=' + data.teachers[i]['per_ID'] + '" ' +
                    '      class="btn btn-primary btn-info btn-sm btn-block"><i class="fas fa-user"></i> ' + i18next.t('View Profile') + '</a>' +
                    '        <a href="#" data-id="' + data.teachers[i]['per_ID'] + '" data-person_name="' + data.teachers[i]['per_FirstName'] + ' ' + data.teachers[i]['per_LastName'] + '" ' +
                    '      class="btn btn-primary btn-danger btn-sm btn-block deleteTeacher"><i class="fas fa-trash-alt"></i> ' + i18next.t('Delete') + '</a>' +
                    '    </div>' +
                    ' </div>' +
                    '</div>';

                cnt += 1;
            }

            res += '</div>';

            $('.teachers_container').html(res);

            $('#sEmailLink').attr('href', 'mailto:' + data.emailLink);
            $('#sEmailLinkBCC').attr('href', 'mailto:?bcc=' + data.emailLink);

            $('#dropDownMail').html(data.dropDown.allNormal);
            $('#dropDownMailBCC').html(data.dropDown.allNormalBCC);

            if (data.kids.length > 0) {
                $('#callRegister').removeClass("disabled");
                $('#exportCheckOutCSV').removeClass("disabled");
                $('#exportCheckOutPDF').removeClass("disabled");
                $('#AddStudentsToGroupCart').removeClass("disabled");
            } else {
                $('#callRegister').addClass("disabled");
                $('#exportCheckOutCSV').addClass("disabled");
                $('#exportCheckOutPDF').addClass("disabled");
                $('#AddStudentsToGroupCart').addClass("disabled");
            }

            if (data.teachers.length > 0) {
                $('#AddToTeacherGroupCart').removeClass("disabled");
            } else {
                $('#AddToTeacherGroupCart').addClass("disabled");
            }
        });
    }

    function exit_edition_mode() {
        window.scrollTo(0, 0);
        $('.edition-mode').hide();

        $(".info").fadeIn(300);
        $('.quick-status').fadeIn(200);
        $('.teachers').fadeIn(200);
        ;
        var oSettings = dataTable.page.len(100).draw();

        var _val = $("#editionMode").is(':checked') ? 'checked' : 'unchecked';

        if (_val == 'checked') {
            $("#editionMode").bootstrapToggle('off');
        }
        ;
    }

    function updateGraphs() {
        load_SundaySchool();

        window.CRM.APIRequest({
            method: "POST",
            path: "sundayschool/getAllGendersForDonut/" + sundayGroupId
        },function (donutData) {
            drawDonut(donutData);
        });

        window.CRM.APIRequest({
            method: "POST",
            path: "sundayschool/getAllStudentsForChart/" + sundayGroupId
        },function (birthDayMonthChart) {
            draw_Chart(birthDayMonthChart);
        });
    }

    $("#editionMode").on('change', function () {
        var _val = $(this).is(':checked') ? 'checked' : 'unchecked';

        if (_val == 'checked') {
            edition_mode()
        } else {
            exit_edition_mode();
        }
    });

    $(document).on("click", ".exit-edition-mode", function () {
        exit_edition_mode();// this for futur dev, we've to update the charts
        updateGraphs();
    });

    /* Badge creation */
    $(document).on("click", "#studentbadge", function () {
        var sundayGroupId = $(this).data("groupid");
        window.CRM.APIRequest({
            method: "GET",
            path: "cart/"
        },function (data) {
            if (data.PeopleCart.length > 0) {
                location.href = window.CRM.root + '/v2/group/' + sundayGroupId + '/badge/1/sundayschool';
            } else {
                location.href = window.CRM.root + '/v2/group/' + sundayGroupId + '/badge/0/sundayschool';
            }
        });
    });

    /* the search fields */
    $("#personSearch").select2({
        minimumInputLength: 2,
        language: window.CRM.shortLocale,
        minimumInputLength: 2,
        placeholder: " -- " + i18next.t("Person") + " -- ",
        allowClear: true, // This is for clear get the clear button if wanted
        ajax: {
            url: function (params) {
                return window.CRM.root + "/api/persons/search/" + params.term;
            },
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    q: params.term, // search term
                    page: params.page
                };
            },
            processResults: function (rdata, page) {
                return {results: rdata};
            },
            cache: true
        }
    });

    $("#personSearch").on("select2:select", function (e) {
        //edition_mode ();
        window.CRM.APIRequest({
            method: "POST",
            path: 'groups/' + sundayGroupId + '/addperson/' + e.params.data.objid,
            data: JSON.stringify({"RoleID": 2})// only a student
        },function (data) {
            dataTable.ajax.reload();/* we reload the data no need to add the person inside the dataTable */
            updateGraphs();
        });
    });

    $("#personSearchTeachers").select2({
        minimumInputLength: 2,
        language: window.CRM.shortLocale,
        minimumInputLength: 2,
        placeholder: " -- " + i18next.t("Teachers") + " -- ",
        allowClear: true, // This is for clear get the clear button if wanted
        ajax: {
            url: function (params) {
                return window.CRM.root + "/api/persons/search/" + params.term;
            },
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    q: params.term, // search term
                    page: params.page
                };
            },
            processResults: function (rdata, page) {
                return {results: rdata};
            },
            cache: true
        }
    });

    $("#personSearchTeachers").on("select2:select", function (e) {
        //edition_mode ();
        window.CRM.APIRequest({
            method: "POST",
            path: 'groups/' + sundayGroupId + '/addteacher/' + e.params.data.objid,
            data: JSON.stringify({"RoleID": 1})// only a teacher
        },function (data) {
            load_SundaySchool(); // we reload the profiles
        });
    });


    /* the membership deletion */
    $('body').on('click', '.deleteTeacher', function () {
        var personId = $(this).data("id");
        var person_name = $(this).data("person_name");

        bootbox.confirm({
            title: i18next.t("Delete this Teacher?"),
            message: i18next.t("Do you want to delete this teacher? This cannot be undone.") + " <b>" + person_name + '</b>',
            buttons: {
                cancel: {
                    className: 'btn-primary',
                    label: '<i class="fas fa-times"></i>' + i18next.t("Cancel")
                },
                confirm: {
                    className: 'btn-danger',
                    label: '<i class="far fa-trash-alt"></i>' + i18next.t("Delete")
                }
            },
            callback: function (result) {
                if (result) {
                    window.CRM.APIRequest({
                        method: "DELETE",
                        path: 'groups/' + sundayGroupId + '/removeperson/' + personId,
                    },function (data) {
                        load_SundaySchool(); // we reload the profiles
                    });
                }
            }
        });
    });


    $('body').on('click', '.delete-person', function () {
        //edition_mode ();
        event.preventDefault();
        var thisLink = $(this);

        bootbox.confirm({
            title: i18next.t("Delete this person?"),
            message: i18next.t("Do you want to delete this person? This cannot be undone.") + " <b>" + thisLink.data('person_name') + '</b>',
            buttons: {
                cancel: {
                    className: 'btn-primary',
                    label: '<i class="fas fa-times"></i>' + i18next.t("Cancel")
                },
                confirm: {
                    className: 'btn-danger',
                    label: '<i class="far fa-trash-alt"></i>' + i18next.t("Delete")
                }
            },
            callback: function (result) {
                if (result) {
                    window.CRM.APIRequest({
                        method: "DELETE",
                        path: 'groups/' + sundayGroupId + '/removeperson/' + thisLink.data('person_id'),
                    },function (data) {
                        dataTable.ajax.reload();/* we reload the data no need to add the person inside the dataTable */
                        updateGraphs();
                    });
                }
            }
        });

    });


    // search for the dates
    $.fn.dataTable.moment = function (format, locale) {
        format = format.replace(/-/g, " ");
        format = format.replace(/\//g, " ");
        format = format.replace("MM", "MMMM");

        var types = $.fn.dataTable.ext.type;

        // Add type detection
        types.detect.unshift(function (d) {
            // Removed true as the last parameter of the following moment
            return moment(d, format, locale).isValid() ?
                'moment-' + format :
                null;
        });

        // Add sorting method - use an integer for the sorting
        types.order['moment-' + format + '-pre'] = function (d) {
            console.log("d");
            return moment(d, format, locale, true).unix();
        };
    };

    $.fn.dataTable.moment(window.CRM.datePickerformat.toUpperCase(), window.CRM.shortLocale);

    // the sundayschool table
    var momentDateFormat = window.CRM.datePickerformat.toUpperCase();
    momentDateFormat = momentDateFormat.replace("MM", "MMMM");
    momentDateFormat = momentDateFormat.replace(/-/g, " ");
    momentDateFormat = momentDateFormat.replace(/\//g, " ");

    // so the dates are now localized in the dataTable
    moment.locale(window.CRM.shortLocale)

    var dataTableConfig = {
        ajax: {
            url: window.CRM.root + "/api/sundayschool/getallstudents/" + sundayGroupId,
            type: 'POST',
            contentType: "application/json",
            dataSrc: "ClassroomStudents"
        },
        pageLength: 100,
        //order: [[ 0, "asc" ]],
        columns: [
            {
                width: 'auto',
                title: i18next.t('First name'),
                data: 'kidId',
                render: function (data, type, full, meta) {
                    return '<table>'
                        + '<tr class="no-background-theme">'
                        + '  <td style="min-width:55px">'
                        + '    <img src="' + window.CRM.root + '/api/persons/' + data + '/thumbnail"'
                        + '        alt="User Image" class="user-image initials-image" width="50" height="50" />'
                        + '  </td>'
                        + '  <td align="left">'
                        + '    <a href="' + window.CRM.root + '/PersonView.php?PersonID=' + data + '">' + full.firstName + '</a>'
                        + '  </dt>'
                        + '</tr>'
                        + '</table>';
                }
            },
            {
                width: 'auto',
                title: i18next.t('Name'),
                data: 'famID',
                render: function (data, type, full, meta) {
                    return '<a href="' + window.CRM.root + '/FamilyView.php?FamilyID=' + data + '">' + full.LastName + '</a>';
                }
            },
            {
                width: 'auto',
                title: i18next.t('Action'),
                data: 'kidId',
                render: function (data, type, full, meta) {
                    var res = '<table  style="width:100px;background-color: transparent !important;"><tr style="background-color: transparent !important;"><td>';

                    if (full.inCart == 0) {
                        res += '<a ' + (window.CRM.showCart ? 'class="AddOneStudentToCart"' : '') + ' data-cartpersonid="' + data + '">'
                            + '<span class="fa-stack">'
                            + '  <i class="fas fa-square fa-stack-2x"></i>'
                            + '  <i class="fas fa-stack-1x fa-inverse ' + (window.CRM.showCart ? 'fa-cart-plus' : 'fa-question') + '"></i>'
                            + '</span>'
                            + '</a>';
                    } else {
                        res += '<a ' + (window.CRM.showCart ? 'class="RemoveOneStudentFromCart"' : '') + ' data-cartpersonid="' + data + '">'
                            + '<span class="fa-stack">'
                            + '  <i class="fas fa-square fa-stack-2x"></i>'
                            + '  <i class="fas fa-stack-1x fa-inverse ' + (window.CRM.showCart ? 'fa-times' : 'fa-question') + '"></i>'
                            + '</span>'
                        '</a>';
                    }

                    if (canDeleteMembers) {
                        res += '<a class="delete-person" data-person_name="' + full.firstName + ' ' + full.LastName + '" data-person_id="' + data + '" data-view="family">'
                            + '  <span class="fa-stack" style="color:red">'
                            + '    <i class="fas fa-square fa-stack-2x"></i>'
                            + '    <i class="far fa-trash-alt fa-stack-1x fa-inverse"></i>'
                            + '  </span>'
                            + '</a>';
                    }

                    res += "</td></tr></table>";

                    return res;
                }
            },
            {
                width: 'auto',
                title: i18next.t('Birth Date'),
                data: 'kidId',
                render: function (data, type, full, meta) {
                    if (!canSeePrivacyData) {
                        return i18next.t('Private Data');
                    }

                    var birthDate = full.birthYear + '-' + ((full.birthMonth < 10) ? '0' : '') + full.birthMonth + '-' + ((full.birthDay < 10) ? '0' : '') + full.birthDay + 'T00:00:00';
                    var outPut = moment(birthDate).format(momentDateFormat);

                    return i18next.t(outPut);
                }
            },
            {
                width: 'auto',
                title: i18next.t('Gender'),
                data: 'kidId',
                render: function (data, type, full, meta) {
                    var gender = i18next.t("Unknown");

                    switch (full.kidGender) {
                        case "1":
                            gender = i18next.t("Boy");
                            break;
                        case "2":
                            gender = i18next.t("Girl");
                            break;
                    }

                    return gender;
                }
            },
            {
                width: 'auto',
                title: i18next.t('Age'),
                data: 'flags',
                render: function (data, type, full, meta) {
                    if (!canSeePrivacyData) {
                        return i18next.t('Private Data');
                    }

                    if (data == "0" || full.birthDay == '' || full.birthDay == '0') {// we are on the case of a show age
                        var realBirthDate = full.birthYear + '-' + ((full.birthMonth < 10) ? '0' : '') + full.birthMonth + '-' + ((full.birthDay < 10) ? '0' : '') + full.birthDay + 'T00:00:00';
                        var birthDate = moment(realBirthDate);
                        var now = moment();
                        var ageDisplay = now.diff(birthDate, 'years');
                        if (ageDisplay < 1) {
                            ageDisplay = now.diff(birthDate, 'months') + " m";
                        }
                        if (ageDisplay) {
                            return ageDisplay;
                        }
                    }
                    return i18next.t("Private Data");
                }
            },
            {
                width: 'auto',
                title: i18next.t('Email'),
                data: 'kidEmail',
                render: function (data, type, full, meta) {
                    if (!canSeePrivacyData) {
                        return i18next.t('Private Data');
                    }
                    return '<a href="mailto:' + data + '">' + data + '</a>';
                }
            },
            {
                width: 'auto',
                title: i18next.t('Mobile'),
                data: 'mobilePhone',
                render: function (data, type, full, meta) {
                    if (!canSeePrivacyData) {
                        return i18next.t('Private Data');
                    }
                    return '<a href="tel:' + data + '">' + data + '</a>';
                }
            },
            {
                width: 'auto',
                title: i18next.t('Home Phone'),
                data: 'homePhone',
                render: function (data, type, full, meta) {
                    if (!canSeePrivacyData) {
                        return i18next.t('Private Data');
                    }
                    return '<a href="tel:' + data + '">' + data + '</a>';
                }
            },
            {
                width: 'auto',
                title: i18next.t('Home Address'),
                data: 'kidId',
                render: function (data, type, full, meta) {
                    if (!canSeePrivacyData) {
                        return i18next.t('Private Data');
                    }
                    var address = full.Address1 + ' ' + full.Address2 + ' ' + full.city + ' ' + full.state + ' ' + full.zip;

                    return window.CRM.tools.getLinkMapFromAddress(address);
                }
            },
            {
                width: 'auto',
                title: i18next.t('Dad Name'),
                data: 'kidId',
                render: function (data, type, full, meta) {
                    if (!canSeePrivacyData) {
                        return i18next.t('Private Data');
                    }

                    if (full.dadFirstName != null && full.dadLastName != null) {
                        return '<a href="' + window.CRM.root + '/PersonView.php?PersonID=' + full.dadId + '">' + full.dadFirstName + ' ' + full.dadLastName + '</a>';
                    }

                    return "";
                }
            },
            {
                width: 'auto',
                title: i18next.t('Dad Mobile'),
                data: 'dadCellPhone',
                render: function (data, type, full, meta) {
                    if (!canSeePrivacyData) {
                        return i18next.t('Private Data');
                    }

                    if (data != null) {
                        return '<a href="tel:' + data + '">' + data + '</a>';
                    }

                    return '';
                }
            },
            {
                width: 'auto',
                title: i18next.t('Dad Email'),
                data: 'dadEmail',
                render: function (data, type, full, meta) {
                    if (!canSeePrivacyData) {
                        return i18next.t('Private Data');
                    }

                    if (data != null) {
                        return '<a href="mailto:' + data + '">' + data + '</a>';
                    }

                    return '';
                }
            },
            {
                width: 'auto',
                title: i18next.t('Mom Name'),
                data: 'kidId',
                render: function (data, type, full, meta) {
                    if (!canSeePrivacyData) {
                        return i18next.t('Private Data');
                    }

                    if (full.momFirstName != null && full.momLastName != null) {
                        return '<a href="' + window.CRM.root + '/PersonView.php?PersonID=' + full.momId + '">' + full.momFirstName + ' ' + full.momLastName + '</a>';
                    }

                    return '';
                }
            },
            {
                width: 'auto',
                title: i18next.t('Mom Mobile'),
                data: 'momCellPhone',
                render: function (data, type, full, meta) {
                    if (!canSeePrivacyData) {
                        return i18next.t('Private Data');
                    }

                    if (data != null) {
                        return '<a href="tel:' + data + '">' + data + '</a>';
                    }

                    return '';
                }
            },
            {
                width: 'auto',
                title: i18next.t('Mom Email'),
                data: 'momEmail',
                render: function (data, type, full, meta) {
                    if (!canSeePrivacyData) {
                        return i18next.t('Private Data');
                    }

                    if (data != null) {
                        return '<a href="mailto:' + data + '">' + data + '</a>';
                    }

                    return '';
                }
            }
        ],
        responsive: true,
        createdRow: function (row, data, index) {
            $(row).addClass("menuLinksRow");
        }
    }

    $.extend(dataTableConfig, window.CRM.plugin.dataTable);

    var dataTable = $("#sundayschoolTable").DataTable(dataTableConfig);

    // the chart donut code
    var hideBirthDayFilter = function () {
        window.CRM.plot.unhighlight();

        var birthDateColumn = dataTable.column(':contains(' + birthDateColumnText + ')');

        birthDateColumn
            .search('')
            .draw();

        birthDayFilter.hide();
    };


    var birthDayFilter = $('.birthday-filter');
    var birthDayMonth = birthDayFilter.find('.month');
    birthDayFilter.find('i.fa-close')
        .bind('click', hideBirthDayFilter);

    $("#bar-chart").bind("plotclick", function (event, pos, item) {
        window.CRM.plot.unhighlight();

        if (!item) {
            hideBirthDayFilter();
            return;
        }

        var month = window.CRM.bar_data.data[item.dataIndex][0];

        var birthDateColumn = dataTable.column(':contains(' + birthDateColumnText + ')');

        birthDateColumn
            .search(month.substr(0, 7))
            .draw();

        birthDayMonth.text(month);
        birthDayFilter.show();

        window.CRM.plot.highlight(item.series, item.datapoint);
    });


    /*
     * BAR CHART
     * ---------
     */
    function draw_Chart(birthDayMonthChart) {
        window.CRM.bar_data = {
            data: birthDayMonthChart,
            color: "#3c8dbc"
        };

        var ticks = [];

        for (i = 0;i < 12;i++) {
            ticks[i] = [i+12,window.CRM.bar_data.data[i][0]];
        }

        window.CRM.plot = $.plot("#bar-chart", [window.CRM.bar_data], {
            grid: {
                borderWidth: 1,
                borderColor: "#f3f3f3",
                tickColor: "#f3f3f3",
                hoverable: true,
                clickable: true
            },
            series: {
                bars: {
                    show: true,
                    barWidth: 0.5,
                    align: "center"
                }
            },
            xaxis: {
                mode: "categories",
                tickLength: 0,
                ticks: ticks
            },
            yaxis: {
                tickSize: 1
            }
        });
    }

    draw_Chart(birthDayMonthChartJSON);

    /* END BAR CHART */

    /*
     * DONUT CHART
     * -----------
     */

    var hideGenderFilter = function () {
        window.CRM.plot.unhighlight();

        var genderColumn = dataTable.column(':contains(' + genderColumnText + ')');

        genderColumn
            .search('')
            .draw();

        genderFilter.hide();
    };

    function drawDonut(donutData) {
        $.plot("#donut-chart", donutData, {
            series: {
                pie: {
                    show: true,
                    radius: 1,
                    innerRadius: 0.5,
                    label: {
                        show: true,
                        radius: 2 / 3,
                        formatter: labelFormatter,
                        threshold: 0.1
                    }

                }
            },
            grid: {
                hoverable: true,
                clickable: true
            },
            legend: {
                show: false
            }
        });
    }


    var genderFilter = $('.gender-filter');
    var genderType = genderFilter.find('.type');
    ;

    genderFilter.find('i.fa-close')
        .bind('click', hideGenderFilter);


    var placeholder = $("#donut-chart");

    // now we draw the donut
    drawDonut(genderChartJSON);

    placeholder.bind("plotclick", function (event, pos, obj) {

        if (!obj) {
            return;
        }

        percent = parseFloat(obj.series.percent).toFixed(2);

        //alert(""  + obj.series.label + ": cpicpi" + percent + "%");

        var gender = obj.series.label;

        var searchGender = gender.substr(0, gender.length - 1);

        var genderColumn = dataTable.column(':contains(' + genderColumnText + ')');

        genderColumn
            .search(searchGender)
            .draw();

        genderType.text(gender);
        genderFilter.show();
    });

    /*
     * END DONUT CHART
     */

    /*
     * Custom Label formatter
     * ----------------------
     */
    function labelFormatter(label, series) {
        return "<div style='font-size:13px; text-align:center; padding:2px; color: #fff; font-weight: 600;'>"
            + label
            + "<br/>"
            + Math.round(series.percent) + "%</div>";
    }

    /* the Cart management */
    // the little buttons
    $(document).on("click", ".AddOneStudentToCart", function () {
        clickedButton = $(this);
        window.CRM.cart.addPerson([clickedButton.data("cartpersonid")], function () {
            $(clickedButton).addClass("RemoveOneStudentFromCart");
            $(clickedButton).removeClass("AddOneStudentToCart");
            $('span i:nth-child(2)', clickedButton).addClass("fa-times");
            $('span i:nth-child(2)', clickedButton).removeClass("fa-cart-plus");
        });

        var studentsButton = $('#AddStudentsToGroupCart');
        $(studentsButton).addClass("RemoveStudentsFromGroupCart");
        $(studentsButton).removeClass("AddStudentsToGroupCart");
        $('i', studentsButton).addClass("fa-times");
        $('i', studentsButton).removeClass("fa-cart-plus");
        text = $(studentsButton).find("span.cartActionDescription")
        if (text) {
            $(text).text(i18next.t("Remove Students from Cart"));
        }
    });

    $(document).on("click", ".RemoveOneStudentFromCart", function () {
        var clickedButton = $(this);
        window.CRM.cart.removePerson([clickedButton.data("cartpersonid")], function () {
            $(clickedButton).addClass("AddOneStudentToCart");
            $(clickedButton).removeClass("RemoveOneStudentFromCart");
            $('span i:nth-child(2)', clickedButton).removeClass("fa-times");
            $('span i:nth-child(2)', clickedButton).addClass("fa-cart-plus");
        });
    });

    $(document).on("click", ".AddStudentsToGroupCart", function () {
        var clickedButton = $(this);
        window.CRM.cart.addStudentGroup(clickedButton.data("cartstudentgroupid"), function () {
            $(clickedButton).addClass("RemoveStudentsFromGroupCart");
            $(clickedButton).removeClass("AddStudentsToGroupCart");
            $('i', clickedButton).addClass("fa-times");
            $('i', clickedButton).removeClass("fa-cart-plus");
            text = $(clickedButton).find("span.cartActionDescription")
            if (text) {
                $(text).text(i18next.t("Remove Students from Cart"));
            }

            // everything is done in the listener
            /*$('.AddOneStudentToCart').addClass("RemoveOneStudentFromCart");
            $('.RemoveOneStudentFromCart').removeClass("AddOneStudentToCart");
            $(".fa-inverse").removeClass("fa-cart-plus");
            $(".fa-inverse").addClass("fa-times");*/
        });
    });

    $(document).on("click", ".RemoveStudentsFromGroupCart", function () {
        clickedButton = $(this);
        window.CRM.cart.removeStudentGroup(clickedButton.data("cartstudentgroupid"), function () {
            $(clickedButton).addClass("AddStudentsToGroupCart");
            $(clickedButton).removeClass("RemoveStudentsFromGroupCart");
            $('i', clickedButton).removeClass("fa-times");
            $('i', clickedButton).addClass("fa-cart-plus");
            text = $(clickedButton).find("span.cartActionDescription")
            if (text) {
                $(text).text(i18next.t("Add Students to Cart"));
            }

            // everything is done in the listener
            /*$('.RemoveOneStudentFromCart').addClass("AddOneStudentToCart");
            $('.AddOneStudentToCart').removeClass("RemoveOneStudentFromCart");
            $(".fa-inverse").removeClass("fa-times");
            $(".fa-inverse").addClass("fa-cart-plus");*/
        });
    });

    $(document).on("click", ".AddToTeacherGroupCart", function () {
        clickedButton = $(this);
        window.CRM.cart.addTeacherGroup(clickedButton.data("cartteachergroupid"), function () {
            $(clickedButton).addClass("RemoveFromTeacherGroupCart");
            $(clickedButton).removeClass("AddToTeacherGroupCart");
            $('i', clickedButton).addClass("fa-times");
            $('i', clickedButton).removeClass("fa-cart-plus");
            text = $(clickedButton).find("span.cartActionDescription")
            if (text) {
                $(text).text(i18next.t("Remove Teachers from Cart"));
            }
        });
    });

    $(document).on("click", ".RemoveFromTeacherGroupCart", function () {
        clickedButton = $(this);
        window.CRM.cart.removeTeacherGroup(clickedButton.data("cartteachergroupid"), function () {
            $(clickedButton).addClass("AddToTeacherGroupCart");
            $(clickedButton).removeClass("RemoveFromTeacherGroupCart");
            $('i', clickedButton).removeClass("fa-times");
            $('i', clickedButton).addClass("fa-cart-plus");
            text = $(clickedButton).find("span.cartActionDescription")
            if (text) {
                $(text).text(i18next.t("Add Teachers to Cart"));
            }
        });
    });

    // newMessage event subscribers  : Listener CRJSOM.js
    $(document).on("updateCartMessage", updateButtons);

    // newMessage event handler
    function updateButtons(e) {
        var cartPeople = e.people;

        if (cartPeople != null) {
            personButtons = $("a[data-cartpersonid]");
            $(personButtons).each(function (index, personButton) {
                personID = $(personButton).data("cartpersonid");
                if (cartPeople.includes(personID) || cartPeople.includes(personID.toString())) {
                    personPresent = true;
                    $(personButton).addClass("RemoveOneStudentFromCart");
                    $(personButton).removeClass("AddOneStudentToCart");
                    fa = $(personButton).find("i.fas.fa-inverse");
                    $(fa).addClass("fa-times");
                    $(fa).removeClass("fa-cart-plus");
                    text = $(personButton).find("span.cartActionDescription")
                    if (text) {
                        $(text).text(i18next.t("Remove from Cart"));
                    }
                } else {
                    $(personButton).addClass("AddOneStudentToCart");
                    $(personButton).removeClass("RemoveOneStudentFromCart");
                    fa = $(personButton).find("i.fas.fa-inverse");

                    $(fa).removeClass("fa-times");
                    $(fa).addClass("fa-cart-plus");
                    text = $(personButton).find("span.cartActionDescription")
                    if (text) {
                        $(text).text(i18next.t("Add to Cart"));
                    }
                }
            });
        }

        if (cartPeople.length == 0) {
            $("#AddToTeacherGroupCart").addClass("AddToTeacherGroupCart");
            $("#AddToTeacherGroupCart").removeClass("RemoveFromTeacherGroupCart");
            $('i', "#AddToTeacherGroupCart").removeClass("fa-times");
            $('i', "#AddToTeacherGroupCart").addClass("fa-cart-plus");
            text = $("#AddToTeacherGroupCart").find("span.cartActionDescription")
            if (text) {
                $(text).text(i18next.t("Add Teachers to Cart"));
            }

            $("#AddStudentsToGroupCart").addClass("AddStudentsToGroupCart");
            $("#AddStudentsToGroupCart").removeClass("RemoveStudentsFromGroupCart");
            $('i', "#AddStudentsToGroupCart").removeClass("fa-times");
            $('i', "#AddStudentsToGroupCart").addClass("fa-cart-plus");
            text = $("#AddStudentsToGroupCart").find("span.cartActionDescription")
            if (text) {
                $(text).text(i18next.t("Add Students to Cart"));
            }

            var clickedButton = $('.RemoveOneStudentFromCart');

            $(clickedButton).addClass("AddOneStudentToCart");
            $(clickedButton).removeClass("RemoveOneStudentFromCart");
            $('span i:nth-child(2)', clickedButton).removeClass("fa-times");
            $('span i:nth-child(2)', clickedButton).addClass("fa-cart-plus");
        }
    }

    // end of cart management


    // checkout the student
    $(document).on("click", ".callRegister", function () {
        var groupName = $(this).data("callregistergroupname")
        var groupID = $(this).data("callregistergroupid");

        window.CRM.APIRequest({
            method: 'GET',
            path: 'events/types',
        },function (typeNames) {
            var lenType = typeNames.length;
            var options = new Array();

            var boxOptions = {
                title: i18next.t("Select the event Type you would like to use to create the Attendance") + " : " + groupName,
                message: '<div class="modal-body">',
                size: "large",
                buttons: {
                    addEvent: {
                        label: i18next.t("Create First A New Event"),
                        className: 'btn-info',
                        callback: function () {
                            location.href = window.CRM.root + '/v2/calendar';
                        }
                    },
                    cancel: {
                        label: i18next.t('Cancel'),
                        className: 'btn-danger'
                    },
                    confirm: {
                        label: i18next.t('Create Event With Students'),
                        className: 'btn btn-primary',
                        callback: function () {
                            var e = document.getElementById("chosenType");
                            var eventTypeID = e.options[e.selectedIndex].value;

                            window.CRM.APIRequest({
                                method: 'POST',
                                path: 'attendees/student',
                                data: JSON.stringify({
                                    "eventTypeID": eventTypeID,
                                    "groupID": groupID,
                                    "rangeInHours": 2
                                })
                            },function (data) {
                                location.href = window.CRM.root + '/Checkin.php';
                            });
                        }
                    }
                }
            };

            boxOptions.message += '<center>' +
                '<p style="color:red">'+
                i18next.t('Be careful! You are about to create or recreate an event of this Sunday school class to call the register.')+
                "<br/>" +
                i18next.t('If the event are already created, go to the "Events" menu and then "Call the register".')+
                '</p>' +
                '<br/>' +
                i18next.t("You can create the event automatically with the students<br> " +
                "- OR - <br>Add the students to the cart and create an event to add them after.") + '</center><br>';
            boxOptions.message += '<select class="bootbox-input bootbox-input-select form-control" id="chosenType">';
            for (i = 0; i < lenType; i++) {
                boxOptions.message += '<option value="' + typeNames[i].eventTypeID + '">' + typeNames[i].name + '</option>';
            }

            boxOptions.message += '</select>\
                             </div>';

            bootbox.dialog(boxOptions).show();
        })
    });

    function BootboxContentCSV(start, end) {
        var time_format;
        var fmt = window.CRM.datePickerformat.toUpperCase();

        var dateStart = moment(start).format(fmt);
        var dateEnd = moment(end).format(fmt);


        var frm_str = '<b><p>' + i18next.t("First, set your time range correctly to make the extraction.") + '</p></b>'
            + '<hr/>'
            + '<div class="row">'
            + '    <div class="col-md-12">'
            + '        <div class="row">'
            + '            <div class="col-md-3"><span style="color: red">*</span>'
            + i18next.t('Start Date') + ' :'
            + '            </div>'
            + '            <div class="input-group col-md-3">'
            + '                 <div class="input-group-prepend">'
            + '                      <span class="input-group-text"><i class="fas fa-calendar"></i></span>'
            + '                 </div>'
            + '                 <input class="form-control date-picker form-control-sm" type="text" id="dateEventStart" name="dateEventStart"  value="' + dateStart + '" '
            + '                        maxlength="10" id="sel1" size="11"'
            + '                        placeholder="' + window.CRM.datePickerformat + '">'
            + '            </div>'
            + '            <div class="col-md-3"><span style="color: red">*</span>'
            + i18next.t('End Date') + ' :'
            + '            </div>'
            + '            <div class="input-group col-md-3">'
            + '                 <div class="input-group-prepend">'
            + '                      <span class="input-group-text"><i class="fas fa-calendar"></i></span>'
            + '                 </div>'
            + '                 <input class="form-control date-picker form-control-sm" type="text" id="dateEventEnd" name="dateEventEnd"  value="' + dateEnd + '" '
            + '                        maxlength="10" id="sel1" size="11"'
            + '                        placeholder="' + window.CRM.datePickerformat + '">'
            + '            </div>'
            + '        </div>'
            + '    </div>'
            + '</div>';

        var object = $('<div/>').html(frm_str).contents();

        return object
    }


    $(document).on("click", ".exportCheckOutCSV", function () {
        var groupID = sundayGroupId;

        var start = moment().subtract(1, 'years').format('YYYY-MM-DD');
        var end = moment().format('YYYY-MM-DD');

        var modal = bootbox.dialog({
            title: i18next.t("Set year range to export"),
            message: BootboxContentCSV(start, end),
            size: "large",
            buttons: [
                {
                    label: i18next.t("Cancel"),
                    className: "btn btn-default",
                    callback: function () {
                        console.log("just do something on close");
                    }
                },
                {
                    label: i18next.t('OK'),
                    className: "btn btn-primary",
                    callback: function () {
                        var dateStart = $('#dateEventStart').val();
                        var dateEnd = $('#dateEventEnd').val();

                        var fmt = window.CRM.datePickerformat.toUpperCase();

                        var real_start = moment(dateStart, fmt).format('YYYY-MM-DD');
                        var real_end = moment(dateEnd, fmt).format('YYYY-MM-DD');

                        window.location = window.CRM.root + "/sundayschool/SundaySchoolAttendeesExport.php?groupID=" + groupID + "&start=" + real_start + "&end=" + real_end;
                    }
                }
            ],
            show: false/*,
         onEscape: function() {
            modal.modal("hide");
         }*/
        });

        modal.modal("show");

        $('.date-picker').datepicker({format: window.CRM.datePickerformat, language: window.CRM.lang});
    });

    function BootboxContentPDF(start, end) {
        var fmt = window.CRM.datePickerformat.toUpperCase();

        var dateStart = moment(start).format(fmt);
        var dateEnd = moment(end).format(fmt);


        var frm_str = '<b><p>' + i18next.t("First, set your date range correctly to make the extraction.") + '</p></b>'
            + '<hr/>'
            + '<div class="row">'
            + '    <div class="col-md-12">'
            + '        <div class="row">'
            + '            <div class="col-md-3"><span style="color:red">*</span>'
            + i18next.t('Start Date') + ' :'
            + '            </div>'
            + '            <div class="input-group col-md-3">'
            + '                 <div class="input-group-prepend">'
            + '                      <span class="input-group-text"><i class="fas fa-calendar"></i></span>'
            + '                 </div>'
            + '                    <input class="form-control date-picker form-control-sm" type="text" id="dateEventStart" name="dateEventStart"  value="' + dateStart + '" '
            + '                        maxlength="10" id="sel1" size="11"'
            + '                        placeholder="' + window.CRM.datePickerformat + '">'
            + '            </div>'
            + '            <div class="col-md-"><span style="color: red">*</span>'
            + i18next.t('End Date') + ' :'
            + '            </div>'
            + '            <div class="input-group col-md-3">'
            + '                 <div class="input-group-prepend">'
            + '                      <span class="input-group-text"><i class="fas fa-calendar"></i></span>'
            + '                 </div>'
            + '                    <input class="form-control date-picker form-control-sm" type="text" id="dateEventEnd" name="dateEventEnd"  value="' + dateEnd + '" '
            + '                        maxlength="10" id="sel1" size="11"'
            + '                        placeholder="' + window.CRM.datePickerformat + '">'
            + '            </div>'
            + '        </div>'
            + '    </div>'
            + '</div>'
            + '<br>'
            + '<div class="row">'
            + '    <div class="col-md-4"><span style="color: red">*</span>'
            + i18next.t('Extra students') + ' :'
            + '    </div>'
            + '    <div class="col-md-3">'
            + '        <input class="form-control form-control-sm" type="text" id="ExtraStudents" name="ExtraStudents"  value="0" maxlength="10" id="sel1" size="11">'
            + '    </div>'
            + '    <div class="col-md-6">'
            + '        <input id="withPictures" type="checkbox" checked> ' + i18next.t('export with photos')
            + '    </div>'
            + '</div>';

        var object = $('<div/>').html(frm_str).contents();

        return object
    }

    $(document).on("click", ".exportCheckOutPDF", function () {
        var groupID = sundayGroupId;

        var start = moment().subtract(1, 'years').format('YYYY-MM-DD');
        var end = moment().format('YYYY-MM-DD');

        var modal = bootbox.dialog({
            title: i18next.t("Set year range to export"),
            message: BootboxContentPDF(start, end),
            size: "large",
            buttons: [
                {
                    label: i18next.t("Cancel"),
                    className: "btn btn-default",
                    callback: function () {
                        console.log("just do something on close");
                    }
                },
                {
                    label: i18next.t('OK'),
                    className: "btn btn-primary",
                    callback: function () {
                        var dateStart = $('#dateEventStart').val();
                        var dateEnd = $('#dateEventEnd').val();

                        var fmt = window.CRM.datePickerformat.toUpperCase();

                        var real_start = moment(dateStart, fmt).format('YYYY-MM-DD');
                        var real_end = moment(dateEnd, fmt).format('YYYY-MM-DD');

                        var withPictures = ($("#withPictures").is(':checked') == true) ? 1 : 0;
                        var ExtraStudents = $("#ExtraStudents").val();

                        window.location = window.CRM.root + "/Reports/ClassRealAttendance.php?groupID=" + groupID + "&start=" + real_start + "&end=" + real_end + "&withPictures=" + withPictures + "&ExtraStudents=" + ExtraStudents + "&exportTypePDF=1";
                    }
                }
            ],
            show: false/*,
         onEscape: function() {
            modal.modal("hide");
         }*/
        });

        modal.modal("show");

        $('.date-picker').datepicker({format: window.CRM.datePickerformat, language: window.CRM.lang});
    });


    // listener : when the delete member is invocated
    $(document).on("updateLocalePageMessage", updateLocaleSCPage);

    // newMessage event handler
    function updateLocaleSCPage(e) {
        dataTable.ajax.reload();/* we reload the data no need to add the person inside the dataTable */
        updateGraphs();
    }

    load_SundaySchool();
});
