<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;
use App\Models\Company;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RolesAndAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear roles si no existen
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $userRole = Role::firstOrCreate(['name' => 'operador']);

        // Crear una empresa por defecto si es que no existe
        $company = Company::firstOrCreate(
        ['name' => 'Amicci'],
        [
            'prefix' => 'AM',
            'last_shipment_number' => 5000,
        ]
        );

        // Crear usuario administrador
        $adminUser = User::firstOrCreate(
        ['email' => 'admin@admin.com'],
        [
            'name' => 'Admin Sistema',
            'password' => Hash::make('password'), // cambiar después
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
        ]
        );

        // Asignarle rol de admin
        if (!$adminUser->hasRole('admin')) {
            $adminUser->assignRole($adminRole);
        }



        // Asignarle la empresa al admin si no la tiene
        if (!$adminUser->companies()->where('company_id', $company->id)->exists()) {
            $adminUser->companies()->attach($company->id);
        }
    }
}