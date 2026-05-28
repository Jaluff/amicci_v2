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
        $ghiotto = Company::updateOrCreate(
            ['name' => 'Ghiotto'],
            [
                'prefix' => 'GH',
                'legal_name' => 'De María Soledad Ghiotto Fabrizio',
                'cuit' => '27-27325319-5',
                'color' => '#22b92c',
                'gross_income' => '913-507812-1',
                'establishment' => '07-5078121-01',
                'stamping_headquarters' => '01 S.C.',
                'start_of_activities' => '2008-03-01',
                'active' => 1,
                'contra_reembolso_percent' => 2.00,
            ]
        );

        $amicci = Company::updateOrCreate(
            ['name' => 'Amicci'],
            [
                'prefix' => 'AM',
                'legal_name' => 'De Transporte Amicci S.A.',
                'cuit' => '30-71150655-8',
                'color' => '#f29121',
                'gross_income' => '913-572741-0',
                'establishment' => '07-5727410-00',
                'stamping_headquarters' => '01 Sede Central',
                'start_of_activities' => '2012-03-01',
                'active' => 1,
                'contra_reembolso_percent' => 2.00,
            ]
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
                $branchBA->id => ['last_shipment_number' => 0],
                $branchMendoza->id => ['last_shipment_number' => 0],
            ]);
        }
    }
}
