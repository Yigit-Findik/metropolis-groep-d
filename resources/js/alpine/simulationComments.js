const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

export const simulationComments = () => ({
    comments: [],
    newBody: '',
    loading: true,
    submitting: false,
    error: null,

    async init() {
        await this.load();
    },

    async load() {
        this.loading = true;
        try {
            const res = await fetch('/comments', { headers: { Accept: 'application/json' } });
            if (res.ok) this.comments = await res.json();
        } finally {
            this.loading = false;
        }
    },

    async submit() {
        const body = this.newBody.trim();
        if (!body || this.submitting) return;

        this.submitting = true;
        this.error = null;

        try {
            const res = await fetch('/comments', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf(),
                    Accept: 'application/json',
                },
                body: JSON.stringify({ body }),
            });

            if (res.ok) {
                const comment = await res.json();
                this.comments.unshift(comment);
                this.newBody = '';
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

    async remove(id) {
        try {
            const res = await fetch(`/comments/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrf(),
                    Accept: 'application/json',
                },
            });

            if (res.ok) {
                this.comments = this.comments.filter(c => c.id !== id);
            }
        } catch {}
    },
});
