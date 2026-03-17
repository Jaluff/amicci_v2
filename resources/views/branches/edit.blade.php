@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200 mb-6 border-b border-gray-200 dark:border-gray-700 pb-2">
            🏢 Editar Sucursal: {{ $branch->name }}
        </h2>

        @if ($errors->any())
        <div class="mb-4 bg-red-50 dark:bg-red-900/40 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 p-4 rounded-md">
            <ul class="list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('branches.update', $branch) }}" method="POST" class="space-y-4 max-w-lg">
            @csrf
            @method('PUT')

            <div>
                <label class="font-medium text-gray-700 dark:text-gray-300 block mb-1">Nombre *</label>
                <x-text-input name="name" type="text" value="{{ old('name', $branch->name) }}"
                    class="w-full py-2 px-3 rounded border-gray-300 dark:border-gray-700" required />
            </div>

            <div>
                <label class="font-medium text-gray-700 dark:text-gray-300 block mb-1">Ubicación Física *</label>
                <select name="ubicacion_id" required
                    class="w-full py-2 px-3 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Seleccionar Ubicación...</option>
                    @foreach($ubicaciones as $ub)
                    <option value="{{ $ub->id }}" @selected(old('ubicacion_id', $branch->ubicacion_id) == $ub->id)>{{ $ub->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="font-medium text-gray-700 dark:text-gray-300 block mb-1">
                    Código numérico *
                    <span class="text-xs text-gray-400 font-normal ml-1">(aparece en el número de guía)</span>
                </label>
                <x-text-input name="code" type="number" value="{{ old('code', $branch->code) }}"
                    class="w-32 py-2 px-3 rounded border-gray-300 dark:border-gray-700"
                    min="1" max="99" required />
                <p class="text-xs text-gray-400 mt-1">Guías emitidas: <strong>{{ $branch->last_shipment_number }}</strong></p>
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" name="active" id="active" value="1"
                    {{ old('active', $branch->active) ? 'checked' : '' }} class="rounded" />
                <label for="active" class="font-medium text-gray-700 dark:text-gray-300">Activa</label>
            </div>

            <div class="flex gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('branches.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 transition">
                    Cancelar
                </a>
                <button type="submit"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md text-sm font-semibold transition">
                    Actualizar
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
