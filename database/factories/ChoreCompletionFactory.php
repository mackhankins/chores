<?php

namespace Database\Factories;

use App\Models\Child;
use App\Models\Chore;
use App\Models\ChoreCompletion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChoreCompletion>
 */
class ChoreCompletionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'chore_id' => Chore::factory(),
            'child_id' => Child::factory(),
            'completed_date' => fake()->date(),
        ];
    }
}
