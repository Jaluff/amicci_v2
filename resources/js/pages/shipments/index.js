/**
 * Listado de guías — shipments/index.blade.php
 * Depende de jQuery y DataTables cargados en app.js.
 */
import $ from 'jquery';
import { openCompanySelector } from '../../shared/company-selector.js';

$(function () {
    $('.select2').select2({ width: '100%' });

    if (!$('#shipmentsTable').length) return;

    $('#shipmentsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: $('#shipmentsTable').data('url'),
            data: function (d) {
                d.origen_id = $('#filter_origen_id').val();
                d.destino_id = $('#filter_destino_id').val();
                d.fecha_inicio = $('#filter_fecha_inicio').val();
                d.fecha_fin = $('#filter_fecha_fin').val();
                d.numero_documento = $('#filter_numero_documento').val();
                d.cliente = $('#filter_cliente').val();
                d.ubicacion = $('#filter_ubicacion').val();
                d.company_id = $('#filter_company_id').val();
            }
        },
        columns: [
            { 
                data: 'empresa', 
                name: 'companies.prefix', 
                responsivePriority: 3,
                render: function(data, type, row) {
                    const color = row.empresa_color || '#6366f1';
                    return `<span class="px-2 py-1 rounded-full text-[10px] font-bold text-white shadow-sm" style="background-color: ${color}">${data}</span>`;
                }
            },
            { data: 'fecha', name: 'shipments.fecha', responsivePriority: 2 },
            { data: 'numero', name: 'shipments.numero', responsivePriority: 1 },
            { data: 'origen_nombre', name: 'origen.nombre', defaultContent: '-', responsivePriority: 6 },
            { data: 'destino_nombre', name: 'destino.nombre', defaultContent: '-', responsivePriority: 6 },
            { data: 'remitente_destinatario', name: 'remitente_destinatario', orderable: false, searchable: false, responsivePriority: 7 },
            { data: 'flete', name: 'shipments.flete', responsivePriority: 5 },
            { data: 'bultos', name: 'bultos', orderable: false, searchable: false, responsivePriority: 4 },
            { data: 'valor_declarado', name: 'valor_declarado', orderable: false, searchable: false, visible: true, responsivePriority: 8 },
            { data: 'total', name: 'shipments.total', responsivePriority: 3 },
            {
                data: 'ubicacion_actual',
                name: 'shipments.ubicacion_actual',
                defaultContent: '-',
                responsivePriority: 5,
                orderable: true,
                searchable: true,
                className: 'text-center',
            },
            { data: 'acciones', name: 'acciones', orderable: false, searchable: false, responsivePriority: 1, className: 'text-center' },
        ],
        order: [[1, 'desc']],
    });

    $('#btn-filter').on('click', function () {
        $('#shipmentsTable').DataTable().ajax.reload();
    });

    $('input[id^="filter_"], select[id^="filter_"]').on('keypress', function (e) {
        if (e.which === 13) {
            e.preventDefault();
            $('#btn-filter').click();
        }
    });

    // Company selector modal — lee datos desde el data-attribute del botón
    const btn = document.getElementById('btn-nueva-guia');
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
                title: 'Nueva Guía de Transporte',
                subtitle: '¿Para qué empresa deseas crear la guía?',
                onSelect: (company) => {
                    window.location.href = `${createUrl}?company_id=${company.id}`;
                }
            });
        });
    }
});
