@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Formulario de Ubicación -->
            <div class="md:col-span-1">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 id="formTitle" class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-4">Nueva Ubicación</h3>
                    
                    <form id="locationForm" action="{{ route('ubicaciones.store') }}" method="POST">
                        @csrf
                        <div id="methodField"></div>
                        
                        <div class="mb-4">
                            <label for="nombre" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre</label>
                            <input type="text" name="nombre" id="nombre" value="{{ old('nombre') }}" 
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                required>
                            @error('nombre')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center gap-2">
                            <button type="submit" id="submitBtn"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Guardar
                            </button>
                            <button type="button" id="cancelBtn" class="hidden text-sm text-gray-600 dark:text-gray-400 hover:underline">
                                Cancelar
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Listado de Ubicaciones -->
            <div class="md:col-span-2">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-4">Listado de Ubicaciones</h3>

                    @if(session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline">{{ session('error') }}</span>
                        </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($ubicaciones as $ubicacion)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ $ubicacion->nombre }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex justify-end gap-3">
                                                <button type="button" 
                                                    class="edit-btn text-indigo-600 hover:text-indigo-900"
                                                    data-id="{{ $ubicacion->id }}"
                                                    data-nombre="{{ $ubicacion->nombre }}"
                                                    data-url="{{ route('ubicaciones.update', $ubicacion) }}">
                                                    Editar
                                                </button>
                                                <form action="{{ route('ubicaciones.destroy', $ubicacion) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar esta ubicación?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900">Eliminar</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="px-6 py-4 text-center text-sm text-gray-500">
                                            No hay ubicaciones registradas.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    $(document).ready(function() {
        const $form = $('#locationForm');
        const $formTitle = $('#formTitle');
        const $submitBtn = $('#submitBtn');
        const $cancelBtn = $('#cancelBtn');
        const $methodField = $('#methodField');
        const $nombreInput = $('#nombre');
        
        const originalAction = $form.attr('action');

        $('.edit-btn').on('click', function() {
            const id = $(this).data('id');
            const nombre = $(this).data('nombre');
            const url = $(this).data('url');

            $nombreInput.val(nombre);
            $form.attr('action', url);
            $methodField.html('<input type="hidden" name="_method" value="PUT">');
            $formTitle.text('Editar Ubicación');
            $submitBtn.text('Actualizar');
            $cancelBtn.removeClass('hidden');
            
            // Scroll to form on mobile
            if (window.innerWidth < 768) {
                $form[0].scrollIntoView({ behavior: 'smooth' });
            }
        });

        $cancelBtn.on('click', function() {
            $nombreInput.val('');
            $form.attr('action', originalAction);
            $methodField.empty();
            $formTitle.text('Nueva Ubicación');
            $submitBtn.text('Guardar');
            $(this).addClass('hidden');
        });
    });
</script>
@endsection
