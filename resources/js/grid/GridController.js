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
    #selectedFunctionCard = null;
    #selectedFunctionData = null;
    #lastFocusedCell = null;
    #announcer = null;
    #pickedUpCell = null;

    constructor(api, qolService) {
        this.#api = api;
        this.#qolService = qolService;
    }

    // Entry point — call once after DOMContentLoaded
    init() {
        this.#setupLibraryCards();

        const grid = document.querySelector('[data-city-grid]');
        if (!grid) return; // Grid page not loaded, nothing to set up

        // Accessible announcer for screen reader messages
        this.#announcer = document.getElementById('grid-a11y-announcer');

        const cells = Array.from(grid.querySelectorAll('[data-grid-cell]'));
        this.#setupCells(cells);
        // Make arrow keys move into and around the grid when focus is on the grid container
        grid.setAttribute('tabindex', grid.getAttribute('tabindex') || '0');
        grid.addEventListener('keydown', (e) => {
            if (!['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'].includes(e.key)) return;

            const active = document.activeElement;
            // If focus is already on a grid cell, let the cell handle the arrow navigation
            if (active && active.matches && active.matches('[data-grid-cell]')) return;

            e.preventDefault();

            // If we have a last focused cell, start navigation from there; otherwise use the first cell
            const start = this.#lastFocusedCell || cells[0];
            if (!start) return;

            this.#focusAdjacentCell(start, cells, e.key);
        });
        this.#setupRemovalZone();
        this.#setupUndoButton();
        this.#setupApprovalToggles(cells);
        this.#setupApproveAllButton(cells);
        this.#setupRevokeAllButton(cells);
    }

    // Returns true if the cell is approved; shows an error message if so
    #isApproved(cell) {
        if (cell.dataset.approved === 'true') {
            const msg = 'This cell is approved and cannot be modified.';
            notify(msg);
            this.#announce(msg);
            return true;
        }
        return false;
    }

    // Applies the approved visual state to a cell element
    #applyApprovedState(cell) {
        cell.dataset.approved = 'true';
        cell.classList.add('is-approved', 'border-purple-600', 'dark:border-purple-500');
        cell.classList.remove('border-gray-200', 'dark:border-gray-700');

        // Update the approve toggle if present
        const toggle = cell.parentElement?.querySelector('.approve-toggle');
        if (toggle) {
            toggle.textContent = 'lock_open';
            toggle.dataset.approved = 'true';
            toggle.title = 'Revoke approval';
            toggle.setAttribute('aria-label', toggle.getAttribute('aria-label')?.replace('Approve', 'Revoke approval for') ?? 'Revoke approval');
            toggle.classList.remove('text-gray-300', 'hover:text-purple-600', 'border-gray-300');
            toggle.classList.add('text-purple-600', 'hover:text-red-500', 'border-purple-600');
        }

        // Update aria-label on the cell button
        const current = cell.getAttribute('aria-label') ?? '';
        if (!current.includes(', approved')) {
            cell.setAttribute('aria-label', current + ', approved');
        }
    }

    // Removes the approved visual state from a cell element
    #removeApprovedState(cell) {
        cell.dataset.approved = 'false';
        cell.classList.remove('is-approved', 'border-purple-600', 'dark:border-purple-500');
        cell.classList.add('border-gray-200', 'dark:border-gray-700');

        // Update the approve toggle if present
        const toggle = cell.parentElement?.querySelector('.approve-toggle');
        if (toggle) {
            toggle.textContent = 'lock';
            toggle.dataset.approved = 'false';
            toggle.title = 'Approve this cell';
            toggle.classList.remove('text-purple-600', 'hover:text-red-500', 'border-purple-600');
            toggle.classList.add('text-gray-300', 'hover:text-purple-600', 'border-gray-300');
        }

        // Remove ', approved' from aria-label
        const current = cell.getAttribute('aria-label') ?? '';
        cell.setAttribute('aria-label', current.replace(', approved', ''));
    }

    // Wires up the approve/revoke toggle spans inside each cell
    #setupApprovalToggles(cells) {
        cells.forEach((cell) => {
            const toggle = cell.parentElement?.querySelector('.approve-toggle');
            if (!toggle) return;

            toggle.addEventListener('click', (e) => {
                e.stopPropagation(); // Don't trigger the parent cell click
                const cellId = toggle.dataset.cellId;
                const isApproved = toggle.dataset.approved === 'true';

                if (isApproved) {
                    this.#api.revoke(cellId)
                        .then(() => {
                            this.#removeApprovedState(cell);
                            const msg = 'Approval revoked.';
                            notify(msg);
                            this.#announce(msg);
                        })
                        .catch((err) => {
                            const msg = err.message || 'Failed to revoke approval.';
                            notify(msg);
                            this.#announce(msg);
                        });
                } else {
                    this.#api.approve(cellId)
                        .then(() => {
                            this.#applyApprovedState(cell);
                            const msg = 'Cell approved.';
                            notify(msg);
                            this.#announce(msg);
                        })
                        .catch((err) => {
                            const msg = err.message || 'Failed to approve cell.';
                            notify(msg);
                            this.#announce(msg);
                        });
                }
            });
        });
    }

    // Wires up the "Approve All" button
    #setupApproveAllButton(cells) {
        const btn = document.getElementById('approve-all-button');
        if (!btn) return;

        btn.addEventListener('click', () => {
            this.#api.approveAll()
                .then(() => {
                    cells.forEach((cell) => this.#applyApprovedState(cell));
                    const msg = 'All cells approved.';
                    notify(msg);
                    this.#announce(msg);
                })
                .catch((err) => {
                    const msg = err.message || 'Failed to approve all cells.';
                    notify(msg);
                    this.#announce(msg);
                });
        });
    }

    // Wires up the "Disapprove All" button
    #setupRevokeAllButton(cells) {
        const btn = document.getElementById('revoke-all-button');
        if (!btn) return;

        btn.addEventListener('click', () => {
            this.#api.revokeAll()
                .then(() => {
                    cells.forEach((cell) => this.#removeApprovedState(cell));
                    const msg = 'All cells disapproved.';
                    notify(msg);
                    this.#announce(msg);
                })
                .catch((err) => {
                    const msg = err.message || 'Failed to disapprove all cells.';
                    notify(msg);
                    this.#announce(msg);
                });
        });
    }

    // Makes every library card draggable and stores its data in the drag transfer
    #setupLibraryCards() {
        const cards = document.querySelectorAll('[data-library-card]');

        cards.forEach((card) => {
            const selectCard = () => {
                if (this.#selectedFunctionCard === card) {
                    this.#selectedFunctionCard = null;
                    this.#selectedFunctionData = null;
                    card.classList.remove('ring-2', 'ring-blue-500');
                    card.blur();
                    this.#announce(`${card.dataset.function || 'Function'} deselected`);
                    return;
                }

                this.#selectedFunctionCard = card;
                this.#selectedFunctionData = this.#buildFunctionDataFromCard(card);
                this.#announce(`${this.#selectedFunctionData.functionName} selected`);

                cards.forEach((otherCard) => {
                    otherCard.classList.toggle('ring-2', otherCard === card);
                    otherCard.classList.toggle('ring-blue-500', otherCard === card);
                });
            };

            card.addEventListener('click', () => {
                selectCard();
            });

            card.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    selectCard();
                }
            });

            card.addEventListener('dragstart', (e) => {
                const functionData = this.#buildFunctionDataFromCard(card);

                e.dataTransfer.setData('function', functionData.functionName);
                e.dataTransfer.setData('function_id', functionData.functionId);
                e.dataTransfer.setData('category', functionData.category);
                e.dataTransfer.setData('image', functionData.image);
                e.dataTransfer.setData('qol_score', functionData.qolScore);
                e.dataTransfer.setData('safety', functionData.safety);
                e.dataTransfer.setData('recreation', functionData.recreation);
                // dataset normalises "environment-quality" to "environmentQuality"
                e.dataTransfer.setData('environmentQuality', functionData.environmentQuality);
                e.dataTransfer.setData('facilities', functionData.facilities);
                e.dataTransfer.setData('mobility', functionData.mobility);

                // Use the card image as the drag ghost
                const img = card.querySelector('img');
                if (img) {
                    e.dataTransfer.setDragImage(img, 25, 25);
                    img.classList.add('grid-drag-image');
                }

                // Fetch which cells are forbidden so dragover can colour them red
                this.#draggingInvalidCells = new Set();
                this.#api.getValidCells(functionData.functionId)
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
            cell.addEventListener('focus', () => {
                this.#lastFocusedCell = cell;
            });

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

            cell.addEventListener('click', () => {
                // If user has picked up a cell, clicking a target places the picked function there.
                if (this.#pickedUpCell) {
                    this.#placePickedIntoCell(cell);
                    return;
                }

                // Click places a selected library function on empty cells.
                this.#placeSelectedFunction(cell);
            });

            cell.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    // If a cell is currently picked up, place it into the focused cell.
                    if (this.#pickedUpCell) {
                        this.#placePickedIntoCell(cell);
                        return;
                    }

                    // If the cell is occupied, pick it up for keyboard move/removal.
                    if (cell.dataset.function && cell.dataset.function !== '') {
                        this.#pickUpCell(cell);
                        return;
                    }

                    this.#placeSelectedFunction(cell);
                    return;
                }

                if (e.key === 'Delete' || e.key === 'Backspace') {
                    e.preventDefault();
                    this.#removeCell(cell);
                    return;
                }

                if (e.key === 'ArrowUp' || e.key === 'ArrowDown' || e.key === 'ArrowLeft' || e.key === 'ArrowRight') {
                    e.preventDefault();
                    this.#focusAdjacentCell(cell, cells, e.key);
                }
                if (e.key === 'Escape') {
                    e.preventDefault();
                    this.#cancelPickup();
                }
            });

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

        // Approved cells cannot be modified
        if (this.#isApproved(cell)) return;

        // Occupied cells cannot be replaced — the user must remove the function first
        if (cell.dataset.function && cell.dataset.function !== '') {
            const msg = 'This grid slot already has a city-function';
            notify(msg);
            this.#announce(msg);
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
            .catch((error) => {
                const msg = error.message || 'Failed to save — please refresh and try again.';
                notify(msg);
                this.#announce(msg);
            });
    }

    // SIM.3 - Subtask 2: Sets up the red drop zone for removing functions
    #setupRemovalZone() {
        const removalZone = document.querySelector('[data-removal-zone]');
        if (!removalZone) return;

        const highlightClasses = ['ring-2', 'ring-red-500', 'bg-red-100', 'dark:bg-red-800/30'];
        const removeFocusedCell = () => {
            const cellElement = this.#lastFocusedCell?.matches?.('[data-grid-cell]') ? this.#lastFocusedCell : null;
            this.#removeCell(cellElement);
        };

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

            if (this.#isApproved(cellElement)) return;

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
                    const msg = error.message || 'Failed to remove function — please try again.';
                    notify(msg);
                    this.#announce(msg);
                });
        });

        removalZone.addEventListener('click', removeFocusedCell);
        removalZone.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                // If user picked up a cell, confirm removal of that picked cell.
                if (this.#pickedUpCell) {
                    this.#completeRemovalFromPickedCell();
                    return;
                }
                removeFocusedCell();
            }

            if (e.key === 'Escape') {
                e.preventDefault();
                this.#cancelPickup();
            }
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
            img.classList.add('object-contain', 'mb-1', 'flex-shrink-0');
            img.style.width = 'calc(var(--grid-size) * 0.42)';
            img.style.height = 'calc(var(--grid-size) * 0.42)';
            img.draggable = false;
            cell.appendChild(img);
        }

        const label = document.createElement('span');
        label.textContent = functionName;
        label.classList.add('font-semibold', 'text-center', 'text-black', 'w-full', 'leading-tight');
        label.style.fontSize = 'max(6px, calc(var(--grid-size) * 0.07))';
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
        cell.classList.add('is-empty');

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
        // Keep the cell reachable by Tab even when empty.
        const row = cell.dataset.row ? `Row ${cell.dataset.row}` : 'Row unknown';
        const column = cell.dataset.column ? `column ${cell.dataset.column}` : 'column unknown';
        cell.setAttribute('tabindex', '0');
        cell.setAttribute('aria-label', `${row}, ${column}, available`);
    }

    #removeCell(cellElement) {
        if (!cellElement || !cellElement.dataset.functionId) {
            notify('Focus an occupied grid cell first, then press Delete to remove it.');
            return;
        }

        if (this.#isApproved(cellElement)) return;

        this.#api.remove(cellElement.dataset.cellId)
            .then(() => {
                const functionName = cellElement.dataset.function ?? 'Function';
                const oldQolScore = parseInt(cellElement.dataset.qolScore ?? '0', 10);

                // If we removed the currently picked up cell, clear pickup state
                if (this.#pickedUpCell === cellElement) this.#pickedUpCell = null;

                this.#clearCell(cellElement);
                cellElement.blur();
                this.#qolService.refresh();
                this.#qolService.showToast(functionName, -oldQolScore);
            })
            .catch((error) => {
                console.error('Error removing function:', error);
                notify('Failed to remove function — please try again.');
            });
    }

    #pickUpCell(cell) {
        if (!cell || !cell.dataset.functionId) return;
        if (this.#isApproved(cell)) return;
        // Mark visually as picked
        this.#pickedUpCell = cell;
        cell.classList.add('is-picked');
        const name = cell.dataset.function ?? 'Function';
        const msg = `Picked up ${name}. Move to a target cell and press Enter to place, or move to the removal area and press Enter to remove. Press Escape to cancel.`;
        notify(msg);
        this.#announce(msg);
    }

    #completeRemovalFromPickedCell() {
        if (!this.#pickedUpCell) return;
        const cell = this.#pickedUpCell;
        this.#pickedUpCell = null;
        cell.classList.remove('is-picked');
        this.#removeCell(cell);
    }

    #cancelPickup() {
        if (!this.#pickedUpCell) return;
        const name = this.#pickedUpCell.dataset.function ?? 'Function';
        this.#pickedUpCell.classList.remove('is-picked');
        this.#pickedUpCell = null;
        const msg = `Cancelled pick up of ${name}.`;
        notify(msg);
        this.#announce(msg);
    }

    async #placePickedIntoCell(targetCell) {
        if (!this.#pickedUpCell) return;

        const source = this.#pickedUpCell;
        if (source === targetCell) {
            // placing back on same cell — cancel
            this.#cancelPickup();
            return;
        }

        // cannot place onto an approved cell
        if (this.#isApproved(targetCell)) return;

        // cannot place onto an occupied cell
        if (targetCell.dataset.function && targetCell.dataset.function !== '') {
            const msg = 'Cannot place here — target cell is occupied.';
            notify(msg);
            this.#announce(msg);
            return;
        }

        const functionId = source.dataset.functionId;
        if (!functionId) {
            const msg = 'Picked function has no id; canceling.';
            notify(msg);
            this.#announce(msg);
            this.#cancelPickup();
            return;
        }

        // Use assign on the target then remove the source
        try {
            const functionName = source.dataset.function ?? '';
            const qolScore = parseInt(source.dataset.qolScore ?? '0', 10);

            await this.#api.assign(targetCell.dataset.cellId, functionId);

            // Determine image src: prefer an <img> inside the source cell, fall back to dataset
            const imgEl = source.querySelector('img');
            const imageSrc = imgEl ? imgEl.src : (source.dataset.image ?? '');

            // Render into target cell using the source's stored attributes
            this.#renderFunctionInCell(targetCell, {
                functionName: source.dataset.function ?? '',
                functionId: functionId,
                category: source.dataset.category ?? '',
                image: imageSrc,
                safety: source.dataset.safety ?? 0,
                recreation: source.dataset.recreation ?? 0,
                environmentQuality: source.dataset.environmentQuality ?? source.dataset['environment-quality'] ?? 0,
                facilities: source.dataset.facilities ?? 0,
                mobility: source.dataset.mobility ?? 0,
            });

            // Now remove the original
            await this.#api.remove(source.dataset.cellId);
            this.#clearCell(source);

            // Clean up pickup state
            source.classList.remove('is-picked');
            this.#pickedUpCell = null;

            this.#qolService.refresh();
            this.#qolService.showToast(functionName, qolScore);
            const msg = `Moved ${functionName} to the selected cell.`;
            notify(msg);
            this.#announce(msg);
        } catch (err) {
            const msg = (err && err.message) ? err.message : 'Failed to move function.';
            notify(msg);
            this.#announce(msg);
            // Always exit pickup mode on failure to prevent further duplication
            if (this.#pickedUpCell) {
                this.#pickedUpCell.classList.remove('is-picked');
                this.#pickedUpCell = null;
            }
        }
    }

    #placeSelectedFunction(cell) {
        const selected = this.#getSelectedFunctionData();
        if (!selected) {
            notify('Select a function first, then place it on a grid cell.');
            return;
        }

        if (this.#isApproved(cell)) return;

        if (cell.dataset.function && cell.dataset.function !== '') {
            const msg = 'This grid slot already has a city-function';
            notify(msg);
            this.#announce(msg);
            return;
        }

        if (!selected.functionId) {
            notify('The selected function is missing an id. Please reselect it from the library.');
            return;
        }

        this.#api.assign(cell.dataset.cellId, selected.functionId)
            .then(() => {
                this.#renderFunctionInCell(cell, {
                    functionName: selected.functionName,
                    functionId: selected.functionId,
                    category: selected.category,
                    image: selected.image,
                    safety: selected.safety,
                    recreation: selected.recreation,
                    environmentQuality: selected.environmentQuality,
                    facilities: selected.facilities,
                    mobility: selected.mobility,
                });
                this.#qolService.refresh();
                this.#qolService.showToast(selected.functionName, selected.qolScore);
            })
            .catch((error) => {
                const msg = error.message || 'Failed to save — please refresh and try again.';
                notify(msg);
                this.#announce(msg);
            });
    }

    #getSelectedFunctionData() {
        const card = this.#selectedFunctionCard;
        if (!card) return this.#selectedFunctionData;

        const rebuilt = this.#buildFunctionDataFromCard(card);
        this.#selectedFunctionData = rebuilt;
        return rebuilt;
    }

    #buildFunctionDataFromCard(card) {
        const functionId = card.getAttribute('data-function-id') || card.dataset.functionId || '';

        return {
            functionName: card.dataset.function || '',
            functionId,
            category: card.dataset.category ?? '',
            image: card.dataset.image ?? '',
            qolScore: parseInt(card.dataset.qolScore ?? '0', 10),
            safety: card.dataset.safety ?? 0,
            recreation: card.dataset.recreation ?? 0,
            environmentQuality: card.dataset.environmentQuality ?? card.dataset['environment-quality'] ?? 0,
            facilities: card.dataset.facilities ?? 0,
            mobility: card.dataset.mobility ?? 0,
        };
    }

    // Announce messages to screen readers via a hidden aria-live region
    #announce(message) {
        if (!this.#announcer) return;
        try {
            // Clear and re-set to ensure screen readers announce repeated messages
            this.#announcer.textContent = '';
            setTimeout(() => { this.#announcer.textContent = message; }, 50);
        } catch (err) {
            // Ignore announcer failures
        }
    }

    #focusAdjacentCell(cell, cells, key) {
        const parse = (v) => Number.parseInt(v ?? '', 10);

        // Build a map of rows -> columns -> cell element for deterministic neighbor lookup
        const rowsMap = new Map();
        cells.forEach((c) => {
            const r = parse(c.dataset.row);
            const col = parse(c.dataset.column);
            if (!Number.isFinite(r) || !Number.isFinite(col)) return;
            if (!rowsMap.has(r)) rowsMap.set(r, new Map());
            rowsMap.get(r).set(col, c);
        });

        const row = parse(cell.dataset.row);
        const column = parse(cell.dataset.column);
        if (!Number.isFinite(row) || !Number.isFinite(column)) return;

        if (key === 'ArrowUp') {
            const targetRow = row - 1;
            const rowMap = rowsMap.get(targetRow);
            const target = rowMap?.get(column);
            if (target) target.focus();
            return;
        }

        if (key === 'ArrowDown') {
            const targetRow = row + 1;
            const rowMap = rowsMap.get(targetRow);
            const target = rowMap?.get(column);
            if (target) target.focus();
            return;
        }

        if (key === 'ArrowLeft' || key === 'ArrowRight') {
            const rowMap = rowsMap.get(row);
            if (!rowMap) return;

            // Get sorted columns in this row
            const cols = Array.from(rowMap.keys()).sort((a, b) => a - b);
            const idx = cols.indexOf(column);
            if (idx === -1) return;

            if (key === 'ArrowLeft' && idx > 0) {
                const targetCol = cols[idx - 1];
                const target = rowMap.get(targetCol);
                if (target) target.focus();
            }

            if (key === 'ArrowRight' && idx < cols.length - 1) {
                const targetCol = cols[idx + 1];
                const target = rowMap.get(targetCol);
                if (target) target.focus();
            }
        }
    }
}
