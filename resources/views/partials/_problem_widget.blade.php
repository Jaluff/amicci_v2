{{--
Widget de Problema — reutilizable para Dispatch, TransportRoute, Shipment, Reparto
Uso:
@include('partials._problem_widget', ['model' => $dispatch, 'modelType' => 'dispatch'])
--}}
@php
$currentProblem = $model->currentProblem;
$hasActive = $model->hasActiveProblem();
@endphp

<div id="problem-widget" data-model-type="{{ $modelType }}" data-model-id="{{ $model->id }}"
    data-has-active="{{ $hasActive ? 'true' : 'false' }}" class="mt-6 border rounded-lg overflow-hidden {{ $hasActive
         ? 'border-red-300 dark:border-red-700'
         : 'border-gray-200 dark:border-gray-700' }}">

    {{-- Header --}}
    <div class="flex items-center justify-between px-4 py-3 {{ $hasActive
        ? 'bg-red-50 dark:bg-red-900/20'
        : 'bg-gray-50 dark:bg-gray-900/40' }}">
        <div class="flex items-center gap-2">
            @if($hasActive)
            <span class="dt-badge dt-badge-red animate-pulse">
                ⚠ PROBLEMA ACTIVO
            </span>
            @if($currentProblem)
            <span class="text-sm text-red-700 dark:text-red-300 italic truncate max-w-xs">
                "{{ $currentProblem->comment }}"
            </span>
            @endif
            @else
            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">
                Sin problemas activos
            </span>
            @endif
        </div>
        {{-- Botón para expandir/colapsar --}}
        <button type="button" onclick="document.getElementById('problem-panel').classList.toggle('hidden')" class="text-xs font-medium px-3 py-1.5 rounded
                {{ $hasActive
                    ? 'text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/40'
                    : 'text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}
                transition">
            {{ $hasActive ? '📋 Ver historial / Resolver' : '+ Reportar problema' }}
        </button>
    </div>

    {{-- Panel expandible --}}
    <div id="problem-panel" class="hidden">

        {{-- Historial de problemas --}}
        @php $problems = $model->problems()->with('user:id,name')->take(10)->get(); @endphp
        @if($problems->count() > 0)
        <div class="border-b border-gray-200 dark:border-gray-700 px-4 py-3 bg-white dark:bg-gray-800">
            <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Historial
            </h4>
            <div class="space-y-2 max-h-48 overflow-y-auto pr-1">
                @foreach($problems as $entry)
                <div class="flex items-start gap-2 text-sm">
                    <span
                        class="mt-0.5 inline-block w-2 h-2 rounded-full flex-shrink-0 {{ $entry->is_active ? 'bg-red-500' : 'bg-green-500' }}"></span>
                    <div>
                        <p class="text-gray-800 dark:text-gray-200">{{ $entry->comment }}</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500">
                            {{ $entry->user?->name ?? 'Sistema' }} · {{ $entry->created_at->format('d/m/Y H:i') }}
                            · <span class="{{ $entry->is_active ? 'text-red-500' : 'text-green-500' }} font-semibold">
                                {{ $entry->is_active ? 'Abierto' : 'Resuelto' }}
                            </span>
                        </p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Formulario para nuevo registro --}}
        <form id="problem-form" class="px-4 py-4 bg-white dark:bg-gray-800 space-y-3">
            @csrf

            <div class="flex gap-3">
                <label class="flex items-center gap-1.5 cursor-pointer">
                    <input type="radio" name="is_active" value="1" {{ (!$hasActive || true) ? 'checked' : '' }}
                        class="w-4 h-4 text-red-600">
                    <span class="text-sm font-medium text-red-600 dark:text-red-400">⚠ Reportar problema</span>
                </label>
                @if($hasActive)
                <label class="flex items-center gap-1.5 cursor-pointer">
                    <input type="radio" name="is_active" value="0" class="w-4 h-4 text-green-600">
                    <span class="text-sm font-medium text-green-600 dark:text-green-400">✓ Marcar como resuelto</span>
                </label>
                @endif
            </div>

            <textarea name="comment" rows="2" placeholder="Describí el problema o su resolución..."
                class="w-full px-3 py-2 text-sm rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 resize-none"
                required minlength="5" maxlength="1000"></textarea>

            <div class="flex justify-end">
                <button type="submit"
                    class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-600 hover:bg-gray-700 text-white text-sm font-semibold rounded-lg transition">
                    Guardar registro
                </button>
            </div>
        </form>
    </div>
</div>