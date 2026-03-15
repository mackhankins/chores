<?php

namespace Database\Factories;

use App\Models\Child;
use App\Models\Chore;
use App\Models\ChoreAssignment;
use App\Models\Room;
use App\Models\RotationGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChoreAssignment>
 */
class ChoreAssignmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'chore_id' => Chore::factory(),
            'room_id' => null,
            'child_id' => Child::factory(),
            'rotation_group_id' => null,
        ];
    }

    public function forRoom(): static
    {
        return $this->state(fn (array $attributes) => [
            'chore_id' => null,
            'room_id' => Room::factory(),
        ]);
    }

    public function rotating(): static
    {
        return $this->state(fn (array $attributes) => [
            'child_id' => null,
            'rotation_group_id' => RotationGroup::factory(),
        ]);
    }
}
