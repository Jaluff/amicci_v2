<div id="shipments-modal"
    class="hidden fixed inset-0 bg-black bg-opacity-50 overflow-y-auto h-full w-full z-50 transition-opacity">
    <div
        class="relative top-10 mx-auto p-6 border w-full max-w-5xl shadow-lg rounded-xl bg-white dark:bg-gray-800 dark:border-gray-700">
        <div class="flex justify-between items-center mb-4 pb-2 border-b dark:border-gray-700">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white">Seleccionar Guías Disponibles</h3>
            <button type="button"
                class="text-gray-400 hover:text-gray-500 focus:outline-none btn-close-shipments-modal">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div
            class="mb-4 text-sm text-gray-600 dark:text-gray-400 bg-blue-50 dark:bg-blue-900/30 p-3 rounded-lg border border-blue-100 dark:border-blue-800">
            <strong>Nota:</strong> Solo se mostrarán las guías con estado "Dto destino" que coincidan con la ubicación
            del reparto.
        </div>

        <div class="overflow-x-auto min-h-[300px]">
            <table class="w-full text-left border-collapse display responsive" id="available-shipments-table"
                data-url="{{ route('deliveries.available-shipments') }}" style="width:100%">
                <thead class="bg-gray-100 dark:bg-gray-700">
                    <tr>
                        <th class="p-2 w-10 text-center"><input type="checkbox" id="check-all-shipments"
                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"></th>
                        <th class="p-2 font-semibold text-gray-800 dark:text-gray-200">Número</th>
                        <th class="p-2 font-semibold text-gray-800 dark:text-gray-200">Fecha</th>
                        <th class="p-2 font-semibold text-gray-800 dark:text-gray-200">Origen</th>
                        <th class="p-2 font-semibold text-gray-800 dark:text-gray-200">Destino</th>
                        <th class="p-2 font-semibold text-gray-800 dark:text-gray-200">Estado</th>
                        <th class="p-2 font-semibold text-gray-800 dark:text-gray-200 text-right">Bultos</th>
                    </tr>
                </thead>
            </table>
        </div>

        <div class="flex justify-end gap-3 mt-6 pt-4 border-t dark:border-gray-700">
            <button type="button"
                class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded transition btn-close-shipments-modal">Cancelar</button>
            <button type="button"
                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded shadow transition btn-confirm-shipments">
                Agregar Seleccionadas (<span id="selected-count">0</span>)
            </button>
        </div>
    </div>
</div>