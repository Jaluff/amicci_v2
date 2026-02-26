<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //// Crear una empresa por defecto si es que no existe
        $company = Company::firstOrCreate(
        ['name' => 'Ghiotto'],
        [
            'prefix' => 'GH',
            'last_shipment_number' => 5000,
        ]
        );
    }
}