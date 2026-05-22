// Tracks the active category filter in the function library dropdown
export const functionLibrary = () => ({
    active: 'All', // 'All' shows every function; any other value filters by category name
    highlightedCells: [],

    getAdjacentCells(row, col) {
        const positions = [
            { r: row - 1, c: col },  // up
            { r: row + 1, c: col },  // down
            { r: row, c: col - 1 },  // left
            { r: row, c: col + 1 },  // right
        ];
        return positions;
    },

    highlightCells(functionId, cardElement) {
        this.clearHighlights();
        
        const conditions = JSON.parse(cardElement.dataset.conditions || '[]');
        const gridCells = document.querySelectorAll('[data-grid-cell]');

        gridCells.forEach(cell => {
            const row = parseInt(cell.dataset.row);
            const col = parseInt(cell.dataset.column);
            const cellFunctionId = cell.dataset.functionId;

            // Skip if cell is occupied
            if (cellFunctionId) return;

            const adjacent = this.getAdjacentCells(row, col);
            let shouldHighlight = false;
            let highlightClass = '';

            // Check conditions
            for (let condition of conditions) {
                const neighbors = this.getNeighborFunctionIds(adjacent);

                if (condition.type === 'forbidden') {
                    // Check if forbidden function is in neighbors
                    if (neighbors.includes(condition.target_function_id)) {
                        shouldHighlight = true;
                        highlightClass = 'bg-red-400 dark:bg-red-500';
                        break;
                    }
                } else if (condition.type === 'required') {
                    // Check if required function is NOT in neighbors
                    if (!neighbors.includes(condition.target_function_id)) {
                        shouldHighlight = true;
                        highlightClass = 'bg-yellow-400 dark:bg-yellow-500';
                    }
                }
            }

            if (shouldHighlight) {
                cell.classList.add(...highlightClass.split(' '));
                this.highlightedCells.push(cell);
            }
        });
    },

    getNeighborFunctionIds(adjacentPositions) {
        const ids = [];
        const gridCells = document.querySelectorAll('[data-grid-cell]');
        
        adjacentPositions.forEach(pos => {
            gridCells.forEach(cell => {
                if (parseInt(cell.dataset.row) === pos.r && parseInt(cell.dataset.column) === pos.c) {
                    const fnId = cell.dataset.functionId;
                    if (fnId) ids.push(parseInt(fnId));
                }
            });
        });
        
        return ids;
    },

    clearHighlights() {
        this.highlightedCells.forEach(cell => {
            cell.classList.remove('bg-red-400', 'dark:bg-red-500', 'bg-yellow-400', 'dark:bg-yellow-500');
        });
        this.highlightedCells = [];
    },
});
