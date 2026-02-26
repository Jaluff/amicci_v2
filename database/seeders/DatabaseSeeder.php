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
            UbicacionSeeder::class,
            CompanySeeder::class,
            PartySeeder::class,
            RolesAndAdminSeeder::class,
        ]);

        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Emilio Jaluff',
            'email' => 'jaluff@email.com',
            'password' => Hash::make('12312312'),
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
        ]);
    }
}