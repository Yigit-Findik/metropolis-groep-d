const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

export const dayNightCycleTimer = (dayDurationSeconds, nightDurationSeconds, eventId, initialPhase, initialPhaseStartedAt) => ({
    label: '',
    colorClass: '',
    _remaining: 0,
    _switchTriggered: false,
    _phase: initialPhase,
    _phaseStartedAt: initialPhaseStartedAt,
    _interval: null,

    init() {
        const key          = 'sim_dnc_' + eventId;
        const stored       = JSON.parse(localStorage.getItem(key) || 'null');
        const phaseDuration = (this._phase === 'day' ? dayDurationSeconds : nightDurationSeconds) * 1_000;

        if (stored && stored.phaseStartedAt === this._phaseStartedAt && stored.phase === this._phase) {
            this._remaining = stored.remaining;
        } else {
            this._remaining = phaseDuration;
            this._save(key);
        }

        this.update();

        const startInterval = () => {
            if (this._interval) clearInterval(this._interval);
            const multiplier  = Number(localStorage.getItem('sim_multiplier')   || 1);
            const unitSeconds = Number(localStorage.getItem('sim_unit_seconds') || 1);
            this._interval = setInterval(() => {
                if (localStorage.getItem('sim_paused') === 'false') {
                    this._remaining -= unitSeconds * 1_000;
                    this._save(key);
                    this.update();
                }

                if (this._remaining <= 0 && !this._switchTriggered) {
                    this._switchTriggered = true;
                    localStorage.removeItem(key);
                    fetch('/events/' + eventId + '/switch-phase', {
                        method:  'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken(),
                            'Accept':       'application/json',
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ from_phase: this._phase }),
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.skipped) {
                            this._switchTriggered = false;
                            return;
                        }
                        const newPhase    = data.phase;
                        const newDuration = (newPhase === 'day' ? dayDurationSeconds : nightDurationSeconds) * 1_000;
                        this._phase          = newPhase;
                        this._phaseStartedAt = Math.floor(Date.now() / 1_000);
                        this._remaining      = newDuration;
                        this._switchTriggered = false;
                        this._save(key);
                        this.update();
                    });
                }
            }, Math.round(1_000 / multiplier));
        };

        startInterval();
        window.addEventListener('simulation:speedchange', startInterval);
    },

    _save(key) {
        localStorage.setItem(key, JSON.stringify({
            phaseStartedAt: this._phaseStartedAt,
            phase:          this._phase,
            remaining:      this._remaining,
        }));
    },

    update() {
        const time = this.formatTime(Math.max(0, this._remaining));
        if (this._phase === 'day') {
            this.label      = 'Day phase — night begins in ' + time;
            this.colorClass = 'text-amber-600 dark:text-amber-400';
        } else {
            this.label      = 'Night phase — day begins in ' + time;
            this.colorClass = 'text-indigo-600 dark:text-indigo-400';
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
