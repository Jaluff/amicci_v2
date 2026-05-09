import $ from 'jquery';
import 'select2';

$(function () {
    // Inicializar Select2 para clientes
    $('.select2-party').select2({
        placeholder: "Buscar cliente...",
        allowClear: true,
        width: '100%'
    });

    // Inicializar Select2 para sucursales
    $('.select2-branch').select2({
        placeholder: "Buscar sucursal...",
        allowClear: true,
        width: '100%'
    });

    // Inicializar Select2 para choferes
    $('#driver_id').select2({
        placeholder: "Seleccione un conductor",
        allowClear: true,
        width: '100%'
    });

    // Filtrar selects cuando cambia la empresa
    $('#company_id').on('change', function () {
        // Podríamos filtrar sucursales y choferes por empresa si fuera necesario,
        // pero en el diseño original de Dispatches se muestran todos.
        // Se deja el evento preparado por si se requiere acotar.
    });
});
