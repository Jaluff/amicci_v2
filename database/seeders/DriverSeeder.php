<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Driver;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DriverSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Driver::firstOrCreate(
        ['email' => 'driver@driver.com'],
        [
            'name' => 'Conductor 1',
            'address' => 'Calle 1',
            'phone' => '12345678',
            'license_number' => 'ABC123',
            'dni' => '21123123',
            'email' => 'driver@driver.com',
        ]
        );

        Driver::firstOrCreate(
        ['email' => 'driver2@driver.com'],
        [
            'name' => 'Conductor 2',
            'address' => 'Calle 2',
            'phone' => '87654321',
            'license_number' => 'DEF456',
            'dni' => '21123124',
            'email' => 'driver2@driver.com',
        ]
        );
    }
}