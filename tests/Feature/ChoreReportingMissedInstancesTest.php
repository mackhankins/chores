<?php

use App\Models\Child;
use App\Models\Chore;
use App\Models\ChoreAssignment;
use App\Models\ChoreCompletion;
use App\Models\ChoreMiss;
use App\Models\Vacation;
use App\Services\ChoreReportingService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Pin "today" to a Wednesday.
    Carbon::setTestNow(Carbon::parse('2025-01-15 12:00:00'));
});

afterEach(function () {
    Carbon::setTestNow();
});

it('lists past scheduled chores with no completion or resolved miss', function () {
    $child = Child::factory()->create();
    $chore = Chore::factory()->create(['days_of_week' => null]);
    ChoreAssignment::factory()->for($chore)->for($child)->create();

    $result = app(ChoreReportingService::class)->getMissedInstances(
        $child,
        today()->subDays(3),
        today(),
    );

    // subDays(3)=Sun, subDays(2)=Mon, subDays(1)=Tue. Today (Wed) is excluded.
    expect($result)->toHaveCount(3);
});

it('excludes today from the missed list', function () {
    $child = Child::factory()->create();
    $chore = Chore::factory()->create(['days_of_week' => null]);
    ChoreAssignment::factory()->for($chore)->for($child)->create();

    $result = app(ChoreReportingService::class)->getMissedInstances(
        $child,
        today(),
        today(),
    );

    expect($result)->toBeEmpty();
});

it('skips vacation days', function () {
    $child = Child::factory()->create();
    $chore = Chore::factory()->create(['days_of_week' => null]);
    ChoreAssignment::factory()->for($chore)->for($child)->create();

    $vacation = Vacation::create([
        'name' => 'Snow day',
        'start_date' => today()->subDays(2),
        'end_date' => today()->subDays(2),
    ]);
    $child->vacations()->attach($vacation->id);

    $result = app(ChoreReportingService::class)->getMissedInstances(
        $child,
        today()->subDays(3),
        today(),
    );

    $dates = $result->map(fn ($row) => $row['date']->toDateString())->all();

    expect($dates)
        ->toContain(today()->subDays(3)->toDateString())
        ->toContain(today()->subDays(1)->toDateString())
        ->not->toContain(today()->subDays(2)->toDateString());
});

it('excludes dates with a completion', function () {
    $child = Child::factory()->create();
    $chore = Chore::factory()->create(['days_of_week' => null]);
    ChoreAssignment::factory()->for($chore)->for($child)->create();

    ChoreCompletion::factory()->create([
        'chore_id' => $chore->id,
        'child_id' => $child->id,
        'completed_date' => today()->subDays(2),
    ]);

    $result = app(ChoreReportingService::class)->getMissedInstances(
        $child,
        today()->subDays(3),
        today(),
    );

    $dates = $result->map(fn ($row) => $row['date']->toDateString())->all();

    expect($dates)->not->toContain(today()->subDays(2)->toDateString());
});

it('excludes dates with a resolved carryover miss', function () {
    $child = Child::factory()->create();
    $chore = Chore::factory()->create(['days_of_week' => null]);
    ChoreAssignment::factory()->for($chore)->for($child)->create();

    ChoreMiss::factory()->completed()->create([
        'chore_id' => $chore->id,
        'child_id' => $child->id,
        'missed_date' => today()->subDays(2),
    ]);

    $result = app(ChoreReportingService::class)->getMissedInstances(
        $child,
        today()->subDays(3),
        today(),
    );

    $dates = $result->map(fn ($row) => $row['date']->toDateString())->all();

    expect($dates)->not->toContain(today()->subDays(2)->toDateString());
});

it('returns rows for all children when no child filter is given', function () {
    $childA = Child::factory()->create();
    $childB = Child::factory()->create();
    $chore = Chore::factory()->create(['days_of_week' => null]);
    ChoreAssignment::factory()->for($chore)->for($childA)->create();
    ChoreAssignment::factory()->for($chore)->for($childB)->create();

    $result = app(ChoreReportingService::class)->getMissedInstances(
        null,
        today()->subDays(1),
        today(),
    );

    $children = $result->pluck('child.id')->unique()->values()->all();

    expect($result)->toHaveCount(2)
        ->and($children)->toContain($childA->id)
        ->and($children)->toContain($childB->id);
});
