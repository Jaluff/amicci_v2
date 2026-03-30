<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UbicacionSeeder::class ,
            CompanySeeder::class ,
            RolesAndAdminSeeder::class ,
            DriverSeeder::class ,
            DelivererSeeder::class, // Asegurar que exista
            BigDataSeeder::class , // El nuevo seeder masivo
            TariffTableSeeder::class, // Cuadros tarifarios y tramos de peso
        ]);

    }
}