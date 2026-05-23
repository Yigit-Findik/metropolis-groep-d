import { notify } from '../utils/notify';

// Manages state for the create and edit modals on the city functions page
export const cityFunctions = () => ({
    open: false,     // Create modal visibility
    editOpen: false, // Edit modal visibility
    editing: {},     // Holds the function data currently being edited
    allFunctions: [], // All available functions for dropdown
    newConditionTarget: null, // Target for new condition
    newConditionType: 'required', // Type for new condition
    lastFocusedElement: null,

    // Populates the editing object with the chosen function's data and opens the edit modal
    openEdit(fn, allFunctions) {
        this.lastFocusedElement = document.activeElement instanceof HTMLElement
            ? document.activeElement
            : null;
        this.editing = JSON.parse(JSON.stringify(fn));
        this.editing.conditions = fn.functionConditions || [];
        this.allFunctions = allFunctions;
        this.newConditionTarget = null;
        this.newConditionType = 'required';
        this.editOpen = true;
    },

    openCreate() {
        this.lastFocusedElement = document.activeElement instanceof HTMLElement
            ? document.activeElement
            : null;
        this.open = true;
    },

    closeCreate() {
        this.open = false;

        setTimeout(() => {
            if (this.lastFocusedElement instanceof HTMLElement && document.contains(this.lastFocusedElement)) {
                this.lastFocusedElement.focus();
            }
        }, 0);
    },

    closeEdit() {
        this.editOpen = false;

        setTimeout(() => {
            if (this.lastFocusedElement instanceof HTMLElement && document.contains(this.lastFocusedElement)) {
                this.lastFocusedElement.focus();
            }
        }, 0);
    },

    getFocusableElements(container) {
        const focusableSelector = [
            'button:not([disabled])',
            'input:not([disabled])',
            'select:not([disabled])',
            'textarea:not([disabled])',
            'a[href]',
            '[tabindex]:not([tabindex="-1"])',
        ].join(', ');

        return Array.from(container.querySelectorAll(focusableSelector))
            .filter((element) => element instanceof HTMLElement && element.offsetParent !== null);
    },

    focusFirstElement(container) {
        const focusables = this.getFocusableElements(container);
        if (focusables.length > 0) {
            focusables[0].focus();
        }
    },

    trapCreateFocus(event) {
        if (!this.open) return;

        const focusables = this.getFocusableElements(event.currentTarget);

        if (focusables.length === 0) return;

        const activeElement = document.activeElement;
        const currentIndex = focusables.indexOf(activeElement);

        event.preventDefault();

        if (event.shiftKey) {
            const previousIndex = currentIndex <= 0 ? focusables.length - 1 : currentIndex - 1;
            focusables[previousIndex].focus();
            return;
        }

        const nextIndex = currentIndex === -1 || currentIndex >= focusables.length - 1 ? 0 : currentIndex + 1;
        focusables[nextIndex].focus();
    },

    enforceCreateFocus(event, container) {
        if (!this.open || !container) return;

        if (!container.contains(event.target)) {
            this.focusFirstElement(container);
        }
    },

    trapEditFocus(event) {
        if (!this.editOpen) return;

        const focusables = this.getFocusableElements(event.currentTarget);

        if (focusables.length === 0) return;

        const activeElement = document.activeElement;
        const currentIndex = focusables.indexOf(activeElement);

        event.preventDefault();

        if (event.shiftKey) {
            const previousIndex = currentIndex <= 0 ? focusables.length - 1 : currentIndex - 1;
            focusables[previousIndex].focus();
            return;
        }

        const nextIndex = currentIndex === -1 || currentIndex >= focusables.length - 1 ? 0 : currentIndex + 1;
        focusables[nextIndex].focus();
    },

    enforceEditFocus(event, container) {
        if (!this.editOpen || !container) return;

        if (!container.contains(event.target)) {
            this.focusFirstElement(container);
        }
    },

    // Add a new condition to the editing function
    addCondition() {
        if (!this.newConditionTarget) return;
        
        const duplicate = this.editing.conditions.some(c => c.target_function_id == this.newConditionTarget);
        if (duplicate) return;
        
        this.editing.conditions.push({
            target_function_id: this.newConditionTarget,
            type: this.newConditionType,
        });
        
        this.newConditionTarget = null;
        this.newConditionType = 'required';
    },

    // Remove a condition by index
    removeCondition(index) {
        this.editing.conditions.splice(index, 1);
    },

    // Get target function name by ID
    getTargetName(targetId) {
        const fn = this.allFunctions.find(f => f.id == targetId);
        return fn ? fn.name : 'Unknown';
    },

    // Submit the edit form with all data including conditions
    async submitEdit(event) {
        event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);
        
        // Signal to the backend that conditions should always be synced,
        // even when the resulting array is empty (all rules deleted).
        formData.append('sync_conditions', '1');

        // Clear any existing conditions from the form
        Array.from(formData.keys()).forEach(key => {
            if (key.startsWith('conditions')) {
                formData.delete(key);
            }
        });

        // Add all conditions to the form data with proper structure
        this.editing.conditions.forEach((condition, index) => {
            formData.append(`conditions[${index}][target_id]`, condition.target_function_id);
            formData.append(`conditions[${index}][type]`, condition.type);
        });
        
        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });
            
            if (!response.ok) {
                const errorText = await response.text();
                console.error('Submit error:', errorText);
                notify('Error saving function');
                return;
            }
            
            // Success - close modal and reload
            this.editOpen = false;
            window.location.reload();
        } catch (error) {
            console.error('Submit error:', error);
            notify('Error saving function');
        }
    },
});
