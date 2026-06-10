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

    // Returns all stored access roads with their cell IDs
    async getAccessRoads() {
        const response = await fetch('/access-roads', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (!response.ok) {
            const data = await this.#readJsonResponse(response, 'access roads index').catch(() => ({}));
            throw new Error(data.message || `Access roads fetch failed: ${response.status}`);
        }

        return this.#readJsonResponse(response, 'access roads index');
    }

    // Calculates and stores an access road between two cells
    async storeAccessRoad(startCellId, endCellId) {
        const response = await fetch('/access-roads', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': this.#csrfToken,
            },
            body: JSON.stringify({
                start_cell_id: parseInt(startCellId),
                end_cell_id: parseInt(endCellId),
            }),
        });

        const data = await this.#readJsonResponse(response, 'access road store').catch(() => ({}));

        if (!response.ok) {
            throw new Error(data.message || `Store access road failed: ${response.status}`);
        }

        return data;
    }

    // Returns all grid cells that are event locations (function linked to a city event)
    async getEventCells() {
        const response = await fetch('/event-cells', {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });

        if (!response.ok) {
            const data = await this.#readJsonResponse(response, 'event cells').catch(() => ({}));
            throw new Error(data.message || `Event cells fetch failed: ${response.status}`);
        }

        return this.#readJsonResponse(response, 'event cells');
    }

    // Returns all stored event routes with their cell IDs
    async getEventRoutes() {
        const response = await fetch('/event-routes', {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });

        if (!response.ok) {
            const data = await this.#readJsonResponse(response, 'event routes index').catch(() => ({}));
            throw new Error(data.message || `Event routes fetch failed: ${response.status}`);
        }

        return this.#readJsonResponse(response, 'event routes index');
    }

    // Creates an event route from an access road to an event cell
    async storeEventRoute(accessRoadId, eventCellId) {
        const response = await fetch('/event-routes', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': this.#csrfToken,
            },
            body: JSON.stringify({ access_road_id: parseInt(accessRoadId), event_cell_id: parseInt(eventCellId) }),
        });

        const data = await this.#readJsonResponse(response, 'event route store').catch(() => ({}));

        if (!response.ok) {
            throw new Error(data.message || `Store event route failed: ${response.status}`);
        }

        return data;
    }

    // Removes an event route by ID
    async destroyEventRoute(id) {
        const response = await fetch(`/event-routes/${id}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': this.#csrfToken,
            },
        });

        if (!response.ok) {
            const data = await this.#readJsonResponse(response, 'event route destroy').catch(() => ({}));
            throw new Error(data.message || `Delete event route failed: ${response.status}`);
        }

        return this.#readJsonResponse(response, 'event route destroy');
    }

    // Removes an access road by ID
    async destroyAccessRoad(id) {
        const response = await fetch(`/access-roads/${id}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': this.#csrfToken,
            },
        });

        if (!response.ok) {
            const data = await this.#readJsonResponse(response, 'access road destroy').catch(() => ({}));
            throw new Error(data.message || `Destroy access road failed: ${response.status}`);
        }

        return this.#readJsonResponse(response, 'access road destroy');
    }

    // Approves a single grid cell (policy maker only)
    async approve(cellId) {
        const response = await fetch(`/grid/${cellId}/approve`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': this.#csrfToken,
            },
        });

        if (!response.ok) {
            const data = await this.#readJsonResponse(response, 'grid approve').catch(() => ({}));
            throw new Error(data.message || `Approve failed: ${response.status}`);
        }

        return this.#readJsonResponse(response, 'grid approve');
    }

    // Revokes approval from a single grid cell (policy maker only)
    async revoke(cellId) {
        const response = await fetch(`/grid/${cellId}/revoke`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': this.#csrfToken,
            },
        });

        if (!response.ok) {
            const data = await this.#readJsonResponse(response, 'grid revoke').catch(() => ({}));
            throw new Error(data.message || `Revoke failed: ${response.status}`);
        }

        return this.#readJsonResponse(response, 'grid revoke');
    }

    // Approves all grid cells at once (policy maker only)
    async approveAll() {
        const response = await fetch('/grid/approve-all', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': this.#csrfToken,
            },
        });

        if (!response.ok) {
            const data = await this.#readJsonResponse(response, 'grid approve-all').catch(() => ({}));
            throw new Error(data.message || `Approve all failed: ${response.status}`);
        }

        return this.#readJsonResponse(response, 'grid approve-all');
    }

    // Revokes approval from all grid cells at once (policy maker only)
    async revokeAll() {
        const response = await fetch('/grid/revoke-all', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': this.#csrfToken,
            },
        });

        if (!response.ok) {
            const data = await this.#readJsonResponse(response, 'grid revoke-all').catch(() => ({}));
            throw new Error(data.message || `Revoke all failed: ${response.status}`);
        }

        return this.#readJsonResponse(response, 'grid revoke-all');
    }
}