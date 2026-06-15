const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

export const gridCellSuggestions = (userRole) => ({
    suggestions: [],
    loading: true,
    submitting: false,
    error: null,

    // Form state
    selectedCellId: '',
    description: '',

    userRole,

    async init() {
        await this.load();
    },

    async load() {
        this.loading = true;
        try {
            const res = await fetch('/suggestions', { headers: { Accept: 'application/json' } });
            if (res.ok) {
                this.suggestions = await res.json();
                this.updateGridIndicators();
            }
        } finally {
            this.loading = false;
        }
    },

    // Highlight cells on the grid that have pending suggestions using Tailwind ring classes
    updateGridIndicators() {
        document.querySelectorAll('[data-grid-cell]').forEach(cell => {
            cell.classList.remove('ring-2', 'ring-orange-500', 'ring-offset-1');
        });

        const pendingCellIds = this.suggestions
            .filter(s => s.status === 'pending')
            .map(s => s.cell_id);

        pendingCellIds.forEach(cellId => {
            const el = document.querySelector(`[data-cell-id="${cellId}"]`);
            if (el) el.classList.add('ring-2', 'ring-orange-500', 'ring-offset-1');
        });
    },

    async submit() {
        if (!this.selectedCellId || !this.description.trim() || this.submitting) return;

        this.submitting = true;
        this.error = null;

        try {
            const res = await fetch('/suggestions', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf(),
                    Accept: 'application/json',
                },
                body: JSON.stringify({
                    cell_id:     this.selectedCellId,
                    description: this.description.trim(),
                }),
            });

            if (res.ok) {
                const suggestion = await res.json();
                this.suggestions.unshift(suggestion);
                this.description = '';
                this.selectedCellId = '';
                this.updateGridIndicators();
            } else {
                const data = await res.json().catch(() => ({}));
                this.error = data.message ?? 'Something went wrong. Please try again.';
            }
        } catch {
            this.error = 'Could not reach the server. Check your connection.';
        } finally {
            this.submitting = false;
        }
    },

    async setStatus(id, status) {
        try {
            const res = await fetch(`/suggestions/${id}/status`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf(),
                    Accept: 'application/json',
                },
                body: JSON.stringify({ status }),
            });

            if (res.ok) {
                const updated = await res.json();
                const suggestion = this.suggestions.find(s => s.id === id);
                if (suggestion) suggestion.status = updated.status;
                this.updateGridIndicators();
            }
        } catch {}
    },

    async remove(id) {
        try {
            const res = await fetch(`/suggestions/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrf(),
                    Accept: 'application/json',
                },
            });

            if (res.ok) {
                this.suggestions = this.suggestions.filter(s => s.id !== id);
                this.updateGridIndicators();
            }
        } catch {}
    },

    statusLabel(status) {
        return { pending: 'Pending', accepted: 'Accepted', rejected: 'Rejected' }[status] ?? status;
    },

    statusClass(status) {
        return {
            pending:  'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300',
            accepted: 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300',
            rejected: 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300',
        }[status] ?? '';
    },

    canChangeStatus() {
        return this.userRole === 'City planner' || this.userRole === 'Administrator';
    },

    canCreate() {
        return this.userRole === 'Policy maker' || this.userRole === 'Administrator';
    },
});
