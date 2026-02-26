/**
 * Formulario de guías (shipments): Select2 con búsqueda y validación origen≠destino, remitente≠destinatario.
 * Depende de jQuery y Select2 cargados en app.js.
 */
(function () {
    'use strict';

    if (!document.getElementById('shipment-form')) return;

    var $ = window.jQuery || window.$;
    if (!$ || !$.fn.select2) return;

    var opts = {
        placeholder: 'Buscar...',
        // has
        width: '100%',
        minimumResultsForSearch: 0,
        language: {
            noResults: function () { return 'Sin resultados'; },
            searching: function () { return 'Buscando...'; },
        },
    };

    function syncDestination() {
        var originVal = $('#origen_id').val();
        var $dest = $('#destino_id');
        $dest.find('option').each(function () {
            var v = this.value;
            $(this).prop('disabled', !!v && String(v) === String(originVal));
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
            var v = this.value;
            $(this).prop('disabled', !!v && String(v) === String(destVal));
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

    function syncRecipient() {
        var senderVal = $('#remitente_id').val();
        var $rec = $('#destinatario_id');
        $rec.find('option').each(function () {
            var v = this.value;
            $(this).prop('disabled', !!v && String(v) === String(senderVal));
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
            var v = this.value;
            $(this).prop('disabled', !!v && String(v) === String(recVal));
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

    function initSelect2() {
        $('#origen_id').select2(opts).on('change', syncDestination);
        $('#destino_id').select2(opts).on('change', syncOrigin);
        $('#remitente_id').select2(opts).on('change', syncRecipient);
        $('#destinatario_id').select2(opts).on('change', syncSender);
        syncDestination();
        syncOrigin();
        syncRecipient();
        syncSender();
    }

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

    $(document).ready(function () {
        initSelect2();
        // initial calculation
        calculateTotals();

        // Calculate totals when import fields change
        function parseNum(v) {
            var n = parseFloat(String(v).replace(/[,\s]/g, ''));
            return isNaN(n) ? 0 : n;
        }

        function calculateTotals() {
            var flete = parseNum($('#flete').val());
            var seguro = parseNum($('#seguro').val());
            var monto_contra_reembolso = parseNum($('#monto_contra_reembolso').val());
            var retencion_mercaderia = parseNum($('#retencion_mercaderia').val());
            var otros_cargos = parseNum($('#otros_cargos').val());
            var iva = parseNum($('#iva_percent').val());

            var subtotal = flete + seguro + monto_contra_reembolso + retencion_mercaderia + otros_cargos;
            var tax = subtotal * (iva / 100);
            var total = subtotal + tax;
            console.log(subtotal, total);

            // Usar .toFixed(2) para mantener formato numérico válido en type="number"
            $('#subtotal').val(subtotal.toFixed(2));
            $('#iva_monto').val(tax.toFixed(2));
            $('#total').val(total.toFixed(2));
        }

        $('#flete, #seguro, #monto_contra_reembolso, #retencion_mercaderia, #otros_cargos, #iva_percent')
            .on('input change', calculateTotals);



        $(document).on('click', '#add-item', function () {
            addItemRow();
        });

        $(document).on('click', '.remove-item', function () {
            var rows = $('#items-container .item-row');
            if (rows.length <= 1) return;
            $(this).closest('.item-row').remove();
            // Reindex names so items[0], items[1], ...
            $('#items-container .item-row').each(function (i) {
                $(this).find('[name^="items["]').each(function () {
                    var name = $(this).attr('name');
                    if (name) {
                        var newName = name.replace(/items\[\d+\]/, 'items[' + i + ']');
                        $(this).attr('name', newName);
                    }
                });
            });
        });

        $('#shipment-form').on('submit', function () {
            var origin = $('#origen_id').val();
            var dest = $('#destino_id').val();
            if (origin && dest && String(origin) === String(dest)) {
                window.toastr.warning('El destino debe ser distinto al origen.');
                return false;
            }
            var sender = $('#remitente_id').val();
            var rec = $('#destinatario_id').val();
            if (sender && rec && String(sender) === String(rec)) {
                window.toastr.warning('El destinatario debe ser distinto al remitente.');
                return false;
            }
            return true;
        });
    });
})();
