const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';
const simPost   = (url) => fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' } });

export const expiryCountdown = (durationSeconds, mode, eventId, activatedAt) => ({
    label: '',
    _remaining: 0,
    _deactivateTriggered: false,
    _interval: null,

    init() {
        const fullMs = durationSeconds * 1000;
        const key    = 'sim_evt_' + eventId + '_exp';
        const stored = JSON.parse(localStorage.getItem(key) || 'null');

        if (stored && stored.activatedAt === activatedAt) {
            this._remaining = stored.remaining;
        } else {
            this._remaining = fullMs;
            localStorage.setItem(key, JSON.stringify({ remaining: fullMs, activatedAt }));
        }

        if (this._remaining <= 0 && !this._deactivateTriggered) {
            this._deactivateTriggered = true;
            localStorage.removeItem(key);
            simPost('/events/' + eventId + '/deactivate');
        }

        this.update();

        const startInterval = () => {
            if (this._interval) clearInterval(this._interval);
            const multiplier  = Number(localStorage.getItem('sim_multiplier')   || 1);
            const unitSeconds = Number(localStorage.getItem('sim_unit_seconds') || 1);
            this._interval = setInterval(() => {
                if (localStorage.getItem('sim_paused') === 'false') {
                    this._remaining -= unitSeconds * 1_000;
                    localStorage.setItem(key, JSON.stringify({ remaining: this._remaining, activatedAt }));
                    this.update();
                }

                // One-off event expired in sim time — deactivate in DB
                if (this._remaining <= 0 && !this._deactivateTriggered) {
                    this._deactivateTriggered = true;
                    localStorage.removeItem(key);
                    simPost('/events/' + eventId + '/deactivate');
                }
            }, Math.round(1_000 / multiplier));
        };

        startInterval();
        window.addEventListener('simulation:speedchange', startInterval);
    },

    update() {
        const diffMs = this._remaining;

        if (diffMs <= 0) {
            this.label = 'Ending...';
            return;
        }

        const sec  = Math.floor(diffMs / 1_000);
        const min  = Math.floor(sec / 60);
        const hrs  = Math.floor(min / 60);
        const days = Math.floor(hrs / 24);
        let t;
        if (days > 0)      t = `${days}d ${hrs % 24}h`;
        else if (hrs > 0)  t = `${hrs}h ${min % 60}m`;
        else if (min > 0)  t = `${min}m ${sec % 60}s`;
        else               t = `${sec}s`;

        this.label = `Expires in ${t}`;
    },
});
