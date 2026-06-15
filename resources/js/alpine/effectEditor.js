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
    functionNameArg: functionName,
    categoryArg: category,
    _toastTimer: null,

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

    startEditing(evt) {
        this.editing = true;
        // Announce to screen readers which function and category are being edited
        const live = document.getElementById('effect-live');
        if (live) {
            live.textContent = `You are editing ${this.functionNameArg}. Effect: ${this.categoryArg}. Current value ${this.value}.`;
        }

        // Try to focus the number input inside this component so screen readers will announce its aria-label.
        try {
            // evt.currentTarget is the button; find the closest root for this x-data and then the input
            const root = evt?.currentTarget?.closest('[x-data]') || null;
            if (root) {
                // find the first number input in this component
                const input = root.querySelector('input[type="number"]');
                if (input) {
                    // small delay to allow Alpine to render the editing template
                    setTimeout(() => input.focus(), 20);
                }
            }
        } catch (e) {
            // ignore focus errors
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
                clearTimeout(this._toastTimer);
                this._toastTimer = setTimeout(() => { toast.classList.add('hidden'); }, 3000);
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
