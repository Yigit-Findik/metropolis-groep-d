/**
 * Manages a floating effect preview popup for function library cards.
 * Shows effects (Safety, Recreation, Environment, Facilities, Mobility) when hovering
 * or focusing on function cards in the library.
 * Subtasks:
 * 1. Show preview on hover and focus
 * 2. Display positive/negative values clearly
 * 3. Keyboard accessible (Escape to close)
 * 4. Screen reader support
 * 5. Auto-close on mouse leave / blur / Escape
 */
export class FunctionLibraryPreview {
    #popup = null;
    #visible = false;
    #activeCard = null;

    setup() {
        // Create the popup element
        this.#popup = this.#createPopup();

        // Listen for events dispatched from Alpine cards
        document.addEventListener('show-library-preview', (e) => {
            const card = e.detail.card;
            if (card) this.#show(card);
        });

        document.addEventListener('hide-library-preview', () => {
            this.#hide();
        });

        // Close on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.#visible) {
                this.#hide();
            }
        });

        // Close on scroll to avoid floating position
        window.addEventListener('scroll', () => {
            if (this.#visible) this.#hide();
        }, { passive: true });

        // Reposition popup as mouse moves
        document.addEventListener('mousemove', (e) => {
            if (this.#visible && this.#activeCard) {
                this.#move(e);
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

        // If already showing for this card, just reposition
        if (this.#activeCard === card && !this.#popup.classList.contains('hidden')) {
            return;
        }

        this.#activeCard = card;
        this.#buildHtml(card);
        this.#popup.classList.remove('hidden');
        this.#visible = true;

        // Position with a slight offset
        const rect = card.getBoundingClientRect();
        this.#popup.style.left = `${Math.round(rect.right + 12)}px`;
        this.#popup.style.top = `${Math.round(rect.top)}px`;
    }

    #hide() {
        this.#popup.classList.add('hidden');
        this.#popup.innerHTML = '';
        this.#visible = false;
        this.#activeCard = null;
    }

    // Moves the popup to follow the cursor with a small offset
    #move(e) {
        if (!this.#visible) return;
        this.#popup.style.left = `${e.clientX + 12}px`;
        this.#popup.style.top = `${e.clientY + 12}px`;
    }

    // Builds the HTML shown inside the popup
    #buildHtml(card) {
        const ds = card.dataset || {};
        const name = ds.function || '';
        const category = ds.category || 'Uncategorized';

        // Create effect badges - IMPORTANT: data-environment-quality becomes environmentQuality in dataset
        const badges = [
            ['safety', 'Saf'],
            ['recreation', 'Rec'],
            ['environmentQuality', 'EnQ'],
            ['facilities', 'Fac'],
            ['mobility', 'Mob'],
        ].map(([key, label]) => {
            const value = parseInt(ds[key] ?? 0, 10);
            const badgeHtml = this.#formatBadge(value);
            return `<div class="flex items-center gap-2 mb-1"><div class="w-10 text-[10px] font-semibold text-gray-500 dark:text-gray-400 uppercase">${label}</div>${badgeHtml}</div>`;
        }).join('');

        // Build complete HTML
        const html = [
            `<div class="font-semibold mb-1.5 text-xs text-gray-900 dark:text-white">${name}</div>`,
            `<div class="inline-flex items-center rounded-full bg-blue-100 px-2 py-0.5 text-[10px] font-semibold text-blue-700 dark:bg-blue-900/40 dark:text-blue-100 mb-2">${category}</div>`,
            `<div class="grid gap-0.5">${badges}</div>`,
        ].join('');

        this.#popup.innerHTML = html;
    }

    // Returns a coloured pill span for a numeric effect value (Subtask 2)
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
