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
    function syncDestination() {
        var originVal = $('#origen_id').val();
        var $dest = $('#destino_id');
        $dest.find('option').each(function () {
            $(this).prop('disabled', !!this.value && String(this.value) === String(originVal));
        });
        if ($dest.val() && String($dest.val()) === String(originVal)) {
            $dest.val(null).trigger('change');
        }
        $dest.off('change.select2-sync').on('change', function () {
            var v = $(this).val();
            if (v && originVal && String(v) === String(originVal)) {
                $(this).val(null).trigger('change');
                return;
            }
            syncOrigin();
        });
    }

    function syncOrigin() {
        var destVal = $('#destino_id').val();
        var $orig = $('#origen_id');
        $orig.find('option').each(function () {
            $(this).prop('disabled', !!this.value && String(this.value) === String(destVal));
        });
        if ($orig.val() && String($orig.val()) === String(destVal)) {
            $orig.val(null).trigger('change');
        }
        $orig.off('change.select2-sync').on('change', function () {
            var v = $(this).val();
            if (v && destVal && String(v) === String(destVal)) {
                $(this).val(null).trigger('change');
                return;
            }
            syncDestination();
        });
    }

    // ─── Sincronización remitente/destinatario ───────────────────────────────
    function syncRecipient() {
        var senderVal = $('#remitente_id').val();
        var $rec = $('#destinatario_id');
        $rec.find('option').each(function () {
            $(this).prop('disabled', !!this.value && String(this.value) === String(senderVal));
        });
        if ($rec.val() && String($rec.val()) === String(senderVal)) {
            $rec.val(null).trigger('change');
        }
        $rec.off('change.select2-sync').on('change', function () {
            var v = $(this).val();
            if (v && senderVal && String(v) === String(senderVal)) {
                $(this).val(null).trigger('change');
                return;
            }
            syncSender();
        });
    }

    function syncSender() {
        var recVal = $('#destinatario_id').val();
        var $send = $('#remitente_id');
        $send.find('option').each(function () {
            $(this).prop('disabled', !!this.value && String(this.value) === String(recVal));
        });
        if ($send.val() && String($send.val()) === String(recVal)) {
            $send.val(null).trigger('change');
        }
        $send.off('change.select2-sync').on('change', function () {
            var v = $(this).val();
            if (v && recVal && String(v) === String(recVal)) {
                $(this).val(null).trigger('change');
                return;
            }
            syncRecipient();
        });
    }

    // ─── Carga de tarifa del remitente (AJAX) ────────────────────────────────
    /**
     * Cuando cambia el remitente, consulta su configuración tarifaria.
     * Si tiene tarifa activa, la muestra en el banner y recalcula el flete.
     */
    function loadSenderTariff(partyId) {
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
                    tariffSetting = null;
                    hideTariffBanner();
                }
                // Recalcular flete con la nueva tarifa
                recalcularFlete();
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
     * Calcula el flete según el modo tarifario.
     * Para modo 'kg' hace AJAX al servidor (necesita la escala de tramos).
     * Para el resto calcula en el cliente directamente.
     */
    function recalcularFlete() {
        if (!tariffSetting || !tariffSetting.has_tariff) return;

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
                    remitente_id: $('#remitente_id').val() 
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

    function addItemRow() {
        var tpl = document.getElementById('item-row-template');
        if (!tpl) return;
        var index = getNextItemIndex();
        var html = tpl.innerHTML.replace(/__INDEX__/g, index);
        $('#items-container').append(html);
    }

    // ─── Init ─────────────────────────────────────────────────────────────────
    function initSelect2() {
        $('#origen_id').select2(opts).on('change', syncDestination);
        $('#destino_id').select2(opts).on('change', syncOrigin);

        // Al cambiar origen o destino → recalcular (afecta al modo kg)
        $('#origen_id, #destino_id').on('change', function () {
            recalcularFlete();
        });

        // Al cambiar remitente → cargar su tarifa y recalcular
        $('#remitente_id').select2(opts).on('change', function () {
            syncRecipient();
            loadSenderTariff($(this).val());
        });

        $('#destinatario_id').select2(opts).on('change', syncSender);

        syncDestination();
        syncOrigin();
        syncRecipient();
        syncSender();
    }

    $(document).ready(function () {
        initSelect2();

        // ── Cargar tarifa si ya hay remitente seleccionado (modo edición)
        var initialSender = $('#remitente_id').val();
        if (initialSender) {
            loadSenderTariff(initialSender);
        }

        // ── Calcular totales de importes ──────────────────────────────────
        function parseNum(v) {
            var n = parseFloat(String(v).replace(/[,\s]/g, ''));
            return isNaN(n) ? 0 : n;
        }

        function calculateTotals() {
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
        }

        $('#flete, #seguro, #monto_contra_reembolso, #retencion_mercaderia, #otros_cargos, #iva_percent')
            .on('input change', calculateTotals);

        calculateTotals();

        // ── Recalcular flete al cambiar ítems ────────────────────────────
        // (cantidad, tipo_paquete, peso, volumen, valor declarado)
        $(document).on('input change', '#items-container [name*="[cantidad]"], #items-container [name*="[tipo_paquete]"], #items-container [name*="[peso]"], #items-container [name*="[volumen]"], #items-container [name*="[monto_valor_declarado]"]', function () {
            recalcularFlete();
        });

        // ── Agregar / quitar ítems ────────────────────────────────────────
        $(document).on('click', '#add-item', function () {
            addItemRow();
        });

        $(document).on('click', '.remove-item', function () {
            var rows = $('#items-container .item-row');
            if (rows.length <= 1) return;
            $(this).closest('.item-row').remove();
            // Re-indexar nombres
            $('#items-container .item-row').each(function (i) {
                $(this).find('[name^="items["]').each(function () {
                    var name = $(this).attr('name');
                    if (name) {
                        $(this).attr('name', name.replace(/items\[\d+\]/, 'items[' + i + ']'));
                    }
                });
            });
            recalcularFlete();
        });

        // ── Validaciones al enviar ────────────────────────────────────────
        $('#shipment-form').on('submit', function () {
            var origin = $('#origen_id').val();
            var dest   = $('#destino_id').val();
            if (origin && dest && String(origin) === String(dest)) {
                window.toastr.warning('El destino debe ser distinto al origen.');
                return false;
            }
            var sender = $('#remitente_id').val();
            var rec    = $('#destinatario_id').val();
            if (sender && rec && String(sender) === String(rec)) {
                window.toastr.warning('El destinatario debe ser distinto al remitente.');
                return false;
            }
            return true;
        });
    });
})();
