// chart vars
var eventBandColor = "#eeeeee";
var eventBandFontColor = "#555555";

// prepare data
var timezone = "T00:00:00"
var daily_prices = [];
var daily_trade_volume = [];
var daily_bid_ask = [];

// get saved date range
if (localStorage.chartDateRanges === undefined) {
    var chartDateRanges = {};
} else {
    var chartDateRanges = JSON.parse(localStorage.chartDateRanges);
}

function renderChartWithItemId(itemId, chartHeaderText, markLines = []) {
    try {
        var currentDateMinimum = chartDateRanges[itemId]['min'];
    } catch (e) {
        var currentDateMinimum = 1;
    }

    var stockChart = new CanvasJS.StockChart("chartContainer", {
        theme: "light2",
        exportEnabled: false,
        rangeChanged: function(e){
            chartDateRanges[itemId] = {'min': e.minimum};
            localStorage.chartDateRanges = JSON.stringify(chartDateRanges);
        },
        charts: [{
            toolTip: {
                shared: true
            },
            axisX: {
                lineThickness: 3,
                tickLength: 0,
                stripLines: [{
                        startValue: new Date('2020-08-18'),
                        endValue: new Date('2020-09-08'),
                        color: eventBandColor,
                        label: "Ronza 2020",
                        labelFontColor: eventBandFontColor
                    },
                    {
                        startValue: new Date('2020-10-14'),
                        endValue: new Date('2020-11-03'),
                        color: eventBandColor,
                        label: "Halloween 2020",
                        labelFontColor: eventBandFontColor
                    },
                    {
                        startValue: new Date('2020-12-08'),
                        endValue: new Date('2021-01-06'),
                        color: eventBandColor,
                        label: "GWH 2020",
                        labelFontColor: eventBandFontColor
                    },
                    {
                        startValue: new Date('2021-02-09'),
                        endValue: new Date('2021-02-23'),
                        color: eventBandColor,
                        label: "LNY 2021",
                        labelFontColor: eventBandFontColor
                    },
                    {
                        startValue: new Date('2021-03-02'),
                        endValue: new Date('2021-03-23'),
                        color: eventBandColor,
                        label: "Birthday 2021",
                        labelFontColor: eventBandFontColor
                    },
                    {
                        startValue: new Date('2021-03-30'),
                        endValue: new Date('2021-04-27'),
                        color: eventBandColor,
                        label: "SEH 2021",
                        labelFontColor: eventBandFontColor
                    },
                    {
                        startValue: new Date('2021-05-25'),
                        endValue: new Date('2021-06-08'),
                        color: eventBandColor,
                        label: "KGA 2021",
                        labelFontColor: eventBandFontColor
                    },
                    {
                        startValue: new Date('2021-06-29'),
                        endValue: new Date('2021-07-13'),
                        color: eventBandColor,
                        label: "Jaq 2021",
                        labelFontColor: eventBandFontColor
                    },
                    {
                        startValue: new Date('2021-07-27'),
                        endValue: new Date('2021-08-17'),
                        color: eventBandColor,
                        label: "Ronza 2021",
                        labelFontColor: eventBandFontColor
                    },
                    {
                        startValue: new Date('2021-10-13'),
                        endValue: new Date('2021-11-02'),
                        color: eventBandColor,
                        label: "Halloween 2021",
                        labelFontColor: eventBandFontColor
                    },
                    {
                        startValue: new Date('2021-12-07'),
                        endValue: new Date('2022-01-05'),
                        color: eventBandColor,
                        label: "GWH 2021",
                        labelFontColor: eventBandFontColor
                    },
                ]
            },
            axisY: {
                suffix: "g",
                stripLines: markLines,
            },
            legend: {
                verticalAlign: "top"
            },
            data: [{
                name: "Bid/Ask",
                type: "rangeArea",
                yValueFormatString: "#,###",
                dataPoints : daily_bid_ask,
                color: "#baffe9",
                markerType: "none",
            }, {
                showInLegend: true,
                name: "Daily marketplace price",
                yValueFormatString: "#,###",
                type: "line",
                click: onClickDatapoint,
                dataPoints: daily_prices
            }, ]
        }, {
            height: 120,
            toolTip: {
                shared: true
            },
            axisY: {
                prefix: "",
                labelFormatter: addSymbols
            },
            legend: {
                verticalAlign: "top"
            },
            data: [{
                showInLegend: true,
                name: "Volume",
                yValueFormatString: "#,###.##",
                dataPoints: daily_trade_volume
            }]
        }],
        navigator: {
            enabled: true,
            height: 50,
            data: [{
                dataPoints: daily_prices
            }],
            slider: {
                minimum: new Date(currentDateMinimum),
                // maximum: new Date(2099, 08, 01)
            }
        }
    });

    document.getElementById('chartHeader').innerHTML = chartHeaderText;

    $.getJSON("api/stock_data/getjson.php?item_id=" + itemId, function(retval) {
        daily_prices = []; daily_trade_volume = [], daily_bid_ask = [];

        for (var i = 0; i < retval.data.length; i++) {
            stockChart.options.charts[0].data[1].dataPoints.push({
                x: new Date(retval.data[i].date + timezone),
                y: Number(retval.data[i].price)
            });
            stockChart.options.charts[1].data[0].dataPoints.push({
                x: new Date(retval.data[i].date + timezone),
                y: Number(retval.data[i].volume)
            });
        }

        for (var i = 0; i < retval.bid_ask.length; i++) {
            stockChart.options.charts[0].data[0].dataPoints.push({
                x: new Date(retval.bid_ask[i].date + timezone),
                y: [Number(retval.bid_ask[i].bid), Number(retval.bid_ask[i].ask)]
            });
        }

        stockChart.render();
    });
}

//calculateMovingAverage(stockChart);

function addSymbols(e) {
    var suffixes = ["", "K", "M", "B"];
    var order = Math.max(Math.floor(Math.log(e.value) / Math.log(1000)), 0);
    if (order > suffixes.length - 1)
        order = suffixes.length - 1;
    var suffix = suffixes[order];
    return CanvasJS.formatNumber(e.value / Math.pow(1000, order)) + suffix;
}

function calculateMovingAverage(chart) {
    var numOfDays = 7;
    // return if there are insufficient dataPoints
    if (chart.options.charts[0].data[0].dataPoints.length <= numOfDays) {
        return;
    } else {
        // Add a new line series for  Moving Averages
        chart.options.charts[0].data.push({
            visible: true,
            showInLegend: true,
            type: "line",
            markerSize: 0,
            name: "7 day trailing average",
            yValueFormatString: "#,###",
            dataPoints: []
        });
        var total;
        for (var i = numOfDays; i < chart.options.charts[0].data[0].dataPoints.length; i++) {
            total = 0;
            for (var j = (i - numOfDays); j < i; j++) {
                total += chart.options.charts[0].data[0].dataPoints[j].y;
            }
            chart.options.charts[0].data[1].dataPoints.push({
                x: chart.options.charts[0].data[0].dataPoints[i].x,
                y: total / numOfDays
            });
        }
    }
}