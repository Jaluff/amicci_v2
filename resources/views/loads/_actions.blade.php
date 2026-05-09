<div class="flex items-center justify-center gap-2">
    {{-- Edit --}}
    <a href="{{ route('loads.edit', $load) }}" title="Editar" class="inline-flex items-center justify-center p-2 rounded-md bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100 dark:bg-blue-900/40 dark:text-blue-400 dark:border-blue-800 dark:hover:bg-blue-800/60 dark:hover:text-blue-300 transition-colors">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
    </a>

    {{-- State Machine --}}
    @if($load->estado === 'Preparado')
    <button type="button" class="btn-change-state inline-flex items-center gap-1 px-2.5 py-1.5 mb-1 sm:mb-0 rounded-md font-bold text-xs shadow-sm transition-all bg-yellow-500 hover:bg-yellow-600 text-white" data-id="{{ $load->id }}" data-status="En viaje" title="Iniciar Viaje">
        🚛 En Viaje
    </button>
    @elseif($load->estado === 'En viaje')
    <button type="button" class="btn-change-state inline-flex items-center gap-1 px-2.5 py-1.5 mb-1 sm:mb-0 rounded-md font-bold text-xs shadow-sm transition-all bg-green-600 hover:bg-green-700 text-white" data-id="{{ $load->id }}" data-status="Arribado" title="Marcar como Arribado">
        ✅ Arribado
    </button>
    <button type="button" class="btn-change-state inline-flex items-center gap-1 px-2.5 py-1.5 mb-1 sm:mb-0 rounded-md font-bold text-xs shadow-sm transition-all bg-gray-500 hover:bg-gray-600 text-white" data-id="{{ $load->id }}" data-status="Preparado" title="Revertir a Preparado">
        ↩ Revertir
    </button>
    @endif

    {{-- Facturar --}}
    @if(!$load->facturada)
    <button type="button" class="btn-invoice inline-flex items-center justify-center p-2 rounded-md bg-purple-50 text-purple-700 border border-purple-200 hover:bg-purple-100 dark:bg-purple-900/40 dark:text-purple-400 dark:border-purple-800 dark:hover:bg-purple-800/60 dark:hover:text-purple-300 transition-colors"
        data-id="{{ $load->id }}"
        data-numero="{{ $load->numero }}"
        data-importe="{{ $load->importe_factura ?? '' }}"
        title="Facturar">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
    </button>
    @endif

    {{-- Cobrar --}}
    @if($load->facturada && !$load->cobrada)
    <button type="button" class="btn-pay inline-flex items-center justify-center p-2 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 dark:bg-emerald-900/40 dark:text-emerald-400 dark:border-emerald-800 dark:hover:bg-emerald-800/60 dark:hover:text-emerald-300 transition-colors" data-id="{{ $load->id }}" data-numero="{{ $load->numero }}" title="Cobrar">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
    </button>
    @endif

    {{-- Delete --}}
    <form action="{{ route('loads.destroy', $load) }}" method="POST" class="inline m-0" onsubmit="return confirm('¿Está seguro de eliminar esta carga?');">
        @csrf
        @method('DELETE')
        <button type="submit" title="Eliminar" class="inline-flex items-center justify-center p-2 rounded-md bg-red-50 text-red-700 border border-red-200 hover:bg-red-100 dark:bg-red-900/40 dark:text-red-400 dark:border-red-800 dark:hover:bg-red-800/60 dark:hover:text-red-300 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
        </button>
    </form>
</div>
