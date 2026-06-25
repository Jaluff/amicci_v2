import $ from 'jquery';
import 'datatables.net-dt';
import 'datatables.net-responsive-dt';
import { openCompanySelector } from '../../shared/company-selector.js';

$(function () {
    const tableElement = $('#loads-table');
    
    if ($('#filter_fecha_range').length) {
        flatpickr('#filter_fecha_range', {
            mode: 'range',
            dateFormat: 'Y-m-d',
            locale: 'es',
            altInput: true,
            altFormat: 'd/m/Y',
            allowInput: true
        });
    }
    
    const dt = tableElement.DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
            url: tableElement.data('url'),
            data: function (d) {
                d.company_id = $('#filter_company').val();
                d.numero = $('#filter_numero').val();
                d.estado = $('#filter_estado').val();
                d.facturada = $('#filter_facturada').val();
                d.cobrada = $('#filter_cobrada').val();
                
                const rangeVal = $('#filter_fecha_range').val();
                if (rangeVal) {
                    const parts = rangeVal.includes(' a ') ? rangeVal.split(' a ') : rangeVal.split(' to ');
                    if (parts.length === 2) {
                        d.fecha_inicio = parts[0];
                        d.fecha_fin = parts[1];
                    } else {
                        d.fecha_inicio = rangeVal;
                        d.fecha_fin = rangeVal;
                    }
                } else {
                    d.fecha_inicio = '';
                    d.fecha_fin = '';
                }
            }
        },
        columns: [
            { data: 'company_name', name: 'company.name', className: 'text-center' },
            { data: 'numero', name: 'numero' },
            { data: 'fecha_carga', name: 'fecha_carga' },
            { data: 'remitente_upper', name: 'remitente.name' },
            { data: 'destinatario_upper', name: 'destinatario.name' },
            { data: 'ruta_corta', name: 'ruta_corta', orderable: false, searchable: false, className: 'text-center' },
            { data: 'importe', name: 'importe_factura', orderable: false, searchable: false, className: 'text-right' },
            { data: 'estado_badge', name: 'estado', orderable: false, searchable: false, className: 'text-center' },
            { data: 'facturada_badge', name: 'facturada', orderable: false, searchable: false, className: 'text-center' },
            { data: 'cobrada_badge', name: 'cobrada', orderable: false, searchable: false, className: 'text-center' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-center' }
        ],
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
        },
        order: [[0, 'desc']]
    });

    if ($.fn.select2) {
        $('#filter_company, #filter_estado, #filter_facturada, #filter_cobrada').select2({
            width: '100%',
            minimumResultsForSearch: 10
        });
    }

    $('#btn-filter').on('click', function () {
        dt.draw();
    });

    // ── Cambiar Estado ──
    tableElement.on('click', '.btn-change-state, .btn-change-state-badge', function () {
        const id = $(this).data('id');
        const status = $(this).data('status');
        
        if (confirm(`¿Cambiar estado a ${status}?`)) {
            const form = $('#form-change-state');
            form.attr('action', `/loads/${id}/change-state`);
            $('#change-state-status').val(status);
            form.submit();
        }
    });

    // ── Facturar ──
    tableElement.on('click', '.btn-invoice', function () {
        const id = $(this).data('id');
        const numero = $(this).data('numero');
        const importe = $(this).data('importe');

        $('#modal-invoice-numero').text(numero);
        $('#form-invoice').attr('action', `/loads/${id}/invoice`);

        // Pre-poblar el importe si viene cargado desde la carga
        const importeInput = $('#input-importe-factura');
        const hint = $('#hint-importe');
        if (importe && parseFloat(importe) > 0) {
            importeInput.val(parseFloat(importe).toFixed(2));
            hint.removeClass('hidden');
        } else {
            importeInput.val('');
            hint.addClass('hidden');
        }

        $('#modal-invoice').removeClass('hidden');
        importeInput.focus();
    });

    // ── Cobrar ──
    tableElement.on('click', '.btn-pay', function () {
        const id = $(this).data('id');
        const numero = $(this).data('numero');
        
        $('#modal-pay-numero').text(numero);
        $('#form-pay').attr('action', `/loads/${id}/pay`);
        $('#modal-pay').removeClass('hidden');
    });

    // Company selector modal
    const btn = document.getElementById('btn-nueva-carga');
    if (btn) {
        const companies = JSON.parse(btn.dataset.companies || '[]');
        const createUrl = btn.dataset.url;

        btn.addEventListener('click', () => {
            if (companies.length === 1) {
                window.location.href = `${createUrl}?company_id=${companies[0].id}`;
                return;
            }
            openCompanySelector({
                companies,
                title: 'Nueva Carga Completa',
                subtitle: '¿Para qué empresa deseas registrar la carga?',
                onSelect: (company) => {
                    window.location.href = `${createUrl}?company_id=${company.id}`;
                }
            });
        });
    }
});
