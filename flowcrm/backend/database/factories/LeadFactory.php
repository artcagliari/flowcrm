<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeadFactory extends Factory
{
    public function definition(): array
    {
        return ['company_id' => Company::factory(), 'name' => fake()->name(), 'email' => fake()->safeEmail(), 'phone' => fake()->phoneNumber(), 'origin' => fake()->randomElement(['Landing Page', 'Instagram', 'Indicação']), 'temperature' => fake()->randomElement(['frio', 'morno', 'quente']), 'status' => 'novo', 'estimated_value' => fake()->numberBetween(1000, 25000)];
    }
}
