<?php

namespace App\Services;

use App\Models\Child;
use App\Models\RotationGroup;
use App\RotationPeriod;
use Carbon\Carbon;

class RotationService
{
    /**
     * Get the currently assigned child for a rotation group on a given date.
     */
    public function getCurrentChild(RotationGroup $group, ?Carbon $date = null): ?Child
    {
        $date ??= Carbon::today();

        $members = $group->members()->get();

        if ($members->isEmpty()) {
            return null;
        }

        $periodsElapsed = $this->calculatePeriodsElapsed($group, $date);
        $index = $periodsElapsed % $members->count();

        return $members->values()->get($index);
    }

    /**
     * Calculate how many rotation periods have elapsed since the start date.
     */
    protected function calculatePeriodsElapsed(RotationGroup $group, Carbon $date): int
    {
        $startDate = $group->start_date;

        if ($date->lt($startDate)) {
            return 0;
        }

        return match ($group->period) {
            RotationPeriod::Daily => (int) $startDate->diffInDays($date),
            RotationPeriod::Weekly => (int) $startDate->diffInWeeks($date),
            RotationPeriod::Biweekly => (int) floor($startDate->diffInWeeks($date) / 2),
            RotationPeriod::Monthly => (int) $startDate->diffInMonths($date),
        };
    }
}
