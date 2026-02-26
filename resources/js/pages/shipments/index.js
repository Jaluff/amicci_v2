/**
 * Listado de guías — shipments/index.blade.php
 * Depende de jQuery y DataTables cargados en app.js.
 */
$(function () {
    if (!$('#shipmentsTable').length) return;

    $('#shipmentsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: $('#shipmentsTable').data('url'),
        columns: [
            { data: 'numero', name: 'shipments.numero', responsivePriority: 1 },
            { data: 'fecha', name: 'shipments.fecha', responsivePriority: 2 },
            { data: 'origen_nombre', name: 'origen.nombre', defaultContent: '-', responsivePriority: 6 },
            { data: 'destino_nombre', name: 'destino.nombre', defaultContent: '-', responsivePriority: 6 },
            { data: 'remitente_nombre', name: 'remitente.name', defaultContent: '-', responsivePriority: 7, visible: false },
            { data: 'destinatario_nombre', name: 'destinatario.name', defaultContent: '-', responsivePriority: 7, visible: false },
            { data: 'flete', name: 'shipments.flete', responsivePriority: 5 },
            { data: 'bultos', name: 'bultos', orderable: false, searchable: false, responsivePriority: 4 },
            { data: 'valor_declarado', name: 'valor_declarado', orderable: false, searchable: false, responsivePriority: 8, visible: false },
            { data: 'total', name: 'shipments.total', responsivePriority: 3 },
            {
                data: 'ubicacion_actual', name: 'shipments.ubicacion_actual', defaultContent: '-', responsivePriority: 5,
                render: function (data) {
                    if (!data || data === '-') return '<span class="dt-badge dt-badge-gray">—</span>';
                    const colores = {
                        'Dto origen': 'dt-badge-indigo',
                        'En transito': 'dt-badge-yellow',
                        'Dto destino': 'dt-badge-blue',
                        'En reparto': 'dt-badge-green',
                        'Entregado': 'dt-badge-green',
                    };
                    const color = colores[data] || 'dt-badge-gray';
                    return '<span class="dt-badge ' + color + '">' + data + '</span>';
                }
            },
            {
                data: 'estado_facturacion', name: 'shipments.estado_facturacion', defaultContent: '-', responsivePriority: 8, visible: false,
                render: function (data) {
                    if (!data || data === '-') return '<span class="dt-badge dt-badge-gray">—</span>';
                    const colores = {
                        'No facturada': 'dt-badge-gray',
                        'Facturada': 'dt-badge-blue',
                        'Rendida': 'dt-badge-green',
                        'Anulada': 'dt-badge-red',
                    };
                    const color = colores[data] || 'dt-badge-gray';
                    return '<span class="dt-badge ' + color + '">' + data + '</span>';
                }
            },
            { data: 'acciones', name: 'acciones', orderable: false, searchable: false, responsivePriority: 1 },
        ],
        order: [[0, 'desc']],
    });
});
