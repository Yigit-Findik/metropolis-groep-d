export const autoHideToast = () => ({
    show: true,
    init() {
        setTimeout(() => { this.show = false; }, 3000);
    },
});
