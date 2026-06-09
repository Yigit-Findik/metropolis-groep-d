export const simulationControls = () => ({
    paused: true,
    speed: 1,
    timer: null,

    init() {
        // Remove all old keys from previous implementations
        ['sim_freeze_offset', 'sim_paused_since', 'sim_now', 'sim_play_started_at', 'sim_base_time']
            .forEach(k => localStorage.removeItem(k));
        // Always start paused on page load
        localStorage.setItem('sim_paused', 'true');
        if (!localStorage.getItem('sim_speed')) {
            localStorage.setItem('sim_speed', '1');
        }
        this.speed = Number(localStorage.getItem('sim_speed'));
        // When leaving the Grid page, pause so Events page timers stay frozen
        window.addEventListener('beforeunload', () => {
            localStorage.setItem('sim_paused', 'true');
        });
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

    setSpeed(newSpeed) {
        localStorage.setItem('sim_speed', String(newSpeed));
        this.speed = newSpeed;
        window.dispatchEvent(new CustomEvent('simulation:speedchange', { detail: { speed: newSpeed } }));
        if (!this.paused) {
            clearInterval(this.timer);
            this.startTimer();
        }
    },

    startTimer() {
        const intervalMs = 5000 / this.speed;
        this.timer = setInterval(() => {
            window.dispatchEvent(new CustomEvent('simulation:tick'));
        }, intervalMs);
    },
});
