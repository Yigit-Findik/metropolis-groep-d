import { notify } from '../utils/notify';

/**
 * SIM.12.2 - Event route placement UI.
 *
 * Flow: user selects an access road from a dropdown, then clicks "Create Route"
 * to enter selection mode. Event location cells are highlighted violet on the grid.
 * The user clicks an event cell to trigger the BFS route calculation.
 *
 * Routes are displayed as violet cells, distinct from amber access road cells.
 * All interactions are keyboard-accessible (Enter/Space to select, Escape to cancel)
 * and announced via the aria-live announcer.
 *
 * Uses capture-phase listeners on the grid so GridController's bubble-phase
 * handlers are suppressed while event-route placement mode is active.
 */
export class EventRouteController {
    #api;
    #qolService;
    #mode = 'normal'; // 'normal' | 'selecting-event-cell'
    #selectedRoadId = null;
    #routes = [];
    #eventCells = []; // [{ id, row, column, function_name }]
    #routeCellIds = new Set();
    #eventCellIds = new Set();

    // DOM refs
    #statusEl = null;
    #routeListEl = null;
    #roadSelectEl = null;
    #createBtn = null;
    #cancelBtn = null;
    #grid = null;
    #announcer = null;

    #captureClick = null;
    #captureKeydown = null;

    constructor(api, qolService) {
        this.#api = api;
        this.#qolService = qolService;
    }

    init() {
        this.#statusEl    = document.getElementById('event-route-status');
        this.#routeListEl = document.getElementById('event-route-list');
        this.#roadSelectEl = document.getElementById('event-route-road-select');
        this.#createBtn   = document.getElementById('event-route-create');
        this.#cancelBtn   = document.getElementById('event-route-cancel');
        this.#grid        = document.querySelector('[data-city-grid]');
        this.#announcer   = document.getElementById('grid-a11y-announcer');

        if (!this.#createBtn) return;

        this.#createBtn.addEventListener('click', () => this.#enterSelectionMode());
        this.#cancelBtn?.addEventListener('click', () => this.#exitSelectionMode());

        // Re-evaluate button state whenever the user changes the road dropdown.
        this.#roadSelectEl?.addEventListener('change', () => this.#updateCreateButtonState());

        // Keep the road dropdown in sync when access roads are added or removed.
        document.addEventListener('roads-updated', (e) => {
            this.#populateRoadSelect(e.detail.roads);
        });

        // When a road is deleted, reload routes since some may have been cascade-deleted.
        document.addEventListener('road-removed', () => this.#reloadRoutes());

        // When safety functions reroute access roads, update event routes to match.
        document.addEventListener('event-routes-updated', (e) => {
            this.#routes = e.detail.routes;
            this.#syncRouteCellIds();
            this.#renderRouteHighlights();
            this.#renderRouteList();
            this.#qolService?.refresh();
        });

        // Keep event cell highlights and routes in sync when grid content changes.
        document.addEventListener('grid-updated', () => this.#refreshAfterGridChange());

        this.#load();
    }

    // ── Initialisation ────────────────────────────────────────────────────────

    async #load() {
        try {
            const [roads, routes, eventCells] = await Promise.all([
                this.#api.getAccessRoads(),
                this.#api.getEventRoutes(),
                this.#api.getEventCells(),
            ]);
            // Set eventCells BEFORE populateRoadSelect so #updateCreateButtonState sees the real data.
            this.#eventCells = eventCells;
            this.#routes     = routes;
            this.#populateRoadSelect(roads);
            this.#syncRouteCellIds();
            this.#syncEventCellIds();
            this.#renderRouteHighlights();
            this.#renderEventCellHighlights();
            this.#renderRouteList();
        } catch {
            // Non-critical; grid works without routes loaded
        }
    }

    async refreshEventCells() {
        try {
            this.#eventCells = await this.#api.getEventCells();
            this.#syncEventCellIds();
            this.#renderEventCellHighlights();
            this.#updateCreateButtonState();
        } catch {
            // ignore
        }
    }

    async #refreshAfterGridChange() {
        try {
            const [eventCells, routes] = await Promise.all([
                this.#api.getEventCells(),
                this.#api.getEventRoutes(),
            ]);
            this.#eventCells = eventCells;
            this.#routes     = routes;
            this.#syncEventCellIds();
            this.#syncRouteCellIds();
            this.#renderEventCellHighlights();
            this.#renderRouteHighlights();
            this.#renderRouteList();
            this.#updateCreateButtonState();
        } catch {
            // ignore
        }
    }

    async #reloadRoutes() {
        try {
            this.#routes = await this.#api.getEventRoutes();
            this.#syncRouteCellIds();
            this.#renderRouteHighlights();
            this.#renderRouteList();
            this.#qolService?.refresh();
        } catch {
            // ignore
        }
    }

    // ── Dropdown helper ───────────────────────────────────────────────────────

    #populateRoadSelect(roads) {
        if (!this.#roadSelectEl) return;
        const previousValue = this.#roadSelectEl.value;
        this.#roadSelectEl.innerHTML = '';

        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = 'Select an access road…';
        placeholder.disabled = true;
        placeholder.selected = !previousValue;
        this.#roadSelectEl.appendChild(placeholder);

        roads.forEach((road, index) => {
            const opt = document.createElement('option');
            opt.value = road.id;
            opt.textContent = `Road ${index + 1} (${road.cell_ids.length} cells)`;
            if (String(road.id) === String(previousValue)) opt.selected = true;
            this.#roadSelectEl.appendChild(opt);
        });

        this.#updateCreateButtonState();
    }

    #updateCreateButtonState() {
        if (!this.#createBtn) return;
        const hasRoad       = this.#roadSelectEl?.value !== '';
        const hasEventCells = this.#eventCells.length > 0;
        this.#createBtn.disabled = !hasRoad || !hasEventCells;
        this.#createBtn.setAttribute('aria-disabled', String(!hasRoad || !hasEventCells));

        if (!hasEventCells) {
            this.#updateStatus('No event locations on the grid. Link a city event to a function, then place that function on the grid.');
        } else if (!hasRoad) {
            this.#updateStatus('Select an access road from the dropdown to get started.');
        } else {
            this.#updateStatus('');
        }
    }

    // ── Mode management ───────────────────────────────────────────────────────

    #enterSelectionMode() {
        this.#selectedRoadId = this.#roadSelectEl?.value || null;
        if (!this.#selectedRoadId) {
            const msg = 'Please select an access road first.';
            notify(msg);
            this.#announce(msg);
            return;
        }

        this.#mode = 'selecting-event-cell';
        this.#createBtn.classList.add('hidden');
        this.#cancelBtn?.classList.remove('hidden');
        this.#roadSelectEl.disabled = true;

        const msg = 'Event route mode: click a highlighted event location cell, or focus it and press Enter, to create the route. Press Escape to cancel.';
        this.#updateStatus(msg);
        this.#announce(msg);

        this.#grid?.classList.add('event-route-selection-mode');

        this.#captureClick   = (e) => this.#handleCapture(e);
        this.#captureKeydown = (e) => this.#handleCapture(e);
        this.#grid?.addEventListener('click',   this.#captureClick,   true);
        this.#grid?.addEventListener('keydown', this.#captureKeydown, true);
    }

    #exitSelectionMode() {
        this.#mode = 'normal';
        this.#selectedRoadId = null;
        this.#createBtn.classList.remove('hidden');
        this.#cancelBtn?.classList.add('hidden');
        if (this.#roadSelectEl) this.#roadSelectEl.disabled = false;
        this.#updateCreateButtonState();

        this.#grid?.classList.remove('event-route-selection-mode');

        this.#grid?.removeEventListener('click',   this.#captureClick,   true);
        this.#grid?.removeEventListener('keydown', this.#captureKeydown, true);
        this.#captureClick = this.#captureKeydown = null;
    }

    // ── Capture-phase event handler ───────────────────────────────────────────

    #handleCapture(e) {
        const cell = e.target.closest('[data-grid-cell]');
        if (!cell) return;

        if (e.type === 'keydown' && e.key === 'Escape') {
            e.stopPropagation();
            e.preventDefault();
            this.#exitSelectionMode();
            this.#announce('Event route placement cancelled.');
            return;
        }

        const isActivation = e.type === 'click' ||
            (e.type === 'keydown' && (e.key === 'Enter' || e.key === ' '));
        if (!isActivation) return;

        e.stopPropagation();
        if (e.type === 'keydown') e.preventDefault();

        const cellId = cell.dataset.cellId;

        // Only accept event location cells.
        if (!this.#eventCellIds.has(String(cellId))) {
            const msg = 'That cell is not an event location. Click a highlighted violet cell.';
            notify(msg);
            this.#announce(msg);
            return;
        }

        this.#createRoute(cellId);
    }

    // ── Route CRUD ────────────────────────────────────────────────────────────

    async #createRoute(eventCellId) {
        // Capture before exitSelectionMode() clears it.
        const roadId = this.#selectedRoadId;

        const calculatingMsg = 'Calculating shortest route to event…';
        this.#updateStatus(calculatingMsg);
        this.#announce(calculatingMsg);

        this.#exitSelectionMode();

        try {
            const result = await this.#api.storeEventRoute(roadId, eventCellId);
            this.#routes.push(result.route);
            this.#syncRouteCellIds();
            this.#renderRouteHighlights();
            this.#renderRouteList();
            this.#qolService?.refresh();

            const msg = `Event route created (${result.route.cell_ids.length} cells). Mobility score updated.`;
            notify(msg);
            this.#announce(msg);
        } catch (err) {
            const msg = err.message || 'Failed to create event route.';
            notify(msg);
            this.#announce(msg);
        }
    }

    async #removeRoute(id, label) {
        try {
            await this.#api.destroyEventRoute(id);
            this.#routes = this.#routes.filter(r => r.id !== id);
            this.#syncRouteCellIds();
            this.#renderRouteHighlights();
            this.#renderRouteList();
            this.#qolService?.refresh();

            const msg = `${label} removed. Mobility score updated.`;
            notify(msg);
            this.#announce(msg);
        } catch (err) {
            const msg = err.message || 'Failed to remove event route.';
            notify(msg);
            this.#announce(msg);
        }
    }

    // ── Rendering ─────────────────────────────────────────────────────────────

    #syncRouteCellIds() {
        this.#routeCellIds = new Set(
            this.#routes.flatMap(r => r.cell_ids.map(String))
        );
    }

    #syncEventCellIds() {
        this.#eventCellIds = new Set(this.#eventCells.map(c => String(c.id)));
    }

    #renderRouteHighlights() {
        document.querySelectorAll('[data-grid-cell]').forEach(cell => {
            const isRoute = this.#routeCellIds.has(String(cell.dataset.cellId));
            cell.classList.toggle('event-route-cell', isRoute);

            const base = (cell.getAttribute('aria-label') || '')
                .replace(/,\s*event route cell/g, '')
                .trimEnd();
            cell.setAttribute('aria-label', isRoute ? `${base}, event route cell` : base);
        });
    }

    #renderEventCellHighlights() {
        document.querySelectorAll('[data-grid-cell]').forEach(cell => {
            const isEvent = this.#eventCellIds.has(String(cell.dataset.cellId));
            cell.classList.toggle('event-location-cell', isEvent);

            const base = (cell.getAttribute('aria-label') || '')
                .replace(/,\s*event location/g, '')
                .trimEnd();
            cell.setAttribute('aria-label', isEvent ? `${base}, event location` : base);
        });
    }

    #renderRouteList() {
        if (!this.#routeListEl) return;
        this.#routeListEl.innerHTML = '';

        if (this.#routes.length === 0) {
            const empty = document.createElement('p');
            empty.className = 'text-gray-500 hc:text-white dark:text-gray-400 text-sm';
            empty.textContent = 'No event routes created.';
            this.#routeListEl.appendChild(empty);
            return;
        }

        this.#routes.forEach((route, index) => {
            const label = `Event Route ${index + 1} (${route.cell_ids.length} cells)`;

            const row = document.createElement('div');
            row.className = 'flex items-center justify-between py-1';

            const span = document.createElement('span');
            span.className = 'text-sm text-gray-700 hc:text-white dark:text-gray-200';
            span.textContent = label;

            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'text-xs text-red-600 hc:text-red-400 dark:text-red-400 hover:underline focus:outline-none focus:ring-1 focus:ring-red-500 hc:focus:ring-yellow-400 rounded px-1';
            removeBtn.textContent = 'Remove';
            removeBtn.setAttribute('aria-label', `Remove ${label}`);
            removeBtn.addEventListener('click', () => this.#removeRoute(route.id, label));

            row.appendChild(span);
            row.appendChild(removeBtn);
            this.#routeListEl.appendChild(row);
        });
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    #updateStatus(msg) {
        if (this.#statusEl) this.#statusEl.textContent = msg;
    }

    #announce(message) {
        if (!this.#announcer) return;
        this.#announcer.textContent = '';
        setTimeout(() => { this.#announcer.textContent = message; }, 50);
    }
}
