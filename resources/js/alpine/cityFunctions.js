// Manages state for the create and edit modals on the city functions page
export const cityFunctions = () => ({
    open: false,     // Create modal visibility
    editOpen: false, // Edit modal visibility
    editing: {},     // Holds the function data currently being edited

    // Populates the editing object with the chosen function's data and opens the edit modal
    openEdit(fn) {
        this.editing = fn;
        this.editOpen = true;
    },
});
