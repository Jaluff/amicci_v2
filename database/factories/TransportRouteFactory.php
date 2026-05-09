<?php

namespace Database\Factories;

use App\Models\TransportRoute;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransportRouteFactory extends Factory
{
    protected $model = TransportRoute::class;

    public function definition(): array
    {
        return [
            'route_number' => 'R-'.$this->faker->unique()->numberBetween(1000, 9999),
            'status' => $this->faker->randomElement(['Cargada', 'Entregada', 'En viaje']),
        ];
    }
}
