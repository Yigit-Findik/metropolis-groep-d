/**
 * Manages QoL score display and toast notifications.
 */
export class QolService {
    #api;
    #toastTimer = null; // Tracks the auto-hide timeout so it can be reset on rapid updates

    constructor(api) {
        this.#api = api;
    }

    // Shows a temporary toast with the function name and its QoL impact
    showToast(functionName, qolScore) {
        const toast = document.getElementById("qol-toast");
        if (!toast) return;

        const isPositive = qolScore >= 0;
        const sign = isPositive ? "+" : "";

        toast.textContent = `${functionName}: ${sign}${qolScore} `;
        toast.className = `fixed bottom-6 right-6 z-50 rounded-xl px-5 py-3 text-sm font-semibold text-white shadow-lg transition-all duration-300 ${isPositive ? "bg-green-500" : "bg-red-500"}`;

        // Reset the timer so rapid drops don't dismiss the toast too early
        clearTimeout(this.#toastTimer);
        this.#toastTimer = setTimeout(() => {
            toast.classList.add("hidden");
        }, 3000);
    }

    // Fetches fresh QoL scores from the server and updates all score elements in the DOM
    async refresh() {
        const total = document.getElementById("qol-score-value");
        if (total) total.textContent = "Calculating...";

        try {
            const data = await this.#api.getQolScore();

            if (total) {
                total.textContent = data.total_score;
                total.setAttribute(
                    "aria-label",
                    `Quality of life ${data.total_score}`,
                );
            }
            if (data.categories) {
                for (const [cat, score] of Object.entries(data.categories)) {
                    // Element IDs use dashes: "qol-environment-quality", "qol-safety", etc.
                    // Checks for whitespace in category names and replaces them with dashes to match the element IDs.
                    const elementId = `qol-${cat.replace(/\s+/g, "-")}`;
                    const el = document.getElementById(elementId);
                    if (el) {
                        const penaltyForCat = data.penalty_categories?.[cat] ?? 0;
                        const gross = score - penaltyForCat; // add back negative penalties so top shows pre-penalty value
                        const display = (gross >= 0 ? "+" : "") + gross;
                        el.textContent = display;
                        el.className = `mt-0.5 text-xl font-semibold ${gross >= 0 ? "text-green-300" : "text-red-300"}`;
                        // Make screen reader announce the category and its value (gross)
                        el.setAttribute("aria-label", `${cat} ${display}`);
                    }

                    const bonus = data.bonus_categories?.[cat] ?? 0;

                    const bonusEl = document.getElementById(`qol-bonus-${cat.replace(/\s+/g, "-")}`);
                    if (bonusEl) {
                        const bonusDisplay = `+${bonus}`;
                        bonusEl.textContent = bonusDisplay;
                        bonusEl.setAttribute("aria-label", `${cat} bonus points ${bonusDisplay}`);
                    }

                    const penalty = data.penalty_categories?.[cat] ?? 0;

                    const penaltyEl = document.getElementById(`qol-penalty-${cat.replace(/\s+/g, "-")}`);
                    if (penaltyEl) {
                        const penaltyDisplay = penalty === 0 ? "-0" : `${penalty}`;
                        penaltyEl.textContent = penaltyDisplay;
                        penaltyEl.setAttribute("aria-label", `${cat} penalty points ${penaltyDisplay}`);
                    }

                }
            }

            // Also ensure penalty row is updated even if a category key was missing
            if (data.penalty_categories) {
                for (const [catKey, penValue] of Object.entries(data.penalty_categories)) {
                    const id = `qol-penalty-${catKey.replace(/\s+/g, "-")}`;
                    const el = document.getElementById(id);
                    if (!el) continue;
                    const display = penValue === 0 ? "-0" : `${penValue}`;
                    el.textContent = display;
                    el.setAttribute("aria-label", `${catKey} penalty points ${display}`);
                }
            }
        } catch {
            // Silently ignore — the display stays at the last known value.
        }
    }
}
