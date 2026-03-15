<?php

namespace Database\Seeders;

use App\Models\Child;
use App\Models\Chore;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => Hash::make('password'),
        ]);

        $alex = Child::create([
            'name' => 'Alex',
            'pin' => '1111',
            'avatar_color' => '#3B82F6',
            'notify_morning_at' => '07:00',
            'notify_reminder_at' => '18:00',
        ]);

        $Sam = Child::create([
            'name' => 'Sam',
            'pin' => '2222',
            'avatar_color' => '#22C55E',
            'notify_morning_at' => '07:00',
            'notify_reminder_at' => '17:30',
        ]);

        $Jordan = Child::create([
            'name' => 'Jordan',
            'pin' => '3333',
            'avatar_color' => '#EC4899',
            'notify_morning_at' => '07:30',
            'notify_reminder_at' => '18:00',
        ]);

        $kitchen = Room::create(['name' => 'Kitchen', 'icon' => '🍳', 'sort_order' => 1]);
        $guestBathroom = Room::create(['name' => 'Guest Bathroom', 'icon' => '🛁', 'sort_order' => 2]);
        $livingRoom = Room::create(['name' => 'Living Room', 'icon' => '🛋️', 'sort_order' => 3]);
        $mudRoom = Room::create(['name' => 'Mud Room', 'icon' => '🥾', 'sort_order' => 4]);
        $laundryRoom = Room::create(['name' => 'Laundry Room', 'icon' => '👕', 'sort_order' => 5]);
        $yard = Room::create(['name' => 'Yard', 'icon' => '🌿', 'sort_order' => 6]);
        $alexRoom = Room::create(['name' => "Alex's Room", 'icon' => '🛏️', 'sort_order' => 7]);
        $samRoom = Room::create(['name' => "Sam's Room", 'icon' => '🛏️', 'sort_order' => 8]);
        $jordanRoom = Room::create(['name' => "Jordan's Room", 'icon' => '🛏️', 'sort_order' => 9]);

        // Kitchen chores
        Chore::create(['name' => 'Wipe counters', 'room_id' => $kitchen->id]);
        Chore::create(['name' => 'Sweep floor', 'room_id' => $kitchen->id]);
        Chore::create(['name' => 'Load dishwasher', 'room_id' => $kitchen->id]);
        Chore::create(['name' => 'Unload dishwasher', 'room_id' => $kitchen->id]);
        Chore::create(['name' => 'Take out trash', 'room_id' => $kitchen->id]);

        // Guest Bathroom chores
        Chore::create(['name' => 'Clean toilet', 'room_id' => $guestBathroom->id]);
        Chore::create(['name' => 'Wipe mirror', 'room_id' => $guestBathroom->id]);
        Chore::create(['name' => 'Sweep floor', 'room_id' => $guestBathroom->id]);

        // Living Room chores
        Chore::create(['name' => 'Vacuum', 'room_id' => $livingRoom->id, 'days_of_week' => ['saturday']]);
        Chore::create(['name' => 'Pick up toys', 'room_id' => $livingRoom->id]);
        Chore::create(['name' => 'Dust shelves', 'room_id' => $livingRoom->id]);

        // Mud Room chores
        Chore::create(['name' => 'Organize shoes', 'room_id' => $mudRoom->id]);
        Chore::create(['name' => 'Sweep floor', 'room_id' => $mudRoom->id]);

        // Laundry Room chores
        Chore::create(['name' => 'Start laundry', 'room_id' => $laundryRoom->id]);
        Chore::create(['name' => 'Fold clothes', 'room_id' => $laundryRoom->id]);

        // Yard chores
        Chore::create([
            'name' => 'Mow the lawn',
            'room_id' => $yard->id,
            'days_of_week' => ['saturday'],
            'frequency' => 'biweekly',
            'frequency_start_date' => '2026-03-14',
        ]);
        Chore::create([
            'name' => 'Weedeat',
            'room_id' => $yard->id,
            'days_of_week' => ['saturday'],
            'frequency' => 'biweekly',
            'frequency_start_date' => '2026-03-14',
        ]);

        // Kid room chores (same chores, different rooms)
        foreach ([$alexRoom, $samRoom, $jordanRoom] as $room) {
            Chore::create(['name' => 'Make bed', 'room_id' => $room->id]);
            Chore::create(['name' => 'Pick up floor', 'room_id' => $room->id]);
            Chore::create(['name' => 'Put away clothes', 'room_id' => $room->id]);
        }
    }
}
