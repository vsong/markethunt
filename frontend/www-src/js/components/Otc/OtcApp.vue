<template>
    <ItemSelectorGeneric :available-options="selectorOptions" @selection-changed="handleSelection"></ItemSelectorGeneric>
    <Highchart v-if="chartData.length > 0" :constructor-type="'stockChart'" :options="chartConfig" ref="chartObj" style="height: 450px"></Highchart>
</template>

<script>
import ItemSelectorGeneric from "../ItemSelectorGeneric.vue";
import {Chart} from 'highcharts-vue';

export default {
    name: "OtcApp",
    components: {
        ItemSelectorGeneric,
        Highchart: Chart
    },
    data() {
        return {
            state: null,
            chartData: []
        }
    },
    computed: {
        selectorOptions() {
            if (this.state == null) return [];

            return this.state.listingCombinations.map(x => ({
                key: x,
                name: `${this.getListingTypeName(x.listing_type)} - ${x.item.name}`,
                thumbnailUrl: `https://cdn.markethunt.win/thumbnail/item/${x.item.item_id}.gif`,
            }));
        },
        eventBands() {
            function UtcIsoDateToMillis(dateStr) {
                return (new Date(dateStr + "T00:00:00+00:00")).getTime();
            }

            function eventBand(labelText, IsoStrFrom, IsoStrTo) {
                return {
                    from: UtcIsoDateToMillis(IsoStrFrom),
                    to: UtcIsoDateToMillis(IsoStrTo),
                    color: "#f2f2f2",
                    label: {
                        text: labelText,
                        rotation: 270,
                        textAlign: 'right',
                        y: 5, // pixels from top of chart
                        x: 4, // fix slight centering issue
                        style: {
                            color: "#999999",
                            fontWeight: 'bold',
                            fontSize: '13px',
                            fontFamily: 'Trebuchet MS, Arial, sans-serif',
                        },
                    },
                }
            }

            if (this.state == null) return [];

            return this.state.events.map(event => eventBand(event.short_name, event.start_date, event.end_date));
        }
        ,
        chartConfig() {
            const data = this.chartData.filter(x => x.sb_price > (x.listing_type === 5 ? 0 : 50) && x.sb_price < 10000);

            const sellData = data.filter(x => x.is_selling).map(x => [x.timestamp, x.sb_price]);
            const buyData = data.filter(x => !x.is_selling).map(x => [x.timestamp, x.sb_price]);

            return {
                chart: {
                    style: {
                        fontFamily: 'arial, sans-serif',
                    },
                    animation: false, // disable range selector zooming animation
                },
                scrollbar: {
                    height: 0,
                    buttonArrowColor: "#ffffff00",
                },
                lang: {
                    rangeSelectorZoom :""
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
                        }, {
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
                    selected: 5,
                    inputEnabled: false,
                    labelStyle: {
                        color: "#444444",
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
                plotOptions: {
                    series: {
                        animation: false,
                        showInLegend: true,
                        gapUnit: "value",
                        gapSize: 1000 * 60 * 60 * 24 * 7,
                    },
                },
                series: [
                    {
                        name: 'Sell Listings',
                        id: 'sellListings',
                        data: sellData,
                        lineWidth: 1.5,
                        states: {
                            hover: {
                                lineWidthPlus: 0,
                                halo: false, // disable translucent halo on marker hover
                            }
                        },
                        yAxis: 0,
                        color: "#4f52aa",
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
                                    + ` <b>${this.y.toLocaleString()}</b><br/>`;
                            },
                        },
                    },
                    {
                        name: 'Buy Listings',
                        id: 'buyListings',
                        data: buyData,
                        lineWidth: 1.5,
                        states: {
                            hover: {
                                lineWidthPlus: 0,
                                halo: false, // disable translucent halo on marker hover
                            }
                        },
                        yAxis: 0,
                        color: "#b91a05",
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
                                    + ` <b>${this.y.toLocaleString()}</b><br/>`;
                            },
                        },
                    }
                ],
                yAxis: {
                    labels: {
                        formatter: function() {
                            return this.value.toLocaleString() + ' SB';
                        },
                        x: -8,
                        style: {
                            color: "#444444",
                            fontSize: '12px',
                        },
                        y: 3,
                    },
                    showLastLabel: true, // show label at top of chart
                    crosshair: {
                        dashStyle: 'Dot',
                        color: "#222222",
                    },
                    opposite: false,
                    alignTicks: false, // disabled, otherwise autoranger will create too large a Y-window
                    gridLineColor: "#aaaaaa"
                },
                xAxis: {
                    type: 'datetime',
                    ordinal: false, // show continuous x axis if dates are missing
                    plotBands: this.eventBands,
                    crosshair: {
                        dashStyle: 'Dot',
                        color: "#222222",
                    },
                    dateTimeLabelFormats:{
                        day: '%b %e',
                        week: '%b %e, \'%y',
                        month: '%b %Y',
                        year: '%Y'
                    },
                    tickPixelInterval: 120,
                    tickColor: "#bbbbbb",
                    labels: {
                        style: {
                            color: '#444444',
                            fontSize: '12px',
                        }
                    }
                },
                navigator: {
                    height: 40,
                    margin: 5,
                    maskInside: false,
                }
            }
        }
    },
    methods: {
        async fetchData() {
            const listingCombinationsFetch = fetch(`/api/otc/listings`)
                .then(res => res.json());

            const eventsFetch = fetch(`/api/events`)
                .then(res => res.json());

            Promise.all([listingCombinationsFetch, eventsFetch]).then(values => {
                this.state = {
                    listingCombinations: values[0],
                    events: values[1],
                }
            })
        },
        getListingTypeName(type) {
            switch (type) {
                case 1:
                    return 'Unopened Map';
                case 2:
                    return 'Fresh Map';
                case 3:
                    return 'Completed Map';
                case 4:
                    return 'Leech';
                case 5:
                    return 'Trade';
                default:
                    return 'Report this to Github';
            }
        },
        async handleSelection(listingCombination) {
            const res = await fetch(`/api/otc/listings/${listingCombination.listing_type}/${listingCombination.item.item_id}`);
            this.chartData = await res.json();
        }
    },
    created() {
        this.fetchData();
    }
}
</script>

<style scoped>

</style>