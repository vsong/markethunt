<template>
    <ModalTemplate
        ref="modal"
        :title="`Delete all ${item.name} positions?`"
        positive-button-text="Delete All"
        negative-button-text="Cancel"
        @modalClosed="$emit('closed')"
        @positiveButtonClicked="deletePositions(); $refs.modal.closeModal()"
        @negativeButtonClicked="$refs.modal.closeModal()"
    >
        <p>You have {{ item.positions.length }} portfolio entries for this item. Are you sure you want to delete them?</p>
    </ModalTemplate>
</template>

<script>
import ModalTemplate from "../ModalTemplate.vue";

export default {
    name: "DeletePortfolioItemWarning",
    components: {ModalTemplate},
    props: ['portfolio', 'item'],
    emits: ['closed'],
    methods: {
        deletePositions() {
            this.item.positions.forEach(position => {
                removePosition(this.portfolio.uid, position.uid);
            });
        }
    }
}
</script>

<style scoped>

</style>