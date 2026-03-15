<?php

namespace Database\Factories;

use App\Models\Chore;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Chore>
 */
class ChoreFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement([
                'Take out trash', 'Wipe counters', 'Sweep floor',
                'Load dishwasher', 'Clean toilet', 'Vacuum',
                'Make bed', 'Pick up toys', 'Mow lawn',
            ]),
            'description' => fake()->optional()->sentence(),
            'room_id' => Room::factory(),
            'days_of_week' => null,
            'frequency' => null,
            'frequency_start_date' => null,
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
