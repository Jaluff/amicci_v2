import $ from 'jquery';

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
            ajax: $('#routes-table').data('url'),
            columns: [
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
                        const origen = data.origin ? data.origin.nombre : '-';
                        const destino = data.destination ? data.destination.nombre : '-';
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
                { data: 'problemas', name: 'problemas', orderable: false, searchable: false },
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
});