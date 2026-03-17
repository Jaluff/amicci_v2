import $ from 'jquery';

$(function () {
    const tableEl = $('#branches-table');
    if (!tableEl.length) return;

    const dataUrl = tableEl.data('url');

    tableEl.DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: dataUrl,
            type: "GET",
        },
        columns: [
            { data: 'id',                   name: 'id' },
            { data: 'name',                 name: 'name' },
            { data: 'ubicacion_nombre',     name: 'ubicacion_nombre' },
            { data: 'code',                 name: 'code' },
            { data: 'last_shipment_number', name: 'last_shipment_number' },
            { data: 'estado',               name: 'estado', orderable: false },
            { data: 'acciones',             name: 'acciones', orderable: false, searchable: false },
        ],
        order: [[0, "desc"]],
    });
});
