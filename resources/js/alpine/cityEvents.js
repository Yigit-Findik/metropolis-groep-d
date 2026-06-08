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
        recurring_active_duration_value: 1,
        recurring_active_duration_unit: 'hour',
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
        recurring_active_duration_value: 1,
        recurring_active_duration_unit: 'hour',
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
    },

    openEdit(event) {
        this.editing = {
            id: event.id,
            name: event.name ?? '',
            description: event.description ?? '',
            event_type: event.event_type ?? 'one-off',
            recurring_frequency_value: event.recurring_frequency_value ?? 1,
            recurring_frequency_unit: event.recurring_frequency_unit ?? 'day',
            recurring_active_duration_value: event.recurring_active_duration_value ?? 1,
            recurring_active_duration_unit: event.recurring_active_duration_unit ?? 'hour',
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
