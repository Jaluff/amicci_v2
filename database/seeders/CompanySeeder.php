<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Ubicacion;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ghiotto = Company::firstOrCreate(
            ['name' => 'Ghiotto'],
            ['prefix' => 'GH', 'last_shipment_number' => 0]
        );

        $amicci = Company::firstOrCreate(
            ['name' => 'Amicci'],
            ['prefix' => 'AM', 'last_shipment_number' => 0]
        );

        $ba = Ubicacion::where('nombre', 'Buenos Aires')->first();
        $mendoza = Ubicacion::where('nombre', 'Mendoza')->first();

        // Crear sucursales globales si no existen
        $branchBA = Branch::firstOrCreate(
            ['code' => 1],
            ['name' => 'Sucursal Buenos Aires', 'ubicacion_id' => $ba?->id]
        );

        $branchMendoza = Branch::firstOrCreate(
            ['code' => 2],
            ['name' => 'Sucursal Mendoza', 'ubicacion_id' => $mendoza?->id]
        );

        // Vincular ubicaciones a sus sucursales (Arquitectura Refactorizada)
        Ubicacion::whereIn('nombre', ['Buenos Aires', 'Buenos Aires (Cap. Fed.)'])
            ->update(['branch_id' => $branchBA->id]);
            
        Ubicacion::whereIn('nombre', ['Mendoza', 'Mendoza Este', 'Mendoza Sur'])
            ->update(['branch_id' => $branchMendoza->id]);

        // Vincular sucursales a las empresas
        foreach ([$ghiotto, $amicci] as $company) {
            $company->branches()->syncWithoutDetaching([
                $branchBA->id      => ['last_shipment_number' => 0],
                $branchMendoza->id => ['last_shipment_number' => 0],
            ]);
        }
    }
}