/**
 * Inline editor for a single effect value cell in the effects table.
 * Accepts server-rendered values as arguments so the logic stays out of Blade.
 *
 * @param {number} initialValue  - The current effect score from the database
 * @param {string} updateUrl     - The route URL for saving the updated value
 * @param {string} csrfToken     - Laravel CSRF token for the POST request
 * @param {string} functionName  - Display name used in the success toast
 * @param {string} category      - The effect category being edited (e.g. 'Safety')
 */
export const effectEditor = (initialValue, updateUrl, csrfToken, functionName, category) => ({
    editing: false,
    originalValue: initialValue, // Kept so we can revert on cancel or server error
    value: initialValue,
    error: '',

    isValid() {
        return this.value >= -10 && this.value <= 10;
    },

    // Validates on each keystroke so the save button disables instantly
    validateInput() {
        this.error = '';
        if (this.value < -10 || this.value > 10) {
            this.error = 'Value must be between -10 and 10';
        }
    },

    async save() {
        this.error = '';
        if (this.value < -10 || this.value > 10) {
            this.error = 'Value must be between -10 and 10';
            return;
        }
        try {
            const response = await fetch(updateUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ category, value: this.value }),
            });

            if (!response.ok) {
                // Revert the input to the last confirmed value on server error
                this.error = 'Failed to update effect';
                this.value = this.originalValue;
                return;
            }

            // Keep the optimistic value and show a brief confirmation toast
            this.originalValue = this.value;
            this.editing = false;

            const toast = document.getElementById('effect-toast');
            if (toast) {
                toast.textContent = `${functionName} effect updated!`;
                toast.className = 'fixed bottom-6 right-6 z-50 rounded-xl px-5 py-3 text-sm font-semibold text-white shadow-lg transition-all duration-300 bg-green-500';
                setTimeout(() => { toast.className += ' hidden'; }, 3000);
            }
        } catch {
            this.error = 'An error occurred';
            this.value = this.originalValue;
        }
    },

    cancel() {
        this.value = this.originalValue;
        this.error = '';
        this.editing = false;
    },

    // Returns a Tailwind text colour class based on whether the value is positive, negative, or zero
    getColor() {
        if (this.value > 0) return 'text-green-600 dark:text-green-400';
        if (this.value < 0) return 'text-red-600 dark:text-red-400';
        return 'text-gray-500 dark:text-gray-400';
    },
});
