<?php

use App\Models\Child;
use App\Models\ChoreCompletion;
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
            ];
        }

        return $grouped;
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
            ChoreCompletion::create([
                'chore_id' => $choreId,
                'child_id' => $child->id,
                'completed_date' => today(),
            ]);
        }

        unset($this->choresByRoom);
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

            {{-- Progress --}}
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
                $progress = $totalChores > 0 ? round(($completedChores / $totalChores) * 100) : 0;
            @endphp

            <div class="mt-4 rounded-2xl bg-white p-4 shadow-sm">
                <div class="mb-2 flex items-center justify-between text-sm">
                    <span class="font-medium">Progress</span>
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
            </div>
        </div>

        {{-- Chores by room --}}
        <div class="flex-1 px-5 pb-8">
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
                <div class="flex flex-1 flex-col items-center justify-center py-20 text-center">
                    <p class="text-5xl">🎉</p>
                    <p class="mt-4 text-xl font-bold">No chores today!</p>
                    <p class="text-gray-500">Enjoy your free time</p>
                </div>
            @endforelse
        </div>
    @endif
</div>
