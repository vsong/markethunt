<template>
    <tr class="portfolio-position-row" @click="expanded = !expanded">
        <td :data-text="position.date_modified">
            <span class="spacer"></span>
            {{ formatDate(new Date(position.date_modified)) }}
        </td>
        <td class="right-align hide-mobile">{{ position.qty.toLocaleString() }}</td>
        <td class="right-align hide-mobile shrink-wrap">
            {{ position.mark.toLocaleString(...localeStringOpts) + currencySuffix}}
        </td>
        <td class="right-align shrink-wrap">
            {{ positionBookValue.toLocaleString(...localeStringOpts) + currencySuffix }}
            <div class="book-value-mobile-hint">
                {{ position.qty.toLocaleString() }} @ {{ position.mark.toLocaleString(...localeStringOpts) + currencySuffix }}
            </div>
        </td>
        <td class="right-align shrink-wrap" v-bind:class="getColorClass(positionChangePercent)">
            {{ positionMarketValue.toLocaleString(...localeStringOpts) + currencySuffix }}
        </td>
        <td class="right-align" v-bind:class="getColorClass(positionChangePercent)">
            {{ formatPercent(positionChangePercent) }}
        </td>
        <td class="right-align hide-mobile">
            {{ positionPortfolioPercent.toFixed(1) + '%' }}
        </td>
        <td v-if="isDebugEnabled()"></td>
        <td class="right-align button-container">
            <span @click="editPosition(position.uid)" class="material-icons">edit</span>
            <span @click="removePosition(position.uid)" class="material-icons">delete</span>
        </td>
    </tr>
</template>

<script>
export default {
    name: "PortfolioPositionRow",
    props: ['position', 'itemData', 'portfolioTotals'],
    computed: {
        currentItemPrice() {
            return this.position.mark_type === 'gold' ? this.itemData.latestPrice : this.itemData.latestSbPrice;
        },
        positionBookValue() {
            return this.position.qty * this.position.mark;
        },
        positionMarketValue() {
            return this.position.qty * this.currentItemPrice;
        },
        positionChangePercent() {
            return (this.currentItemPrice / this.position.mark - 1) * 100;
        },
        positionPortfolioPercent() {
            const goldConversionFactor = this.position.mark_type === 'sb' ? appData.itemData[114].latest_price : 1;
            return (this.positionMarketValue * goldConversionFactor / this.portfolioTotals.total) * 100;
        },
        localeStringOpts() {
            return getMarkTypeLocaleStringOpts(this.position.mark_type);
        },
        currencySuffix() {
            return getMarkTypeAppendText(this.position.mark_type);
        }
    },
    methods: {
        formatPercent(num) {
            return formatPercent(num);
        },
        getColorClass(num) {
            return getVueColorClassBinding(num);
        },
        isDebugEnabled() {
            return isDebugModeEnabled();
        },
        removePosition(positionUid) {
            for (let portfolio of appData.portfolios) {
                const positionIndex = portfolio.positions.findIndex(position => position.uid === positionUid);
                if (positionIndex > -1) {
                    //removePosition(portfolio.uid, portfolio.positions[positionIndex].uid);
                    portfolio.positions.splice(positionIndex, 1);
                    break;
                }
            }
        },
        editPosition(positionUid) {
            let pidx = 0;
            let positionIdx = 0;

            for (pidx = 0; pidx < appData.portfolios.length; pidx++) {
                positionIdx = appData.portfolios[pidx].positions.findIndex(position => position.uid === positionUid);
                if (positionIdx > -1) {
                    break;
                }
            }

            const positionIndex = appData.portfolios[pidx].positions.map(p => p.uid).indexOf(positionUid);
            const position = appData.portfolios[pidx].positions[positionIndex];
            addToPortfolioModal(position.item_id, position, pidx, positionIndex);
        },
        formatDate(date) {
            return date.toLocaleString();
        },
        unixTimeToIsoString(timestamp) {
            return unixTimeToIsoString(timestamp);
        }
    }
}
</script>

<style scoped>
.portfolio-position-row {
    font-size: 0.8em;
}

.portfolio-position-row td {
    padding-top: 5px;
    padding-bottom: 5px;
}

td.button-container .material-icons {
    font-size: 18px;
}

.spacer {
    padding-left: 23px;
}
</style>