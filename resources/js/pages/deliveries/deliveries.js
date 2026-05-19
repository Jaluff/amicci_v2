import { openCompanySelector } from '../../shared/company-selector.js';

const DeliveryModule = (function ($) {
    let dataTable;

    const init = function () {
        initDataTable();
        initProblemModal();
    };

    const initDataTable = function () {
        if (!$('#deliveries-table').length) return;

        dataTable = $('#deliveries-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: $('#deliveries-table').data('url'),
                data: function (d) {
                    d.location_id = $('#filter_location_id').val();
                    d.fecha_inicio = $('#filter_fecha_inicio').val();
                    d.fecha_fin = $('#filter_fecha_fin').val();
                    d.numero_documento = $('#filter_numero_documento').val();
                    d.estado = $('#filter_estado').val();
                    d.company_id = $('#filter_company_id').val();
                }
            },
            columns: [
                { 
                    data: 'empresa', 
                    name: 'companies.prefix',
                    render: function(data, type, row) {
                        const color = row.empresa_color || '#6366f1';
                        return `<span class="px-2 py-1 rounded-full text-[10px] font-bold text-white shadow-sm" style="background-color: ${color}">${data}</span>`;
                    }
                },
                {
                    data: 'load_date',
                    name: 'load_date',
                    render: function (data) {
                        if (!data) return '-';
                        return new Date(data).toLocaleDateString('es-AR');
                    }
                },
                { data: 'delivery_number', name: 'delivery_number' },
                {
                    data: 'deliverer.name',
                    name: 'deliverer.name',
                    orderable: false,
                    searchable: false,
                    defaultContent: '-'
                },
                {
                    data: 'location.name',
                    name: 'location.name',
                    orderable: false,
                    searchable: false,
                    defaultContent: '-'
                },
                {
                    data: 'status',
                    name: 'status',
                    render: function (data) {
                        if (!data) return '<span class="dt-badge dt-badge-gray">—</span>';
                        const colores = {
                            'Listo': 'dt-badge-blue',
                            'En reparto': 'dt-badge-yellow',
                            'Finalizado': 'dt-badge-green',
                            'Con problemas': 'dt-badge-red'
                        };
                        const color = colores[data] || 'dt-badge-gray';
                        return '<span class="dt-badge ' + color + '">' + data + '</span>';
                    }
                },
                { data: 'guide_count', name: 'guide_count', defaultContent: '0' },
                { data: 'package_count', name: 'package_count', defaultContent: '0' },
                { data: 'acciones', name: 'acciones', orderable: false, searchable: false }
            ],
            order: [[0, 'desc']],
        });
    };

    // El modal de problemas de guías se inicializa en $(document).ready
    const initProblemModal = function () { };

    return {
        init: init
    };
})($);

$(document).ready(function () {
    DeliveryModule.init();

    $('#btn-filter').on('click', function () {
        $('#deliveries-table').DataTable().ajax.reload();
    });

    $('input[id^="filter_"], select[id^="filter_"]').on('keypress', function (e) {
        if (e.which === 13) {
            e.preventDefault();
            $('#btn-filter').click();
        }
    });

    // === Modal and Form Logic ===
    const modal = $('#shipments-modal');
    const tableBody = $('#selected-shipments-table tbody');
    let dtAvailable;
    let selectedStorage = new Map();

    $('#delivery-form').on('keypress', function (e) {
        if (e.which === 13 && e.target.tagName !== 'TEXTAREA') {
            e.preventDefault();
        }
    });

    $('.shipment-row input[name="shipments[]"]').each(function () {
        selectedStorage.set($(this).val(), null);
    });

    function toggleHeaderLock() {
        const count = selectedStorage.size;
        const $targets = $('#location_id');
        if (count > 0) {
            $targets.addClass('pointer-events-none bg-gray-100 dark:bg-gray-800 opacity-75').attr('tabindex', '-1');
            if ($targets.hasClass('select2-hidden-accessible')) {
                $targets.next('.select2-container').addClass('pointer-events-none opacity-75');
            }
        } else {
            $targets.removeClass('pointer-events-none bg-gray-100 dark:bg-gray-800 opacity-75').removeAttr('tabindex');
            if ($targets.hasClass('select2-hidden-accessible')) {
                $targets.next('.select2-container').removeClass('pointer-events-none opacity-75');
            }
        }
    }

    $('.btn-open-shipments-modal').on('click', function () {
        const locationId = $('select[name="location_id"]').val();

        // Let's get delivery id if we are editing
        const deliveryId = typeof window.deliveryId !== 'undefined' ? window.deliveryId : '';

        if (!locationId) {
            alert('Atención: Debe seleccionar una Ubicación de Reparto antes de poder buscar guías.');
            return;
        }

        modal.removeClass('hidden');
        if (!dtAvailable) {
            initAvailableShipmentsTable();
        } else {
            // Resetear búsqueda y paginación a la página 1
            dtAvailable.search('');
            dtAvailable.page('first');
            dtAvailable.ajax.reload(null, true); // true = reset paging
            updateSelectedCount();
        }
    });

    $('.btn-close-shipments-modal').on('click', function () {
        modal.addClass('hidden');
    });

    $('select[name="location_id"]').on('change', function () {
        if (dtAvailable) {
            dtAvailable.ajax.reload();
            $('#check-all-shipments').prop('checked', false);
        }
    });

    function initAvailableShipmentsTable() {
        dtAvailable = $('#available-shipments-table').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 10,
            ajax: {
                url: $('#available-shipments-table').data('url'),
                data: function (d) {
                    d.location_id = $('select[name="location_id"]').val();
                    const urlParams = new URLSearchParams(window.location.search);
                    const pathParts = window.location.pathname.split('/');
                    const editIdx = pathParts.indexOf('edit');
                    if (editIdx > -1) {
                        d.delivery_id = pathParts[editIdx - 1]; // /deliveries/{id}/edit
                    }
                    d.company_id = $('input[name="company_id"]').val();
                }
            },
            columns: [
                { data: 'check', name: 'check', orderable: false, searchable: false, className: 'text-center' },
                { 
                    data: 'numero', 
                    name: 'shipments.numero',
                    render: function (data, type, row) {
                        let html = data;
                        if (row.has_active_problem > 0) {
                            html += ` <span class="text-amber-500 font-bold ml-1 cursor-pointer btn-open-spm" 
                                data-shipment-id="${row.id}" data-shipment-numero="${row.numero}" title="Problema activo" style="color: #f59e0b !important;">⚠</span>`;
                        } else if (row.has_resolved_problem > 0) {
                            html += ` <span class="text-green-500 font-bold ml-1 cursor-pointer btn-open-spm" 
                                data-shipment-id="${row.id}" data-shipment-numero="${row.numero}" title="Problema resuelto">✓</span>`;
                        }
                        return html;
                    } 
                },
                { data: 'empresa', name: 'empresa', orderable: false, searchable: false },
                { data: 'fecha', name: 'shipments.fecha' },
                { data: 'origen_nombre', name: 'origen.nombre' },
                { data: 'destino_nombre', name: 'destino.nombre' },
                {
                    data: 'ubicacion_actual',
                    name: 'shipments.ubicacion_actual',
                    render: function (data) {
                        if (!data) return '<span class="dt-badge dt-badge-gray">—</span>';
                        const colores = {
                            'Dto origen': 'dt-badge-indigo',
                            'En transito': 'dt-badge-yellow',
                            'Dto destino': 'dt-badge-blue',
                            'En reparto': 'dt-badge-orange',
                            'Entregado': 'dt-badge-green',
                            'Con problemas': 'dt-badge-red'
                        };
                        const color = colores[data] || 'dt-badge-gray';
                        return '<span class="dt-badge ' + color + '">' + data + '</span>';
                    }
                },
                { data: 'bultos', name: 'bultos', orderable: false, searchable: false, className: 'text-right' },
            ],
            order: [[1, 'desc']],
            drawCallback: function () {
                $('.shipment-checkbox').prop('checked', false);
                $('#check-all-shipments').prop('checked', false);

                $('.shipment-checkbox').each(function () {
                    if (selectedStorage.has($(this).val())) {
                        $(this).prop('checked', true);
                    }
                });
                updateSelectedCount();
            }
        });
    }

    $(document).on('change', '.shipment-checkbox', function () {
        const id = $(this).val();
        if ($(this).is(':checked')) {
            selectedStorage.set(id, {
                numero: $(this).data('numero'),
                origen: $(this).data('origen'),
                destino: $(this).data('destino'),
                estado: $(this).data('estado'),
                bultos: $(this).data('bultos'),
                hasProblem: $(this).data('has-problem'),
                hasResolvedProblem: $(this).data('has-resolved-problem')
            });
        } else {
            selectedStorage.delete(id);
            $('#check-all-shipments').prop('checked', false);
        }
        updateSelectedCount();
    });

    $('#check-all-shipments').on('change', function () {
        const isChecked = $(this).is(':checked');
        $('.shipment-checkbox').each(function () {
            $(this).prop('checked', isChecked);
            const id = $(this).val();
            if (isChecked) {
                selectedStorage.set(id, {
                    numero: $(this).data('numero'),
                    origen: $(this).data('origen'),
                    destino: $(this).data('destino'),
                    estado: $(this).data('estado'),
                    bultos: $(this).data('bultos'),
                    hasProblem: $(this).data('has-problem'),
                    hasResolvedProblem: $(this).data('has-resolved-problem')
                });
            } else {
                selectedStorage.delete(id);
            }
        });
        updateSelectedCount();
    });

    function updateSelectedCount() {
        $('#selected-count').text(selectedStorage.size);
    }

    $('.btn-confirm-shipments').on('click', function () {
        selectedStorage.forEach(function (data, id) {
            if (!data) return;

            if (tableBody.find(`tr[data-id="${id}"]`).length === 0) {
                tableBody.find('.empty-row').remove();

                const numero = data.numero;
                const origen = data.origen;
                const destino = data.destino;
                const estado = data.estado;
                const bultos = data.bultos;

                const coloresMap = {
                    'Dto origen': 'dt-badge-indigo',
                    'En transito': 'dt-badge-yellow',
                    'Dto destino': 'dt-badge-blue',
                    'En reparto': 'dt-badge-orange',
                    'Entregado': 'dt-badge-green',
                    'Con problemas': 'dt-badge-red'
                };
                const coloresStr = coloresMap[estado] || 'dt-badge-gray';

                const hasActiveProblem = data.hasProblem === true || data.hasProblem === 'true';
                const hasResolvedProblem = data.hasResolvedProblem === true || data.hasResolvedProblem === 'true';
                
                let problemIcon = '';
                if (hasActiveProblem) {
                    problemIcon = ` <span class="text-amber-500 font-bold ml-1 animate-pulse cursor-pointer btn-open-spm" 
                        data-shipment-id="${id}"
                        data-shipment-numero="${numero}"
                        style="color: #f59e0b !important;"
                        title="Tiene un problema activo. Click para ver/resolver.">⚠</span>`;
                } else if (hasResolvedProblem) {
                    problemIcon = ` <span class="text-green-600 font-bold ml-1 cursor-pointer btn-open-spm" 
                        data-shipment-id="${id}"
                        data-shipment-numero="${numero}"
                        style="color: #10b981 !important;"
                        title="Problema resuelto. Click para ver historial.">✓</span>`;
                }

                const rowHtml = `
                    <tr class="shipment-row hover:bg-gray-50 dark:hover:bg-gray-700 transition" data-id="${id}">
                        <td class="p-3 text-sm text-gray-800 dark:text-gray-200">
                            ${numero}${problemIcon}
                            <input type="hidden" name="shipments[]" value="${id}">
                        </td>
                        <td class="p-3 text-sm text-gray-800 dark:text-gray-200">${origen}</td>
                        <td class="p-3 text-sm text-gray-800 dark:text-gray-200">${destino}</td>
                        <td class="p-3 text-sm text-gray-800 dark:text-gray-200">
                            <span class="dt-badge ${coloresStr}">${estado}</span>
                        </td>
                        <td class="p-3 text-sm text-gray-800 dark:text-gray-200">${bultos}</td>
                        <td class="p-3 text-center">
                            <button type="button" class="text-red-500 hover:text-red-700 btn-remove-shipment font-bold mr-2" title="Remover">&times;</button>
                            <button type="button" class="text-yellow-500 hover:text-yellow-700 btn-problem-shipment font-bold" title="Reportar Problema" data-id="${id}">!</button>
                        </td>
                    </tr>
                `;
                tableBody.append(rowHtml);
            }
        });

        modal.addClass('hidden');
        toggleHeaderLock();
        $('.assigned-count').text(tableBody.find('.shipment-row').length);
    });

    // === Remover Guía de la tabla ===
    $(document).on('click', '.btn-remove-shipment', function () {
        const row = $(this).closest('tr');
        const id = row.data('id').toString();

        selectedStorage.delete(id);
        row.remove();

        if (tableBody.find('.shipment-row').length === 0) {
            tableBody.append('<tr class="empty-row"><td colspan="6" class="p-4 text-center text-gray-500 text-sm">Aún no se han asignado guías</td></tr>');
        }
        toggleHeaderLock();
        $('.assigned-count').text(tableBody.find('.shipment-row').length);
    });

    // === Escuchar evento de problemas resueltos/creados ===
    $(document).on('documentProblemStored', function (e, data) {
        if (dtAvailable) {
            dtAvailable.ajax.reload(null, false);
        }
        // Si estamos en edición y hay tabla de guías seleccionadas, recargamos la página para actualizar el icono de la vista
        if ($('#delivery-form').length && $('#selected-shipments-table').length) {
            window.location.reload();
        }
    });

    // === Lógica de Devoluciones (Aviso de guías con problemas) ===
    $(document).on('click', '.btn-show-devolutions', function() {
        const deliveryId = $(this).data('model-id');
        const $modal = $('#devolution-warning-modal');
        const $list = $('#devolution-list');
        const $btnConfirm = $('#btn-confirm-finish-anyway');

        $list.data('delivery-id', deliveryId); // Guardar para uso en botones internos
        $list.html('<div class="text-center py-4"><span class="animate-spin inline-block w-6 h-6 border-2 border-indigo-500 border-t-transparent rounded-full"></span></div>');
        $btnConfirm.addClass('hidden'); 
        $modal.removeClass('hidden');

        loadDevolutionList(deliveryId);
    });

    function loadDevolutionList(deliveryId) {
        const $list = $('#devolution-list');
        $.ajax({
            url: '/documents/problem/shipments',
            data: { model_type: 'delivery', model_id: deliveryId },
            success: function(response) {
                let html = '';
                if (response.shipments.length === 0) {
                    html = '<p class="text-center text-gray-500 py-4 font-medium">No hay guías con problemas pendientes en este reparto.</p>';
                } else {
                    response.shipments.forEach(s => {
                        html += `
                            <div class="flex items-center justify-between p-3 bg-amber-50 dark:bg-amber-900/10 border border-amber-100 dark:border-amber-800 rounded-lg">
                                <div>
                                    <span class="font-mono font-bold text-amber-700 dark:text-amber-400">Guía ${s.numero}</span>
                                    <div class="text-xs text-gray-500 mt-0.5">${s.problema}</div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-amber-600 dark:text-amber-500 font-medium italic">A Devolver</span>
                                    <button type="button" class="btn-return-shipment-now px-2 py-1 bg-red-600 text-white text-xs font-bold rounded hover:bg-red-700 transition" 
                                        data-shipment-id="${s.id}" title="Quitar del reparto y devolver a Dto Destino">
                                        Devolver
                                    </button>
                                </div>
                            </div>
                        `;
                    });
                }
                $list.html(html);
            },
            error: function() {
                $list.html('<p class="text-center text-red-500 py-4">Error al cargar el listado.</p>');
            }
        });
    }

    $(document).on('click', '.btn-return-shipment-now', function() {
        const $btn = $(this);
        const shipmentId = $btn.data('shipment-id');
        const deliveryId = $('#devolution-list').data('delivery-id');

        if (!confirm('¿Seguro que desea devolver esta guía a depósito y quitarla del reparto?')) return;

        $btn.prop('disabled', true).text('...');

        $.ajax({
            url: `/deliveries/${deliveryId}/return-shipment/${shipmentId}`,
            method: 'POST',
            data: { _token: $('meta[name="csrf-token"]').attr('content') },
            success: function() {
                // Recargar lista del modal
                loadDevolutionList(deliveryId);
                // Recargar tabla principal si existe (index)
                if ($('#deliveries-table').length) {
                    $('#deliveries-table').DataTable().ajax.reload(null, false);
                }
                // Si estamos en edición, mejor recargar la página para limpiar los inputs y la tabla de guías
                if ($('#delivery-form').length) {
                    window.location.reload();
                }
            },
            error: function(xhr) {
                alert('Error: ' + (xhr.responseJSON?.message || 'No se pudo procesar la devolución.'));
                $btn.prop('disabled', false).text('Devolver');
            }
        });
    });

    $(document).on('click', '.btn-close-devolution', function() {
        $('#devolution-warning-modal').addClass('hidden');
    });

    // Se mantiene la advertencia en EDIT solo si el usuario presiona el botón específico si se agregara, 
    // pero el usuario pidió quitarlo del flujo de "Finalizar".
    // Eliminamos la interceptación de #btn-delivery-finish que abría el modal automáticamente.

    // Company selector modal
    const btn = document.getElementById('btn-crear-reparto');
    if (btn) {
        const companies = JSON.parse(btn.dataset.companies || '[]');
        const createUrl = btn.dataset.url;

        btn.addEventListener('click', () => {
            if (companies.length === 1) {
                window.location.href = `${createUrl}?company_id=${companies[0].id}`;
                return;
            }
            openCompanySelector({
                companies,
                title: 'Nuevo Reparto',
                subtitle: '¿Para qué empresa deseas crear el reparto?',
                onSelect: (company) => {
                    window.location.href = `${createUrl}?company_id=${company.id}`;
                }
            });
        });
    }

    toggleHeaderLock();
});