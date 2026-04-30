/**
 * Formulario de guías (shipments).
 * - Select2 con búsqueda, validación origen≠destino, remitente≠destinatario.
 * - Carga automática de configuración tarifaria del remitente vía AJAX.
 * - Cálculo automático del flete según modo de facturación y los ítems de carga.
 */
(function () {
    'use strict';

    if (!document.getElementById('shipment-form')) return;

    var $ = window.jQuery || window.$;
    if (!$ || !$.fn.select2) return;

    // ─── Configuración tarifaria cargada del remitente ──────────────────────
    var tariffSetting = null;

    function parseNum(v) {
        if (!v) return 0;
        let s = String(v).replace(/[^0-9,.]/g, '');
        if (s.includes(',') && s.includes('.')) {
            if (s.lastIndexOf(',') > s.lastIndexOf('.')) {
                s = s.replace(/\./g, '').replace(/,/g, '.');
            } else {
                s = s.replace(/,/g, '');
            }
        } else if (s.includes(',')) {
            s = s.replace(/,/g, '.');
        }
        const n = parseFloat(s);
        return isNaN(n) ? 0 : n;
    }

    // ─── Select2 opts ────────────────────────────────────────────────────────
    var opts = {
        placeholder: 'Buscar...',
        width: '100%',
        minimumResultsForSearch: 0,
        language: {
            noResults:  function () { return 'Sin resultados'; },
            searching:  function () { return 'Buscando...';    },
        },
    };

    // ─── Sincronización origen/destino ───────────────────────────────────────
    /* Logic removed to allow same origin and destination */

    // ─── Sincronización remitente/destinatario ───────────────────────────────
    /* Logic removed to allow same sender and recipient */

    // ─── Carga de tarifa del pagador (AJAX) ────────────────────────────────
    /**
     * Cuando cambia el pagador, consulta su configuración tarifaria.
     * Si tiene tarifa activa, la muestra en el banner y recalcula el flete.
     */
    function loadTariff(partyId, preventRecalculate = false) {
        if (!partyId) {
            tariffSetting = null;
            hideTariffBanner();
            return;
        }

        $.ajax({
            url: '/parties/' + partyId + '/tariff-setting',
            method: 'GET',
            success: function (data) {
                if (data.has_tariff) {
                    tariffSetting = data;
                    showTariffBanner(data);
                } else {
                    tariffSetting = data; // Guardamos igual porque trae IVA y Seguro
                    hideTariffBanner();
                }
                // Recalcular flete con la nueva tarifa (solo si no se previene)
                if (!preventRecalculate) {
                    recalcularFlete();
                    recalcularCargosCliente();
                }
            },
            error: function () {
                tariffSetting = null;
                hideTariffBanner();
            }
        });
    }

    function showTariffBanner(data) {
        var $banner = $('#tariff-banner');
        if (!$banner.length) return;
        $banner.find('#tariff-mode-label').text(data.billing_mode_label || data.billing_mode);
        $banner.show();
    }

    function hideTariffBanner() {
        $('#tariff-banner').hide();
    }

    /**
     * Calcula los recargos automáticos basados en el cliente o la ruta
     * (Seguro, Contra-reembolso, IVA)
     */
    function recalcularCargosCliente(preventOverwrite = false) {
        var totalValor = 0;
        $('#items-container .item-row').each(function () {
            // Seleccionar por nombre o por clase si tuviera, pero name* es robusto
            var valStr = $(this).find('input[name*="[monto_valor_declarado]"]').val();
            totalValor += parseNum(valStr);
        });

        // Seguro (Depende de Tarifa Cliente)
        if (tariffSetting && tariffSetting.has_insurance) {
            var insPct = parseFloat(tariffSetting.insurance_percent) || 0;
            var seguro = (totalValor * insPct) / 1000;
            $('#seguro').val(seguro.toFixed(2)).trigger('change');
        } else if (tariffSetting && !preventOverwrite) {
            $('#seguro').val('0.00').trigger('change');
        }

        // Contra reembolso (Depende de Configuración Empresa - Independiente de Tarifa)
        var isContra = $('input[name="contra_reembolso"]:checked').val() == '1';
        if (isContra) {
            var pctCR = window.GlobalContraPct || 0;
            var montoCR = (totalValor * pctCR) / 1000;
            $('#monto_contra_reembolso').val(montoCR.toFixed(2)).trigger('change');
        } else {
            $('#monto_contra_reembolso').val('0.00').trigger('change');
        }

        // IVA percent (Depende de Tarifa Cliente o Default)
        if (tariffSetting && !preventOverwrite) {
            var ivaPct = parseFloat(tariffSetting.iva_percent) || 0;
            $('#iva_percent').val(ivaPct).trigger('change');
        }

        // Siempre al final actualizar subtotales
        if (typeof window.calculateTotals === 'function') {
            window.calculateTotals();
        }
    }

    /**
     * Calcula el flete según el modo tarifario.
     * Para modo 'kg' hace AJAX al servidor (necesita la escala de tramos).
     * Para el resto calcula en el cliente directamente.
     */
    function recalcularFlete(preventOverwrite = false) {
        if (!tariffSetting || !tariffSetting.has_tariff || preventOverwrite) return;

        var mode = tariffSetting.billing_mode;

        // Sumar totales de ítems
        var totalKg      = 0;
        var totalM3      = 0;
        var totalBultos  = 0;
        var totalPallets = 0;
        var totalValor   = 0;

        $('#items-container .item-row').each(function () {
            var tipo = $(this).find('[name*="[tipo_paquete]"]').val() || '';
            var cant = parseFloat($(this).find('[name*="[cantidad]"]').val()) || 0;
            var peso = parseFloat($(this).find('[name*="[peso]"]').val())     || 0;
            var vol  = parseFloat($(this).find('[name*="[volumen]"]').val())  || 0;
            var val  = parseFloat($(this).find('[name*="[monto_valor_declarado]"]').val()) || 0;

            totalKg    += peso;
            totalM3    += vol;
            totalValor += val;

            if (tipo === 'bultos')  totalBultos  += cant;
            if (tipo === 'palets')  totalPallets += cant;
        });

        // Modo kg: delegar al servidor (consulta la escala de tramos)
        if (mode === 'kg') {
            var origenId  = $('#origen_id').val();
            var destinoId = $('#destino_id').val();
            var payerType = $('input[name="flete_a_pagar_en"]:checked').val() || 'origen';
            var partyId   = payerType === 'origen' ? $('#remitente_id').val() : $('#destinatario_id').val();

            if (!origenId || !destinoId) {
                $('#tariff-mode-label').text(tariffSetting.billing_mode_label + ' — Esperando origen y destino...');
                return;
            }

            if (totalKg <= 0) {
                $('#flete').val('0.00').trigger('change');
                $('#tariff-mode-label').text(tariffSetting.billing_mode_label + ' — Esperando cargar peso en los ítems...');
                return;
            }

            $.ajax({
                url: '/shipments/calcular-flete',
                method: 'GET',
                data: { 
                    origen_id: origenId, 
                    destino_id: destinoId, 
                    peso_kg: totalKg,
                    party_id: partyId,
                    payer_type: payerType
                },
                success: function (res) {
                    if (res.flete > 0) {
                        $('#flete').val(res.flete.toFixed(2)).trigger('change');
                    } else {
                        $('#flete').val('0.00').trigger('change');
                    }
                    if (res.detalle) {
                        $('#tariff-mode-label').text(tariffSetting.billing_mode_label + ' — ' + res.detalle);
                    }
                }
            });
            return; // La actualización la hace el callback AJAX
        }

        // Resto de modos: cálculo en el cliente
        var flete = 0;

        switch (mode) {
            case 'tonelada':
                var ton = totalKg / 1000;
                flete = ton * tariffSetting.rate_per_ton;
                if (tariffSetting.minimum_charge > 0 && flete < tariffSetting.minimum_charge) {
                    flete = tariffSetting.minimum_charge;
                }
                break;

            case 'volumen':
                flete = totalM3 * tariffSetting.rate_per_m3;
                if (tariffSetting.minimum_charge > 0 && flete < tariffSetting.minimum_charge) {
                    flete = tariffSetting.minimum_charge;
                }
                break;

            case 'bultos':
                flete = totalBultos * tariffSetting.rate_per_bulto;
                if (tariffSetting.minimum_per_bulto > 0 && flete < tariffSetting.minimum_per_bulto) {
                    flete = tariffSetting.minimum_per_bulto;
                }
                break;

            case 'pallets':
                flete = totalPallets * tariffSetting.rate_per_pallet;
                if (tariffSetting.minimum_per_pallet > 0 && flete < tariffSetting.minimum_per_pallet) {
                    flete = tariffSetting.minimum_per_pallet;
                }
                break;

            case 'bultos_pallets':
                var fleteBultos  = totalBultos  * tariffSetting.rate_per_bulto;
                var fletePallets = totalPallets * tariffSetting.rate_per_pallet;
                if (tariffSetting.minimum_per_bulto  > 0 && fleteBultos  < tariffSetting.minimum_per_bulto)  fleteBultos  = tariffSetting.minimum_per_bulto;
                if (tariffSetting.minimum_per_pallet > 0 && fletePallets < tariffSetting.minimum_per_pallet) fletePallets = tariffSetting.minimum_per_pallet;
                flete = fleteBultos + fletePallets;
                break;

            case 'valor_declarado':
                flete = totalValor * (tariffSetting.declared_value_pct / 100);
                if (tariffSetting.minimum_charge > 0 && flete < tariffSetting.minimum_charge) {
                    flete = tariffSetting.minimum_charge;
                }
                break;
        }

        $('#flete').val(flete.toFixed(2)).trigger('change');
    }


    // ─── Gestión de ítems ─────────────────────────────────────────────────────
    function getNextItemIndex() {
        var max = -1;
        $('#items-container .item-row').each(function () {
            var m = $(this).find('[name^="items["]').first().attr('name');
            if (m) {
                var num = parseInt(m.replace('items[', '').replace('][', ''), 10);
                if (!isNaN(num) && num > max) max = num;
            }
        });
        return max + 1;
    }

    function toggleRemoveButtons() {
        var $rows = $('#items-container .item-row');
        if ($rows.length <= 1) {
            $rows.find('.remove-item').addClass('hidden');
        } else {
            $rows.find('.remove-item').removeClass('hidden');
        }
    }

    function addItemRow() {
        var tpl = document.getElementById('item-row-template');
        if (!tpl) return;
        var index = getNextItemIndex();
        var html = tpl.innerHTML.replace(/__INDEX__/g, index);
        $('#items-container').append(html);
        toggleRemoveButtons();
    }

    // ─── Init ─────────────────────────────────────────────────────────────────
    function initSelect2() {
        $('#origen_id').select2(opts);
        $('#destino_id').select2(opts);

        // Al cambiar origen o destino → recalcular flete
        $('#origen_id, #destino_id').on('change', function () {
            recalcularFlete();
        });

        // Al cambiar checkbox de contra-reembolso
        $('input[name="contra_reembolso"]').on('change', function () {
            recalcularCargosCliente();
        });

        // Al cambiar remitente → recargar tarifa solo si él paga
        $('#remitente_id').select2(opts).on('change', function () {
            var senderVal = $(this).val();
            if ($('input[name="flete_a_pagar_en"]:checked').val() === 'origen') {
                loadTariff(senderVal);
            }
        });

        // Al cambiar destinatario → recargar tarifa solo si él paga
        $('#destinatario_id').select2(opts).on('change', function () {
            var recVal = $(this).val();
            if ($('input[name="flete_a_pagar_en"]:checked').val() === 'destino') {
                loadTariff(recVal);
            }
        });

        // Al cambiar de pagador → recargar tarifa con el party que corresponda
        $('input[name="flete_a_pagar_en"]').on('change', function() {
            var payerType = $(this).val();
            var payerId = payerType === 'origen' ? $('#remitente_id').val() : $('#destinatario_id').val();
            loadTariff(payerId);
        });

        /* syncDestination(); */
        /* syncOrigin(); */
    }

    $(document).ready(function () {
        initSelect2();

        // ── Cargar tarifa si ya hay pagador seleccionado (modo edición)
        var isEdit = $('#shipment-form').data('is-edit') === true;
        var initialPayerOption = $('input[name="flete_a_pagar_en"]:checked').val() || 'origen';
        var initialPayerId = initialPayerOption === 'origen' ? $('#remitente_id').val() : $('#destinatario_id').val();
        
        if (initialPayerId) {
            // Pasamos true para NO sobrescribir los valores que ya vienen de la DB al cargar
            loadTariff(initialPayerId, isEdit);
        }

        // ── Calcular totales de importes ──────────────────────────────────
        window.calculateTotals = function() { // Exponer globalmente para recalcularCargosCliente
            var flete                = parseNum($('#flete').val());
            var seguro               = parseNum($('#seguro').val());
            var monto_contra_reembolso = parseNum($('#monto_contra_reembolso').val());
            var retencion_mercaderia = parseNum($('#retencion_mercaderia').val());
            var otros_cargos         = parseNum($('#otros_cargos').val());
            var iva                  = parseNum($('#iva_percent').val());

            var subtotal = flete + seguro + monto_contra_reembolso + retencion_mercaderia + otros_cargos;
            var tax      = subtotal * (iva / 100);
            var total    = subtotal + tax;

            $('#subtotal').val(subtotal.toFixed(2));
            $('#iva_monto').val(tax.toFixed(2));
            $('#total').val(total.toFixed(2));
        };


        $('#flete, #seguro, #monto_contra_reembolso, #retencion_mercaderia, #otros_cargos, #iva_percent')
            .on('input change', window.calculateTotals);

        recalcularCargosCliente(isEdit);
        calculateTotals();
        toggleRemoveButtons();

        // ── Recalcular flete al cambiar ítems ────────────────────────────
        // (cantidad, tipo_paquete, peso, volumen, valor declarado)
        $(document).on('input change', '#items-container [name*="[cantidad]"], #items-container [name*="[tipo_paquete]"], #items-container [name*="[peso]"], #items-container [name*="[volumen]"], #items-container [name*="[monto_valor_declarado]"]', function () {
            recalcularFlete();
            recalcularCargosCliente(); // recalcula porque cambió valor declarado
        });

        // ── Agregar / quitar ítems ────────────────────────────────────────
        $(document).on('click', '#add-item', function () {
            addItemRow();
        });

        $(document).on('click', '.remove-item', function () {
            var $rows = $('#items-container .item-row');
            if ($rows.length <= 1) return;
            $(this).closest('.item-row').remove();
            toggleRemoveButtons();
            // Re-indexar nombres
            // Re-indexar nombres...
            $('#items-container .item-row').each(function (i) {
                $(this).find('[name^="items["]').each(function () {
                    var name = $(this).attr('name');
                    if (name) {
                        $(this).attr('name', name.replace(/items\[\d+\]/, 'items[' + i + ']'));
                    }
                });
            });
            recalcularFlete();
            recalcularCargosCliente();
        });

        // ── Validaciones al enviar ────────────────────────────────────────
        $('#shipment-form').on('submit', function () {
            return true;
        });

        // ─── Modal Rápido de Nuevo Cliente ────────────────────────────────────
        var $quickPartyModal = $('#quick-party-modal');
        var $quickPartyForm = $('#quick-party-form');
        var currentQuickPartyTarget = null; // Guardará si fue remitente_id o destinatario_id

        $('.btn-quick-party').on('click', function() {
            currentQuickPartyTarget = $(this).data('target');
            $quickPartyForm[0].reset();
            $quickPartyModal.removeClass('hidden');
        });

        $('#btn-close-quick-party, #backdrop-quick-party').on('click', function() {
            $quickPartyModal.addClass('hidden');
            currentQuickPartyTarget = null;
        });

        $quickPartyForm.on('submit', function(e) {
            e.preventDefault();
            var $submitBtn = $(this).find('button[type="submit"]');
            var originalText = $submitBtn.text();
            
            $submitBtn.prop('disabled', true).text('Guardando...');

            $.ajax({
                url: '/parties/ajax-store',
                method: 'POST',
                data: $quickPartyForm.serialize(),
                success: function(response) {
                    if (response.success && response.party) {
                        var newOption = new Option(response.party.name, response.party.id, true, true);
                        $(currentQuickPartyTarget).append(newOption).trigger('change');
                        
                        window.toastr.success('Cliente creado correctamente');
                        $quickPartyModal.addClass('hidden');
                    }
                },
                error: function(xhr) {
                    var msg = 'Ocurrió un error al crear el cliente.';
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        msg = Object.values(xhr.responseJSON.errors)[0][0];
                    }
                    window.toastr.error(msg);
                },
                complete: function() {
                    $submitBtn.prop('disabled', false).text(originalText);
                }
            });
        });

    });
})();
