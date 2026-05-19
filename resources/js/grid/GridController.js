/**
 * Orchestrates the city grid: drag-and-drop from library to cells,
 * removal via the drop zone, and the undo button.
 */
export class GridController {
    #api;
    #qolService;

    constructor(api, qolService) {
        this.#api = api;
        this.#qolService = qolService;
    }

    init() {
        this.#setupLibraryCards();

        const grid = document.querySelector('[data-city-grid]');
        if (!grid) return;

        const cells = Array.from(grid.querySelectorAll('[data-grid-cell]'));
        this.#setupCells(cells);
        this.#setupRemovalZone(cells);
        this.#setupUndoButton();
    }

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
                e.dataTransfer.setData('environmentQuality', card.dataset.environmentQuality ?? card.dataset['environment-quality'] ?? 0);
                e.dataTransfer.setData('facilities', card.dataset.facilities ?? 0);
                e.dataTransfer.setData('mobility', card.dataset.mobility ?? 0);

                const img = card.querySelector('img');
                if (img) {
                    e.dataTransfer.setDragImage(img, 25, 25);
                    img.classList.add('grid-drag-image');
                }
            });
        });
    }

    #setupCells(cells) {
        cells.forEach((cell) => {
            cell.addEventListener('dragover', (e) => {
                e.preventDefault();
                cells.forEach((c) => c.classList.remove('ring-4', 'ring-blue-500'));
                cell.classList.add('ring-4', 'ring-blue-500');
            });

            cell.addEventListener('dragleave', () => {
                cell.classList.remove('ring-4', 'ring-blue-500');
            });

            cell.addEventListener('drop', (e) => this.#handleCellDrop(e, cell, cells));

            // SIM.3 - Subtask 1: Allow dragging occupied cells to the removal zone.
            cell.addEventListener('dragstart', (e) => {
                if (!cell.dataset.functionId || cell.dataset.functionId === '') {
                    e.preventDefault();
                    return;
                }

                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('cellId', cell.dataset.cellId);
                e.dataTransfer.setData('fromCell', 'true');

                const img = cell.querySelector('img');
                if (img) e.dataTransfer.setDragImage(img, 25, 25);
            });
        });
    }

    #handleCellDrop(e, cell, cells) {
        e.preventDefault();

        if (cell.dataset.function && cell.dataset.function !== '') {
            if (!confirm('Are you sure you want to change this function?')) return;
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

        this.#renderFunctionInCell(cell, {
            functionName, functionId, category, image,
            safety, recreation, environmentQuality, facilities, mobility,
        });

        this.#api.assign(cellId, functionId)
            .then(() => {
                this.#qolService.refresh();
                this.#qolService.showToast(functionName, qolScore);
            })
            .catch(() => alert('Failed to save — please refresh and try again.'));
    }

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

        // SIM.3 - Subtask 3: Clear cell after successful removal.
        removalZone.addEventListener('drop', (e) => {
            e.preventDefault();
            removalZone.classList.remove(...highlightClasses);

            if (e.dataTransfer.getData('fromCell') !== 'true') return;

            const cellId = e.dataTransfer.getData('cellId');
            const cellElement = document.querySelector(`[data-cell-id="${cellId}"]`);

            if (!cellElement) {
                alert('Error: Could not find the cell to remove from.');
                return;
            }

            // SIM.3 - Subtask 5: Send removal request to backend.
            this.#api.remove(cellId)
                .then(() => {
                    const functionName = cellElement.dataset.function ?? 'Function';
                    const oldQolScore = parseInt(cellElement.dataset.qolScore ?? '0', 10);

                    this.#clearCell(cellElement);
                    this.#qolService.refresh();
                    this.#qolService.showToast(functionName, -oldQolScore);
                })
                .catch((error) => {
                    console.error('Error removing function:', error);
                    alert('Failed to remove function — please try again.');
                });
        });
    }

    #setupUndoButton() {
        const undoButton = document.getElementById('undo-button');
        if (!undoButton) return;

        undoButton.addEventListener('click', () => {
            this.#api.undo()
                .then((data) => {
                    const cellElement = document.querySelector(`[data-cell-id="${data.cell.id}"]`);
                    if (!cellElement) return;

                    this.#clearCell(cellElement);

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
                .catch(() => alert('Nothing to undo'));
        });
    }

    #renderFunctionInCell(cell, { functionName, functionId, category, image, safety, recreation, environmentQuality, facilities, mobility }) {
        cell.innerHTML = '';
        cell.classList.remove('ring-4', 'ring-blue-500');

        if (image) {
            const img = document.createElement('img');
            img.src = image;
            img.classList.add('mb-1');
            img.draggable = false;
            cell.appendChild(img);
        }

        const label = document.createElement('span');
        label.textContent = functionName;
        label.classList.add('text-xs', 'font-semibold', 'text-center', 'text-black');
        cell.appendChild(label);

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

    #clearCell(cell) {
        cell.innerHTML = '';
        cell.classList.remove('is-occupied');
        cell.classList.add('is-empty');
        cell.dataset.function = '';
        cell.dataset.functionId = '';
        cell.dataset.category = '';
        cell.dataset.safety = '';
        cell.dataset.recreation = '';
        cell.dataset.environmentQuality = '';
        cell.dataset.facilities = '';
        cell.dataset.mobility = '';
    }
}
