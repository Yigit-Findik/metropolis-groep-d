import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

const initializeCityGrid = () => {
	const grid = document.querySelector('[data-city-grid]');
	const cards = document.querySelectorAll('[data-function]');
	
	cards.forEach(card => {
		card.addEventListener("dragstart", (e) => {
			e.dataTransfer.setData("function", card.dataset.function);
			e.dataTransfer.setData("image", card.dataset.image);

			const img = card.querySelector("img");
			if (img) {
				e.dataTransfer.setDragImage(img, 25, 25);
				img.style.pointerEvents = "none";
			}
		});
	});

	if (!grid) {
		return;
	}

	const cells = Array.from(grid.querySelectorAll('[data-grid-cell]'));
	cells.forEach(cell => {

		cell.addEventListener("dragover", (e) => {
			e.preventDefault();

			cells.forEach(c => {
				c.classList.remove("ring-2", "ring-blue-500");
			});

			cell.classList.add("ring-2", "ring-blue-500");
		});

		cell.addEventListener("drop", (e) => {
			e.preventDefault();

			if (cell.dataset.function && cell.dataset.function !== "") {
				const confirmChange = confirm("Are you sure you want to change this function?");
				if (!confirmChange) return;
			}

			const functionName = e.dataTransfer.getData("function");
			const image = e.dataTransfer.getData("image");

			cell.innerHTML = "";

			cell.classList.remove("ring-2", "ring-blue-500");

			if (image) {
				const img = document.createElement("img");
				img.src = image;
				img.classList.add("w-10", "h-10", "object-contain");

				img.draggable = false;

				cell.appendChild(img);
			}

			const label = document.createElement("span");
			label.textContent = functionName;
			label.classList.add("text-xs", "font-semibold");
			cell.appendChild(label);


			cell.classList.remove("is-empty");
			cell.classList.add("is-occupied");

			cell.dataset.function = functionName;

		});

	});


	const preview = document.querySelector('[data-selected-cell-preview]');
	const clearButton = document.querySelector('[data-clear-selection]');
	let selectedCell = cells[0] ?? null;
	let hoveredCell = null;


	const clearSelectionClasses = () => {
		cells.forEach((cell) => {
			cell.classList.remove('is-selected');
			cell.setAttribute('aria-pressed', 'false');
		});
	};

	const clearPreviewClasses = () => {
		preview?.classList.remove('is-empty', 'is-occupied', 'is-selected', 'is-hover');
	};

	const applyPreviewState = (cell, mode) => {
		if (!cell) {
			return;
		}

		if (!preview) {
			return;
		}

		clearPreviewClasses();
		preview.classList.add(cell.classList.contains('is-occupied') ? 'is-occupied' : 'is-empty');
		preview.classList.add(mode === 'hover' ? 'is-hover' : 'is-selected');
	};

	const renderSelectedCell = () => {
		clearSelectionClasses();

		if (selectedCell) {
			selectedCell.classList.add('is-selected');
			selectedCell.setAttribute('aria-pressed', 'true');
			applyPreviewState(selectedCell, 'selected');
			return;
		}

		clearPreviewClasses();
		preview?.classList.add('is-empty');
	};

	const previewCell = (cell) => {
		hoveredCell = cell;
		applyPreviewState(cell, 'hover');
	};

	const restoreSelectedCell = () => {
		hoveredCell = null;
		renderSelectedCell();
	};

	const setSelection = (cell) => {
		selectedCell = cell;
		hoveredCell = null;
		renderSelectedCell();
	};

	const bindPreviewEvents = (cell) => {
		cell.addEventListener('mouseenter', () => previewCell(cell));
		cell.addEventListener('mouseleave', () => {
			if (hoveredCell === cell) {
				restoreSelectedCell();
			}
		});
		cell.addEventListener('focus', () => previewCell(cell));
		cell.addEventListener('blur', () => {
			if (hoveredCell === cell) {
				restoreSelectedCell();
			}
		});
	};

	cells.forEach(bindPreviewEvents);

	grid.addEventListener('click', (event) => {
		const cell = event.target.closest('[data-grid-cell]');

		if (!cell || !grid.contains(cell)) {
			return;
		}

		// Validation, checks if the cell has a name already

		if (cell.dataset.function !== ""){
			console.log("Oeps! Deze locatie is al bezet");
			alert("Deze plek is al bezet!");
			return;
		}

		// end validation

		setSelection(cell);
	});

	clearButton?.addEventListener('click', () => {
		selectedCell = null;
		hoveredCell = null;
		clearSelectionClasses();
		clearPreviewClasses();
		renderSelectedCell();
	});

	if (cells.length > 0) {
		setSelection(cells[0]);
	}
};

document.addEventListener('DOMContentLoaded', initializeCityGrid);
