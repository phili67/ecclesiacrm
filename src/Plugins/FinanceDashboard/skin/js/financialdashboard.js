$(function() {
    if (window.CRM.depositData && window.CRM.bEnabledFinance) {
        //---------------
        //- LINE CHART  -
        //---------------
        var lineDataRaw = window.CRM.depositData;

        var lineData = {
            labels: [],
            datasets: [
                {
                    data: [],
                    backgroundColor: [],
                    borderColor: []
                }
            ]
        };


        $.each(lineDataRaw.Deposits, function (i, val) {
            lineData.labels.push(moment(val.Date).format(window.CRM.datePickerformat.toUpperCase()));
            lineData.datasets[0].data.push(val.totalAmount);

        });

        lineData.datasets[0].backgroundColor = 'rgba(115, 159, 205, 1)';
        lineData.datasets[0].borderColor = 'rgba(95, 139, 185, 1)';
        lineData.datasets[0].label = i18next.t("Tracking", {ns: 'FinanceDashboard'});

        options = {
            responsive: true,
            maintainAspectRatio: false
        };


        var lineChartCanvas = $("#deposit-lineGraph").get(0).getContext("2d");
        var lineChart = new Chart(lineChartCanvas, {
            type: 'line',
            data: lineData,
            options: {
                scales: {
                    yAxes: [{
                        stacked: true
                    }]
                }
            }
        });
    }
});
