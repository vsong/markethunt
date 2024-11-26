// chart vars
var UtcTimezone = "T00:00:00+00:00"

// style
var primaryLineColor = "#4f52aa";
var secondaryLineColor = "#b91a05";
var sbiLineColor = "#00c000"
var volumeColor = "#51cda0";
var volumeLabelColor = "#3ab28a";

var eventBandColor = "#f2f2f2";
var eventBandFontColor = "#999999"; // recommend to have same or close color as yGridLineColor for visual clarity
var xTickColor = "#bbbbbb";
var xGridLineColor = "#cccccc";
var yGridLineColor = "#aaaaaa";
var yGridLineColorLighter = "#dddddd";
var axisLabelColor = "#444444";
var crosshairColor = "#222222";

var chartFont = "arial, sans-serif";

function UtcIsoDateToMillis(dateStr) {
    return (new Date(dateStr + UtcTimezone)).getTime();
}

function formatSISuffix(num, decimalPlaces) {
    const suffixes = ["", "K", "M", "B"];
    let order = Math.max(Math.floor(Math.log(num) / Math.log(1000)), 0);
    if (order > suffixes.length - 1) {
        order = suffixes.length - 1;
    }
    let significand = num / Math.pow(1000, order);
    return significand.toFixed(decimalPlaces) + suffixes[order];
}

function eventBand(labelText, IsoStrFrom, IsoStrTo) {
    return {
        from: UtcIsoDateToMillis(IsoStrFrom),
        to: UtcIsoDateToMillis(IsoStrTo),
        color: eventBandColor,
        label: {
            text: labelText,
            rotation: 270,
            textAlign: 'right',
            y: 5, // pixels from top of chart
            x: 4, // fix slight centering issue
            style: {
                color: eventBandFontColor,
                fontWeight: 'bold',
                fontSize: '13px',
                fontFamily: 'Trebuchet MS',
            },
        },
    }
}

function yearLine(year) {
    return {
        value: UtcIsoDateToMillis(`${year}-01-01`),
        color: xGridLineColor,
        width: 1,
        zIndex: 2,
    }
}

var eventBands = [];
eventDates.forEach(event => eventBands.push(eventBand(event.short_name, event.start_date, event.end_date)));

var yearLines = [];
for (let year = 2008; year < 2099; year++) {
    yearLines.push(yearLine(year));
}

function renderChartWithItemId(itemId, chartHeaderText) {
    itemId = Number(itemId);

    // set up header elements
    const chartTitleElem = document.getElementById('chartHeader');
    const externalMhLinkElem = document.getElementById('chart-external-mh-link');
    const priceElem = document.getElementById('chart-header-price');
    const changeElem = document.getElementById('chart-header-change');
    const sbPriceElem = document.getElementById('chart-header-sb-index');
    const tradeVolumeElem = document.getElementById('extra-info-volume-display');
    const goldVolumeElem = document.getElementById('extra-info-gold-volume-display');
    const weekTradeVolumeElem = document.getElementById('extra-info-7d-volume-display');
    const weekGoldVolumeElem = document.getElementById('extra-info-7d-gold-volume-display');
    const loadingElem = document.getElementsByClassName('chart-loading')[0];

    chartTitleElem.innerHTML = chartHeaderText;
    externalMhLinkElem.href = 'https://www.mousehuntgame.com/i.php?id=' + itemId;
    priceElem.innerHTML = "-- g";
    changeElem.innerHTML = "-- (-- %)";
    changeElem.className = '';
    sbPriceElem.innerHTML = "-- SB";
    tradeVolumeElem.innerHTML = "--";
    goldVolumeElem.innerHTML = "--";
    weekTradeVolumeElem.innerHTML = "--";
    weekGoldVolumeElem.innerHTML = "--";
    loadingElem.style.display = "flex";
    
    function renderChart(response) {
        var daily_prices = [];
        var daily_trade_volume = [];
        var sbi = [];
        for (var i = 0; i < response.market_data.length; i++) {
            daily_prices.push([
                UtcIsoDateToMillis(response.market_data[i].date),
                Number(response.market_data[i].price)
            ]);
            daily_trade_volume.push([
                UtcIsoDateToMillis(response.market_data[i].date),
                Number(response.market_data[i].volume)
            ]);
            sbi.push([
                UtcIsoDateToMillis(response.market_data[i].date),
                Number(response.market_data[i].sb_price)
            ]);
        }

        Highcharts.setOptions({
            chart: {
                style: {
                    fontFamily: chartFont,
                },
            },
            lang: {
                rangeSelectorZoom :""
            },
            plotOptions: {
                series: {
                    animation: false,
                    dataGrouping: {
                        enabled: itemId === 114,
                        units: [['day', [1]], ['week', [1]]],
                        groupPixelWidth: 3,
                    },
                    showInLegend: true,
                },
            },
            xAxis: {
                // lineColor: '#555',
                tickColor: xTickColor,
                labels: {
                    style: {
                        color: axisLabelColor,
                        fontSize: '12px',
                    }
                }
            },
            yAxis: {
                gridLineColor: yGridLineColor,
                labels: {
                    style: {
                        color: axisLabelColor,
                        fontSize: '12px',
                    },
                    y: 3,
                }
            }
        });

        // Create the chart
        var chart = new Highcharts.stockChart('chartContainer', {
            chart: {
                animation: false, // disable range selector zooming animation
            },
            // must keep scrollbar enabled for dynamic scrolling, so hide the scrollbar instead
            scrollbar: {
                height: 0,
                buttonArrowColor: "#ffffff00",
            },
            title: {
                enabled: false,
            },
            rangeSelector: {
                buttons: [
                    {
                        type: 'month',
                        count: 1,
                        text: '1M'
                    }, {
                        type: 'month',
                        count: 3,
                        text: '3M'
                    }, {
                        type: 'month',
                        count: 6,
                        text: '6M'
                    }, {
                        type: 'year',
                        count: 1,
                        text: '1Y'
                    }, {
                        type: 'year',
                        count: 2,
                        text: '2Y'
                    }, {
                        type: 'all',
                        text: 'All'
                    },
                ],
                selected: 4,
                inputEnabled: false,
                labelStyle: {
                    color: axisLabelColor,
                },
                buttonPosition: {
                    y: 5,
                },
                x: -5.5,
            },
            legend: {
                enabled: true,
                align: 'right',
                verticalAlign: 'top',
                width: '35%',
                y: -23,
                padding: 0,
                itemStyle: {
                    color: '#000000',
                    fontSize: "13px",
                },
            },
            tooltip: {
                animation: false,
                shared: true,
                split: false,
                headerFormat: '<span style="font-size: 13px">{point.key}</span><br/>',
                xDateFormat: '%b %e, %Y %H:%M UTC',
                backgroundColor: 'rgba(255, 255, 255, 1)',
                hideDelay: 0, // makes tooltip feel more responsive when crossing gap between plots
                style: {
                    color: '#000000',
                    fontSize: '13px',
                }
            },
            series: [
                {
                    name: 'Daily price',
                    id: 'dailyPrice',
                    data: daily_prices,
                    lineWidth: 1.5,
                    states: {
                        hover: {
                            lineWidthPlus: 0,
                            halo: false, // disable translucent halo on marker hover
                        }
                    },
                    yAxis: 0,
                    color: primaryLineColor,
                    marker: {
                        states: {
                            hover: {
                                lineWidth: 0,
                            }
                        },
                    },
                    tooltip: {
                        pointFormatter: function() {
                            return `<span style="color:${this.color}">\u25CF</span>`
                                + ` ${this.series.name}:`
                                + ` <b>${this.y.toLocaleString()}g</b><br/>`;
                        },
                    },
                }, {
                    name: 'Volume',
                    type: 'column',
                    data: daily_trade_volume,
                    pointPadding: 0, // disable point and group padding to simulate column area chart
                    groupPadding: 0,
                    yAxis: 2,
                    color: volumeColor,
                    tooltip: {
                        pointFormatter: function() {
                            if (this.y !== 0){
                                var volumeText = this.y.toLocaleString();
                            } else {
                                var volumeText = 'n/a';
                            }
                            return `<span style="color:${this.color}">\u25CF</span>`
                                + ` ${this.series.name}:`
                                + ` <b>${volumeText}</b><br/>`;
                        },
                    },
                }, {
                    name: 'SBI',
                    id: 'sbi',
                    data: sbi,
                    visible: false,
                    lineWidth: 1.5,
                    states: {
                        hover: {
                            lineWidthPlus: 0,
                            halo: false, // disable translucent halo on marker hover
                        }
                    },
                    yAxis: 1,
                    color: sbiLineColor,
                    marker: {
                        states: {
                            hover: {
                                lineWidth: 0,
                            }
                        },
                    },
                    tooltip: {
                        pointFormatter: function() {
                            if (this.y >= 1000) {
                                var sbiText = Math.round(this.y).toLocaleString();
                            } else if (this.y >= 100) {
                                var sbiText = this.y.toFixed(1).toLocaleString();
                            } else if (this.y >= 10) {
                                var sbiText = this.y.toFixed(2).toLocaleString();
                            } else {
                                var sbiText = this.y.toFixed(3).toLocaleString();
                            }
                            return `<span style="color:${this.color}">\u25CF</span>`
                                + ` SB Index:`
                                + ` <b>${sbiText} SB</b><br/>`;
                        },
                    },
                },
            ],
            yAxis: [
                {
                    // lineWidth: 1,
                    labels: {
                        formatter: function() {
                            return this.value.toLocaleString() + 'g';
                        },
                        x: -8,
                    },
                    showLastLabel: true, // show label at top of chart
                    crosshair: {
                        dashStyle: 'Dot',
                        color: crosshairColor,
                    },
                    opposite: false,
                    alignTicks: false, // disabled, otherwise autoranger will create too large a Y-window
                }, {
                    gridLineWidth: 0,
                    labels: {
                        formatter: function() {
                            return this.value.toLocaleString() + ' SB';
                        }
                    },
                    showLastLabel: true, // show label at top of chart
                    opposite: true,
                    alignTicks: false,
                }, {
                    top: '82%',
                    height: '18%',
                    offset: 0,
                    labels: {
                        align: 'left',
                        x: 4,
                        style: {
                            color: volumeLabelColor
                        }
                    },
                    gridLineWidth: 0,
                    opposite: false,
                    tickPixelInterval: 25,
                    showFirstLabel: false,
                    allowDecimals: false
            }],
            xAxis: {
                type: 'datetime',
                ordinal: false, // show continuous x axis if dates are missing
                plotBands: eventBands,
                plotLines: yearLines,
                crosshair: {
                    dashStyle: 'Dot',
                    color: crosshairColor,
                },
                dateTimeLabelFormats:{
                    day: '%b %e',
                    week: '%b %e, \'%y',
                    month: '%b %Y',
                    year: '%Y'
                },
                tickPixelInterval: 120,
            },
            navigator: {
                height: 40,
                margin: 5,
                maskInside: false,
            }
        });

        loadingElem.style.display = "none";

        if (response.market_data.length > 0) {
            var latest = response.market_data[response.market_data.length - 1];
            priceElem.innerHTML = latest.price.toLocaleString() + 'g';
            
            // set SB price
            try {
                sbPriceElem.innerHTML = latest.sb_price.toLocaleString("en-US", {maximumFractionDigits: 2}) + ' SB';
            } catch (e) {
                sbPriceElem.innerHTML = 'SB price not available';
            }

            // set price gain/loss
            if ((response.market_data.length > 1) && (Date.now() - UtcIsoDateToMillis(latest.date) < 2 * 86400 * 1000)) {
                var secondLatest = response.market_data[response.market_data.length - 2];
                var goldDiff = latest.price - secondLatest.price;
                var diffClass = "";
                var prefix = "";

                if (goldDiff > 0) {
                    prefix = '+';
                    diffClass = 'gains-text';
                } else if (goldDiff < 0) {
                    prefix = '-';
                    diffClass = 'loss-text';
                }

                changeElem.innerHTML = prefix 
                    + Math.abs(goldDiff).toLocaleString() + 'g'
                    + ' (' + Math.abs((latest.price / secondLatest.price - 1) * 100).toFixed(1) + '%)';
                    changeElem.className = diffClass;
            } else {
                changeElem.innerHTML = "<i>No recent activity</i>";
            }

            // set volume data
            const utcTodayMillis = UtcIsoDateToMillis(new Date().toISOString().substring(0, 10));

            let tradeVolumeText = "0";
            if (utcTodayMillis - UtcIsoDateToMillis(latest.date) <= 86400 * 1000 && latest.volume !== null) {
                tradeVolumeText = latest.volume.toLocaleString();
            }
            tradeVolumeElem.innerHTML = tradeVolumeText;

            let goldVolumeText = "0";
            if (utcTodayMillis - UtcIsoDateToMillis(latest.date) <= 86400 * 1000 && latest.volume !== null) {
                goldVolumeText = formatSISuffix(latest.volume * latest.price, 2);
            }
            goldVolumeElem.innerHTML = goldVolumeText + " gold";

            const weeklyVolText = response.market_data.reduce(
                function(sum, dataPoint) {
                    if (utcTodayMillis - UtcIsoDateToMillis(dataPoint.date) <= 7 * 86400 * 1000) {
                        return sum + (dataPoint.volume !== null ? dataPoint.volume : 0);
                    } else {
                        return sum;
                    }
                },
                0
            );
            weekTradeVolumeElem.innerHTML = weeklyVolText.toLocaleString();

            const weeklyGoldVol = response.market_data.reduce(
                function(sum, dataPoint) {
                    if (utcTodayMillis - UtcIsoDateToMillis(dataPoint.date) <= 7 * 86400 * 1000) {
                        return sum + (dataPoint.volume !== null ? dataPoint.volume * dataPoint.price : 0);
                    } else {
                        return sum;
                    }
                },
                0
            );
            weekGoldVolumeElem.innerHTML = (weeklyGoldVol === 0 ? '0' : formatSISuffix(weeklyGoldVol, 2)) + " gold";
        }
    }

    $.getJSON(`/api/items/${itemId}`, function (response) {
        var selector = document.getElementById('selected-item');
        var selectedItemId = selector.dataset.itemId;

        if (selectedItemId == null || selectedItemId == itemId) {
            renderChart(response);
        }
    });
}

function renderBiHourlyStockChart(itemId) {
    itemId = Number(itemId);

    const loadingElem = document.getElementsByClassName('chart-loading')[0];
    loadingElem.style.display = "flex";

    function renderChart(response) {
        const bid_data = [];
        const ask_data = [];
        const supply_data = [];

        response.stock_data.forEach(x => {
            bid_data.push([x.timestamp, x.bid]);
            ask_data.push([x.timestamp, x.ask]);
            supply_data.push([x.timestamp, x.supply]);
        })

        Highcharts.setOptions({
            chart: {
                style: {
                    fontFamily: chartFont,
                },
            },
            lang: {
                rangeSelectorZoom :""
            },
            plotOptions: {
                series: {
                    animation: false,
                    dataGrouping: {
                        enabled: true,
                        units: [['hour', [1]], ['day', [1]], ['week', [1]]],
                        groupPixelWidth: 3,
                    },
                    showInLegend: true,
                },
            },
            xAxis: {
                tickColor: xTickColor,
                labels: {
                    style: {
                        color: axisLabelColor,
                        fontSize: '12px',
                    }
                }
            },
            yAxis: {
                gridLineColor: yGridLineColor,
                labels: {
                    style: {
                        color: axisLabelColor,
                        fontSize: '12px',
                    },
                    y: 3,
                }
            }
        });

        // Create the chart
        var chart = new Highcharts.stockChart('chartContainer', {
            chart: {
                animation: false, // disable range selector zooming animation
            },
            // must keep scrollbar enabled for dynamic scrolling, so hide the scrollbar instead
            scrollbar: {
                height: 0,
                buttonArrowColor: "#ffffff00",
            },
            title: {
                enabled: false,
            },
            rangeSelector: {
                buttons: [
                    {
                        type: 'day',
                        count: 7,
                        text: '7D'
                    },
                    {
                        type: 'month',
                        count: 1,
                        text: '1M'
                    }, {
                        type: 'month',
                        count: 3,
                        text: '3M'
                    }, {
                        type: 'month',
                        count: 6,
                        text: '6M'
                    }, {
                        type: 'year',
                        count: 1,
                        text: '1Y'
                    }, {
                        type: 'all',
                        text: 'All'
                    },
                ],
                selected: 1,
                inputEnabled: false,
                labelStyle: {
                    color: axisLabelColor,
                },
                buttonPosition: {
                    y: 5,
                },
                x: -5.5,
            },
            legend: {
                enabled: true,
                align: 'right',
                verticalAlign: 'top',
                width: '35%',
                y: -23,
                padding: 0,
                itemStyle: {
                    color: '#000000',
                    fontSize: "13px",
                },
            },
            tooltip: {
                animation: false,
                shared: true,
                split: false,
                headerFormat: '<span style="font-size: 13px">{point.key}</span><br/>',
                xDateFormat: '%b %e, %Y %H:%M UTC',
                backgroundColor: 'rgba(255, 255, 255, 1)',
                hideDelay: 0, // makes tooltip feel more responsive when crossing gap between plots
                style: {
                    color: '#000000',
                    fontSize: '13px',
                }
            },
            series: [
                {
                    name: 'Ask',
                    id: 'ask',
                    type: 'line',
                    data: ask_data,
                    lineWidth: 1.5,
                    states: {
                        hover: {
                            lineWidthPlus: 0,
                            halo: false, // disable translucent halo on marker hover
                        }
                    },
                    yAxis: 0,
                    color: primaryLineColor,
                    marker: {
                        states: {
                            hover: {
                                lineWidth: 0,
                            }
                        },
                    },
                    tooltip: {
                        pointFormatter: function() {
                            return `<span style="color:${this.color}">\u25CF</span>`
                                + ` ${this.series.name}:`
                                + ` <b>${this.y.toLocaleString()}g</b><br/>`;
                        },
                    },
                    zIndex: 1,
                },
                {
                    name: 'Bid',
                    id: 'bid',
                    type: 'line',
                    data: bid_data,
                    lineWidth: 1.5,
                    states: {
                        hover: {
                            lineWidthPlus: 0,
                            halo: false, // disable translucent halo on marker hover
                        }
                    },
                    yAxis: 0,
                    color: secondaryLineColor,
                    marker: {
                        states: {
                            hover: {
                                lineWidth: 0,
                            }
                        },
                    },
                    tooltip: {
                        pointFormatter: function() {
                            return `<span style="color:${this.color}">\u25CF</span>`
                                + ` ${this.series.name}:`
                                + ` <b>${this.y.toLocaleString()}g</b><br/>`;
                        },
                    },
                    zIndex: 2,
                },
                {
                    name: 'Supply',
                    id: 'supply',
                    type: 'area',
                    data: supply_data,
                    yAxis: 1,
                    color: volumeColor,
                    lineWidth: 1.5,
                    states: {
                        hover: {
                            lineWidthPlus: 0,
                            halo: false, // disable translucent halo on marker hover
                        }
                    },
                    marker: {
                        states: {
                            hover: {
                                lineWidth: 0,
                            }
                        },
                    },
                    zIndex: 0,
                },
            ],
            yAxis: [
                {
                    labels: {
                        formatter: function() {
                            return this.value.toLocaleString() + 'g';
                        },
                        x: -8,
                    },
                    showLastLabel: true, // show label at top of chart
                    crosshair: {
                        dashStyle: 'Dot',
                        color: crosshairColor,
                    },
                    opposite: false,
                    alignTicks: false, // disabled, otherwise autoranger will create too large a Y-window
                },
                {
                    top: '82%',
                    height: '18%',
                    offset: 0,
                    min: 0,
                    labels: {
                        align: 'left',
                        x: 4,
                        style: {
                            color: volumeLabelColor
                        }
                    },
                    gridLineWidth: 0,
                    opposite: false,
                    tickPixelInterval: 25,
                    showFirstLabel: false,
                    allowDecimals: false
                }
            ],
            xAxis: {
                type: 'datetime',
                ordinal: false, // show continuous x axis if dates are missing
                plotBands: eventBands,
                plotLines: yearLines,
                crosshair: {
                    dashStyle: 'Dot',
                    color: crosshairColor,
                },
                dateTimeLabelFormats:{
                    day: '%b %e',
                    week: '%b %e, \'%y',
                    month: '%b %Y',
                    year: '%Y'
                },
                tickPixelInterval: 120,
            },
            navigator: {
                height: 40,
                margin: 5,
                maskInside: false,
            }
        });

        loadingElem.style.display = "none";
    }

    $.getJSON(`/api/items/${itemId}/stock?token=${localStorage.apiToken}`, (response) => {
        var selector = document.getElementById('selected-item');
        var selectedItemId = selector.dataset.itemId;

        if (selectedItemId == null || selectedItemId == itemId) {
            renderChart(response);
        }
    });
}