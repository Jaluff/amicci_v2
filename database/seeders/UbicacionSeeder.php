<?php

namespace Database\Seeders;

use App\Models\Ubicacion;
use Illuminate\Database\Seeder;

class UbicacionSeeder extends Seeder
{
    /**
     * Ubicaciones para origen y destino en guías, rutas y despachos.
     */
    public function run(): void
    {
        $ubicaciones = [
            'Buenos Aires',
            'Mendoza',
            'San Juan',
            'San Luis',
        ];

        foreach ($ubicaciones as $nombre) {
            Ubicacion::firstOrCreate(['nombre' => $nombre]);
        }
    }
}
