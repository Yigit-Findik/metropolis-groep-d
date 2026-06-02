//simple confirm modal
export const confirmModal = () => ({
    show: false,
    message: '',
    confirmCallback: null,
    triggerElement: null,

    open(message, callback, triggerElement = null) {
        this.message = message;
        this.confirmCallback = callback;
        this.triggerElement = triggerElement;
        this.show = true;
    },

    confirm() {
        this.show = false;
        if (this.confirmCallback) this.confirmCallback();
        this.restoreFocus();
    },

    cancel() {
        this.show = false;
        this.restoreFocus();
    },

    restoreFocus() {
        if (this.triggerElement && typeof this.triggerElement.focus === 'function') {
            this.triggerElement.focus();
        }
        this.triggerElement = null;
    },
});
