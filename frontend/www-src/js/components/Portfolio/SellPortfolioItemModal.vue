<template>
    <ModalTemplate
        ref="modal"
        :title="`Sell ${item.name}`"
        positive-button-text="Sell"
        negative-button-text="Cancel"
        width="auto"
        @modalClosed="$emit('closed')"
        @positiveButtonClicked="sell"
        @negativeButtonClicked="$refs.modal.closeModal()"
    >
        <form id="sell-item-form" name="sell-item-form" class="pure-form pure-form-stacked" onsubmit="return false">
            <fieldset>
                <label for="comment">Amount</label>
                <div style="display: flex; align-items: center">
                    <div style="flex-grow: 1">
                        <input type="number" id="comment" min=1 :max="itemQty" v-model="sellAmount"/>
                    </div>
                    <div style="margin-left: 0.5em">
                        / {{ itemQty.toLocaleString() }}
                    </div>
                </div>

                <template v-if="markType === 'gold'">
                    <label for="sell-price-gold">Sell price (ea)</label>
                    <input type="number" id="sell-price-gold" min=1 max=9999999999 v-model="sellPrice">
                </template>
                <template v-else>
                    <label for="sell-price-sb">Sell price (ea)</label>
                    <input type="number" id="sell-price-sb" min=0.001 max=999999 step="0.001" v-model="sellPrice">
                </template>
                <label for="radio-gold-sell-price" class="pure-radio marktype-radio">
                    <input type="radio" id="radio-gold-sell-price" name="sell-mark-type" value="gold" disabled :checked.attr="markType === 'gold'" />Gold
                </label>
                <label for="radio-sb-sell-price" class="pure-radio marktype-radio">
                    <input type="radio" id="radio-sb-sell-price" name="sell-mark-type" value="sb" disabled :checked.attr="markType === 'sb'" />SB
                </label>

                <label>Cost Formula</label>
                <label for="radio-cost-lifo" class="pure-radio marktype-radio">
                    <input type="radio" id="radio-cost-lifo" name="sell-cost-method" value="fifo" v-model="costingMethod" />FIFO
                </label>
                <label for="radio-cost-avg" class="pure-radio marktype-radio">
                    <input type="radio" id="radio-cost-avg" name="sell-cost-method" value="avg" v-model="costingMethod" />Weighted average
                </label>
                <label for="radio-cost-fifo" class="pure-radio marktype-radio">
                    <input type="radio" id="radio-cost-fifo" name="sell-cost-method" value="lifo" v-model="costingMethod" />LIFO
                </label>
            </fieldset>
        </form>
        <h4>Positions Summary</h4>
        <table class="pure-table pure-table-striped sell-summary-table">
            <thead>
                <tr>
                    <th class="shrink-wrap">Date added</th>
                    <th class="right-align shrink-wrap">Qty</th>
                    <th class="right-align">Buy price</th>
                    <th class="right-align">Profit</th>
                </tr>
            </thead>
            <tbody style="font-size: 0.9em">
                <tr v-for="position in sortedPositions">
                    <td class="shrink-wrap">{{ unixTimeToIsoString(position.date_modified) }}</td>
                    <td class="right-align shrink-wrap">
                        <template v-if="positionChanges[position.uid].newQty === position.qty">{{ position.qty.toLocaleString() }}</template>
                        <template v-else><s>{{ position.qty.toLocaleString() }}</s> {{ positionChanges[position.uid].newQty.toLocaleString() }}</template>
                    </td>
                    <td class="right-align">
                        <template v-if="positionChanges[position.uid].newQty === position.qty">
                            {{ position.mark.toLocaleString(...localeStringOpts) + currencySuffix }}
                        </template>
                        <template v-else>
                            <span :class="getColorClass(sellPrice - position.mark)">
                                {{ position.mark.toLocaleString(...localeStringOpts) + currencySuffix }}
                            </span>
                        </template>
                    </td>
                    <td class="right-align">
                        <span v-if="positionChanges[position.uid].newQty !== position.qty" :class="getColorClass(positionChanges[position.uid].profit)">
                            {{ positionChanges[position.uid].profit.toLocaleString(...localeStringOpts) + currencySuffix}} ({{ formatPercent(positionChanges[position.uid].profitPercent) }})
                        </span>
                    </td>
                </tr>
            </tbody>
        </table>
        <p style="font-weight: bold">
            Total Profit:
            <span :class="getColorClass(totalProfit)">
                {{ totalProfit.toLocaleString(...localeStringOpts) + currencySuffix }} ({{ formatPercent(totalProfitPercent) }})
            </span>
        </p>
    </ModalTemplate>
</template>

<script>
import ModalTemplate from "../ModalTemplate.vue";

export default {
    name: "SellPortfolioItemModal",
    components: {ModalTemplate},
    props: ['portfolio', 'item'],
    emits: ['closed'],
    data() {
        return {
            sellAmount: 1,
            sellPrice: 1,
            costingMethod: 'fifo'
        }
    },
    computed: {
        itemQty() {
            return this.item.positions.reduce((sum, position) => {
                return sum += position.qty;
            }, 0);
        },
        markType() {
            return this.item.positions[0].mark_type;
        },
        sortedPositions() {
            return [...this.item.positions].sort((a, b) => a.date_modified - b.date_modified);
        },
        sortedPositionsByQtyDesc() {
            return [...this.item.positions].sort((a, b) => b.qty - a.qty);
        },
        positionChanges() {
            // WARNING: positionChanges computed property is invalid while sell() method is running! Please make an
            // immutable copy first before performing any data mutations based on positionChanges.

            const positionChanges = {};

            if (this.costingMethod === 'fifo') {
                let amountSold = 0;

                this.sortedPositions.forEach(position => {
                    const amountLeftToSell = this.sellAmount - amountSold;

                    if (amountLeftToSell <= 0) {
                        positionChanges[position.uid] = {newQty: position.qty};
                        return;
                    }

                    const positionSellAmount = Math.min(position.qty, amountLeftToSell);
                    amountSold += positionSellAmount;

                    positionChanges[position.uid] = {newQty: position.qty - positionSellAmount};
                })
            } else if (this.costingMethod === 'avg') {
                let amountSold = 0;
                let amountLeftToSell = this.sellAmount;

                this.sortedPositionsByQtyDesc.forEach((position, i) => {
                    positionChanges[position.uid] = {newQty: position.qty};
                });

                while (amountSold < this.sellAmount && amountSold < this.itemQty ) {
                    this.sortedPositionsByQtyDesc.forEach((position, i) => {
                        if (amountLeftToSell <= 0 || positionChanges[position.uid].newQty <= 0) {
                            return;
                        }

                        const positionSellAmount = Math.max(Math.min(position.qty, Math.floor(this.sellAmount / this.itemQty  * position.qty), amountLeftToSell), 1);
                        amountSold += positionSellAmount;
                        amountLeftToSell -= positionSellAmount;

                        positionChanges[position.uid].newQty -= positionSellAmount;
                    });
                }
            } else { // lifo
                let amountSold = 0;

                [...this.sortedPositions].reverse().forEach(position => {
                    const amountLeftToSell = this.sellAmount - amountSold;

                    if (amountLeftToSell <= 0) {
                        positionChanges[position.uid] = {newQty: position.qty};
                        return;
                    }

                    const positionSellAmount = Math.min(position.qty, amountLeftToSell);
                    amountSold += positionSellAmount;

                    positionChanges[position.uid] = {newQty: position.qty - positionSellAmount};
                })
            }

            this.sortedPositions.forEach(position => {
                const sellAmount = position.qty - positionChanges[position.uid].newQty;
                positionChanges[position.uid].profit = sellAmount * (this.sellPrice - position.mark);
                positionChanges[position.uid].profitPercent = (this.sellPrice / position.mark - 1) * 100;
                positionChanges[position.uid].cost = position.mark * sellAmount;
            })

            return positionChanges;
        },
        totalCost() {
            return Object.keys(this.positionChanges).reduce((sum, positionUid) => {
                return sum += this.positionChanges[positionUid].cost;
            }, 0);
        },
        totalProfit() {
            return Object.keys(this.positionChanges).reduce((sum, positionUid) => {
                return sum += this.positionChanges[positionUid].profit;
            }, 0);
        },
        totalProfitPercent() {
            return this.totalProfit / this.totalCost * 100;
        },
        averageCost() {
            return this.totalCost / this.sellAmount;
        },
        currencySuffix() {
            return getMarkTypeAppendText(this.markType);
        },
        localeStringOpts() {
            return getMarkTypeLocaleStringOpts(this.markType);
        }
    },
    methods: {
        sell() {
            if (!document.forms['sell-item-form'].reportValidity()) {
                return;
            }

            // clone to prevent positionChanges computed property from changing mid-loop
            const positionChanges = Object.assign({}, this.positionChanges);

            [...this.sortedPositions].forEach(position => {
                const positionChange = positionChanges[position.uid];

                if (positionChange.newQty === 0) {
                    removePosition(this.portfolio.uid, position.uid);
                } else if (positionChange.newQty < position.qty) {
                    editPosition(this.portfolio.uid, position.uid, positionChange.newQty, position.mark)
                }
            });

            appendSellHistory(this.portfolio.uid, this.item.itemId, this.sellAmount, this.averageCost, this.sellPrice, this.markType);
            this.$refs.modal.closeModal();
        },
        unixTimeToIsoString(timestamp) {
            return unixTimeToIsoString(timestamp);
        },
        formatPercent(num) {
            return formatPercent(num);
        },
        getColorClass(num) {
            return getVueColorClassBinding(num);
        }
    },
    created() {
        this.sellPrice = this.markType === 'gold' ? this.item.latestPrice : Math.round(this.item.latestSbPrice * 100) / 100;
    }
}
</script>

<style scoped>
.sell-summary-table {
    width: 100%
}
</style>