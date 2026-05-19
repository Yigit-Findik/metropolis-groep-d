/**
 * Handles all HTTP communication with the grid backend.
 * Reads the CSRF token once from the DOM on construction.
 */
export class GridApi {
    #csrfToken;

    constructor() {
        this.#csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    }

    async assign(cellId, functionId) {
        const response = await fetch(`/grid/${cellId}/assign`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.#csrfToken,
            },
            body: JSON.stringify({ function_id: parseInt(functionId) }),
        });

        if (!response.ok) throw new Error(`Assign failed: ${response.status}`);

        return response.json();
    }

    async remove(cellId) {
        const response = await fetch(`/grid/${cellId}/remove`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.#csrfToken,
            },
        });

        if (!response.ok) throw new Error(`Remove failed: ${response.status}`);

        return response.json();
    }

    async undo() {
        const response = await fetch('/grid/undo', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.#csrfToken,
            },
        });

        if (!response.ok) throw new Error(`Undo failed: ${response.status}`);

        return response.json();
    }

    async getQolScore() {
        const response = await fetch('/grid/qol-score');

        if (!response.ok) throw new Error(`QoL score fetch failed: ${response.status}`);

        return response.json();
    }
}
