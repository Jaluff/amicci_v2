$(function () {
    const modal = $('#routes-modal');
    const tableBody = $('#selected-routes-table tbody');
    let dtAvailable;
    let selectedStorage = new Map();

    // Prevenir submit con Enter
    $('#dispatch-form').on('keypress', function (e) {
        if (e.which === 13 && e.target.tagName !== 'TEXTAREA') {
            e.preventDefault();
        }
    });

    // Cargar rutas preexistentes (modo edición)
    $('.route-row input[name="routes[]"]').each(function () {
        selectedStorage.set($(this).val(), null);
    });

    function toggleHeaderLock() {
        const count = selectedStorage.size;
        const $targets = $('#origin_id, #destination_id');
        if (count > 0) {
            $targets.addClass('pointer-events-none bg-gray-100 dark:bg-gray-800 opacity-75').attr('tabindex', '-1');
        } else {
            $targets.removeClass('pointer-events-none bg-gray-100 dark:bg-gray-800 opacity-75').removeAttr('tabindex');
        }
    }

    // Control origen/destino
    function handleLocationSelects() {
        const originSelect = $('#origin_id');
        const destSelect = $('select[name="destination_id"]');
        const branchSelect = $('#branch_id');

        const updateOptions = function () {
            // Ya no bloqueamos el destino si es igual al origen
        };

        originSelect.on('change', function () {
            // destSelect.val(''); // Comentado para mantener fluidez
            updateOptions();
        });

        // Auto-seleccionar Origen al cambiar de Sucursal
        if (branchSelect.length && branchSelect.is('select')) {
            branchSelect.on('change', function() {
                const selected = $(this).find('option:selected');
                const ubicacionId = selected.data('ubicacion');
                if (ubicacionId) {
                    originSelect.val(ubicacionId).trigger('change');
                }
            });
            // Disparar inicialmente si estamos en creación
            if (!$('input[name="_method"][value="PUT"]').length) {
                branchSelect.trigger('change');
            }
        }

        updateOptions();
    }
    handleLocationSelects();
    toggleHeaderLock();

    // Abrir Modal
    $('.btn-open-routes-modal').on('click', function () {
        const originId = $('select[name="origin_id"]').val();
        const destinationId = $('select[name="destination_id"]').val();

        if (!originId || !destinationId) {
            alert('Debe seleccionar un Origen y un Destino antes de buscar rutas.');
            return;
        }

        modal.removeClass('hidden');
        if (!dtAvailable) {
            initAvailableRoutesTable();
        } else {
            // Resetear búsqueda y paginación a la página 1
            dtAvailable.search('');
            dtAvailable.page('first');
            dtAvailable.ajax.reload(null, true); // true = reset paging
            updateSelectedCount();
        }
    });

    // Cerrar Modal
    $(document).on('click', '.btn-close-routes-modal', function () {
        modal.addClass('hidden');
    });

    // Recargar DT al cambiar origen/destino
    $('select[name="origin_id"], select[name="destination_id"]').on('change', function () {
        if (dtAvailable) {
            dtAvailable.ajax.reload();
            $('#check-all-routes').prop('checked', false);
        }
    });

    // Iniciar DataTable de rutas disponibles
    function initAvailableRoutesTable() {
        dtAvailable = $('#available-routes-table').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 10,
            ajax: {
                url: $('#available-routes-table').data('url'),
                data: function (d) {
                    d.origin_id = $('select[name="origin_id"]').val();
                    d.destination_id = $('select[name="destination_id"]').val();
                }
            },
            columns: [
                { data: 'check', name: 'check', orderable: false, searchable: false, className: 'text-center' },
                { 
                    data: 'route_number', 
                    name: 'route_number',
                    render: function (data, type, row) {
                        let html = data;
                        if (row.problem_count > 0) {
                            html += ` <span class="text-amber-500 font-bold ml-1 animate-pulse cursor-pointer problem-badge" 
                                data-model-type="route" 
                                data-model-id="${row.id}" 
                                data-label="Ruta #${data}" 
                                style="color: #f59e0b !important;"
                                title="Contiene guías con problemas">⚠</span>`;
                        }
                        return html;
                    }
                },
                { data: 'empresa', name: 'empresa', orderable: false, searchable: false },
                { data: 'driver.name', name: 'driver.name', defaultContent: '-' },
                { data: 'origen_nombre', name: 'origen_nombre' },
                { data: 'destino_nombre', name: 'destino_nombre' },
                {
                    data: 'status',
                    name: 'status',
                    render: function (data) {
                        if (!data) return '<span class="dt-badge dt-badge-gray">—</span>';
                        const colores = {
                            'Cargada': 'dt-badge-blue',
                            'En viaje': 'dt-badge-yellow',
                            'Entregada': 'dt-badge-green',
                            'Con problemas': 'dt-badge-red'
                        };
                        const color = colores[data] || 'dt-badge-gray';
                        return '<span class="dt-badge ' + color + '">' + data + '</span>';
                    }
                },
                { data: 'shipments_count', name: 'shipments_count', orderable: false, searchable: false, className: 'text-right' },
            ],
            order: [[1, 'desc']],
            drawCallback: function () {
                $('.route-checkbox').prop('checked', false);
                $('#check-all-routes').prop('checked', false);
                $('.route-checkbox').each(function () {
                    if (selectedStorage.has($(this).val())) {
                        $(this).prop('checked', true);
                    }
                });
                updateSelectedCount();
            }
        });
    }

    // Checkbox individual
    $(document).on('change', '.route-checkbox', function () {
        const id = $(this).val();
        if ($(this).is(':checked')) {
            selectedStorage.set(id, {
                numero: $(this).data('numero'),
                origen: $(this).data('origen'),
                destino: $(this).data('destino'),
                estado: $(this).data('estado'),
                rutas: $(this).data('rutas'),
                hasProblem: $(this).data('has-problem')
            });
        } else {
            selectedStorage.delete(id);
            $('#check-all-routes').prop('checked', false);
        }
        updateSelectedCount();
    });

    // Check all
    $('#check-all-routes').on('change', function () {
        const isChecked = $(this).is(':checked');
        $('.route-checkbox').each(function () {
            $(this).prop('checked', isChecked);
            const id = $(this).val();
            if (isChecked) {
                selectedStorage.set(id, {
                    numero: $(this).data('numero'),
                    origen: $(this).data('origen'),
                    destino: $(this).data('destino'),
                    estado: $(this).data('estado'),
                    rutas: $(this).data('rutas'),
                    hasProblem: $(this).data('has-problem')
                });
            } else {
                selectedStorage.delete(id);
            }
        });
        updateSelectedCount();
    });

    function updateSelectedCount() {
        $('#selected-routes-count').text(selectedStorage.size);
    }

    // Confirmar selección
    $('.btn-confirm-routes').on('click', function () {
        selectedStorage.forEach(function (data, id) {
            if (!data) return;

            if (tableBody.find(`tr[data-id="${id}"]`).length === 0) {
                tableBody.find('.empty-row').remove();

                const numero = data.numero;
                const origen = data.origen;
                const destino = data.destino;
                const estado = data.estado;
                const rutas = data.rutas;
                const hasProblem = data.hasProblem === true || data.hasProblem === 'true';
                const problemIcon = hasProblem ? ` <span class="text-amber-500 font-bold ml-1 animate-pulse cursor-pointer problem-badge" 
                    data-model-type="route" 
                    data-model-id="${id}" 
                    data-label="Ruta #${numero}" 
                    style="color: #f59e0b !important;"
                    title="Contiene guías con problemas">⚠</span>` : '';

                const coloresMap = {
                    'Cargada': 'dt-badge-blue',
                    'En viaje': 'dt-badge-yellow',
                    'Entregada': 'dt-badge-green',
                    'Con problemas': 'dt-badge-red'
                };
                const coloresStr = coloresMap[estado] || 'dt-badge-gray';

                const rowHtml = `
                    <tr class="route-row hover:bg-gray-50 dark:hover:bg-gray-700 transition" data-id="${id}">
                        <td class="p-3 text-sm text-gray-800 dark:text-gray-200">
                            ${numero}${problemIcon}
                            <input type="hidden" name="routes[]" value="${id}">
                        </td>
                        <td class="p-3 text-sm text-gray-800 dark:text-gray-200">${origen}</td>
                        <td class="p-3 text-sm text-gray-800 dark:text-gray-200">${destino}</td>
                        <td class="p-3 text-sm text-gray-800 dark:text-gray-200">
                            <span class="dt-badge ${coloresStr}">${estado}</span>
                        </td>
                        <td class="p-3 text-sm text-gray-800 dark:text-gray-200">${rutas}</td>
                        <td class="p-3 text-center">
                            <button type="button" class="text-amber-500 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900/40 p-1 rounded transition btn-remove-route font-bold text-lg leading-none" title="Remover">&times;</button>
                        </td>
                    </tr>
                `;
                tableBody.append(rowHtml);
            }
        });

        modal.addClass('hidden');
        toggleHeaderLock();
        $('.assigned-count').text(tableBody.find('.route-row').length);
    });

    // Remover ruta de la tabla principal
    $(document).on('click', '.btn-remove-route', function () {
        const row = $(this).closest('tr');
        const id = row.data('id').toString();

        selectedStorage.delete(id);
        row.remove();

        if (tableBody.find('.route-row').length === 0) {
            tableBody.append('<tr class="empty-row"><td colspan="5" class="p-4 text-center text-gray-500 text-sm">Aún no se han asignado rutas</td></tr>');
        }
        toggleHeaderLock();
        $('.assigned-count').text(tableBody.find('.route-row').length);
    });
    // --- MODAL DE IMPRESIÓN DE GUÍAS POR RUTA ---
    const printModal = $('#print-route-modal');
    
    $(document).on('click', '.btn-print-route-guides', function() {
        const routeId = $(this).data('route-id');
        const routeNumber = $(this).data('route-number');
        
        $('#modal-route-number').text(routeNumber);
        $('#print-guides-body').html('<tr><td colspan="4" class="p-8 text-center text-gray-500">Cargando guías...</td></tr>');
        printModal.removeClass('hidden');
        
        // Cargar guías vía AJAX
        $.get(`/routes/${routeId}/shipments`, function(shipments) {
            let html = '';
            if (shipments.length === 0) {
                html = '<tr><td colspan="4" class="p-8 text-center text-gray-500">No hay guías asignadas a esta ruta.</td></tr>';
            } else {
                shipments.forEach(shipment => {
                    const remitente = shipment.sender ? shipment.sender.name : '-';
                    const destinatario = shipment.recipient ? shipment.recipient.name : '-';
                    
                    html += `
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <td class="p-3 text-center">
                            <input type="checkbox" name="ids[]" value="${shipment.id}" class="print-guide-checkbox w-4 h-4 text-blue-600 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700" checked>
                        </td>
                        <td class="p-3 text-sm font-bold text-gray-800 dark:text-gray-200">${shipment.numero}</td>
                        <td class="p-3 text-sm text-gray-600 dark:text-gray-400">${remitente}</td>
                        <td class="p-3 text-sm text-gray-600 dark:text-gray-400">${destinatario}</td>
                        <td class="p-3 text-sm text-center font-mono text-gray-500">${shipment.bultos || 0}</td>
                    </tr>`;
                });
            }
            $('#print-guides-body').html(html);
            $('#check-all-print').prop('checked', true);
        });
    });

    $(document).on('click', '.btn-close-print-modal', function() {
        printModal.addClass('hidden');
    });

    $('#check-all-print').on('change', function() {
        $('.print-guide-checkbox').prop('checked', $(this).is(':checked'));
    });
});
$(document).on('documentProblemStored', function (e, data) {
    if ($('#dispatch-form').length && $('#selected-routes-table').length) {
        window.location.reload();
    }
});
