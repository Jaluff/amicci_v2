<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#0f172a">
    <meta name="description" content="Amicci Repartos — Gestión de entregas para repartidores">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>Amicci Repartos</title>

    <link rel="manifest" href="/manifest.json">
    <link rel="icon" type="image/png" sizes="192x192" href="/pwa-icons/icon-192.png">
    <link rel="apple-touch-icon" href="/pwa-icons/icon-192.png">

    @vite(['resources/pwa/css/pwa.css', 'resources/pwa/js/pwa.js'])
</head>
<body>
    <div id="app"></div>

    <script>
        window.APP_CONFIG = {
            apiBase: '/api',
        };

        // Registrar Service Worker
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(reg => console.log('[SW] Registrado:', reg.scope))
                    .catch(err => console.error('[SW] Error:', err));
            });
        }
    </script>
</body>
</html>
