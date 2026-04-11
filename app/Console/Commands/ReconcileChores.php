<?php

namespace App\Console\Commands;

use App\Models\Child;
use App\Models\ChoreCompletion;
use App\Models\ChoreMiss;
use App\Services\ChoreService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ReconcileChores extends Command
{
    protected $signature = 'chores:reconcile {--date= : The date to reconcile (defaults to yesterday)}';

    protected $description = 'Record missed chores for a given day as carryover tasks';

    public function handle(ChoreService $choreService): void
    {
        $date = $this->option('date')
            ? Carbon::parse($this->option('date'))
            : today()->subDay();

        $this->info("Reconciling chores for {$date->toDateString()}...");

        $children = Child::all();
        $missCount = 0;

        foreach ($children as $child) {
            if ($child->isOnVacation($date)) {
                continue;
            }

            $chores = $choreService->getChoresForChildOnDate($child, $date);

            if ($chores->isEmpty()) {
                continue;
            }

            $completedIds = ChoreCompletion::query()
                ->where('child_id', $child->id)
                ->where('completed_date', $date)
                ->pluck('chore_id')
                ->toArray();

            foreach ($chores as $item) {
                $chore = $item['chore'];

                if (in_array($chore->id, $completedIds)) {
                    continue;
                }

                if (! $chore->is_carryover_eligible) {
                    continue;
                }

                ChoreMiss::query()->firstOrCreate([
                    'chore_id' => $chore->id,
                    'child_id' => $child->id,
                    'missed_date' => $date,
                ]);

                $missCount++;
            }
        }

        $this->info("Recorded {$missCount} missed ".($missCount === 1 ? 'chore' : 'chores').'.');
    }
}
