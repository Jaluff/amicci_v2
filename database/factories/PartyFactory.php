<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Party;
use Illuminate\Database\Eloquent\Factories\Factory;

class PartyFactory extends Factory
{
    protected $model = Party::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::first()?->id ?? 1,
            'name' => $this->faker->company(),
            'document' => $this->faker->regexify('[0-9]{8,11}'),
            'document_type' => $this->faker->randomElement(['CUIT', 'DNI']),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'tax_status' => $this->faker->randomElement(['Responsable Inscripto', 'Monotributo', 'Exento']),
        ];
    }
}
