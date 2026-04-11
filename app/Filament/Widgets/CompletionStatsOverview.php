<?php

namespace App\Filament\Widgets;

use App\Models\Child;
use App\Services\ChoreReportingService;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CompletionStatsOverview extends StatsOverviewWidget
{
    protected ?string $pollingInterval = null;

    protected static ?int $sort = -1;

    protected function getStats(): array
    {
        $service = app(ChoreReportingService::class);
        $startOfMonth = today()->startOfMonth();
        $endOfMonth = today()->endOfMonth();

        $stats = [];

        $children = Child::all();
        $overallTotal = 0;
        $overallCompleted = 0;

        foreach ($children as $child) {
            $childStats = $service->getCompletionStats($child, $startOfMonth, $endOfMonth);
            $dailyRates = $service->getDailyRates($child, $startOfMonth, $endOfMonth);

            $overallTotal += $childStats['total'];
            $overallCompleted += $childStats['completed'];

            $color = match (true) {
                $childStats['rate'] >= 80 => 'success',
                $childStats['rate'] >= 50 => 'warning',
                default => 'danger',
            };

            $stats[] = Stat::make($child->name, $childStats['rate'].'%')
                ->description($childStats['completed'].'/'.$childStats['total'].' chores this month')
                ->chart($dailyRates)
                ->color($color);
        }

        $overallRate = $overallTotal > 0 ? round(($overallCompleted / $overallTotal) * 100) : 0;

        array_unshift($stats, Stat::make('Household', $overallRate.'%')
            ->description('Overall completion rate for '.today()->format('F'))
            ->color(match (true) {
                $overallRate >= 80 => 'success',
                $overallRate >= 50 => 'warning',
                default => 'danger',
            }));

        return $stats;
    }
}
