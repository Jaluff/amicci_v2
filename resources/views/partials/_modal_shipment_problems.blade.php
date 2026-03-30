<div id="shipment-problems-modal"
    class="hidden fixed inset-0 bg-black bg-opacity-50 overflow-y-auto h-full w-full z-[60] transition-opacity">
    <div
        class="relative top-10 mx-auto p-6 border w-full max-w-2xl shadow-lg rounded-xl bg-white dark:bg-gray-800 dark:border-gray-700">
        <div class="flex justify-between items-center mb-4 pb-2 border-b dark:border-gray-700">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white">Problemas de la Guía <span id="spm-guia-numero"
                    class="text-indigo-600 dark:text-indigo-400"></span></h3>
            <button type="button" class="text-gray-400 hover:text-gray-500 focus:outline-none btn-close-spm-modal">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div id="spm-history" class="mb-6 space-y-3 max-h-60 overflow-y-auto pr-2">
            {{-- Historial inyectado vía JS --}}
        </div>

        <form id="spm-form" class="space-y-4 border-t dark:border-gray-700 pt-4">
            <input type="hidden" id="spm-shipment-id">

            <div class="flex gap-4 mb-2">
                <label class="flex items-center gap-1.5 cursor-pointer">
                    <input type="radio" name="spm_active" value="1" checked
                        class="w-4 h-4 text-red-600 focus:ring-red-500">
                    <span class="text-sm font-medium text-red-600 dark:text-red-400">⚠ Reportar nuevo problema</span>
                </label>
                <label class="flex items-center gap-1.5 cursor-pointer" id="spm-resolve-radio-label"
                    style="display:none">
                    <input type="radio" name="spm_active" value="0" class="w-4 h-4 text-green-600 focus:ring-green-500">
                    <span class="text-sm font-medium text-green-600 dark:text-green-400">✓ Marcar como resuelto</span>
                </label>
            </div>

            <textarea id="spm-comment" rows="3" placeholder="Detalle la situación o la resolución aplicada..."
                class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 resize-none"
                required minlength="5" maxlength="1000"></textarea>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button"
                    class="px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 rounded transition btn-close-spm-modal">Cancelar</button>
                <button type="submit"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none transition">
                    Guardar Registro
                </button>
            </div>
        </form>
    </div>
</div>