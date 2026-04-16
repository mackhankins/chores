<?php

namespace App\Services;

use App\Models\Child;
use App\Models\ChoreCompletion;
use App\Models\ChoreMiss;
use App\Models\RentPayment;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

class ChoreReportingService
{
    public function __construct(
        protected ChoreService $choreService,
    ) {}

    /**
     * Build a [scheduled_date => [chore_id, ...]] lookup that treats both
     * same-day completions and resolved carryover misses as completed on
     * the originally-scheduled date.
     *
     * @return array<string, array<int, string>>
     */
    protected function buildCompletedLookup(Child $child, Carbon $startDate, Carbon $endDate): array
    {
        $lookup = [];

        ChoreCompletion::query()
            ->where('child_id', $child->id)
            ->whereBetween('completed_date', [$startDate, $endDate])
            ->get()
            ->each(function (ChoreCompletion $c) use (&$lookup) {
                $lookup[$c->completed_date->toDateString()][] = $c->chore_id;
            });

        ChoreMiss::query()
            ->where('child_id', $child->id)
            ->whereNotNull('completed_at')
            ->whereBetween('missed_date', [$startDate, $endDate])
            ->get()
            ->each(function (ChoreMiss $m) use (&$lookup) {
                $lookup[$m->missed_date->toDateString()][] = $m->chore_id;
            });

        return $lookup;
    }

    /**
     * Get completion stats for a child over a date range.
     *
     * @return array{total: int, completed: int, rate: float}
     */
    public function getCompletionStats(Child $child, Carbon $startDate, Carbon $endDate): array
    {
        $total = 0;
        $completed = 0;

        $completedLookup = $this->buildCompletedLookup($child, $startDate, $endDate);

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

            $dayCompletedIds = $completedLookup[$date->toDateString()] ?? [];

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

            $paid = $rent !== null
                ? (float) RentPayment::query()
                    ->where('child_id', $child->id)
                    ->whereBetween('paid_date', [$startDate, $endDate])
                    ->sum('amount')
                : 0;

            return [
                'child' => $child,
                'total' => $stats['total'],
                'completed' => $stats['completed'],
                'missed' => $stats['total'] - $stats['completed'],
                'rate' => $stats['rate'],
                'earned' => $earned,
                'rent' => $rent,
                'paid' => $paid,
                'balance' => $rent !== null ? max(0, $rent - $earned - $paid) : null,
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

        $completedLookup = $this->buildCompletedLookup($child, $startDate, $endDate);

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

            $dayCompletedIds = $completedLookup[$date->toDateString()] ?? [];

            $dayCompleted = $chores->filter(
                fn (array $item) => in_array($item['chore']->id, $dayCompletedIds)
            )->count();

            $rates[] = round(($dayCompleted / $dayTotal) * 100);
        }

        return $rates;
    }

    /**
     * Get instance-level missed chore rows for a date range.
     *
     * Emits one row per (child, chore, scheduled_date) where the chore was assigned
     * but has no matching completion on that date. Today is excluded so live, still-
     * actionable chores aren't flagged as missed.
     *
     * @return Collection<int, array{child: Child, chore: Chore, date: Carbon, room_name: string}>
     */
    public function getMissedInstances(?Child $child, Carbon $startDate, Carbon $endDate): Collection
    {
        $children = $child ? collect([$child]) : Child::all();
        $cutoff = today()->subDay();

        $rows = collect();

        foreach ($children as $c) {
            $completedLookup = $this->buildCompletedLookup($c, $startDate, $endDate);

            foreach (CarbonPeriod::create($startDate, $endDate) as $date) {
                if ($date->isAfter($cutoff)) {
                    break;
                }

                if ($c->isOnVacation($date)) {
                    continue;
                }

                $dayCompletedIds = $completedLookup[$date->toDateString()] ?? [];

                foreach ($this->choreService->getChoresForChildOnDate($c, $date) as $item) {
                    if (in_array($item['chore']->id, $dayCompletedIds)) {
                        continue;
                    }

                    $rows->push([
                        'child' => $c,
                        'chore' => $item['chore'],
                        'date' => $date->copy(),
                        'room_name' => $item['chore']->room->name,
                    ]);
                }
            }
        }

        return $rows->sortBy([
            ['date', 'asc'],
            ['child.name', 'asc'],
            ['chore.name', 'asc'],
        ])->values();
    }

    /**
     * Get per-chore breakdown for a child over a date range.
     *
     * @return Collection<int, array{chore_name: string, room_name: string, total: int, completed: int, rate: float}>
     */
    public function getPerChoreStats(Child $child, Carbon $startDate, Carbon $endDate): Collection
    {
        $choreStats = [];

        $completedLookup = $this->buildCompletedLookup($child, $startDate, $endDate);

        foreach (CarbonPeriod::create($startDate, $endDate) as $date) {
            if ($date->isAfter(today())) {
                break;
            }

            if ($child->isOnVacation($date)) {
                continue;
            }

            $chores = $this->choreService->getChoresForChildOnDate($child, $date);
            $dayCompletedIds = $completedLookup[$date->toDateString()] ?? [];

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
