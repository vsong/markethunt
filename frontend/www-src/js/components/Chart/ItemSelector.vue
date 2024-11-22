<template>
    <q-select
        outlined
        dense
        v-model="selectedItem"
        use-input
        fill-input
        hide-selected
        input-debounce="0"
        :options="options"
        option-value="item_id"
        option-label="name"
        options-dense
        @filter="filterFn"
        @clear="cleared"
        ref="selector"
    >
        <template #prepend>
            <q-icon name="search" />
        </template>
        <template #no-option>
            <q-item>
                <q-item-section class="text-grey">
                    No results
                </q-item-section>
            </q-item>
        </template>
        <template #option="scope">
            <q-item v-bind="scope.itemProps" style="padding-top: 4px; padding-bottom: 4px">
                <q-item-section side>
                    <div class="thumbnail-container">
                        <img :src="`https://cdn.markethunt.win/thumbnail/item/${scope.opt.item_id}.gif`" class="item-lookup-thumbnail">
                    </div>
                </q-item-section>
                <q-item-section>
                    <q-item-label v-html="annotateNameInList(scope.opt.name, lastUserInputText)" />
                    <q-item-label caption style="margin-top: 0">
                        {{ scope.opt.latest_price.toLocaleString() }}g
                    </q-item-label>
                </q-item-section>
            </q-item>
        </template>
    </q-select>
    <div
        style="display: none;"
        id="selected-item"
        :data-item-id="mostRecentNotNullSelectedItem.item_id"
        :data-item-name="mostRecentNotNullSelectedItem.name"
    ></div>
</template>

<script>

export default {
    name: "ItemSelector",
    data() {
        return {
            selectedItem: itemData[window.initialItemId ?? 926],
            options: [],
            lastUserInputText: '',
            mostRecentNotNullSelectedItem: itemData[window.initialItemId ?? 926]
        }
    },
    computed: {
        filteredItemData() {
            let values = Object.values(itemData);
            values.sort((a, b) => a.name > b.name ? 1 : -1);
            return values.filter(i => i.currently_tradeable);
        },
    },
    methods: {
        filterFn(val, update) {
            this.lastUserInputText = val;

            update(() => {
                this.options = this.filteredItemData.filter(x => {
                    const itemNameNormalized = x.name.toLowerCase();
                    const searchTermNormalized = val.toLowerCase();

                    if (itemNameNormalized.indexOf(searchTermNormalized) > -1) return true;

                    const firstLettersOfItemName = itemNameNormalized
                        .split(/[ |-]+/)
                        .filter(x => x.trim().length > 0)
                        .map(x => x[0]);

                    return firstLettersOfItemName.join('').includes(searchTermNormalized);
                });
            },
                ref => {
                    // if (this.selectedItem) return; // prevent display bug if opening popup menu when item is already selected
                    //
                    // ref.setOptionIndex(-1);
                    // ref.moveOptionSelection(1, true);

                    // From Quasar example docs
                    if (val !== '' && ref.options.length > 0 && ref.getOptionIndex() === -1) {
                        ref.moveOptionSelection(1, true) // focus the first selectable option and do not update the input-value
                        ref.toggleOption(ref.options[ref.optionIndex], true) // toggle the focused option
                    }
                });
        },
        cleared() {
            this.selectedItem = null;
            this.$refs.selector.showPopup();
        },
        annotateNameInList(name, searchTerm) {
            const itemNameNormalized = name.toLowerCase();
            const searchTermNormalized = searchTerm.toLowerCase();

            const simpleSearchResult = itemNameNormalized.indexOf(searchTermNormalized);
            if (simpleSearchResult > -1) {
                return [
                    name.slice(0, simpleSearchResult),
                    `<span class="search-text-highlight">`,
                    name.slice(simpleSearchResult, simpleSearchResult + searchTerm.length),
                    `</span>`,
                    name.slice(simpleSearchResult + searchTerm.length)
                ].join('');
            }

            const firstLettersOfItemName = itemNameNormalized
                .split(/[ |-]+/)
                .filter(x => x.trim().length > 0)
                .map(x => x[0]);

            const lettersToSkipWhenAnnotating = firstLettersOfItemName.join('').indexOf(searchTermNormalized);

            if (lettersToSkipWhenAnnotating < 0) {
                console.warn(`Could not determine starting position of first letter highlighting (this should never happen). ${{lettersToSkipWhenAnnotating}}`);
                return name;
            }

            let firstLetterEndIndices = [0];

            let matches = [...name.matchAll(/[ |-]+/g)];
            matches.forEach((match) => {
                firstLetterEndIndices.push(match.index + match[0].length);
            });

            let nameAnnotated = name;

            for (let i = searchTerm.length - 1; i >= 0; i--) {
                const spanInsertStartIndex = firstLetterEndIndices[i + lettersToSkipWhenAnnotating];
                const spanInsertEndIndex = firstLetterEndIndices[i + lettersToSkipWhenAnnotating] + 1;
                nameAnnotated = [
                    nameAnnotated.slice(0, spanInsertStartIndex),
                    `<span class="search-text-highlight">`,
                    nameAnnotated.slice(spanInsertStartIndex, spanInsertEndIndex),
                    `</span>`,
                    nameAnnotated.slice(spanInsertEndIndex)
                ].join('');
            }

            return nameAnnotated;
        }
    },
    watch: {
        selectedItem(newValue, oldValue) {
            if (newValue == null) return;
            if (oldValue?.item_id === newValue.item_id) return;

            this.mostRecentNotNullSelectedItem = newValue;
            handleChartSubmit(newValue.item_id, newValue.name);
            this.$refs.selector.blur(); // doesn't work on mobile chrome/firefox
            document.getElementById('stock-data-checkbox').checked = false;
        }
    },
    created() {
        document.addEventListener('setChart', (e) => {
            this.selectedItem = itemData[e.detail];
        });
    },
    unmounted() {
        document.removeEventListener('setChart');
    }
}
</script>

<style scoped>
:deep(.material-icons) {
    text-shadow: none;
}
.thumbnail-container {
    width: 32px;
    height: 32px;
}
.item-lookup-thumbnail {
    width: 100%;
    height: 100%;
    object-fit: contain;
}
:deep(.search-text-highlight) {
    background-color: #ceffb1;
}
</style>