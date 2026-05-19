export const gridZoom = () => ({
    size: 96,
    isDesktop: window.matchMedia('(min-width: 1024px)').matches,
    init() {
        const mq = window.matchMedia('(min-width: 1024px)');
        mq.addEventListener('change', (e) => { this.isDesktop = e.matches; });
    },
});
