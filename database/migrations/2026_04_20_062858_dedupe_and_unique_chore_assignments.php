<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            $this->dedupe('chore_id');
            $this->dedupe('room_id');
        });

        Schema::table('chore_assignments', function (Blueprint $table) {
            $table->unique(['chore_id', 'child_id'], 'chore_assignments_chore_child_unique');
            $table->unique(['chore_id', 'rotation_group_id'], 'chore_assignments_chore_rotation_unique');
            $table->unique(['room_id', 'child_id'], 'chore_assignments_room_child_unique');
            $table->unique(['room_id', 'rotation_group_id'], 'chore_assignments_room_rotation_unique');
        });
    }

    public function down(): void
    {
        Schema::table('chore_assignments', function (Blueprint $table) {
            $table->dropUnique('chore_assignments_chore_child_unique');
            $table->dropUnique('chore_assignments_chore_rotation_unique');
            $table->dropUnique('chore_assignments_room_child_unique');
            $table->dropUnique('chore_assignments_room_rotation_unique');
        });
    }

    /**
     * Collapse rows that share (target, child_id, rotation_group_id) to one keeper.
     */
    protected function dedupe(string $targetColumn): void
    {
        $dupeGroups = DB::table('chore_assignments')
            ->whereNotNull($targetColumn)
            ->select($targetColumn, 'child_id', 'rotation_group_id', DB::raw('COUNT(*) as cnt'))
            ->groupBy($targetColumn, 'child_id', 'rotation_group_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($dupeGroups as $group) {
            $keeperId = DB::table('chore_assignments')
                ->where($targetColumn, $group->{$targetColumn})
                ->where(fn ($q) => $group->child_id === null
                    ? $q->whereNull('child_id')
                    : $q->where('child_id', $group->child_id))
                ->where(fn ($q) => $group->rotation_group_id === null
                    ? $q->whereNull('rotation_group_id')
                    : $q->where('rotation_group_id', $group->rotation_group_id))
                ->orderByDesc('updated_at')
                ->orderByDesc('id')
                ->value('id');

            DB::table('chore_assignments')
                ->where($targetColumn, $group->{$targetColumn})
                ->where(fn ($q) => $group->child_id === null
                    ? $q->whereNull('child_id')
                    : $q->where('child_id', $group->child_id))
                ->where(fn ($q) => $group->rotation_group_id === null
                    ? $q->whereNull('rotation_group_id')
                    : $q->where('rotation_group_id', $group->rotation_group_id))
                ->where('id', '!=', $keeperId)
                ->delete();
        }
    }
};
