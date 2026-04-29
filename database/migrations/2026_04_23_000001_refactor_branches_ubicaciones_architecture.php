<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Refactor arquitectónico: Sucursales como puntos operativos globales,
 * Ubicaciones como zonas tarifarias vinculadas a sucursales.
 *
 * Cambios:
 * 1. Ubicaciones reciben branch_id (la sucursal a la que pertenecen)
 * 2. Se crea pivot branch_company para numeración por empresa+sucursal
 * 3. Se consolidan 4 branches en 2 (independientes de empresa)
 * 4. Se normalizan tariff_tables (strings → FK a ubicaciones)
 * 5. Routes/Dispatches origin_id/destination_id cambian de ubicaciones → branches
 * 6. Deliveries location_id cambia de ubicaciones → branches
 * 7. Se eliminan company_id/branch_id de routes, dispatches, deliveries
 */
return new class extends Migration
{
    public function up(): void
    {
        // ─── PASO 1: Agregar branch_id a ubicaciones ───
        Schema::table('ubicaciones', function (Blueprint $table) {
            $table->unsignedBigInteger('branch_id')->nullable()->after('nombre');
        });

        // Poblar branch_id basado en los datos actuales
        // Buenos Aires (id=1) y Buenos Aires Cap. Fed (id=5) → Branch 1 (Suc BA)
        // Mendoza (id=4), Mendoza Este (id=2), Mendoza Sur (id=3) → Branch 2 (Suc Mendoza)
        DB::table('ubicaciones')->where('id', 1)->update(['branch_id' => 1]);
        DB::table('ubicaciones')->where('id', 5)->update(['branch_id' => 1]);
        DB::table('ubicaciones')->where('id', 4)->update(['branch_id' => 2]);
        DB::table('ubicaciones')->where('id', 2)->update(['branch_id' => 2]);
        DB::table('ubicaciones')->where('id', 3)->update(['branch_id' => 2]);

        // Agregar FK después de poblar
        Schema::table('ubicaciones', function (Blueprint $table) {
            $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
        });

        // ─── PASO 2: Crear tabla pivot branch_company ───
        Schema::create('branch_company', function (Blueprint $table) {
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('last_shipment_number')->default(0);
            $table->primary(['branch_id', 'company_id']);
        });

        // Poblar pivot con datos actuales de last_shipment_number
        // Branch 1 (company 1) → company_id=1, branch_id=1
        // Branch 2 (company 1) → company_id=1, branch_id=2
        // Branch 3 (company 2) → company_id=2, branch_id=1 (se fusiona con branch 1)
        // Branch 4 (company 2) → company_id=2, branch_id=2 (se fusiona con branch 2)
        $branches = DB::table('branches')->get();
        foreach ($branches as $branch) {
            $targetBranchId = $branch->code; // code 1→branch 1, code 2→branch 2
            DB::table('branch_company')->insert([
                'branch_id'            => $targetBranchId,
                'company_id'           => $branch->company_id,
                'last_shipment_number' => $branch->last_shipment_number,
            ]);
        }

        // ─── PASO 3: Consolidar branches (4→2) ───
        // Actualizar todas las referencias de branch 3→1 y 4→2

        // Shipments
        DB::table('shipments')->where('branch_id', 3)->update(['branch_id' => 1]);
        DB::table('shipments')->where('branch_id', 4)->update(['branch_id' => 2]);

        // Transport routes
        DB::table('transport_routes')->where('branch_id', 3)->update(['branch_id' => 1]);
        DB::table('transport_routes')->where('branch_id', 4)->update(['branch_id' => 2]);

        // Dispatches
        DB::table('dispatches')->where('branch_id', 3)->update(['branch_id' => 1]);
        DB::table('dispatches')->where('branch_id', 4)->update(['branch_id' => 2]);

        // Deliveries
        DB::table('deliveries')->where('branch_id', 3)->update(['branch_id' => 1]);
        DB::table('deliveries')->where('branch_id', 4)->update(['branch_id' => 2]);

        // Branch-User pivot
        DB::table('branch_user')->where('branch_id', 3)->update(['branch_id' => 1]);
        DB::table('branch_user')->where('branch_id', 4)->update(['branch_id' => 2]);
        // Limpiar duplicados en branch_user después de la fusión
        $duplicates = DB::table('branch_user')
            ->select('user_id', 'branch_id', DB::raw('MIN(id) as keep_id'))
            ->groupBy('user_id', 'branch_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();
        foreach ($duplicates as $dup) {
            DB::table('branch_user')
                ->where('user_id', $dup->user_id)
                ->where('branch_id', $dup->branch_id)
                ->where('id', '!=', $dup->keep_id)
                ->delete();
        }

        // Renombrar branches consolidadas
        DB::table('branches')->where('id', 1)->update(['name' => 'Sucursal Buenos Aires']);
        DB::table('branches')->where('id', 2)->update(['name' => 'Sucursal Mendoza']);

        // Eliminar branches duplicadas (3 y 4)
        DB::table('branches')->whereIn('id', [3, 4])->delete();

        // Quitar company_id y last_shipment_number de branches
        Schema::table('branches', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropUnique(['company_id', 'code']);
            $table->dropColumn(['company_id', 'last_shipment_number']);
            $table->unique('code');
        });

        // ─── PASO 4: Normalizar tariff_tables (strings → FK) ───
        Schema::table('tariff_tables', function (Blueprint $table) {
            $table->unsignedBigInteger('origin_id')->nullable()->after('name');
            $table->unsignedBigInteger('destination_id')->nullable()->after('origin_id');
        });

        // Poblar FKs basado en nombres
        $tariffs = DB::table('tariff_tables')->get();
        foreach ($tariffs as $tariff) {
            $originUbicacion = DB::table('ubicaciones')
                ->whereRaw('LOWER(nombre) = LOWER(?)', [trim($tariff->origin)])
                ->first();
            $destUbicacion = DB::table('ubicaciones')
                ->whereRaw('LOWER(nombre) = LOWER(?)', [trim($tariff->destination)])
                ->first();

            // Intentar match parcial si el exacto falla
            if (!$originUbicacion) {
                $originUbicacion = DB::table('ubicaciones')
                    ->whereRaw('LOWER(nombre) LIKE ?', ['%' . strtolower(trim($tariff->origin)) . '%'])
                    ->first();
            }
            if (!$destUbicacion) {
                $destUbicacion = DB::table('ubicaciones')
                    ->whereRaw('LOWER(nombre) LIKE ?', ['%' . strtolower(trim($tariff->destination)) . '%'])
                    ->first();
            }

            if ($originUbicacion && $destUbicacion) {
                DB::table('tariff_tables')->where('id', $tariff->id)->update([
                    'origin_id'      => $originUbicacion->id,
                    'destination_id' => $destUbicacion->id,
                ]);
            }
        }

        // Agregar FKs y eliminar columnas de strings
        Schema::table('tariff_tables', function (Blueprint $table) {
            $table->foreign('origin_id')->references('id')->on('ubicaciones')->restrictOnDelete();
            $table->foreign('destination_id')->references('id')->on('ubicaciones')->restrictOnDelete();
            $table->dropIndex(['origin', 'destination']);
            $table->dropColumn(['origin', 'destination']);
        });

        // ─── PASOS 5-7 movidos a migración 000002 ───
        // Los pasos de remap de FKs en routes, dispatches y deliveries
        // se ejecutan en una migración separada para mayor robustez.

        // ─── PASO 8: Shipments - quitar CompanyScope/BranchScope (solo código) ───
        // Los campos company_id y branch_id se MANTIENEN en shipments
        // para numeración e identificación de empresa emisora.
        // La eliminación de scopes es un cambio en el modelo, no en la migración.
    }

    public function down(): void
    {
        // Este refactor es irreversible por la complejidad de la consolidación de datos.
        // Para revertir: restaurar un backup de la base de datos.
        throw new \RuntimeException(
            'Este refactor arquitectónico no es reversible automáticamente. ' .
            'Restaure un backup de la base de datos si necesita revertir.'
        );
    }
};
