// Shows a toast notification that automatically disappears after 3 seconds
export const autoHideToast = () => ({
    show: true,
    init() {
        setTimeout(() => { this.show = false; }, 3000);
    },
});
