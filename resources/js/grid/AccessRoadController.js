import { notify } from '../utils/notify';

/**
 * SIM.12 - Access road placement UI.
 *
 * Two-click selection: user picks start cell then end cell; the server
 * runs BFS and returns the path. Roads are highlighted amber on the grid.
 * All interactions are keyboard-accessible (Enter/Space to select, Escape
 * to cancel) and announced via the existing aria-live announcer.
 *
 * Uses capture-phase listeners on the grid container so that GridController's
 * bubble-phase cell handlers are suppressed while road-placement mode is active.
 */
export class AccessRoadController {
    #api;
    #qolService;
    #mode = 'normal'; // 'normal' | 'selecting-start' | 'selecting-end'
    #startCell = null;
    #roads = [];
    #roadCellIds = new Set();

    // DOM refs
    #statusEl = null;
    #roadListEl = null;
    #toggleBtn = null;
    #cancelBtn = null;
    #grid = null;
    #announcer = null;

    // Stored so we can remove listeners when exiting road mode
    #captureClick = null;
    #captureKeydown = null;

    constructor(api, qolService) {
        this.#api = api;
        this.#qolService = qolService;
    }

    init() {
        this.#statusEl   = document.getElementById('access-road-status');
        this.#roadListEl = document.getElementById('access-road-list');
        this.#toggleBtn  = document.getElementById('access-road-toggle');
        this.#cancelBtn  = document.getElementById('access-road-cancel');
        this.#grid       = document.querySelector('[data-city-grid]');
        this.#announcer  = document.getElementById('grid-a11y-announcer');

        if (!this.#toggleBtn) return;

        this.#toggleBtn.addEventListener('click', () => this.#enterRoadMode());
        this.#cancelBtn?.addEventListener('click', () => this.#exitRoadMode());

        document.addEventListener('roads-updated', (e) => {
            this.#roads = e.detail.roads;
            this.#syncRoadCellIds();
            this.#renderRoadHighlights();
            this.#renderRoadList();
        });

        this.#loadRoads();
    }

    // ── Mode management ───────────────────────────────────────────────────────

    #enterRoadMode() {
        this.#mode = 'selecting-start';
        this.#toggleBtn.classList.add('hidden');
        this.#cancelBtn?.classList.remove('hidden');

        const msg = 'Road placement mode: click a cell, or focus a cell and press Enter, to select the start point.';
        this.#updateStatus(msg);
        this.#announce(msg);

        this.#grid?.classList.add('road-selection-mode');

        // Capture phase fires before GridController's cell-level bubble handlers,
        // letting us take ownership of clicks/keys and stop further propagation.
        this.#captureClick   = (e) => this.#handleCapture(e);
        this.#captureKeydown = (e) => this.#handleCapture(e);
        this.#grid?.addEventListener('click',   this.#captureClick,   true);
        this.#grid?.addEventListener('keydown', this.#captureKeydown, true);
    }

    #exitRoadMode() {
        this.#mode = 'normal';
        this.#startCell = null;
        this.#toggleBtn.classList.remove('hidden');
        this.#cancelBtn?.classList.add('hidden');
        this.#updateStatus('');

        this.#grid?.classList.remove('road-selection-mode');

        document.querySelectorAll('[data-grid-cell].road-start-selected').forEach(c => {
            c.classList.remove('road-start-selected');
            c.removeAttribute('aria-pressed');
        });

        this.#grid?.removeEventListener('click',   this.#captureClick,   true);
        this.#grid?.removeEventListener('keydown', this.#captureKeydown, true);
        this.#captureClick = this.#captureKeydown = null;
    }

    // ── Capture-phase event handler ───────────────────────────────────────────

    #handleCapture(e) {
        const cell = e.target.closest('[data-grid-cell]');
        if (!cell) return;

        // Escape always cancels, regardless of event type
        if (e.type === 'keydown' && e.key === 'Escape') {
            e.stopPropagation();
            e.preventDefault();
            this.#exitRoadMode();
            this.#announce('Road placement cancelled.');
            return;
        }

        // Only act on click or Enter / Space keydown
        const isActivation = e.type === 'click' ||
            (e.type === 'keydown' && (e.key === 'Enter' || e.key === ' '));
        if (!isActivation) return;

        // Prevent GridController from also handling this event
        e.stopPropagation();
        if (e.type === 'keydown') e.preventDefault();

        this.#selectCell(cell);
    }

    #selectCell(cell) {
        if (this.#mode === 'selecting-start') {
            this.#startCell = cell;
            cell.classList.add('road-start-selected');
            cell.setAttribute('aria-pressed', 'true');
            this.#mode = 'selecting-end';

            const msg = `Start: Row ${cell.dataset.row}, Column ${cell.dataset.column}. Now select the end cell, or press Escape to cancel.`;
            this.#updateStatus(msg);
            this.#announce(msg);
            return;
        }

        if (this.#mode === 'selecting-end') {
            if (cell === this.#startCell) {
                const msg = 'Start and end cells must be different. Select a different end cell.';
                notify(msg);
                this.#announce(msg);
                return;
            }
            this.#placeRoad(this.#startCell, cell);
        }
    }

    // ── Road CRUD ─────────────────────────────────────────────────────────────

    async #loadRoads() {
        try {
            this.#roads = await this.#api.getAccessRoads();
            this.#syncRoadCellIds();
            this.#renderRoadHighlights();
            this.#renderRoadList();
        } catch {
            // Non-critical; grid works without roads loaded
        }
    }

    async #placeRoad(startCell, endCell) {
        const calculatingMsg = 'Calculating shortest route…';
        this.#updateStatus(calculatingMsg);
        this.#announce(calculatingMsg);

        // Exit road mode first so GridController resumes normal operation
        this.#exitRoadMode();

        try {
            const result = await this.#api.storeAccessRoad(
                startCell.dataset.cellId,
                endCell.dataset.cellId,
            );
            this.#roads.push(result.road);
            this.#syncRoadCellIds();
            this.#renderRoadHighlights();
            this.#renderRoadList();
            this.#qolService?.refresh();

            const msg = `Access road placed (${result.road.cell_ids.length} cells). Mobility score updated.`;
            notify(msg);
            this.#announce(msg);
        } catch (err) {
            const msg = err.message || 'Failed to place access road.';
            notify(msg);
            this.#announce(msg);
        }
    }

    async #toggleRoad(id) {
        try {
            const updated = await this.#api.toggleAccessRoad(id);
            const idx = this.#roads.findIndex(r => r.id === updated.id);
            if (idx !== -1) this.#roads[idx] = updated;
            this.#syncRoadCellIds();
            this.#renderRoadHighlights();
            this.#renderRoadList();
            this.#qolService?.refresh();
        } catch (err) {
            notify(err.message || 'Failed to toggle road.');
        }
    }

    async #removeRoad(id, label) {
        try {
            await this.#api.destroyAccessRoad(id);
            this.#roads = this.#roads.filter(r => r.id !== id);
            this.#syncRoadCellIds();
            this.#renderRoadHighlights();
            this.#renderRoadList();
            this.#qolService?.refresh();

            // SIM.12.2 - Notify EventRouteController so it can drop routes that used this road.
            document.dispatchEvent(new CustomEvent('road-removed', { detail: { road_id: id } }));

            const msg = `${label} removed. Mobility score updated.`;
            notify(msg);
            this.#announce(msg);
        } catch (err) {
            const msg = err.message || 'Failed to remove access road.';
            notify(msg);
            this.#announce(msg);
        }
    }

    // ── Rendering ─────────────────────────────────────────────────────────────

    #syncRoadCellIds() {
        this.#roadCellIds = new Set(
            this.#roads
                .filter(r => r.is_active)
                .flatMap(r => r.cell_ids.map(String))
        );
    }

    #renderRoadHighlights() {
        document.querySelectorAll('[data-grid-cell]').forEach(cell => {
            const isRoad = this.#roadCellIds.has(String(cell.dataset.cellId));
            cell.classList.toggle('road-cell', isRoad);

            // Keep aria-label consistent with road membership
            const base = (cell.getAttribute('aria-label') || '')
                .replace(/,\s*access road cell/g, '')
                .trimEnd();
            cell.setAttribute('aria-label', isRoad ? `${base}, access road cell` : base);
        });
    }

    #renderRoadList() {
        if (!this.#roadListEl) return;
        this.#roadListEl.innerHTML = '';

        if (this.#roads.length === 0) {
            const empty = document.createElement('p');
            empty.className = 'text-gray-500 dark:text-gray-400 text-sm';
            empty.textContent = 'No access roads placed.';
            this.#roadListEl.appendChild(empty);
            return;
        }

        this.#roads.forEach((road, index) => {
            const active = road.is_active !== false;
            const label = `Road ${index + 1} (${road.cell_ids.length} cells)`;

            const row = document.createElement('div');
            row.className = 'flex items-center justify-between py-1';

            const span = document.createElement('span');
            span.className = active
                ? 'text-sm text-gray-700 dark:text-gray-200'
                : 'text-sm text-gray-400 dark:text-gray-500 line-through';
            span.textContent = label;

            const actions = document.createElement('div');
            actions.className = 'flex gap-1';

            const toggleBtn = document.createElement('button');
            toggleBtn.type = 'button';
            toggleBtn.className = active
                ? 'text-xs text-blue-600 dark:text-blue-400 hover:underline focus:outline-none focus:ring-1 focus:ring-blue-500 rounded px-1'
                : 'text-xs text-green-600 dark:text-green-400 hover:underline focus:outline-none focus:ring-1 focus:ring-green-500 rounded px-1';
            toggleBtn.textContent = active ? 'Deactivate' : 'Activate';
            toggleBtn.setAttribute('aria-label', `${active ? 'Deactivate' : 'Activate'} ${label}`);
            toggleBtn.addEventListener('click', () => this.#toggleRoad(road.id));

            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'text-xs text-red-600 dark:text-red-400 hover:underline focus:outline-none focus:ring-1 focus:ring-red-500 rounded px-1';
            removeBtn.textContent = 'Remove';
            removeBtn.setAttribute('aria-label', `Remove ${label}`);
            removeBtn.addEventListener('click', () => this.#removeRoad(road.id, label));

            actions.appendChild(toggleBtn);
            actions.appendChild(removeBtn);
            row.appendChild(span);
            row.appendChild(actions);
            this.#roadListEl.appendChild(row);
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
