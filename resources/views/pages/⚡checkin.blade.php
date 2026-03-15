<?php

use App\Models\Child;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts::kid')]
#[Title('Check In')]
class extends Component
{
    public string $pin = '';
    public string $error = '';

    public function appendDigit(string $digit): void
    {
        if (strlen($this->pin) < 4) {
            $this->pin .= $digit;
            $this->error = '';
        }

        if (strlen($this->pin) === 4) {
            $this->submit();
        }
    }

    public function deleteDigit(): void
    {
        $this->pin = substr($this->pin, 0, -1);
        $this->error = '';
    }

    public function clear(): void
    {
        $this->pin = '';
        $this->error = '';
    }

    public function submit(): void
    {
        $child = Child::where('pin', $this->pin)->first();

        if (! $child) {
            $this->error = 'Wrong PIN. Try again!';
            $this->pin = '';
            return;
        }

        session([
            'child_id' => $child->id,
            'child_name' => $child->name,
            'child_color' => $child->avatar_color,
            'checkin_at' => now()->toDateString(),
        ]);

        $this->redirect('/chores');
    }
};
?>

<div class="flex min-h-svh select-none flex-col items-center justify-center px-6 py-8">
    <h1 class="mb-1 text-2xl font-bold">Who's checking in?</h1>
    <p class="mb-8 text-sm text-gray-500">Enter your 4-digit PIN</p>

    {{-- PIN display --}}
    <div class="mb-6 flex gap-4">
        @for ($i = 0; $i < 4; $i++)
            <div class="flex h-16 w-16 items-center justify-center rounded-2xl border-2 {{ $i < strlen($pin) ? 'border-blue-500 bg-blue-50' : 'border-gray-300 bg-white' }} text-2xl font-bold transition-all">
                {{ $i < strlen($pin) ? '●' : '' }}
            </div>
        @endfor
    </div>

    {{-- Error message --}}
    @if ($error)
        <p class="mb-4 text-sm font-medium text-red-500">{{ $error }}</p>
    @endif

    {{-- Keypad --}}
    <div class="grid w-full max-w-xs grid-cols-3 gap-3">
        @foreach (range(1, 9) as $digit)
            <button
                wire:click="appendDigit('{{ $digit }}')"
                class="flex h-18 items-center justify-center rounded-2xl bg-white text-2xl font-semibold shadow-sm transition-transform active:scale-90"
            >
                {{ $digit }}
            </button>
        @endforeach
        <button
            wire:click="clear"
            class="flex h-18 items-center justify-center rounded-2xl bg-gray-200 text-sm font-semibold transition-transform active:scale-90"
        >
            Clear
        </button>
        <button
            wire:click="appendDigit('0')"
            class="flex h-18 items-center justify-center rounded-2xl bg-white text-2xl font-semibold shadow-sm transition-transform active:scale-90"
        >
            0
        </button>
        <button
            wire:click="deleteDigit"
            class="flex h-18 items-center justify-center rounded-2xl bg-gray-200 text-2xl transition-transform active:scale-90"
        >
            ⌫
        </button>
    </div>
</div>
