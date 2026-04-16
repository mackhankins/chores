<?php

use App\Filament\Pages\ChoreReport;
use App\Models\Child;
use App\Models\Chore;
use App\Models\ChoreCompletion;
use App\Models\ChoreMiss;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Carbon::setTestNow(Carbon::parse('2025-01-15 12:00:00'));
    $this->actingAs(User::factory()->create());
});

afterEach(function () {
    Carbon::setTestNow();
});

it('creates a completion on the scheduled date and resolves the matching miss', function () {
    $child = Child::factory()->create();
    $chore = Chore::factory()->create(['value' => 2.50]);

    $miss = ChoreMiss::factory()->create([
        'chore_id' => $chore->id,
        'child_id' => $child->id,
        'missed_date' => today()->subDays(3),
        'completed_at' => null,
    ]);

    Livewire::test(ChoreReport::class)
        ->call('markMissedComplete', $child->id, $chore->id, today()->subDays(3)->toDateString());

    $completion = ChoreCompletion::where('chore_id', $chore->id)
        ->where('child_id', $child->id)
        ->first();

    expect($completion)->not->toBeNull()
        ->and($completion->completed_date->toDateString())->toBe(today()->subDays(3)->toDateString())
        ->and((float) $completion->earned_amount)->toBe(2.50);

    expect($miss->refresh()->completed_at)->not->toBeNull();
});

it('is idempotent on repeat calls', function () {
    $child = Child::factory()->create();
    $chore = Chore::factory()->create(['value' => 1.00]);

    $dateStr = today()->subDays(2)->toDateString();

    $page = Livewire::test(ChoreReport::class);
    $page->call('markMissedComplete', $child->id, $chore->id, $dateStr);
    $page->call('markMissedComplete', $child->id, $chore->id, $dateStr);

    $count = ChoreCompletion::where('chore_id', $chore->id)
        ->where('child_id', $child->id)
        ->count();

    expect($count)->toBe(1);
});

it('creates a completion even when no miss record exists', function () {
    $child = Child::factory()->create();
    $chore = Chore::factory()->create(['value' => 0.75]);

    Livewire::test(ChoreReport::class)
        ->call('markMissedComplete', $child->id, $chore->id, today()->subDays(1)->toDateString());

    $completion = ChoreCompletion::where('chore_id', $chore->id)
        ->where('child_id', $child->id)
        ->first();

    expect($completion)->not->toBeNull()
        ->and($completion->completed_date->toDateString())->toBe(today()->subDays(1)->toDateString());

    expect(ChoreMiss::where('chore_id', $chore->id)->count())->toBe(0);
});

it('silently no-ops when the child does not exist', function () {
    $chore = Chore::factory()->create();

    Livewire::test(ChoreReport::class)
        ->call('markMissedComplete', '01kp0000000000000000000000', $chore->id, today()->subDays(1)->toDateString());

    expect(ChoreCompletion::count())->toBe(0);
});
