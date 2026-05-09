<?php

namespace Database\Factories;

use App\Models\Shipment;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShipmentFactory extends Factory
{
    protected $model = Shipment::class;

    public function definition(): array
    {
        $flete = $this->faker->randomFloat(2, 500, 5000);
        $total = $flete * 1.21;

        return [
            'numero' => 'TMP-'.$this->faker->unique()->numberBetween(100000, 999999),
            'fecha' => $this->faker->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),
            'flete' => $flete,
            'total' => $total,
            'subtotal' => $flete,
            'iva_monto' => $total - $flete,
            'ubicacion_actual' => $this->faker->randomElement(['Dto origen', 'En transito', 'Dto destino', 'En reparto', 'Entregado']),
            'cobrada' => $this->faker->boolean(20),
            'contra_reembolso' => $this->faker->boolean(10),
        ];
    }
}
