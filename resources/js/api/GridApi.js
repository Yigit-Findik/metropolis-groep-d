/**
 * Handles all HTTP communication with the grid backend.
 * Reads the CSRF token once from the DOM on construction.
 */
export class GridApi {
    #csrfToken;

    constructor() {
        // Grab the CSRF token Laravel injects into the page <head>
        this.#csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    }

    // Assigns a city function to a grid cell
    async assign(cellId, functionId) {
        const response = await fetch(`/grid/${cellId}/assign`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.#csrfToken,
            },
            body: JSON.stringify({ function_id: parseInt(functionId) }),
        });

        if (!response.ok) {
            const data = await response.json().catch(() => ({}));
            throw new Error(data.message || `Assign failed: ${response.status}`);
        }

        return response.json();
    }

    // Removes the city function from a grid cell
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

    // Reverts the last grid action
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

    // Fetches the current total and per-category QoL scores
    async getQolScore() {
        const response = await fetch('/grid/qol-score');

        if (!response.ok) throw new Error(`QoL score fetch failed: ${response.status}`);

        return response.json();
    }

    // Fetches valid and invalid cells for placing a function based on adjacency rules
    async getValidCells(functionId) {
        const response = await fetch(`/grid/valid-cells?function_id=${functionId}`);

        if (!response.ok) throw new Error(`Valid cells fetch failed: ${response.status}`);

        return response.json();
    }
}
