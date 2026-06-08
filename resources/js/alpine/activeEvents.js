export const activeEvents = () => ({
    events: [],

    async init() {
        await this.fetchEvents();

        setInterval(() => {
            if (localStorage.getItem('sim_paused') === 'false') {
                const tick = Number(localStorage.getItem('sim_speed') || 1) * 1_000;
                this.events = this.events.map(e => {
                    const updated = { ...e };
                    if (updated._remainingMs  != null) updated._remainingMs  = Math.max(0, updated._remainingMs  - tick);
                    if (updated._reactivateMs != null) updated._reactivateMs = Math.max(0, updated._reactivateMs - tick);
                    this._save(updated);
                    return updated;
                });
            }
        }, 1_000);
    },

    _save(e) {
        if (e.event_type === 'recurring') {
            // Same key/format as recurringEventTimer on Events page
            localStorage.setItem('sim_evt_' + e.id, JSON.stringify({
                activatedAt:  e._activatedAt,
                expires:      e._remainingMs,
                reactivates:  e._reactivateMs,
            }));
        } else {
            // Same key/format as expiryCountdown on Events page
            localStorage.setItem('sim_evt_' + e.id + '_exp', JSON.stringify({
                activatedAt: e._activatedAt,
                remaining:   e._remainingMs,
            }));
        }
    },

    async fetchEvents() {
        try {
            const res = await fetch('/events/active');
            if (!res.ok) return;
            const raw     = await res.json();
            const playing = localStorage.getItem('sim_paused') === 'false';

            this.events = raw.map(e => {
                const activatedAt     = e.activated_at_timestamp;
                const fullRemainingMs = e.active_duration_seconds != null ? e.active_duration_seconds * 1000 : null;
                const fullCycleMs     = e.cycle_duration_seconds  != null ? e.cycle_duration_seconds  * 1000 : null;

                let remainingMs  = fullRemainingMs;
                let reactivateMs = fullCycleMs;

                if (!playing && activatedAt != null) {
                    if (e.event_type === 'recurring') {
                        const s = JSON.parse(localStorage.getItem('sim_evt_' + e.id) || 'null');
                        if (s && s.activatedAt === activatedAt) {
                            if (s.expires     != null) remainingMs  = s.expires;
                            if (s.reactivates != null) reactivateMs = s.reactivates;
                        } else {
                            localStorage.setItem('sim_evt_' + e.id, JSON.stringify({
                                activatedAt, expires: remainingMs, reactivates: reactivateMs,
                            }));
                        }
                    } else {
                        const s = JSON.parse(localStorage.getItem('sim_evt_' + e.id + '_exp') || 'null');
                        if (s && s.activatedAt === activatedAt) {
                            if (s.remaining != null) remainingMs = s.remaining;
                        } else {
                            localStorage.setItem('sim_evt_' + e.id + '_exp', JSON.stringify({
                                activatedAt, remaining: remainingMs,
                            }));
                        }
                    }
                }

                return {
                    ...e,
                    _activatedAt:  activatedAt,
                    _remainingMs:  remainingMs,
                    _reactivateMs: reactivateMs,
                };
            });
        } catch {}
    },

    formatStatus(event) {
        if (event.is_active && event._remainingMs != null) {
            return event._remainingMs <= 0 ? 'Ending...' : `Ends ${this.formatTime(event._remainingMs)}`;
        }
        if (!event.is_active && event._reactivateMs != null) {
            return event._reactivateMs <= 0 ? 'Reactivating...' : `Reactivates ${this.formatTime(event._reactivateMs)}`;
        }
        return '—';
    },

    formatTime(ms) {
        const sec  = Math.floor(ms / 1_000);
        const min  = Math.floor(sec / 60);
        const hrs  = Math.floor(min / 60);
        const days = Math.floor(hrs / 24);
        if (days > 0) return `in ${days}d ${hrs % 24}h`;
        if (hrs > 0)  return `in ${hrs}h ${min % 60}m`;
        if (min > 0)  return `in ${min}m ${sec % 60}s`;
        return `in ${sec}s`;
    },
});
