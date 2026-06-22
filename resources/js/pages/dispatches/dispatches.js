import $ from 'jquery';
import { openCompanySelector } from '../../shared/company-selector.js';

const DispatchModule = (function ($) {
    let dataTable;

    const init = function () {
        initDataTable();
    };

    const initDataTable = function () {
        if (!$('#dispatches-table').length) return;

        dataTable = $('#dispatches-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: $('#dispatches-table').data('url'),
                data: function (d) {
                    d.origen_id = $('#filter_origen_id').val();
                    d.destino_id = $('#filter_destino_id').val();
                    d.numero_documento = $('#filter_numero_documento').val();
                    d.estado = $('#filter_estado').val();

                    const rangeVal = $('#filter_fecha_range').val();
                    if (rangeVal && rangeVal.includes(' to ')) {
                        const parts = rangeVal.split(' to ');
                        d.fecha_inicio = parts[0];
                        d.fecha_fin = parts[1];
                    } else if (rangeVal) {
                        d.fecha_inicio = rangeVal;
                        d.fecha_fin = rangeVal;
                    } else {
                        d.fecha_inicio = '';
                        d.fecha_fin = '';
                    }
                }
            },
            columns: [
                { data: 'fecha', name: 'created_at' },
                { data: 'dispatch_number', name: 'dispatch_number' },
                {
                    data: 'driver.name',
                    name: 'driver.name',
                    defaultContent: 'No asignado'
                },
                {
                    data: 'ruta_corta',
                    name: 'ruta_corta',
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                },
                {
                    data: 'status',
                    name: 'status',
                    className: 'text-center',
                    render: function (data) {
                        if (!data) return '<span class="dt-badge dt-badge-gray">—</span>';
                        const colores = {
                            'Cargado': 'dt-badge-blue',
                            'En viaje': 'dt-badge-yellow',
                            'Arribado': 'dt-badge-green',
                        };
                        const color = colores[data] || 'dt-badge-gray';
                        return `<span class="dt-badge ${color}">${data}</span>`;
                    }
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function (data) {
                        let details = [];
                        if (data.seal_number) details.push(`Precinto: <strong>${data.seal_number}</strong>`);
                        if (data.semi_number) details.push(`Semi: <strong>${data.semi_number}</strong>`);
                        if (data.chassis_number) details.push(`Chasis: <strong>${data.chassis_number}</strong>`);
                        if (details.length === 0) return '<span class="text-gray-400 italic text-sm">Sin detalles</span>';
                        return `<div class="text-sm text-gray-600 dark:text-gray-400 space-y-0.5">${details.join('<br>')}</div>`;
                    }
                },
                {
                    data: 'cost',
                    name: 'cost',
                    render: function (data) {
                        if (!data || data == 0) return '<span class="text-gray-400 text-sm">—</span>';
                        return `$${parseFloat(data).toLocaleString('es-AR', { minimumFractionDigits: 2 })}`;
                    }
                },
                { data: 'routes_count', name: 'routes_count', defaultContent: '0', orderable: false, searchable: false, className: 'text-center' },
                { data: 'acciones', name: 'acciones', orderable: false, searchable: false, className: 'text-center' }
            ],
            order: [[0, 'desc']],
        });
    };

    return { init };
})($);

$(document).ready(function () {
    if ($('#filter_fecha_range').length) {
        flatpickr('#filter_fecha_range', {
            mode: 'range',
            dateFormat: 'Y-m-d',
            locale: 'es',
            altInput: true,
            altFormat: 'd/m/Y',
            allowInput: true
        });
    }

    DispatchModule.init();

    $('#btn-filter').on('click', function () {
        $('#dispatches-table').DataTable().ajax.reload();
    });

    $('input[id^="filter_"], select[id^="filter_"]').on('keypress', function (e) {
        if (e.which === 13) {
            e.preventDefault();
            $('#btn-filter').click();
        }
    });


});
