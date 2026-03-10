<!-- Modal Modificar Permisos -->
<div id="modalPermissions" tabindex="-1" aria-hidden="true"
    class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full bg-gray-900 bg-opacity-50">
    <div class="relative p-4 w-full max-w-2xl max-h-full m-auto mt-10">
        <!-- Modal content -->
        <div class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700">
            <!-- Modal header -->
            <div
                class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600 border-gray-200">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                    Modificar Roles y Permisos
                </h3>
                <button type="button"
                    class="btn-close-modal text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                    </svg>
                    <span class="sr-only">Cerrar modal</span>
                </button>
            </div>
            <!-- Modal body -->
            <div class="p-4 md:p-5 space-y-4">
                <form id="formPermissions">
                    @csrf
                    <input type="hidden" id="permissionUserId" name="user_id" value="">

                    <div class="mb-4">
                        <h4
                            class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2 border-b dark:border-gray-600 pb-1">
                            Roles</h3>
                            <div id="rolesContainer"
                                class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm text-gray-700 dark:text-gray-300">
                                <!-- JS will populate checkboxes here -->
                            </div>
                    </div>

                    <div>
                        <h4
                            class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2 border-b dark:border-gray-600 pb-1">
                            Permisos Especiales</h3>
                            <div id="permissionsContainer"
                                class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm text-gray-700 dark:text-gray-300">
                                <!-- JS will populate checkboxes here -->
                            </div>
                    </div>
                </form>
            </div>
            <!-- Modal footer -->
            <div
                class="flex items-center justify-end p-4 md:p-5 border-t border-gray-200 rounded-b dark:border-gray-600">
                <button type="button"
                    class="btn-close-modal py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700 mr-2">
                    Cancelar
                </button>
                <button type="button" id="btnSavePermissions"
                    class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                    Guardar
                </button>
            </div>
        </div>
    </div>
</div>