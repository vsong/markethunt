<template>
    <div style="text-align: center">
        <h1>Portfolio</h1>
    </div>
    <div v-cloak id="vue-container" class="watchlist-tabs-container">
        <ul>
            <li v-for="(portfolio, pidx) in portfolios" class="watchlist-tab">
                <a v-bind:href="'#' + portfolio.uid" @click="setTabIndex(pidx)">{{ portfolio.name }}</a>
                <div class="tab-control-container">
                    <i class="ui-icon light-ui tablesorter-icon ui-icon-pencil" @click="editPortfolio(pidx)"></i>
                    <i class="ui-icon light-ui tablesorter-icon ui-icon-close" @click="removePortfolio(pidx)"></i>
                </div>
            </li>
            <li class="watchlist-tab"><a style="padding: 0;" href="#newportfolio" @click="newPortfolio()"><span style="margin: 4px 1px;" class="material-icons">add</span></a></li>
        </ul>
        <div v-for="(portfolio, pidx) in portfolios" v-bind:id="portfolio.uid" class="watchlist-tab-content" :key="portfolio.uid">
            <template v-if="portfolio.uid === portfolios[selectedPortfolioIdx].uid">
                <table class="pure-table small-td-text table-sortable" style="width:100%; background-color: white;">
                    <thead>
                        <tr>
                            <!-- Must close th on same line to prevent extra space between text and sort arrows -->
                            <th class="sortable-header" @click="setSort('name')">
                                Item<i class="sortable-header ui-icon light-ui" :class="sortIconClass('name')"></i>
                            </th>
                            <th class="sortable-header right-align shrink-wrap hide-mobile" @click="setSort('qty')">
                                Qty<i class="ui-icon light-ui" :class="sortIconClass('qty')"></i>
                            </th>
                            <th class="sortable-header right-align shrink-wrap hide-mobile" @click="setSort('avgBuyPrice')">
                                Buy Price<i class="ui-icon light-ui" :class="sortIconClass('avgBuyPrice')"></i>
                            </th>
                            <th class="sortable-header right-align shrink-wrap" @click="setSort('bookValue')">
                                Book Value<i class="ui-icon light-ui" :class="sortIconClass('bookValue')"></i>
                            </th>
                            <th class="sortable-header right-align shrink-wrap" @click="setSort('marketValue')">
                                Market Value<i class="ui-icon light-ui" :class="sortIconClass('marketValue')"></i>
                            </th>
                            <th class="sortable-header right-align shrink-wrap" @click="setSort('changePercent')">
                                Chg%<i class="ui-icon light-ui " :class="sortIconClass('changePercent')"></i>
                            </th>
                            <th class="sortable-header right-align shrink-wrap hide-mobile" @click="setSort('portfolioPercent')">
                                Portfolio%<i class="ui-icon light-ui " :class="sortIconClass('portfolioPercent')"></i>
                            </th>
                            <th class="shrink-wrap sorter-false" style="background-color: rgb(216, 0, 0)" v-if="isDebugEnabled()">Debug</th>
                            <th class="button-container sorter-false"></th>
                        </tr>
                    </thead>
                    <template v-for="markType in ['sb', 'gold']">
                        <template v-if="portfolio.positions.filter(position => position.mark_type === markType).length >= 1">
                            <PortfolioMarktypeSubtable
                                :mark-type="markType"
                                :portfolio="selectedPortfolio"
                                :totalMarketValues="portfolioTotalMarketValues"
                                :itemMarketData="appData.itemData"
                                :debug-enabled="isDebugEnabled()"
                                :sort-ascending="sortAscending"
                                :sort-key="sortKey"
                            ></PortfolioMarktypeSubtable>
                        </template>
                    </template>
                </table>
                <div v-if="portfolio.positions.length === 0" class="empty-watchlist-message">This portfolio is empty.</div>
                <div class="hide-mobile" style="margin-top: 10px; text-align: right">
                    <a href="https://greasyfork.org/en/scripts/441382-mousehunt-markethunt-inventory-export">
                        <span class="material-icons">download</span>Import inventory from Mousehunt
                    </a>
                </div>
            </template>
        </div>
        <div id="newportfolio" class="watchlist-tab-content"></div>
        <div v-if="portfolios.length === 0">
            <p>You deleted all your portfolios! Click the <span style="font-size: 16px" class="material-icons">add</span> icon above to create a new portfolio. </p>
            <img src="/assets/img/NotLikeDuck.png" alt="SB position indicator"/>
        </div>
        <PortfolioDebugger v-if="isDebugEnabled()"></PortfolioDebugger>
    </div>
</template>

<script>
import PortfolioDebugger from './PortfolioDebugger.vue';
import PortfolioMarktypeSubtable from "./PortfolioMarktypeSubtable.vue";

export default {
    name: "PortfolioApp.vue",
    components: {
        PortfolioMarktypeSubtable,
        PortfolioDebugger
    },
    data() {
        return {
            appData: {
                itemData,
            },
            portfolios: JSON.parse(JSON.stringify(reactivePortfolio)),
            redrawTabs: false,
            selectedPortfolioIdx: 0,
            debugMode,
            sortAscending: true,
            sortKey: 'name',
        }
    },
    computed: {
        portfolioTotalMarketValues() {
            const portfolio = this.selectedPortfolio;
            let totalGoldValue = 0;
            let totalSbValue = 0;

            for (const position of portfolio.positions) {
                if (position.mark_type === "gold") {
                    totalGoldValue += position.qty * appData.itemData[position.item_id].latest_price;
                } else if (position.mark_type === "sb") {
                    totalSbValue += position.qty * appData.itemData[position.item_id].latest_sb_price;
                }
            }

            totalGoldValue += portfolio.inventory_gold ?? 0;

            return {
                gold: totalGoldValue,
                sb: totalSbValue,
                total: totalGoldValue + totalSbValue * appData.itemData[114].latest_price
            }
        },
        selectedPortfolio() {
            return this.portfolios[this.selectedPortfolioIdx];
        }
    },
    methods: {
        removePortfolio(pidx) {
            showUndeleteToast(getPortfolioObjKey());
            if (pidx < this.selectedPortfolioIdx || this.selectedPortfolioIdx === appData.portfolios.length - 1) {
                this.selectedPortfolioIdx--;
                $("#vue-container").tabs("option", "active", this.selectedPortfolioIdx);
            }
            appData.portfolios.splice(pidx, 1);
            this.redrawTabs = true;
            setPortfolioObj(appData.portfolios);
        },
        editPortfolio(pidx) {
            newPortfolioModal(pidx);
        },
        setTabIndex(pidx) {
            this.selectedPortfolioIdx = pidx;
            $('#portfolio-select').val(pidx);
            window.location.hash = this.portfolios[pidx].uid;
        },
        sortIconClass(sortKey) {
            if (this.sortKey !== sortKey) {
                return ['ui-icon-caret-2-n-s'];
            }

            if (this.sortAscending) {
                return ['ui-icon-caret-1-n'];
            } else {
                return ['ui-icon-caret-1-s'];
            }
        },
        newPortfolio() {
            newPortfolioModal();
            this.redrawTabs = true;
        },
        isDebugEnabled() {
            return isDebugModeEnabled();
        },
        setSort(sortKey) {
            if (this.sortKey === sortKey) {
                this.sortAscending = !this.sortAscending;
            } else {
                this.sortAscending = true;
                this.sortKey = sortKey;
            }
        }
    },
    created() {
        // hack to link non-esm reactive vue portfolio with esm vue app
        document.addEventListener('portfolioSavedNonEsm', (e) => {
            this.portfolios = JSON.parse(JSON.stringify(reactivePortfolio));
        });
    },
    updated() {
        if (this.redrawTabs) {
            this.redrawTabs = false;
            try {
                $("#vue-container").tabs('refresh');
            } catch (e) {
                // do nothing
            }
        }
    },
    mounted() {
        // initial jquery tabs update
        $("#vue-container").tabs({
            beforeActivate: function(event, ui) {
                if (ui.newPanel.attr('id') === 'newportfolio'){
                    event.preventDefault();
                }
            }
        });

        const portfolio_uid = decodeURI(window.location.hash.substring(1));
        if (portfolio_uid) {
            const index = this.portfolios.findIndex(portfolio => portfolio.uid === portfolio_uid);
            this.selectedPortfolioIdx = index >= 0 ? index : 0;
        }
    },
    unmounted() {
        document.removeEventListener('portfolioSavedNonEsm');
    }
}
</script>

<style scoped>

</style>