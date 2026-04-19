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
            }).catch(() => {
                alert("Failed to save — please refresh and try again.");
            });
        });
    });

};

document.addEventListener("DOMContentLoaded", initializeCityGrid);
