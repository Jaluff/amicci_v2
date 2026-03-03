import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import colors from 'tailwindcss/colors';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './app/Http/Controllers/**/*.php',
        './resources/js/**/*.js',
    ],

    theme: {
        extend: {
            colors: {
                // Al sobreescribir 'indigo', todo lo que usa Laravel/Breeze/Jetstream
                // automáticamente cambiará de violeta a este azul estilo Bootstrap 5.
                indigo: {
                    50: '#f0f5ff',
                    100: '#e5edff',
                    200: '#cddbfe',
                    300: '#a4bdfd',
                    400: '#7694f8',
                    500: '#3b82f6',
                    600: '#0d6efd', // Bootstrap 5 Primary
                    700: '#0b5ed7', // Hover Bootstrap
                    800: '#0a58ca',
                    900: '#084298',
                },

                // Agregamos las variables exactas de Bootstrap 5 por si querés
                // usarlas directamente como: bg-primary, text-danger, bg-success, etc.
                primary: '#0d6efd',
                secondary: '#6c757d',
                success: '#198754',
                info: '#0dcaf0',
                warning: '#ffc107',
                danger: '#dc3545',
                light: '#f8f9fa',
                bsdark: '#212529', // Nombre bsdark para no chocar con el dark mode de Tailwind
            },
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};
