/**
 * Listado de guías — shipments/index.blade.php
 * Depende de jQuery y DataTables cargados en app.js.
 */
import $ from 'jquery';

$(function () {
    if (!$('#shipmentsTable').length) return;

    $('#shipmentsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: $('#shipmentsTable').data('url'),
            data: function (d) {
                d.origen_id = $('#filter_origen_id').val();
                d.destino_id = $('#filter_destino_id').val();
                d.fecha_inicio = $('#filter_fecha_inicio').val();
                d.fecha_fin = $('#filter_fecha_fin').val();
                d.numero_documento = $('#filter_numero_documento').val();
                d.cliente = $('#filter_cliente').val();
                d.ubicacion = $('#filter_ubicacion').val();
            }
        },
        columns: [
            { data: 'fecha', name: 'shipments.fecha', responsivePriority: 2 },
            { data: 'numero', name: 'shipments.numero', responsivePriority: 1 },
            { data: 'origen_nombre', name: 'origen.nombre', defaultContent: '-', responsivePriority: 6 },
            { data: 'destino_nombre', name: 'destino.nombre', defaultContent: '-', responsivePriority: 6 },
            { data: 'remitente_destinatario', name: 'remitente_destinatario', orderable: false, searchable: false, responsivePriority: 7 },
            { data: 'flete', name: 'shipments.flete', responsivePriority: 5 },
            { data: 'bultos', name: 'bultos', orderable: false, searchable: false, responsivePriority: 4 },
            { data: 'valor_declarado', name: 'valor_declarado', orderable: false, searchable: false, visible: false },
            { data: 'total', name: 'shipments.total', responsivePriority: 3 },
            {
                data: 'ubicacion_actual',
                name: 'shipments.ubicacion_actual',
                defaultContent: '-',
                responsivePriority: 5,
                orderable: true,
                searchable: true,
            },
            { data: 'acciones', name: 'acciones', orderable: false, searchable: false, responsivePriority: 1 },
        ],
        order: [[0, 'desc']],
    });

    $('#btn-filter').on('click', function () {
        $('#shipmentsTable').DataTable().ajax.reload();
    });

    $('input[id^="filter_"], select[id^="filter_"]').on('keypress', function (e) {
        if (e.which === 13) {
            e.preventDefault();
            $('#btn-filter').click();
        }
    });
});
