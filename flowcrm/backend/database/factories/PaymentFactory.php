<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return ['company_id' => Company::factory(), 'description' => fake()->sentence(3), 'amount' => fake()->numberBetween(500, 9000), 'due_date' => fake()->dateTimeBetween('-10 days', '+20 days'), 'paid_at' => fake()->optional()->dateTimeBetween('-20 days', 'now'), 'payment_method' => 'Pix', 'status' => fake()->randomElement(['pago', 'pendente', 'atrasado'])];
    }
}
