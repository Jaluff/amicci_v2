<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    <!-- PWA Support -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#dc8a18">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Amicci">
    <link rel="apple-touch-icon" href="/images/logo_amicci.png">
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(reg => console.log('Service Worker registered.'))
                    .catch(err => console.log('Service Worker failed:', err));
            });
        }
    </script>
</head>

<body class="font-sans antialiased bg-white dark:bg-gray-900">
    <div class="min-h-screen bg-white dark:bg-gray-900">
        @if(!request()->has('iframe'))
            {{-- Header minimalista para repartidores con solo la opción de Cerrar Sesión --}}
            <header class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
                <div class="max-w-lg mx-auto px-4 h-14 flex items-center justify-between">
                    <span class="font-bold text-gray-800 dark:text-gray-200 text-sm">Amicci Repartos</span>
                    <form method="POST" action="{{ route('logout') }}" class="m-0">
                        @csrf
                        <button type="submit" class="text-xs font-bold text-red-600 hover:text-red-700 bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-900/50 rounded-lg px-3 py-1.5 transition">
                            Cerrar Sesión
                        </button>
                    </form>
                </div>
            </header>
        @endif

        <!-- Toast Notifications -->
        @include('components.toast-notifications')

        <!-- Page Content -->
        <main class="py-4">
            <div class="max-w-full mx-auto px-4">
                @yield('content')
                {{ $slot ?? '' }}
            </div>
        </main>
    </div>

    @include('partials._global_problem_modals')
    @stack('modals')
    @yield('scripts')
</body>

</html>
