const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

export const dayNightCycleTimer = (dayDurationSeconds, nightDurationSeconds, eventId, currentPhase, phaseStartedAt) => ({
    label: '',
    colorClass: '',
    _remaining: 0,
    _switchTriggered: false,

    init() {
        const key          = 'sim_dnc_' + eventId;
        const stored       = JSON.parse(localStorage.getItem(key) || 'null');
        const phaseDuration = (currentPhase === 'day' ? dayDurationSeconds : nightDurationSeconds) * 1_000;

        if (stored && stored.phaseStartedAt === phaseStartedAt && stored.phase === currentPhase) {
            this._remaining = stored.remaining;
        } else {
            this._remaining = phaseDuration;
            this._save(key, currentPhase, phaseStartedAt);
        }

        this.update(currentPhase);

        setInterval(() => {
            if (localStorage.getItem('sim_paused') === 'false') {
                const tick       = Number(localStorage.getItem('sim_speed') || 1) * 1_000;
                this._remaining -= tick;
                this._save(key, currentPhase, phaseStartedAt);
                this.update(currentPhase);
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
                    body: JSON.stringify({ from_phase: currentPhase }),
                }).finally(() => window.location.reload());
            }
        }, 1_000);
    },

    _save(key, phase, startedAt) {
        localStorage.setItem(key, JSON.stringify({
            phaseStartedAt: startedAt,
            phase,
            remaining: this._remaining,
        }));
    },

    update(phase) {
        const time = this.formatTime(Math.max(0, this._remaining));
        if (phase === 'day') {
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
