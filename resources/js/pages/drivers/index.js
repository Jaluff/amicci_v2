import $ from 'jquery';

$(function () {
    const tableEl = $('#driversTable');
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
            { data: "name", name: "name" },
            { data: "dni", name: "dni" },
            { data: "license_number", name: "license_number" },
            { data: "phone", name: "phone" },
            { data: "email", name: "email" },
            { data: "address", name: "address" },
            {
                data: "acciones",
                name: "acciones",
                orderable: false,
                searchable: false,
            },
        ],
        order: [[0, "asc"]],
    });
});
