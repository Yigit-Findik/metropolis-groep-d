//shows a non blocking confirmation modal when a delete form is submitted
export const deleteForm = (name) => ({
    form: null,
    
    confirmAndSubmit(event) {
        event.preventDefault();
        this.form = event.target;
        
        //find the confirmation modal component on the page
        const confirmModalEl = document.querySelector('[x-data*="confirmModal"]');
        if (confirmModalEl && confirmModalEl.__x_dataStack) {
            const confirmModal = confirmModalEl.__x_dataStack[0];
            confirmModal.open(
                `Delete "${name}"?`,
                () => this.form.submit()
            );
        }
    },
});
