/**
 * company-selector.js
 * Modal de selección de empresa reutilizable.
 * 
 * Uso:
 *   import { openCompanySelector } from '@/shared/company-selector.js';
 *   openCompanySelector({
 *     companies: [...],    // Array: [{id, prefix, name, color}]
 *     title: 'Seleccionar empresa',
 *     onSelect: (company) => { window.location.href = `...?company_id=${company.id}`; }
 *   });
 */

let modalEl = null;

function buildModal() {
    if (document.getElementById('company-selector-modal')) return;

    const html = `
    <div id="company-selector-modal" class="fixed inset-0 z-[9999] hidden" role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div id="csm-backdrop" class="absolute inset-0 bg-gray-950/70 backdrop-blur-sm"></div>

        <!-- Panel -->
        <div class="relative flex items-center justify-center min-h-screen p-4">
            <div id="csm-panel"
                class="relative bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-md
                       border border-gray-200 dark:border-gray-700 overflow-hidden
                       transform transition-all duration-200 scale-95 opacity-0">

                <!-- Header -->
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-800">
                    <div>
                        <h3 id="csm-title" class="text-base font-bold text-gray-900 dark:text-white">Seleccionar empresa</h3>
                        <p id="csm-subtitle" class="text-xs text-gray-400 dark:text-gray-500 mt-0.5"></p>
                    </div>
                    <button id="csm-close"
                        class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-200
                               hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Company buttons grid -->
                <div id="csm-buttons" class="p-5 grid grid-cols-2 gap-3"></div>

                <!-- Footer -->
                <div class="px-6 pb-5">
                    <button id="csm-cancel"
                        class="w-full py-2 text-xs font-medium text-gray-500 dark:text-gray-400
                               hover:text-gray-700 dark:hover:text-gray-200 transition">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>`;

    document.body.insertAdjacentHTML('beforeend', html);

    modalEl = document.getElementById('company-selector-modal');
    const panel = document.getElementById('csm-panel');

    document.getElementById('csm-close').addEventListener('click', closeModal);
    document.getElementById('csm-cancel').addEventListener('click', closeModal);
    document.getElementById('csm-backdrop').addEventListener('click', closeModal);

    // Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeModal();
    });
}

function openModal() {
    modalEl.classList.remove('hidden');
    // Trigger animation
    requestAnimationFrame(() => {
        const panel = document.getElementById('csm-panel');
        panel.classList.remove('scale-95', 'opacity-0');
        panel.classList.add('scale-100', 'opacity-100');
    });
}

function closeModal() {
    const panel = document.getElementById('csm-panel');
    panel.classList.add('scale-95', 'opacity-0');
    panel.classList.remove('scale-100', 'opacity-100');
    setTimeout(() => {
        modalEl.classList.add('hidden');
    }, 150);
}

/**
 * Hex color → RGBA with given opacity (for button bg)
 */
function hexToRgba(hex, alpha) {
    const r = parseInt(hex.slice(1, 3), 16);
    const g = parseInt(hex.slice(3, 5), 16);
    const b = parseInt(hex.slice(5, 7), 16);
    return `rgba(${r}, ${g}, ${b}, ${alpha})`;
}

export function openCompanySelector({ companies = [], title = 'Seleccionar empresa', subtitle = '', onSelect }) {
    buildModal();

    // Set title
    document.getElementById('csm-title').textContent = title;
    document.getElementById('csm-subtitle').textContent = subtitle;

    // Recover last selection
    const lastId = parseInt(localStorage.getItem('last_selected_company_id'));

    // Build company buttons
    const container = document.getElementById('csm-buttons');
    container.innerHTML = '';

    companies.forEach(company => {
        const color = company.color || '#6366f1';
        const isLast = lastId && company.id === lastId;

        const btn = document.createElement('button');
        btn.type = 'button';
        btn.setAttribute('data-company-id', company.id);
        btn.style.cssText = `
            border: 2px solid ${color};
            color: ${color};
            background-color: ${hexToRgba(color, isLast ? 0.15 : 0.05)};
        `;
        btn.className = `relative w-full flex flex-col items-center justify-center
            gap-1 py-5 px-3 rounded-xl font-semibold transition-all duration-150
            hover:scale-[1.02] active:scale-[0.98] cursor-pointer
            ${isLast ? 'ring-2 ring-offset-2 dark:ring-offset-gray-900' : ''}`;

        if (isLast) {
            btn.style.ringColor = color;
            // Last used badge
            const badge = document.createElement('span');
            badge.className = 'absolute top-2 right-2 text-[9px] font-bold px-1.5 py-0.5 rounded-full';
            badge.style.backgroundColor = color;
            badge.style.color = '#fff';
            badge.textContent = 'Último';
            btn.appendChild(badge);
        }

        const prefix = document.createElement('span');
        prefix.className = 'text-3xl font-black tracking-tight leading-none';
        prefix.style.color = color;
        prefix.textContent = company.prefix;

        const name = document.createElement('span');
        name.className = 'text-[11px] font-medium opacity-80 text-center leading-tight';
        name.style.color = color;
        name.textContent = company.name;

        btn.appendChild(prefix);
        btn.appendChild(name);

        btn.addEventListener('click', () => {
            localStorage.setItem('last_selected_company_id', company.id);
            closeModal();
            setTimeout(() => {
                if (onSelect) onSelect(company);
            }, 160);
        });

        container.appendChild(btn);
    });

    openModal();
}
