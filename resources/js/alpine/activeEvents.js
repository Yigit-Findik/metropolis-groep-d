export const activeEvents = () => ({
    events: [],
    now: Date.now(),

    async init() {
        await this.fetchEvents();
        // Refresh events on each simulation tick so pause/speed changes take effect
        window.addEventListener('simulation:tick', () => this.fetchEvents());
        // Update the clock every second so countdowns stay smooth
        setInterval(() => { this.now = Date.now(); }, 1_000);
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
