const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';
const simPost   = (url) => fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' } });

export const recurringEventTimer = (activeDurationSeconds, cycleDurationSeconds, eventId, activatedAt) => ({
    label: '',
    colorClass: '',
    _expires: 0,
    _reactivates: 0,
    _deactivateTriggered: false,
    _reactivateTriggered: false,
    _interval: null,

    init() {
        const fullExpiresMs = activeDurationSeconds * 1000;
        const fullCycleMs   = cycleDurationSeconds  * 1000;
        const key           = 'sim_evt_' + eventId;
        const stored        = JSON.parse(localStorage.getItem(key) || 'null');

        if (stored && stored.activatedAt === activatedAt) {
            this._expires     = stored.expires;
            this._reactivates = stored.reactivates;
        } else {
            this._expires     = fullExpiresMs;
            this._reactivates = fullCycleMs;
            this._save(key);
        }

        // If already past expiry (e.g. after reload), don't fire duplicate API calls
        if (this._expires <= 0)     this._deactivateTriggered = true;
        if (this._reactivates <= 0) this._reactivateTriggered = true;

        this.update();

        const startInterval = () => {
            if (this._interval) clearInterval(this._interval);
            const multiplier  = Number(localStorage.getItem('sim_multiplier')   || 1);
            const unitSeconds = Number(localStorage.getItem('sim_unit_seconds') || 1);
            this._interval = setInterval(() => {
                if (localStorage.getItem('sim_paused') === 'false') {
                    this._expires    -= unitSeconds * 1_000;
                    this._reactivates -= unitSeconds * 1_000;
                    this._save(key);
                    this.update();
                }

                // Sim cycle ended — deactivate in DB
                if (this._expires <= 0 && !this._deactivateTriggered) {
                    this._deactivateTriggered = true;
                    simPost('/events/' + eventId + '/deactivate').then(() => {
                        window.dispatchEvent(new CustomEvent('simulation:event-changed', { detail: { id: eventId, isActive: false } }));
                    });
                }

                // Sim cycle fully elapsed — reactivate in DB and restart this timer in-place
                if (this._reactivates <= 0 && !this._reactivateTriggered) {
                    this._reactivateTriggered = true;
                    localStorage.removeItem(key);
                    simPost('/events/' + eventId + '/activate').then(() => {
                        this._expires             = fullExpiresMs;
                        this._reactivates         = fullCycleMs;
                        this._deactivateTriggered = false;
                        this._reactivateTriggered = false;
                        this._save(key);
                        this.update();
                        window.dispatchEvent(new CustomEvent('simulation:event-changed', { detail: { id: eventId, isActive: true } }));
                    });
                }
            }, Math.round(1_000 / multiplier));
        };

        startInterval();
        window.addEventListener('simulation:speedchange', startInterval);
    },

    _save(key) {
        localStorage.setItem(key, JSON.stringify({
            activatedAt,
            expires:     this._expires,
            reactivates: this._reactivates,
        }));
    },

    update() {
        if (this._expires > 0) {
            this.label      = `Cycle ends in ${this.formatTime(this._expires)}`;
            this.colorClass = 'text-emerald-600 dark:text-emerald-400';
        } else if (this._reactivates > 0) {
            this.label      = `Reactivates in ${this.formatTime(this._reactivates)}`;
            this.colorClass = 'text-amber-600 dark:text-amber-400';
        } else {
            this.label      = 'Reactivating...';
            this.colorClass = 'text-amber-600 dark:text-amber-400';
        }
    },

    formatTime(ms) {
        const sec  = Math.floor(ms / 1_000);
        const min  = Math.floor(sec / 60);
        const hrs  = Math.floor(min / 60);
        const days = Math.floor(hrs / 24);
        if (days > 0) return `${days}d ${hrs % 24}h`;
        if (hrs > 0)  return `${hrs}h ${min % 60}m`;
        if (min > 0)  return `${min}m ${sec % 60}s`;
        return `${sec}s`;
    },
});
