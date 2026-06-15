// Tracks the active category filter and text search in the function library
export const functionLibrary = (functions = []) => ({
    active: 'All', // 'All' shows every function; any other value filters by category name
    searchTerm: '',
    highlightedCells: [],
    noResultsAnnouncement: '',
    noResultsAnnouncementTimeout: null,
    functions,

    normalizeSearch(value) {
        return String(value ?? '').trim().toLowerCase();
    },

    matchesSearch(functionName, functionCategory) {
        const searchTerm = this.normalizeSearch(this.searchTerm);

        if (!searchTerm) {
            return true;
        }

        return this.normalizeSearch(functionName).includes(searchTerm)
            || this.normalizeSearch(functionCategory).includes(searchTerm);
    },

    isVisible(functionName, functionCategory) {
        const categoryMatches = this.active === 'All' || this.active === functionCategory;

        return categoryMatches && this.matchesSearch(functionName, functionCategory);
    },

    hasVisibleFunctions() {
        return this.functions.some((functionItem) =>
            this.isVisible(functionItem.name, functionItem.category),
        );
    },

    syncNoResultsAnnouncement() {
        const message = this.hasVisibleFunctions() ? '' : 'No results found.';

        if (!message) {
            if (this.noResultsAnnouncementTimeout) {
                clearTimeout(this.noResultsAnnouncementTimeout);
                this.noResultsAnnouncementTimeout = null;
            }

            this.noResultsAnnouncement = '';
            return;
        }

        if (this.noResultsAnnouncementTimeout) {
            clearTimeout(this.noResultsAnnouncementTimeout);
        }

        this.noResultsAnnouncement = '';
        this.noResultsAnnouncementTimeout = setTimeout(() => {
            this.noResultsAnnouncement = message;
            this.noResultsAnnouncementTimeout = null;
        }, 125);
    },

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
        
        let conditions = [];
        try {
            conditions = JSON.parse(cardElement.dataset.conditions || '[]');
        } catch {
            conditions = [];
        }
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
                        // !important prefix overrides .grid-cell.is-empty { background } which is unlayered
                        highlightClass = '!bg-red-400 dark:!bg-red-500';
                        break;
                    }
                } else if (condition.type === 'required') {
                    // Check if required function is NOT in neighbors
                    if (!neighbors.includes(condition.target_function_id)) {
                        shouldHighlight = true;
                        highlightClass = '!bg-yellow-400 dark:!bg-yellow-500';
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
            cell.classList.remove('!bg-red-400', 'dark:!bg-red-500', '!bg-yellow-400', 'dark:!bg-yellow-500');
        });
        this.highlightedCells = [];
    },
});
