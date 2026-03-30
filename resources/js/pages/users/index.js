import $ from 'jquery';

$(function () {
    // Escuchar el click en btn-edit-permissions dentro de la grilla de usuarios
    $(document).on('click', '.btn-edit-permissions', function () {
        const userId = $(this).data('user-id');
        $('#permissionUserId').val(userId);

        // Limpiar contenedores
        $('#rolesContainer').empty();
        $('#permissionsContainer').empty();

        // Disable form while loading
        $(this).prop('disabled', true).text('Cargando...');
        const btn = $(this);

        // Hacer $.get
        $.get(`/users/${userId}/permissions`)
            .done(function (data) {
                // Dibujar checkboxes de Roles
                if (data.roles && data.roles.length > 0) {
                    data.roles.forEach(role => {
                        const isChecked = data.userRoles.includes(role.id) ? 'checked' : '';
                        const html = `
                            <div class="flex items-center">
                                <input type="checkbox" id="role_${role.id}" name="roles[]" value="${role.id}" ${isChecked}
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="role_${role.id}" class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">${role.name}</label>
                            </div>
                        `;
                        $('#rolesContainer').append(html);
                    });
                } else {
                    $('#rolesContainer').html('<p class="text-xs text-gray-500 dark:text-gray-400">No hay roles disponibles.</p>');
                }

                // Dibujar checkboxes de Permisos directos
                if (data.permissions && data.permissions.length > 0) {
                    data.permissions.forEach(permission => {
                        const isChecked = data.userPermissions.includes(permission.id) ? 'checked' : '';
                        const html = `
                            <div class="flex items-center">
                                <input type="checkbox" id="perm_${permission.id}" name="permissions[]" value="${permission.id}" ${isChecked}
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="perm_${permission.id}" class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">${permission.name}</label>
                            </div>
                        `;
                        $('#permissionsContainer').append(html);
                    });
                } else {
                    $('#permissionsContainer').html('<p class="text-xs text-gray-500 dark:text-gray-400">No hay permisos adicionales disponibles.</p>');
                }

                // Mostrar Modal (Tailwind CSS flowbite-like or manual)
                $('#modalPermissions').removeClass('hidden').addClass('flex');
            })
            .fail(function (xhr) {
                toastr.error('Error al cargar la información de permisos.');
            })
            .always(function () {
                btn.prop('disabled', false).text('Permisos');
            });
    });

    // Cerrar modal
    $(document).on('click', '.btn-close-modal', function () {
        $('#modalPermissions').removeClass('flex').addClass('hidden');
    });

    // Guardar permisos
    $(document).on('click', '#btnSavePermissions', function (e) {
        e.preventDefault();

        const userId = $('#permissionUserId').val();
        const btn = $(this);
        btn.prop('disabled', true).text('Guardando...');

        const formData = $('#formPermissions').serialize();

        $.ajax({
            url: `/users/${userId}/permissions`,
            type: 'PUT',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                toastr.success(response.message || 'Permisos actualizados correctamente.');
                $('#modalPermissions').removeClass('flex').addClass('hidden');

                // Recargar DataTables de usuarios si está inicializado (Users index template might just reload page or datatables via API. Wait, it is drawn with blade.)
                // As the table is populated by blade, simple refresh is better, or let user manually refresh. In the prompt: "cerrar el modal y recargar la tabla de usuarios si fuera necesario". The blade loops over $users so reloading the page is needed to see changes in "roles" column, or we can just leave it as is. Let's reload page.
                setTimeout(function () {
                    window.location.reload();
                }, 1000);
            },
            error: function (xhr) {
                const message = xhr.responseJSON?.message || 'Ocurrió un error al guardar los permisos.';
                toastr.error(message);
            },
            complete: function () {
                btn.prop('disabled', false).text('Guardar');
            }
        });
    });
});
