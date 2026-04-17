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
            'monthly_expenses' => 200.00,
        ]);

        $sam = Child::create([
            'name' => 'Sam',
            'pin' => '2222',
            'avatar_color' => '#22C55E',
            'notify_morning_at' => '07:00',
            'notify_reminder_at' => '17:30',
            'monthly_expenses' => 150.00,
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
        $wipeCounters = Chore::create(['name' => 'Wipe counters', 'room_id' => $kitchen->id, 'value' => 1.00, 'description' => 'Use the spray cleaner under the sink. Wipe down all counters, the stove top, and around the sink. Rinse the sponge when done.']);
        $sweepKitchen = Chore::create(['name' => 'Sweep floor', 'room_id' => $kitchen->id, 'value' => 1.00]);
        $loadDishwasher = Chore::create(['name' => 'Load dishwasher', 'room_id' => $kitchen->id, 'value' => 1.50, 'description' => 'Rinse food off plates first. Big stuff on the bottom, cups and bowls on top. Add a detergent pod and start it.']);
        $unloadDishwasher = Chore::create(['name' => 'Unload dishwasher', 'room_id' => $kitchen->id, 'value' => 1.50]);
        $trash = Chore::create(['name' => 'Take out trash', 'room_id' => $kitchen->id, 'value' => 1.00, 'description' => 'Tie the bag, take it to the bin outside, and put a fresh bag in the can.']);
        Chore::create(['name' => 'Wipe down the inside of the refrigerator', 'room_id' => $kitchen->id, 'value' => 2.50, 'description' => 'Take out anything expired, wipe each shelf with a damp cloth, and put everything back.']);

        // Guest Bathroom (daily)
        $cleanToilet = Chore::create(['name' => 'Clean toilet', 'room_id' => $guestBathroom->id, 'value' => 2.00, 'description' => 'Use the toilet brush and blue cleaner inside the bowl. Wipe the seat and lid with a disinfectant wipe.']);
        $wipeMirror = Chore::create(['name' => 'Wipe mirror', 'room_id' => $guestBathroom->id, 'value' => 0.75]);
        Chore::create(['name' => 'Sweep floor', 'room_id' => $guestBathroom->id, 'value' => 1.00]);

        // Living Room
        Chore::create(['name' => 'Vacuum', 'room_id' => $livingRoom->id, 'days_of_week' => ['saturday'], 'value' => 2.00]);
        Chore::create(['name' => 'Pick up toys', 'room_id' => $livingRoom->id, 'value' => 0.75]);
        Chore::create(['name' => 'Dust shelves', 'room_id' => $livingRoom->id, 'value' => 1.00]);

        // Mud Room (daily)
        Chore::create(['name' => 'Organize shoes', 'room_id' => $mudRoom->id, 'value' => 0.75]);
        Chore::create(['name' => 'Sweep floor', 'room_id' => $mudRoom->id, 'value' => 1.00]);

        // Laundry Room (daily)
        Chore::create(['name' => 'Start laundry', 'room_id' => $laundryRoom->id, 'value' => 1.00]);
        Chore::create(['name' => 'Fold clothes', 'room_id' => $laundryRoom->id, 'value' => 1.50]);

        // Yard (biweekly Saturdays, carryover eligible)
        Chore::create([
            'name' => 'Mow the lawn',
            'room_id' => $yard->id,
            'days_of_week' => ['saturday'],
            'frequency' => 'biweekly',
            'frequency_start_date' => '2026-03-14',
            'is_carryover_eligible' => true,
            'value' => 5.00,
        ]);
        Chore::create([
            'name' => 'Weedeat',
            'room_id' => $yard->id,
            'days_of_week' => ['saturday'],
            'frequency' => 'biweekly',
            'frequency_start_date' => '2026-03-14',
            'is_carryover_eligible' => true,
            'value' => 3.00,
        ]);

        // Kid room chores (daily, per-child)
        foreach ([$alexRoom, $samRoom, $jordanRoom] as $room) {
            Chore::create(['name' => 'Make bed', 'room_id' => $room->id, 'value' => 0.50, 'description' => 'Pull the sheets smooth, fluff the pillows, and lay the comforter flat.']);
            Chore::create(['name' => 'Pick up floor', 'room_id' => $room->id, 'value' => 0.50]);
            Chore::create(['name' => 'Put away clothes', 'room_id' => $room->id, 'value' => 0.50]);
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
                'earned_amount' => $chore->value,
            ]);
        }
    }
}
