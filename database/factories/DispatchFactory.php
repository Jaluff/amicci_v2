<?php

namespace Database\Factories;

use App\Models\Dispatch;
use Illuminate\Database\Eloquent\Factories\Factory;

class DispatchFactory extends Factory
{
    protected $model = Dispatch::class;

    public function definition(): array
    {
        return [
            'dispatch_number' => 'D-'.$this->faker->unique()->numberBetween(1000, 9999),
            'status' => $this->faker->randomElement(['Cargado', 'En viaje', 'Arribado']),
            'cost' => $this->faker->randomFloat(2, 5000, 50000),
        ];
    }
}
