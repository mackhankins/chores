<?php

namespace App\Filament\Pages;

use App\Models\Child;
use App\Models\Chore;
use App\Models\ChoreCompletion;
use App\Models\ChoreMiss;
use App\Services\ChoreReportingService;
use BackedEnum;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
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

    public bool $missedOnly = false;

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
        $missedInstances = null;

        $selectedChild = $this->childFilter ? Child::find($this->childFilter) : null;

        if ($this->missedOnly) {
            $missedInstances = $service->getMissedInstances($selectedChild, $start, $end);
        } elseif ($selectedChild) {
            $perChoreStats = $service->getPerChoreStats($selectedChild, $start, $end);
        }

        return [
            'childrenStats' => $childrenStats,
            'perChoreStats' => $perChoreStats,
            'missedInstances' => $missedInstances,
            'startDate' => $start->format('M j, Y'),
            'endDate' => $end->format('M j, Y'),
        ];
    }

    public function markMissedComplete(string $childId, string $choreId, string $date): void
    {
        $child = Child::find($childId);
        $chore = Chore::find($choreId);

        if (! $child || ! $chore) {
            return;
        }

        $scheduledDate = Carbon::parse($date)->startOfDay();

        ChoreCompletion::firstOrCreate(
            [
                'chore_id' => $chore->id,
                'child_id' => $child->id,
                'completed_date' => $scheduledDate,
            ],
            ['earned_amount' => $chore->value],
        );

        ChoreMiss::query()
            ->where('chore_id', $chore->id)
            ->where('child_id', $child->id)
            ->whereDate('missed_date', $scheduledDate)
            ->whereNull('completed_at')
            ->update(['completed_at' => now()]);

        Notification::make()
            ->title("Marked complete for {$child->name}")
            ->body("{$chore->name} on {$scheduledDate->format('M j, Y')}")
            ->success()
            ->send();
    }
}
