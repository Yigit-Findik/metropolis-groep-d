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

    if (Number.isNaN(row) || Number.isNaN(column)) {
        return [];
    }

    return cells.filter((cell) => {
        if (cell === sourceCell) return false;

        if (!cell.dataset || !cell.dataset.function || cell.dataset.function === '') {
            return false;
        }

        const cellRow = Number.parseInt(cell.dataset.row || "", 10);
        const cellColumn = Number.parseInt(cell.dataset.column || "", 10);

        return (
            (cellRow === row && Math.abs(cellColumn - column) === 1) ||
            (cellColumn === column && Math.abs(cellRow - row) === 1)
        );
    });
};

const formatBadge = (value) => {
    const n = parseInt(value || 0, 10);
    const sign = n > 0 ? `+${n}` : `${n}`;
    const bg = n > 0 ? 'bg-green-500 text-white' : n < 0 ? 'bg-red-500 text-white' : 'bg-gray-300 text-gray-800 dark:bg-gray-600 dark:text-gray-100';
    return `<span class="inline-flex items-center justify-center px-2 py-0.5 rounded-full text-[11px] ${bg}">${sign}</span>`;
};

const setupHoverPopup = () => {
    const popup = createHoverPopup();
    const cells = Array.from(document.querySelectorAll('[data-grid-cell]'));

    const setPopupScale = (el) => {
        const baseSize = 96;
        const cellSize = el.getBoundingClientRect().width || baseSize;
        const scale = Math.max(0.75, Math.min(2.5, cellSize / baseSize));

        popup.style.transformOrigin = 'top left';
        popup.style.transform = `scale(${scale})`;
    };

    const buildHtml = (el) => {
        const ds = el.dataset || {};
        const name = ds.function || '';
        const parts = [];
        parts.push(`<div class="font-semibold mb-1 text-xs">${name}</div>`);

        const mapping = [
            ['safety', 'Saf'],
            ['recreation', 'Rec'],
            ['environmentQuality', 'EnQ'],
            ['facilities', 'Fac'],
            ['mobility', 'Mob'],
        ];

        const badges = mapping.map(([key, label]) => {
            const val = ds[key] ?? 0;
            const b = formatBadge(val);
            return `<div class="flex items-center gap-2"><div class="w-8 text-[10px] text-gray-500 dark:text-gray-400">${label}</div>${b}</div>`;
        }).join('');

        parts.push(`<div class="grid gap-1">${badges}</div>`);
        return parts.join('');
    };

    let visible = false;

    const show = (el, e) => {
        // Only show for occupied grid cells (cells have a non-empty `data-function`)
        if (!el.dataset || !el.dataset.function || el.dataset.function === '') return;

        clearHoverHighlights(cells);
        el.classList.add(...HOVER_HIGHLIGHT_CLASSES);
        setPopupScale(el);

        const neighbors = getOrthogonalNeighbors(cells, el);
        neighbors.forEach((neighbor) => {
            neighbor.classList.add(...NEIGHBOR_HIGHLIGHT_CLASSES);
        });

        popup.innerHTML = buildHtml(el);
        popup.classList.remove('hidden');
        visible = true;
        move(e);
    };

    const hide = () => {
        clearHoverHighlights(cells);
        popup.classList.add('hidden');
        popup.style.transform = '';
        visible = false;
    };

    const move = (e) => {
        if (!visible) return;
        const x = e.clientX + 12;
        const y = e.clientY + 12;
        popup.style.left = `${x}px`;
        popup.style.top = `${y}px`;
    };

    // Attach listeners only to grid cells, not to library cards
    const elements = Array.from(document.querySelectorAll('[data-grid-cell]'));
    elements.forEach((el) => {
        el.addEventListener('mouseenter', (e) => show(el, e));
        el.addEventListener('mousemove', (e) => move(e));
        el.addEventListener('mouseleave', () => hide());
    });
};

// Initialize hover popup after DOM is ready and grid initialized
document.addEventListener('DOMContentLoaded', () => {
    setupHoverPopup();
});
