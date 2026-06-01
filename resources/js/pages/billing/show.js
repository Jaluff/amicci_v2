import $ from "jquery";
import "datatables.net-dt";
import "datatables.net-buttons-dt";
import "datatables.net-buttons/js/buttons.html5.mjs";

document.addEventListener("DOMContentLoaded", function () {
    const tableEl = document.getElementById("invoice-shipments-table");
    if (!tableEl) return;

    const invoiceNum = tableEl.dataset.invoiceNumber || 'Export';

    $(tableEl).DataTable({
        paging: false,
        searching: false,
        info: false,
        ordering: false,
        dom: "Brt",
        buttons: [
            {
                extend: "excelHtml5",
                text: "📊 Exportar a Excel",
                title: "Factura_" + invoiceNum,
                footer: true,
                className: "inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 transition mr-2"
            },
            {
                extend: "pdfHtml5",
                text: "📄 Exportar a PDF (Horizontal)",
                title: "Factura_" + invoiceNum,
                orientation: "landscape",
                pageSize: "A4",
                footer: true,
                className: "inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 transition"
            }
        ],
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json"
        }
    });
});
