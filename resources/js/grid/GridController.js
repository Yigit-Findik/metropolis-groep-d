import { notify } from '../utils/notify';

/**
 * Orchestrates the city grid: drag-and-drop from library to cells,
 * removal via the drop zone, and the undo button.
 */
export class GridController {
    #api;
    #qolService;
    // Cell IDs (strings) that are forbidden for the function currently being dragged
    #draggingInvalidCells = new Set();

    constructor(api, qolService) {
        this.#api = api;
        this.#qolService = qolService;
    }

    // Entry point — call once after DOMContentLoaded
    init() {
        this.#setupLibraryCards();

        const grid = document.querySelector('[data-city-grid]');
        if (!grid) return; // Grid page not loaded, nothing to set up

        const cells = Array.from(grid.querySelectorAll('[data-grid-cell]'));
        this.#setupCells(cells);
        this.#setupRemovalZone();
        this.#setupUndoButton();
    }

    // Makes every library card draggable and stores its data in the drag transfer
    #setupLibraryCards() {
        const cards = document.querySelectorAll('[data-function]');

        cards.forEach((card) => {
            card.addEventListener('dragstart', (e) => {
                e.dataTransfer.setData('function', card.dataset.function);
                e.dataTransfer.setData('function_id', card.dataset.functionId);
                e.dataTransfer.setData('category', card.dataset.category ?? '');
                e.dataTransfer.setData('image', card.dataset.image);
                e.dataTransfer.setData('qol_score', card.dataset.qolScore);
                e.dataTransfer.setData('safety', card.dataset.safety ?? 0);
                e.dataTransfer.setData('recreation', card.dataset.recreation ?? 0);
                // dataset normalises "environment-quality" to "environmentQuality"
                e.dataTransfer.setData('environmentQuality', card.dataset.environmentQuality ?? card.dataset['environment-quality'] ?? 0);
                e.dataTransfer.setData('facilities', card.dataset.facilities ?? 0);
                e.dataTransfer.setData('mobility', card.dataset.mobility ?? 0);

                // Use the card image as the drag ghost
                const img = card.querySelector('img');
                if (img) {
                    e.dataTransfer.setDragImage(img, 25, 25);
                    img.classList.add('grid-drag-image');
                }

                // Fetch which cells are forbidden so dragover can colour them red
                this.#draggingInvalidCells = new Set();
                this.#api.getValidCells(card.dataset.functionId)
                    .then(data => {
                        this.#draggingInvalidCells = new Set(data.invalid.map(String));
                    })
                    .catch(() => {});
            });

            card.addEventListener('dragend', () => {
                this.#draggingInvalidCells = new Set();
            });
        });
    }

    // Attaches drag/drop listeners to each grid cell
    #setupCells(cells) {
        cells.forEach((cell) => {
            // Highlight the cell being hovered during a drag
            cell.addEventListener('dragover', (e) => {
                e.preventDefault();
                cells.forEach((c) => c.classList.remove('ring-4', 'ring-blue-500', 'ring-red-500'));
                const isOccupied = cell.classList.contains('is-occupied');
                const isForbidden = isOccupied || this.#draggingInvalidCells.has(String(cell.dataset.cellId));
                cell.classList.add('ring-4', isForbidden ? 'ring-red-500' : 'ring-blue-500');
            });

            cell.addEventListener('dragleave', () => {
                cell.classList.remove('ring-4', 'ring-blue-500', 'ring-red-500');
            });

            cell.addEventListener('drop', (e) => this.#handleCellDrop(e, cell, cells));

            // SIM.3 - Subtask 1: Allow dragging an occupied cell to the removal zone
            cell.addEventListener('dragstart', (e) => {
                if (!cell.dataset.functionId || cell.dataset.functionId === '') {
                    e.preventDefault(); // Empty cells cannot be dragged
                    return;
                }

                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('cellId', cell.dataset.cellId);
                e.dataTransfer.setData('fromCell', 'true'); // Distinguishes from library drags

                const img = cell.querySelector('img');
                if (img) e.dataTransfer.setDragImage(img, 25, 25);
            });
        });
    }

    // Handles a card or cell being dropped onto a grid cell
    #handleCellDrop(e, cell, cells) {
        e.preventDefault();

        // Drags that started from an occupied cell are only allowed in the removal zone, not on other cells
        if (e.dataTransfer.getData('fromCell') === 'true') return;

        // Occupied cells cannot be replaced — the user must remove the function first
        if (cell.dataset.function && cell.dataset.function !== '') {
            notify('This grid slot already has a city-function');
            return;
        }

        const functionName = e.dataTransfer.getData('function');
        const functionId = e.dataTransfer.getData('function_id');
        const category = e.dataTransfer.getData('category');
        const image = e.dataTransfer.getData('image');
        const qolScore = parseInt(e.dataTransfer.getData('qol_score'), 10);
        const safety = e.dataTransfer.getData('safety') ?? 0;
        const recreation = e.dataTransfer.getData('recreation') ?? 0;
        const environmentQuality = e.dataTransfer.getData('environmentQuality') ?? e.dataTransfer.getData('environment-quality') ?? 0;
        const facilities = e.dataTransfer.getData('facilities') ?? 0;
        const mobility = e.dataTransfer.getData('mobility') ?? 0;
        const cellId = cell.dataset.cellId;

        this.#api.assign(cellId, functionId)
            .then(() => {
                this.#renderFunctionInCell(cell, {
                    functionName, functionId, category, image,
                    safety, recreation, environmentQuality, facilities, mobility,
                });
                this.#qolService.refresh();
                this.#qolService.showToast(functionName, qolScore);
            })
            .catch((error) => notify(error.message || 'Failed to save — please refresh and try again.'));
    }

    // SIM.3 - Subtask 2: Sets up the red drop zone for removing functions
    #setupRemovalZone() {
        const removalZone = document.querySelector('[data-removal-zone]');
        if (!removalZone) return;

        const highlightClasses = ['ring-2', 'ring-red-500', 'bg-red-100', 'dark:bg-red-800/30'];

        removalZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            removalZone.classList.add(...highlightClasses);
        });

        removalZone.addEventListener('dragleave', () => {
            removalZone.classList.remove(...highlightClasses);
        });

        // SIM.3 - Subtask 3: Clear the cell and sync with the backend after a drop
        removalZone.addEventListener('drop', (e) => {
            e.preventDefault();
            removalZone.classList.remove(...highlightClasses);

            // Only accept drags that originated from a grid cell, not the library
            if (e.dataTransfer.getData('fromCell') !== 'true') return;

            const cellId = e.dataTransfer.getData('cellId');
            const cellElement = document.querySelector(`[data-cell-id="${cellId}"]`);

            if (!cellElement) {
                notify('Error: Could not find the cell to remove from.');
                return;
            }

            // SIM.3 - Subtask 5: Send removal request to backend
            this.#api.remove(cellId)
                .then(() => {
                    const functionName = cellElement.dataset.function ?? 'Function';
                    const oldQolScore = parseInt(cellElement.dataset.qolScore ?? '0', 10);

                    this.#clearCell(cellElement);
                    this.#qolService.refresh();
                    // Show the negative impact of the removal
                    this.#qolService.showToast(functionName, -oldQolScore);
                })
                .catch((error) => {
                    console.error('Error removing function:', error);
                    notify('Failed to remove function — please try again.');
                });
        });

        
    }

    // Wires up the undo button; reverts the last assign/remove action
    #setupUndoButton() {
        const undoButton = document.getElementById('undo-button');
        if (!undoButton) return;

        undoButton.addEventListener('click', () => {
            this.#api.undo()
                .then((data) => {
                    const cellElement = document.querySelector(`[data-cell-id="${data.cell.id}"]`);
                    if (!cellElement) return;

                    this.#clearCell(cellElement);

                    // If the server restored a previous function, re-render it
                    if (data.cell.city_function) {
                        const fn = data.cell.city_function;
                        this.#renderFunctionInCell(cellElement, {
                            functionName: fn.name,
                            functionId: data.cell.function_id,
                            image: fn.image_path,
                            category: '',
                            safety: 0, recreation: 0, environmentQuality: 0,
                            facilities: 0, mobility: 0,
                        });
                    }

                    this.#qolService.refresh();
                    this.#qolService.showToast('Action undone', 0);
                })
                .catch(() => notify('Nothing to undo'));
        });
    }

    // Writes a function's image, label, and data attributes into a cell element
    #renderFunctionInCell(cell, { functionName, functionId, category, image, safety, recreation, environmentQuality, facilities, mobility }) {
        cell.innerHTML = '';
        cell.classList.remove('ring-4', 'ring-blue-500', 'ring-red-500', 'border-2', 'border-dashed', 'border-gray-300', 'dark:border-gray-600');

        if (image) {
            const img = document.createElement('img');
            img.src = image;
            img.alt = functionName;
            img.classList.add('mb-1');
            img.draggable = false;
            cell.appendChild(img);
        }

        const label = document.createElement('span');
        label.textContent = functionName;
        label.classList.add('text-xs', 'font-semibold', 'text-center', 'text-black');
        cell.appendChild(label);

        // Make occupied cells keyboard-focusable for accessibility
        cell.setAttribute('tabindex', '0');
        // Provide a helpful aria-label so screen readers announce the cell location and content
        const row = cell.dataset.row ? `Row ${cell.dataset.row}` : 'Row unknown';
        const column = cell.dataset.column ? `column ${cell.dataset.column}` : 'column unknown';
        cell.setAttribute('aria-label', `${row}, ${column}, occupied by ${functionName}${category ? `, category ${category}` : ''}`);

        // Mark as occupied and store all effect values so the hover popup can read them
        cell.classList.remove('is-empty');
        cell.classList.add('is-occupied');
        cell.dataset.function = functionName;
        cell.dataset.functionId = functionId;
        cell.dataset.category = category;
        cell.dataset.safety = safety;
        cell.dataset.recreation = recreation;
        cell.dataset.environmentQuality = environmentQuality;
        cell.dataset.facilities = facilities;
        cell.dataset.mobility = mobility;
    }

    // Resets a cell to its empty state, clearing all content and attributes
    #clearCell(cell) {
        cell.innerHTML = '';
        cell.classList.remove('is-occupied');
        cell.classList.add('is-empty', 'border-2', 'border-dashed', 'border-gray-300', 'dark:border-gray-600');
        
        // Add visual indicator for empty cell
        const indicator = document.createElement('span');
        indicator.textContent = '+';
        indicator.setAttribute('aria-hidden', 'true');
        indicator.className = 'text-gray-400 dark:text-gray-600 text-2xl font-light';
        cell.appendChild(indicator);
        
        cell.dataset.function = '';
        cell.dataset.functionId = '';
        cell.dataset.category = '';
        cell.dataset.safety = '';
        cell.dataset.recreation = '';
        cell.dataset.environmentQuality = '';
        cell.dataset.facilities = '';
        cell.dataset.mobility = '';
        
        // Make empty cells keyboard-accessible with clear aria-label
        cell.setAttribute('tabindex', '0');
        const row = cell.dataset.row ? `Row ${cell.dataset.row}` : 'Row unknown';
        const column = cell.dataset.column ? `column ${cell.dataset.column}` : 'column unknown';
        cell.setAttribute('aria-label', `${row}, ${column}, empty, available for function placement`);
    }
}
