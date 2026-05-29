<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        
        $exists = Illuminate\Support\Facades\DB::table('permissions')
            ->where('name', 'editar guias facturadas')
            ->where('guard_name', 'web')
            ->exists();

        if (!$exists) {
            Illuminate\Support\Facades\DB::table('permissions')->insert([
                'name' => 'editar guias facturadas',
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        Illuminate\Support\Facades\DB::table('permissions')
            ->where('name', 'editar guias facturadas')
            ->where('guard_name', 'web')
            ->delete();
    }
};
