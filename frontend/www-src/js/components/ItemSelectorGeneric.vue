<template>
    <q-select
        outlined
        dense
        v-model="selectedOption"
        use-input
        fill-input
        hide-selected
        input-debounce="0"
        :options="options"
        option-value="key"
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
                <q-item-section side v-if="scope.opt.thumbnailUrl != null">
                    <div class="thumbnail-container">
                        <img :src="scope.opt.thumbnailUrl" class="item-lookup-thumbnail">
                    </div>
                </q-item-section>
                <q-item-section>
                    <q-item-label v-html="annotateNameInList(scope.opt.name, lastUserInputText)" />
                    <q-item-label caption style="margin-top: 0" v-if="scope.opt.caption != null">
                        {{ scope.opt.caption }}
                    </q-item-label>
                </q-item-section>
            </q-item>
        </template>
    </q-select>
</template>

<script>

export default {
    name: "ItemSelectorGeneric",
    props: ['availableOptions'], // {name, key, thumbnailUrl (optional), caption (optional)}
    emits: ['selectionChanged'],
    data() {
        return {
            selectedOption: null,
            options: [],
            lastUserInputText: ''
        }
    },
    computed: {
        sortedOptions() {
            let values = Object.values(this.availableOptions);
            values.sort((a, b) => a.name > b.name ? 1 : -1);
            return values;
        },
    },
    methods: {
        filterFn(val, update) {
            this.lastUserInputText = val;

            update(() => {
                this.options = this.sortedOptions.filter(x => {
                    const nameNormalized = x.name.toLowerCase();
                    const searchTermNormalized = val.toLowerCase();

                    if (nameNormalized.indexOf(searchTermNormalized) > -1) return true;

                    const firstLettersOfItemName = nameNormalized
                        .split(/[ |-]+/)
                        .filter(x => x.trim().length > 0)
                        .map(x => x[0]);

                    return firstLettersOfItemName.join('').includes(searchTermNormalized);
                });
            },
                ref => {
                    // if (this.selectedOption) return; // prevent display bug if opening popup menu when item is already selected
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
            this.selectedOption = null;
            this.$refs.selector.showPopup();
        },
        annotateNameInList(name, searchTerm) {
            const nameNormalized = name.toLowerCase();
            const searchTermNormalized = searchTerm.toLowerCase();

            const simpleSearchResult = nameNormalized.indexOf(searchTermNormalized);
            if (simpleSearchResult > -1) {
                return [
                    name.slice(0, simpleSearchResult),
                    `<span class="search-text-highlight">`,
                    name.slice(simpleSearchResult, simpleSearchResult + searchTerm.length),
                    `</span>`,
                    name.slice(simpleSearchResult + searchTerm.length)
                ].join('');
            }

            const firstLettersOfItemName = nameNormalized
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
        selectedOption(newValue, oldValue) {
            if (newValue == null) return;
            if (oldValue?.key === newValue.key) return;

            this.$emit('selectionChanged', newValue.key);
            this.$refs.selector.blur(); // doesn't work on mobile chrome/firefox
        }
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