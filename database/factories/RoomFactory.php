<?php

namespace Database\Factories;

use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Room>
 */
class RoomFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement([
                'Kitchen', 'Bathroom', 'Living Room', 'Bedroom',
                'Garage', 'Yard', 'Laundry Room', 'Dining Room',
            ]),
            'icon' => fake()->optional()->randomElement(['🍳', '🛁', '🛋️', '🛏️', '🚗', '🌿', '👕', '🍽️']),
            'sort_order' => fake()->numberBetween(0, 10),
        ];
    }
}
