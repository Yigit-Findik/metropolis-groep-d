// Handles the full cycle of a recurring event in one component:
// while the active window is open  → green "Cycle ends in X"
// while waiting for the next cycle → amber "Reactivates in X"
// When the reactivation moment arrives, triggers server-side processing then reloads.
export const recurringEventTimer = (expiresAtIso, nextActivationAtIso) => ({
    label: '',
    colorClass: '',
    _expiresAt: new Date(expiresAtIso).getTime(),
    _nextActivationAt: new Date(nextActivationAtIso).getTime(),
    _reloadTriggered: false,

    init() {
        this.update();
        // Display is static — only check for auto-reload when cycle completes
        setInterval(() => {
            const now = Number(localStorage.getItem('sim_now') || Date.now());
            const reactivateMs = this._nextActivationAt - now;
            if (reactivateMs <= 0 && !this._reloadTriggered) {
                this._reloadTriggered = true;
                fetch('/events/active').finally(() => window.location.reload());
            }
        }, 1_000);
    },

    update() {
        const now = Number(localStorage.getItem('sim_now') || Date.now());
        const activeMs = this._expiresAt - now;

        if (activeMs > 0) {
            this.label = `Cycle ends in ${this.formatTime(activeMs)}`;
            this.colorClass = 'text-emerald-600 dark:text-emerald-400';
            return;
        }

        const reactivateMs = this._nextActivationAt - now;

        if (reactivateMs > 0) {
            this.label = `Reactivates in ${this.formatTime(reactivateMs)}`;
            this.colorClass = 'text-amber-600 dark:text-amber-400';
            return;
        }

        // Reactivation moment has arrived — tell the server, then reload the page.
        this.label = 'Reactivating...';
        this.colorClass = 'text-amber-600 dark:text-amber-400';

        if (!this._reloadTriggered) {
            this._reloadTriggered = true;
            fetch('/events/active')
                .finally(() => window.location.reload());
        }
    },

    formatTime(ms) {
        const sec = Math.floor(ms / 1_000);
        const min = Math.floor(sec / 60);
        const hrs = Math.floor(min / 60);
        const days = Math.floor(hrs / 24);
        if (days > 0) return `${days}d ${hrs % 24}h`;
        if (hrs > 0)  return `${hrs}h ${min % 60}m`;
        if (min > 0)  return `${min}m ${sec % 60}s`;
        return `${sec}s`;
    },
});
