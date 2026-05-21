// Simple non-blocking confirmation modal
export const confirmModal = () => ({
    show: false,
    message: '',
    confirmCallback: null,

    open(message, callback) {
        this.message = message;
        this.confirmCallback = callback;
        this.show = true;
    },

    confirm() {
        this.show = false;
        if (this.confirmCallback) this.confirmCallback();
    },

    cancel() {
        this.show = false;
    },
});
