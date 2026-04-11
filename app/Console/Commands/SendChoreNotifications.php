<?php

namespace App\Console\Commands;

use App\Models\Child;
use App\Models\ChoreCompletion;
use App\Services\ChoreService;
use App\Services\SmsService;
use Illuminate\Console\Command;

class SendChoreNotifications extends Command
{
    protected $signature = 'chores:notify {--test-child= : Send both notifications to a specific child by name, ignoring schedule}';

    protected $description = 'Send morning chore lists and evening reminders via SMS';

    public function handle(ChoreService $choreService, SmsService $smsService): void
    {
        $dashboardUrl = config('app.url');

        if ($testName = $this->option('test-child')) {
            $this->sendTestNotification($testName, $choreService, $smsService, $dashboardUrl);

            return;
        }

        $currentTime = now()->format('H:i');
        $this->sendMorningNotifications($currentTime, $choreService, $smsService, $dashboardUrl);
        $this->sendReminderNotifications($currentTime, $choreService, $smsService, $dashboardUrl);
    }

    protected function sendTestNotification(
        string $childName,
        ChoreService $choreService,
        SmsService $smsService,
        string $dashboardUrl,
    ): void {
        $child = Child::where('name', $childName)->first();

        if (! $child) {
            $this->error("Child \"{$childName}\" not found.");

            return;
        }

        if (! $child->phone || ! $child->carrier) {
            $this->error("{$child->name} has no phone number or carrier set.");

            return;
        }

        $chores = $choreService->getTodaysChoresForChild($child);

        if ($chores->isEmpty()) {
            $this->warn("{$child->name} has no chores today.");

            return;
        }

        $count = $chores->count();

        $message = "Good morning {$child->name}! You have {$count} "
            .($count === 1 ? 'chore' : 'chores')
            ." today. {$dashboardUrl}";

        $smsService->send($child->phone, $message, $child->carrier);
        $this->info("Test morning notification sent to {$child->name} at {$child->phone}");

        $completedIds = ChoreCompletion::query()
            ->where('child_id', $child->id)
            ->where('completed_date', today())
            ->pluck('chore_id')
            ->toArray();

        $remaining = $chores->filter(
            fn (array $item) => ! in_array($item['chore']->id, $completedIds)
        );

        if ($remaining->isEmpty()) {
            $this->info('All chores complete — reminder skipped.');

            return;
        }

        $remainingCount = $remaining->count();

        $reminderMessage = "Hey {$child->name}, you still have {$remainingCount} "
            .($remainingCount === 1 ? 'chore' : 'chores')
            ." left. Finish up! {$dashboardUrl}";

        $smsService->send($child->phone, $reminderMessage, $child->carrier);
        $this->info("Test reminder sent to {$child->name}");
    }

    protected function sendMorningNotifications(
        string $currentTime,
        ChoreService $choreService,
        SmsService $smsService,
        string $dashboardUrl,
    ): void {
        $children = Child::query()
            ->whereNotNull('phone')
            ->whereNotNull('carrier')
            ->whereNotNull('notify_morning_at')
            ->whereRaw("strftime('%H:%M', notify_morning_at) = ?", [$currentTime])
            ->get();

        foreach ($children as $child) {
            $chores = $choreService->getTodaysChoresForChild($child);
            $carryover = $choreService->getCarryoverChoresForChild($child);

            $todayCount = $chores->count();
            $carryoverCount = $carryover->count();
            $totalCount = $todayCount + $carryoverCount;

            if ($totalCount === 0) {
                continue;
            }

            $message = "Good morning {$child->name}! You have {$totalCount} "
                .($totalCount === 1 ? 'chore' : 'chores')
                .' today';

            if ($carryoverCount > 0) {
                $message .= " ({$carryoverCount} to catch up on)";
            }

            $message .= ". {$dashboardUrl}";

            $smsService->send($child->phone, $message, $child->carrier);
            $this->info("Morning notification sent to {$child->name}");
        }
    }

    protected function sendReminderNotifications(
        string $currentTime,
        ChoreService $choreService,
        SmsService $smsService,
        string $dashboardUrl,
    ): void {
        $children = Child::query()
            ->whereNotNull('phone')
            ->whereNotNull('carrier')
            ->whereNotNull('notify_reminder_at')
            ->whereRaw("strftime('%H:%M', notify_reminder_at) = ?", [$currentTime])
            ->get();

        foreach ($children as $child) {
            $chores = $choreService->getTodaysChoresForChild($child);
            $carryover = $choreService->getCarryoverChoresForChild($child);

            $completedIds = ChoreCompletion::query()
                ->where('child_id', $child->id)
                ->where('completed_date', today())
                ->pluck('chore_id')
                ->toArray();

            $remainingToday = $chores->filter(
                fn (array $item) => ! in_array($item['chore']->id, $completedIds)
            )->count();

            $remainingCarryover = $carryover->count();
            $totalRemaining = $remainingToday + $remainingCarryover;

            if ($totalRemaining === 0) {
                continue;
            }

            $message = "Hey {$child->name}, you still have {$totalRemaining} "
                .($totalRemaining === 1 ? 'chore' : 'chores')
                ." left. Finish up! {$dashboardUrl}";

            $smsService->send($child->phone, $message, $child->carrier);
            $this->info("Reminder sent to {$child->name} ({$totalRemaining} remaining)");
        }
    }
}
