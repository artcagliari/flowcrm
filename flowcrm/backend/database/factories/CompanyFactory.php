<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CompanyFactory extends Factory
{
    public function definition(): array
    {
        return ['name' => fake()->company(), 'email' => fake()->companyEmail(), 'phone' => fake()->phoneNumber(), 'primary_color' => '#4F8CFF'];
    }
}
