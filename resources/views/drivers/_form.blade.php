<form
    action="{{ $isEdit ? route('drivers.update', $driver->id) : route('drivers.store') }}"
    method="POST">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 relative">

        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre / Razón Social *</label>
            <x-text-input type="text" id="name" name="name"
                value="{{ old('name', $driver->name ?? '') }}"
                class="mt-1 block w-full" required />
            @error('name')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label for="dni" class="block text-sm font-medium text-gray-700 dark:text-gray-300">DNI / Documento *</label>
            <x-text-input type="text" id="dni" name="dni"
                value="{{ old('dni', $driver->dni ?? '') }}"
                class="mt-1 block w-full" required />
            @error('dni')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label for="license_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Número de Licencia *</label>
            <x-text-input type="text" id="license_number" name="license_number"
                value="{{ old('license_number', $driver->license_number ?? '') }}"
                class="mt-1 block w-full" required />
            @error('license_number')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Teléfono *</label>
            <x-text-input type="text" id="phone" name="phone"
                value="{{ old('phone', $driver->phone ?? '') }}"
                class="mt-1 block w-full" required />
            @error('phone')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Correo Electrónico *</label>
            <x-text-input type="email" id="email" name="email"
                value="{{ old('email', $driver->email ?? '') }}"
                class="mt-1 block w-full" required />
            @error('email')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Dirección *</label>
            <x-text-input type="text" id="address" name="address"
                value="{{ old('address', $driver->address ?? '') }}"
                class="mt-1 block w-full" required />
            @error('address')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <!-- Acciones -->
    <div class="flex justify-end gap-3 pt-4 mt-6 border-t border-gray-200 dark:border-gray-700">
        <a href="{{ route('drivers.index') }}"
            class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Cancelar
        </a>
        <button type="submit"
            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
            @if($isEdit)
                Actualizar
            @else
                Guardar
            @endif
        </button>
    </div>
</form>
