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

        if (! $child->phone) {
            $this->error("{$child->name} has no phone number set.");

            return;
        }

        $chores = $choreService->getTodaysChoresForChild($child);

        if ($chores->isEmpty()) {
            $this->warn("{$child->name} has no chores today.");

            return;
        }

        $choreNames = $chores->pluck('chore.name')->implode(', ');
        $count = $chores->count();

        $message = "Good morning {$child->name}! You have {$count} "
            .($count === 1 ? 'chore' : 'chores')
            ." today: {$choreNames}. Check them off here: {$dashboardUrl}";

        $smsService->send($child->phone, $message);
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

        $remainingNames = $remaining->pluck('chore.name')->implode(', ');
        $remainingCount = $remaining->count();

        $reminderMessage = "Hey {$child->name}, you still have {$remainingCount} "
            .($remainingCount === 1 ? 'chore' : 'chores')
            ." left: {$remainingNames}. Finish up! {$dashboardUrl}";

        $smsService->send($child->phone, $reminderMessage);
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
            ->whereNotNull('notify_morning_at')
            ->whereRaw("strftime('%H:%M', notify_morning_at) = ?", [$currentTime])
            ->get();

        foreach ($children as $child) {
            $chores = $choreService->getTodaysChoresForChild($child);

            if ($chores->isEmpty()) {
                continue;
            }

            $choreNames = $chores->pluck('chore.name')->implode(', ');
            $count = $chores->count();

            $message = "Good morning {$child->name}! You have {$count} "
                .($count === 1 ? 'chore' : 'chores')
                ." today: {$choreNames}. Check them off here: {$dashboardUrl}";

            $smsService->send($child->phone, $message);
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
            ->whereNotNull('notify_reminder_at')
            ->whereRaw("strftime('%H:%M', notify_reminder_at) = ?", [$currentTime])
            ->get();

        foreach ($children as $child) {
            $chores = $choreService->getTodaysChoresForChild($child);

            if ($chores->isEmpty()) {
                continue;
            }

            $completedIds = ChoreCompletion::query()
                ->where('child_id', $child->id)
                ->where('completed_date', today())
                ->pluck('chore_id')
                ->toArray();

            $remaining = $chores->filter(
                fn (array $item) => ! in_array($item['chore']->id, $completedIds)
            );

            if ($remaining->isEmpty()) {
                continue;
            }

            $remainingNames = $remaining->pluck('chore.name')->implode(', ');
            $count = $remaining->count();

            $message = "Hey {$child->name}, you still have {$count} "
                .($count === 1 ? 'chore' : 'chores')
                ." left: {$remainingNames}. Finish up! {$dashboardUrl}";

            $smsService->send($child->phone, $message);
            $this->info("Reminder sent to {$child->name} ({$count} remaining)");
        }
    }
}
