export const eventCard = (id, serverIsActive) => ({
    id,
    isActive: serverIsActive,

    init() {
        window.addEventListener('simulation:event-changed', (e) => {
            if (e.detail?.id === this.id) {
                this.isActive = e.detail.isActive;
            }
        });
    },
});

export const cityEvents = () => ({
    editOpen: false,
    dayNightEditOpen: false,

    allFunctions: [],

    creating: {
        name: '',
        description: '',
        event_type: 'one-off',
        recurring_frequency_value: 1,
        recurring_frequency_unit: 'day',
        recurring_time_slots: [],
        one_off_duration_value: 1,
        one_off_duration_unit: 'day',
    },

    editing: {
        id: null,
        name: '',
        description: '',
        event_type: 'one-off',
        recurring_frequency_value: 1,
        recurring_frequency_unit: 'day',
        recurring_time_slots: [],
        one_off_duration_value: 1,
        one_off_duration_unit: 'day',
        linkedFunctions: [],
    },

    dayNightEditing: {
        id: null,
        day_duration_value: 8,
        day_duration_unit: 'hour',
        night_duration_value: 8,
        night_duration_unit: 'hour',
        dayLinkedFunctions: [],
        nightLinkedFunctions: [],
    },

    init() {
        this.allFunctions = window.cityFunctionsData ?? [];

        this._resizeSlots(this.creating.recurring_time_slots, this.creating.recurring_frequency_value);

        this.$watch('creating.recurring_frequency_value', (val) => {
            this._resizeSlots(this.creating.recurring_time_slots, parseInt(val) || 0);
        });

        this.$watch('editing.recurring_frequency_value', (val) => {
            this._resizeSlots(this.editing.recurring_time_slots, parseInt(val) || 0);
        });
    },

    _resizeSlots(slots, n) {
        while (slots.length < n) slots.push({ start: '', end: '', week_day: null, month_date: null });
        if (slots.length > n) slots.splice(n);
    },

    openEdit(event) {
        const slots = Array.isArray(event.recurring_time_slots) ? event.recurring_time_slots : [];
        const freq  = event.recurring_frequency_value ?? slots.length ?? 1;

        this.editing = {
            id: event.id,
            name: event.name ?? '',
            description: event.description ?? '',
            event_type: event.event_type ?? 'one-off',
            recurring_frequency_value: freq,
            recurring_frequency_unit: event.recurring_frequency_unit ?? 'day',
            recurring_time_slots: slots.length > 0
                ? slots.map(s => ({ start: s.start ?? '', end: s.end ?? '', week_day: s.week_day ?? null, month_date: s.month_date ?? null }))
                : Array.from({ length: freq }, () => ({ start: '', end: '', week_day: null, month_date: null })),
            one_off_duration_value: event.one_off_duration_value ?? 1,
            one_off_duration_unit: event.one_off_duration_unit ?? 'day',
            linkedFunctions: event.linkedFunctions ?? [],
        };

        this.editOpen = true;
    },

    closeEdit() {
        this.editOpen = false;
    },

    openDayNightEdit(event) {
        this.dayNightEditing = {
            id: event.id,
            day_duration_value: event.day_duration_value ?? 8,
            day_duration_unit: event.day_duration_unit ?? 'hour',
            night_duration_value: event.night_duration_value ?? 8,
            night_duration_unit: event.night_duration_unit ?? 'hour',
            dayLinkedFunctions: event.dayLinkedFunctions ?? [],
            nightLinkedFunctions: event.nightLinkedFunctions ?? [],
        };

        this.dayNightEditOpen = true;
    },

    closeDayNightEdit() {
        this.dayNightEditOpen = false;
    },
});
