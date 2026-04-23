<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;
use App\Models\Company;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RolesAndAdminSeeder extends Seeder
{
    public function run(): void
    {
        // Crear roles si no existen
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $operatorRole = Role::firstOrCreate(['name' => 'operador']);
        $supervisorRole = Role::firstOrCreate(['name' => 'supervisor']);

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


        // Crear usuario supervisor
        $supervisorUser = User::firstOrCreate(
        ['email' => 'supervisor@supervisor.com'],
        [
            'name' => 'Supervisor',
            'password' => Hash::make('password'), // cambiar después
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
        ]
        );

        // Crear usuario operador
        $operatorUser = User::firstOrCreate(
        ['email' => 'operador@operador.com'],
        [
            'name' => 'Operador',
            'password' => Hash::make('password'), // cambiar después
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
        ]
        );


        // Asignarle rol de admin
        if (!$adminUser->hasRole('admin')) {
            $adminUser->assignRole($adminRole);
        }
        // Asignarle rol de supervisor
        if (!$supervisorUser->hasRole('supervisor')) {
            $supervisorUser->assignRole($supervisorRole);
        }

        // Asignarle rol de operador
        if (!$operatorUser->hasRole('operador')) {
            $operatorUser->assignRole($operatorRole);
        }


        // Asignarle la empresa al admin si no la tiene
        $companies = Company::all();
        foreach ($companies as $company) {
            if (!$adminUser->companies()->where('company_id', $company->id)->exists()) {
                $adminUser->companies()->attach($company->id);
            }

        }

        // Asignarle la empresa al supervisor si no la tiene
        if (!$supervisorUser->companies()->where('company_id', $company->id)->exists()) {
            $supervisorUser->companies()->attach($company->id);
        }

        // Asignarle la empresa al operador si no la tiene
        if (!$operatorUser->companies()->where('company_id', $company->id)->exists()) {
            $operatorUser->companies()->attach($company->id);
        }

        // Asignar primera sucursal al operador
        $branch = \App\Models\Branch::where('company_id', $company->id)->first();
        if ($branch && !$operatorUser->branches()->where('branch_id', $branch->id)->exists()) {
            $operatorUser->branches()->attach($branch->id);
        }

        
        // El supervisor tiene ambas sucursales
        $branches = \App\Models\Branch::where('company_id', $company->id)->get();
        foreach ($branches as $b) {
            if (!$supervisorUser->branches()->where('branch_id', $b->id)->exists()) {
                $supervisorUser->branches()->attach($b->id);
            }
        }
        // El Administrador tiene ambas sucursales
        $branches = \App\Models\Branch::where('company_id', $company->id)->get();
        foreach ($branches as $b) {
            if (!$adminUser->branches()->where('branch_id', $b->id)->exists()) {
                $adminUser->branches()->attach($b->id);
            }
        }
    }
}