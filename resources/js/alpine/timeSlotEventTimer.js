const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';
const simPost   = (url) => fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' } });

const DAY_NAMES = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

const ordinal = (n) => {
    const s = ['th', 'st', 'nd', 'rd'];
    const v = n % 100;
    return n + (s[(v - 20) % 10] || s[v] || s[0]);
};

/**
 * slots: [{start_seconds, end_seconds, week_day?, month_date?}, ...]
 * frequencyUnit: 'hour' | 'day' | 'week' | 'month'
 */
export const timeSlotEventTimer = (slots, eventId, initialIsActive, frequencyUnit = 'day') => ({
    label: '',
    colorClass: '',
    _isActive: initialIsActive,
    _activateTriggered: false,
    _deactivateTriggered: false,

    init() {
        this.update();
        setInterval(() => {
            if (localStorage.getItem('sim_paused') === 'false') {
                this.update();
            }
        }, 1_000);
    },

    update() {
        if (!slots || slots.length === 0) {
            this.label      = 'No time slots configured';
            this.colorClass = 'text-slate-400';
            return;
        }

        const clockMs    = Number(localStorage.getItem('sim_clock_ms') || 0);
        const currentSec = Math.floor(clockMs / 1_000);

        let activeSlot = null;
        let waitSec    = null;
        let nextLabel  = '';
        let remainingSec = 0;

        if (frequencyUnit === 'week') {
            const weekDay        = Number(localStorage.getItem('sim_week_day') || 1);
            const WEEK_SEC       = 7 * 86400;
            const currentWeekSec = (weekDay - 1) * 86400 + currentSec;

            activeSlot = slots.find(s => {
                if (!s.week_day) return false;
                const slotStart = (s.week_day - 1) * 86400 + s.start_seconds;
                const slotEnd   = (s.week_day - 1) * 86400 + s.end_seconds;
                return currentWeekSec >= slotStart && currentWeekSec < slotEnd;
            });

            if (activeSlot) {
                const slotEnd = (activeSlot.week_day - 1) * 86400 + activeSlot.end_seconds;
                remainingSec  = slotEnd - currentWeekSec;
            } else {
                const best = slots
                    .filter(s => s.week_day)
                    .map(s => {
                        const slotStart = (s.week_day - 1) * 86400 + s.start_seconds;
                        const wait = slotStart > currentWeekSec
                            ? slotStart - currentWeekSec
                            : WEEK_SEC - currentWeekSec + slotStart;
                        return { s, wait };
                    })
                    .sort((a, b) => a.wait - b.wait)[0];

                if (best) {
                    waitSec   = best.wait;
                    nextLabel = `${DAY_NAMES[best.s.week_day - 1]} ${this._secsToTime(best.s.start_seconds)}`;
                }
            }

        } else if (frequencyUnit === 'month') {
            const monthDate        = Number(localStorage.getItem('sim_month_date') || 1);
            const MONTH_SEC        = 31 * 86400;
            const currentMonthSec  = (monthDate - 1) * 86400 + currentSec;

            activeSlot = slots.find(s => {
                if (!s.month_date) return false;
                const slotStart = (s.month_date - 1) * 86400 + s.start_seconds;
                const slotEnd   = (s.month_date - 1) * 86400 + s.end_seconds;
                return currentMonthSec >= slotStart && currentMonthSec < slotEnd;
            });

            if (activeSlot) {
                const slotEnd = (activeSlot.month_date - 1) * 86400 + activeSlot.end_seconds;
                remainingSec  = slotEnd - currentMonthSec;
            } else {
                const best = slots
                    .filter(s => s.month_date)
                    .map(s => {
                        const slotStart = (s.month_date - 1) * 86400 + s.start_seconds;
                        const wait = slotStart > currentMonthSec
                            ? slotStart - currentMonthSec
                            : MONTH_SEC - currentMonthSec + slotStart;
                        return { s, wait };
                    })
                    .sort((a, b) => a.wait - b.wait)[0];

                if (best) {
                    waitSec   = best.wait;
                    nextLabel = `${ordinal(best.s.month_date)} ${this._secsToTime(best.s.start_seconds)}`;
                }
            }

        } else {
            activeSlot = slots.find(s => currentSec >= s.start_seconds && currentSec < s.end_seconds);

            if (activeSlot) {
                remainingSec = activeSlot.end_seconds - currentSec;
            } else {
                const best = slots
                    .map(s => ({ s, wait: s.start_seconds > currentSec ? s.start_seconds - currentSec : 24 * 3600 - currentSec + s.start_seconds }))
                    .sort((a, b) => a.wait - b.wait)[0];

                if (best) {
                    waitSec   = best.wait;
                    nextLabel = this._secsToTime(best.s.start_seconds);
                }
            }
        }

        if (activeSlot) {
            this.label      = `Active — ends at ${this._secsToTime(activeSlot.end_seconds)} (${this._formatTime(remainingSec * 1_000)})`;
            this.colorClass = 'text-emerald-600 dark:text-emerald-400';

            if (!this._isActive && !this._activateTriggered) {
                this._activateTriggered   = true;
                this._deactivateTriggered = false;
                this._isActive = true;
                simPost('/events/' + eventId + '/activate').then(() => {
                    window.dispatchEvent(new CustomEvent('simulation:event-changed', { detail: { id: eventId, isActive: true } }));
                });
            }
        } else if (waitSec !== null) {
            this.label      = `Next: ${nextLabel} (${this._formatTime(waitSec * 1_000)})`;
            this.colorClass = 'text-amber-600 dark:text-amber-400';

            if (this._isActive && !this._deactivateTriggered) {
                this._deactivateTriggered = true;
                this._activateTriggered   = false;
                this._isActive = false;
                simPost('/events/' + eventId + '/deactivate').then(() => {
                    window.dispatchEvent(new CustomEvent('simulation:event-changed', { detail: { id: eventId, isActive: false } }));
                });
            }
        } else {
            this.label      = 'No upcoming slots';
            this.colorClass = 'text-slate-400';
        }
    },

    _secsToTime(totalSec) {
        const h = Math.floor(totalSec / 3600);
        const m = Math.floor((totalSec % 3600) / 60);
        return String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0');
    },

    _formatTime(ms) {
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
