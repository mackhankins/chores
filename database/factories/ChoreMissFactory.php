<?php

namespace Database\Factories;

use App\Models\Child;
use App\Models\Chore;
use App\Models\ChoreMiss;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChoreMiss>
 */
class ChoreMissFactory extends Factory
{
    public function definition(): array
    {
        return [
            'chore_id' => Chore::factory(),
            'child_id' => Child::factory(),
            'missed_date' => fake()->date(),
            'completed_at' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'completed_at' => now(),
        ]);
    }
}
