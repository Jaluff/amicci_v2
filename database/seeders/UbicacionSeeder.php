<?php

namespace Database\Seeders;

use App\Models\Ubicacion;
use Illuminate\Database\Seeder;

class UbicacionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ubicaciones = [
            'Buenos Aires',
            'Mendoza Este',
            'Mendoza Sur',
            'Mendoza',
        ];

        foreach ($ubicaciones as $nombre) {
            Ubicacion::firstOrCreate(['nombre' => $nombre]);
        }
    }
}
