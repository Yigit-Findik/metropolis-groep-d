// SIM.6 — controls for play/pause and simulation speed
export const simulationControls = () => ({
    paused: true,  // starts paused
    speed: 1,      // current speed: 1, 2, or 5
    timer: null,

    play() {
        this.paused = false;
        this.startTimer();
    },

    pause() {
        this.paused = true;
        clearInterval(this.timer);
        this.timer = null;
    },

    setSpeed(newSpeed) {
        this.speed = newSpeed;
        if (!this.paused) {
            // restart the timer with the new interval
            clearInterval(this.timer);
            this.startTimer();
        }
    },

    startTimer() {
        // 1x = every 5 seconds, 2x = every 2.5 seconds, 5x = every 1 second
        const intervalMs = 5000 / this.speed;
        this.timer = setInterval(() => {
            window.dispatchEvent(new CustomEvent('simulation:tick'));
        }, intervalMs);
    },
});
