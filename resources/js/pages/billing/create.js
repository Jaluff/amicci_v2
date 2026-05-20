import $ from "jquery";
import "datatables.net-dt";
import select2 from "select2";
import { partyAjaxConfig } from "../../shared/select2Ajax";

window.$ = window.jQuery = $;
select2();

document.addEventListener("DOMContentLoaded", function () {
    // Select2 para el filtro de cliente
    if ($("#filter_party_id").length) {
        $("#filter_party_id").select2(Object.assign({
            placeholder: "Seleccione un cliente...",
            allowClear: true,
            width: "100%",
        }, partyAjaxConfig));
    }

    // Select2 para el select de cliente en el formulario de generación
    if ($("#party_select").length) {
        $("#party_select").select2(Object.assign({
            placeholder: "Seleccione un cliente...",
            allowClear: false,
            width: "100%",
        }, partyAjaxConfig));
    }

    const tableEl = document.getElementById("available-shipments-table");
    if (!tableEl) return;

    const dataUrl         = tableEl.dataset.url;
    const preselectedJson = tableEl.dataset.preselected;
    const preselectedIds  = preselectedJson ? JSON.parse(preselectedJson) : [];
    const isEditMode      = preselectedIds.length > 0;

    // Estado interno de IDs y totales seleccionados
    let selectedIds    = new Set(preselectedIds.map(String));
    let selectedTotals = {}; // id => total numérico

    function parseMoneyValue(val) {
        if (val === null || val === undefined || val === "") return 0;
        let str = val.toString()
            .replace(/[\$\s]/g, "")
            .replace(/\./g, "")
            .replace(",", ".");
        return parseFloat(str) || 0;
    }

    function updateSummary() {
        const count = selectedIds.size;
        const total = Object.values(selectedTotals).reduce((a, b) => a + b, 0);

        $("#selected-count").text(count);
        $("#selected-total").text(
            "$ " + total.toFixed(2)
                .replace(".", ",")
                .replace(/\B(?=(\d{3})+(?!\d))/g, ".")
        );

        // Habilitar/deshabilitar botón generar (solo en create)
        const btn = $("#btn-generate");
        if (btn.length) {
            btn.prop("disabled", count === 0);
        }

        // Sincronizar company_id
        $("#invoice_company_id").val($("#filter_company_id").val());

        // Reconstruir hidden inputs de shipment_ids
        const container = $("#shipment-ids-container");
        // En modo edit, limpiamos y re-generamos todos
        // En modo create, los pre-selected no existen así que igualmente limpiamos
        container.find("input[type='hidden']").not(".pre-selected-shipment").remove();
        if (isEditMode) {
            container.empty();
        }
        selectedIds.forEach((id) => {
            if (!container.find(`input[value="${id}"]`).length) {
                container.append(
                    `<input type="hidden" name="shipment_ids[]" value="${id}">`
                );
            }
        });
    }

    function applyRowStyle(checkbox) {
        const tds = $(checkbox).closest("tr").find("td");
        if ($(checkbox).prop("checked")) {
            tds.addClass("!bg-indigo-100 dark:!bg-indigo-900/60 !text-indigo-900 dark:!text-indigo-100 font-bold");
        } else {
            tds.removeClass("!bg-indigo-100 dark:!bg-indigo-900/60 !text-indigo-900 dark:!text-indigo-100 font-bold");
        }
    }

    // DataTable de guías disponibles
    const table = $(tableEl).DataTable({
        processing: true,
        serverSide: true,
        responsive: false,
        scrollX: true,
        paging: false,
        deferLoading: 0,
        ajax: {
            url: dataUrl,
            data: function (d) {
                d.start_date = $("#filter_start_date").val();
                d.end_date   = $("#filter_end_date").val();
                d.company_id = $("#filter_company_id").val();
                d.party_id   = $("#filter_party_id").val();
                d.numero     = $("#filter_numero").val();
            },
        },
        columns: [
            { data: "selection",              name: "selection",              orderable: false, searchable: false },
            { data: "fecha",                  name: "fecha" },
            { data: "fecha_entrega",          name: "fecha_entrega" },
            { data: "numero",                 name: "numero" },
            { data: "sender_name",            name: "sender.name",            searchable: false },
            { data: "recipient_name",         name: "recipient.name",         searchable: false },
            { data: "ubicacion_actual",       name: "ubicacion_actual" },
            { data: "invoice_badge",          name: "invoice_badge",          orderable: false, searchable: false },
            { data: "cobrada",                name: "cobrada" },
            { data: "flete",                  name: "flete" },
            { data: "seguro",                 name: "seguro" },
            { data: "monto_contra_reembolso", name: "monto_contra_reembolso" },
            { data: "retencion_mercaderia",   name: "retencion_mercaderia" },
            { data: "total",                  name: "total" },
        ],
        order: [[1, "desc"]],

        drawCallback: function () {
            // Restaurar estado de checkboxes tras cada draw
            $(".row-select").each(function () {
                const id    = $(this).val();
                const total = parseMoneyValue($(this).data("total"));

                if (selectedIds.has(id)) {
                    $(this).prop("checked", true);
                    selectedTotals[id] = total; // Actualiza el total desde el servidor
                    applyRowStyle(this);
                }
            });

            // Actualizar "Seleccionar todos"
            const enabledBoxes  = $(".row-select:not([disabled])");
            const checkedBoxes  = $(".row-select:checked");
            const allChecked    = enabledBoxes.length > 0 && enabledBoxes.length === checkedBoxes.length;
            $("#selectAll").prop("checked", allChecked);

            updateSummary();
        },

        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json",
        },
    });

    // -------------------------------------------------------------------
    // Eventos de selección
    // -------------------------------------------------------------------

    $(document).on("change", ".row-select", function () {
        const id    = $(this).val();
        const total = parseMoneyValue($(this).data("total"));

        if ($(this).prop("checked")) {
            selectedIds.add(id);
            selectedTotals[id] = total;
        } else {
            selectedIds.delete(id);
            delete selectedTotals[id];
            $("#selectAll").prop("checked", false);
        }

        applyRowStyle(this);

        // Verificar si todos están chequeados
        const enabledBoxes = $(".row-select:not([disabled])");
        const allChecked   = enabledBoxes.length > 0 &&
            enabledBoxes.length === $(".row-select:checked").length;
        $("#selectAll").prop("checked", allChecked);

        updateSummary();
    });

    $("#selectAll").on("change", function () {
        const checked = $(this).prop("checked");
        $(".row-select:not([disabled])").each(function () {
            const id    = $(this).val();
            const total = parseMoneyValue($(this).data("total"));
            $(this).prop("checked", checked);
            if (checked) {
                selectedIds.add(id);
                selectedTotals[id] = total;
            } else {
                selectedIds.delete(id);
                delete selectedTotals[id];
            }
            applyRowStyle(this);
        });
        updateSummary();
    });

    // -------------------------------------------------------------------
    // Filtros
    // -------------------------------------------------------------------

    $("#btn-filter").on("click", function () {
        table.ajax.reload();
    });

    $("input[id^='filter_']").on("keypress", function (e) {
        if (e.which === 13) table.ajax.reload();
    });

    // Sincronizar cliente del filtro → select del formulario y recargar tabla
    $("#filter_company_id").on("change", function () {
        table.ajax.reload();
    });

    // Sincronizar cliente del filtro → select del formulario y recargar tabla
    $("#filter_party_id").on("change", function () {
        const val = $(this).val();
        if (val && $("#party_select").length) {
            const partyName = $(this).find("option:selected").text();
            if ($("#party_select").find("option[value='" + val + "']").length) {
                $("#party_select").val(val).trigger("change");
            } else {
                var newOption = new Option(partyName, val, true, true);
                $("#party_select").append(newOption).trigger('change');
            }
        }
        table.ajax.reload();
    });

    // -------------------------------------------------------------------
    // Validación pre-submit
    // -------------------------------------------------------------------

    const form = document.getElementById("invoice-form") ||
                 document.getElementById("edit-invoice-form");

    if (form) {
        form.addEventListener("submit", function (e) {
            if (selectedIds.size === 0) {
                e.preventDefault();
                alert("Debe seleccionar al menos una guía.");
                return;
            }
            // Actualizar hidden inputs antes de enviar
            updateSummary();
        });
    }

    // Resumen inicial en modo edición
    if (isEditMode) {
        updateSummary();
    }

    // Carga inicial
    table.ajax.reload();
});
