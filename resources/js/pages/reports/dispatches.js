import $ from "jquery";
import "datatables.net-dt";
import "datatables.net-buttons-dt";
import "datatables.net-buttons/js/buttons.html5.mjs";
import "datatables.net-buttons/js/buttons.colVis.mjs";
import select2 from "select2";
import { partyAjaxConfig } from "../../shared/select2Ajax";

window.$ = window.jQuery = $;
select2(); // Initialize select2 on local jQuery instance

document.addEventListener("DOMContentLoaded", function () {
    // Reset all filter inputs on page load so they do not stay sticky
    $("#filter_company_id").val("");
    $("#filter_party_id").val([]).trigger("change");
    $("#filter_origin_id").val([]).trigger("change");
    $("#filter_destination_id").val([]).trigger("change");
    $("#filter_ubicacion_actual").val([]).trigger("change");
    $("#filter_dispatch_number").val("");
    $("#filter_route_number").val("");
    $("#filter_delivery_number").val("");
    $("#filter_start_date").val("");
    $("#filter_end_date").val("");

    $("#local_filter_remitente").val([]).trigger("change");
    $("#local_filter_destinatario").val([]).trigger("change");
    $("#local_filter_cobrada").val([]).trigger("change");

    // Initialize select2 for multiple selections (AJAX)
    if ($("#filter_party_id").length) {
        $("#filter_party_id").select2(Object.assign({
            placeholder: "Buscar cliente(s)...",
            allowClear: true,
            width: '100%'
        }, partyAjaxConfig));
    }

    // Initialize select2 for multiple selections (Normal)
    const select2Selectors = [
        '#filter_origin_id', '#filter_destination_id', '#filter_ubicacion_actual',
        '#local_filter_remitente', '#local_filter_destinatario', '#local_filter_cobrada'
    ];
    select2Selectors.forEach(selector => {
        if ($(selector).length) {
            $(selector).select2({
                placeholder: "Seleccione...",
                allowClear: true,
                width: '100%'
            });
        }
    });

    const tableEl = document.getElementById("reports-table");
    if (!tableEl) return;

    const dataUrl = tableEl.dataset.url;

    let isPopulating = false;
    let hasFiltered = false;

    const table = $(tableEl).DataTable({
        processing: true,
        serverSide: false,
        scrollX: true,
        scrollY: "380px",
        scrollCollapse: true,
        responsive: false,
        autoWidth: true,
        stateSave: true,
        stateSaveParams: function (settings, data) {
            // Do not save search filters and pagination index in the saved state
            data.search.search = "";
            data.start = 0; // always start on the first page
            if (data.columns) {
                for (let i = 0; i < data.columns.length; i++) {
                    data.columns[i].search.search = "";
                }
            }
        },
        paging: true,
        pageLength: 30,
        lengthMenu: [[15, 30, -1], [15, 30, "Todas"]],
        order: [],
        orderFixed: {
            pre: [[0, 'desc']]
        },
        deferLoading: 0,
        ajax: function (data, callback, settings) {
            if (!hasFiltered) {
                callback({ data: [] });
                return;
            }
            $.ajax({
                url: dataUrl,
                data: {
                    start_date: $("#filter_start_date").val(),
                    end_date: $("#filter_end_date").val(),
                    company_id: $("#filter_company_id").val(),
                    party_id: $("#filter_party_id").val(),
                    origin_id: $("#filter_origin_id").val(),
                    destination_id: $("#filter_destination_id").val(),
                    ubicacion_actual: $("#filter_ubicacion_actual").val(),
                    dispatch_number: $("#filter_dispatch_number").val(),
                    route_number: $("#filter_route_number").val(),
                    delivery_number: $("#filter_delivery_number").val()
                },
                success: function (json) {
                    if (json.data) {
                        json.data.forEach(row => {
                            row.checked = true;
                        });
                    }
                    callback(json);
                }
            });
        },
        dom: "<'dt-controls'lBf>rtip",
        buttons: [
            {
                extend: 'colvis',
                text: 'Columnas Visibles',
                className: 'bg-gray-100 text-gray-800 border'
            },
            {
                extend: 'excelHtml5',
                text: 'Exportar a Excel',
                exportOptions: { 
                    columns: ':visible',
                    rows: function (idx, data, node) {
                        return $('.row-select:checked').length === 0 || $(node).find('.row-select').prop('checked');
                    }
                }
            },
            {
                extend: 'pdfHtml5',
                text: 'Exportar a PDF',
                orientation: 'landscape',
                pageSize: 'LEGAL',
                exportOptions: { 
                    columns: ':visible',
                    rows: function (idx, data, node) {
                        return $('.row-select:checked').length === 0 || $(node).find('.row-select').prop('checked');
                    }
                },
                customize: function (doc) {
                    const pageFlete = $("#page-total-flete").text();
                    const pageSeguro = $("#page-total-seguro").text();
                    const pageContra = $("#page-total-contra-reembolso").text();
                    const pageRet = $("#page-total-retencion-mercaderia").text();
                    const pageValDec = $("#page-total-valor-declarado").text();
                    const pageTotal = $("#page-total-sum").text();

                    const genFlete = $("#general-total-flete").text();
                    const genSeguro = $("#general-total-seguro").text();
                    const genContra = $("#general-total-contra-reembolso").text();
                    const genRet = $("#general-total-retencion-mercaderia").text();
                    const genValDec = $("#general-total-valor-declarado").text();
                    const genTotal = $("#general-total-sum").text();

                    const totalsTable = {
                        table: {
                            widths: ['*', 250, 50, 250, '*'],
                            body: [
                                [
                                    {},
                                    {
                                        table: {
                                            widths: ['*', 'auto'],
                                            body: [
                                                [{text: 'TOTALES DE LA PÁGINA ACTUAL', colSpan: 2, bold: true, fillColor: '#f3f4f6', alignment: 'center'}, {}],
                                                ['Flete', pageFlete],
                                                ['Seguro', pageSeguro],
                                                ['Contra Reembolso', pageContra],
                                                ['Retiro Mercadería', pageRet],
                                                ['Valor Declarado', pageValDec],
                                                [{text: 'Total', bold: true, color: '#4f46e5'}, {text: pageTotal, bold: true, color: '#4f46e5'}]
                                            ]
                                        },
                                        layout: 'lightHorizontalLines'
                                    },
                                    {},
                                    {
                                        table: {
                                            widths: ['*', 'auto'],
                                            body: [
                                                [{text: 'TOTALES DEL FILTRO GENERAL', colSpan: 2, bold: true, fillColor: '#f3f4f6', alignment: 'center'}, {}],
                                                ['Flete', genFlete],
                                                ['Seguro', genSeguro],
                                                ['Contra Reembolso', genContra],
                                                ['Retiro Mercadería', genRet],
                                                ['Valor Declarado', genValDec],
                                                [{text: 'Total', bold: true, color: '#16a34a'}, {text: genTotal, bold: true, color: '#16a34a'}]
                                            ]
                                        },
                                        layout: 'lightHorizontalLines'
                                    },
                                    {}
                                ]
                            ]
                        },
                        layout: 'noBorders',
                        margin: [0, 20, 0, 0]
                    };

                    doc.content.push(totalsTable);
                }
            }
        ],
        columns: [
            { 
                data: "checked", 
                name: "selection", 
                className: "text-center",
                orderable: true, 
                searchable: false,
                render: function(data, type, row) {
                    const checkedAttr = data ? 'checked' : '';
                    return `<input type="checkbox" class="row-select w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500 cursor-pointer" value="${row.id}" ${checkedAttr}>`;
                }
            },
            { data: "fecha", name: "fecha" },
            { data: "fecha_entrega", name: "fecha_entrega" },
            { data: "numero", name: "numero" },
            { 
                data: "sender_name", 
                name: "sender.name",
                render: function(data, type, row) {
                    if (!data) return '-';
                    return `<div class="!whitespace-normal line-clamp-2 max-w-[150px] break-words" title="${data}">${data}</div>`;
                }
            },
            { 
                data: "recipient_name", 
                name: "recipient.name",
                render: function(data, type, row) {
                    if (!data) return '-';
                    return `<div class="!whitespace-normal line-clamp-2 max-w-[150px] break-words" title="${data}">${data}</div>`;
                }
            },
            { data: "origin_name", name: "origin.name" },
            { data: "destination_name", name: "destination.name" },
            { data: "ruta_numero", name: "transportRoute.route_number", searchable: false },
            { data: "despacho_numero", name: "transportRoute.dispatch.dispatch_number", searchable: false },
            { data: "reparto_numero", name: "delivery.delivery_number", searchable: false },
            { data: "flete_a_pagar_en", name: "flete_a_pagar_en", className: "text-center" },
            { data: "cobrada", name: "cobrada", className: "text-center" },
            { 
                data: "remitos", 
                name: "remitos", 
                orderable: false, 
                searchable: false,
                render: function(data, type, row) {
                    if (!data) return '-';
                    let parts = data.split(',').map(p => p.trim()).filter(Boolean);
                    if (parts.length === 0) return '-';
                    let chunks = [];
                    for (let i = 0; i < parts.length; i += 3) {
                        chunks.push(parts.slice(i, i + 3).join(', '));
                    }
                    return chunks.join('<br>');
                }
            },
            { data: "ubicacion_actual", name: "ubicacion_actual", className: "text-center" },
            { data: "items_sum_cantidad", name: "items_sum_cantidad", searchable: false, className: "text-center" },
            { data: "items_sum_peso", name: "items_sum_peso", searchable: false, className: "text-right" },
            { data: "items_sum_volumen", name: "items_sum_volumen", searchable: false, className: "text-right" },
            { data: "flete", name: "flete", className: "text-right" },
            { data: "seguro", name: "seguro", className: "text-right" },
            { data: "monto_contra_reembolso", name: "monto_contra_reembolso", className: "text-right" },
            { data: "retencion_mercaderia", name: "retencion_mercaderia", className: "text-right" },
            { data: "items_sum_monto_valor_declarado", name: "items_sum_monto_valor_declarado", searchable: false, className: "text-right" },
            { data: "total", name: "total", className: "text-right" }
        ],
        drawCallback: function () {
            // Restore "Select All" based on actual row states
            const allChecked = $('.row-select:checked').length === $('.row-select').length && $('.row-select').length > 0;
            $('#selectAll').prop('checked', allChecked);
            updateRowStyles();
            populateLocalFilters(this.api());
            updateSummaryTables(this.api());
        },
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json",
        },
        initComplete: function (settings, json) {
            // Hide page loader with transition when initialization and first AJAX load is done
            const loader = document.getElementById("page-loader");
            if (loader) {
                loader.style.opacity = '0';
                setTimeout(() => {
                    loader.classList.add("hidden");
                }, 300);
            }
            // Automatically Select All Rows on initial load so footer displays Grand Total initially
            if ($('.row-select').length > 0) {
                $('#selectAll').prop('checked', true);
                updateRowStyles();
                updateSummaryTables(this.api());
            }
        }
    });


    function populateLocalFilters(api) {
        isPopulating = true;

        const currentRemitente = $('#local_filter_remitente').val() || [];
        const currentDestinatario = $('#local_filter_destinatario').val() || [];
        const currentCobrada = $('#local_filter_cobrada').val() || [];

        const remitentes = new Set();
        const destinatarios = new Set();
        const cobradas = new Set();

        api.rows().every(function () {
            const data = this.data();
            const sender = data.sender_name || '';
            const recipient = data.recipient_name || '';
            let cobradaStr = 'No';
            if (data.cobrada === 'Sí' || data.cobrada === true || data.cobrada === 1 || data.cobrada === '1') {
                cobradaStr = 'Sí';
            }

            const matchesRemitente = currentRemitente.length === 0 || currentRemitente.includes(sender);
            const matchesDestinatario = currentDestinatario.length === 0 || currentDestinatario.includes(recipient);
            const matchesCobrada = currentCobrada.length === 0 || currentCobrada.includes(cobradaStr);

            if (matchesDestinatario && matchesCobrada) {
                if (sender) remitentes.add(sender);
            }
            if (matchesRemitente && matchesCobrada) {
                if (recipient) destinatarios.add(recipient);
            }
            if (matchesRemitente && matchesDestinatario) {
                cobradas.add(cobradaStr);
            }
        });

        const remitenteSelect = $('#local_filter_remitente');
        remitenteSelect.empty();
        Array.from(remitentes).sort().forEach(name => {
            const selected = currentRemitente.includes(name) ? 'selected' : '';
            remitenteSelect.append(`<option value="${name}" ${selected}>${name}</option>`);
        });
        remitenteSelect.trigger('change.select2');

        const destinatarioSelect = $('#local_filter_destinatario');
        destinatarioSelect.empty();
        Array.from(destinatarios).sort().forEach(name => {
            const selected = currentDestinatario.includes(name) ? 'selected' : '';
            destinatarioSelect.append(`<option value="${name}" ${selected}>${name}</option>`);
        });
        destinatarioSelect.trigger('change.select2');

        const cobradaSelect = $('#local_filter_cobrada');
        cobradaSelect.empty();
        Array.from(cobradas).sort().forEach(val => {
            const selected = currentCobrada.includes(val) ? 'selected' : '';
            cobradaSelect.append(`<option value="${val}" ${selected}>${val}</option>`);
        });
        cobradaSelect.trigger('change.select2');

        isPopulating = false;
    }

    function updateRowStyles() {
        $('.row-select').each(function() {
            let tr = $(this).closest('tr');
            let tds = tr.find('td');
            if ($(this).prop('checked')) {
                tds.addClass('!bg-indigo-100 dark:!bg-indigo-900/60 !text-indigo-900 dark:!text-indigo-100 font-bold');
            } else {
                tds.removeClass('!bg-indigo-100 dark:!bg-indigo-900/60 !text-indigo-900 dark:!text-indigo-100 font-bold');
            }
        });
    }

    // Interactions for Checkboxes
    $('#selectAll').on('change', function() {
        const isChecked = $(this).prop('checked');
        table.rows().every(function () {
            const rowData = this.data();
            rowData.checked = isChecked;
            this.invalidate();
        });
        table.draw(false);
    });

    $(document).on('change', '.row-select', function() {
        const tr = $(this).closest('tr');
        const row = table.row(tr);
        const rowData = row.data();
        rowData.checked = $(this).prop('checked');
        row.invalidate().draw(false);
    });

    // Also on initial data load (ajax.reload success natively handled by drawCallback)
    table.on('draw.dt', function() {
        // Adjust columns to align headers with the body when drawn
        table.columns.adjust();
    });

    // Toggle advanced filters panel
    $("#btn-toggle-advanced-filters").on("click", function() {
        const container = $("#advanced-filters-container");
        const icon = $("#advanced-filters-icon");
        const text = $("#advanced-filters-text");

        if (container.hasClass("hidden")) {
            container.removeClass("hidden");
            icon.addClass("rotate-180");
            text.text("Menos Filtros");
        } else {
            container.addClass("hidden");
            icon.removeClass("rotate-180");
            text.text("Más Filtros");
        }
    });

    // Trigger refresh when filter button is clicked
    $("#btn-filter").on("click", function () {
        hasFiltered = true;
        table.ajax.reload();
    });

    // Also trigger refresh when pressing enter on text inputs
    $("input[id^='filter_']").on("keypress", function(e) {
        if(e.which === 13) {
            hasFiltered = true;
            table.ajax.reload();
        }
    });

    // Local filters for already shown records
    $('#local_filter_remitente').on('change', function () {
        if (isPopulating) return;
        const selected = $(this).val() || [];
        if (selected.length === 0) {
            table.column(4).search('').draw();
        } else {
            const escaped = selected.map(val => $.fn.dataTable.util.escapeRegex(val));
            table.column(4).search('^(' + escaped.join('|') + ')$', true, false).draw();
        }
    });

    $('#local_filter_destinatario').on('change', function () {
        if (isPopulating) return;
        const selected = $(this).val() || [];
        if (selected.length === 0) {
            table.column(5).search('').draw();
        } else {
            const escaped = selected.map(val => $.fn.dataTable.util.escapeRegex(val));
            table.column(5).search('^(' + escaped.join('|') + ')$', true, false).draw();
        }
    });

    $('#local_filter_cobrada').on('change', function () {
        if (isPopulating) return;
        const selected = $(this).val() || [];
        if (selected.length === 0) {
            table.column(12).search('').draw();
        } else {
            const escaped = selected.map(val => $.fn.dataTable.util.escapeRegex(val));
            table.column(12).search('^(' + escaped.join('|') + ')$', true, false).draw();
        }
    });

    function updateSummaryTables(api) {
        let intVal = function (i) {
            if(i === null || i === undefined || i === '') return 0;
            if (typeof i === 'number') return i;
            let val = i.toString().replace(/[\$\s]/g, '');
            val = val.replace(/\./g, '');
            val = val.replace(/,/g, '.');
            return parseFloat(val) || 0;
        };

        const amountFields = {
            flete: 0,
            seguro: 0,
            contraReembolso: 0,
            retencionMercaderia: 0,
            valorDeclarado: 0,
            total: 0
        };

        let pageTotals = { ...amountFields };
        let generalTotals = { ...amountFields };

        // 1. Calculate Page Totals (only selected/checked rows on current page)
        api.rows({ page: 'current' }).every(function () {
            let node = this.node();
            if ($(node).find('.row-select').is(':checked')) {
                let rowData = this.data();
                pageTotals.flete += intVal(rowData.flete);
                pageTotals.seguro += intVal(rowData.seguro);
                pageTotals.contraReembolso += intVal(rowData.monto_contra_reembolso);
                pageTotals.retencionMercaderia += intVal(rowData.retencion_mercaderia);
                pageTotals.valorDeclarado += intVal(rowData.items_sum_monto_valor_declarado);
                pageTotals.total += intVal(rowData.total);
            }
        });

        // 2. Calculate General Totals (all filtered rows)
        api.rows({ search: 'applied' }).every(function () {
            let rowData = this.data();
            generalTotals.flete += intVal(rowData.flete);
            generalTotals.seguro += intVal(rowData.seguro);
            generalTotals.contraReembolso += intVal(rowData.monto_contra_reembolso);
            generalTotals.retencionMercaderia += intVal(rowData.retencion_mercaderia);
            generalTotals.valorDeclarado += intVal(rowData.items_sum_monto_valor_declarado);
            generalTotals.total += intVal(rowData.total);
        });

        // Helper to format as currency
        let formatCurrency = function(val) {
            let formatted = val.toFixed(2).replace('.', ',');
            return '$ ' + formatted.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        };

        // Update Page Totals in UI
        $("#page-total-flete").text(formatCurrency(pageTotals.flete));
        $("#page-total-seguro").text(formatCurrency(pageTotals.seguro));
        $("#page-total-contra-reembolso").text(formatCurrency(pageTotals.contraReembolso));
        $("#page-total-retencion-mercaderia").text(formatCurrency(pageTotals.retencionMercaderia));
        $("#page-total-valor-declarado").text(formatCurrency(pageTotals.valorDeclarado));
        $("#page-total-sum").text(formatCurrency(pageTotals.total));

        // Update General Totals in UI
        $("#general-total-flete").text(formatCurrency(generalTotals.flete));
        $("#general-total-seguro").text(formatCurrency(generalTotals.seguro));
        $("#general-total-contra-reembolso").text(formatCurrency(generalTotals.contraReembolso));
        $("#general-total-retencion-mercaderia").text(formatCurrency(generalTotals.retencionMercaderia));
        $("#general-total-valor-declarado").text(formatCurrency(generalTotals.valorDeclarado));
        $("#general-total-sum").text(formatCurrency(generalTotals.total));
    }
});
