<?php

namespace App\Filament\Pages;

use App\Models\Child;
use App\Services\ChoreReportingService;
use BackedEnum;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Carbon;
use UnitEnum;

class ChoreReport extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static string|UnitEnum|null $navigationGroup = 'Chores';

    protected static ?int $navigationSort = 5;

    protected static ?string $title = 'Completion Report';

    protected static ?string $navigationLabel = 'Report';

    protected string $view = 'filament.pages.chore-report';

    public ?string $childFilter = null;

    public ?string $periodFilter = 'this_month';

    public ?string $startDate = null;

    public ?string $endDate = null;

    public function mount(): void
    {
        $this->startDate = today()->startOfMonth()->toDateString();
        $this->endDate = today()->toDateString();
    }

    public function updatedPeriodFilter(): void
    {
        [$start, $end] = match ($this->periodFilter) {
            'this_week' => [today()->startOfWeek(), today()],
            'last_week' => [today()->subWeek()->startOfWeek(), today()->subWeek()->endOfWeek()],
            'this_month' => [today()->startOfMonth(), today()],
            'last_month' => [today()->subMonth()->startOfMonth(), today()->subMonth()->endOfMonth()],
            default => [Carbon::parse($this->startDate), Carbon::parse($this->endDate)],
        };

        $this->startDate = $start->toDateString();
        $this->endDate = $end->toDateString();
    }

    public function getReportData(): array
    {
        $service = app(ChoreReportingService::class);
        $start = Carbon::parse($this->startDate);
        $end = Carbon::parse($this->endDate);

        $childrenStats = $service->getAllChildrenStats($start, $end);

        if ($this->childFilter) {
            $childrenStats = $childrenStats->filter(
                fn (array $stat) => $stat['child']->id === $this->childFilter
            );
        }

        $perChoreStats = null;

        if ($this->childFilter) {
            $child = Child::find($this->childFilter);
            if ($child) {
                $perChoreStats = $service->getPerChoreStats($child, $start, $end);
            }
        }

        return [
            'childrenStats' => $childrenStats,
            'perChoreStats' => $perChoreStats,
            'startDate' => $start->format('M j, Y'),
            'endDate' => $end->format('M j, Y'),
        ];
    }
}
