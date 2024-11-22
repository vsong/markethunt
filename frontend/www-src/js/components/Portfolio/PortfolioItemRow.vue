<template>
    <tr :class="{'clickable': item.positions.length > 1, 'zebra-striped': zebraStriped}" @click="expand">
        <td :class="{'sb-row-marker' : markType === 'sb'}" :data-text="item.name" style="padding-left: 0px">
            <span class="material-icons row-expand-icon" :class="{'invisible': item.positions.length <= 1}">
                {{ expanded ? 'expand_more' : 'chevron_right' }}
            </span>
            <a @click.stop="setChart(item.itemId.toString(), item.name)">
                {{ item.name }}
            </a>
        </td>
        <td class="right-align hide-mobile">{{ item.qty.toLocaleString() }}</td>
        <td class="right-align hide-mobile shrink-wrap">
            {{ item.avgBuyPrice.toLocaleString(...localeStringOpts) + currencySuffix}}
        </td>
        <td class="right-align shrink-wrap">
            {{ item.bookValue.toLocaleString(...localeStringOpts) + currencySuffix }}
            <div class="book-value-mobile-hint">
                {{ item.qty.toLocaleString() }} @ {{ item.avgBuyPrice.toLocaleString(...localeStringOpts) + currencySuffix }}
            </div>
        </td>
        <td class="right-align shrink-wrap" v-bind:class="getColorClass(item.changePercent)">
            {{ item.marketValue.toLocaleString(...localeStringOpts) + currencySuffix }}
        </td>
        <td class="right-align" v-bind:class="getColorClass(item.changePercent)">
            {{ formatPercent(item.changePercent) }}
        </td>
        <td class="right-align hide-mobile">
            {{ item.portfolioPercent.toFixed(1) + '%' }}
        </td>
        <td v-if="isDebugEnabled()">{{ item.itemId }}</td>
        <td class="right-align button-container">
            <div class="qty-badge" v-if="item.positions.length > 1">{{ item.positions.length }}</div>
            <span class="material-icons" v-if="item.positions.length === 1" @click.stop="editPosition">edit</span>
            <span class="material-icons" @click.stop="showSellItemModal = true">sell</span>
            <span class="material-icons" @click.stop="deleteItem">delete</span>
        </td>
    </tr>
    <DeletePortfolioItemWarning
        v-if="showDeleteItemWarning"
        :portfolio="portfolio"
        :item="item"
        @closed="showDeleteItemWarning = false"
    ></DeletePortfolioItemWarning>
    <SellPortfolioItemModal
        v-if="showSellItemModal"
        :portfolio="portfolio"
        :item="item"
        @closed="showSellItemModal = false"
    ></SellPortfolioItemModal>
    <template v-if="expanded && item.positions.length > 1">
        <PortfolioPositionRow
            v-for="position in item.positions"
            :key="position.uid"
            :class="{'zebra-striped': zebraStriped}"
            :position="position"
            :itemData="item"
            :portfolioTotals="portfolioTotals"
        ></PortfolioPositionRow>
    </template>
</template>

<script>
import PortfolioPositionRow from "./PortfolioPositionRow.vue";
import DeletePortfolioItemWarning from "./DeletePortfolioItemWarning.vue";
import SellPortfolioItemModal from "./SellPortfolioItemModal.vue";

export default {
    name: "PortfolioItemRow",
    components: {
        SellPortfolioItemModal,
        DeletePortfolioItemWarning,
        PortfolioPositionRow
    },
    props: ['portfolio', 'markType', 'item', 'portfolioTotals', 'zebraStriped'],
    data() {
        return {
            expanded: false,
            showDeleteItemWarning: false,
            showSellItemModal: false,
        }
    },
    computed: {
        localeStringOpts() {
            return getMarkTypeLocaleStringOpts(this.markType);
        },
        currencySuffix() {
            return getMarkTypeAppendText(this.markType);
        }
    },
    methods: {
        expand() {
            if (this.item.positions.length > 1) {
                this.expanded = !this.expanded;
            }
        },
        formatPercent(num) {
            return formatPercent(num);
        },
        getColorClass(num) {
            return getVueColorClassBinding(num);
        },
        isDebugEnabled() {
            return isDebugModeEnabled();
        },
        setChart(itemId, headerText) {
            const event = new CustomEvent('setChart', {detail: itemId});
            document.dispatchEvent(event);

            window.history.replaceState({}, headerText, "/portfolio.php?item_id=" + itemId);
        },
        editPosition() {
            let pidx = appData.portfolios.findIndex(portfolio => portfolio.uid === this.portfolio.uid);
            let positionIdx = this.portfolio.positions.findIndex(position => position.uid === this.item.positions[0].uid);
            let position = appData.portfolios[pidx].positions[positionIdx];

            addToPortfolioModal(position.item_id, position, pidx, positionIdx);
        },
        deleteItem() {
            if (this.item.positions.length > 1) {
                this.showDeleteItemWarning = true;
            } else {
                removePosition(this.portfolio.uid, this.item.positions[0].uid)
            }
        }
    }
}
</script>

<style scoped>
.clickable {
    cursor: pointer;
}

.clickable:hover {
    background-color: var(--highlight-color-light);
}

.row-expand-icon {
    font-size: 18px;
    vertical-align: top;
    color: var(--highlight-color);
}

.invisible {
    opacity: 0;
}

.zebra-striped {
    background-color: #f2f2f2;
}

.qty-badge {
    display: inline-block;
    font-size: 0.8em;
    color: white;
    background-color: var(--highlight-color);
    padding: 0.3em;
    border-radius: 99em;
    width: 12px;
    height: 12px;
    text-align: center;
    box-shadow: 1px 1px 1px #aaaaaa;
    margin-right: 2px;
    box-sizing: content-box; /* override quasar css */
}
</style>