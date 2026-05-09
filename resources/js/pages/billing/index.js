import $ from "jquery";
import "datatables.net-dt";
import "datatables.net-buttons-dt";
import "datatables.net-buttons/js/buttons.html5.mjs";
import "datatables.net-buttons/js/buttons.colVis.mjs";
import select2 from "select2";

window.$ = window.jQuery = $;
select2();

document.addEventListener("DOMContentLoaded", function () {

    // Select2 para el filtro de cliente
    if ($("#filter_party_id").length) {
        $("#filter_party_id").select2({
            placeholder: "Buscar cliente...",
            allowClear: true,
            width: "100%",
        });
    }

    if ($("#invoice_party_id").length) {
        $("#invoice_party_id").select2({
            placeholder: "Seleccione cliente...",
            allowClear: true,
            width: "100%",
            dropdownParent: $('#modal-generate-invoice')
        });
    }

    const tableEl = document.getElementById("invoices-table");
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
                d.start_date     = $("#filter_start_date").val();
                d.end_date       = $("#filter_end_date").val();
                d.company_id     = $("#filter_company_id").val();
                d.party_id       = $("#filter_party_id").val();
                d.numero         = $("#filter_numero").val();
                d.invoice_numero = $("#filter_invoice_numero").val();
                d.facturada      = $("#filter_facturada").val();
                d.cobrada        = $("#filter_cobrada").val();
            },
        },
        dom: "<'dt-controls'lBf>rtip",
        buttons: [
            { extend: "colvis",    text: "Columnas" },
            { extend: "excelHtml5", text: "Exportar Excel", exportOptions: { columns: ":visible" } },
            { extend: "pdfHtml5",  text: "Exportar PDF", orientation: "landscape", pageSize: "LEGAL", exportOptions: { columns: ":visible" } },
        ],
        columns: [
            { data: "selection",      name: "selection",      orderable: false, searchable: false },
            { data: "fecha",          name: "shipments.fecha" },
            { data: "numero",         name: "shipments.numero" },
            { data: "sender_name",    name: "sender_name",    orderable: false, searchable: false },
            { data: "recipient_name", name: "recipient_name", orderable: false, searchable: false },
            { data: "invoice_badge",  name: "invoice_badge",  orderable: false, searchable: false },
            { data: "cobrada",        name: "shipments.cobrada" },
            { data: "total",          name: "shipments.total" },
            { data: "actions",        name: "actions",        orderable: false, searchable: false },
        ],
        order: [[1, "desc"]], // Ordenar por fecha (ahora en la columna 1)
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json",
        },
        createdRow: function(row, data, dataIndex) {
            $(row).addClass('text-gray-700 dark:text-gray-300');
        }
    });

    // Eventos de Filtro
    $("#btn-filter").on("click", function () {
        table.ajax.reload();
    });

    $("input[id^='filter_']").on("keypress", function (e) {
        if (e.which === 13) table.ajax.reload();
    });

    $("#filter_company_id, #filter_facturada, #filter_cobrada").on("change", function () {
        table.ajax.reload();
    });

    $("#filter_party_id").on("change", function () {
        table.ajax.reload();
    });

    // -------------------------------------------------------------
    // Lógica de Selección y Barra Flotante
    // -------------------------------------------------------------
    let selectedRows = [];
    let accumulatedTotal = 0;

    function updateSelectionUI() {
        const count = selectedRows.length;
        if (count > 0) {
            $("#selection-bar").removeClass("hidden");
            $("#selected-count").text(count);
            $("#selected-total").text(
                new Intl.NumberFormat("es-AR", {
                    style: "currency",
                    currency: "ARS",
                }).format(accumulatedTotal)
            );
        } else {
            $("#selection-bar").addClass("hidden");
        }
        
        // Sincronizar el select-all
        const availableCheckboxes = $(".row-select:not(:disabled)").length;
        const checkedCheckboxes = $(".row-select:checked").length;
        $("#select-all").prop("checked", availableCheckboxes > 0 && availableCheckboxes === checkedCheckboxes);
    }

    $("#invoices-table").on("change", ".row-select", function () {
        const id = $(this).val();
        const rawTotal = parseFloat($(this).data("total")) || 0;

        if ($(this).is(":checked")) {
            if (!selectedRows.includes(id)) {
                selectedRows.push(id);
                accumulatedTotal += rawTotal;
            }
        } else {
            selectedRows = selectedRows.filter((rowId) => rowId !== id);
            accumulatedTotal -= rawTotal;
        }
        updateSelectionUI();
    });

    $("#select-all").on("change", function () {
        const isChecked = $(this).is(":checked");
        $(".row-select:not(:disabled)").each(function () {
            const id = $(this).val();
            const rawTotal = parseFloat($(this).data("total")) || 0;

            if (isChecked && !$(this).is(":checked")) {
                $(this).prop("checked", true);
                selectedRows.push(id);
                accumulatedTotal += rawTotal;
            } else if (!isChecked && $(this).is(":checked")) {
                $(this).prop("checked", false);
                selectedRows = selectedRows.filter((rowId) => rowId !== id);
                accumulatedTotal -= rawTotal;
            }
        });
        updateSelectionUI();
    });

    table.on("page.dt search.dt", function () {
        selectedRows = [];
        accumulatedTotal = 0;
        $("#select-all").prop("checked", false);
        updateSelectionUI();
    });

    table.on('draw.dt', function() {
        // Restaurar estado de checks si por alguna razón no se limpió selectRows 
        // o si queremos mantener la selección entre páginas (por ahora limpiamos en cada busqueda o pagina).
        $(".row-select").each(function () {
            if (selectedRows.includes($(this).val())) {
                $(this).prop("checked", true);
            }
        });
        updateSelectionUI();
    });

    // -------------------------------------------------------------
    // Lógica del Modal de Facturación
    // -------------------------------------------------------------
    
    // Open modal
    $("#btn-open-invoice-modal").on("click", function () {
        if (selectedRows.length === 0) return;
        
        $("#modal-invoice-count").text(selectedRows.length);
        
        // Remove previous hidden inputs
        $(".shipment-id-input").remove();
        
        // Create hidden inputs for each selected row
        selectedRows.forEach((id) => {
            $("<input>").attr({
                type: "hidden",
                name: "shipment_ids[]",
                class: "shipment-id-input",
                value: id
            }).appendTo("#form-generate-invoice");
        });

        // Pasar company_id del filtro al modal
        $("#modal_invoice_company_id").val($("#filter_company_id").val());

        // Preseleccionar el cliente del filtro si existe
        const filteredPartyId = $("#filter_party_id").val();
        if (filteredPartyId) {
            $("#invoice_party_id").val(filteredPartyId).trigger("change");
        } else {
            $("#invoice_party_id").val("").trigger("change");
        }
        
        $("#modal-generate-invoice").removeClass("hidden");
    });

    // Close modal
    $("#btn-close-invoice-modal, #modal-invoice-backdrop").on("click", function () {
        $("#modal-generate-invoice").addClass("hidden");
    });

});
