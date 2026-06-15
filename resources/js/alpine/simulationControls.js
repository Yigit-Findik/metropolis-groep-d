export const simulationControls = () => ({
    paused: true,
    speed: 1,
    multiplier: 1,
    unitSeconds: 1,
    timer: null,
    simTime: '00:00',
    _clockInterval: null,
    skipAmount: 1,
    skipUnit: 60,

    init() {
        // Remove all old keys from previous implementations
        ['sim_freeze_offset', 'sim_paused_since', 'sim_now', 'sim_play_started_at', 'sim_base_time',
         'sim_custom_value', 'sim_custom_unit']
            .forEach(k => localStorage.removeItem(k));
        // Always start paused on page load
        localStorage.setItem('sim_paused', 'true');
        this.multiplier  = Number(localStorage.getItem('sim_multiplier')   || 1);
        this.unitSeconds = Number(localStorage.getItem('sim_unit_seconds') || 1);
        // Derive speed from the two parts so they stay in sync
        const combined = this.multiplier * this.unitSeconds;
        localStorage.setItem('sim_speed', String(combined));
        this.speed = combined;
        if (!localStorage.getItem('sim_clock_ms')) {
            const now = new Date();
            const msFromMidnight = (now.getHours() * 3600 + now.getMinutes() * 60 + now.getSeconds()) * 1_000;
            localStorage.setItem('sim_clock_ms', String(msFromMidnight));
        }
        if (!localStorage.getItem('sim_week_day'))   localStorage.setItem('sim_week_day',   '1');
        if (!localStorage.getItem('sim_month_date')) localStorage.setItem('sim_month_date', '1');
        this.simTime = this._formatClock(Number(localStorage.getItem('sim_clock_ms')));
        window.addEventListener('beforeunload', () => {
            localStorage.setItem('sim_paused', 'true');
        });
        this._startClock();
    },

    play() {
        localStorage.setItem('sim_paused', 'false');
        this.paused = false;
        window.dispatchEvent(new CustomEvent('simulation:play'));
        this.startTimer();
    },

    pause() {
        localStorage.setItem('sim_paused', 'true');
        this.paused = true;
        clearInterval(this.timer);
        this.timer = null;
        window.dispatchEvent(new CustomEvent('simulation:pause'));
    },

    setMultiplier(n) {
        this.multiplier = n;
        localStorage.setItem('sim_multiplier', String(n));
        this.setSpeed(n * this.unitSeconds);
    },

    setUnit(n) {
        this.unitSeconds = n;
        localStorage.setItem('sim_unit_seconds', String(n));
        this.setSpeed(this.multiplier * n);
    },

    setSpeed(newSpeed) {
        localStorage.setItem('sim_speed', String(newSpeed));
        this.speed = newSpeed;
        window.dispatchEvent(new CustomEvent('simulation:speedchange', { detail: { speed: newSpeed } }));
        if (!this.paused) {
            clearInterval(this.timer);
            this.startTimer();
        }
        // Restart clock so interval matches new speed
        clearInterval(this._clockInterval);
        this._clockInterval = null;
        this._startClock();
    },

    startTimer() {
        const intervalMs = 5000 / this.speed;
        this.timer = setInterval(() => {
            window.dispatchEvent(new CustomEvent('simulation:tick'));
        }, intervalMs);
    },

    _startClock() {
        if (this._clockInterval) return;
        // Multiplier controls frequency (2x → every 500ms), unit controls step size (hours → 3600s per tick)
        const intervalMs = Math.round(1_000 / this.multiplier);
        this._clockInterval = setInterval(() => {
            if (localStorage.getItem('sim_paused') === 'false') {
                const tick    = this.unitSeconds * 1_000;
                const DAY_MS  = 24 * 3600 * 1_000;
                const prevMs  = Number(localStorage.getItem('sim_clock_ms') || 0);
                const clockMs = (prevMs + tick) % DAY_MS;
                localStorage.setItem('sim_clock_ms', String(clockMs));
                this.simTime = this._formatClock(clockMs);

                const daysElapsed = Math.floor((prevMs + tick) / DAY_MS);
                if (daysElapsed > 0) {
                    const wd = Number(localStorage.getItem('sim_week_day') || 1);
                    localStorage.setItem('sim_week_day', String(((wd - 1 + daysElapsed) % 7) + 1));
                    const md = Number(localStorage.getItem('sim_month_date') || 1);
                    localStorage.setItem('sim_month_date', String(((md - 1 + daysElapsed) % 31) + 1));
                }
            }
        }, intervalMs);
    },

    skip() {
        const addMs  = this.skipAmount * this.skipUnit * 1_000;
        const DAY_MS = 24 * 3600 * 1_000;
        const prevMs = Number(localStorage.getItem('sim_clock_ms') || 0);
        const newRawMs = prevMs + addMs;
        const clockMs  = newRawMs % DAY_MS;
        localStorage.setItem('sim_clock_ms', String(clockMs));
        this.simTime = this._formatClock(clockMs);

        const daysElapsed = Math.floor(newRawMs / DAY_MS);
        if (daysElapsed > 0) {
            const wd = Number(localStorage.getItem('sim_week_day') || 1);
            localStorage.setItem('sim_week_day', String(((wd - 1 + daysElapsed) % 7) + 1));
            const md = Number(localStorage.getItem('sim_month_date') || 1);
            localStorage.setItem('sim_month_date', String(((md - 1 + daysElapsed) % 31) + 1));
        }

        window.dispatchEvent(new CustomEvent('simulation:skip', { detail: { addMs } }));

        const announcer = document.getElementById('grid-a11y-announcer');
        if (announcer) announcer.textContent = `Simulation time skipped to ${this.simTime}`;
    },

    _formatClock(ms) {
        const totalSec = Math.floor(ms / 1_000);
        const h = Math.floor(totalSec / 3600);
        const m = Math.floor((totalSec % 3600) / 60);
        return String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0');
    },
});
