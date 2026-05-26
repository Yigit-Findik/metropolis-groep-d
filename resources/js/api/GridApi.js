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

    async #readJsonResponse(response, actionLabel) {
        const contentType = response.headers.get('content-type') || '';
        const bodyText = await response.text();

        if (!bodyText) {
            return {};
        }

        if (!contentType.includes('application/json')) {
            const preview = bodyText.replace(/\s+/g, ' ').slice(0, 120);
            throw new Error(`Unexpected non-JSON response from ${actionLabel}: ${preview}`);
        }

        try {
            return JSON.parse(bodyText);
        } catch {
            throw new Error(`Invalid JSON response from ${actionLabel}`);
        }
    }

    // Assigns a city function to a grid cell
    async assign(cellId, functionId) {
        const response = await fetch(`/grid/${cellId}/assign`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': this.#csrfToken,
            },
            body: JSON.stringify({ function_id: parseInt(functionId) }),
        });

        if (!response.ok) {
            const data = await this.#readJsonResponse(response, 'grid assign').catch(() => ({}));
            throw new Error(data.message || `Assign failed: ${response.status}`);
        }

        return this.#readJsonResponse(response, 'grid assign');
    }

    // Removes the city function from a grid cell
    async remove(cellId) {
        const response = await fetch(`/grid/${cellId}/remove`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': this.#csrfToken,
            },
        });

        if (!response.ok) {
            const data = await this.#readJsonResponse(response, 'grid remove').catch(() => ({}));
            throw new Error(data.message || `Remove failed: ${response.status}`);
        }

        return this.#readJsonResponse(response, 'grid remove');
    }

    // Reverts the last grid action
    async undo() {
        const response = await fetch('/grid/undo', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': this.#csrfToken,
            },
        });

        if (!response.ok) {
            const data = await this.#readJsonResponse(response, 'grid undo').catch(() => ({}));
            throw new Error(data.message || `Undo failed: ${response.status}`);
        }

        return this.#readJsonResponse(response, 'grid undo');
    }

    // Fetches the current total and per-category QoL scores
    async getQolScore() {
        const response = await fetch('/grid/qol-score');

        if (!response.ok) {
            const data = await this.#readJsonResponse(response, 'grid qol score').catch(() => ({}));
            throw new Error(data.message || `QoL score fetch failed: ${response.status}`);
        }

        return this.#readJsonResponse(response, 'grid qol score');
    }

    // Fetches valid and invalid cells for placing a function based on adjacency rules
    async getValidCells(functionId) {
        const response = await fetch(`/grid/valid-cells?function_id=${functionId}`);

        if (!response.ok) {
            const data = await this.#readJsonResponse(response, 'grid valid cells').catch(() => ({}));
            throw new Error(data.message || `Valid cells fetch failed: ${response.status}`);
        }

        return this.#readJsonResponse(response, 'grid valid cells');
    }
}
