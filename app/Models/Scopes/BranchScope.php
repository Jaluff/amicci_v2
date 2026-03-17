<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class BranchScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        if (app()->runningInConsole()) {
            return;
        }

        $user = auth()->user();
        if (!$user) {
            return;
        }

        // Obtener IDs de sucursales asignadas
        $branchIds = $user->branches()->pluck('branches.id')->toArray();

        if (!empty($branchIds)) {
            $builder->whereIn($model->getTable() . '.branch_id', $branchIds);
        } elseif (!$user->hasRole('admin')) {
            // No es admin y no tiene sucursales: no ve nada
            $builder->whereRaw('1 = 0');
        }
        // Si es admin y no tiene sucursales asignadas, el filtro no se aplica (ve todas)
    }
}
