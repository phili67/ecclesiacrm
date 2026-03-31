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
                    label: i18next.t("Tracking", {ns: 'FinanceDashboard'}),
                    data: [],
                    borderColor: '#ffffff',
                    backgroundColor: 'rgba(255, 255, 255, 0.1)',
                    borderWidth: 4,
                    borderCapStyle: 'round',
                    borderJoinStyle: 'round',
                    fill: true,
                    lineTension: 0.3,
                    pointRadius: 5,
                    pointHoverRadius: 8,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 3,
                    pointHoverBackgroundColor: '#ffffff',
                    pointHoverBorderWidth: 3,
                    pointHoverBorderColor: '#ffffff',
                    spanGaps: true,
                    stepped: false
                }
            ]
        };

        $.each(lineDataRaw.Deposits, function (i, val) {
            lineData.labels.push(moment(val.Date).format(window.CRM.datePickerformat.toUpperCase()));
            lineData.datasets[0].data.push(val.totalAmount);
        });

        // Plugin pour fond transparent et axes blancs
        const transparentBackgroundPlugin = {
            id: 'customCanvasBackgroundColor',
            afterDatasetsDraw(chart) {
                const {ctx, scales, chartArea} = chart;
                ctx.save();
                
                // Redessiner les axes en blanc avec un style plus élégant
                ctx.strokeStyle = '#ffffff';
                ctx.lineWidth = 2;
                ctx.setLineDash([]);
                
                // Axe X
                if (scales.y) {
                    ctx.beginPath();
                    ctx.moveTo(chartArea.left, scales.y.bottom);
                    ctx.lineTo(chartArea.right, scales.y.bottom);
                    ctx.stroke();
                }
                
                // Axe Y
                if (scales.x) {
                    ctx.beginPath();
                    ctx.moveTo(scales.x.left, chartArea.top);
                    ctx.lineTo(scales.x.left, chartArea.bottom);
                    ctx.stroke();
                }
                
                ctx.restore();
            },
            beforeDraw(chart) {
                const {ctx, width, height} = chart;
                ctx.save();
                ctx.globalCompositeOperation = 'destination-over';
                ctx.fillStyle = 'transparent';
                ctx.fillRect(0, 0, width, height);
                ctx.restore();
            }
        };

        var lineChartCanvas = $("#deposit-lineGraph").get(0).getContext("2d");
        var lineChart = new Chart(lineChartCanvas, {
            type: 'line',
            data: lineData,
            plugins: [transparentBackgroundPlugin],
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                animation: {
                    duration: 1500,
                    easing: 'easeInOutQuart'
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 20,
                            fontSize: 13,
                            fontStyle: 'bold',
                            fontFamily: "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif",
                            fontColor: '#ffffff'
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.9)',
                        titleFontSize: 13,
                        bodyFontSize: 12,
                        padding: 12,
                        displayColors: true,
                        borderColor: 'rgba(40, 167, 69, 0.8)',
                        borderWidth: 2,
                        cornerRadius: 6,
                        titleFontColor: '#ffffff',
                        bodyFontColor: '#ffffff',
                        callbacks: {
                            label: function(context) {
                                return ` ${context.dataset.label}: \$${context.yLabel.toFixed(2)}`;
                            }
                        }
                    }
                },
                scales: {
                    yAxes: [{
                        beginAtZero: true,
                        scaleLineColor: '#ffffff',
                        gridLines: {
                            color: 'rgba(255, 255, 255, 0.2)',
                            drawBorder: true,
                            borderColor: '#ffffff',
                            zeroLineColor: '#ffffff'
                        },
                        ticks: {
                            callback: function(value) {
                                return `${window.CRM.currency} ${value.toFixed(0)}`;
                            },
                            fontColor: '#ffffff',
                            fontSize: 11,
                            fontStyle: 'bold',
                            padding: 8
                        }
                    }],
                    xAxes: [{
                        scaleLineColor: '#ffffff',
                        gridLines: {
                            color: 'rgba(255, 255, 255, 0.15)',
                            drawBorder: true,
                            borderColor: '#ffffff',
                            zeroLineColor: '#ffffff'
                        },
                        ticks: {
                            fontColor: '#ffffff',
                            fontSize: 10,
                            fontStyle: '600',
                            padding: 8
                        }
                    }]
                }
            }
        });
    }
});
