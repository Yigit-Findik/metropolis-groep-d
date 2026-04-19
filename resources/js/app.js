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

    // Set the text and styling of the toast based on QoL score
    toast.textContent = `${functionName}: ${sign}${qolScore} `;
    toast.className = `fixed bottom-6 right-6 z-50 px-5 py-3 rounded-xl shadow-lg text-white text-sm font-semibold ${
        isPositive ? "bg-green-500" : "bg-red-500"
    }`;

    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => {
        toast.classList.add("hidden");
    }, 3000);
};

const refreshQolScore = () => {
    fetch("/grid/qol-score")
        .then((r) => r.json())
        .then((data) => {
            const el = document.getElementById("qol-score-value");
            if (el) el.textContent = data.total_score;
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
            }).then(() => {
                refreshQolScore();
                showToast(functionName, qolScore);
            }).catch(() => {
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
            removalZone.classList.add("ring-2", "ring-red-500", "bg-red-100", "dark:bg-red-800/30");
        });

        // Remove highlight when dragging leaves the removal zone
        removalZone.addEventListener("dragleave", () => {
            removalZone.classList.remove("ring-2", "ring-red-500", "bg-red-100", "dark:bg-red-800/30");
        });

        // SIM.3 - Subtask 3: Clear Cell After Successful Removal
        // Handle the drop event on removal zone
        removalZone.addEventListener("drop", (e) => {
            e.preventDefault();
            removalZone.classList.remove("ring-2", "ring-red-500", "bg-red-100", "dark:bg-red-800/30");

            // Check if this drag came from a cell (not from the library)
            const fromCell = e.dataTransfer.getData("fromCell");
            if (fromCell !== "true") {
                return; // Ignore drops from library cards
            }

            const cellId = e.dataTransfer.getData("cellId");

            // Find the cell element that we're removing from
            const cellElement = document.querySelector(`[data-cell-id="${cellId}"]`);
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
                        throw new Error(`HTTP error! status: ${response.status}`);
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
