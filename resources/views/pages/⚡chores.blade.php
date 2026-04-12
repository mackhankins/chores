<?php

use App\Models\Child;
use App\Models\Chore;
use App\Models\ChoreCompletion;
use App\Models\ChoreMiss;
use App\Models\RentPayment;
use App\Services\ChoreService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts::kid')]
#[Title('My Chores')]
class extends Component
{
    public function mount(): void
    {
        if (! $this->child()) {
            $this->redirect('/');
            return;
        }
    }

    #[Computed]
    public function child(): ?Child
    {
        $childId = session('child_id');
        $checkinDate = session('checkin_at');

        if (! $childId || $checkinDate !== now()->toDateString()) {
            session()->forget(['child_id', 'child_name', 'child_color', 'checkin_at']);
            return null;
        }

        return Child::find($childId);
    }

    #[Computed]
    public function choresByRoom(): array
    {
        $child = $this->child();
        if (! $child) {
            return [];
        }

        $service = app(ChoreService::class);
        $chores = $service->getTodaysChoresForChild($child);

        $completedIds = ChoreCompletion::query()
            ->where('child_id', $child->id)
            ->where('completed_date', today())
            ->pluck('chore_id')
            ->toArray();

        $grouped = [];
        foreach ($chores as $item) {
            $roomName = $item['chore']->room->name;
            $roomIcon = $item['chore']->room->icon ?? '';

            if (! isset($grouped[$roomName])) {
                $grouped[$roomName] = [
                    'icon' => $roomIcon,
                    'chores' => [],
                ];
            }

            $grouped[$roomName]['chores'][] = [
                'id' => $item['chore']->id,
                'name' => $item['chore']->name,
                'completed' => in_array($item['chore']->id, $completedIds),
                'is_carryover' => false,
                'missed_date' => null,
            ];
        }

        return $grouped;
    }

    #[Computed]
    public function carryoverChores(): array
    {
        $child = $this->child();
        if (! $child) {
            return [];
        }

        $service = app(ChoreService::class);
        $misses = $service->getCarryoverChoresForChild($child);

        $grouped = [];
        foreach ($misses as $miss) {
            if (! $miss->chore || ! $miss->chore->room) {
                continue;
            }

            $roomName = $miss->chore->room->name;
            $roomIcon = $miss->chore->room->icon ?? '';

            if (! isset($grouped[$roomName])) {
                $grouped[$roomName] = [
                    'icon' => $roomIcon,
                    'chores' => [],
                ];
            }

            $grouped[$roomName]['chores'][] = [
                'id' => $miss->id,
                'chore_name' => $miss->chore->name,
                'missed_date' => $miss->missed_date->format('D n/j'),
            ];
        }

        return $grouped;
    }

    #[Computed]
    public function monthlyEarnings(): array
    {
        $child = $this->child();
        if (! $child) {
            return ['earned' => 0, 'rent' => null, 'paid' => 0, 'balance' => 0, 'potential' => 0, 'missed' => 0];
        }

        $earned = (float) ChoreCompletion::query()
            ->where('child_id', $child->id)
            ->whereBetween('completed_date', [today()->startOfMonth(), today()->endOfMonth()])
            ->sum('earned_amount');

        $rent = $child->monthly_rent ? (float) $child->monthly_rent : null;

        $paid = $rent !== null
            ? (float) RentPayment::query()
                ->where('child_id', $child->id)
                ->whereBetween('paid_date', [today()->startOfMonth(), today()->endOfMonth()])
                ->sum('amount')
            : 0;

        $balance = $rent !== null ? max(0, $rent - $earned - $paid) : $earned;

        // Potential = already-earned from past days + everything still achievable today through month-end.
        $service = app(ChoreService::class);
        $earnedBeforeToday = (float) ChoreCompletion::query()
            ->where('child_id', $child->id)
            ->whereBetween('completed_date', [today()->startOfMonth(), today()->subDay()])
            ->sum('earned_amount');

        $remaining = 0.0;
        foreach (\Carbon\CarbonPeriod::create(today(), today()->endOfMonth()) as $date) {
            if ($child->isOnVacation($date)) {
                continue;
            }

            foreach ($service->getChoresForChildOnDate($child, $date) as $item) {
                $remaining += (float) ($item['chore']->value ?? 0);
            }
        }

        $scheduledBeforeToday = 0.0;
        foreach (\Carbon\CarbonPeriod::create(today()->startOfMonth(), today()->subDay()) as $date) {
            if ($child->isOnVacation($date)) {
                continue;
            }

            foreach ($service->getChoresForChildOnDate($child, $date) as $item) {
                $scheduledBeforeToday += (float) ($item['chore']->value ?? 0);
            }
        }

        $potential = $earnedBeforeToday + $remaining;
        $missed = max(0, $scheduledBeforeToday - $earnedBeforeToday);

        return [
            'earned' => $earned,
            'rent' => $rent,
            'paid' => $paid,
            'balance' => $balance,
            'potential' => $potential,
            'missed' => $missed,
        ];
    }

    public function toggleChore(string $choreId): void
    {
        $child = $this->child();
        if (! $child) {
            return;
        }

        $existing = ChoreCompletion::query()
            ->where('chore_id', $choreId)
            ->where('child_id', $child->id)
            ->where('completed_date', today())
            ->first();

        if ($existing) {
            $existing->delete();
        } else {
            $chore = Chore::find($choreId);

            ChoreCompletion::create([
                'chore_id' => $choreId,
                'child_id' => $child->id,
                'completed_date' => today(),
                'earned_amount' => $chore?->value,
            ]);
        }

        unset($this->choresByRoom);
        unset($this->monthlyEarnings);
    }

    public function completeCarryover(string $missId): void
    {
        $child = $this->child();
        if (! $child) {
            return;
        }

        $miss = ChoreMiss::query()
            ->where('id', $missId)
            ->where('child_id', $child->id)
            ->whereNull('completed_at')
            ->first();

        if ($miss) {
            $miss->update(['completed_at' => now()]);

            if ($miss->chore?->value) {
                ChoreCompletion::create([
                    'chore_id' => $miss->chore_id,
                    'child_id' => $child->id,
                    'completed_date' => today(),
                    'earned_amount' => $miss->chore->value,
                ]);
            }
        }

        unset($this->carryoverChores);
        unset($this->monthlyEarnings);
    }

    public function logout(): void
    {
        session()->forget(['child_id', 'child_name', 'child_color', 'checkin_at']);
        $this->redirect('/');
    }
};
?>

<div class="flex min-h-svh select-none flex-col">
    @if ($this->child())
        {{-- Sticky header --}}
        <div class="sticky top-0 z-10 bg-gray-100 px-5 pb-4 pt-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Hey, {{ session('child_name') }}!</h1>
                    <p class="text-sm text-gray-500">Here's what you need to do today</p>
                </div>
                <button
                    wire:click="logout"
                    class="rounded-xl bg-gray-200 px-4 py-2.5 text-sm font-semibold transition-transform active:scale-90"
                >
                    Switch
                </button>
            </div>
        </div>

        {{-- Progress + Earnings card --}}
        <div class="px-5">
            @php
                $totalChores = 0;
                $completedChores = 0;
                foreach ($this->choresByRoom as $room) {
                    foreach ($room['chores'] as $chore) {
                        $totalChores++;
                        if ($chore['completed']) {
                            $completedChores++;
                        }
                    }
                }
                $carryoverCount = 0;
                foreach ($this->carryoverChores as $room) {
                    $carryoverCount += count($room['chores']);
                }
                $totalChores += $carryoverCount;
                $progress = $totalChores > 0 ? round(($completedChores / $totalChores) * 100) : 0;
                $earnings = $this->monthlyEarnings;
            @endphp

            <div class="rounded-2xl bg-white p-4 shadow-sm">
                {{-- Chore progress --}}
                <div class="mb-2 flex items-center justify-between text-sm">
                    <span class="font-medium">Today</span>
                    <span class="font-bold" style="color: {{ session('child_color') }}">{{ $completedChores }}/{{ $totalChores }}</span>
                </div>
                <div class="h-3 overflow-hidden rounded-full bg-gray-200">
                    <div
                        class="h-full rounded-full transition-all duration-500"
                        style="width: {{ $progress }}%; background-color: {{ session('child_color') }}"
                    ></div>
                </div>
                @if ($progress === 100 && $totalChores > 0)
                    <p class="mt-2 text-center text-sm font-bold text-green-600">All done! Great job! 🎉</p>
                @endif

                {{-- Earnings / Rent --}}
                @if ($earnings['rent'] !== null)
                    @php
                        $totalCredit = $earnings['earned'] + $earnings['paid'];
                        $rentProgress = $earnings['rent'] > 0 ? min(100, round(($totalCredit / $earnings['rent']) * 100)) : 100;
                        $potentialSavings = $earnings['potential'] - $earnings['earned'];
                    @endphp
                    <div class="mt-4 mb-2 flex items-center justify-between text-sm">
                        <span class="font-medium">{{ $earnings['balance'] > 0 ? 'Rent' : 'Rent — Paid off!' }}</span>
                        <span class="font-bold {{ $earnings['balance'] > 0 ? 'text-red-500' : 'text-green-600' }}">${{ number_format($earnings['balance'], 2) }}</span>
                    </div>
                    <div class="h-3 overflow-hidden rounded-full bg-red-100">
                        <div
                            class="h-full rounded-full bg-green-500 transition-all duration-500"
                            style="width: {{ $rentProgress }}%"
                        ></div>
                    </div>
                    <div class="mt-1 flex items-center justify-between text-xs text-gray-400">
                        <span>${{ number_format($totalCredit, 2) }} of ${{ number_format($earnings['rent'], 2) }}</span>
                        <span>
                            @if ($potentialSavings > 0 && $earnings['balance'] > 0)
                                <span class="text-green-600">${{ number_format(min($potentialSavings, $earnings['balance']), 2) }} still possible</span>
                            @endif
                            @if ($earnings['missed'] > 0)
                                <span class="text-red-400">· ${{ number_format($earnings['missed'], 2) }} missed</span>
                            @endif
                        </span>
                    </div>
                @elseif ($earnings['potential'] > 0)
                    @php
                        $earnProgress = $earnings['potential'] > 0 ? min(100, round(($earnings['earned'] / $earnings['potential']) * 100)) : 0;
                    @endphp
                    <div class="mt-4 mb-2 flex items-center justify-between text-sm">
                        <span class="font-medium">Earned</span>
                        <span class="font-bold text-green-600">${{ number_format($earnings['earned'], 2) }}</span>
                    </div>
                    <div class="h-3 overflow-hidden rounded-full bg-gray-200">
                        <div
                            class="h-full rounded-full bg-green-500 transition-all duration-500"
                            style="width: {{ $earnProgress }}%"
                        ></div>
                    </div>
                    <p class="mt-1 flex items-center justify-between text-xs text-gray-400">
                        <span>${{ number_format($earnings['potential'], 2) }} possible</span>
                        @if ($earnings['missed'] > 0)
                            <span class="text-red-400">${{ number_format($earnings['missed'], 2) }} missed</span>
                        @endif
                    </p>
                @endif
            </div>
        </div>

        {{-- Chores by room --}}
        <div class="flex-1 px-5 pb-8 pt-6">
            {{-- Carryover section --}}
            @if (count($this->carryoverChores) > 0)
                <div class="mb-6">
                    <div class="mb-3 flex items-center gap-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-amber-600">Catch Up</span>
                        <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-700">{{ $carryoverCount }}</span>
                    </div>
                    @foreach ($this->carryoverChores as $roomName => $room)
                        <div class="mb-3">
                            <h2 class="mb-2 text-xs font-bold uppercase tracking-wider text-gray-400">
                                {{ $room['icon'] }} {{ $roomName }}
                            </h2>
                            <div class="flex flex-col gap-2">
                                @foreach ($room['chores'] as $chore)
                                    <button
                                        wire:click="completeCarryover('{{ $chore['id'] }}')"
                                        class="flex w-full items-center gap-4 rounded-2xl border-2 border-amber-200 bg-amber-50 p-5 shadow-sm transition-transform active:scale-[0.97]"
                                    >
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border-2 border-amber-400">
                                        </div>
                                        <div class="text-left">
                                            <span class="text-lg font-medium">{{ $chore['chore_name'] }}</span>
                                            <span class="block text-xs text-amber-600">From {{ $chore['missed_date'] }}</span>
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Today's chores --}}
            @forelse ($this->choresByRoom as $roomName => $room)
                <div class="mb-5">
                    <h2 class="mb-2 text-xs font-bold uppercase tracking-wider text-gray-400">
                        {{ $room['icon'] }} {{ $roomName }}
                    </h2>
                    <div class="flex flex-col gap-2">
                        @foreach ($room['chores'] as $chore)
                            <button
                                wire:click="toggleChore('{{ $chore['id'] }}')"
                                class="flex w-full items-center gap-4 rounded-2xl bg-white p-5 shadow-sm transition-transform active:scale-[0.97] {{ $chore['completed'] ? 'opacity-50' : '' }}"
                            >
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border-2 transition-all {{ $chore['completed'] ? 'border-green-500 bg-green-500 text-white' : 'border-gray-300' }}">
                                    @if ($chore['completed'])
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                        </svg>
                                    @endif
                                </div>
                                <span class="text-left text-lg font-medium {{ $chore['completed'] ? 'text-gray-400 line-through' : '' }}">
                                    {{ $chore['name'] }}
                                </span>
                            </button>
                        @endforeach
                    </div>
                </div>
            @empty
                @if (count($this->carryoverChores) === 0)
                    <div class="flex flex-1 flex-col items-center justify-center py-20 text-center">
                        <p class="text-5xl">🎉</p>
                        <p class="mt-4 text-xl font-bold">No chores today!</p>
                        <p class="text-gray-500">Enjoy your free time</p>
                    </div>
                @endif
            @endforelse
        </div>
    @endif
</div>
