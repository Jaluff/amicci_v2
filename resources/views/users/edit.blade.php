@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="w-full sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200 mb-6">Editar Usuario: {{ $user->name }}</h2>

            <form action="{{ route('users.update', $user) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <x-input-label for="name" value="Nombre" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                            :value="old('name', $user->name)" required autofocus />
                    </div>

                    <div>
                        <x-input-label for="email" value="Email" />
                        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                            :value="old('email', $user->email)" required />
                    </div>

                    <div>
                        <x-input-label for="password" value="Contraseña (dejar en blanco para no cambiar)" />
                        <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" />
                    </div>

                    <div>
                        <x-input-label for="password_confirmation" value="Confirmar Contraseña" />
                        <x-text-input id="password_confirmation" name="password_confirmation" type="password"
                            class="mt-1 block w-full" />
                    </div>

                    <div>
                        <x-input-label for="role" value="Rol en el sistema" />
                        <select id="role" name="role" required
                            class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm mt-1 block w-full">
                            <option value="">Selecciona un rol</option>
                            @foreach($roles as $role)
                            <option value="{{ $role->name }}" {{ old('role', $user->roles->first()?->name) ==
                                $role->name ? 'selected' : '' }}>
                                {{ ucfirst($role->name) }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mb-6">
                    <x-input-label value="Empresas Asignadas" class="mb-2" />
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                        @foreach($companies as $company)
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="companies[]" value="{{ $company->id }}" {{
                                (is_array(old('companies')) && in_array($company->id, old('companies'))) ||
                            $user->companies->contains($company->id) ? 'checked' : '' }}
                            class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600
                            shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800">
                            <span class="ml-2 text-gray-700 dark:text-gray-300">{{ $company->prefix }} - {{
                                $company->name }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex items-center justify-end mt-4">
                    <a href="{{ route('users.index') }}"
                        class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 mr-2">
                        Cancelar
                    </a>
                    <x-primary-button>
                        Actualizar Usuario
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection