// Shows a browser confirm dialog before allowing a delete form to submit
export const deleteForm = (name) => ({
    confirmAndSubmit(event) {
        if (!window.confirm(`Delete ${name}?`)) {
            event.preventDefault(); // Cancel the submit if the user clicks Cancel
        }
    },
});
