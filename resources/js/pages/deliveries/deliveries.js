import $ from 'jquery';

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
            ajax: $('#deliveries-table').data('url'),
            columns: [
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
                    data: null,
                    name: 'deliverer',
                    orderable: false,
                    searchable: false,
                    render: function (data) {
                        return data.deliverer ? data.deliverer.name : '-';
                    }
                },
                {
                    data: null,
                    name: 'location',
                    orderable: false,
                    searchable: false,
                    render: function (data) {
                        return data.location ? data.location.nombre : '-';
                    }
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
                { data: 'problemas', name: 'problemas', orderable: false, searchable: false },
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

    // === Modal and Form Logic ===
    const modal = $('#shipments-modal');
    const tableBody = $('#selected-shipments-table tbody');
    let dtAvailable;
    let selectedStorage = new Set();

    $('#delivery-form').on('keypress', function (e) {
        if (e.which === 13 && e.target.tagName !== 'TEXTAREA') {
            e.preventDefault();
        }
    });

    $('.shipment-row input[name="shipments[]"]').each(function () {
        selectedStorage.add($(this).val());
    });

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
            dtAvailable.ajax.reload(null, false);
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
                }
            },
            columns: [
                { data: 'check', name: 'check', orderable: false, searchable: false, className: 'text-center' },
                { data: 'numero', name: 'shipments.numero' },
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
        if ($(this).is(':checked')) {
            selectedStorage.add($(this).val());
        } else {
            selectedStorage.delete($(this).val());
            $('#check-all-shipments').prop('checked', false);
        }
        updateSelectedCount();
    });

    $('#check-all-shipments').on('change', function () {
        const isChecked = $(this).is(':checked');
        $('.shipment-checkbox').each(function () {
            $(this).prop('checked', isChecked);
            if (isChecked) {
                selectedStorage.add($(this).val());
            } else {
                selectedStorage.delete($(this).val());
            }
        });
        updateSelectedCount();
    });

    function updateSelectedCount() {
        $('#selected-count').text(selectedStorage.size);
    }

    $('.btn-confirm-shipments').on('click', function () {
        $('.shipment-checkbox:checked').each(function () {
            const id = $(this).val();

            if (tableBody.find(`tr[data-id="${id}"]`).length === 0) {
                tableBody.find('.empty-row').remove();

                const numero = $(this).data('numero');
                const origen = $(this).data('origen');
                const destino = $(this).data('destino');
                const estado = $(this).data('estado');
                const bultos = $(this).data('bultos');

                const coloresMap = {
                    'Dto origen': 'dt-badge-indigo',
                    'En transito': 'dt-badge-yellow',
                    'Dto destino': 'dt-badge-blue',
                    'En reparto': 'dt-badge-orange',
                    'Entregado': 'dt-badge-green',
                    'Con problemas': 'dt-badge-red'
                };
                const coloresStr = coloresMap[estado] || 'dt-badge-gray';

                const rowHtml = `
                    <tr class="shipment-row hover:bg-gray-50 dark:hover:bg-gray-700 transition" data-id="${id}">
                        <td class="p-3 text-sm text-gray-800 dark:text-gray-200">
                            ${numero}
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
    });

    // === Problemas de Guías Modal ===
    const spmModal = $('#shipment-problems-modal');

    $(document).on('click', '.btn-problem-shipment', function () {
        const row = $(this).closest('tr');
        const id = $(this).data('id');
        const numeroText = row.find('td').first().text().trim();

        $('#spm-shipment-id').val(id);
        $('#spm-guia-numero').text(numeroText);
        $('#spm-comment').val('');
        $('input[name="spm_active"][value="1"]').prop('checked', true);
        $('#spm-history').html('<p class="text-center text-gray-500 py-4">Cargando historial...</p>');

        spmModal.removeClass('hidden');

        $.ajax({
            url: '/documents/problem',
            method: 'GET',
            data: {
                model_type: 'shipment',
                model_id: id
            },
            success: function (res) {
                if (res.has_active) {
                    $('#spm-resolve-radio-label').show();
                } else {
                    $('#spm-resolve-radio-label').hide();
                }

                if (res.history.length === 0) {
                    $('#spm-history').html('<p class="text-center text-sm text-gray-500 dark:text-gray-400 italic">Sin problemas registrados.</p>');
                    return;
                }

                let html = '';
                res.history.forEach(item => {
                    const date = new Date(item.created_at).toLocaleString('es-AR', { dateStyle: 'short', timeStyle: 'short' });
                    const activeStr = item.is_active ? '<span class="text-red-500 font-semibold">Abierto</span>' : '<span class="text-green-500 font-semibold">Resuelto</span>';
                    const dotColor = item.is_active ? 'bg-red-500' : 'bg-green-500';
                    const user = item.user ? item.user.name : 'Sistema';

                    html += `
                    <div class="flex items-start gap-2 text-sm bg-gray-50 dark:bg-gray-900/30 p-2 rounded">
                        <span class="mt-1 w-2 h-2 rounded-full flex-shrink-0 ${dotColor}"></span>
                        <div>
                            <p class="text-gray-800 dark:text-gray-200">${item.comment}</p>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                ${user} · ${date} · ${activeStr}
                            </p>
                        </div>
                    </div>`;
                });
                $('#spm-history').html(html);
            },
            error: function () {
                $('#spm-history').html('<p class="text-center text-red-500">Error al cargar historial.</p>');
            }
        });
    });

    $('.btn-close-spm-modal').on('click', function () {
        spmModal.addClass('hidden');
    });

    $('#spm-form').on('submit', function (e) {
        e.preventDefault();
        const id = $('#spm-shipment-id').val();
        const isActive = $('input[name="spm_active"]:checked').val();
        const comment = $('#spm-comment').val();

        const submitBtn = $(this).find('button[type="submit"]');
        submitBtn.prop('disabled', true).text('Guardando...');

        $.ajax({
            url: '/documents/problem',
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                model_type: 'shipment',
                model_id: id,
                is_active: isActive,
                comment: comment
            },
            success: function (res) {
                spmModal.addClass('hidden');

                // Agregamos o quitamos el texto de problema rojo de la fila
                const theRow = $('#selected-shipments-table').find(`tr[data-id="${id}"]`);
                if (theRow.length > 0) {
                    const statusTd = theRow.find('td:eq(3)'); // la celda de estado 
                    statusTd.find('span.text-red-500').remove(); // limpiamos anterior
                    if (isActive == '1') {
                        statusTd.append('<span class="text-red-500 font-bold ml-2 text-xs" data-active-problem="true">(⚠ PROBLEMA)</span>');
                    }
                }

                // Refrescamos datatables si está disponible (por si el status impacta en la tabla master de Repartos)
                if (dataTable) {
                    dataTable.ajax.reload(null, false);
                }
            },
            error: function (xhr) {
                alert('Error al reportar problema: ' + (xhr.responseJSON?.message || 'Error desconocido'));
            },
            complete: function () {
                submitBtn.prop('disabled', false).text('Guardar Registro');
            }
        });
    });
});