export const deleteForm = (name) => ({
    confirmAndSubmit(event) {
        if (!window.confirm(`Delete ${name}?`)) {
            event.preventDefault();
        }
    },
});
