<template>
    <div id="modal-dialog">
        <slot></slot>
    </div>
</template>

<script>
export default {
    name: "ModalTemplate",
    props: {
        'title': {
            type: String,
            default: ''
        },
        'positiveButtonText': {
            type: String,
            default: null
        },
        'negativeButtonText': {
            type: String,
            default: null
        },
        'width': {
            default: 300
        }
    },
    emits: ['modalClosed', 'positiveButtonClicked', 'negativeButtonClicked'],
    data() {
        return {
            dialog: null,
        }
    },
    methods: {
        closeModal() {
            this.dialog.dialog("close");
            this.dialog.dialog('destroy');
            this.$emit('modalClosed');
        }
    },
    mounted() {
        let buttons = [];

        if (this.positiveButtonText) {
            buttons.push({
                text: this.positiveButtonText,
                id: "modal-positive-button",
                click: () => this.$emit('positiveButtonClicked'),
            });
        }

        if (this.negativeButtonText) {
            buttons.push({
                text: this.negativeButtonText,
                id: "modal-negative-button",
                click: () => this.$emit('negativeButtonClicked'),
            });
        }

        this.dialog = $("#modal-dialog").dialog({
            dialogClass: 'themed-titlebar',
            width: this.width,
            title: this.title,
            autoOpen: false,
            modal: true,
            buttons: buttons,
            open: (event, ui) => {
                // click outside to close
                $('.ui-widget-overlay').bind('click', () => {
                    this.closeModal();
                });

                // override button classes
                $('#modal-positive-button').removeClass().addClass("pure-button pure-button-primary");
                $('#modal-negative-button').removeClass().addClass("pure-button");
            }
        });

        this.dialog.dialog("open");
    }
}
</script>

<style scoped>

</style>