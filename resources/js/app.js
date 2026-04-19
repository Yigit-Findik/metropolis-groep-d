import "./bootstrap";

import Alpine from "alpinejs";

window.Alpine = Alpine;

Alpine.start();

const initializeCityGrid = () => {
    const grid = document.querySelector("[data-city-grid]");
    const cards = document.querySelectorAll("[data-function]");

    // When you start dragging a library card, save its name, id, and image
    // so we can read them when it gets dropped onto a cell
    cards.forEach((card) => {
        card.addEventListener("dragstart", (e) => {
            e.dataTransfer.setData("function", card.dataset.function);
            e.dataTransfer.setData("function_id", card.dataset.functionId);
            e.dataTransfer.setData("image", card.dataset.image);

            // Use the card's image as the drag ghost
            const img = card.querySelector("img");
            if (img) {
                e.dataTransfer.setDragImage(img, 25, 25);
                img.style.pointerEvents = "none";
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
                c.classList.remove("ring-2", "ring-blue-500");
            });

            cell.classList.add("ring-2", "ring-blue-500");
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
            const cellId = cell.dataset.cellId;

            // Clear whatever was in the cell before and remove the highlight
            cell.innerHTML = "";
            cell.classList.remove("ring-2", "ring-blue-500");

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
            }).catch(() => {
                alert("Failed to save — please refresh and try again.");
            });
        });
    });

    const preview = document.querySelector("[data-selected-cell-preview]");
    const clearButton = document.querySelector("[data-clear-selection]");
    let selectedCell = cells[0] ?? null;
    let hoveredCell = null;

    // Remove the selected style from every cell
    const clearSelectionClasses = () => {
        cells.forEach((cell) => {
            cell.classList.remove("is-selected");
            cell.setAttribute("aria-pressed", "false");
        });
    };

    // Remove all state classes from the preview panel
    const clearPreviewClasses = () => {
        preview?.classList.remove(
            "is-empty",
            "is-occupied",
            "is-selected",
            "is-hover",
        );
    };

    // Update the preview panel to reflect a given cell's state
    const applyPreviewState = (cell, mode) => {
        if (!cell) {
            return;
        }

        if (!preview) {
            return;
        }

        clearPreviewClasses();
        // Copy whether the cell is occupied or empty
        preview.classList.add(
            cell.classList.contains("is-occupied") ? "is-occupied" : "is-empty",
        );
        // Mark as hovered or selected depending on what triggered this
        preview.classList.add(mode === "hover" ? "is-hover" : "is-selected");
    };

    // Highlight the selected cell and update the preview panel
    const renderSelectedCell = () => {
        clearSelectionClasses();

        if (selectedCell) {
            selectedCell.classList.add("is-selected");
            selectedCell.setAttribute("aria-pressed", "true");
            applyPreviewState(selectedCell, "selected");
            return;
        }

        clearPreviewClasses();
        preview?.classList.add("is-empty");
    };

    // Show a temporary hover preview when the mouse is over a cell
    const previewCell = (cell) => {
        hoveredCell = cell;
        applyPreviewState(cell, "hover");
    };

    // Go back to showing the selected cell after the mouse leaves
    const restoreSelectedCell = () => {
        hoveredCell = null;
        renderSelectedCell();
    };

    // Make a cell the active selection
    const setSelection = (cell) => {
        selectedCell = cell;
        hoveredCell = null;
        renderSelectedCell();
    };

    // Attach hover and focus listeners so the preview updates as you move around
    const bindPreviewEvents = (cell) => {
        cell.addEventListener("mouseenter", () => previewCell(cell));
        cell.addEventListener("mouseleave", () => {
            if (hoveredCell === cell) {
                restoreSelectedCell();
            }
        });
        cell.addEventListener("focus", () => previewCell(cell));
        cell.addEventListener("blur", () => {
            if (hoveredCell === cell) {
                restoreSelectedCell();
            }
        });
    };

    cells.forEach(bindPreviewEvents);

    // Select a cell when it is clicked, but block clicks on occupied cells
    grid.addEventListener("click", (event) => {
        const cell = event.target.closest("[data-grid-cell]");

        if (!cell || !grid.contains(cell)) {
            return;
        }

        if (cell.dataset.function !== "") {
            console.log("This location is already occupied!");
            alert("This location is already occupied!");
            return;
        }

        setSelection(cell);
    });

    // Deselect everything when the clear button is pressed
    clearButton?.addEventListener("click", () => {
        selectedCell = null;
        hoveredCell = null;
        clearSelectionClasses();
        clearPreviewClasses();
        renderSelectedCell();
    });

    // Select the first cell by default when the page loads
    if (cells.length > 0) {
        setSelection(cells[0]);
    }
};

document.addEventListener("DOMContentLoaded", initializeCityGrid);
