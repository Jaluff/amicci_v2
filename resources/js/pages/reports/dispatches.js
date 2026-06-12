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

    const table = $(tableEl).DataTable({
        processing: true,
        serverSide: false,
        scrollX: true,
        autoWidth: false,
        stateSave: true,
        columnDefs: [
            { width: '1%', targets: '_all' }
        ],
        paging: true,
        pageLength: 30,
        lengthMenu: [[15, 30, -1], [15, 30, "Todas"]],
        order: [],
        deferLoading: 0,
        ajax: {
            url: dataUrl,
            dataSrc: 'data',
            data: function (d) {
                d.start_date = $("#filter_start_date").val();
                d.end_date = $("#filter_end_date").val();
                d.company_id = $("#filter_company_id").val();
                d.party_id = $("#filter_party_id").val(); // will be an array
                d.origin_id = $("#filter_origin_id").val();
                d.destination_id = $("#filter_destination_id").val();
                d.ubicacion_actual = $("#filter_ubicacion_actual").val();
                d.cobrada = $("#filter_cobrada").val();
                d.dispatch_number = $("#filter_dispatch_number").val();
                d.route_number = $("#filter_route_number").val();
                d.delivery_number = $("#filter_delivery_number").val();
            },
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
                }
            }
        ],
        columns: [
            { data: "selection", name: "selection", orderable: false, searchable: false },
            { data: "fecha", name: "fecha" },
            { data: "numero", name: "numero" },
            { data: "sender_name", name: "sender.name" },
            { data: "recipient_name", name: "recipient.name" },
            { data: "origin_name", name: "origin.name" },
            { data: "destination_name", name: "destination.name" },
            { data: "ruta_numero", name: "transportRoute.route_number", searchable: false },
            { data: "despacho_numero", name: "transportRoute.dispatch.dispatch_number", searchable: false },
            { data: "reparto_numero", name: "delivery.delivery_number", searchable: false },
            { data: "flete_a_pagar_en", name: "flete_a_pagar_en" },
            { data: "cobrada", name: "cobrada" },
            { data: "remitos", name: "remitos", orderable: false, searchable: false },
            { data: "ubicacion_actual", name: "ubicacion_actual", className: "text-center" },
            { data: "items_sum_cantidad", name: "items_sum_cantidad", searchable: false },
            { data: "items_sum_peso", name: "items_sum_peso", searchable: false },
            { data: "items_sum_volumen", name: "items_sum_volumen", searchable: false },
            { data: "flete", name: "flete" },
            { data: "seguro", name: "seguro" },
            { data: "monto_contra_reembolso", name: "monto_contra_reembolso" },
            { data: "retencion_mercaderia", name: "retencion_mercaderia" },
            { data: "items_sum_monto_valor_declarado", name: "items_sum_monto_valor_declarado", searchable: false },
            { data: "total", name: "total" }
        ],
        drawCallback: function () {
            // Restore "Select All" to unchecked upon physical reload
            $('#selectAll').prop('checked', false);
            updateFooters(this.api());
            populateLocalFilters(this.api());
        },
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json",
        },
    });

    function updateFooters(api) {
        let intVal = function (i) {
            if(i === null || i === undefined || i === '') return 0;
            if (typeof i === 'number') return i;
            // Clean specific chars ($ and white spaces)
            let val = i.toString().replace(/[\$\s]/g, '');
            // Convert dot (thousands format in arg) into nothing
            val = val.replace(/\./g, '');
            // Convert comma (decimals in arg) into real dot
            val = val.replace(/,/g, '.');
            return parseFloat(val) || 0;
        };

        // Indices for the numeric columns (14 to 22)
        const columnsToSum = [14, 15, 16, 17, 18, 19, 20, 21, 22];
        
        columnsToSum.forEach(function(index) {
            let total = 0;
            api.rows({ search: 'applied' }).every(function (rowIdx, tableLoop, rowLoop) {
                let node = this.node();
                if ($(node).find('.row-select').is(':checked')) {
                    total += intVal(this.data()[api.column(index).dataSrc()]);
                }
            });
            
            // Format back to Argentina string (1.234,50)
            let isMoney = [17, 18, 19, 20, 21, 22].includes(index);
            let formattedTotal = total.toFixed(2).replace('.', ',');
            if (isMoney) {
                formattedTotal = '$ ' + formattedTotal.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            } else {
                formattedTotal = formattedTotal.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            }
                
            $(api.column(index).footer()).html(formattedTotal);
        });
    }

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
        $('.row-select').prop('checked', $(this).prop('checked'));
        updateRowStyles();
        updateFooters(table);
    });

    $(document).on('change', '.row-select', function() {
        if ($(this).prop('checked')) {
            let tr = $(this).closest('tr');
            tr.prependTo(tr.parent());
        }

        if (!$(this).prop('checked')) {
            $('#selectAll').prop('checked', false);
        } else {
            // Check if all are checked
            if ($('.row-select:checked').length === $('.row-select').length) {
                $('#selectAll').prop('checked', true);
            }
        }
        updateRowStyles();
        updateFooters(table);
    });

    // Also on initial data load (ajax.reload success natively handled by drawCallback)
    table.on('draw.dt', function() {
        // Automatically Select All Rows on load so footer displays Grand Total initially
        if ($('.row-select').length > 0) {
            $('#selectAll').prop('checked', true);
            $('.row-select').prop('checked', true);
            updateRowStyles();
            updateFooters(table);
        }
    });

    // Trigger refresh when filter button is clicked
    $("#btn-filter").on("click", function () {
        table.ajax.reload();
    });

    // Also trigger refresh when pressing enter on text inputs
    $("input[id^='filter_']").on("keypress", function(e) {
        if(e.which === 13) {
            table.ajax.reload();
        }
    });

    // Local filters for already shown records
    $('#local_filter_remitente').on('change', function () {
        if (isPopulating) return;
        const selected = $(this).val() || [];
        if (selected.length === 0) {
            table.column(3).search('').draw();
        } else {
            const escaped = selected.map(val => $.fn.dataTable.util.escapeRegex(val));
            table.column(3).search('^(' + escaped.join('|') + ')$', true, false).draw();
        }
    });

    $('#local_filter_destinatario').on('change', function () {
        if (isPopulating) return;
        const selected = $(this).val() || [];
        if (selected.length === 0) {
            table.column(4).search('').draw();
        } else {
            const escaped = selected.map(val => $.fn.dataTable.util.escapeRegex(val));
            table.column(4).search('^(' + escaped.join('|') + ')$', true, false).draw();
        }
    });

    $('#local_filter_cobrada').on('change', function () {
        if (isPopulating) return;
        const selected = $(this).val() || [];
        if (selected.length === 0) {
            table.column(11).search('').draw();
        } else {
            const escaped = selected.map(val => $.fn.dataTable.util.escapeRegex(val));
            table.column(11).search('^(' + escaped.join('|') + ')$', true, false).draw();
        }
    });
});
