// Manages state for the create and edit modals on the city functions page
export const cityFunctions = () => ({
    open: false,     // Create modal visibility
    editOpen: false, // Edit modal visibility
    editing: {},     // Holds the function data currently being edited
    allFunctions: [], // All available functions for dropdown
    newConditionTarget: null, // Target for new condition
    newConditionType: 'required', // Type for new condition

    // Populates the editing object with the chosen function's data and opens the edit modal
    openEdit(fn, allFunctions) {
        this.editing = JSON.parse(JSON.stringify(fn));
        this.editing.conditions = fn.functionConditions || [];
        this.allFunctions = allFunctions;
        this.newConditionTarget = null;
        this.newConditionType = 'required';
        this.editOpen = true;
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
                alert('Error saving function');
                return;
            }
            
            // Success - close modal and reload
            this.editOpen = false;
            window.location.reload();
        } catch (error) {
            console.error('Submit error:', error);
            alert('Error saving function');
        }
    },
});
