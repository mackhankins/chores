<?php

namespace Database\Factories;

use App\Models\Child;
use App\Models\Expense;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Expense>
 */
class ExpenseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'child_id' => Child::factory(),
            'amount' => fake()->randomFloat(2, 50, 250),
            'paid_date' => fake()->date(),
        ];
    }
}
