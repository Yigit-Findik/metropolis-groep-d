import { getEventModifiersForFunction, getActiveEventsForFunction } from '../alpine/activeEvents.js';

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
        // Hide popup on scroll/touch to avoid it floating in a wrong position
        const hideOnScroll = () => { if (this.#visible) this.#hide(); };
        window.addEventListener('scroll', hideOnScroll, { passive: true });
        window.addEventListener('touchmove', hideOnScroll, { passive: true });
        this.#grid.closest('.lg\\:overflow-auto')?.addEventListener('scroll', hideOnScroll, { passive: true });

        this.#grid.addEventListener('focusin', (e) => {
            const el = e.target.closest('[data-grid-cell]');
            if (!el || !this.#grid.contains(el) || !el.dataset.function) return;

            const rect = el.getBoundingClientRect();
            this.#show(el, { clientX: rect.right, clientY: rect.top });
        });

        this.#grid.addEventListener('focusout', (e) => {
            const relatedTarget = e.relatedTarget;
            if (relatedTarget?.closest('[data-grid-cell]')?.dataset?.function) return;
            if (this.#visible) this.#hide();
        });

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

    // Moves the popup to follow the cursor, flipping sides if it would overflow the viewport
    #move(e) {
        if (!this.#visible) return;
        const offset = 12;

        // Place at default position first so getBoundingClientRect reflects the scaled size
        this.#popup.style.left = `${e.clientX + offset}px`;
        this.#popup.style.top = `${e.clientY + offset}px`;

        const rect = this.#popup.getBoundingClientRect();

        const left = rect.right > window.innerWidth
            ? Math.max(0, e.clientX - offset - rect.width)
            : e.clientX + offset;

        const top = rect.bottom > window.innerHeight
            ? Math.max(0, e.clientY - offset - rect.height)
            : e.clientY + offset;

        this.#popup.style.left = `${left}px`;
        this.#popup.style.top = `${top}px`;
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
        const activeFunction = (activeCell.dataset.function || '').trim().toLowerCase();
        const activeIsSensitive = HoverPopup.#SENSITIVE_FUNCTIONS.has(activeFunction);
        const activeIsPolluter = HoverPopup.#POLLUTERS.some((p) => activeFunction.includes(p));

        // +2 bonus for each same-category orthogonal neighbor
        this.#getOrthogonalNeighbors(cells, activeCell)
            .map((n) => n.cell)
            .filter((cell) => this.#getCategoryKey(cell.dataset.category) === activeCategory)
            .forEach((cell) => this.#createBadge(cell, 2, activeCell));

        // Penalty for sensitive functions next to polluters
        if (activeIsSensitive || activeIsPolluter) {
            this.#getOrthogonalNeighbors(cells, activeCell)
                .map((n) => n.cell)
                .filter((cell) => {
                    const neighborFunction = (cell.dataset.function || '').trim().toLowerCase();
                    return activeIsSensitive
                        ? HoverPopup.#POLLUTERS.some((p) => neighborFunction.includes(p))
                        : HoverPopup.#SENSITIVE_FUNCTIONS.has(neighborFunction);
                })
                .forEach((cell) => this.#createBadge(cell, -2, activeCell));
        }
    }

    // Creates and positions a floating +/- badge over the given cell
    #createBadge(cell, amount, anchorCell = null) {
        const cellId = cell.dataset.cellId || cell.getAttribute('data-cell-id') || '';
        const anchorId = anchorCell?.dataset.cellId || anchorCell?.getAttribute('data-cell-id') || '';
        const id = anchorId ? `${cellId}-${anchorId}-${amount}` : `${cellId}-${amount}`;
        // Remove any existing badge for this same badge position before adding a new one
        document.querySelector(`.bonus-badge[data-target="${id}"]`)?.remove();

        const badge = document.createElement('div');
        const isPositive = amount > 0;
        badge.className = `bonus-badge ${isPositive ? 'bonus-badge--positive' : 'bonus-badge--negative'}`;
        const cellRect = cell.getBoundingClientRect();
        let scale = Math.max(0.75, Math.min(2.5, (cellRect.width || HoverPopup.#BASE_CELL_SIZE) / HoverPopup.#BASE_CELL_SIZE));

        badge.style.position = 'fixed';
        badge.style.zIndex = '60';
        badge.style.pointerEvents = 'none';

        if (isPositive) {
            const shortLabel = this.#getCategoryShortLabel(cell.dataset.category);
            const anchorRect = anchorCell?.getBoundingClientRect() ?? null;
            const positionRect = anchorRect
                ? {
                    left: (anchorRect.left + cellRect.left + anchorRect.width / 2 + cellRect.width / 2) / 2,
                    top: (anchorRect.top + cellRect.top + anchorRect.height / 2 + cellRect.height / 2) / 2,
                }
                : {
                    left: Math.round(cellRect.left + cellRect.width * 0.68),
                    top: Math.round(cellRect.top - cellRect.height * 0.12),
                };
            badge.innerHTML = `
                <span class="flex h-11 w-11 flex-col items-center justify-center rounded-full bg-green-500 px-1 text-[10px] font-semibold leading-none text-white shadow-md ring-2 ring-white/80">
                    <span class="uppercase tracking-[0.18em]">${shortLabel}</span>
                    <span class="text-[11px] font-bold">+${amount}</span>
                </span>
            `;
            badge.style.left = `${Math.round(positionRect.left)}px`;
            badge.style.top = `${Math.round(positionRect.top)}px`;
            badge.style.transformOrigin = 'center';
            badge.style.transform = `translate(-50%, -50%) scale(${scale})`;
        } else {
            const shortLabel = this.#getCategoryShortLabel(cell.dataset.category);
            const anchorRect = anchorCell?.getBoundingClientRect() ?? null;
            const positionRect = anchorRect
                ? {
                    left: (anchorRect.left + cellRect.left + anchorRect.width / 2 + cellRect.width / 2) / 2,
                    top: (anchorRect.top + cellRect.top + anchorRect.height / 2 + cellRect.height / 2) / 2,
                }
                : {
                    left: Math.round(cellRect.left + cellRect.width * 0.68),
                    top: Math.round(cellRect.top - cellRect.height * 0.12),
                };
            badge.innerHTML = `
                <span class="flex h-11 w-11 flex-col items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-semibold leading-none text-white shadow-md ring-2 ring-white/80">
                    <span class="uppercase tracking-[0.18em]">${shortLabel}</span>
                    <span class="text-[11px] font-bold">-${Math.abs(amount)}</span>
                </span>
            `;
            badge.style.left = `${Math.round(positionRect.left)}px`;
            badge.style.top = `${Math.round(positionRect.top)}px`;
            badge.style.transformOrigin = 'center';
            badge.style.transform = `translate(-50%, -50%) scale(${scale})`;
        }
        badge.setAttribute('data-target', id);

        document.body.appendChild(badge);
    }

    #getCategoryShortLabel(category) {
        const normalized = (category || '').trim().toLowerCase();
        const labels = {
            safety: 'saf',
            recreation: 'rec',
            'environment quality': 'enq',
            environmentQuality: 'enq',
            facilities: 'fac',
            mobility: 'mob',
        };

        return labels[normalized] || normalized.slice(0, 3) || 'cat';
    }

    #getInwardArrow(direction, tone = 'green') {
        const color = tone === 'red' ? 'text-red-700' : 'text-green-700';
        const common = `h-4 w-4 ${color} drop-shadow-sm`;

        if (direction === 'right') {
            return `
                <svg viewBox="0 0 16 16" fill="none" class="${common}" aria-hidden="true">
                    <path d="M2 8h9" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" />
                    <path d="M8 4l4 4-4 4" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            `;
        }

        if (direction === 'left') {
            return `
                <svg viewBox="0 0 16 16" fill="none" class="${common}" aria-hidden="true">
                    <path d="M14 8H5" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" />
                    <path d="M8 4 4 8l4 4" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            `;
        }

        if (direction === 'down') {
            return `
                <svg viewBox="0 0 16 16" fill="none" class="${common}" aria-hidden="true">
                    <path d="M8 2v9" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" />
                    <path d="M4 8l4 4 4-4" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            `;
        }

        return `
            <svg viewBox="0 0 16 16" fill="none" class="${common}" aria-hidden="true">
                <path d="M8 14V5" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" />
                <path d="M4 8l4-4 4 4" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        `;
    }

    #removeBonusBadges() {
        document.querySelectorAll('.bonus-badge').forEach((n) => n.remove());
    }

    // Returns a coloured pill span for a numeric effect value.
    #formatBadge(value, eventModified = false) {
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
        const eventMods = ds.functionId ? getEventModifiersForFunction(ds.functionId) : null;

        const badges = [
            ['safety', 'Saf'],
            ['recreation', 'Rec'],
            ['environmentQuality', 'EnQ'],
            ['facilities', 'Fac'],
            ['mobility', 'Mob'],
        ].map(([key, label]) => {
            const base = parseInt(ds[key] ?? 0, 10);
            const mod  = eventMods?.[key] ?? 0;
            const b = this.#formatBadge(base + mod, mod !== 0);
            return `<div class="flex items-center gap-2"><div class="w-8 text-[10px] text-gray-500 dark:text-gray-400">${label}</div>${b}</div>`;
        }).join('');

        const activeEvents = ds.functionId ? getActiveEventsForFunction(ds.functionId) : [];
        const eventsSection = this.#formatActiveEvents(activeEvents);

        return [
            `<div class="font-semibold mb-1 text-xs">${name}</div>`,
            `<div class="mb-2 inline-flex items-center rounded-full bg-blue-100 px-2 py-0.5 text-[11px] font-semibold text-blue-700 dark:bg-blue-900/40 dark:text-blue-100">${category}</div>`,
            `<div class="grid gap-1">${badges}</div>`,
            eventsSection ? `<div class="border-t border-gray-300 dark:border-gray-600 pt-2 mt-2 text-[10px] space-y-0.5">${eventsSection}</div>` : '',
        ].join('');
    }

    #formatActiveEvents(events) {
        if (!events || events.length === 0) return '';

        const STAT_LABELS = [
            ['safety',             'Saf'],
            ['recreation',         'Rec'],
            ['environmentQuality', 'EnQ'],
            ['facilities',         'Fac'],
            ['mobility',           'Mob'],
        ];

        return events.map(ev => {
            const parts = STAT_LABELS
                .filter(([key]) => ev[key] !== 0)
                .map(([key, label]) => {
                    const v = ev[key];
                    const color = v > 0 ? 'text-green-600 dark:text-green-400' : 'text-red-500 dark:text-red-400';
                    return `<span class="${color}">${v > 0 ? '+' : ''}${v} ${label}</span>`;
                });

            const mods = parts.length ? `: ${parts.join(', ')}` : '';
            return `<div class="text-gray-700 dark:text-gray-300 font-semibold">• ${ev.name}${mods}</div>`;
        }).join('');
    }
}
