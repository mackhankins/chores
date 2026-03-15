<?php

namespace Database\Factories;

use App\Models\RotationGroup;
use App\RotationPeriod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RotationGroup>
 */
class RotationGroupFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true).' Rotation',
            'period' => fake()->randomElement(RotationPeriod::cases()),
            'start_date' => fake()->date(),
        ];
    }
}
