// Controls the cell size slider and tracks whether the viewport is desktop width
export const gridZoom = () => ({
    size: 128, // Default cell size in pixels (desktop)
    mobileSize: 64, // Will be measured from actual cell width on init
    isDesktop: window.matchMedia('(min-width: 1024px)').matches,
    get effectiveGridSize() {
        return this.isDesktop ? this.size : this.mobileSize;
    },
    init() {
        const mq = window.matchMedia('(min-width: 1024px)');
        mq.addEventListener('change', (e) => { this.isDesktop = e.matches; });
        this.$nextTick(() => this._syncMobileSize());
        window.addEventListener('resize', () => this._syncMobileSize(), { passive: true });
    },
    _syncMobileSize() {
        if (this.isDesktop) return;
        const cell = document.querySelector('[data-grid-cell]');
        if (cell) this.mobileSize = Math.round(cell.getBoundingClientRect().width);
    },
});
