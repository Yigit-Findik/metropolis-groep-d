import "./bootstrap";

import Alpine from "alpinejs";

window.Alpine = Alpine;

Alpine.start();

let toastTimer = null;

const showToast = (functionName, qolScore) => {
    const toast = document.getElementById("qol-toast");
    if (!toast) return;

    const isPositive = qolScore >= 0;
    const sign = isPositive ? "+" : "";

    // Keep the visual styling in CSS and only switch between semantic modifier classes here.
    toast.textContent = `${functionName}: ${sign}${qolScore} `;
    toast.className = `qol-toast ${isPositive ? "qol-toast--positive" : "qol-toast--negative"}`;

    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => {
        toast.classList.add("hidden");
    }, 3000);
};

const refreshQolScore = () => {
    fetch("/grid/qol-score")
        .then((r) => r.json())
        .then((data) => {
            const total = document.getElementById("qol-score-value");
            if (total) total.textContent = data.total_score;

            if (data.categories) {
                for (const [cat, score] of Object.entries(data.categories)) {
                    // Convert category names to match HTML IDs (replace spaces with dashes)
                    const elementId = `qol-${cat.replace(/\s+/g, '-')}`;
                    const el = document.getElementById(elementId);
                    if (el) {
                        el.textContent = (score >= 0 ? "+" : "") + score;
                        el.className = `qol-score-value ${score >= 0 ? "qol-score-value--positive" : "qol-score-value--negative"}`;
                    }
                }
            }
        })
        .catch(() => {});
};

const initializeCityGrid = () => {
    const grid = document.querySelector("[data-city-grid]");
    const cards = document.querySelectorAll("[data-function]");

    // Get the removal zone element (SIM.3 - Subtask 2: Define a Drop Zone Outside the Grid)
    const removalZone = document.querySelector("[data-removal-zone]");

    // When you start dragging a library card, save its name, id, and image
    // so we can read them when it gets dropped onto a cell
    cards.forEach((card) => {
        card.addEventListener("dragstart", (e) => {
            e.dataTransfer.setData("function", card.dataset.function);
            e.dataTransfer.setData("function_id", card.dataset.functionId);
            e.dataTransfer.setData("category", card.dataset.category ?? "");
            e.dataTransfer.setData("image", card.dataset.image);
            e.dataTransfer.setData("qol_score", card.dataset.qolScore);
            // Also include per-category effect values so the cell can show them after drop
            e.dataTransfer.setData("safety", card.dataset.safety ?? 0);
            e.dataTransfer.setData("recreation", card.dataset.recreation ?? 0);
            e.dataTransfer.setData("environmentQuality", card.dataset.environmentQuality ?? card.dataset['environment-quality'] ?? 0);
            e.dataTransfer.setData("facilities", card.dataset.facilities ?? 0);
            e.dataTransfer.setData("mobility", card.dataset.mobility ?? 0);

            // Use the card's image as the drag ghost
            const img = card.querySelector("img");
            if (img) {
                e.dataTransfer.setDragImage(img, 25, 25);
                img.classList.add("grid-drag-image");
            }
        });
    });

    // Stop here if there is no grid on the page
    if (!grid) {
        return;
    }

    const cells = Array.from(grid.querySelectorAll("[data-grid-cell]"));
    cells.forEach((cell) => {
        // Highlight the cell the user is dragging over
        cell.addEventListener("dragover", (e) => {
            e.preventDefault();

            // Remove the highlight from all other cells first
            cells.forEach((c) => {
                c.classList.remove("ring-4", "ring-blue-500");
            });

            cell.classList.add("ring-4", "ring-blue-500");
        });

        // Remove the highlight when the drag leaves this cell
        cell.addEventListener("dragleave", () => {
            cell.classList.remove("ring-4", "ring-blue-500");
        });

        // Fires when a dragged card is released onto a cell
        cell.addEventListener("drop", (e) => {
            e.preventDefault();

            // Ask the user to confirm if the cell already has a function
            if (cell.dataset.function && cell.dataset.function !== "") {
                const confirmChange = confirm(
                    "Are you sure you want to change this function?",
                );
                if (!confirmChange) return;
            }

            // Read the data that was stored when the drag started
            const functionName = e.dataTransfer.getData("function");
            const functionId = e.dataTransfer.getData("function_id");
            const category = e.dataTransfer.getData("category");
            const image = e.dataTransfer.getData("image");
            const qolScore = parseInt(e.dataTransfer.getData("qol_score"), 10);
            const safety = e.dataTransfer.getData("safety") ?? 0;
            const recreation = e.dataTransfer.getData("recreation") ?? 0;
            const environmentQuality = e.dataTransfer.getData("environmentQuality") ?? e.dataTransfer.getData("environment-quality") ?? 0;
            const facilities = e.dataTransfer.getData("facilities") ?? 0;
            const mobility = e.dataTransfer.getData("mobility") ?? 0;
            const cellId = cell.dataset.cellId;

            // Clear whatever was in the cell before and remove the highlight
            cell.innerHTML = "";
            cell.classList.remove("ring-4", "ring-blue-500");

            // Show the function's image inside the cell
            if (image) {
                const img = document.createElement("img");
                img.src = image;
                img.classList.add("mb-1");
                img.draggable = false;
                cell.appendChild(img);
            }

            // Show the function's name below the image
            const label = document.createElement("span");
            label.textContent = functionName;
            label.classList.add(
                "text-xs",
                "font-semibold",
                "text-center",
                "text-black",
            );
            cell.appendChild(label);

            // Mark the cell as occupied and store the function data on the element
            cell.classList.remove("is-empty");
            cell.classList.add("is-occupied");
            cell.dataset.function = functionName;
            cell.dataset.functionId = functionId;
            cell.dataset.category = category;
            // Store individual effect values on the cell so the hover popup can read them
            cell.dataset.safety = safety;
            cell.dataset.recreation = recreation;
            // DOM dataset maps data-environment-quality to environmentQuality
            cell.dataset.environmentQuality = environmentQuality;
            cell.dataset.facilities = facilities;
            cell.dataset.mobility = mobility;

            // Send the assignment to the server so it is saved in the database
            const csrfToken = document
                .querySelector('meta[name="csrf-token"]')
                ?.getAttribute("content");
            fetch(`/grid/${cellId}/assign`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                },
                body: JSON.stringify({ function_id: parseInt(functionId) }),
            })
                .then(() => {
                    refreshQolScore();
                    showToast(functionName, qolScore);
                })
                .catch(() => {
                    alert("Failed to save — please refresh and try again.");
                });
        });

        // SIM.3 - Subtask 1: Add Drag-Off Detection to Grid Cells
        // Listen for when user starts dragging a PLACED function (inside a cell) to remove it
        cell.addEventListener("dragstart", (e) => {
            // Only allow dragging if the cell is occupied (has a function)
            if (!cell.dataset.functionId || cell.dataset.functionId === "") {
                e.preventDefault();
                return;
            }

            // Store cell information so we know which cell to remove from
            e.dataTransfer.effectAllowed = "move";
            e.dataTransfer.setData("cellId", cell.dataset.cellId);
            e.dataTransfer.setData("fromCell", "true"); // Flag to indicate this is from a cell (not library)

            // Show a subtle drag image
            const img = cell.querySelector("img");
            if (img) {
                e.dataTransfer.setDragImage(img, 25, 25);
            }
        });
    });

    // SIM.3 - Subtask 2: Define a Drop Zone Outside the Grid (removal zone setup)
    if (removalZone) {
        // Allow dragging over the removal zone
        removalZone.addEventListener("dragover", (e) => {
            e.preventDefault();
            e.dataTransfer.dropEffect = "move";

            // Visual feedback: highlight the removal zone when dragging over it
            removalZone.classList.add(
                "ring-2",
                "ring-red-500",
                "bg-red-100",
                "dark:bg-red-800/30",
            );
        });

        // Remove highlight when dragging leaves the removal zone
        removalZone.addEventListener("dragleave", () => {
            removalZone.classList.remove(
                "ring-2",
                "ring-red-500",
                "bg-red-100",
                "dark:bg-red-800/30",
            );
        });

        // SIM.3 - Subtask 3: Clear Cell After Successful Removal
        // Handle the drop event on removal zone
        removalZone.addEventListener("drop", (e) => {
            e.preventDefault();
            removalZone.classList.remove(
                "ring-2",
                "ring-red-500",
                "bg-red-100",
                "dark:bg-red-800/30",
            );

            // Check if this drag came from a cell (not from the library)
            const fromCell = e.dataTransfer.getData("fromCell");
            if (fromCell !== "true") {
                return; // Ignore drops from library cards
            }

            const cellId = e.dataTransfer.getData("cellId");

            // Find the cell element that we're removing from
            const cellElement = document.querySelector(
                `[data-cell-id="${cellId}"]`,
            );
            if (!cellElement) {
                alert("Error: Could not find the cell to remove from.");
                return;
            }

            // SIM.3 - Subtask 5: Send Removal Request to Backend
            // Send DELETE request to backend to remove the function from database
            const csrfToken = document
                .querySelector('meta[name="csrf-token"]')
                ?.getAttribute("content");

            fetch(`/grid/${cellId}/remove`, {
                method: "DELETE",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                },
            })
                .then((response) => {
                    if (!response.ok) {
                        throw new Error(
                            `HTTP error! status: ${response.status}`,
                        );
                    }
                    return response.json();
                })
                .then((data) => {
                    // Success! Now clear the cell visually
                    // Clear the cell's inner HTML to remove the image and label
                    cellElement.innerHTML = "";

                    // Update CSS classes: mark as empty, remove occupied
                    cellElement.classList.remove("is-occupied");
                    cellElement.classList.add("is-empty");

                    // Clear the data attributes
                    cellElement.dataset.function = "";
                    cellElement.dataset.functionId = "";
                    cellElement.dataset.category = "";
                    cellElement.dataset.safety = "";
                    cellElement.dataset.recreation = "";
                    cellElement.dataset.environmentQuality = "";
                    cellElement.dataset.facilities = "";
                    cellElement.dataset.mobility = "";

                    refreshQolScore();
                })
                .catch((error) => {
                    console.error("Error removing function:", error);
                    alert("Failed to remove function — please try again.");
                });
        });
    }
};

document.addEventListener("DOMContentLoaded", () => {
    initializeCityGrid();
    refreshQolScore();
});

// Hover popup: show small effect badges when hovering any element with `data-function`
const createHoverPopup = () => {
    let popup = document.getElementById("function-hover-popup");
    if (popup) return popup;

    popup = document.createElement("div");
    popup.id = "function-hover-popup";
    popup.style.position = "fixed";
    popup.style.pointerEvents = "none";
    popup.style.zIndex = "9999";
    popup.className = "hidden bg-white dark:bg-gray-800 text-xs rounded-md shadow-lg p-2 text-gray-900 dark:text-gray-100";
    document.body.appendChild(popup);
    return popup;
};

const HOVER_HIGHLIGHT_CLASSES = [
    "ring-4",
    "ring-amber-400",
    "bg-amber-50",
    "dark:bg-amber-400/10",
];

const NEIGHBOR_HIGHLIGHT_CLASSES = [
    "ring-2",
    "ring-amber-300",
    "bg-amber-50/70",
    "dark:bg-amber-400/5",
];

const clearHoverHighlights = (cells) => {
    cells.forEach((cell) => {
        cell.classList.remove(...HOVER_HIGHLIGHT_CLASSES, ...NEIGHBOR_HIGHLIGHT_CLASSES);
    });
};

const getOrthogonalNeighbors = (cells, sourceCell) => {
    const row = Number.parseInt(sourceCell.dataset.row || "", 10);
    const column = Number.parseInt(sourceCell.dataset.column || "", 10);
    const sourceCategory = sourceCell.dataset.category || "";

    if (Number.isNaN(row) || Number.isNaN(column)) {
        return [];
    }

    const neighbors = [];

    cells.forEach((cell) => {
        if (cell === sourceCell) return false;

        if (!cell.dataset || !cell.dataset.function || cell.dataset.function === '') {
            return;
        }

        const cellRow = Number.parseInt(cell.dataset.row || "", 10);
        const cellColumn = Number.parseInt(cell.dataset.column || "", 10);

        const rowDelta = cellRow - row;
        const columnDelta = cellColumn - column;
        const isOrthogonal =
            (cellRow === row && Math.abs(columnDelta) === 1) ||
            (cellColumn === column && Math.abs(rowDelta) === 1);

        if (!isOrthogonal) return;

        const sameCategory = sourceCategory !== "" && sourceCategory === (cell.dataset.category || "");
        const bonus = sameCategory ? 2 : 0;
        const direction = rowDelta === -1
            ? "top"
            : rowDelta === 1
                ? "bottom"
                : columnDelta === -1
                    ? "left"
                    : "right";

        neighbors.push({
            cell,
            direction,
            bonus,
            sameCategory,
        });
    });

    return neighbors;
};

const formatBadge = (value) => {
    const n = parseInt(value || 0, 10);
    const sign = n > 0 ? `+${n}` : `${n}`;
    const bg = n > 0 ? 'bg-green-500 text-white' : n < 0 ? 'bg-red-500 text-white' : 'bg-gray-300 text-gray-800 dark:bg-gray-600 dark:text-gray-100';
    return `<span class="inline-flex items-center justify-center px-2 py-0.5 rounded-full text-[11px] ${bg}">${sign}</span>`;
};

const setupHoverPopup = () => {
    const popup = createHoverPopup();
    const grid = document.querySelector('[data-city-grid]');
    let activeCell = null;
    const BASE_CELL_SIZE = 96;

    const getCells = () => Array.from(document.querySelectorAll('[data-grid-cell]'));
    // Only these function names receive penalty badges and adjusted popup values.
    const SENSITIVE_FUNCTIONS = new Set(['park', 'school', 'hospital']);

    // Polluting function names (lowercase) that cause penalties when adjacent
    const POLLUTERS = ['road', 'store', 'gas station'];

    const setPopupScale = (el) => {
        const cellSize = el.getBoundingClientRect().width || BASE_CELL_SIZE;
        const scale = Math.max(0.75, Math.min(2.5, cellSize / BASE_CELL_SIZE));

        popup.style.transformOrigin = 'top left';
        popup.style.transform = `scale(${scale})`;
        return scale;
    };

    const getCategoryKey = (category) => {
        const normalized = (category || '').trim().toLowerCase();
        if (normalized === 'environment quality') return 'environmentQuality';
        return normalized;
    };

    const createBadgeForCell = ({ cell, amount }) => {
        const id = cell.dataset.cellId || cell.getAttribute('data-cell-id') || '';
        const existing = document.querySelector(`.bonus-badge[data-target="${id}"]`);
        if (existing) existing.remove();

        const badge = document.createElement('div');
        const isPositive = amount > 0;
        badge.className = `bonus-badge ${isPositive ? 'bonus-badge--positive' : 'bonus-badge--negative'}`;
        badge.textContent = isPositive ? `+${amount}` : `${amount}`;
        badge.setAttribute('data-target', id);
        badge.style.position = 'fixed';
        badge.style.zIndex = 60;
        badge.style.pointerEvents = 'auto'; // allow hover so popup stays visible when moving into badge

        // Position on the sensitive cell itself so the penalty is easy to see.
        const rect = cell.getBoundingClientRect();
        const left = rect.left + rect.width * 0.68;
        const top = rect.top - rect.height * 0.12;

        badge.style.left = `${Math.round(left)}px`;
        badge.style.top = `${Math.round(top)}px`;

        // Scale badge to match grid scaling based on neighbor cell size
        const cellSize = rect.width || BASE_CELL_SIZE;
        const scale = Math.max(0.75, Math.min(2.5, cellSize / BASE_CELL_SIZE));
        badge.style.transformOrigin = 'top left';
        badge.style.transform = `scale(${scale})`;

        document.body.appendChild(badge);
        return badge;
    };

    const updateBonusBadges = (activeCell) => {
        // Remove existing badges then recreate
        document.querySelectorAll('.bonus-badge').forEach((n) => n.remove());
        if (!activeCell) return;

        const cells = getCells();
        const activeCategory = getCategoryKey(activeCell.dataset.category);

        const sameCategoryNeighbors = getOrthogonalNeighbors(cells, activeCell)
            .map((neighbor) => neighbor.cell)
            .filter((cell) => getCategoryKey(cell.dataset.category) === activeCategory);

        sameCategoryNeighbors.forEach((cell) => {
            createBadgeForCell({ cell, amount: 2 });
        });

        const sensitiveNeighbors = getOrthogonalNeighbors(cells, activeCell)
            .map((neighbor) => neighbor.cell)
            .filter((cell) => SENSITIVE_FUNCTIONS.has((cell.dataset.function || '').trim().toLowerCase()));

        sensitiveNeighbors.forEach((cell) => {
            const penalty = getOrthogonalNeighbors(cells, cell).filter((neighbor) => {
                const fn = (neighbor.cell.dataset.function || '').trim().toLowerCase();
                return POLLUTERS.some((p) => fn.includes(p));
            }).length * 2;

            if (penalty > 0) {
                createBadgeForCell({ cell, amount: -penalty });
            }
        });
    };

    const removeBonusBadges = () => {
        document.querySelectorAll('.bonus-badge').forEach((n) => n.remove());
    };

    const buildHtml = (el) => {
        const ds = el.dataset || {};
        const name = ds.function || '';
        const category = ds.category || 'Uncategorized';
        // Compute sensitive penalties from orthogonal polluting neighbors
        const orthNeighbors = getOrthogonalNeighbors(getCells(), el).map(n => n.cell);
        const pollutingCount = orthNeighbors.filter((c) => {
            const fn = (c.dataset.function || '').trim().toLowerCase();
            return POLLUTERS.some((p) => fn.includes(p));
        }).length;
        const sensitiveKey = SENSITIVE_FUNCTIONS.has((name || '').trim().toLowerCase())
            ? getCategoryKey(ds.category)
            : null;
        const penalty = pollutingCount * 2;
        const bonusCount = getOrthogonalNeighbors(getCells(), el)
            .filter((neighbor) => getCategoryKey(neighbor.cell.dataset.category) === getCategoryKey(ds.category))
            .length;
        const bonus = bonusCount * 2;
        const parts = [];
        parts.push(`<div class="font-semibold mb-1 text-xs">${name}</div>`);
        parts.push(`<div class="mb-2 inline-flex items-center rounded-full bg-blue-100 px-2 py-0.5 text-[11px] font-semibold text-blue-700 dark:bg-blue-900/40 dark:text-blue-100">${category}</div>`);

        const mapping = [
            ['safety', 'Saf'],
            ['recreation', 'Rec'],
            ['environmentQuality', 'EnQ'],
            ['facilities', 'Fac'],
            ['mobility', 'Mob'],
        ];

        const badges = mapping.map(([key, label]) => {
            let val = parseInt(ds[key] ?? 0, 10);
            if (sensitiveKey && key === sensitiveKey && penalty > 0) {
                val = val - penalty;
            } else if (key === getCategoryKey(ds.category) && bonus > 0) {
                val = val + bonus;
            }
            const b = formatBadge(val);
            return `<div class="flex items-center gap-2"><div class="w-8 text-[10px] text-gray-500 dark:text-gray-400">${label}</div>${b}</div>`;
        }).join('');

        parts.push(`<div class="grid gap-1">${badges}</div>`);

        // NOTE: Orthogonal bonuses are shown as badges attached to the neighbor cells
        // rather than inside this popup. This keeps the popup focused on the hovered
        // function's own effects. The badge rendering is handled elsewhere in the
        // hover flow so we don't add bonus rows here.
        return parts.join('');
    };

    let visible = false;

    const show = (el, e) => {
        // Only show for occupied grid cells (cells have a non-empty `data-function`)
        if (!el.dataset || !el.dataset.function || el.dataset.function === '') return;

        if (activeCell === el && !popup.classList.contains('hidden')) {
            setPopupScale(el);
            move(e);
            return;
        }

        activeCell = el;

        const cells = getCells();
        clearHoverHighlights(cells);
        el.classList.add(...HOVER_HIGHLIGHT_CLASSES);
        setPopupScale(el);

        const neighbors = getOrthogonalNeighbors(cells, el);
        neighbors.forEach(({ cell: neighbor }) => {
            neighbor.classList.add(...NEIGHBOR_HIGHLIGHT_CLASSES);
        });

        // Render bonus badges on the hovered cell and penalty badges on adjacent sensitive cells.
        updateBonusBadges(el);

        popup.innerHTML = buildHtml(el);
        popup.classList.remove('hidden');
        visible = true;
        move(e);
    };

    const hide = () => {
        clearHoverHighlights(getCells());
        popup.classList.add('hidden');
        popup.style.transform = '';
        removeBonusBadges();
        activeCell = null;
        visible = false;
    };

    const move = (e) => {
        if (!visible) return;
        const x = e.clientX + 12;
        const y = e.clientY + 12;
        popup.style.left = `${x}px`;
        popup.style.top = `${y}px`;
    };

    // Attach listeners through the grid so changes to the cell DOM keep working without re-binding.
    if (!grid) return;

    grid.addEventListener('mouseover', (e) => {
        const el = e.target.closest('[data-grid-cell]');
        if (!el || !grid.contains(el)) return;
        show(el, e);
    });

    grid.addEventListener('mousemove', (e) => {
        if (!visible) return;
        move(e);
        // Reposition badges dynamically while moving
        if (activeCell) {
            updateBonusBadges(activeCell);
            // Rebuild popup contents to reflect any dynamic penalties
            popup.innerHTML = buildHtml(activeCell);
        }
    });

    grid.addEventListener('mouseout', (e) => {
        const relatedTarget = e.relatedTarget;
        if (relatedTarget && relatedTarget.closest('[data-grid-cell]')?.dataset?.function) {
            // Moving straight into another occupied cell will trigger mouseover on that cell.
            return;
        }

        const el = e.target.closest('[data-grid-cell]');
        if (!el || !grid.contains(el)) return;

        hide();
    });
};

// Initialize hover popup after DOM is ready and grid initialized
document.addEventListener('DOMContentLoaded', () => {
    setupHoverPopup();
});
