<?php

namespace Database\Seeders;

use App\Models\Child;
use App\Models\Chore;
use App\Models\ChoreAssignment;
use App\Models\ChoreCompletion;
use App\Models\Room;
use App\Models\RotationGroup;
use App\Models\RotationGroupMember;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Admin ──────────────────────────────────────────────
        User::create([
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => Hash::make('password'),
        ]);

        // ── Children ───────────────────────────────────────────
        $alex = Child::create([
            'name' => 'Alex',
            'pin' => '1111',
            'avatar_color' => '#3B82F6',
            'notify_morning_at' => '07:00',
            'notify_reminder_at' => '18:00',
        ]);

        $sam = Child::create([
            'name' => 'Sam',
            'pin' => '2222',
            'avatar_color' => '#22C55E',
            'notify_morning_at' => '07:00',
            'notify_reminder_at' => '17:30',
        ]);

        $jordan = Child::create([
            'name' => 'Jordan',
            'pin' => '3333',
            'avatar_color' => '#EC4899',
            'notify_morning_at' => '07:30',
            'notify_reminder_at' => '18:00',
        ]);

        // ── Rooms ──────────────────────────────────────────────
        $kitchen = Room::create(['name' => 'Kitchen', 'icon' => '🍳', 'sort_order' => 1]);
        $guestBathroom = Room::create(['name' => 'Guest Bathroom', 'icon' => '🛁', 'sort_order' => 2]);
        $livingRoom = Room::create(['name' => 'Living Room', 'icon' => '🛋️', 'sort_order' => 3]);
        $mudRoom = Room::create(['name' => 'Mud Room', 'icon' => '🥾', 'sort_order' => 4]);
        $laundryRoom = Room::create(['name' => 'Laundry Room', 'icon' => '👕', 'sort_order' => 5]);
        $yard = Room::create(['name' => 'Yard', 'icon' => '🌿', 'sort_order' => 6]);
        $alexRoom = Room::create(['name' => "Alex's Room", 'icon' => '🛏️', 'sort_order' => 7]);
        $samRoom = Room::create(['name' => "Sam's Room", 'icon' => '🛏️', 'sort_order' => 8]);
        $jordanRoom = Room::create(['name' => "Jordan's Room", 'icon' => '🛏️', 'sort_order' => 9]);

        // ── Chores ─────────────────────────────────────────────
        // Kitchen (daily)
        $wipeCounters = Chore::create(['name' => 'Wipe counters', 'room_id' => $kitchen->id]);
        $sweepKitchen = Chore::create(['name' => 'Sweep floor', 'room_id' => $kitchen->id]);
        $loadDishwasher = Chore::create(['name' => 'Load dishwasher', 'room_id' => $kitchen->id]);
        $unloadDishwasher = Chore::create(['name' => 'Unload dishwasher', 'room_id' => $kitchen->id]);
        $trash = Chore::create(['name' => 'Take out trash', 'room_id' => $kitchen->id]);

        // Guest Bathroom (daily)
        $cleanToilet = Chore::create(['name' => 'Clean toilet', 'room_id' => $guestBathroom->id]);
        $wipeMirror = Chore::create(['name' => 'Wipe mirror', 'room_id' => $guestBathroom->id]);
        Chore::create(['name' => 'Sweep floor', 'room_id' => $guestBathroom->id]);

        // Living Room
        Chore::create(['name' => 'Vacuum', 'room_id' => $livingRoom->id, 'days_of_week' => ['saturday']]);
        Chore::create(['name' => 'Pick up toys', 'room_id' => $livingRoom->id]);
        Chore::create(['name' => 'Dust shelves', 'room_id' => $livingRoom->id]);

        // Mud Room (daily)
        Chore::create(['name' => 'Organize shoes', 'room_id' => $mudRoom->id]);
        Chore::create(['name' => 'Sweep floor', 'room_id' => $mudRoom->id]);

        // Laundry Room (daily)
        Chore::create(['name' => 'Start laundry', 'room_id' => $laundryRoom->id]);
        Chore::create(['name' => 'Fold clothes', 'room_id' => $laundryRoom->id]);

        // Yard (biweekly Saturdays, carryover eligible)
        Chore::create([
            'name' => 'Mow the lawn',
            'room_id' => $yard->id,
            'days_of_week' => ['saturday'],
            'frequency' => 'biweekly',
            'frequency_start_date' => '2026-03-14',
            'is_carryover_eligible' => true,
        ]);
        Chore::create([
            'name' => 'Weedeat',
            'room_id' => $yard->id,
            'days_of_week' => ['saturday'],
            'frequency' => 'biweekly',
            'frequency_start_date' => '2026-03-14',
            'is_carryover_eligible' => true,
        ]);

        // Kid room chores (daily, per-child)
        foreach ([$alexRoom, $samRoom, $jordanRoom] as $room) {
            Chore::create(['name' => 'Make bed', 'room_id' => $room->id]);
            Chore::create(['name' => 'Pick up floor', 'room_id' => $room->id]);
            Chore::create(['name' => 'Put away clothes', 'room_id' => $room->id]);
        }

        // ── Rotation Groups ────────────────────────────────────
        $dailyRotation = RotationGroup::create([
            'name' => 'Daily Rotation',
            'period' => 'daily',
            'start_date' => today(),
        ]);
        RotationGroupMember::create(['rotation_group_id' => $dailyRotation->id, 'child_id' => $alex->id, 'position' => 0]);
        RotationGroupMember::create(['rotation_group_id' => $dailyRotation->id, 'child_id' => $sam->id, 'position' => 1]);
        RotationGroupMember::create(['rotation_group_id' => $dailyRotation->id, 'child_id' => $jordan->id, 'position' => 2]);

        $weeklyRotation = RotationGroup::create([
            'name' => 'Weekly Rotation',
            'period' => 'weekly',
            'start_date' => today()->startOfWeek(),
        ]);
        RotationGroupMember::create(['rotation_group_id' => $weeklyRotation->id, 'child_id' => $alex->id, 'position' => 0]);
        RotationGroupMember::create(['rotation_group_id' => $weeklyRotation->id, 'child_id' => $sam->id, 'position' => 1]);
        RotationGroupMember::create(['rotation_group_id' => $weeklyRotation->id, 'child_id' => $jordan->id, 'position' => 2]);

        // ── Assignments ────────────────────────────────────────

        // Kid rooms → fixed to each child (room-level)
        ChoreAssignment::create(['room_id' => $alexRoom->id, 'child_id' => $alex->id]);
        ChoreAssignment::create(['room_id' => $samRoom->id, 'child_id' => $sam->id]);
        ChoreAssignment::create(['room_id' => $jordanRoom->id, 'child_id' => $jordan->id]);

        // Kitchen → daily rotation (room-level)
        ChoreAssignment::create(['room_id' => $kitchen->id, 'rotation_group_id' => $dailyRotation->id]);

        // Guest Bathroom → weekly rotation (room-level)
        ChoreAssignment::create(['room_id' => $guestBathroom->id, 'rotation_group_id' => $weeklyRotation->id]);

        // Living Room → daily rotation (room-level)
        ChoreAssignment::create(['room_id' => $livingRoom->id, 'rotation_group_id' => $dailyRotation->id]);

        // Mud Room → fixed to Jordan
        ChoreAssignment::create(['room_id' => $mudRoom->id, 'child_id' => $jordan->id]);

        // Laundry Room → fixed to Sam
        ChoreAssignment::create(['room_id' => $laundryRoom->id, 'child_id' => $sam->id]);

        // Yard chores → weekly rotation (individual chores)
        $yardChores = Chore::where('room_id', $yard->id)->get();
        foreach ($yardChores as $chore) {
            ChoreAssignment::create(['chore_id' => $chore->id, 'rotation_group_id' => $weeklyRotation->id]);
        }

        // Specific overrides: trash is always Alex's job
        ChoreAssignment::create(['chore_id' => $trash->id, 'child_id' => $alex->id]);

        // ── Sample completions (today) ─────────────────────────
        // Alex already did some chores today
        $alexRoomChores = Chore::where('room_id', $alexRoom->id)->take(2)->get();
        foreach ($alexRoomChores as $chore) {
            ChoreCompletion::create([
                'chore_id' => $chore->id,
                'child_id' => $alex->id,
                'completed_date' => today(),
            ]);
        }
    }
}
