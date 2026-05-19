export const cityFunctions = () => ({
    open: false,
    editOpen: false,
    editing: {},
    openEdit(fn) {
        this.editing = fn;
        this.editOpen = true;
    },
});
