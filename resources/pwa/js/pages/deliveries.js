/**
 * Deliveries List Page
 */

import { apiGet } from '../api.js';
import { saveData, getData } from '../store.js';
import { render, renderLoading, formatDate, escapeHtml, showToast } from '../ui.js';
import { navigate } from '../router.js';
import { getUser, logout } from '../auth.js';
import { updateConnectionStatus } from '../sync.js';

export async function renderDeliveriesPage() {
    renderLoading('Cargando repartos...');

    let deliveries = [];

    try {
        if (navigator.onLine) {
            const { ok, status, data } = await apiGet('/deliveries');

            if (status === 401) {
                // Token expired — redirect to login
                await logout();
                navigate('/login');
                return;
            }

            if (ok && data?.data) {
                deliveries = data.data;
                // Cache for offline use
                await saveData('deliveries', deliveries);
            }
        } else {
            // Load from cache
            deliveries = (await getData('deliveries')) || [];
            showToast('📦 Mostrando datos guardados (offline)', 'info');
        }
    } catch (err) {
        // Network error — try cache
        deliveries = (await getData('deliveries')) || [];
        if (deliveries.length > 0) {
            showToast('📦 Mostrando datos guardados (sin conexión)', 'info');
        }
    }

    const user = getUser();

    render(`
        <nav class="navbar">
            <div class="navbar-brand">
                <span class="icon">🚚</span>
                <span>Transporte Amicci</span>
            </div>
            <div class="navbar-actions">
                <span id="sync-status" class="sync-badge online">
                    <span class="dot"></span> Online
                </span>
                <button class="btn btn-ghost btn-icon" id="logout-btn" title="Cerrar sesión">
                    🚪
                </button>
            </div>
        </nav>

        <div class="page">
            <div class="page-header">
                <h1 class="page-title">Mis Repartos</h1>
                <p class="page-subtitle">Hola, ${escapeHtml(user?.name || 'Repartidor')}</p>
            </div>

            ${deliveries.length === 0 ? `
                <div class="empty-state">
                    <div class="icon">📋</div>
                    <h3>Sin repartos activos</h3>
                    <p>No tenés repartos en estado "En reparto" en este momento.</p>
                    <button class="btn btn-primary mt-lg" id="refresh-btn">
                        Actualizar
                    </button>
                </div>
            ` : `
                <div class="delivery-list">
                    ${deliveries.map(d => deliveryCard(d)).join('')}
                </div>
            `}
        </div>

        <div id="offline-queue-badge" class="offline-queue-badge"></div>
    `);

    updateConnectionStatus();
    bindEvents(deliveries);
}

function deliveryCard(d) {
    const isCompleted = d.status === 'Completado';
    const statusClass = isCompleted ? 'completado' : 'en-reparto';

    return `
        <a class="delivery-card" data-delivery-id="${d.id}" href="#/deliveries/${d.id}">
            <div class="delivery-card-header">
                <span class="delivery-number">Reparto #${escapeHtml(d.delivery_number || String(d.id))}</span>
                <span class="delivery-badge ${statusClass}">${escapeHtml(d.status)}</span>
            </div>
            <div class="delivery-meta">
                <div class="delivery-meta-item">
                    <span class="icon">📍</span>
                    <span class="value">${escapeHtml(d.location?.name || '-')}</span>
                </div>
                <div class="delivery-meta-item">
                    <span class="icon">📦</span>
                    <span class="value">${d.guide_count || 0} guías</span>
                </div>
                <div class="delivery-meta-item">
                    <span class="icon">🚗</span>
                    <span class="value">${escapeHtml(d.vehicle_plate || 'Sin patente')}</span>
                </div>
                <div class="delivery-meta-item">
                    <span class="icon">📅</span>
                    <span class="value">${formatDate(d.dispatch_date || d.load_date)}</span>
                </div>
            </div>
        </a>
    `;
}

function bindEvents() {
    // Logout
    document.getElementById('logout-btn')?.addEventListener('click', async () => {
        await logout();
        navigate('/login');
    });

    // Refresh button (empty state)
    document.getElementById('refresh-btn')?.addEventListener('click', () => {
        renderDeliveriesPage();
    });
}
