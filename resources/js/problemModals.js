/**
 * problemModals.js — Módulo global de modales de problemas.
 * Cargado en app.js: disponible en TODAS las páginas.
 *
 * Disparadores:
 *  - .problem-badge[data-model-type][data-model-id]  → abre resumen de guías con problema
 *  - .btn-open-spm[data-shipment-id][data-shipment-numero] → abre modal individual de guía
 *  - .btn-problem-shipment[data-id]                  → ídem (alias usado en deliveries form)
 */

import $ from 'jquery';

const PROBLEM_HISTORY_URL = '/documents/problem';
const PROBLEM_STORE_URL = '/documents/problem';
const PROBLEM_SHIPMENTS_URL = '/documents/problem/shipments';

// ── Helpers ────────────────────────────────────────────────────────────────

function historyHtml(items) {
    if (!items || items.length === 0) {
        return '<p class="text-center text-sm text-gray-500 dark:text-gray-400 italic py-2">Sin problemas registrados.</p>';
    }
    return items.map(item => {
        const date = new Date(item.created_at).toLocaleString('es-AR', { dateStyle: 'short', timeStyle: 'short' });
        const activeStr = item.is_active
            ? '<span class="text-red-500 font-semibold">Abierto</span>'
            : '<span class="text-green-500 font-semibold">Resuelto</span>';
        const dot = item.is_active ? 'bg-red-500' : 'bg-green-500';
        const user = item.user ? item.user.name : 'Sistema';
        return `
        <div class="flex items-start gap-2 text-sm bg-gray-50 dark:bg-gray-900/40 p-2.5 rounded-lg">
            <span class="mt-1.5 w-2 h-2 rounded-full flex-shrink-0 ${dot}"></span>
            <div class="flex-1 min-w-0">
                <p class="text-gray-800 dark:text-gray-200 leading-snug">${item.comment}</p>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">${user} · ${date} · ${activeStr}</p>
            </div>
        </div>`;
    }).join('');
}

// ── Modal individual de Guía ───────────────────────────────────────────────

function openShipmentModal(shipmentId, shipmentNumero) {
    const modal = $('#shipment-problems-modal');
    $('#spm-shipment-id').val(shipmentId);
    $('#spm-guia-numero').text(shipmentNumero || shipmentId);
    $('#spm-comment').val('');
    $('input[name="spm_active"][value="1"]').prop('checked', true);
    $('#spm-resolve-radio-label').hide();
    $('#spm-history').html('<p class="text-center text-gray-500 dark:text-gray-400 py-4 text-sm">Cargando historial...</p>');
    modal.removeClass('hidden');

    $.ajax({
        url: PROBLEM_HISTORY_URL,
        method: 'GET',
        data: { model_type: 'shipment', model_id: shipmentId },
        success: function (res) {
            if (res.has_active) $('#spm-resolve-radio-label').show();
            $('#spm-history').html(historyHtml(res.history));
        },
        error: function () {
            $('#spm-history').html('<p class="text-center text-red-500">Error al cargar historial.</p>');
        }
    });
}

$(document).on('click', '.btn-close-spm-modal', function () {
    $('#shipment-problems-modal').addClass('hidden');
});

$(document).on('submit', '#spm-form', function (e) {
    e.preventDefault();
    const id = $('#spm-shipment-id').val();
    const isActive = $('input[name="spm_active"]:checked').val();
    const comment = $('#spm-comment').val().trim();
    if (!comment) return;

    const btn = $('#spm-submit-btn');
    btn.prop('disabled', true).text('Guardando...');

    $.ajax({
        url: PROBLEM_STORE_URL,
        method: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            model_type: 'shipment',
            model_id: id,
            is_active: isActive,
            comment: comment,
        },
        success: function () {
            $('#shipment-problems-modal').addClass('hidden');

            // Si hay resumen abierto, recargarlo
            const psm = $('#problems-summary-modal');
            if (!psm.hasClass('hidden')) {
                psm.trigger('reload');
            }

            // Badge de la tabla de guías en deliveries form
            const theRow = $('#selected-shipments-table').find(`tr[data-id="${id}"]`);
            if (theRow.length > 0) {
                const statusTd = theRow.find('td:eq(3)');
                statusTd.find('[data-active-problem]').remove();
                if (isActive == '1') {
                    statusTd.append('<span class="text-red-500 font-bold ml-2 text-xs" data-active-problem="true">(⚠ PROBLEMA)</span>');
                }
            }

            // Recargar cualquier DataTable presente
            if (window.$ && $.fn.dataTable) {
                $.fn.dataTable.tables({ visible: true, api: true }).ajax.reload(null, false);
            }
        },
        error: function (xhr) {
            alert('Error: ' + (xhr.responseJSON?.message || 'Ocurrió un error.'));
        },
        complete: function () {
            btn.prop('disabled', false).text('Guardar');
        }
    });
});

// Alias para deliveries form (.btn-problem-shipment[data-id])
$(document).on('click', '.btn-problem-shipment', function (e) {
    e.stopPropagation();
    const id = $(this).data('id');
    const numero = $(this).closest('tr').find('td:first').text().trim() || id;
    openShipmentModal(id, numero);
});

// Alias para dashboard/any (.btn-open-spm[data-shipment-id])
$(document).on('click', '.btn-open-spm', function () {
    openShipmentModal($(this).data('shipment-id'), $(this).data('shipment-numero'));
});

// ── Modal Resumen de Guías con Problema (para badges de rutas/despachos/repartos) ──

$(document).on('click', '.problem-badge', function () {
    const modelType = $(this).data('model-type');
    const modelId = $(this).data('model-id');
    const label = $(this).data('label') || '';

    const modal = $('#problems-summary-modal');
    $('#psm-subtitle').text(label);
    $('#psm-body').html('<p class="text-center text-gray-400 py-6">Cargando...</p>');
    modal.removeClass('hidden');

    function loadSummary() {
        $.ajax({
            url: PROBLEM_SHIPMENTS_URL,
            method: 'GET',
            data: { model_type: modelType, model_id: modelId },
            success: function (res) {
                if (res.shipments.length === 0) {
                    $('#psm-body').html('<p class="text-center text-gray-500 dark:text-gray-400 italic py-6">🎉 Sin guías con problemas activos.</p>');
                    return;
                }
                let html = '';
                res.shipments.forEach(s => {
                    html += `
                    <div class="flex items-start justify-between gap-3 bg-red-50 dark:bg-red-900/10 border border-red-200 dark:border-red-800 rounded-xl p-4">
                        <div class="flex-1 min-w-0">
                            <p class="font-mono font-bold text-indigo-600 dark:text-indigo-400 text-sm">Guía ${s.numero}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">${s.origen} → ${s.destino}</p>
                            <p class="text-sm text-red-700 dark:text-red-300 mt-1.5 italic">"${s.problema}"</p>
                            <p class="text-xs text-gray-400 mt-1">${s.problem_at}</p>
                        </div>
                        <button type="button"
                            class="btn-open-spm flex-shrink-0 inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition"
                            data-shipment-id="${s.id}"
                            data-shipment-numero="${s.numero}">
                            Ver / Resolver
                        </button>
                    </div>`;
                });
                $('#psm-body').html(html);
            },
            error: function () {
                $('#psm-body').html('<p class="text-center text-red-500 py-4">Error al cargar las guías.</p>');
            }
        });
    }

    loadSummary();
    modal.off('reload').on('reload', loadSummary);
});

$(document).on('click', '.btn-close-psm', function () {
    $('#problems-summary-modal').addClass('hidden');
});

// Cerrar modales al hacer click fuera
$(document).on('click', '#problems-summary-modal, #shipment-problems-modal', function (e) {
    if ($(e.target).is(this)) {
        $(this).addClass('hidden');
    }
});

export { };
