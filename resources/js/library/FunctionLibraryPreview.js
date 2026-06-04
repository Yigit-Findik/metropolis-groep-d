export class FunctionLibraryPreview {
    #popup = null;
    #visible = false;
    #activeCard = null;
    #functionNames = {};

    isVisible() {
        return this.#visible;
    }

    setup() {
        this.#popup = this.#createPopup();
        this.#buildFunctionNameMap();

        document.addEventListener('show-library-preview', (e) => {
            const card = e.detail.card;
            if (card) this.#show(card);
        });

        document.addEventListener('hide-library-preview', () => {
            this.#hide();
        });

        // Close on escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.#visible) {
                this.#hide();
            }
        });

        window.addEventListener('scroll', () => {
            if (this.#visible) this.#hide();
        }, { passive: true });

        document.addEventListener('mousemove', (e) => {
            if (this.#visible && this.#activeCard) {
                this.#move(e);
            }
        });
    }

    #buildFunctionNameMap() {
        document.querySelectorAll('[data-library-card]').forEach(card => {
            const id = card.dataset.functionId;
            const name = card.dataset.function;
            if (id && name) {
                this.#functionNames[id] = name;
            }
        });
    }

    // Creates the floating popup element
    #createPopup() {
        let popup = document.getElementById('function-library-preview-popup');
        if (popup) return popup;

        popup = document.createElement('div');
        popup.id = 'function-library-preview-popup';
        popup.style.position = 'fixed';
        popup.style.pointerEvents = 'none';
        popup.style.zIndex = '9999';
        popup.className = 'hidden bg-white dark:bg-gray-800 text-xs rounded-md shadow-lg p-3 text-gray-900 dark:text-gray-100';
        popup.setAttribute('role', 'tooltip');
        popup.setAttribute('aria-live', 'polite');
        document.body.appendChild(popup);
        return popup;
    }

    // Shows the popup for a given card
    #show(card) {
        if (!card || !card.dataset?.function) return;

        if (this.#activeCard === card && !this.#popup.classList.contains('hidden')) {
            return;
        }

        this.#activeCard = card;
        this.#buildHtml(card);
        this.#popup.classList.remove('hidden');
        this.#visible = true;

        const rect = card.getBoundingClientRect();
        let left = rect.right + 12;
        let top = rect.top;

        // If the popup overflows the right edge show it on the left side
        if (left + this.#popup.offsetWidth > window.innerWidth) {
            left = rect.left - this.#popup.offsetWidth - 12;
        }

        // If the popup overflows the bottom edge shift it up safely
        if (top + this.#popup.offsetHeight > window.innerHeight) {
            top = window.innerHeight - this.#popup.offsetHeight - 12;
        }

        this.#popup.style.left = `${Math.round(left)}px`;
        this.#popup.style.top = `${Math.round(top)}px`;
    }

    #hide() {
        this.#popup.classList.add('hidden');
        this.#popup.innerHTML = '';
        this.#visible = false;
        this.#activeCard = null;
    }

    // Moves the popup to follow the cursor with simple edge adjustments
    #move(e) {
        if (!this.#visible) return;

        let left = e.clientX + 12;
        let top = e.clientY + 12;

        // Keep inside bounds while dragging/moving mouse
        if (left + this.#popup.offsetWidth > window.innerWidth) {
            left = e.clientX - this.#popup.offsetWidth - 12;
        }
        if (top + this.#popup.offsetHeight > window.innerHeight) {
            top = e.clientY - this.#popup.offsetHeight - 12;
        }

        this.#popup.style.left = `${left}px`;
        this.#popup.style.top = `${top}px`;
    }

    #buildHtml(card) {
        const ds = card.dataset || {};
        const name = ds.function || '';
        const category = ds.category || 'Uncategorized';

        const badges = [
            ['safety', 'Saf', 'Safety'],
            ['recreation', 'Rec', 'Recreation'],
            ['environmentQuality', 'EnQ', 'Environment Quality'],
            ['facilities', 'Fac', 'Facilities'],
            ['mobility', 'Mob', 'Mobility'],
        ].map(([key, label, fullName]) => {
            const value = parseInt(ds[key] ?? 0, 10);
            const badgeHtml = this.#formatBadge(value);

            return `
                <div class="flex items-center gap-2 mb-1">
                    <div class="w-10 text-[10px] font-semibold text-gray-500 dark:text-gray-400 uppercase" aria-hidden="true">${label}</div>
                    <span class="sr-only">${fullName}:</span>
                    ${badgeHtml}
                </div>
            `;
        }).join('');

        let conditions = [];
        try {
            conditions = JSON.parse(ds.conditions || '[]');
        } catch {
            conditions = [];
        }

        const conditionsList = this.#formatConditions(conditions);

        const html = [
            `<div class="font-semibold mb-1.5 text-xs text-gray-900 dark:text-white">${name}</div>`,
            `<div class="inline-flex items-center rounded-full bg-blue-100 px-2 py-0.5 text-[10px] font-semibold text-blue-700 dark:bg-blue-900/40 dark:text-blue-100 mb-2">${category}</div>`,
            `<div class="grid gap-0.5 mb-2">${badges}</div>`,
            `<div class="border-t border-gray-300 dark:border-gray-600 pt-2 mt-2 text-[10px] space-y-0.5">${conditionsList}</div>`,
        ].join('');

        this.#popup.innerHTML = html;
    }

    #formatConditions(conditions) {
        if (!conditions || conditions.length === 0) {
            return '<span class="text-gray-500 dark:text-gray-400">No placement conditions</span>';
        }

        return conditions.map(cond => {
            const targetName = this.#functionNames[cond.target_function_id] || 'Unknown';
            const text = cond.type === 'required'
                ? `Must be adjacent to ${targetName}`
                : `Cannot be adjacent to ${targetName}`;
            const color = cond.type === 'required' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400';
            return `<div class="${color} font-semibold">• ${text}</div>`;
        }).join('');
    }

    // Returns a coloured pill span for a numeric effect value
    #formatBadge(value) {
        const n = parseInt(value || 0, 10);
        const sign = n > 0 ? `+${n}` : `${n}`;
        const bg = n > 0
            ? 'bg-green-500 text-white'
            : n < 0
                ? 'bg-red-500 text-white'
                : 'bg-gray-300 text-gray-800 dark:bg-gray-600 dark:text-gray-100';
        return `<span class="inline-flex items-center justify-center px-2 py-0.5 rounded-full text-[11px] font-bold ${bg}">${sign}</span>`;
    }
}