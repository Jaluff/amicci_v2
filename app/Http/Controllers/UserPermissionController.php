<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Http\Requests\UpdateUserPermissionsRequest;

class UserPermissionController extends Controller
{
    /**
     * Get available and assigned roles and permissions for a user.
     */
    public function show(User $user): JsonResponse
    {
        // Available roles and permissions
        $roles = Role::get(['id', 'name']);
        $permissions = Permission::get(['id', 'name']);

        // Assigned roles and permissions
        $userRoles = $user->roles()->pluck('id');
        $userPermissions = $user->permissions()->pluck('id');

        return response()->json([
            'roles' => $roles,
            'permissions' => $permissions,
            'userRoles' => $userRoles,
            'userPermissions' => $userPermissions,
        ]);
    }

    /**
     * Update the assigned roles and permissions for a user.
     */
    public function update(UpdateUserPermissionsRequest $request, User $user): JsonResponse
    {
        $user->syncRoles($request->validated('roles', []));
        $user->syncPermissions($request->validated('permissions', []));

        return response()->json([
            'success' => true,
            'message' => 'Permisos y roles actualizados correctamente.'
        ]);
    }
}