import './bootstrap';
import './stateMachine';
import './problemWidget';
import './problemModals';

import $ from 'jquery';
window.$ = window.jQuery = $;

import select2 from 'select2';
import 'select2/dist/css/select2.min.css';
select2(window, $);

import 'datatables.net';
import 'datatables.net-dt/css/dataTables.dataTables.css';
import 'datatables.net-responsive';
import 'datatables.net-responsive-dt/css/responsive.dataTables.css';
import 'datatables.net-buttons';
import 'datatables.net-buttons-dt/css/buttons.dataTables.css';
import 'datatables.net-buttons/js/buttons.colVis.mjs';

// Toastr
import toastr from 'toastr';
import 'toastr/build/toastr.min.css';
window.toastr = toastr;

toastr.options = {
    "closeButton": true,
    "progressBar": true,
    "positionClass": "toast-top-center",
    "timeOut": 5000,
    "extendedTimeOut": 1000,
    "showEasing": "swing",
    "hideEasing": "linear",
    "showMethod": "slideDown",
    "hideMethod": "slideUp"
};

import Alpine from 'alpinejs';

window.Alpine = Alpine;

document.addEventListener('alpine:init', () => {
    Alpine.data('themeToggle', () => ({
        isDark: false,
        init() {
            this.isDark = document.documentElement.classList.contains('dark');
        },
        toggleTheme() {
            this.isDark = !this.isDark;
            if (this.isDark) {
                document.documentElement.classList.add('dark');
                localStorage.setItem('color-theme', 'dark');
            } else {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('color-theme', 'light');
            }
        }
    }));
});

Alpine.start();

// ============================================
// DataTables: Defaults globales para todo el proyecto
// Cualquier tabla que llame .DataTable() hereda estos valores
// ============================================
$(function () {
    $.extend(true, $.fn.dataTable.defaults, {
        stateSave: true,
        stateDuration: -1,
        pageLength: 25,
        responsive: {
            details: {
                type: 'column',
                target: 'tr',
            },
        },
        layout: {
            topStart: {
                buttons: [
                    {
                        extend: 'colvis',
                        text: '☰ Columnas',
                        className: 'dt-colvis-btn',
                    },
                ],
            },
        },
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json',
        },
    });
});
