import $ from 'jquery';

$(function () {
    const modal = $('#routes-modal');
    const tableBody = $('#selected-routes-table tbody');
    let dtAvailable;
    let selectedStorage = new Set();

    // Prevenir submit con Enter
    $('#dispatch-form').on('keypress', function (e) {
        if (e.which === 13 && e.target.tagName !== 'TEXTAREA') {
            e.preventDefault();
        }
    });

    // Cargar rutas preexistentes (modo edición)
    $('.route-row input[name="routes[]"]').each(function () {
        selectedStorage.add($(this).val());
    });

    // Control origen/destino
    function handleLocationSelects() {
        const originSelect = $('select[name="origin_id"]');
        const destSelect = $('select[name="destination_id"]');

        const updateOptions = function () {
            const originVal = originSelect.val();
            destSelect.find('option').prop('disabled', false);
            if (originVal) {
                destSelect.find(`option[value="${originVal}"]`).prop('disabled', true);
                if (destSelect.val() === originVal) destSelect.val('');
            }
        };

        originSelect.on('change', function () {
            destSelect.val('');
            updateOptions();
        });

        updateOptions();
    }
    handleLocationSelects();

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
            dtAvailable.ajax.reload(null, false);
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
                { data: 'route_number', name: 'route_number' },
                { data: 'driver.name', name: 'driver.name', defaultContent: '-' },
                { data: 'origen_nombre', name: 'origen.nombre' },
                { data: 'destino_nombre', name: 'destino.nombre' },
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
        if ($(this).is(':checked')) {
            selectedStorage.add($(this).val());
        } else {
            selectedStorage.delete($(this).val());
            $('#check-all-routes').prop('checked', false);
        }
        updateSelectedCount();
    });

    // Check all
    $('#check-all-routes').on('change', function () {
        const isChecked = $(this).is(':checked');
        $('.route-checkbox').each(function () {
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
        $('#selected-routes-count').text(selectedStorage.size);
    }

    // Confirmar selección
    $('.btn-confirm-routes').on('click', function () {
        $('.route-checkbox:checked').each(function () {
            const id = $(this).val();

            if (tableBody.find(`tr[data-id="${id}"]`).length === 0) {
                tableBody.find('.empty-row').remove();

                const numero = $(this).data('numero');
                const origen = $(this).data('origen');
                const destino = $(this).data('destino');
                const estado = $(this).data('estado');
                const rutas = $(this).data('rutas');

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
                            ${numero}
                            <input type="hidden" name="routes[]" value="${id}">
                        </td>
                        <td class="p-3 text-sm text-gray-800 dark:text-gray-200">${origen}</td>
                        <td class="p-3 text-sm text-gray-800 dark:text-gray-200">${destino}</td>
                        <td class="p-3 text-sm text-gray-800 dark:text-gray-200">
                            <span class="dt-badge ${coloresStr}">${estado}</span>
                        </td>
                        <td class="p-3 text-sm text-gray-800 dark:text-gray-200">${rutas}</td>
                        <td class="p-3 text-center">
                            <button type="button" class="text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900/40 p-1 rounded transition btn-remove-route font-bold text-lg leading-none" title="Remover">&times;</button>
                        </td>
                    </tr>
                `;
                tableBody.append(rowHtml);
            }
        });

        modal.addClass('hidden');
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
    });
});
