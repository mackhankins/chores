<?php

namespace Database\Factories;

use App\Enums\Carrier;
use App\Models\Child;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Child>
 */
class ChildFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->firstName(),
            'phone' => fake()->optional()->e164PhoneNumber(),
            'carrier' => fake()->optional()->randomElement(Carrier::cases()),
            'pin' => fake()->numerify('####'),
            'avatar_color' => fake()->randomElement([
                '#EF4444', '#F97316', '#EAB308', '#22C55E',
                '#3B82F6', '#8B5CF6', '#EC4899', '#14B8A6',
            ]),
            'notify_morning_at' => null,
            'notify_reminder_at' => null,
        ];
    }
}
