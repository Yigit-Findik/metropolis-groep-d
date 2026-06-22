const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';
const simPost   = (url) => fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' } });

// Maps function ID -> net modifiers summed from all currently active events
const _modifiersByFunction = new Map();
let _activeEventsCache = [];

function _rebuildModifiers(events) {
    _modifiersByFunction.clear();
    _activeEventsCache = events;
    for (const event of events) {
        if (!event.is_active) continue;
        for (const fn of (event.linked_functions ?? [])) {
            const id = Number(fn.function_id);
            const cur = _modifiersByFunction.get(id) ?? { safety: 0, recreation: 0, environmentQuality: 0, facilities: 0, mobility: 0 };
            cur.safety             += fn.safety_modifier              ?? 0;
            cur.recreation         += fn.recreation_modifier          ?? 0;
            cur.environmentQuality += fn.environment_quality_modifier ?? 0;
            cur.facilities         += fn.facilities_modifier          ?? 0;
            cur.mobility           += fn.mobility_modifier            ?? 0;
            _modifiersByFunction.set(id, cur);
        }
    }
}

/** Returns the net event modifiers for a given function ID, or null if none. */
export function getEventModifiersForFunction(functionId) {
    return _modifiersByFunction.get(Number(functionId)) ?? null;
}

/** Returns all currently active events that affect a given function ID, with their per-stat modifiers. */
export function getActiveEventsForFunction(functionId) {
    const id = Number(functionId);
    return _activeEventsCache
        .filter(e => e.is_active && (e.linked_functions ?? []).some(fn => Number(fn.function_id) === id))
        .map(e => {
            const fn = e.linked_functions.find(fn => Number(fn.function_id) === id);
            return {
                name:                e.name,
                safety:              fn.safety_modifier              ?? 0,
                recreation:          fn.recreation_modifier          ?? 0,
                environmentQuality:  fn.environment_quality_modifier ?? 0,
                facilities:          fn.facilities_modifier          ?? 0,
                mobility:            fn.mobility_modifier            ?? 0,
            };
        });
}

export const activeEvents = () => ({
    events: [],
    _pendingDeactivations: new Set(),
    _pendingReactivations: new Set(),
    _pendingSlotChanges: new Set(),
    _interval: null,

    async init() {
        await this.fetchEvents();

        const startInterval = () => {
            if (this._interval) clearInterval(this._interval);
            const multiplier  = Number(localStorage.getItem('sim_multiplier')   || 1);
            const unitSeconds = Number(localStorage.getItem('sim_unit_seconds') || 1);
            const tick        = unitSeconds * 1_000;
            this._interval = setInterval(() => {
                if (localStorage.getItem('sim_paused') === 'false') {
                    this._tick(tick);
                }
            }, Math.round(1_000 / multiplier));
        };

        startInterval();
        window.addEventListener('simulation:speedchange', startInterval);
        window.addEventListener('simulation:skip', (e) => this._tick(e.detail.addMs));
    },

    _tick(tickMs) {
        this.events = this.events.map(e => {
            const updated        = { ...e };
            const prevRemaining  = updated._remainingMs;
            const prevReactivate = updated._reactivateMs;

            if (!updated._hasTimeSlot) {
                if (updated._remainingMs  != null) updated._remainingMs  = Math.max(0, updated._remainingMs  - tickMs);
                if (updated._reactivateMs != null) updated._reactivateMs = Math.max(0, updated._reactivateMs - tickMs);
            }
            this._save(updated);

            if (updated.event_type === 'day-night' && updated.is_active &&
                prevRemaining != null && prevRemaining > 0 && updated._remainingMs === 0 &&
                !updated._phaseSwitchTriggered) {
                updated._phaseSwitchTriggered = true;
                const fromPhase = updated._currentPhase;
                localStorage.removeItem('sim_dnc_' + updated.id);
                fetch('/events/' + updated.id + '/switch-phase', {
                    method:  'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json', 'Content-Type': 'application/json' },
                    body: JSON.stringify({ from_phase: fromPhase }),
                }).then(() => this.fetchEvents());
            }

            if (updated.event_type === 'recurring' && !updated._hasTimeSlot &&
                updated.is_active && updated._remainingMs != null &&
                prevRemaining > 0 && updated._remainingMs <= 0 &&
                !this._pendingDeactivations.has(updated.id)) {
                this._pendingDeactivations.add(updated.id);
                simPost('/events/' + updated.id + '/sim-deactivate').then(() => {
                    this._pendingDeactivations.delete(updated.id);
                    this.fetchEvents();
                    window.dispatchEvent(new CustomEvent('simulation:event-changed', { detail: { id: updated.id, isActive: false } }));
                    this._announce(`${updated.name} event has ended`);
                });
            }

            if (updated.event_type === 'recurring' && !updated._hasTimeSlot &&
                !updated.is_active && prevReactivate != null && prevReactivate > 0 &&
                updated._reactivateMs <= 0 && !this._pendingReactivations.has(updated.id)) {
                this._pendingReactivations.add(updated.id);
                simPost('/events/' + updated.id + '/sim-reactivate').then(() => {
                    this._pendingReactivations.delete(updated.id);
                    this.fetchEvents();
                    window.dispatchEvent(new CustomEvent('simulation:event-changed', { detail: { id: updated.id, isActive: true } }));
                    this._announce(`${updated.name} event is now active`);
                });
            }

            if (updated.event_type === 'one-off' && updated.is_active &&
                updated._remainingMs != null && prevRemaining > 0 && updated._remainingMs <= 0 &&
                !this._pendingDeactivations.has(updated.id)) {
                this._pendingDeactivations.add(updated.id);
                simPost('/events/' + updated.id + '/deactivate').then(() => {
                    this._pendingDeactivations.delete(updated.id);
                    this.fetchEvents();
                    window.dispatchEvent(new CustomEvent('simulation:event-changed', { detail: { id: updated.id, isActive: false } }));
                    this._announce(`${updated.name} event has ended`);
                });
            }

            if (updated._hasTimeSlot) {
                const inSlot = this._isInSlot(updated);
                if (inSlot && !updated.is_active && !this._pendingSlotChanges.has(updated.id)) {
                    this._pendingSlotChanges.add(updated.id);
                    simPost('/events/' + updated.id + '/sim-reactivate').then(() => {
                        window.dispatchEvent(new CustomEvent('simulation:event-changed', { detail: { id: updated.id, isActive: true } }));
                        this._announce(`${updated.name} event is now active`);
                        this.fetchEvents().then(() => this._pendingSlotChanges.delete(updated.id));
                    });
                } else if (!inSlot && updated.is_active && !this._pendingSlotChanges.has(updated.id)) {
                    this._pendingSlotChanges.add(updated.id);
                    simPost('/events/' + updated.id + '/sim-deactivate').then(() => {
                        window.dispatchEvent(new CustomEvent('simulation:event-changed', { detail: { id: updated.id, isActive: false } }));
                        this._announce(`${updated.name} event has ended`);
                        this.fetchEvents().then(() => this._pendingSlotChanges.delete(updated.id));
                    });
                }
            }

            return updated;
        });
    },

    _save(e) {
        if (e.event_type === 'day-night') {
            localStorage.setItem('sim_dnc_' + e.id, JSON.stringify({
                phaseStartedAt: e._phaseStartedAt,
                phase:          e._currentPhase,
                remaining:      e._remainingMs,
            }));
        } else if (e.event_type === 'recurring') {
            localStorage.setItem('sim_evt_' + e.id, JSON.stringify({
                activatedAt:  e._activatedAt,
                expires:      e._remainingMs,
                reactivates:  e._reactivateMs,
            }));
        } else {
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
                if (e.event_type === 'day-night') {
                    const phase          = e.current_phase;
                    const phaseStartedAt = e.phase_started_at_timestamp;
                    const phaseDuration  = phase === 'day' ? e.day_duration_seconds : e.night_duration_seconds;
                    const fullPhaseMs    = phaseDuration != null ? phaseDuration * 1000 : null;
                    const key            = 'sim_dnc_' + e.id;

                    let remainingMs = fullPhaseMs;

                    const stored = JSON.parse(localStorage.getItem(key) || 'null');
                    if (stored && stored.phaseStartedAt === phaseStartedAt && stored.phase === phase) {
                        remainingMs = stored.remaining;
                    } else if (!playing) {
                        localStorage.setItem(key, JSON.stringify({
                            phaseStartedAt, phase, remaining: remainingMs,
                        }));
                    }

                    return {
                        ...e,
                        _activatedAt:        phaseStartedAt,
                        _phaseStartedAt:     phaseStartedAt,
                        _currentPhase:       phase,
                        _remainingMs:        remainingMs,
                        _reactivateMs:       null,
                        _phaseSwitchTriggered: false,
                    };
                }

                const activatedAt     = e.activated_at_timestamp;
                const fullRemainingMs = e.active_duration_seconds != null ? e.active_duration_seconds * 1000 : null;
                const fullCycleMs     = e.cycle_duration_seconds  != null ? e.cycle_duration_seconds  * 1000 : null;

                let remainingMs  = fullRemainingMs;
                let reactivateMs = fullCycleMs;

                if (activatedAt != null) {
                    if (e.event_type === 'recurring') {
                        const s = JSON.parse(localStorage.getItem('sim_evt_' + e.id) || 'null');
                        if (s && s.activatedAt === activatedAt) {
                            if (s.expires     != null) remainingMs  = s.expires;
                            if (s.reactivates != null) reactivateMs = s.reactivates;
                        } else if (!playing) {
                            localStorage.setItem('sim_evt_' + e.id, JSON.stringify({
                                activatedAt, expires: remainingMs, reactivates: reactivateMs,
                            }));
                        }
                    } else {
                        const s = JSON.parse(localStorage.getItem('sim_evt_' + e.id + '_exp') || 'null');
                        if (s && s.activatedAt === activatedAt) {
                            if (s.remaining != null) remainingMs = s.remaining;
                        } else if (!playing) {
                            localStorage.setItem('sim_evt_' + e.id + '_exp', JSON.stringify({
                                activatedAt, remaining: remainingMs,
                            }));
                        }
                    }
                }

                const hasTimeSlot = Array.isArray(e.time_slots) && e.time_slots.length > 0;

                // If the timer is already at zero but the server still says active,
                // trigger deactivation immediately (handles page-reload after expiry)
                if (e.event_type === 'recurring' && !hasTimeSlot &&
                    remainingMs != null && remainingMs <= 0 && e.is_active &&
                    !this._pendingDeactivations.has(e.id)) {
                    this._pendingDeactivations.add(e.id);
                    simPost('/events/' + e.id + '/sim-deactivate')
                        .then(() => {
                            this._pendingDeactivations.delete(e.id);
                            this.fetchEvents();
                            window.dispatchEvent(new CustomEvent('simulation:event-changed', { detail: { id: e.id, isActive: false } }));
                        });
                }

                if (e.event_type === 'one-off' &&
                    remainingMs != null && remainingMs <= 0 && e.is_active &&
                    !this._pendingDeactivations.has(e.id)) {
                    this._pendingDeactivations.add(e.id);
                    simPost('/events/' + e.id + '/deactivate')
                        .then(() => {
                            this._pendingDeactivations.delete(e.id);
                            this.fetchEvents();
                            window.dispatchEvent(new CustomEvent('simulation:event-changed', { detail: { id: e.id, isActive: false } }));
                        });
                }

                return {
                    ...e,
                    _activatedAt:  activatedAt,
                    _remainingMs:  remainingMs,
                    _reactivateMs: reactivateMs,
                    _hasTimeSlot:  hasTimeSlot,
                };
            });

            _rebuildModifiers(this.events);
        } catch {}
    },

    _t2s(s) {
        return String(Math.floor(s / 3600)).padStart(2, '0') + ':' + String(Math.floor((s % 3600) / 60)).padStart(2, '0');
    },

    _slotStatus(slots, nowSec, periodSec, startOf, endOf, labelOf) {
        const active = slots.find(s => nowSec >= startOf(s) && nowSec < endOf(s));
        if (active) {
            const remaining = endOf(active) - nowSec;
            return `Active — ends ${this._t2s(active.end_seconds)} (${this.formatTime(remaining * 1_000)})`;
        }
        const next = slots
            .map(s => ({ s, wait: startOf(s) > nowSec ? startOf(s) - nowSec : periodSec - nowSec + startOf(s) }))
            .sort((a, b) => a.wait - b.wait)[0];
        if (!next) return '—';
        return `Next: ${labelOf(next.s)}${this._t2s(next.s.start_seconds)} (${this.formatTime(next.wait * 1_000)})`;
    },

    _isInSlot(event) {
        const slots      = event.time_slots;
        const unit       = event.recurring_frequency_unit;
        const clockMs    = Number(localStorage.getItem('sim_clock_ms') || 0);
        const currentSec = Math.floor(clockMs / 1_000);

        if (unit === 'week') {
            const weekDay        = Number(localStorage.getItem('sim_week_day') || 1);
            const currentWeekSec = (weekDay - 1) * 86400 + currentSec;
            return slots.some(s => s.week_day &&
                currentWeekSec >= (s.week_day - 1) * 86400 + s.start_seconds &&
                currentWeekSec <  (s.week_day - 1) * 86400 + s.end_seconds);
        }
        if (unit === 'month') {
            const monthDate       = Number(localStorage.getItem('sim_month_date') || 1);
            const currentMonthSec = (monthDate - 1) * 86400 + currentSec;
            return slots.some(s => s.month_date &&
                currentMonthSec >= (s.month_date - 1) * 86400 + s.start_seconds &&
                currentMonthSec <  (s.month_date - 1) * 86400 + s.end_seconds);
        }
        return slots.some(s => currentSec >= s.start_seconds && currentSec < s.end_seconds);
    },

    formatStatus(event) {
        if (event._hasTimeSlot) {
            const clockMs    = Number(localStorage.getItem('sim_clock_ms') || 0);
            const currentSec = Math.floor(clockMs / 1_000);
            const slots      = event.time_slots;
            const unit       = event.recurring_frequency_unit;

            if (unit === 'week') {
                const weekDay   = Number(localStorage.getItem('sim_week_day') || 1);
                const nowSec    = (weekDay - 1) * 86400 + currentSec;
                const DAY_NAMES = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                return this._slotStatus(
                    slots.filter(s => s.week_day), nowSec, 7 * 86400,
                    s => (s.week_day - 1) * 86400 + s.start_seconds,
                    s => (s.week_day - 1) * 86400 + s.end_seconds,
                    s => DAY_NAMES[s.week_day - 1] + ' ',
                );
            }

            if (unit === 'month') {
                const monthDate = Number(localStorage.getItem('sim_month_date') || 1);
                const nowSec    = (monthDate - 1) * 86400 + currentSec;
                const ordinal   = n => { const v = n % 100; return n + (['th','st','nd','rd'][(v - 20) % 10] || ['th','st','nd','rd'][v] || 'th'); };
                return this._slotStatus(
                    slots.filter(s => s.month_date), nowSec, 31 * 86400,
                    s => (s.month_date - 1) * 86400 + s.start_seconds,
                    s => (s.month_date - 1) * 86400 + s.end_seconds,
                    s => ordinal(s.month_date) + ' ',
                );
            }

            return this._slotStatus(
                slots, currentSec, 24 * 3600,
                s => s.start_seconds,
                s => s.end_seconds,
                () => '',
            );
        }

        if (event.event_type === 'day-night') {
            const phase = event._currentPhase;
            if (!phase) return 'Inactive';
            const time = event._remainingMs > 0 ? this.formatTime(event._remainingMs) : '';
            if (phase === 'day') {
                return time ? `Day — night ${time}` : 'Switching...';
            }
            return time ? `Night — day ${time}` : 'Switching...';
        }

        if (event.is_active && event._remainingMs != null) {
            return event._remainingMs <= 0 ? 'Ending...' : `Ends ${this.formatTime(event._remainingMs)}`;
        }
        if (!event.is_active && event._reactivateMs != null) {
            return event._reactivateMs <= 0 ? 'Reactivating...' : `Reactivates ${this.formatTime(event._reactivateMs)}`;
        }
        return '—';
    },

    progressPercent(event) {
        if (event._hasTimeSlot) {
            const clockMs    = Number(localStorage.getItem('sim_clock_ms') || 0);
            const currentSec = Math.floor(clockMs / 1_000);
            const slots      = event.time_slots;
            const unit       = event.recurring_frequency_unit;

            let nowSec, startOf, endOf, periodSec;
            if (unit === 'week') {
                const weekDay = Number(localStorage.getItem('sim_week_day') || 1);
                nowSec    = (weekDay - 1) * 86400 + currentSec;
                startOf   = s => (s.week_day   - 1) * 86400 + s.start_seconds;
                endOf     = s => (s.week_day   - 1) * 86400 + s.end_seconds;
                periodSec = 7 * 86400;
            } else if (unit === 'month') {
                const monthDate = Number(localStorage.getItem('sim_month_date') || 1);
                nowSec    = (monthDate - 1) * 86400 + currentSec;
                startOf   = s => (s.month_date - 1) * 86400 + s.start_seconds;
                endOf     = s => (s.month_date - 1) * 86400 + s.end_seconds;
                periodSec = 31 * 86400;
            } else {
                nowSec    = currentSec;
                startOf   = s => s.start_seconds;
                endOf     = s => s.end_seconds;
                periodSec = 24 * 3600;
            }

            if (event.is_active) {
                const active = slots.find(s => nowSec >= startOf(s) && nowSec < endOf(s));
                if (!active) return null;
                const duration = endOf(active) - startOf(active);
                if (duration <= 0) return null;
                return Math.min(100, Math.max(0, ((nowSec - startOf(active)) / duration) * 100));
            }

            // Inactive: progress through the gap between last slot end and next slot start
            const nextSlot = slots
                .map(s => ({ s, wait: startOf(s) > nowSec ? startOf(s) - nowSec : periodSec - nowSec + startOf(s) }))
                .sort((a, b) => a.wait - b.wait)[0];
            if (!nextSlot) return null;

            const prevEnd = slots
                .map(s => ({ elapsed: endOf(s) <= nowSec ? nowSec - endOf(s) : nowSec + periodSec - endOf(s) }))
                .sort((a, b) => a.elapsed - b.elapsed)[0];
            if (!prevEnd) return null;

            const gapSec = prevEnd.elapsed + nextSlot.wait;
            if (gapSec <= 0) return null;
            return Math.min(100, Math.max(0, (prevEnd.elapsed / gapSec) * 100));
        }

        if (event.event_type === 'day-night') {
            const phaseDuration = event._currentPhase === 'day'
                ? event.day_duration_seconds
                : event.night_duration_seconds;
            if (!phaseDuration || event._remainingMs == null) return null;
            return Math.min(100, Math.max(0, (1 - event._remainingMs / (phaseDuration * 1000)) * 100));
        }

        if (event.is_active && event._remainingMs != null && event.active_duration_seconds) {
            return Math.min(100, Math.max(0, (1 - event._remainingMs / (event.active_duration_seconds * 1000)) * 100));
        }

        if (!event.is_active && event._reactivateMs != null && event.cycle_duration_seconds && event.active_duration_seconds) {
            const cooldownMs = (event.cycle_duration_seconds - event.active_duration_seconds) * 1000;
            if (cooldownMs <= 0) return null;
            return Math.min(100, Math.max(0, (1 - event._reactivateMs / cooldownMs) * 100));
        }

        return null;
    },

    _announce(msg) {
        const el = document.getElementById('grid-a11y-announcer');
        if (!el) return;
        el.textContent = '';
        setTimeout(() => { el.textContent = msg; }, 50);
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
