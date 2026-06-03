export const cityEvents = () => ({
    editOpen: false,

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
});
