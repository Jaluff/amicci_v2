<?php

namespace Database\Seeders;

use App\Models\Deliverer;
use Illuminate\Database\Seeder;

class DelivererSeeder extends Seeder
{
    public function run(): void
    {
        $deliverers = [
            ['name' => 'Repartidor Local 1', 'phone' => '1122334455'],
            ['name' => 'Repartidor Local 2', 'phone' => '1133445566'],
            ['name' => 'Repartidor Externo 1', 'phone' => '1144556677'],
        ];

        foreach ($deliverers as $d) {
            Deliverer::updateOrCreate(['name' => $d['name']], $d);
        }
    }
}
