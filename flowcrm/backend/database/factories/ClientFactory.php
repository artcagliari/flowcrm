<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClientFactory extends Factory
{
    public function definition(): array
    {
        return ['company_id' => Company::factory(), 'name' => fake()->company(), 'email' => fake()->safeEmail(), 'phone' => fake()->phoneNumber(), 'city' => fake()->city(), 'origin' => fake()->randomElement(['Google', 'Instagram', 'Indicação']), 'status' => 'ativo'];
    }
}
