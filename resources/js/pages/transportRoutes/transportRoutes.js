import $ from 'jquery';
import { openCompanySelector } from '../../shared/company-selector.js';

const RouteModule = (function ($) {
    let dataTable;

    const init = function () {
        initDataTable();
    };

    const initDataTable = function () {
        if (!$('#routes-table').length) return;

        dataTable = $('#routes-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: $('#routes-table').data('url'),
                data: function (d) {
                    d.origen_id = $('#filter_origen_id').val();
                    d.destino_id = $('#filter_destino_id').val();
                    d.numero_documento = $('#filter_numero_documento').val();
                    d.estado = $('#filter_estado').val();
                    d.company_id = $('#filter_company_id').val();

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
                { 
                    data: 'empresa', 
                    name: 'companies.prefix',
                    className: 'text-center',
                    render: function(data) {
                        return data; // El HTML viene del servidor
                    }
                },
                {
                    data: 'created_at',
                    name: 'created_at',
                    render: function (data) {
                        if (!data) return '-';
                        return new Date(data).toLocaleDateString('es-AR');
                    }
                },
                { data: 'route_number', name: 'route_number' },
                {
                    data: 'ruta_corta',
                    name: 'ruta_corta',
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                },
                {
                    data: null,
                    name: 'dispatch_details',
                    orderable: false,
                    searchable: false,
                    render: function (data) {
                        if (!data.dispatch || (!data.dispatch.semi_number && !data.dispatch.chassis_number && !data.dispatch.seal_number)) {
                            return '<span class="text-gray-400 italic">No asignado</span>';
                        }

                        let details = [];
                        if (data.dispatch.semi_number) details.push(`Semi: ${data.dispatch.semi_number}`);
                        if (data.dispatch.chassis_number) details.push(`Chasis: ${data.dispatch.chassis_number}`);
                        if (data.dispatch.seal_number) details.push(`Precinto: ${data.dispatch.seal_number}`);

                        return details.join(' | ');
                    }
                },
                {
                    data: 'status',
                    name: 'status',
                    className: 'text-center',
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
                { data: 'shipments_count', name: 'shipments_count', defaultContent: '0', orderable: false, searchable: false, className: 'text-center' },
                { data: 'acciones', name: 'acciones', orderable: false, searchable: false, className: 'text-center' }
            ],
            order: [[0, 'desc']],
            /* language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
            } */
        });
    };

    return {
        init: init
    };
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

    RouteModule.init();

    if ($.fn.select2) {
        $('#filter_company_id, #filter_origen_id, #filter_destino_id, #filter_estado').select2({
            width: '100%',
            minimumResultsForSearch: 10
        });
    }

    $('#btn-filter').on('click', function () {
        $('#routes-table').DataTable().ajax.reload();
    });

    $('input[id^="filter_"], select[id^="filter_"]').on('keypress', function (e) {
        if (e.which === 13) {
            e.preventDefault();
            $('#btn-filter').click();
        }
    });

    // Company selector modal
    const btn = document.getElementById('btn-crear-ruta');
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
                title: 'Nueva Ruta de Transporte',
                subtitle: '¿Para qué empresa deseas crear la ruta?',
                onSelect: (c) => { window.location.href = `${createUrl}?company_id=${c.id}`; }
            });
        });
    }
});