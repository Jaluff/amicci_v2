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
                    data: 'created_at',
                    name: 'created_at',
                    render: function (data) {
                        if (!data) return '-';
                        return new Date(data).toLocaleDateString('es-AR');
                    }
                },
                { data: 'route_number', name: 'route_number' },
                {
                    data: null,
                    name: 'origin_destination',
                    orderable: false,
                    searchable: false,
                    render: function (data) {
                        const origen = data.origin ? data.origin.name : '-';
                        const destino = data.destination ? data.destination.name : '-';
                        return `<strong>${origen}</strong> &rarr; <strong>${destino}</strong>`;
                    }
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
                { data: 'shipments_count', name: 'shipments_count', defaultContent: '0' },
                { data: 'acciones', name: 'acciones', orderable: false, searchable: false }
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
    RouteModule.init();

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