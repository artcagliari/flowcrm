<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    public function definition(): array
    {
        return ['company_id' => Company::factory(), 'title' => fake()->sentence(4), 'due_at' => fake()->dateTimeBetween('now', '+15 days'), 'priority' => fake()->randomElement(['baixa', 'media', 'alta', 'urgente']), 'status' => 'pendente'];
    }
}
