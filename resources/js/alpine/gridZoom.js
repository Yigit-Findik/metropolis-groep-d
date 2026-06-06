// Controls the cell size slider and tracks whether the viewport is desktop width
export const gridZoom = () => ({
    size: 128, // Default cell size in pixels
    isDesktop: window.matchMedia('(min-width: 1024px)').matches,
    init() {
        // Keep isDesktop in sync when the window is resized past the breakpoint
        const mq = window.matchMedia('(min-width: 1024px)');
        mq.addEventListener('change', (e) => { this.isDesktop = e.matches; });
    },
});
