import $ from 'jquery';

$(function () {
    const modal = $('#shipments-modal');
    const tableBody = $('#selected-shipments-table tbody');
    let dtAvailable;
    let selectedStorage = new Set();

    // Evitar que el form se envíe al presionar enter accidentalmente (común con DataTables si se busca)
    $('#route-form').on('keypress', function (e) {
        if (e.which === 13 && e.target.tagName !== 'TEXTAREA') {
            e.preventDefault();
        }
    });

    // Rellenamos el Set con las guías que ya venían seleccionadas (en modo edición)
    $('.shipment-row input[name="shipments[]"]').each(function () {
        selectedStorage.add($(this).val());
    });

    // Control de selectores de Origen y Destino (para evitar seleccionar el mismo)
    function handleLocationSelects() {
        const originSelect = $('select[name="origin_id"]');
        const destSelect = $('select[name="destination_id"]');

        const updateOptions = function () {
            const originVal = originSelect.val();

            // Resetear las opciones del destino
            destSelect.find('option').prop('disabled', false);

            if (originVal) {
                // Bloquear el valor elegido en origen dentro de destino
                destSelect.find(`option[value="${originVal}"]`).prop('disabled', true);

                // Por precaución, si por estado viejo el destino resulta ser el origen, lo limpiamos
                if (destSelect.val() === originVal) {
                    destSelect.val('');
                }
            }
        };

        originSelect.on('change', function () {
            // Cuando cambie origen, siempre se resetea destino
            destSelect.val('');
            updateOptions();
        });

        // Ejecutar al cargar la vista
        updateOptions();
    }
    handleLocationSelects();

    // Abrir Modal
    $('.btn-open-shipments-modal').on('click', function () {
        const originId = $('select[name="origin_id"]').val();
        const destinationId = $('select[name="destination_id"]').val();

        if (!originId || !destinationId) {
            alert('Atención: Debe seleccionar un Origen y un Destino antes de poder buscar guías.');
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

    // Cerrar Modal
    $('.btn-close-shipments-modal').on('click', function () {
        modal.addClass('hidden');
    });

    // Escuchar cambios en los selects para recargar Datatable si los cambian (o limpiar si quieren buscar)
    $('select[name="origin_id"], select[name="destination_id"]').on('change', function () {
        if (dtAvailable) {
            dtAvailable.ajax.reload();
            $('#check-all-shipments').prop('checked', false);
        }
    });

    // Iniciar DataTable
    function initAvailableShipmentsTable() {
        dtAvailable = $('#available-shipments-table').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 10,
            ajax: {
                url: $('#available-shipments-table').data('url'),
                data: function (d) {
                    d.origin_id = $('select[name="origin_id"]').val();
                    d.destination_id = $('select[name="destination_id"]').val();
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
                        };
                        const color = colores[data] || 'dt-badge-gray';
                        return '<span class="dt-badge ' + color + '">' + data + '</span>';
                    }
                },
                { data: 'bultos', name: 'bultos', orderable: false, searchable: false, className: 'text-right' },
            ],
            order: [[1, 'desc']],
            /* language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json' }, */
            drawCallback: function () {
                // Recuperar estado de checks persistentes
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

    // Eventos Modal - Checkbox
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

    // Confirmar adición desde el Modal hacia el Formulario subyacente
    $('.btn-confirm-shipments').on('click', function () {
        // Recorremos los checkboxes del DOM actual en DT
        $('.shipment-checkbox:checked').each(function () {
            const id = $(this).val();

            // Verificamos si la fila ya ha sido agregada a la tabla para evitar duplicados
            if (tableBody.find(`tr[data-id="${id}"]`).length === 0) {
                // Removemos row "vacío" si existe
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
                            <button type="button" class="text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900/40 p-1 rounded transition btn-remove-shipment font-bold text-lg leading-none" title="Remover">&times;</button>
                        </td>
                    </tr>
                `;
                tableBody.append(rowHtml);
            }
        });

        modal.addClass('hidden');
    });

    // Remover guía de la tabla principal
    $(document).on('click', '.btn-remove-shipment', function () {
        const row = $(this).closest('tr');
        const id = row.data('id').toString();

        selectedStorage.delete(id); // importante quitarlo del Set también por si abren el modal de vuelta
        row.remove();

        if (tableBody.find('.shipment-row').length === 0) {
            tableBody.append('<tr class="empty-row"><td colspan="5" class="p-4 text-center text-gray-500 text-sm">Aún no se han asignado guías</td></tr>');
        }
    });

});
