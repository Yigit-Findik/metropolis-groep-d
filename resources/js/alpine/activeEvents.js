export const activeEvents = () => ({
    events: [],
    now: Number(localStorage.getItem('sim_now') || Date.now()),
    simPaused: true,
    simSpeed: 1,

    async init() {
        await this.fetchEvents();
        window.addEventListener('simulation:tick',        () => this.fetchEvents());
        setInterval(() => this.fetchEvents(), 30_000);
        window.addEventListener('simulation:play',        () => { this.simPaused = false; });
        window.addEventListener('simulation:pause',       () => { this.simPaused = true; });
        window.addEventListener('simulation:speedchange', (e) => { this.simSpeed = e.detail.speed; });
        // Advance clock at simulation speed and save to localStorage so it persists across page navigations.
        setInterval(() => {
            if (!this.simPaused) {
                this.now += 1000 * this.simSpeed;
                localStorage.setItem('sim_now', String(this.now));
            }
        }, 1_000);
    },

    async fetchEvents() {
        try {
            const res = await fetch('/events/active');
            if (res.ok) {
                this.events = await res.json();
            }
        } catch {
            // Silently ignore network errors; the panel stays stale rather than crashing.
        }
    },

    formatStatus(event) {
        if (event.is_active && event.expires_at) {
            const ms = new Date(event.expires_at) - this.now;
            return ms <= 0 ? 'Ending...' : `Ends ${this.formatTime(ms)}`;
        }
        if (!event.is_active && event.next_activation_at) {
            const ms = new Date(event.next_activation_at) - this.now;
            return ms <= 0 ? 'Reactivating...' : `Reactivates ${this.formatTime(ms)}`;
        }
        return '—';
    },

    formatTime(ms) {
        const sec = Math.floor(ms / 1_000);
        const min = Math.floor(sec / 60);
        const hrs = Math.floor(min / 60);
        const days = Math.floor(hrs / 24);
        if (days > 0) return `in ${days}d ${hrs % 24}h`;
        if (hrs > 0)  return `in ${hrs}h ${min % 60}m`;
        if (min > 0)  return `in ${min}m ${sec % 60}s`;
        return `in ${sec}s`;
    },
});
