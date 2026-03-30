<?php

namespace Database\Factories;

use App\Models\Delivery;
use Illuminate\Database\Eloquent\Factories\Factory;

class DeliveryFactory extends Factory
{
    protected $model = Delivery::class;

    public function definition(): array
    {
        return [
            'delivery_number' => 'REP-' . $this->faker->unique()->numberBetween(1000, 9999),
            'status' => $this->faker->randomElement(['Listo', 'En reparto', 'Finalizado']),
            'load_date' => now()->format('Y-m-d'),
        ];
    }
}
