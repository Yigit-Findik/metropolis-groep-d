//shows a non blocking confirmation modal when a delete form is submitted
export const deleteForm = (name) => ({
    form: null,
    
    confirmAndSubmit(event) {
        event.preventDefault();
        this.form = event.target;
        const submitter = event.submitter ?? null;
        
        //find the confirmation modal component on the page
        const confirmModalEl = document.querySelector('[x-data*="confirmModal"]');
        if (confirmModalEl) {
            const confirmModal = Alpine.$data(confirmModalEl);
            confirmModal.open(
                `Delete "${name}"?`,
                () => this.form.submit(),
                submitter
            );
        }
    },
});
