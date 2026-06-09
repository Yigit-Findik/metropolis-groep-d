/**
 * Manages QoL score display and toast notifications.
 */
export class QolService {
    #api;
    #toastTimer = null; // Tracks the auto-hide timeout so it can be reset on rapid updates
    #refreshing = false; // Prevents overlapping requests at high simulation speeds

    constructor(api) {
        this.#api = api;
    }

    // Determines the quality of life label based on score
    // Good = 50+, moderate = 0-49, bad = < 0
    #getScoreLabel(score) {
        if (score >= 50) return 'Good';
        if (score >= 0) return 'Moderate';
        return 'Bad';
    }

    // shows a temporary toast with the function name and its QoL impact
    showToast(functionName, qolScore) {
        const toast = document.getElementById("qol-toast");
        if (!toast) return;

        const isPositive = qolScore >= 0;
        const sign = isPositive ? "+" : "";
        // add visual symbol for positive (good), for negative (bad)
        const symbol = isPositive ? "▲" : "▼";

        // Build the toast message with symbol and score
        const message = `${symbol} ${functionName}: ${sign}${qolScore}`;
        toast.textContent = message;
        toast.className = `fixed bottom-6 right-6 z-50 rounded-xl px-5 py-3 text-sm font-semibold text-white shadow-lg transition-all duration-300 ${isPositive ? "bg-green-500" : "bg-red-500"}`;
        toast.setAttribute("aria-live", "assertive");
        toast.setAttribute("role", "status");

        // Reset the timer so rapid drops don't dismiss the toast too early
        clearTimeout(this.#toastTimer);
        this.#toastTimer = setTimeout(() => {
            toast.classList.add("hidden");
        }, 3000);
    }

    // Only sets textContent when the value actually changed — prevents aria-live from firing on every tick
    #setText(el, text) {
        if (el && el.textContent !== String(text)) el.textContent = text;
    }

    // Fetches fresh QoL scores from the server and updates all score elements in the DOM
    async refresh(force = false) {
        if (!force && this.#refreshing) return;
        this.#refreshing = true;
        try {
            const data = await this.#api.getQolScore();
            const total = document.getElementById("qol-score-value");

            if (total) {
                const scoreText = String(data.total_score);
                if (total.textContent !== scoreText) {
                    total.textContent = scoreText;
                    total.setAttribute("aria-label", `Total quality of life score: ${scoreText}`);
                }
            }

            // Update the QoL label element with status (good, moderate, bad)
            const labelEl = document.getElementById("qol-score-label");
            if (labelEl) {
                const label = this.#getScoreLabel(data.total_score);
                this.#setText(labelEl, label);
            }
            if (data.categories) {
                for (const [cat, score] of Object.entries(data.categories)) {
                    // Element IDs use dashes: "qol-environment-quality", "qol-safety", etc.
                    // Checks for whitespace in category names and replaces them with dashes to match the element IDs.
                    const elementId = `qol-${cat.replace(/\s+/g, "-")}`;
                    const el = document.getElementById(elementId);
                    if (el) {
                        const penaltyForCat = data.penalty_categories?.[cat] ?? 0;
                        const gross = score - penaltyForCat;
                        const display = (gross >= 0 ? "+" : "") + gross;
                        this.#setText(el, display);
                        el.className = `mt-0.5 text-xl font-semibold ${gross >= 0 ? "text-green-300" : "text-red-300"}`;
                        el.setAttribute("aria-label", `${cat} ${display}`);
                    }

                    const bonus = data.bonus_categories?.[cat] ?? 0;
                    const bonusEl = document.getElementById(`qol-bonus-${cat.replace(/\s+/g, "-")}`);
                    if (bonusEl) this.#setText(bonusEl, `+${bonus}`);

                    const penalty = data.penalty_categories?.[cat] ?? 0;
                    const penaltyEl = document.getElementById(`qol-penalty-${cat.replace(/\s+/g, "-")}`);
                    if (penaltyEl) this.#setText(penaltyEl, penalty === 0 ? "-0" : `${penalty}`);

                    const eventMod = data.event_categories?.[cat] ?? 0;
                    const eventEl = document.getElementById(`qol-event-${cat.replace(/\s+/g, "-")}`);
                    if (eventEl) {
                        const eventDisplay = (eventMod >= 0 ? "+" : "") + eventMod;
                        this.#setText(eventEl, eventDisplay);
                        eventEl.className = `font-semibold ${eventMod > 0 ? "text-green-600 dark:text-green-400" : eventMod < 0 ? "text-red-600 dark:text-red-400" : "text-gray-500 dark:text-gray-400"}`;
                    }

                }
            }

            if (data.penalty_categories) {
                for (const [catKey, penValue] of Object.entries(data.penalty_categories)) {
                    const el = document.getElementById(`qol-penalty-${catKey.replace(/\s+/g, "-")}`);
                    if (el) this.#setText(el, penValue === 0 ? "-0" : `${penValue}`);
                }
            }
        } catch {
            // Silently ignore — the display stays at the last known value.
        } finally {
            this.#refreshing = false;
        }
    }
}
