/**
 * Manages the floating hover popup on grid cells.
 * Shows effect badges, highlights orthogonal neighbors, and marks penalty zones.
 */
export class HoverPopup {
    // CSS classes applied to the hovered cell and its orthogonal neighbors
    static #HOVER_HIGHLIGHT = ['ring-4', 'ring-amber-400', 'bg-amber-50', 'dark:bg-amber-400/10'];
    static #NEIGHBOR_HIGHLIGHT = ['ring-2', 'ring-amber-300', 'bg-amber-50/70', 'dark:bg-amber-400/5'];

    // Functions that lose QoL when next to polluters
    static #SENSITIVE_FUNCTIONS = new Set(['park', 'school', 'hospital']);

    // Functions that cause penalties when adjacent to sensitive functions
    static #POLLUTERS = ['road', 'store', 'gas station'];

    static #BASE_CELL_SIZE = 96; // Default cell pixel size used for popup scale calculations

    #popup;
    #grid;
    #activeCell = null;
    #visible = false;

    // Entry point — call once after DOMContentLoaded
    setup() {
        this.#grid = document.querySelector('[data-city-grid]');
        if (!this.#grid) return;

        this.#popup = this.#createPopup();
        this.#attachListeners();
    }

    // Creates the floating popup element and appends it to the body
    #createPopup() {
        let popup = document.getElementById('function-hover-popup');
        if (popup) return popup; // Reuse if already in DOM

        popup = document.createElement('div');
        popup.id = 'function-hover-popup';
        popup.style.position = 'fixed';
        popup.style.pointerEvents = 'none'; // Clicks fall through to the grid
        popup.style.zIndex = '9999';
        popup.className = 'hidden bg-white dark:bg-gray-800 text-xs rounded-md shadow-lg p-2 text-gray-900 dark:text-gray-100';
        document.body.appendChild(popup);
        return popup;
    }

    // Attaches all mouse and touch event listeners to the grid
    #attachListeners() {
        // Hide popup on scroll/touch to avoid it floating in a wrong position on mobile
        window.addEventListener('scroll', () => this.#hideOnMobileScroll(), { passive: true });
        window.addEventListener('touchmove', () => this.#hideOnMobileScroll(), { passive: true });

        // Delegate events through the grid so they still work after the DOM is mutated by drops
        this.#grid.addEventListener('mouseover', (e) => {
            const el = e.target.closest('[data-grid-cell]');
            if (!el || !this.#grid.contains(el)) return;
            this.#show(el, e);
        });

        // On mobile, a tap toggles the popup instead of hover
        this.#grid.addEventListener('click', (e) => {
            if (!this.#isMobileViewport()) return;
            const el = e.target.closest('[data-grid-cell]');
            if (!el || !this.#grid.contains(el)) return;
            this.#show(el, e);
        });

        // Reposition popup and refresh badges as the cursor moves
        this.#grid.addEventListener('mousemove', (e) => {
            if (!this.#visible) return;
            this.#move(e);
            if (this.#activeCell) {
                this.#updateBonusBadges(this.#activeCell);
                this.#popup.innerHTML = this.#buildHtml(this.#activeCell);
            }
        });

        this.#grid.addEventListener('mouseout', (e) => {
            // If moving directly into another occupied cell, mouseover handles the switch
            const relatedTarget = e.relatedTarget;
            if (relatedTarget?.closest('[data-grid-cell]')?.dataset?.function) return;

            const el = e.target.closest('[data-grid-cell]');
            if (!el || !this.#grid.contains(el)) return;

            this.#hide();
        });
    }

    // Shows the popup for the given cell, highlighting it and its neighbors
    #show(el, e) {
        if (!el.dataset?.function || el.dataset.function === '') return; // Skip empty cells

        // If already showing for this cell, just reposition
        if (this.#activeCell === el && !this.#popup.classList.contains('hidden')) {
            this.#setPopupScale(el);
            this.#move(e);
            return;
        }

        this.#activeCell = el;

        const cells = this.#getCells();
        this.#clearHighlights(cells);
        el.classList.add(...HoverPopup.#HOVER_HIGHLIGHT);
        this.#setPopupScale(el);

        const neighbors = this.#getOrthogonalNeighbors(cells, el);
        neighbors.forEach(({ cell }) => cell.classList.add(...HoverPopup.#NEIGHBOR_HIGHLIGHT));

        this.#updateBonusBadges(el);
        this.#popup.innerHTML = this.#buildHtml(el);
        this.#popup.classList.remove('hidden');
        this.#visible = true;
        this.#move(e);
    }

    #hide() {
        this.#clearHighlights(this.#getCells());
        this.#popup.classList.add('hidden');
        this.#popup.style.transform = '';
        this.#removeBonusBadges();
        this.#activeCell = null;
        this.#visible = false;
    }

    // Moves the popup to follow the cursor with a small offset
    #move(e) {
        if (!this.#visible) return;
        this.#popup.style.left = `${e.clientX + 12}px`;
        this.#popup.style.top = `${e.clientY + 12}px`;
    }

    #hideOnMobileScroll() {
        if (!this.#visible || !this.#activeCell || !this.#isMobileViewport()) return;
        this.#hide();
    }

    #isMobileViewport() {
        return window.matchMedia('(max-width: 1023px)').matches;
    }

    // Re-queries cells every time — handles DOM mutations from drops without rebinding
    #getCells() {
        return Array.from(document.querySelectorAll('[data-grid-cell]'));
    }

    // Scales the popup proportionally to the current zoom level of the grid cells
    #setPopupScale(el) {
        const cellSize = el.getBoundingClientRect().width || HoverPopup.#BASE_CELL_SIZE;
        const scale = Math.max(0.75, Math.min(2.5, cellSize / HoverPopup.#BASE_CELL_SIZE));
        this.#popup.style.transformOrigin = 'top left';
        this.#popup.style.transform = `scale(${scale})`;
    }

    #clearHighlights(cells) {
        cells.forEach((cell) => {
            cell.classList.remove(...HoverPopup.#HOVER_HIGHLIGHT, ...HoverPopup.#NEIGHBOR_HIGHLIGHT);
        });
    }

    // Normalises category names to match the dataset key convention (e.g. camelCase)
    #getCategoryKey(category) {
        const normalized = (category || '').trim().toLowerCase();
        if (normalized === 'environment quality') return 'environmentQuality';
        return normalized;
    }

    /**
     * Returns all directly adjacent (up/down/left/right) occupied cells.
     * Also calculates whether each neighbor shares the same category as the source.
     */
    #getOrthogonalNeighbors(cells, sourceCell) {
        const row = Number.parseInt(sourceCell.dataset.row || '', 10);
        const column = Number.parseInt(sourceCell.dataset.column || '', 10);
        const sourceCategory = sourceCell.dataset.category || '';

        if (Number.isNaN(row) || Number.isNaN(column)) return [];

        const neighbors = [];

        cells.forEach((cell) => {
            if (cell === sourceCell) return;
            if (!cell.dataset?.function || cell.dataset.function === '') return;

            const cellRow = Number.parseInt(cell.dataset.row || '', 10);
            const cellColumn = Number.parseInt(cell.dataset.column || '', 10);
            const rowDelta = cellRow - row;
            const columnDelta = cellColumn - column;

            // A cell is orthogonal if it shares exactly one axis and is exactly 1 step away
            const isOrthogonal =
                (cellRow === row && Math.abs(columnDelta) === 1) ||
                (cellColumn === column && Math.abs(rowDelta) === 1);

            if (!isOrthogonal) return;

            const sameCategory = sourceCategory !== '' && sourceCategory === (cell.dataset.category || '');
            const direction = rowDelta === -1 ? 'top' : rowDelta === 1 ? 'bottom' : columnDelta === -1 ? 'left' : 'right';

            neighbors.push({ cell, direction, bonus: sameCategory ? 2 : 0, sameCategory });
        });

        return neighbors;
    }

    /**
     * Redraws all bonus/penalty badges floating over the relevant cells.
     * +2 badges appear on same-category neighbors; negative badges appear on
     * sensitive neighbors that are adjacent to polluters.
     */
    #updateBonusBadges(activeCell) {
        this.#removeBonusBadges();
        if (!activeCell) return;

        const cells = this.#getCells();
        const activeCategory = this.#getCategoryKey(activeCell.dataset.category);

        // +2 bonus for each same-category orthogonal neighbor
        this.#getOrthogonalNeighbors(cells, activeCell)
            .map((n) => n.cell)
            .filter((cell) => this.#getCategoryKey(cell.dataset.category) === activeCategory)
            .forEach((cell) => this.#createBadge(cell, 2));

        // Penalty for sensitive functions next to polluters
        this.#getOrthogonalNeighbors(cells, activeCell)
            .map((n) => n.cell)
            .filter((cell) => HoverPopup.#SENSITIVE_FUNCTIONS.has((cell.dataset.function || '').trim().toLowerCase()))
            .forEach((cell) => {
                // Count how many polluters are directly adjacent to this sensitive cell
                const penalty = this.#getOrthogonalNeighbors(cells, cell)
                    .filter(({ cell: neighbor }) =>
                        HoverPopup.#POLLUTERS.some((p) =>
                            (neighbor.dataset.function || '').trim().toLowerCase().includes(p)
                        )
                    )
                    .length * 2;

                if (penalty > 0) this.#createBadge(cell, -penalty);
            });
    }

    // Creates and positions a floating +/- badge over the given cell
    #createBadge(cell, amount) {
        const id = cell.dataset.cellId || cell.getAttribute('data-cell-id') || '';
        // Remove any existing badge for this cell before adding a new one
        document.querySelector(`.bonus-badge[data-target="${id}"]`)?.remove();

        const badge = document.createElement('div');
        const isPositive = amount > 0;
        badge.className = `bonus-badge ${isPositive ? 'bonus-badge--positive' : 'bonus-badge--negative'}`;
        badge.textContent = isPositive ? `+${amount}` : `${amount}`;
        badge.setAttribute('data-target', id);
        badge.style.cssText = 'position:fixed;z-index:60;pointer-events:auto;';

        // Anchor the badge to the top-right corner of the cell
        const rect = cell.getBoundingClientRect();
        badge.style.left = `${Math.round(rect.left + rect.width * 0.68)}px`;
        badge.style.top = `${Math.round(rect.top - rect.height * 0.12)}px`;

        // Scale badge to match the grid zoom level
        const scale = Math.max(0.75, Math.min(2.5, (rect.width || HoverPopup.#BASE_CELL_SIZE) / HoverPopup.#BASE_CELL_SIZE));
        badge.style.transformOrigin = 'top left';
        badge.style.transform = `scale(${scale})`;

        document.body.appendChild(badge);
    }

    #removeBonusBadges() {
        document.querySelectorAll('.bonus-badge').forEach((n) => n.remove());
    }

    // Returns a coloured pill span for a numeric effect value
    #formatBadge(value) {
        const n = parseInt(value || 0, 10);
        const sign = n > 0 ? `+${n}` : `${n}`;
        const bg = n > 0
            ? 'bg-green-500 text-white'
            : n < 0
                ? 'bg-red-500 text-white'
                : 'bg-gray-300 text-gray-800 dark:bg-gray-600 dark:text-gray-100';
        return `<span class="inline-flex items-center justify-center px-2 py-0.5 rounded-full text-[11px] ${bg}">${sign}</span>`;
    }

    // Builds the HTML shown inside the popup for a given cell
    #buildHtml(el) {
        const ds = el.dataset || {};
        const name = ds.function || '';
        const category = ds.category || 'Uncategorized';

        const badges = [
            ['safety', 'Saf'],
            ['recreation', 'Rec'],
            ['environmentQuality', 'EnQ'],
            ['facilities', 'Fac'],
            ['mobility', 'Mob'],
        ].map(([key, label]) => {
            const b = this.#formatBadge(ds[key] ?? 0);
            return `<div class="flex items-center gap-2"><div class="w-8 text-[10px] text-gray-500 dark:text-gray-400">${label}</div>${b}</div>`;
        }).join('');

        return [
            `<div class="font-semibold mb-1 text-xs">${name}</div>`,
            `<div class="mb-2 inline-flex items-center rounded-full bg-blue-100 px-2 py-0.5 text-[11px] font-semibold text-blue-700 dark:bg-blue-900/40 dark:text-blue-100">${category}</div>`,
            `<div class="grid gap-1">${badges}</div>`,
        ].join('');
    }
}
