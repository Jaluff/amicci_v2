<nav x-data="{ open: false }" class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
    <!-- Primary Navigation Menu -->
    <div class="w-full px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                </div>
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('shipments.index')" :active="request()->routeIs('shipments.*')">
                        {{ __('Guias') }}
                    </x-nav-link>
                </div>

                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('loads.index')" :active="request()->routeIs('loads.*')">
                        {{ __('Cargas') }}
                    </x-nav-link>
                </div>
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('routes.index')" :active="request()->routeIs('routes.*')">
                        {{ __('Rutas') }}
                    </x-nav-link>
                </div>
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dispatches.index')" :active="request()->routeIs('dispatches.*')">
                        {{ __('Despachos') }}
                    </x-nav-link>
                </div>
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('deliveries.index')" :active="request()->routeIs('deliveries.*')">
                        {{ __('Repartos') }}
                    </x-nav-link>
                </div>
                <!-- Dropdown Menú Agenda (Desktop) -->
                <div class="hidden sm:flex sm:items-center sm:ms-10 sm:-my-px h-16 pt-1">
                    <x-dropdown align="left" width="48">
                        <x-slot name="trigger">
                            <button
                                class="inline-flex items-center px-1 pt-1 h-full border-b-2 {{ request()->routeIs('parties.*') || request()->routeIs('drivers.*') || request()->routeIs('deliverers.*') || request()->routeIs('branches.*') ? 'border-indigo-400 dark:border-indigo-600 text-gray-900 dark:text-gray-100' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-700' }} text-sm font-medium leading-5 focus:outline-none transition duration-150 ease-in-out">
                                <div>{{ __('Agenda') }}</div>
                                <div class="ms-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('parties.index')">
                                {{ __('Clientes') }}
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('drivers.index')">
                                {{ __('Conductores') }}
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('deliverers.index')">
                                {{ __('Repartidores') }}
                            </x-dropdown-link>
                        </x-slot>
                    </x-dropdown>
                </div>
                @if(auth()->user()->hasRole(['admin', 'supervisor']))
                    <!-- Dropdown Menú Administración (Desktop) -->
                    <div class="hidden sm:flex sm:items-center sm:ms-10 sm:-my-px h-16 pt-1">
                        <x-dropdown align="left" width="48">
                            <x-slot name="trigger">
                                <button
                                    class="inline-flex items-center px-1 pt-1 h-full border-b-2 {{ request()->routeIs('reports.*') || request()->routeIs('billing.*') ? 'border-indigo-400 dark:border-indigo-600 text-gray-900 dark:text-gray-100' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-700' }} text-sm font-medium leading-5 focus:outline-none transition duration-150 ease-in-out">
                                    <div>{{ __('Administración') }}</div>
                                    <div class="ms-1">
                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <x-dropdown-link :href="route('reports.dispatches.index')">
                                    {{ __('Reporte de Guías') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('billing.index')" :active="request()->routeIs('billing.*')">
                                    {{ __('Facturación') }}
                                </x-dropdown-link>
                            </x-slot>
                        </x-dropdown>
                    </div>

                    <!-- Dropdown Menú Configuraciones (Desktop) -->
                    <div class="hidden sm:flex sm:items-center sm:ms-10 sm:-my-px h-16 pt-1">
                        <x-dropdown align="left" width="48">
                            <x-slot name="trigger">
                                <button
                                    class="inline-flex items-center px-1 pt-1 h-full border-b-2 {{ request()->routeIs('users.*') || request()->routeIs('branches.*') || request()->routeIs('companies.*') || request()->routeIs('company.*') || request()->routeIs('tariff-tables.*') ? 'border-indigo-400 dark:border-indigo-600 text-gray-900 dark:text-gray-100' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-700' }} text-sm font-medium leading-5 focus:outline-none transition duration-150 ease-in-out">
                                    <div>{{ __('Configuraciones') }}</div>
                                    <div class="ms-1">
                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <x-dropdown-link :href="route('users.index')">
                                    {{ __('Usuarios') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('branches.index')">
                                    {{ __('Sucursales') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('companies.index')">
                                    {{ __('Datos de Empresa') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('tariff-tables.index')">
                                    {{ __('Tarifas') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('ubicaciones.index')">
                                    {{ __('Ubicaciones') }}
                                </x-dropdown-link>
                            </x-slot>
                        </x-dropdown>
                    </div>
                @endif
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6 gap-3">



                <!-- Theme Toggle Button -->
                <div x-data="themeToggle()">
                    <button @click="toggleTheme()"
                        class="text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none rounded-lg  p-2.5">
                        <svg x-show="!isDark" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"
                            xmlns="http://www.w3.org/2000/svg">
                            <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                        </svg>
                        <svg x-show="isDark" x-cloak class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"
                            xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"
                                fill-rule="evenodd" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>

                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button
                            class="inline-flex items-center px-3 py-2 border border-transparent  leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Mi Perfil') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Salir') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open"
                    class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round"
                            stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('shipments.index')" :active="request()->routeIs('shipments.index')">
                {{ __('Guias') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('loads.index')" :active="request()->routeIs('loads.*')">
                {{ __('Cargas') }}
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('routes.index')" :active="request()->routeIs('routes.*')">
                {{ __('Rutas') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('dispatches.index')" :active="request()->routeIs('dispatches.*')">
                {{ __('Despachos') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('deliveries.index')" :active="request()->routeIs('deliveries.*')">
                {{ __('Repartos') }}
            </x-responsive-nav-link>
            <!-- Dropdown Menú Agenda (Mobile) -->
            <div x-data="{ agendaOpen: false }" class="border-t border-gray-200 dark:border-gray-700 mt-2">
                <button @click="agendaOpen = ! agendaOpen"
                    class="w-full flex items-center justify-between ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-600 focus:outline-none transition duration-150 ease-in-out">
                    {{ __('Agenda') }}
                    <svg class="fill-current h-4 w-4 transition-transform duration-200"
                        :class="{ 'rotate-180': agendaOpen }" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                            clip-rule="evenodd" />
                    </svg>
                </button>
                <div x-show="agendaOpen" class="bg-gray-50 dark:bg-gray-900 pl-4 py-1" style="display: none;">
                    <x-responsive-nav-link :href="route('parties.index')" :active="request()->routeIs('parties.*')">
                        {{ __('Clientes') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('drivers.index')" :active="request()->routeIs('drivers.*')">
                        {{ __('Conductores') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('deliverers.index')"
                        :active="request()->routeIs('deliverers.*')">
                        {{ __('Repartidores') }}
                    </x-responsive-nav-link>
                </div>
            </div>
            @if(auth()->user()->hasRole(['admin', 'supervisor']))
                <!-- Dropdown Menú Administración (Mobile) -->
                <div x-data="{ adminOpen: false }" class="border-t border-gray-200 dark:border-gray-700 mt-2">
                    <button @click="adminOpen = ! adminOpen"
                        class="w-full flex items-center justify-between ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-600 focus:outline-none transition duration-150 ease-in-out">
                        {{ __('Administración') }}
                        <svg class="fill-current h-4 w-4 transition-transform duration-200"
                            :class="{ 'rotate-180': adminOpen }" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                clip-rule="evenodd" />
                        </svg>
                    </button>
                    <div x-show="adminOpen" class="bg-gray-50 dark:bg-gray-900 pl-4 py-1" style="display: none;">
                        <x-responsive-nav-link :href="route('reports.dispatches.index')"
                            :active="request()->routeIs('reports.dispatches.*')">
                            {{ __('Reporte de Guías') }}
                        </x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('billing.index')" :active="request()->routeIs('billing.*')">
                            {{ __('Facturación') }}
                        </x-responsive-nav-link>
                    </div>
                </div>

                <!-- Dropdown Menú Configuraciones (Mobile) -->
                <div x-data="{ confOpen: false }" class="border-t border-gray-200 dark:border-gray-700 mt-2">
                    <button @click="confOpen = ! confOpen"
                        class="w-full flex items-center justify-between ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-600 focus:outline-none transition duration-150 ease-in-out">
                        {{ __('Configuraciones') }}
                        <svg class="fill-current h-4 w-4 transition-transform duration-200"
                            :class="{ 'rotate-180': confOpen }" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                clip-rule="evenodd" />
                        </svg>
                    </button>
                    <div x-show="confOpen" class="bg-gray-50 dark:bg-gray-900 pl-4 py-1" style="display: none;">
                        <x-responsive-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')">
                            {{ __('Usuarios') }}
                        </x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('branches.index')" :active="request()->routeIs('branches.*')">
                            {{ __('Sucursales') }}
                        </x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('companies.index')" :active="request()->routeIs('companies.*') || request()->routeIs('company.*')">
                            {{ __('Datos de Empresa') }}
                        </x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('tariff-tables.index')"
                            :active="request()->routeIs('tariff-tables.*')">
                            {{ __('Tarifas') }}
                        </x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('ubicaciones.index')"
                            :active="request()->routeIs('ubicaciones.*')">
                            {{ __('Ubicaciones') }}
                        </x-responsive-nav-link>
                    </div>
                </div>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ Auth::user()->name }}</div>
                <div class="font-medium  text-gray-500">{{ Auth::user()->email }}</div>
            </div>



            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Mi Perfil') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>