let notifyTimer = null;

/**
 * Show a non-blocking toast notification at the bottom of the screen.
 * Use this instead of alert() so the page is never frozen.
 *
 * @param {string} message
 */
export function notify(message) {
    let toast = document.getElementById('js-notify-toast');

    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'js-notify-toast';
        document.body.appendChild(toast);
    }

    toast.className = 'fixed bottom-6 left-1/2 -translate-x-1/2 z-[9999] px-5 py-3 rounded-xl shadow-lg bg-red-700 text-white text-sm font-semibold whitespace-pre-line max-w-sm text-left';
    toast.textContent = message;

    clearTimeout(notifyTimer);
    notifyTimer = setTimeout(() => toast.remove(), 4000);
}
