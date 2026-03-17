<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Ubicacion;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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

        foreach ([$ghiotto, $amicci] as $company) {
            $company->branches()->firstOrCreate(
                ['code' => 1],
                ['name' => "Sucursal Buenos Aires {$company->name}", 'ubicacion_id' => $ba?->id]
            );
            $company->branches()->firstOrCreate(
                ['code' => 2],
                ['name' => "Sucursal Mendoza {$company->name}", 'ubicacion_id' => $mendoza?->id]
            );
        }
    }
}