/**
 * Delivery Detail Page — shows shipments with delivery confirmation.
 */

import { apiGet, apiPost } from '../api.js';
import { saveData, getData, addToSyncQueue, getSyncQueue } from '../store.js';
import { render, renderLoading, formatDate, formatCurrency, escapeHtml, showToast } from '../ui.js';
import { navigate } from '../router.js';
import { logout } from '../auth.js';
import { updateConnectionStatus, updateSyncBadge } from '../sync.js';

let currentDelivery = null;
let checkedShipments = new Set();
let originalChecked = new Set();
let hasChanges = false;

export async function renderDeliveryDetailPage(params) {
    const deliveryId = params.id;
    renderLoading('Cargando reparto...');

    let delivery = null;

    try {
        if (navigator.onLine) {
            const { ok, status, data } = await apiGet(`/deliveries/${deliveryId}`);

            if (status === 401) {
                await logout();
                navigate('/login');
                return;
            }

            if (ok && data?.data) {
                delivery = data.data;
                await saveData(`delivery_${deliveryId}`, delivery);
            }
        }

        if (!delivery) {
            delivery = await getData(`delivery_${deliveryId}`);
            if (delivery) {
                showToast('📦 Datos guardados (offline)', 'info');
            }
        }
    } catch {
        delivery = await getData(`delivery_${deliveryId}`);
        if (delivery) {
            showToast('📦 Datos guardados (sin conexión)', 'info');
        }
    }

    if (!delivery) {
        render(`
            <div class="page">
                <div class="empty-state">
                    <div class="icon">❌</div>
                    <h3>Reparto no encontrado</h3>
                    <p>No se pudo cargar el reparto. Verificá tu conexión.</p>
                    <button class="btn btn-primary mt-lg" onclick="location.hash='#/deliveries'">
                        Volver
                    </button>
                </div>
            </div>
        `);
        return;
    }

    currentDelivery = delivery;

    // Initialize checked state from shipment statuses
    checkedShipments = new Set();
    originalChecked = new Set();
    delivery.shipments.forEach(s => {
        if (s.ubicacion_actual === 'Entregado') {
            checkedShipments.add(s.id);
            originalChecked.add(s.id);
        }
    });
    hasChanges = false;

    renderDetail(delivery);
}

function renderDetail(delivery) {
    const totalShipments = delivery.shipments.length;
    const deliveredCount = delivery.shipments.filter(s => checkedShipments.has(s.id)).length;
    const pendingCount = totalShipments - deliveredCount;

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
            </div>
        </nav>

        <div class="page" style="padding-bottom: 100px;">
            <div class="detail-header">
                <button class="back-btn" id="back-btn">←</button>
                <div class="detail-info">
                    <h1 class="page-title">Reparto #${escapeHtml(delivery.delivery_number || String(delivery.id))}</h1>
                    <p class="page-subtitle">
                        ${escapeHtml(delivery.location?.name || '')}
                        ${delivery.vehicle_plate ? ' · ' + escapeHtml(delivery.vehicle_plate) : ' · Sin patente'}
                    </p>
                </div>
            </div>

            <div class="detail-stats">
                <div class="stat-card">
                    <div class="stat-value">${totalShipments}</div>
                    <div class="stat-label">Total</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value" style="color: var(--color-success);" id="delivered-count">${deliveredCount}</div>
                    <div class="stat-label">Entregadas</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value" style="color: var(--color-warning);" id="pending-count">${pendingCount}</div>
                    <div class="stat-label">Pendientes</div>
                </div>
            </div>

            <div class="shipments-section">
                <div class="shipments-section-title">Guías del reparto</div>
                <div class="shipment-list" id="shipment-list">
                    ${delivery.shipments.map(s => shipmentCard(s)).join('')}
                </div>
            </div>
        </div>

        <div class="save-bar" id="save-bar">
            <div class="save-bar-info">
                <strong id="changes-text">Sin cambios</strong>
            </div>
            <button class="btn btn-success" id="save-btn">
                Guardar
            </button>
        </div>

        <div id="offline-queue-badge" class="offline-queue-badge"></div>
    `);

    updateConnectionStatus();
    bindDetailEvents(delivery);

    // Check offline queue
    getSyncQueue().then(q => updateSyncBadge(q.length));
}

function shipmentCard(s) {
    const isDelivered = checkedShipments.has(s.id);
    const statusClass = isDelivered ? 'entregado' : 'en-reparto';
    const statusText = isDelivered ? 'Entregado' : 'En reparto';
    const itemClass = isDelivered ? 'delivered' : '';

    return `
        <div class="shipment-item ${itemClass}" data-shipment-id="${s.id}">
            <div class="shipment-item-header">
                <input
                    type="checkbox"
                    class="shipment-checkbox"
                    data-id="${s.id}"
                    ${isDelivered ? 'checked' : ''}
                >
                <span class="shipment-numero">Guía #${escapeHtml(s.numero || String(s.id))}</span>
                <span class="shipment-status-badge ${statusClass}">${statusText}</span>
            </div>
            <div class="shipment-details">
                ${s.recipient ? `
                    <div class="detail-row">
                        <span class="icon">👤</span>
                        <span class="text">${escapeHtml(s.recipient.name)}</span>
                    </div>
                    ${s.recipient.address ? `
                        <div class="detail-row">
                            <span class="icon">📍</span>
                            <span class="text">${escapeHtml(s.recipient.address)}${s.recipient.locality ? ', ' + escapeHtml(s.recipient.locality) : ''}${s.recipient.city ? ' - ' + escapeHtml(s.recipient.city) : ''}</span>
                        </div>
                    ` : ''}
                    ${s.recipient.phone ? `
                        <div class="detail-row">
                            <span class="icon">📞</span>
                            <span class="text"><a href="tel:${escapeHtml(s.recipient.phone)}" style="color: var(--color-primary-light); text-decoration: none;">${escapeHtml(s.recipient.phone)}</a></span>
                        </div>
                    ` : ''}
                ` : ''}
                <div class="detail-row">
                    <span class="icon">📦</span>
                    <span class="text">${s.bultos || 0} bulto(s)</span>
                </div>
                ${s.contra_reembolso ? `
                    <div class="shipment-contra-reembolso">
                        💰 Contra reembolso: ${formatCurrency(s.monto_contra_reembolso)}
                    </div>
                ` : ''}
                ${s.notas ? `
                    <div class="detail-row mt-sm">
                        <span class="icon">📝</span>
                        <span class="text" style="color: var(--color-warning);">${escapeHtml(s.notas)}</span>
                    </div>
                ` : ''}
            </div>
        </div>
    `;
}

function bindDetailEvents(delivery) {
    // Back button
    document.getElementById('back-btn')?.addEventListener('click', () => {
        navigate('/deliveries');
    });

    // Checkbox changes
    document.getElementById('shipment-list')?.addEventListener('change', (e) => {
        if (!e.target.classList.contains('shipment-checkbox')) return;

        const shipmentId = parseInt(e.target.dataset.id);
        const item = e.target.closest('.shipment-item');
        const badge = item.querySelector('.shipment-status-badge');

        if (e.target.checked) {
            checkedShipments.add(shipmentId);
            item.classList.add('delivered');
            badge.className = 'shipment-status-badge entregado';
            badge.textContent = 'Entregado';
        } else {
            checkedShipments.delete(shipmentId);
            item.classList.remove('delivered');
            badge.className = 'shipment-status-badge en-reparto';
            badge.textContent = 'En reparto';
        }

        updateSaveBar(delivery);
    });

    // Save button
    document.getElementById('save-btn')?.addEventListener('click', () => {
        saveChanges(delivery);
    });
}

function updateSaveBar(delivery) {
    const saveBar = document.getElementById('save-bar');
    const changesText = document.getElementById('changes-text');
    const deliveredCount = document.getElementById('delivered-count');
    const pendingCount = document.getElementById('pending-count');

    // Check if anything changed from original state
    const changed = !setsEqual(checkedShipments, originalChecked);
    hasChanges = changed;

    if (changed) {
        saveBar.classList.add('visible');
        const newlyDelivered = [...checkedShipments].filter(id => !originalChecked.has(id)).length;
        const reverted = [...originalChecked].filter(id => !checkedShipments.has(id)).length;

        const parts = [];
        if (newlyDelivered > 0) parts.push(`${newlyDelivered} nueva(s)`);
        if (reverted > 0) parts.push(`${reverted} revertida(s)`);
        changesText.textContent = parts.join(', ');
    } else {
        saveBar.classList.remove('visible');
    }

    // Update stats
    const total = delivery.shipments.length;
    const delivered = checkedShipments.size;
    deliveredCount.textContent = delivered;
    pendingCount.textContent = total - delivered;
}

async function saveChanges(delivery) {
    const saveBtn = document.getElementById('save-btn');
    saveBtn.disabled = true;
    saveBtn.textContent = 'Guardando...';

    const shipmentIds = [...checkedShipments];

    try {
        if (navigator.onLine) {
            const { ok, data } = await apiPost(`/deliveries/${delivery.id}/confirm`, {
                shipment_ids: shipmentIds,
            });

            if (ok) {
                showToast('✅ ' + (data?.message || 'Guardado correctamente'), 'success');
                // Update original state
                originalChecked = new Set(checkedShipments);
                hasChanges = false;
                document.getElementById('save-bar').classList.remove('visible');

                // Update cached data
                delivery.shipments.forEach(s => {
                    s.ubicacion_actual = checkedShipments.has(s.id) ? 'Entregado' : 'En reparto';
                    s.fecha_entrega = checkedShipments.has(s.id) ? new Date().toISOString().split('T')[0] : null;
                });
                await saveData(`delivery_${delivery.id}`, delivery);
            } else {
                showToast('❌ ' + (data?.message || 'Error al guardar'), 'error');
            }
        } else {
            // Queue for sync
            await addToSyncQueue({
                endpoint: `/deliveries/${delivery.id}/confirm`,
                body: { shipment_ids: shipmentIds },
            });

            showToast('📦 Guardado offline. Se sincronizará cuando vuelva la conexión.', 'info');

            // Update local state optimistically
            originalChecked = new Set(checkedShipments);
            hasChanges = false;
            document.getElementById('save-bar').classList.remove('visible');

            delivery.shipments.forEach(s => {
                s.ubicacion_actual = checkedShipments.has(s.id) ? 'Entregado' : 'En reparto';
            });
            await saveData(`delivery_${delivery.id}`, delivery);

            const queue = await getSyncQueue();
            updateSyncBadge(queue.length);
        }
    } catch {
        // Network error — queue offline
        await addToSyncQueue({
            endpoint: `/deliveries/${delivery.id}/confirm`,
            body: { shipment_ids: shipmentIds },
        });
        showToast('📦 Sin conexión. Guardado para sincronizar después.', 'info');

        originalChecked = new Set(checkedShipments);
        hasChanges = false;
        document.getElementById('save-bar').classList.remove('visible');

        const queue = await getSyncQueue();
        updateSyncBadge(queue.length);
    }

    saveBtn.disabled = false;
    saveBtn.textContent = 'Guardar';
}

function setsEqual(a, b) {
    if (a.size !== b.size) return false;
    for (const item of a) {
        if (!b.has(item)) return false;
    }
    return true;
}
