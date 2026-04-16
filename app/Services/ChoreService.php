<?php

namespace App\Services;

use App\Models\Child;
use App\Models\Chore;
use App\Models\ChoreAssignment;
use App\Models\ChoreCompletion;
use App\Models\ChoreMiss;
use Carbon\Carbon;
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
        return $this->getChoresForDate(today());
    }

    /**
     * Get chores scheduled for a specific date.
     *
     * @return Collection<int, array{chore: Chore, assignment: ChoreAssignment, child: ?Child}>
     */
    public function getChoresForDate(Carbon $date): Collection
    {
        $assignments = ChoreAssignment::query()
            ->with(['chore.room', 'room.chores', 'child', 'rotationGroup.members'])
            ->get();

        $choreAssignments = $assignments->whereNotNull('chore_id');
        $roomAssignments = $assignments->whereNotNull('room_id');

        $directlyAssignedChoreIds = $choreAssignments->pluck('chore_id')->toArray();

        $results = collect();

        foreach ($choreAssignments as $assignment) {
            if (! $assignment->chore?->is_active || ! $assignment->chore->isScheduledOn($date)) {
                continue;
            }

            $results->push([
                'chore' => $assignment->chore,
                'assignment' => $assignment,
                'child' => $this->resolveChild($assignment, $date),
            ]);
        }

        foreach ($roomAssignments as $assignment) {
            if (! $assignment->room) {
                continue;
            }

            foreach ($assignment->room->chores as $chore) {
                if (! $chore->is_active || ! $chore->isScheduledOn($date)) {
                    continue;
                }

                if (in_array($chore->id, $directlyAssignedChoreIds)) {
                    continue;
                }

                $results->push([
                    'chore' => $chore,
                    'assignment' => $assignment,
                    'child' => $this->resolveChild($assignment, $date),
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
        return $this->getChoresForChildOnDate($child, today());
    }

    /**
     * Get chores for a specific child on a specific date.
     *
     * @return Collection<int, array{chore: Chore, assignment: ChoreAssignment}>
     */
    public function getChoresForChildOnDate(Child $child, Carbon $date): Collection
    {
        if ($child->isOnVacation($date)) {
            return collect();
        }

        return $this->getChoresForDate($date)
            ->filter(fn (array $item) => $item['child']?->id === $child->id)
            ->values();
    }

    /**
     * Get outstanding carryover chores for a child (missed and not yet completed).
     *
     * @return Collection<int, ChoreMiss>
     */
    public function getCarryoverChoresForChild(Child $child): Collection
    {
        $carryoverDays = config('chores.carryover_days', 7);
        $cutoffDate = today()->subDays($carryoverDays);

        $misses = ChoreMiss::query()
            ->with('chore.room')
            ->where('child_id', $child->id)
            ->whereNull('completed_at')
            ->where('missed_date', '>=', $cutoffDate)
            ->orderBy('missed_date')
            ->get();

        return $this->filterSupersededMisses($misses, $child);
    }

    /**
     * Drop misses whose chore has a newer scheduled occurrence — today's schedule,
     * a later miss, or a later completion. The earning window closes the moment
     * the chore comes back around, so an older miss can no longer be caught up.
     *
     * Already-resolved misses (completed_at set) pass through untouched so the UI
     * can still render them with a checkmark on the day they were resolved.
     *
     * @param  Collection<int, ChoreMiss>  $misses
     * @return Collection<int, ChoreMiss>
     */
    public function filterSupersededMisses(Collection $misses, Child $child): Collection
    {
        if ($misses->isEmpty()) {
            return $misses;
        }

        $todayChoreIds = $this->getTodaysChoresForChild($child)
            ->pluck('chore.id')
            ->all();

        $choreIds = $misses->pluck('chore_id')->unique()->all();

        $latestMissDates = ChoreMiss::query()
            ->where('child_id', $child->id)
            ->whereIn('chore_id', $choreIds)
            ->selectRaw('chore_id, MAX(missed_date) as latest_date')
            ->groupBy('chore_id')
            ->get()
            ->mapWithKeys(fn ($row) => [$row->chore_id => Carbon::parse($row->latest_date)->toDateString()])
            ->all();

        $latestCompletionDates = ChoreCompletion::query()
            ->where('child_id', $child->id)
            ->whereIn('chore_id', $choreIds)
            ->selectRaw('chore_id, MAX(completed_date) as latest_date')
            ->groupBy('chore_id')
            ->get()
            ->mapWithKeys(fn ($row) => [$row->chore_id => Carbon::parse($row->latest_date)->toDateString()])
            ->all();

        return $misses->filter(function (ChoreMiss $miss) use ($todayChoreIds, $latestMissDates, $latestCompletionDates) {
            if ($miss->completed_at !== null) {
                return true;
            }

            if (in_array($miss->chore_id, $todayChoreIds)) {
                return false;
            }

            $missDateStr = $miss->missed_date->toDateString();

            if (($latestMissDates[$miss->chore_id] ?? null) > $missDateStr) {
                return false;
            }

            if (($latestCompletionDates[$miss->chore_id] ?? null) > $missDateStr) {
                return false;
            }

            return true;
        })->values();
    }

    /**
     * Resolve which child is responsible for an assignment (fixed or rotated).
     */
    public function resolveChild(ChoreAssignment $assignment, ?Carbon $date = null): ?Child
    {
        if ($assignment->child) {
            return $assignment->child;
        }

        if ($assignment->rotationGroup) {
            return $this->rotationService->getCurrentChild($assignment->rotationGroup, $date);
        }

        return null;
    }
}
