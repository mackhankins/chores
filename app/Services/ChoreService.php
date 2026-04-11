<?php

namespace App\Services;

use App\Models\Child;
use App\Models\Chore;
use App\Models\ChoreAssignment;
use Illuminate\Support\Collection;

class ChoreService
{
    public function __construct(
        protected RotationService $rotationService,
    ) {}

    /**
     * Get today's chores with their resolved assignments.
     *
     * Returns a collection of ['chore' => Chore, 'assignment' => ChoreAssignment, 'child' => ?Child]
     *
     * @return Collection<int, array{chore: Chore, assignment: ChoreAssignment, child: ?Child}>
     */
    public function getTodaysChores(): Collection
    {
        $assignments = ChoreAssignment::query()
            ->with(['chore.room', 'room.chores', 'child', 'rotationGroup.members'])
            ->get();

        $choreAssignments = $assignments->whereNotNull('chore_id');
        $roomAssignments = $assignments->whereNotNull('room_id');

        $directlyAssignedChoreIds = $choreAssignments->pluck('chore_id')->toArray();

        $results = collect();

        foreach ($choreAssignments as $assignment) {
            if (! $assignment->chore?->is_active || ! $assignment->chore->isScheduledForToday()) {
                continue;
            }

            $results->push([
                'chore' => $assignment->chore,
                'assignment' => $assignment,
                'child' => $this->resolveChild($assignment),
            ]);
        }

        foreach ($roomAssignments as $assignment) {
            if (! $assignment->room) {
                continue;
            }

            foreach ($assignment->room->chores as $chore) {
                if (! $chore->is_active || ! $chore->isScheduledForToday()) {
                    continue;
                }

                if (in_array($chore->id, $directlyAssignedChoreIds)) {
                    continue;
                }

                $results->push([
                    'chore' => $chore,
                    'assignment' => $assignment,
                    'child' => $this->resolveChild($assignment),
                ]);
            }
        }

        return $results->sortBy('chore.room.sort_order')->values();
    }

    /**
     * Get today's chores for a specific child.
     *
     * @return Collection<int, array{chore: Chore, assignment: ChoreAssignment}>
     */
    public function getTodaysChoresForChild(Child $child): Collection
    {
        if ($child->isOnVacation()) {
            return collect();
        }

        return $this->getTodaysChores()
            ->filter(fn (array $item) => $item['child']?->id === $child->id)
            ->values();
    }

    /**
     * Resolve which child is responsible for an assignment (fixed or rotated).
     */
    public function resolveChild(ChoreAssignment $assignment): ?Child
    {
        if ($assignment->child) {
            return $assignment->child;
        }

        if ($assignment->rotationGroup) {
            return $this->rotationService->getCurrentChild($assignment->rotationGroup);
        }

        return null;
    }
}
