import $ from "jquery";
import "datatables.net-dt";
import "datatables.net-buttons-dt";
import "datatables.net-buttons/js/buttons.html5.mjs";
import "datatables.net-buttons/js/buttons.colVis.mjs";
import select2 from "select2";
import { partyAjaxConfig } from "../../shared/select2Ajax";

window.$ = window.jQuery = $;
select2();

document.addEventListener("DOMContentLoaded", function () {
    if ($("#filter_party_id").length) {
        $("#filter_party_id").select2(Object.assign({ placeholder: "Buscar cliente...", allowClear: true, width: "100%" }, partyAjaxConfig));
    }

    const tableEl = document.getElementById("invoices-list-table");
    if (!tableEl) return;

    const dataUrl = tableEl.dataset.url;

    const table = $(tableEl).DataTable({
        processing: true,
        serverSide: true,
        responsive: false,
        scrollX: true,
        paging: true,
        pageLength: 25,
        ajax: {
            url: dataUrl,
            data: function (d) {
                d.start_date = $("#filter_start_date").val();
                d.end_date   = $("#filter_end_date").val();
                d.company_id = $("#filter_company_id").val();
                d.party_id   = $("#filter_party_id").val();
                d.numero     = $("#filter_numero").val();
                d.cobrada    = $("#filter_cobrada").val();
            },
        },
        dom: "<'dt-controls'lBf>rtip",
        buttons: [
            { extend: "excelHtml5", text: "Exportar Excel", exportOptions: { columns: ":visible" } },
            { extend: "pdfHtml5",  text: "Exportar PDF", orientation: "landscape", pageSize: "LEGAL" },
        ],
        columns: [
            { data: "fecha_factura",  name: "fecha_factura" },
            { data: "numero",         name: "numero", className: "font-bold text-indigo-600 dark:text-indigo-400" },
            { data: "party_name",     name: "party_name", orderable: false, searchable: false },
            { data: "numero_recibo",  name: "numero_recibo" },
            { data: "shipments_count",name: "shipments_count", orderable: false, searchable: false },
            { data: "cobrada",        name: "cobrada" },
            { data: "total",          name: "total", className: "font-bold text-green-600 dark:text-green-400" },
            { data: "actions",        name: "actions", orderable: false, searchable: false },
        ],
        order: [[0, "desc"]],
        language: { url: "https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json" },
        createdRow: function (row, data, dataIndex) {
            $(row).addClass("text-gray-900 dark:text-gray-100");
        }
    });

    $("#btn-filter").on("click", function () { table.ajax.reload(); });
    $("input[id^='filter_']").on("keypress", function (e) { if (e.which === 13) table.ajax.reload(); });
    $("#filter_company_id, #filter_cobrada").on("change", function () { table.ajax.reload(); });
    $("#filter_party_id").on("change", function () { table.ajax.reload(); });
});
