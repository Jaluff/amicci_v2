import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/pages/shipments/form.js',
                'resources/js/pages/shipments/index.js',
                'resources/js/pages/transportRoutes/transportRoutes.js',
                'resources/js/pages/transportRoutes/form.js',
                'resources/js/pages/dispatches/dispatches.js',
                'resources/js/pages/dispatches/form.js',
                'resources/js/pages/deliveries/deliveries.js'
            ],
            refresh: true,
        }),
    ],
});
