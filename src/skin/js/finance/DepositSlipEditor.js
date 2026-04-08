$(function() {
    const updateSelectionActions = () => {
        let selectedRows = dataT.rows('.selected').data().length;

        $("#deleteSelectedRows").prop('disabled', !(selectedRows));
        $("#deleteSelectedRows").html('<i class="fas fa-trash-alt mr-1"></i>' + i18next.t("Delete") + " (" + selectedRows + ") " + i18next.t("Selected Rows"));

        $("#invalidateSelectedRows").prop('disabled', !(selectedRows));
        $("#invalidateSelectedRows").html('<i class="fas fa-undo mr-1"></i>' + i18next.t("Pledge") + " (" + selectedRows + ") " + i18next.t("Selected Rows"));

        $("#validateSelectedRows").prop('disabled', !(selectedRows));
        $("#validateSelectedRows").html('<i class="fas fa-check mr-1"></i>' + i18next.t("Payment") + " (" + selectedRows + ") " + i18next.t("Selected Rows"));
    };

    $('#deleteSelectedRows').on('click', function () {
        let deletedRows = dataT.rows('.selected').data();
        bootbox.confirm({
            title: i18next.t("Confirm Delete"),
            message: "<p>" + i18next.t("Are you sure ? You're about to delete the selected") + deletedRows.length + " " + i18next.t("payments(s)?") + "</p>" +
                "<p>" + i18next.t("This action CANNOT be undone, and may have legal implications!") + "</p>" +
                "<p>" + i18next.t("Please ensure this what you want to do.") + "</p>",
            buttons: {
                cancel: {
                    label: i18next.t("Close")
                },
                confirm: {
                    label: i18next.t("Delete")
                }
            },
            callback: function (result) {
                if (result) {
                    window.CRM.deletesRemaining = deletedRows.length;

                    $.each(deletedRows, function (index, value) {
                        window.CRM.APIRequest({
                            method: 'DELETE',
                            path: 'payments/byGroupKey', // the url where we want to POST
                            data: JSON.stringify({"Groupkey": value.Groupkey})
                        },function (data) {
                            dataT.rows('.selected').remove().draw(false);
                            window.CRM.deletesRemaining--;
                            if (window.CRM.deletesRemaining == 0) {
                                dataT.ajax.reload();
                                load_charts();
                                updateSelectionActions();
                            }
                        });
                    });
                }
            }
        })
    });

    $("#invalidateSelectedRows").on('click',function (e) {
        let rows = dataT.rows('.selected').data();
        let newData = new Array();

        for (let i = 0; i < rows.length; i++) {
            newData.push(rows[i]);
        }

        window.CRM.APIRequest({
            method: 'POST',
            path: 'payments/invalidate',
            data: JSON.stringify({"data": newData})
        },function (data) {
            dataT.ajax.reload();
            updateSelectionActions();
        });
    });

    $("#validateSelectedRows").on('click',function (e) {
        let rows = dataT.rows('.selected').data();
        let newData = new Array();

        for (let i = 0; i < rows.length; i++) {
            newData.push(rows[i]);
        }

        window.CRM.APIRequest({
            method: 'POST',
            path: 'payments/validate',
            data: JSON.stringify({"data": newData})
        },function (data) {
            dataT.ajax.reload();
            updateSelectionActions();
        });
    });

    $(document).on('click', '.detailButton', function () {
        let gk = $(this).data("gk");

        window.CRM.APIRequest({
            method: 'POST',
            path: 'pledges/detail',
            data: JSON.stringify({"groupKey": gk})
        },function (data) {
            let len = data.Pledges.length;
            let fmt = window.CRM.datePickerformat.toUpperCase();
            let date = moment(data.Date).format(fmt);

            let message = "<table class='outer'>";

            message += "<tr><td><label>" + i18next.t("Depid") + " </label> </td><td>&nbsp;:&nbsp;</td><td>" + data.Pledges[0].Depid + "</td></tr>";
            message += "<tr><td><label>" + i18next.t("Name") + " </label> </td><td>&nbsp;:&nbsp;</td><td>" + data.Pledges[0].FamilyName + "</td></tr>";
            message += "<tr><td><label>" + i18next.t("Address1") + " </label> </td><td>&nbsp;:&nbsp;</td><td>" + i18next.t(data.Pledges[0].Address1) + "</td></tr>";
            message += "<tr><td><label>" + i18next.t("Date") + " </label> </td><td>&nbsp;:&nbsp;</td><td>" + date + "</td></tr>";

            let type = "Disabled";
            if (data.Pledges[0].EnableCreditCard) {
                type = "Credit Card";
            } else if (data.Pledges[0].EnableBankDraft) {
                type = "Bank Draft";
            }
            message += "<tr><td><label>" + i18next.t("Type") + " </label> </td><td>&nbsp;:&nbsp;</td><td>" + i18next.t(type) + "</td></tr>";
            let BankName = "";
            if (data.Pledges[0].BankName) {
                BankName = data.Pledges[0].BankName;
            }
            message += "<tr><td><label>" + i18next.t("Bank Name") + " </label> </td><td>&nbsp;:&nbsp;</td><td>" + BankName + "</td></tr>";

            message += "<tr><td><label>" + i18next.t("Non deductible") + " </label> </td><td>&nbsp;:&nbsp;</td><td>" + data.Pledges[0].Nondeductible + "</td></tr>";
            message += "<tr><td><label>" + i18next.t("Statut") + " </label> </td><td>&nbsp;:&nbsp;</td><td>" + i18next.t(data.Pledges[0].Pledgeorpayment) + "</td></tr>";
            message += "<tr><td>&nbsp;</td><td></td><td></td></tr>";


            for (let i = 0; i < len; i++) {
                message += "<tr><td><u><b>" + i18next.t("Deposit") + " " + (i + 1) + "</b></u></td><td></td><td></td></tr>";
                message += "<tr><td><label>" + i18next.t("Schedule") + " </label> </td><td>&nbsp;:&nbsp;</td><td>" + i18next.t(data.Pledges[i].Schedule) + "</td></tr>";


                message += "<tr><td><label>" + i18next.t("Amount") + " </label> </td><td>&nbsp;:&nbsp;</td><td>" + data.Pledges[i].Amount + "</td></tr>";
                message += "<tr><td><label>" + i18next.t("Comment") + " </label> </td><td>&nbsp;:&nbsp;</td><td>" + i18next.t(data.Pledges[i].Comment) + "</td></tr>";
                message += "<tr><td>&nbsp;</td><td></td><td></td></tr>";
            }

            message += "</table>";

            bootbox.alert({
                //size: "small",
                title: i18next.t("Electronic Transaction Details"),
                message: message,
                callback: function () { /* your callback code */
                }
            })
        });
    });

    const load_charts = () => {
        window.CRM.APIRequest({
            method: 'POST',
            path: 'payments/getchartsarrays',
            data: JSON.stringify({"depositSlipID": depositSlipID})
        },function (data) {
            fundData = data.fundData;
            pledgeData = data.pledgeData;
            pledgeDataType = data.pledgeTypeData;

            initCharts(fundData, pledgeData);

            const formatMoney = (amount) => {
                return window.CRM.currency + Number(amount).toLocaleString(window.CRM.lang, {maximumFractionDigits: 2});
            };

            let len = fundData.datasets[0].data.length;

            $("#mainFundTotals").empty();
            let globalTotal = 0;
            for (let i = 0; i < len; ++i) {
                $("#mainFundTotals").append(
                    '<li class="d-flex justify-content-between border-bottom py-1">' +
                    '<span>' + fundData.labels[i] + '</span>' +
                    '<strong>' + formatMoney(fundData.datasets[0].data[i]) + '</strong>' +
                    '</li>'
                );
                globalTotal += Number(fundData.datasets[0].data[i]);
            }

            $("#GlobalTotal").empty();
            $("#GlobalTotal").append(
                '<li class="d-flex justify-content-between border-bottom py-1">' +
                '<span><strong>' + i18next.t("TOTAL") + ' (' + len + ')</strong></span>' +
                '<strong>' + formatMoney(globalTotal) + '</strong>' +
                '</li>'
            );

            if (pledgeDataType[0].value != null) {
                $("#GlobalTotal").append(
                    '<li class="d-flex justify-content-between border-bottom py-1">' +
                    '<span>' + pledgeDataType[0].label + ' (' + pledgeDataType[0].countCash + ')</span>' +
                    '<strong>' + formatMoney(pledgeDataType[0].value) + '</strong>' +
                    '</li>'
                );
            }
            if (pledgeDataType[1].value != null) {
                $("#GlobalTotal").append(
                    '<li class="d-flex justify-content-between border-bottom py-1">' +
                    '<span>' + pledgeDataType[1].label + ' (' + pledgeDataType[1].countChecks + ')</span>' +
                    '<strong>' + formatMoney(pledgeDataType[1].value) + '</strong>' +
                    '</li>'
                );
            }
        });
    }

    const initPaymentTable = (type) => {
        let colDef = [
            {
                width: 'auto',
                title: i18next.t('Family') + ' ' + i18next.t('or') + ' ' + i18next.t('Person'),
                data: 'FamilyString',
                render: function (data, type, full, meta) {
                    let familyName = data ? data : i18next.t('Anonymous');
                    /*var res = ((is_closed == 0)?'<a href=\''+ window.CRM.root + '/v2/deposit/pledge/editor/GroupKey/' + full.Groupkey + '/v2-deposit-slipeditor-' + depositSlipID + '\'><span class="fa-stack"><i class="fas fa-square fa-stack-2x"></i><i class="fas '+  (isDepositClosed ? "fa-search-plus": "fa-pencil-alt" ) +' fa-stack-1x fa-inverse"></i></span></a>':'');
                    res+=familyName;*/

                    var res = '<a href=\''+ window.CRM.root + '/v2/deposit/pledge/editor/GroupKey/' + full.Groupkey + '/v2-deposit-slipeditor-' + depositSlipID +'\'><span class="fa-stack"><i class="fas fa-square fa-stack-2x"></i><i class="fas ' + (isDepositClosed ? "fa-search-plus" : "fa-pencil-alt") + ' fa-stack-1x fa-inverse"></i></span></a>';

                    res += familyName;

                    return res;
                }
            },
            {
                width: 'auto',
                title: i18next.t('Fund'),
                data: 'DonationFundNames',
                render: function (data, type, full, meta) {
                    return data;
                }
            },
            {
                width: 'auto',
                title: i18next.t('Pledge or payment'),
                data: 'Pledgeorpayment',
                render: function (data, type, full, meta) {
                    if (data == null) {
                        return i18next.t("None");
                    } else {
                        return i18next.t(data);
                    }
                }
            },
            {
                width: 'auto',
                title: i18next.t('Amount'),
                data: 'sumAmount',
                render: function (data, type, full, meta) {
                    return Number(data).toLocaleString(window.CRM.lang);
                }
            },
            {
                width: 'auto',
                title: i18next.t('Method'),
                data: 'Method',
                render: function (data, type, full, meta) {
                    return i18next.t(data);
                }
            }
        ];

        if (!(type == 'BankDraft' || type == 'CreditCard')) {
            colDef.push(
                {
                    width: 'auto',
                    title: i18next.t('Check Number'),
                    data: 'Checkno',
                    render: function (data, type, full, meta) {
                        if (data == null || data == "0") {
                            return i18next.t("None");
                        } else {
                            return i18next.t(data);
                        }
                    }
                }
            );
        }

        if (depositType == "CreditCard") {
            colDef.push(
                {
                    width: 'auto',
                    title: i18next.t('Details'),
                    data: 'Id',
                    render: function (data, type, full, meta) {
                        return '<button type="button" data-GK="' + full.Groupkey + '" class="btn btn-info detailButton">' + i18next.t("Details") + '</button>'
                    }
                }
            );
        }

        dataT = $("#paymentsTable").DataTable({
            ajax: {
                url: window.CRM.root + "/api/deposits/" + depositSlipID + "/pledges",
                dataSrc: '',
                "beforeSend": function (xhr) {
                    xhr.setRequestHeader('Authorization',
                        "Bearer " +  window.CRM.jwtToken
                    );
                }
            },
            "language": {
                "url": window.CRM.plugin.dataTable.language.url
            },
            pageLength: 8,
            dom: 'rtip',
            autoWidth: false,
            columns: colDef,
            responsive: true,
            "createdRow": function (row, data, index) {
                $(row).addClass("paymentRow");
            }
        });
        updateSelectionActions();
        dataT.on('xhr', function () {
            var json = dataT.ajax.json();
            //console.log(json);
        });
    }

    const initDepositSlipEditor = () => {
        function format(d) {
            // `d` is the original data object for the row
            return '<table cellpadding="5" cellspacing="0" border="0" style="padding-left:50px;">' +
                '<tr>' +
                '<td>Date:</td>' +
                '<td>' + moment(d.Date).format(window.CRM.datePickerformat.toUpperCase()) + '</td>' +
                '</tr>' +
                '<tr>' +
                '<td>Fiscal Year:</td>' +
                '<td>' + d.Fyid + '</td>' +
                '</tr>' +
                '<tr>' +
                '<td>Fund(s):</td>' +
                '<td>' + d.DonationFundName + '</td>' +
                '</tr>' +
                '<tr>' +
                '<td>Non Deductible:</td>' +
                '<td>' + d.Nondeductible + '</td>' +
                '</tr>' +
                '<tr>' +
                '<td>Comment:</td>' +
                '<td>' + d.Comment + '</td>' +
                '</tr>' +
                '</table>';
        }

        $("#DepositSlipSubmit").on('click',function (e) {
            e.preventDefault();
            
            //process the form
            window.CRM.APIRequest({
                method: 'POST',
                path: 'deposits/' + depositSlipID, // the url where we want to POST
                data: JSON.stringify({
                    'depositDate': moment($('#DepositDate').val(), window.CRM.datePickerformat.toUpperCase()).format('YYYY-MM-DD'),
                    'depositComment': $("#Comment").val(),
                    'depositClosed': $('#Closed').is(':checked'),
                    'depositType': depositType
                }) // our data object
            }, function (data) {
                location.reload();
            });                        
        });

        $('#paymentsTable tbody').on('click', 'td.details-control', function () {
            let tr = $(this).closest('tr');
            let row = dataT.row(tr);
            if (row.child.isShown()) {
                // This row is already open - close it
                row.child.hide();
                tr.removeClass('shown');
                $(this).html('<i class="fas fa-plus-circle"></i>');
            } else {
                // Open this row
                row.child(format(row.data())).show();
                tr.addClass('shown');
                $(this).html('<i class="fas fa-minus-circle"></i>');
            }
        });

        $(document).on('click', ".paymentRow", function (event) {
            if (!($(event.target).hasClass("details-control") || $(event.target).hasClass("fa"))) {
                $(this).toggleClass('selected');
                updateSelectionActions();
            }


        });

    }

    const initCharts = (fundChartData, pledgeChartData) => {
        const palette = [
            '#1f7a8c', '#bfdbf7', '#f4a259', '#5c946e', '#d1495b',
            '#3d5a80', '#e0a458', '#7b2cbf', '#2a9d8f', '#e76f51'
        ];

        const applyDatasetStyle = (chartData) => {
            if (!chartData || !chartData.datasets || !chartData.datasets[0]) {
                return;
            }

            const ds = chartData.datasets[0];
            if (!ds.backgroundColor || ds.backgroundColor.length === 0) {
                ds.backgroundColor = palette;
            }

            ds.borderColor = '#ffffff';
            ds.borderWidth = 2;
            ds.hoverBorderWidth = 2;
        };

        const formatMoney = (amount) => {
            return window.CRM.currency + Number(amount).toLocaleString(window.CRM.lang, {maximumFractionDigits: 2});
        };

        let pieOptions = {
            cutoutPercentage: 66,
            rotation: -1.5707963267948966,
            circumference: 6.283185307179586,
            maintainAspectRatio: false,
            legend: {
                display: false
            },
            tooltips: {
                callbacks: {
                    label: function (tooltipItem, chartData) {
                        let label = chartData.labels[tooltipItem.index] || '';
                        let value = chartData.datasets[tooltipItem.datasetIndex].data[tooltipItem.index] || 0;
                        return label + ': ' + formatMoney(value);
                    }
                }
            },
            animation: {
                animateRotate: true,
                animateScale: true,
                duration: 700
            },
        };

        var len = fundChartData.datasets[0].data.length;
        if (len == 0) return;

        applyDatasetStyle(fundChartData);
        applyDatasetStyle(pledgeChartData);

        if (window.CRM.depositTypeChart) {
            window.CRM.depositTypeChart.destroy();
        }
        if (window.CRM.depositFundChart) {
            window.CRM.depositFundChart.destroy();
        }

        var typeDonutContext = $("#type-donut").get(0).getContext("2d");
        window.CRM.depositTypeChart = new Chart(typeDonutContext, {
            type: 'doughnut',
            data: fundChartData,
            options: pieOptions
        });

        var fundDonutContext = $("#fund-donut").get(0).getContext("2d");
        window.CRM.depositFundChart = new Chart(fundDonutContext, {
            type: 'doughnut',
            data: pledgeChartData,
            options: pieOptions
        });

    }

    initPaymentTable(DepositType);
    initDepositSlipEditor();
    load_charts();
    updateSelectionActions();
});
