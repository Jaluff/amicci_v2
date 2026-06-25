/**
 * UI utilities — toast notifications and helpers.
 */

let toastContainer = null;

/**
 * Ensure the toast container exists in the DOM.
 */
function ensureContainer() {
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container';
        document.body.appendChild(toastContainer);
    }
    return toastContainer;
}

/**
 * Show a toast notification.
 * @param {string} message
 * @param {'success'|'error'|'info'} type
 * @param {number} duration - ms
 */
export function showToast(message, type = 'info', duration = 3000) {
    const container = ensureContainer();

    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;

    container.appendChild(toast);

    setTimeout(() => {
        toast.classList.add('toast-out');
        setTimeout(() => toast.remove(), 300);
    }, duration);
}

/**
 * Render HTML into the #app container.
 */
export function render(html) {
    const app = document.getElementById('app');
    if (app) {
        app.innerHTML = html;
    }
}

/**
 * Render a loading screen.
 */
export function renderLoading(message = 'Cargando...') {
    render(`
        <div class="loading-screen">
            <div class="spinner"></div>
            <p>${message}</p>
        </div>
    `);
}

/**
 * Format a date string (YYYY-MM-DD) for display.
 */
export function formatDate(dateStr) {
    if (!dateStr) return '-';
    const [y, m, d] = dateStr.split('-');
    return `${d}/${m}/${y}`;
}

/**
 * Format a number as currency.
 */
export function formatCurrency(amount) {
    if (!amount && amount !== 0) return '-';
    return new Intl.NumberFormat('es-AR', {
        style: 'currency',
        currency: 'ARS',
    }).format(amount);
}

/**
 * Escape HTML special characters.
 */
export function escapeHtml(str) {
    if (!str) return '';
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}
