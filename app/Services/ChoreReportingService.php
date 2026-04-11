<?php

namespace App\Services;

use App\Models\Child;
use App\Models\ChoreCompletion;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

class ChoreReportingService
{
    public function __construct(
        protected ChoreService $choreService,
    ) {}

    /**
     * Get completion stats for a child over a date range.
     *
     * @return array{total: int, completed: int, rate: float}
     */
    public function getCompletionStats(Child $child, Carbon $startDate, Carbon $endDate): array
    {
        $total = 0;
        $completed = 0;

        $completedLookup = ChoreCompletion::query()
            ->where('child_id', $child->id)
            ->whereBetween('completed_date', [$startDate, $endDate])
            ->get()
            ->groupBy(fn (ChoreCompletion $c) => $c->completed_date->toDateString());

        foreach (CarbonPeriod::create($startDate, $endDate) as $date) {
            if ($date->isAfter(today())) {
                break;
            }

            if ($child->isOnVacation($date)) {
                continue;
            }

            $chores = $this->choreService->getChoresForChildOnDate($child, $date);
            $dayTotal = $chores->count();
            $total += $dayTotal;

            $dayCompletedIds = $completedLookup->get($date->toDateString(), collect())
                ->pluck('chore_id')
                ->toArray();

            $completed += $chores->filter(
                fn (array $item) => in_array($item['chore']->id, $dayCompletedIds)
            )->count();
        }

        return [
            'total' => $total,
            'completed' => $completed,
            'rate' => $total > 0 ? round(($completed / $total) * 100) : 0,
        ];
    }

    /**
     * Get completion stats for all children over a date range.
     *
     * @return Collection<int, array{child: Child, total: int, completed: int, missed: int, rate: float}>
     */
    public function getAllChildrenStats(Carbon $startDate, Carbon $endDate): Collection
    {
        return Child::all()->map(function (Child $child) use ($startDate, $endDate) {
            $stats = $this->getCompletionStats($child, $startDate, $endDate);

            $earned = (float) ChoreCompletion::query()
                ->where('child_id', $child->id)
                ->whereBetween('completed_date', [$startDate, $endDate])
                ->sum('earned_amount');

            $rent = $child->monthly_rent ? (float) $child->monthly_rent : null;

            return [
                'child' => $child,
                'total' => $stats['total'],
                'completed' => $stats['completed'],
                'missed' => $stats['total'] - $stats['completed'],
                'rate' => $stats['rate'],
                'earned' => $earned,
                'rent' => $rent,
                'balance' => $rent !== null ? max(0, $rent - $earned) : null,
            ];
        });
    }

    /**
     * Get daily completion rates for a child over a date range (for sparklines).
     *
     * @return array<int, float>
     */
    public function getDailyRates(Child $child, Carbon $startDate, Carbon $endDate): array
    {
        $rates = [];

        $completedLookup = ChoreCompletion::query()
            ->where('child_id', $child->id)
            ->whereBetween('completed_date', [$startDate, $endDate])
            ->get()
            ->groupBy(fn (ChoreCompletion $c) => $c->completed_date->toDateString());

        foreach (CarbonPeriod::create($startDate, $endDate) as $date) {
            if ($date->isAfter(today())) {
                break;
            }

            if ($child->isOnVacation($date)) {
                continue;
            }

            $chores = $this->choreService->getChoresForChildOnDate($child, $date);
            $dayTotal = $chores->count();

            if ($dayTotal === 0) {
                continue;
            }

            $dayCompletedIds = $completedLookup->get($date->toDateString(), collect())
                ->pluck('chore_id')
                ->toArray();

            $dayCompleted = $chores->filter(
                fn (array $item) => in_array($item['chore']->id, $dayCompletedIds)
            )->count();

            $rates[] = round(($dayCompleted / $dayTotal) * 100);
        }

        return $rates;
    }

    /**
     * Get per-chore breakdown for a child over a date range.
     *
     * @return Collection<int, array{chore_name: string, room_name: string, total: int, completed: int, rate: float}>
     */
    public function getPerChoreStats(Child $child, Carbon $startDate, Carbon $endDate): Collection
    {
        $choreStats = [];

        $completedLookup = ChoreCompletion::query()
            ->where('child_id', $child->id)
            ->whereBetween('completed_date', [$startDate, $endDate])
            ->get()
            ->groupBy(fn (ChoreCompletion $c) => $c->completed_date->toDateString());

        foreach (CarbonPeriod::create($startDate, $endDate) as $date) {
            if ($date->isAfter(today())) {
                break;
            }

            if ($child->isOnVacation($date)) {
                continue;
            }

            $chores = $this->choreService->getChoresForChildOnDate($child, $date);
            $dayCompletedIds = $completedLookup->get($date->toDateString(), collect())
                ->pluck('chore_id')
                ->toArray();

            foreach ($chores as $item) {
                $choreId = $item['chore']->id;

                if (! isset($choreStats[$choreId])) {
                    $choreStats[$choreId] = [
                        'chore_id' => $choreId,
                        'chore_name' => $item['chore']->name,
                        'room_name' => $item['chore']->room->name,
                        'total' => 0,
                        'completed' => 0,
                        'rate' => 0,
                    ];
                }

                $choreStats[$choreId]['total']++;

                if (in_array($choreId, $dayCompletedIds)) {
                    $choreStats[$choreId]['completed']++;
                }
            }
        }

        return collect($choreStats)
            ->map(function (array $stat) {
                $stat['rate'] = $stat['total'] > 0
                    ? round(($stat['completed'] / $stat['total']) * 100)
                    : 0;

                return $stat;
            })
            ->sortBy('rate')
            ->values();
    }
}
