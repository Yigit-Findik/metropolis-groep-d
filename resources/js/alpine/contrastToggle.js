export const contrastToggle = () => ({
    active: false,

    init() {
        this.active = document.documentElement.classList.contains('high-contrast');
    },

    toggle() {
        this.active = !this.active;
        document.documentElement.classList.toggle('high-contrast', this.active);
        localStorage.setItem('high-contrast', this.active ? 'true' : 'false');
    },
});
