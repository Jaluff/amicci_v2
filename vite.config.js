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
                'resources/js/pages/deliveries/deliveries.js',
                'resources/js/pages/users/index.js',
                'resources/js/pages/drivers/index.js',
                'resources/js/pages/deliverers/index.js',
                'resources/js/pages/branches/index.js',
                'resources/js/pages/reports/dispatches.js',
                'resources/js/pages/billing/index.js',
                'resources/js/pages/billing/invoices.js',
                'resources/js/pages/billing/show.js',
                'resources/js/pages/loads/index.js',
                'resources/js/pages/loads/form.js'
            ],
            refresh: true,
        }),
    ],
    server: {
        host: '0.0.0.0',
        port: 5173,
        strictPort: true,
        hmr: {
            host: undefined,
        },
    },
});
