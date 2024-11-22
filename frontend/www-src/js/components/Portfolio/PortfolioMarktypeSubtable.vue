<template>
    <tbody class="tablesorter-infoOnly" style="border-bottom: 1px dashed grey">
        <tr class="portfolio-summary-row">
            <td v-if="markType === 'gold'">Total gold value</td>
            <td v-if="markType === 'sb'" >Total SB value</td>
            <td class="right-align hide-mobile">{{ subPortfolioTotalQty.toLocaleString() }}</td>
            <td class="right-align hide-mobile">{{
                    subPortfolioAvgBuyPrice.toLocaleString(...localeStringOpts) + markTypeAppendText
                }}</td>
            <td class="right-align">{{
                    subPortfolioTotalBookValue.toLocaleString(...localeStringOpts) + markTypeAppendText
                }}</td>
            <td class="right-align" v-bind:class="getColorClass(totalMarketValues[markType] / subPortfolioTotalBookValue - 1)">
                {{ totalMarketValues[markType].toLocaleString(...localeStringOpts) + markTypeAppendText }}
            </td>
            <td class="right-align" v-bind:class="getColorClass(totalMarketValues[markType] / subPortfolioTotalBookValue - 1)">
                {{
                    formatPercent((totalMarketValues[markType] / subPortfolioTotalBookValue - 1) * 100)
                }}
            </td>
            <td class="right-align hide-mobile">
                {{ (totalMarketValues[markType] * (markType === 'sb' ? itemMarketData[114].latest_price : 1) / totalMarketValues.total * 100).toFixed(1) }}%
            </td>
            <td v-if="debugEnabled"></td>
            <td></td>
        </tr>
        <tr class="portfolio-summary-row" style="font-weight: normal" v-if="markType === 'gold' && portfolio.inventory_gold">
            <td>Inventory gold</td>
            <td class="right-align hide-mobile"></td>
            <td class="right-align hide-mobile"></td>
            <td class="right-align">{{ portfolio.inventory_gold.toLocaleString(...localeStringOpts) }}</td>
            <td class="right-align">{{ portfolio.inventory_gold.toLocaleString(...localeStringOpts) }}</td>
            <td class="right-align"></td>
            <td class="right-align hide-mobile">{{ ((portfolio.inventory_gold ?? 0) / totalMarketValues.total * 100).toFixed(1) }}%</td>
            <td v-if="debugEnabled"></td>
            <td></td>
        </tr>
    </tbody>
    <tbody>
        <template v-for="(item, idx) in sortedGroupedItemData">
            <PortfolioItemRow
                v-if="maxVisibleRows == null || idx < maxVisibleRows"
                :zebraStriped="idx % 2 === 1"
                :key="item.itemId"
                :portfolio="portfolio"
                :markType="markType"
                :item="item"
                :portfolioTotals="totalMarketValues"
            ></PortfolioItemRow>
        </template>
        <tr v-if="maxVisibleRows !== null && maxVisibleRows < sortedGroupedItemData.length">
            <td colspan="8" style="text-align: center">
                <a href="#" @click="showAllRows">Show all entries ({{ sortedGroupedItemData.length - maxVisibleRows }} hidden)</a>
            </td>
        </tr>
    </tbody>
</template>

<script>
import PortfolioItemRow from "./PortfolioItemRow.vue";

export default {
    name: "PortfolioMarktypeSubtable",
    components: {
        PortfolioItemRow
    },
    props: ['markType', 'portfolio', 'totalMarketValues', 'itemMarketData', 'debugEnabled', 'sortAscending', 'sortKey'],
    data() {
        return {
            maxVisibleRows: 100
        }
    },
    computed: {
        mappedPositions() {
            const map = {
                'gold': {},
                'sb': {}
            };

            this.portfolio.positions.forEach(position => {
                if (map[position.mark_type][position.item_id]) {
                    map[position.mark_type][position.item_id].push(position);
                } else {
                    map[position.mark_type][position.item_id] = [position];
                }
            });

            return map;
        },
        subPortfolioUniqueItemIds() {
            return Object.keys(this.mappedPositions[this.markType]);
        },
        subPortfolioTotalQty() {
            return this.portfolio.positions.reduce((totalQty, position) => {
                if (position.mark_type === this.markType) {
                    return totalQty + position.qty;
                } else {
                    return totalQty;
                }
            }, 0);
        },
        subPortfolioTotalBookValue() {
            let totalBookValue = this.portfolio.positions.reduce((totalBook, position) => {
                if (position.mark_type === this.markType) {
                    return totalBook + position.qty * position.mark;
                } else {
                    return totalBook;
                }
            }, 0);

            if (this.markType === 'gold') {
                totalBookValue += (this.portfolio.inventory_gold ?? 0);
            }

            return totalBookValue;
        },
        subPortfolioAvgBuyPrice() {
            let avgPrice = this.subPortfolioTotalBookValue / this.subPortfolioTotalQty;
            if (this.markType === "gold") {
                return Math.round(avgPrice);
            } else {
                return Math.round(avgPrice * 100) / 100;
            }
        },
        groupedItemData() {
            const groupedItemData = [];

            this.subPortfolioUniqueItemIds.forEach(itemId => {
                groupedItemData.push({
                    itemId,
                    qty: this.itemQty(itemId),
                    name: this.itemMarketData[itemId].name,
                    latestPrice: this.itemMarketData[itemId].latest_price,
                    latestSbPrice: this.itemMarketData[itemId].latest_sb_price,
                    avgBuyPrice: this.itemAverageBuyPrice(itemId),
                    bookValue: this.itemBookValue(itemId),
                    marketValue: this.itemMarketValue(itemId),
                    changePercent: this.itemChangePercent(itemId),
                    portfolioPercent: this.itemPortfolioPercent(itemId),
                    positions: this.mappedPositions[this.markType][itemId]
                });
            });

            return groupedItemData;
        },
        sortedGroupedItemData() {

            const sorted = [...this.groupedItemData];

            sorted.sort((a, b) => {
                if (this.sortKey === 'name') {
                    const orderFactor = this.sortAscending ? 1 : -1;

                    if (a.name > b.name) {
                        return 1 * orderFactor;
                    } else if (a.name < b.name) {
                        return -1 * orderFactor;
                    }

                    return 0;
                }

                if (this.sortAscending) {
                    return a[this.sortKey] - b[this.sortKey];
                } else {
                    return b[this.sortKey] - a[this.sortKey];
                }
            });

            return sorted;
        },
        markTypeAppendText() {
            return getMarkTypeAppendText(this.markType);
        },
        localeStringOpts() {
            return getMarkTypeLocaleStringOpts(this.markType);
        },
    },
    methods: {
        itemPositions(itemId) {
            return this.mappedPositions[this.markType][itemId];
        },
        currentItemPrice(itemId) {
            return this.markType === 'gold'
                ? this.itemMarketData[itemId].latest_price
                : this.itemMarketData[itemId].latest_sb_price;
        },
        itemQty(itemId) {
            return this.itemPositions(itemId).reduce((sum, position) => {
                return sum += position.qty;
            }, 0);
        },
        itemAverageBuyPrice(itemId) {
            return this.itemBookValue(itemId) / this.itemQty(itemId);
        },
        itemBookValue(itemId) {
            return this.itemPositions(itemId).reduce((sum, position) => {
                return sum += position.qty * position.mark;
            }, 0);
        },
        itemMarketValue(itemId) {
            return this.currentItemPrice(itemId) * this.itemQty(itemId);
        },
        itemChangePercent(itemId) {
            return (this.currentItemPrice(itemId) / this.itemAverageBuyPrice(itemId) - 1) * 100;
        },
        itemPortfolioPercent(itemId) {
            const goldConversionFactor = this.markType === 'sb' ? this.itemMarketData[114].latest_price : 1
            return (this.itemMarketValue(itemId) * goldConversionFactor / this.totalMarketValues.total) * 100;
        },
        formatPercent(num) {
            return formatPercent(num);
        },
        getColorClass(num) {
            return getVueColorClassBinding(num);
        },
        showAllRows() {
            this.maxVisibleRows = null;
        }
    }
}
</script>

<style scoped>

</style>