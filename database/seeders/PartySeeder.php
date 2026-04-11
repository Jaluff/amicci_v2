<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Party;
use App\Models\Scopes\CompanyScope;
use Illuminate\Database\Seeder;

class PartySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $company = Company::first();
        if (! $company) {
            $this->command->warn('No hay empresa. Ejecutá CompanySeeder primero.');
            return;
        }

        $parties = [
            [
                'name' => 'Distribuidora Cuyo S.A.',
                'phone' => '261 456-7890',
                'phone_secondary' => '261 456-7891',
                'email' => 'ventas@distribuidoracuyo.com.ar',
                'document' => '30-71234567-8',
                'document_type' => 'CUIT',
                'tax_status' => 'Responsable Inscripto',
            ],
            [
                'name' => 'Juan Pérez',
                'phone' => '261 555-1234',
                'phone_secondary' => null,
                'email' => 'juan.perez@email.com',
                'document' => '28.456.789',
                'document_type' => 'DNI',
                'tax_status' => 'Consumidor Final',
            ],
            [
                'name' => 'Bodega Los Andes',
                'phone' => '261 498-2000',
                'phone_secondary' => '261 498-2001',
                'email' => 'logistica@bodegalosandes.com',
                'document' => '30-70123456-9',
                'document_type' => 'CUIT',
                'tax_status' => 'Responsable Inscripto',
            ],
            [
                'name' => 'María González',
                'phone' => '264 423-5678',
                'phone_secondary' => null,
                'email' => 'maria.gonzalez@gmail.com',
                'document' => '27.123.456',
                'document_type' => 'DNI',
                'tax_status' => 'Monotributo',
            ],
            [
                'name' => 'Comercial San Luis S.R.L.',
                'phone' => '266 452-1000',
                'phone_secondary' => '266 452-1001',
                'email' => 'pedidos@comercialsanluis.com.ar',
                'document' => '30-70987654-3',
                'document_type' => 'CUIT',
                'tax_status' => 'Responsable Inscripto',
            ],
            [
                'name' => 'Depósito Buenos Aires',
                'phone' => '11 4862-5555',
                'phone_secondary' => '11 4862-5556',
                'email' => 'recepcion@depositoba.com.ar',
                'document' => '30-71234567-1',
                'document_type' => 'CUIT',
                'tax_status' => 'Responsable Inscripto',
            ],
        ];

        foreach ($parties as $data) {
            Party::withoutGlobalScope(CompanyScope::class)->firstOrCreate(
                [
                    'company_id' => $company->id,
                    'name' => $data['name'],
                ],
                array_merge($data, ['company_id' => $company->id])
            );
        }
    }
}
